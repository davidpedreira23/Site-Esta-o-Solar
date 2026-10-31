<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\App;
use App\Core\Database as DB;
use App\Core\NotFoundException;
use App\Core\ValidationException;
use App\Support\Validacao as V;

/**
 * SERVIÇO DE ESTAÇÕES  (entidade principal 1)
 * Regras de negócio + acesso ao banco da tabela `estacoes`.
 * CRUD:  criar() | listar()/buscar() | atualizar() | excluir()
 */
final class EstacaoService
{
    /* ----------------------------- READ ----------------------------- */

    /** Lista todas as estações, já com total de leituras e status online/offline. */
    public function listar(): array
    {
        $linhas = DB::all(
            'SELECT e.*, (SELECT COUNT(*) FROM leituras l WHERE l.estacao_id = e.id) AS total_leituras
               FROM estacoes e ORDER BY e.id'
        );
        return array_map([$this, 'comStatus'], $linhas);
    }

    public function listarAtivas(): array
    {
        return array_map([$this, 'comStatus'], DB::all('SELECT * FROM estacoes WHERE ativa = 1 ORDER BY id'));
    }

    public function buscar(int $id): array
    {
        $e = DB::one('SELECT * FROM estacoes WHERE id = ?', [$id]);
        if (!$e) {
            throw new NotFoundException('Estação não encontrada.');
        }
        return $this->comStatus($e);
    }

    /** Usado pela API: acha a estação dona de uma chave X-API-KEY. */
    public function buscarPorChave(string $chave): ?array
    {
        return DB::one('SELECT * FROM estacoes WHERE api_key = ?', [$chave]);
    }

    /* ---------------------------- CREATE ---------------------------- */

    public function criar(array $in): int
    {
        $d = $this->validar($in);
        $d['api_key'] = $this->gerarChave();
        DB::run(
            'INSERT INTO estacoes (nome, local, descricao, latitude, longitude, ativa, api_key)
             VALUES (:nome, :local, :descricao, :latitude, :longitude, :ativa, :api_key)',
            $d
        );
        return DB::lastId();
    }

    /* ---------------------------- UPDATE ---------------------------- */

    public function atualizar(int $id, array $in): void
    {
        $this->buscar($id); // garante que existe (senão lança 404)
        $d = $this->validar($in);
        $d['id'] = $id;
        DB::run(
            'UPDATE estacoes SET nome = :nome, local = :local, descricao = :descricao,
                    latitude = :latitude, longitude = :longitude, ativa = :ativa
              WHERE id = :id',
            $d
        );
    }

    /** Gera uma nova api_key (use se a chave vazar). O ESP32 precisa ser regravado com a nova. */
    public function regenerarChave(int $id): string
    {
        $this->buscar($id);
        $chave = $this->gerarChave();
        DB::run('UPDATE estacoes SET api_key = ? WHERE id = ?', [$chave, $id]);
        return $chave;
    }

    /** Chamado a cada leitura recebida do ESP32: marca "visto agora". */
    public function registrarContato(int $id): void
    {
        DB::run('UPDATE estacoes SET ultimo_contato = NOW() WHERE id = ?', [$id]);
    }

    /* ---------------------------- DELETE ---------------------------- */

    /** Exclui a estação. Suas leituras são apagadas junto (ON DELETE CASCADE no banco). */
    public function excluir(int $id): void
    {
        $this->buscar($id);
        DB::run('DELETE FROM estacoes WHERE id = ?', [$id]);
    }

    /* --------------------------- INTERNOS --------------------------- */

    /** Valida e normaliza os dados vindos do formulário/API. Lança ValidationException se algo estiver errado. */
    private function validar(array $in): array
    {
        $erros = [];
        [$nome, $erros['nome']]           = V::texto($in['nome'] ?? null, 100);
        [$local, $erros['local']]         = V::texto($in['local'] ?? null, 150);
        [$desc, $erros['descricao']]      = V::texto($in['descricao'] ?? null, 1000, false);
        [$lat, $erros['latitude']]        = V::numero($in['latitude'] ?? null, -90, 90, false);
        [$lng, $erros['longitude']]       = V::numero($in['longitude'] ?? null, -180, 180, false);
        $erros = array_filter($erros);
        if ($erros) {
            throw new ValidationException($erros);
        }
        return [
            'nome' => $nome, 'local' => $local, 'descricao' => $desc,
            'latitude' => $lat, 'longitude' => $lng,
            'ativa' => !empty($in['ativa']) && $in['ativa'] !== '0' && $in['ativa'] !== false ? 1 : 0,
        ];
    }

    private function gerarChave(): string
    {
        return bin2hex(random_bytes(16)); // 32 caracteres aleatórios
    }

    /** Acrescenta o campo `online` (true se enviou dados nos últimos N minutos). */
    private function comStatus(array $e): array
    {
        $min = App::config('offline_minutos');
        $e['online'] = $e['ultimo_contato'] !== null
            && strtotime($e['ultimo_contato']) >= time() - $min * 60;
        return $e;
    }
}

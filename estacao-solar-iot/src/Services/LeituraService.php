<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\App;
use App\Core\Database as DB;
use App\Core\NotFoundException;
use App\Core\ValidationException;
use App\Support\Validacao as V;

/**
 * SERVIÇO DE LEITURAS  (entidade principal 2)
 * Medições de temperatura, umidade e luminosidade.
 * Podem chegar do ESP32 (origem 'esp32') ou ser lançadas à mão no painel (origem 'manual').
 */
final class LeituraService
{
    /* ----------------------------- READ ----------------------------- */

    /** Lista paginada, com filtro opcional por estação. Devolve itens + dados de paginação. */
    public function listar(?int $estacaoId = null, int $pagina = 1): array
    {
        $porPagina = (int) App::config('por_pagina');
        $pagina    = max(1, $pagina);
        $where     = $estacaoId ? 'WHERE l.estacao_id = :est' : '';
        $params    = $estacaoId ? ['est' => $estacaoId] : [];

        $total = (int) DB::one("SELECT COUNT(*) AS n FROM leituras l $where", $params)['n'];
        $paginas = max(1, (int) ceil($total / $porPagina));
        $pagina  = min($pagina, $paginas);
        $offset  = ($pagina - 1) * $porPagina;

        // LIMIT/OFFSET precisam ser inteiros (já garantidos acima), por isso entram direto na string
        $itens = DB::all(
            "SELECT l.*, e.nome AS estacao_nome
               FROM leituras l JOIN estacoes e ON e.id = l.estacao_id
               $where ORDER BY l.registrada_em DESC, l.id DESC
               LIMIT $porPagina OFFSET $offset",
            $params
        );
        return compact('itens', 'total', 'pagina', 'paginas');
    }

    public function buscar(int $id): array
    {
        $l = DB::one(
            'SELECT l.*, e.nome AS estacao_nome FROM leituras l
               JOIN estacoes e ON e.id = l.estacao_id WHERE l.id = ?',
            [$id]
        );
        if (!$l) {
            throw new NotFoundException('Leitura não encontrada.');
        }
        return $l;
    }

    /** As N leituras mais recentes de uma estação, em ordem cronológica (para os gráficos). */
    public function ultimas(int $estacaoId, int $limite = 30): array
    {
        $limite = max(1, min($limite, 500));
        $linhas = DB::all(
            "SELECT id, temperatura, umidade, luminosidade, registrada_em
               FROM leituras WHERE estacao_id = ? ORDER BY registrada_em DESC, id DESC LIMIT $limite",
            [$estacaoId]
        );
        return array_reverse($linhas);
    }

    public function ultima(int $estacaoId): ?array
    {
        return DB::one(
            'SELECT * FROM leituras WHERE estacao_id = ? ORDER BY registrada_em DESC, id DESC LIMIT 1',
            [$estacaoId]
        );
    }

    /** Mínimo / máximo / média das últimas 24 horas (funções de agregação do SQL). */
    public function estatisticas24h(int $estacaoId): array
    {
        $r = DB::one(
            'SELECT COUNT(*) AS n,
                    MIN(temperatura) AS temp_min, MAX(temperatura) AS temp_max, AVG(temperatura) AS temp_media,
                    AVG(umidade) AS umid_media, AVG(luminosidade) AS luz_media
               FROM leituras
              WHERE estacao_id = ? AND registrada_em >= NOW() - INTERVAL 24 HOUR',
            [$estacaoId]
        );
        foreach (['temp_min', 'temp_max', 'temp_media', 'umid_media', 'luz_media'] as $k) {
            $r[$k] = $r[$k] !== null ? round((float) $r[$k], 1) : null;
        }
        $r['n'] = (int) $r['n'];
        return $r;
    }

    /* ---------------------------- CREATE ---------------------------- */

    /** Cria uma leitura. $origem = 'esp32' (vinda do dispositivo) ou 'manual' (digitada no painel). */
    public function criar(array $in, string $origem = 'manual'): int
    {
        $d = $this->validar($in, true);
        $d['origem'] = $origem;
        DB::run(
            'INSERT INTO leituras (estacao_id, temperatura, umidade, luminosidade, ldr_bruto, origem, registrada_em)
             VALUES (:estacao_id, :temperatura, :umidade, :luminosidade, :ldr_bruto, :origem, COALESCE(:registrada_em, NOW()))',
            $d
        );
        return DB::lastId();
    }

    /* ---------------------------- UPDATE ---------------------------- */

    public function atualizar(int $id, array $in): void
    {
        $this->buscar($id);
        $d = $this->validar($in, true);
        $d['id'] = $id;
        DB::run(
            'UPDATE leituras SET estacao_id = :estacao_id, temperatura = :temperatura, umidade = :umidade,
                    luminosidade = :luminosidade, ldr_bruto = :ldr_bruto,
                    registrada_em = COALESCE(:registrada_em, registrada_em)
              WHERE id = :id',
            $d
        );
    }

    /* ---------------------------- DELETE ---------------------------- */

    public function excluir(int $id): void
    {
        $this->buscar($id);
        DB::run('DELETE FROM leituras WHERE id = ?', [$id]);
    }

    /* --------------------------- INTERNOS --------------------------- */

    private function validar(array $in, bool $exigeEstacao): array
    {
        $erros = [];

        $estacaoId = (int) ($in['estacao_id'] ?? 0);
        if ($exigeEstacao) {
            if ($estacaoId <= 0 || !DB::one('SELECT id FROM estacoes WHERE id = ?', [$estacaoId])) {
                $erros['estacao_id'] = 'Selecione uma estação válida.';
            }
        }
        // Mesmos limites do sensor DHT22 e do conversor do LDR
        [$temp, $erros['temperatura']]   = V::numero($in['temperatura'] ?? null, -40, 80);
        [$umid, $erros['umidade']]       = V::numero($in['umidade'] ?? null, 0, 100);
        [$luz, $erros['luminosidade']]   = V::numero($in['luminosidade'] ?? null, 0, 100);
        [$ldr, $erros['ldr_bruto']]      = V::numero($in['ldr_bruto'] ?? null, 0, 4095, false);

        // Data/hora opcional (campo <input type="datetime-local"> envia "2026-10-30T14:30")
        $quando = null;
        $bruto  = trim((string) ($in['registrada_em'] ?? ''));
        if ($bruto !== '') {
            $ts = strtotime(str_replace('T', ' ', $bruto));
            if ($ts === false) {
                $erros['registrada_em'] = 'Data/hora inválida.';
            } else {
                $quando = date('Y-m-d H:i:s', $ts);
            }
        }

        $erros = array_filter($erros);
        if ($erros) {
            throw new ValidationException($erros);
        }
        return [
            'estacao_id'    => $estacaoId,
            'temperatura'   => round($temp, 2),
            'umidade'       => round($umid, 2),
            'luminosidade'  => (int) round($luz),
            'ldr_bruto'     => $ldr !== null ? (int) $ldr : null,
            'registrada_em' => $quando,
        ];
    }
}

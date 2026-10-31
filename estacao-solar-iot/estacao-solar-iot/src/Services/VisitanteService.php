<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\App;
use App\Core\Database as DB;
use App\Core\NotFoundException;
use App\Core\ValidationException;
use App\Support\Validacao as V;

/**
 * SERVIÇO DE VISITANTES
 * Cadastro de quem visitou o estande na Feira Tecnológica.
 */
final class VisitanteService
{
    /* ----------------------------- READ ----------------------------- */

    /** Lista paginada com busca opcional por nome, e-mail ou instituição. */
    public function listar(string $busca = '', int $pagina = 1): array
    {
        $porPagina = (int) App::config('por_pagina');
        $pagina    = max(1, $pagina);
        $where     = '';
        $params    = [];
        if ($busca !== '') {
            $where  = 'WHERE v.nome LIKE :b1 OR v.email LIKE :b2 OR v.instituicao LIKE :b3';
            $like   = '%' . $busca . '%';
            $params = ['b1' => $like, 'b2' => $like, 'b3' => $like];
        }
        $total   = (int) DB::one("SELECT COUNT(*) AS n FROM visitantes v $where", $params)['n'];
        $paginas = max(1, (int) ceil($total / $porPagina));
        $pagina  = min($pagina, $paginas);
        $offset  = ($pagina - 1) * $porPagina;

        $itens = DB::all(
            "SELECT v.*, e.nome AS estacao_nome
               FROM visitantes v LEFT JOIN estacoes e ON e.id = v.estacao_id
               $where ORDER BY v.visitado_em DESC, v.id DESC LIMIT $porPagina OFFSET $offset",
            $params
        );
        return compact('itens', 'total', 'pagina', 'paginas');
    }

    public function buscar(int $id): array
    {
        $v = DB::one(
            'SELECT v.*, e.nome AS estacao_nome FROM visitantes v
               LEFT JOIN estacoes e ON e.id = v.estacao_id WHERE v.id = ?',
            [$id]
        );
        if (!$v) {
            throw new NotFoundException('Visitante não encontrado.');
        }
        return $v;
    }

    /* ---------------------------- CREATE ---------------------------- */

    public function criar(array $in): int
    {
        $d = $this->validar($in);
        $this->garantirEmailUnico($d['email']);
        DB::run(
            'INSERT INTO visitantes (nome, email, instituicao, cidade, estacao_id, comentario)
             VALUES (:nome, :email, :instituicao, :cidade, :estacao_id, :comentario)',
            $d
        );
        return DB::lastId();
    }

    /* ---------------------------- UPDATE ---------------------------- */

    public function atualizar(int $id, array $in): void
    {
        $this->buscar($id);
        $d = $this->validar($in);
        $this->garantirEmailUnico($d['email'], $id);
        $d['id'] = $id;
        DB::run(
            'UPDATE visitantes SET nome = :nome, email = :email, instituicao = :instituicao,
                    cidade = :cidade, estacao_id = :estacao_id, comentario = :comentario
              WHERE id = :id',
            $d
        );
    }

    /* ---------------------------- DELETE ---------------------------- */

    public function excluir(int $id): void
    {
        $this->buscar($id);
        DB::run('DELETE FROM visitantes WHERE id = ?', [$id]);
    }

    /* --------------------------- INTERNOS --------------------------- */

    private function validar(array $in): array
    {
        $erros = [];
        [$nome, $erros['nome']]          = V::texto($in['nome'] ?? null, 120);
        [$email, $erros['email']]        = V::email($in['email'] ?? null);
        [$inst, $erros['instituicao']]   = V::texto($in['instituicao'] ?? null, 150, false);
        [$cid, $erros['cidade']]         = V::texto($in['cidade'] ?? null, 100, false);
        [$com, $erros['comentario']]     = V::texto($in['comentario'] ?? null, 500, false);

        $estacaoId = (int) ($in['estacao_id'] ?? 0);
        if ($estacaoId > 0 && !DB::one('SELECT id FROM estacoes WHERE id = ?', [$estacaoId])) {
            $erros['estacao_id'] = 'Estação inexistente.';
        }
        $erros = array_filter($erros);
        if ($erros) {
            throw new ValidationException($erros);
        }
        return [
            'nome' => $nome, 'email' => $email, 'instituicao' => $inst, 'cidade' => $cid,
            'estacao_id' => $estacaoId > 0 ? $estacaoId : null, 'comentario' => $com,
        ];
    }

    /** Regra de negócio: um e-mail só pode ter um cadastro (mensagem amigável antes de bater na UNIQUE do banco). */
    private function garantirEmailUnico(string $email, ?int $ignorarId = null): void
    {
        $r = DB::one('SELECT id FROM visitantes WHERE email = ? AND id <> ?', [$email, $ignorarId ?? 0]);
        if ($r) {
            throw new ValidationException(['email' => 'Este e-mail já está cadastrado.']);
        }
    }
}

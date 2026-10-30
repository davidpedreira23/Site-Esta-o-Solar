<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\ValidationException;
use App\Services\EstacaoService;
use App\Services\LeituraService;

/** CRUD de LEITURAS (páginas web). */
final class LeituraController extends Controller
{
    private LeituraService $svc;

    public function __construct()
    {
        $this->svc = new LeituraService();
    }

    // READ (lista paginada + filtro por estação)
    public function index(Request $req, array $p): Response
    {
        $filtro = (int) ($req->query['estacao'] ?? 0) ?: null;
        $r = $this->svc->listar($filtro, (int) ($req->query['pagina'] ?? 1));
        return $this->view('leituras/index', [
            'titulo' => 'Leituras', 'r' => $r, 'filtro' => $filtro,
            'estacoes' => (new EstacaoService())->listar(),
        ]);
    }

    // CREATE (formulário)
    public function novo(Request $req, array $p): Response
    {
        return $this->view('leituras/form', [
            'titulo' => 'Nova leitura manual', 'leitura' => ['estacao_id' => (int) ($req->query['estacao'] ?? 0)],
            'erros' => [], 'acao' => '/leituras', 'estacoes' => (new EstacaoService())->listar(),
        ]);
    }

    // CREATE (grava)
    public function criar(Request $req, array $p): Response
    {
        try {
            $this->svc->criar($req->body, 'manual');
            return $this->redirecionar('/leituras', 'Leitura registrada.');
        } catch (ValidationException $e) {
            return $this->view('leituras/form', [
                'titulo' => 'Nova leitura manual', 'leitura' => $req->body, 'erros' => $e->errors,
                'acao' => '/leituras', 'estacoes' => (new EstacaoService())->listar(),
            ], 422);
        }
    }

    // UPDATE (formulário)
    public function editar(Request $req, array $p): Response
    {
        $l = $this->svc->buscar($p['id']);
        $l['registrada_em'] = date('Y-m-d\TH:i', strtotime($l['registrada_em'])); // formato do datetime-local
        return $this->view('leituras/form', [
            'titulo' => 'Editar leitura #' . $p['id'], 'leitura' => $l, 'erros' => [],
            'acao' => "/leituras/{$p['id']}/atualizar", 'estacoes' => (new EstacaoService())->listar(),
        ]);
    }

    // UPDATE (grava)
    public function atualizar(Request $req, array $p): Response
    {
        try {
            $this->svc->atualizar($p['id'], $req->body);
            return $this->redirecionar('/leituras', 'Leitura atualizada.');
        } catch (ValidationException $e) {
            return $this->view('leituras/form', [
                'titulo' => 'Editar leitura #' . $p['id'], 'leitura' => $req->body, 'erros' => $e->errors,
                'acao' => "/leituras/{$p['id']}/atualizar", 'estacoes' => (new EstacaoService())->listar(),
            ], 422);
        }
    }

    // DELETE
    public function excluir(Request $req, array $p): Response
    {
        $this->svc->excluir($p['id']);
        return $this->redirecionar('/leituras', 'Leitura excluída.');
    }
}

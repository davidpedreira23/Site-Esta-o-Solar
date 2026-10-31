<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\ValidationException;
use App\Services\EstacaoService;
use App\Services\VisitanteService;

/**
 * CRUD de VISITANTES (páginas web).
 * Cadastro (novo/criar) é PÚBLICO: qualquer visitante da feira se registra.
 * Listar, editar e excluir exigem login de administrador.
 */
final class VisitanteController extends Controller
{
    private VisitanteService $svc;

    public function __construct()
    {
        $this->svc = new VisitanteService();
    }

    public function index(Request $req, array $p): Response
    {
        $busca = trim((string) ($req->query['q'] ?? ''));
        $r = $this->svc->listar($busca, (int) ($req->query['pagina'] ?? 1));
        return $this->view('visitantes/index', ['titulo' => 'Visitantes', 'r' => $r, 'busca' => $busca]);
    }

    public function novo(Request $req, array $p): Response
    {
        return $this->view('visitantes/form', [
            'titulo' => 'Cadastro de visitante', 'visitante' => [], 'erros' => [],
            'acao' => '/visitantes', 'estacoes' => (new EstacaoService())->listarAtivas(), 'publico' => true,
        ]);
    }

    public function criar(Request $req, array $p): Response
    {
        try {
            $this->svc->criar($req->body);
            return $this->redirecionar('/visitantes/obrigado');
        } catch (ValidationException $e) {
            return $this->view('visitantes/form', [
                'titulo' => 'Cadastro de visitante', 'visitante' => $req->body, 'erros' => $e->errors,
                'acao' => '/visitantes', 'estacoes' => (new EstacaoService())->listarAtivas(), 'publico' => true,
            ], 422);
        }
    }

    public function obrigado(Request $req, array $p): Response
    {
        return $this->view('visitantes/obrigado', ['titulo' => 'Obrigado!']);
    }

    public function editar(Request $req, array $p): Response
    {
        return $this->view('visitantes/form', [
            'titulo' => 'Editar visitante', 'visitante' => $this->svc->buscar($p['id']), 'erros' => [],
            'acao' => "/visitantes/{$p['id']}/atualizar", 'estacoes' => (new EstacaoService())->listar(), 'publico' => false,
        ]);
    }

    public function atualizar(Request $req, array $p): Response
    {
        try {
            $this->svc->atualizar($p['id'], $req->body);
            return $this->redirecionar('/visitantes', 'Visitante atualizado.');
        } catch (ValidationException $e) {
            return $this->view('visitantes/form', [
                'titulo' => 'Editar visitante', 'visitante' => $req->body, 'erros' => $e->errors,
                'acao' => "/visitantes/{$p['id']}/atualizar", 'estacoes' => (new EstacaoService())->listar(), 'publico' => false,
            ], 422);
        }
    }

    public function excluir(Request $req, array $p): Response
    {
        $this->svc->excluir($p['id']);
        return $this->redirecionar('/visitantes', 'Visitante excluído.');
    }
}

<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\ValidationException;
use App\Services\EstacaoService;
use App\Services\LeituraService;

/**
 * CRUD de ESTAÇÕES (páginas web).
 * O controller só: recebe a requisição -> chama o Service -> devolve a página/redirecionamento.
 * Regras de negócio e SQL ficam no EstacaoService.
 */
final class EstacaoController extends Controller
{
    private EstacaoService $svc;

    public function __construct()
    {
        $this->svc = new EstacaoService();
    }

    // READ (lista)
    public function index(Request $req, array $p): Response
    {
        return $this->view('estacoes/index', ['titulo' => 'Estações', 'estacoes' => $this->svc->listar()]);
    }

    // READ (detalhe)
    public function mostrar(Request $req, array $p): Response
    {
        $e = $this->svc->buscar($p['id']);
        $ult = (new LeituraService())->listar($p['id'], 1)['itens'];
        return $this->view('estacoes/mostrar', ['titulo' => $e['nome'], 'estacao' => $e, 'leituras' => array_slice($ult, 0, 5)]);
    }

    // CREATE (formulário)
    public function novo(Request $req, array $p): Response
    {
        return $this->view('estacoes/form', [
            'titulo' => 'Nova estação', 'estacao' => ['ativa' => 1], 'erros' => [], 'acao' => '/estacoes',
        ]);
    }

    // CREATE (grava)
    public function criar(Request $req, array $p): Response
    {
        try {
            $id = $this->svc->criar($req->body);
            return $this->redirecionar("/estacoes/$id", 'Estação cadastrada! Copie a chave de API para o ESP32.');
        } catch (ValidationException $e) {
            return $this->view('estacoes/form', [
                'titulo' => 'Nova estação', 'estacao' => $req->body, 'erros' => $e->errors, 'acao' => '/estacoes',
            ], 422);
        }
    }

    // UPDATE (formulário)
    public function editar(Request $req, array $p): Response
    {
        return $this->view('estacoes/form', [
            'titulo' => 'Editar estação', 'estacao' => $this->svc->buscar($p['id']), 'erros' => [],
            'acao' => "/estacoes/{$p['id']}/atualizar",
        ]);
    }

    // UPDATE (grava)
    public function atualizar(Request $req, array $p): Response
    {
        try {
            $this->svc->atualizar($p['id'], $req->body);
            return $this->redirecionar("/estacoes/{$p['id']}", 'Estação atualizada.');
        } catch (ValidationException $e) {
            return $this->view('estacoes/form', [
                'titulo' => 'Editar estação', 'estacao' => $req->body + ['id' => $p['id']], 'erros' => $e->errors,
                'acao' => "/estacoes/{$p['id']}/atualizar",
            ], 422);
        }
    }

    // Ação extra: gerar nova chave de API
    public function novaChave(Request $req, array $p): Response
    {
        $this->svc->regenerarChave($p['id']);
        return $this->redirecionar("/estacoes/{$p['id']}", 'Nova chave gerada. Atualize o firmware do ESP32!');
    }

    // DELETE
    public function excluir(Request $req, array $p): Response
    {
        $this->svc->excluir($p['id']);
        return $this->redirecionar('/estacoes', 'Estação e suas leituras foram excluídas.');
    }
}

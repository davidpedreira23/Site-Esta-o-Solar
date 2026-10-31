<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Services\VisitanteService;

/** API REST de VISITANTES — /api/visitantes (leitura/edição/exclusão exigem login; POST é público). */
final class ApiVisitanteController
{
    private VisitanteService $svc;

    public function __construct()
    {
        $this->svc = new VisitanteService();
    }

    public function listar(Request $req, array $p): Response
    {
        $r = $this->svc->listar(trim((string) ($req->query['q'] ?? '')), (int) ($req->query['pagina'] ?? 1));
        return Response::json(['dados' => $r['itens'], 'total' => $r['total'], 'pagina' => $r['pagina'], 'paginas' => $r['paginas']]);
    }

    public function mostrar(Request $req, array $p): Response
    {
        return Response::json(['dados' => $this->svc->buscar($p['id'])]);
    }

    public function criar(Request $req, array $p): Response
    {
        $id = $this->svc->criar($req->body);
        return Response::json(['dados' => $this->svc->buscar($id)], 201);
    }

    public function atualizar(Request $req, array $p): Response
    {
        $this->svc->atualizar($p['id'], $req->body + $this->svc->buscar($p['id']));
        return Response::json(['dados' => $this->svc->buscar($p['id'])]);
    }

    public function excluir(Request $req, array $p): Response
    {
        $this->svc->excluir($p['id']);
        return Response::json(['mensagem' => 'Visitante excluído.']);
    }
}

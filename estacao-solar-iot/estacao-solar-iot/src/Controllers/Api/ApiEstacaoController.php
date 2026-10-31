<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Services\EstacaoService;

/** API REST de ESTAÇÕES — /api/estacoes  (escrita exige login). A api_key nunca é exposta aqui. */
final class ApiEstacaoController
{
    private EstacaoService $svc;

    public function __construct()
    {
        $this->svc = new EstacaoService();
    }

    private static function publica(array $e): array
    {
        unset($e['api_key']); // segredo do dispositivo: não sai pela API
        return $e;
    }

    public function listar(Request $req, array $p): Response
    {
        return Response::json(['dados' => array_map([self::class, 'publica'], $this->svc->listar())]);
    }

    public function mostrar(Request $req, array $p): Response
    {
        return Response::json(['dados' => self::publica($this->svc->buscar($p['id']))]);
    }

    public function criar(Request $req, array $p): Response
    {
        $id = $this->svc->criar($req->body);
        return Response::json(['dados' => self::publica($this->svc->buscar($id))], 201);
    }

    public function atualizar(Request $req, array $p): Response
    {
        $this->svc->atualizar($p['id'], $req->body);
        return Response::json(['dados' => self::publica($this->svc->buscar($p['id']))]);
    }

    public function excluir(Request $req, array $p): Response
    {
        $this->svc->excluir($p['id']);
        return Response::json(['mensagem' => 'Estação excluída.']);
    }
}

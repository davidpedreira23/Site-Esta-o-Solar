<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Services\EstacaoService;
use App\Services\LeituraService;

/**
 * API REST de LEITURAS — /api/leituras
 *
 *  POST   /api/leituras        <- chamado pelo ESP32 (autentica com cabeçalho X-API-KEY)
 *  GET    /api/leituras        lista (filtros: ?estacao_id=1&pagina=1)
 *  GET    /api/leituras/{id}   detalhe
 *  PUT    /api/leituras/{id}   atualiza  (exige login)
 *  DELETE /api/leituras/{id}   exclui    (exige login)
 */
final class ApiLeituraController
{
    private LeituraService $svc;

    public function __construct()
    {
        $this->svc = new LeituraService();
    }

    /** CREATE — rota usada pelo dispositivo. Não usa sessão: usa a api_key da estação. */
    public function receber(Request $req, array $p): Response
    {
        $chave = $req->header('x-api-key');
        if (!$chave) {
            throw new HttpException(401, 'Cabeçalho X-API-KEY ausente.');
        }
        $estacoes = new EstacaoService();
        $est = $estacoes->buscarPorChave($chave);
        if (!$est) {
            throw new HttpException(401, 'Chave de API inválida.');
        }
        if (!(int) $est['ativa']) {
            throw new HttpException(403, 'Estação desativada.');
        }
        // A estação vem da CHAVE, nunca do corpo: o dispositivo não pode "falar por outra estação"
        $id = $this->svc->criar(['estacao_id' => $est['id']] + $req->body, 'esp32');
        $estacoes->registrarContato((int) $est['id']);

        return Response::json(['dados' => $this->svc->buscar($id)], 201);
    }

    public function listar(Request $req, array $p): Response
    {
        $est = (int) ($req->query['estacao_id'] ?? 0) ?: null;
        $r   = $this->svc->listar($est, (int) ($req->query['pagina'] ?? 1));
        return Response::json(['dados' => $r['itens'], 'total' => $r['total'], 'pagina' => $r['pagina'], 'paginas' => $r['paginas']]);
    }

    public function mostrar(Request $req, array $p): Response
    {
        return Response::json(['dados' => $this->svc->buscar($p['id'])]);
    }

    public function atualizar(Request $req, array $p): Response
    {
        $atual = $this->svc->buscar($p['id']);
        // Em PUT, campos não enviados mantêm o valor atual
        $this->svc->atualizar($p['id'], $req->body + $atual);
        return Response::json(['dados' => $this->svc->buscar($p['id'])]);
    }

    public function excluir(Request $req, array $p): Response
    {
        $this->svc->excluir($p['id']);
        return Response::json(['mensagem' => 'Leitura excluída.']);
    }
}

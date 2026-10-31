<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Services\DashboardService;

/** GET /api/dashboard?estacao=1 — alimenta os cards e gráficos (a página atualiza sozinha a cada 5 s). */
final class ApiDashboardController
{
    public function resumo(Request $req, array $p): Response
    {
        $id = isset($req->query['estacao']) ? (int) $req->query['estacao'] : null;
        $limite = (int) ($req->query['limite'] ?? 30);
        return Response::json((new DashboardService())->resumo($id, $limite));
    }
}

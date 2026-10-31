<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\DashboardService;

/** Página inicial pública: painel em tempo real da estação (cards + gráficos). */
final class DashboardController extends Controller
{
    public function index(Request $req, array $p): Response
    {
        $svc = new DashboardService();
        $dados = $svc->resumo(isset($req->query['estacao']) ? (int) $req->query['estacao'] : null);
        return $this->view('dashboard/index', ['titulo' => 'Painel ao vivo', 'resumo' => $dados]);
    }
}

<?php
declare(strict_types=1);

use App\Controllers\Api\{ApiDashboardController, ApiEstacaoController, ApiLeituraController, ApiVisitanteController};
use App\Controllers\{AuthController, DashboardController, EstacaoController, LeituraController, VisitanteController};

/**
 * MAPA DE ROTAS DO SISTEMA
 * O 3º argumento "true" significa: exige login de administrador.
 *
 * Padrão CRUD usado nas páginas web:
 *   GET  /recurso                 -> READ   (lista)
 *   GET  /recurso/novo            -> CREATE (formulário)
 *   POST /recurso                 -> CREATE (grava)
 *   GET  /recurso/{id}/editar     -> UPDATE (formulário)
 *   POST /recurso/{id}/atualizar  -> UPDATE (grava)
 *   POST /recurso/{id}/excluir    -> DELETE
 * (navegadores só enviam GET/POST em formulários, por isso o PUT/DELETE "de verdade" fica na API)
 */
return static function (App\Core\Router $r): void {

    // ----------------------------- PÁGINAS WEB -----------------------------
    $r->get('/',            [DashboardController::class, 'index']);          // público

    $r->get('/login',       [AuthController::class, 'formulario']);
    $r->post('/login',      [AuthController::class, 'entrar']);
    $r->post('/logout',     [AuthController::class, 'sair']);

    // Estações (CRUD completo) — admin
    $r->get('/estacoes',                       [EstacaoController::class, 'index'], true);
    $r->get('/estacoes/novo',                  [EstacaoController::class, 'novo'], true);
    $r->post('/estacoes',                      [EstacaoController::class, 'criar'], true);
    $r->get('/estacoes/{id}',                  [EstacaoController::class, 'mostrar'], true);
    $r->get('/estacoes/{id}/editar',           [EstacaoController::class, 'editar'], true);
    $r->post('/estacoes/{id}/atualizar',       [EstacaoController::class, 'atualizar'], true);
    $r->post('/estacoes/{id}/nova-chave',      [EstacaoController::class, 'novaChave'], true);
    $r->post('/estacoes/{id}/excluir',         [EstacaoController::class, 'excluir'], true);

    // Leituras (CRUD completo) — admin
    $r->get('/leituras',                       [LeituraController::class, 'index'], true);
    $r->get('/leituras/novo',                  [LeituraController::class, 'novo'], true);
    $r->post('/leituras',                      [LeituraController::class, 'criar'], true);
    $r->get('/leituras/{id}/editar',           [LeituraController::class, 'editar'], true);
    $r->post('/leituras/{id}/atualizar',       [LeituraController::class, 'atualizar'], true);
    $r->post('/leituras/{id}/excluir',         [LeituraController::class, 'excluir'], true);

    // Visitantes — cadastro PÚBLICO; o resto é admin
    $r->get('/visitantes/novo',                [VisitanteController::class, 'novo']);
    $r->post('/visitantes',                    [VisitanteController::class, 'criar']);
    $r->get('/visitantes/obrigado',            [VisitanteController::class, 'obrigado']);
    $r->get('/visitantes',                     [VisitanteController::class, 'index'], true);
    $r->get('/visitantes/{id}/editar',         [VisitanteController::class, 'editar'], true);
    $r->post('/visitantes/{id}/atualizar',     [VisitanteController::class, 'atualizar'], true);
    $r->post('/visitantes/{id}/excluir',       [VisitanteController::class, 'excluir'], true);

    // ------------------------------- API REST -------------------------------
    $r->get('/api/dashboard',                  [ApiDashboardController::class, 'resumo']);

    $r->post('/api/leituras',                  [ApiLeituraController::class, 'receber']);   // ESP32 (X-API-KEY)
    $r->get('/api/leituras',                   [ApiLeituraController::class, 'listar']);
    $r->get('/api/leituras/{id}',              [ApiLeituraController::class, 'mostrar']);
    $r->put('/api/leituras/{id}',              [ApiLeituraController::class, 'atualizar'], true);
    $r->delete('/api/leituras/{id}',           [ApiLeituraController::class, 'excluir'], true);

    $r->get('/api/estacoes',                   [ApiEstacaoController::class, 'listar']);
    $r->get('/api/estacoes/{id}',              [ApiEstacaoController::class, 'mostrar']);
    $r->post('/api/estacoes',                  [ApiEstacaoController::class, 'criar'], true);
    $r->put('/api/estacoes/{id}',              [ApiEstacaoController::class, 'atualizar'], true);
    $r->delete('/api/estacoes/{id}',           [ApiEstacaoController::class, 'excluir'], true);

    $r->post('/api/visitantes',                [ApiVisitanteController::class, 'criar']);
    $r->get('/api/visitantes',                 [ApiVisitanteController::class, 'listar'], true);
    $r->get('/api/visitantes/{id}',            [ApiVisitanteController::class, 'mostrar'], true);
    $r->put('/api/visitantes/{id}',            [ApiVisitanteController::class, 'atualizar'], true);
    $r->delete('/api/visitantes/{id}',         [ApiVisitanteController::class, 'excluir'], true);

    // Login pela API (para testar com Postman/curl): devolve cookie de sessão
    $r->post('/api/login',                     [AuthController::class, 'entrarApi']);
};

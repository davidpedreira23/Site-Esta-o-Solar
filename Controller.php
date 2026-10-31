<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Core\View;

/** Base dos controllers web: atalhos para renderizar página e redirecionar com mensagem. */
abstract class Controller
{
    protected function view(string $nome, array $dados = [], int $status = 200): Response
    {
        return Response::html(View::render($nome, $dados), $status);
    }

    protected function redirecionar(string $caminho, ?string $mensagem = null, string $tipo = 'ok'): Response
    {
        if ($mensagem !== null) {
            flash($tipo, $mensagem);
        }
        return Response::redirect($caminho);
    }
}

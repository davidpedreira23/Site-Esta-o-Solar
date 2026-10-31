<?php
declare(strict_types=1);

/**
 * FRONT CONTROLLER — todas as requisições entram por aqui (via .htaccess).
 * Fluxo:  Request -> Router -> Controller -> Service -> Banco -> Response
 */

// Só para o servidor embutido do PHP (php -S): entrega css/js/imagens direto, sem passar pelo roteador
if (PHP_SAPI === 'cli-server') {
    $arq = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($arq)) {
        return false;
    }
}

$raiz = dirname(__DIR__);

// Autoload simples (PSR-4): App\Services\EstacaoService -> src/Services/EstacaoService.php
spl_autoload_register(static function (string $classe) use ($raiz): void {
    if (str_starts_with($classe, 'App\\')) {
        $arquivo = $raiz . '/src/' . str_replace('\\', '/', substr($classe, 4)) . '.php';
        if (is_file($arquivo)) {
            require $arquivo;
        }
    }
});
// Exceptions.php guarda 3 classes num arquivo só: carregamos manualmente
require $raiz . '/src/Core/Exceptions.php';
require $raiz . '/src/Support/helpers.php';

use App\Core\{App, Auth, Request, Router};

App::boot(require $raiz . '/config/config.php');
Auth::start();

$router = new Router();
(require $raiz . '/config/routes.php')($router);

$router->dispatch(Request::capture())->send();

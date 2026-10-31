<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Representa a requisição HTTP recebida (método, caminho, dados enviados, cabeçalhos).
 * Aceita tanto formulários HTML quanto JSON (usado pelo ESP32 e pela API).
 */
final class Request
{
    public function __construct(
        public string $method,
        public string $path,
        public array $query,
        public array $body,
        public array $headers,
    ) {}

    public static function capture(): self
    {
        $uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $uri    = rawurldecode($uri);
        $script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';

        // Descobre o "caminho base": testa a pasta do index.php e a pasta acima dela
        // (cobre acesso direto a /public e acesso pela raiz do projeto via .htaccess)
        $pasta = rtrim(str_replace('\\', '/', dirname($script)), '/');
        $pai   = rtrim(str_replace('\\', '/', dirname($pasta)), '/');
        $base  = '';
        foreach ([$pasta, $pai] as $cand) {
            if ($cand !== '' && ($uri === $cand || str_starts_with($uri, $cand . '/'))) {
                $base = $cand;
                break;
            }
        }
        App::$base = $base;
        $path = substr($uri, strlen($base)) ?: '/';
        $path = '/' . trim($path, '/');

        // Cabeçalhos em minúsculo
        $headers = [];
        foreach ($_SERVER as $k => $v) {
            if (str_starts_with($k, 'HTTP_')) {
                $headers[strtolower(str_replace('_', '-', substr($k, 5)))] = (string) $v;
            }
        }
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $headers['content-type'] = $_SERVER['CONTENT_TYPE'];
        }

        // Corpo: JSON (API/ESP32) ou formulário (páginas web)
        $body = $_POST;
        if (str_contains($headers['content-type'] ?? '', 'application/json')) {
            $json = json_decode((string) file_get_contents('php://input'), true);
            $body = is_array($json) ? $json : [];
        }

        return new self($_SERVER['REQUEST_METHOD'] ?? 'GET', $path, $_GET, $body, $headers);
    }

    public function isApi(): bool
    {
        return str_starts_with($this->path, '/api');
    }

    public function header(string $nome): ?string
    {
        return $this->headers[strtolower($nome)] ?? null;
    }

    public function input(string $chave, mixed $padrao = null): mixed
    {
        return $this->body[$chave] ?? $padrao;
    }
}

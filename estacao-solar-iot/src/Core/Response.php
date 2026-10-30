<?php
declare(strict_types=1);

namespace App\Core;

/** Resposta HTTP: HTML, JSON ou redirecionamento. O Router é quem a "envia". */
final class Response
{
    private function __construct(
        private int $status,
        private string $corpo,
        private array $headers = [],
    ) {}

    public static function html(string $html, int $status = 200): self
    {
        return new self($status, $html, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public static function json(mixed $dados, int $status = 200): self
    {
        return new self(
            $status,
            json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION),
            ['Content-Type' => 'application/json; charset=utf-8']
        );
    }

    public static function redirect(string $caminho): self
    {
        return new self(302, '', ['Location' => App::$base . $caminho]);
    }

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $k => $v) {
            header("$k: $v");
        }
        echo $this->corpo;
    }
}

<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Guarda configuração global e o "caminho base" da aplicação.
 * O caminho base permite o sistema funcionar tanto em http://localhost:8000/
 * quanto em http://localhost/estacao-solar-iot/ (XAMPP).
 */
final class App
{
    private static array $config = [];
    public static string $base = '';

    public static function boot(array $config): void
    {
        self::$config = $config;
        date_default_timezone_set($config['timezone']);
    }

    /** App::config('db') ou App::config('db')['host'] */
    public static function config(string $chave): mixed
    {
        return self::$config[$chave] ?? null;
    }
}

<?php
declare(strict_types=1);

/**
 * CONFIGURAÇÃO DA APLICAÇÃO
 * Lê as variáveis do arquivo .env (se existir) ou do ambiente do servidor (Docker).
 * Assim nenhuma senha fica escrita dentro do código versionado no GitHub.
 */

// 1) Carrega o .env (formato CHAVE=valor, uma por linha)
$envFile = dirname(__DIR__) . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linha) {
        $linha = trim($linha);
        if ($linha === '' || $linha[0] === '#' || !str_contains($linha, '=')) {
            continue; // ignora comentários e linhas inválidas
        }
        [$k, $v] = explode('=', $linha, 2);
        $_ENV[trim($k)] = trim($v);
    }
}

// 2) Função auxiliar: procura primeiro no .env, depois no ambiente, senão usa o padrão
$env = static fn(string $k, string $padrao = ''): string =>
    $_ENV[$k] ?? (getenv($k) !== false ? (string) getenv($k) : $padrao);

return [
    'db' => [
        'host' => $env('DB_HOST', '127.0.0.1'),
        'port' => $env('DB_PORT', '3306'),
        'name' => $env('DB_NAME', 'estacao_solar'),
        'user' => $env('DB_USER', 'root'),
        'pass' => $env('DB_PASS', ''),
    ],
    'debug'          => $env('APP_DEBUG', '0') === '1',
    'timezone'       => 'America/Sao_Paulo',
    'offline_minutos' => (int) $env('ESTACAO_OFFLINE_MIN', '2'),
    'por_pagina'     => 10,
];

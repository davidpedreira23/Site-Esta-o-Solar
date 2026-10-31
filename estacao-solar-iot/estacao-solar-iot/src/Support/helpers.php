<?php
declare(strict_types=1);

use App\Core\App;
use App\Core\Auth;

/** Escapa texto para HTML (proteção contra XSS). Use SEMPRE ao imprimir dado vindo do usuário/banco. */
function e(mixed $v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Monta uma URL interna respeitando o caminho base. */
function url(string $caminho = '/'): string
{
    return App::$base . $caminho;
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(Auth::csrfToken()) . '">';
}

/** Mensagens "flash": aparecem uma vez após redirecionar (ex.: "Salvo com sucesso"). */
function flash(?string $tipo = null, ?string $msg = null): ?array
{
    if ($tipo !== null) {
        $_SESSION['flash'] = ['tipo' => $tipo, 'msg' => $msg];
        return null;
    }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function data_br(?string $d, bool $segundos = false): string
{
    return $d ? date($segundos ? 'd/m/Y H:i:s' : 'd/m/Y H:i', strtotime($d)) : '—';
}

function sel(mixed $a, mixed $b): string
{
    return (string) $a === (string) $b ? 'selected' : '';
}

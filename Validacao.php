<?php
declare(strict_types=1);

namespace App\Support;

/**
 * Pequenas funções de validação/limpeza reaproveitadas pelos Services.
 * Cada uma recebe o valor bruto e devolve [valorLimpo, mensagemDeErro|null].
 */
final class Validacao
{
    public static function texto(mixed $v, int $max, bool $obrigatorio = true): array
    {
        $v = trim((string) ($v ?? ''));
        if ($v === '') {
            return [$obrigatorio ? '' : null, $obrigatorio ? 'Campo obrigatório.' : null];
        }
        if (self::len($v) > $max) {
            return [$v, "Máximo de $max caracteres."];
        }
        return [$v, null];
    }

    public static function numero(mixed $v, float $min, float $max, bool $obrigatorio = true): array
    {
        if ($v === null || trim((string) $v) === '') {
            return [null, $obrigatorio ? 'Campo obrigatório.' : null];
        }
        $v = str_replace(',', '.', trim((string) $v)); // aceita vírgula decimal (padrão BR)
        if (!is_numeric($v)) {
            return [null, 'Informe um número válido.'];
        }
        $n = (float) $v;
        if ($n < $min || $n > $max) {
            return [$n, "Valor deve estar entre $min e $max."];
        }
        return [$n, null];
    }

    public static function email(mixed $v): array
    {
        $v = trim((string) ($v ?? ''));
        if ($v === '') {
            return ['', 'Campo obrigatório.'];
        }
        if (self::len($v) > 150 || !filter_var($v, FILTER_VALIDATE_EMAIL)) {
            return [$v, 'E-mail inválido.'];
        }
        return [self::minusculo($v), null];
    }

    /** Tamanho em caracteres (usa mbstring se existir; senão conta pelo PCRE em UTF-8). */
    private static function len(string $s): int
    {
        return function_exists('mb_strlen') ? mb_strlen($s) : (int) preg_match_all('/./su', $s);
    }

    private static function minusculo(string $s): string
    {
        return function_exists('mb_strtolower') ? mb_strtolower($s) : strtolower($s);
    }
}

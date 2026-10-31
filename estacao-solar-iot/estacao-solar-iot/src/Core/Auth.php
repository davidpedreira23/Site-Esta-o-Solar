<?php
declare(strict_types=1);

namespace App\Core;

/**
 * AUTENTICAÇÃO (sessão PHP) + proteção CSRF.
 *  - Login compara a senha digitada com o hash guardado no banco (password_verify).
 *  - CSRF: todo formulário POST leva um token secreto da sessão; sem ele a requisição é recusada.
 */
final class Auth
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'httponly' => true,       // JavaScript não lê o cookie
                'samesite' => 'Lax',      // não é enviado em POST vindo de outros sites
            ]);
            session_start();
        }
    }

    public static function attempt(string $email, string $senha): bool
    {
        $u = Database::one('SELECT id, nome, senha_hash FROM usuarios WHERE email = ?', [$email]);
        // password_verify também roda quando o usuário não existe para não revelar por tempo de resposta
        $hash = $u['senha_hash'] ?? '$2y$10$invalidinvalidinvalidinvalidinvalidinvalidinvalidinvalidinva';
        if ($u && password_verify($senha, $hash)) {
            session_regenerate_id(true);  // evita "session fixation"
            $_SESSION['usuario'] = ['id' => (int) $u['id'], 'nome' => $u['nome']];
            return true;
        }
        return false;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    public static function check(): bool
    {
        return isset($_SESSION['usuario']);
    }

    public static function user(): ?array
    {
        return $_SESSION['usuario'] ?? null;
    }

    public static function csrfToken(): string
    {
        return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
    }

    public static function csrfValido(?string $token): bool
    {
        return is_string($token) && hash_equals($_SESSION['csrf'] ?? '', $token);
    }
}

<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * CAMADA DE ACESSO A DADOS
 * Mantém UMA única conexão PDO (padrão Singleton) reaproveitada por todos os Services.
 * Todas as consultas do sistema usam "prepared statements" -> protege contra SQL Injection.
 */
final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            $c   = App::config('db');
            $dsn = "mysql:host={$c['host']};port={$c['port']};dbname={$c['name']};charset=utf8mb4";

            self::$pdo = new PDO($dsn, $c['user'], $c['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // erros viram exceções
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // linhas como array
                PDO::ATTR_EMULATE_PREPARES   => false,                    // prepared statements de verdade
            ]);
            // Alinha o fuso do MySQL com o do PHP (para NOW() gravar a hora certa)
            self::$pdo->exec("SET time_zone = '" . (new \DateTime('now', new \DateTimeZone(App::config('timezone'))))->format('P') . "'");
        }
        return self::$pdo;
    }

    /** Executa um SELECT e devolve todas as linhas. */
    public static function all(string $sql, array $params = []): array
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    /** Executa um SELECT e devolve só a primeira linha (ou null). */
    public static function one(string $sql, array $params = []): ?array
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($params);
        $r = $st->fetch();
        return $r === false ? null : $r;
    }

    /** Executa INSERT/UPDATE/DELETE e devolve quantas linhas foram afetadas. */
    public static function run(string $sql, array $params = []): int
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($params);
        return $st->rowCount();
    }

    public static function lastId(): int
    {
        return (int) self::pdo()->lastInsertId();
    }
}

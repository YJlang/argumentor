<?php

declare(strict_types=1);

namespace App\Support;

use PDO;

/**
 * 단일 PDO 커넥션 팩토리. 항상 prepared statement를 사용할 것.
 */
final class Database
{
    private static ?PDO $instance = null;

    public static function connection(): PDO
    {
        if (self::$instance instanceof PDO) {
            return self::$instance;
        }

        $host    = Env::get('DB_HOST', '127.0.0.1');
        $port    = Env::get('DB_PORT', '3306');
        $name    = Env::get('DB_NAME', 'argumentor');
        $charset = Env::get('DB_CHARSET', 'utf8mb4');
        $user    = Env::get('DB_USER', 'root');
        $pass    = Env::get('DB_PASS', '');

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";

        self::$instance = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);

        return self::$instance;
    }
}

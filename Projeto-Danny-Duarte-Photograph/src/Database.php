<?php

declare(strict_types=1);

namespace Danny;

use PDO;

require_once __DIR__ . '/SchemaFixer.php';

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            env('DB_HOST', 'db'),
            env('DB_PORT', '3306'),
            env('DB_DATABASE', 'danny')
        );

        self::$connection = new PDO($dsn, (string) env('DB_USERNAME', 'danny'), (string) env('DB_PASSWORD', ''), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        SchemaFixer::run(self::$connection);

        return self::$connection;
    }
}

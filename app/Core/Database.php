<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $db = Env::get('DB_NAME') ?? '';
        $user = Env::get('DB_USER') ?? '';
        $pass = Env::get('DB_PASS') ?? '';
        $charset = Env::get('DB_CHARSET', 'utf8mb4') ?? 'utf8mb4';

        $socket = Env::get('DB_UNIX_SOCKET', '') ?? '';
        $socket = trim((string) $socket);

        if ($socket !== '') {
            $dsn = "mysql:unix_socket={$socket};dbname={$db};charset={$charset}";
        } else {
            $hostRaw = Env::get('DB_HOST', 'localhost') ?? 'localhost';
            $portEnv = Env::get('DB_PORT', '') ?? '';
            $portEnv = trim((string) $portEnv);
            $host = $hostRaw;
            $port = $portEnv !== '' ? (int) $portEnv : null;
            if ($port === null && preg_match('/^(.+):(\d+)$/', $hostRaw, $m)) {
                $host = $m[1];
                $port = (int) $m[2];
            }
            $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
            if ($port !== null && $port > 0) {
                $dsn .= ";port={$port}";
            }
        }

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        try {
            self::$pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Error al conectar con la base de datos'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        return self::$pdo;
    }
}

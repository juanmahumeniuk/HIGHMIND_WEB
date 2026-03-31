<?php
declare(strict_types=1);

namespace App\Core;

final class Https
{
    public static function requireIfEnabled(): void
    {
        if (!self::isForceEnabled()) {
            return;
        }

        if (self::connectionIsSecure()) {
            return;
        }

        $host = $_SERVER['HTTP_HOST'] ?? '';
        if (preg_match('/^(localhost|127\.0\.0\.1)(:\d+)?$/i', $host) === 1) {
            return;
        }
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        header('Location: https://' . $host . $uri, true, 301);
        exit;
    }

    private static function isForceEnabled(): bool
    {
        $v = strtolower(Env::get('FORCE_HTTPS', 'true') ?? 'true');
        return !in_array($v, ['0', 'false', 'no', 'off'], true);
    }

    public static function connectionIsSecure(): bool
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            return true;
        }
        if (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') {
            return true;
        }
        return false;
    }
}

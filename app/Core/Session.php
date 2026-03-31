<?php
declare(strict_types=1);

namespace App\Core;

final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'secure' => Https::connectionIsSecure(),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }
}

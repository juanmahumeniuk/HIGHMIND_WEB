<?php
declare(strict_types=1);

namespace App\Core;

final class JsonResponse
{
    public static function send(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}

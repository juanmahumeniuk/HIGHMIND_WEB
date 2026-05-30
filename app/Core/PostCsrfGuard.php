<?php
declare(strict_types=1);

namespace App\Core;

trait PostCsrfGuard
{
    private function assertPostCsrf(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            JsonResponse::send(['ok' => false, 'msg' => 'Método no permitido'], 405);
            return false;
        }
        $token = Input::postCsrfToken();
        if ($token === '' || !Csrf::validate($token)) {
            JsonResponse::send(['ok' => false, 'msg' => 'Token de seguridad inválido'], 403);
            return false;
        }
        return true;
    }
}

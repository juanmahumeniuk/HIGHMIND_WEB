<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\Usuario;

final class AdminAuth
{
    public static function require(): bool
    {
        Session::start();
        if (!isset($_SESSION['usuario_id'])) {
            JsonResponse::send(['ok' => false, 'msg' => 'No autorizado'], 401);
            return false;
        }
        $uid = (int) $_SESSION['usuario_id'];
        $userModel = new Usuario();
        if (!$userModel->esAdminPorId($uid)) {
            JsonResponse::send(['ok' => false, 'msg' => 'Se requieren permisos de administrador'], 403);
            return false;
        }
        $_SESSION['usuario_es_admin'] = true;
        return true;
    }
}

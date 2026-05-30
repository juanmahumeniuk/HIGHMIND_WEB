<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Admin\CarritoAdminHandler;
use App\Controllers\Admin\ContactoAdminHandler;
use App\Controllers\Admin\ProductoAdminHandler;
use App\Controllers\Admin\UsuarioAdminHandler;
use App\Core\AdminAuth;
use App\Core\Controller\AbstractController;

final class AdminController extends AbstractController
{
    public function handle(string $tail): void
    {
        $tail = trim($tail, '/');
        if ($tail === '') {
            $this->jsonError('Ruta admin inválida', 404);
            return;
        }

        $segments = explode('/', $tail);
        $entity = $segments[0] ?? '';
        $id = isset($segments[1]) && ctype_digit((string) $segments[1]) ? (int) $segments[1] : null;

        if (!AdminAuth::require()) {
            return;
        }

        match ($entity) {
            'productos' => (new ProductoAdminHandler())->handle($id),
            'usuarios' => (new UsuarioAdminHandler())->handle($id),
            'carrito_items' => (new CarritoAdminHandler())->handle($id),
            'contacto_mensajes' => (new ContactoAdminHandler())->handle($id),
            default => $this->jsonError('Recurso admin desconocido', 404),
        };
    }
}

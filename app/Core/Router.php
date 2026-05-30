<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Controller\AbstractController;

final class Router extends AbstractController
{
    public function dispatch(string $resource): void
    {
        match ($resource) {
            'productos' => (new \App\Controllers\ProductoController())->index(),
            'usuarios'  => (new \App\Controllers\UsuarioController())->handle(),
            'carrito'   => (new \App\Controllers\CarritoController())->handle(),
            'contacto'  => (new \App\Controllers\ContactoController())->handle(),
            'pagos'     => (new \App\Controllers\PagoController())->handle(),
            'firebase'  => (new \App\Controllers\FirebaseController())->handle(),
            default     => $this->jsonError('Recurso no encontrado', 404),
        };
    }
}

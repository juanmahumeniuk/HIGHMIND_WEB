<?php
declare(strict_types=1);

namespace App\Core;

use App\Controllers\CarritoController;
use App\Controllers\ContactoController;
use App\Controllers\PagoController;
use App\Controllers\ProductoController;
use App\Controllers\UsuarioController;

final class Router
{
    public function dispatch(string $resource): void
    {
        match ($resource) {
            'productos' => (new ProductoController())->index(),
            'usuarios' => (new UsuarioController())->handle(),
            'carrito' => (new CarritoController())->handle(),
            'contacto' => (new ContactoController())->handle(),
            'pagos' => (new PagoController())->handle(),
            default => JsonResponse::send(['error' => 'Recurso no encontrado'], 404),
        };
    }
}

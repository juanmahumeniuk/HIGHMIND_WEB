<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Input;
use App\Core\JsonResponse;
use App\Core\Session;
use App\Models\Carrito;

final class CarritoController
{
    public function handle(): void
    {
        Session::start();
        if (!isset($_SESSION['usuario_id'])) {
            JsonResponse::send(['ok' => false, 'msg' => 'No autorizado'], 401);
            return;
        }
        $usuarioId = (int) $_SESSION['usuario_id'];
        $action = $_POST['action'] ?? $_GET['action'] ?? '';
        $model = new Carrito();

        if ($action === 'get') {
            $this->get($model, $usuarioId);
            return;
        }

        if (!in_array($action, ['add', 'remove', 'update', 'clear'], true)) {
            JsonResponse::send(['ok' => false, 'msg' => 'Acción no válida']);
            return;
        }

        if (!$this->assertMutatingPostWithCsrf()) {
            return;
        }

        match ($action) {
            'add' => $this->add($model, $usuarioId),
            'remove' => $this->remove($model, $usuarioId),
            'update' => $this->update($model, $usuarioId),
            'clear' => $this->clear($model, $usuarioId),
            default => JsonResponse::send(['ok' => false, 'msg' => 'Acción no válida']),
        };
    }

    private function assertMutatingPostWithCsrf(): bool
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

    private function add(Carrito $model, int $usuarioId): void
    {
        $productoId = Input::postPositiveInt('id', 0);
        if ($productoId < 1) {
            JsonResponse::send(['ok' => false, 'msg' => 'Producto inválido'], 400);
            return;
        }
        $cantidad = Input::postPositiveInt('qty', 1);
        if ($cantidad < 1) {
            $cantidad = 1;
        }
        $model->agregarOIncrementar($usuarioId, $productoId, $cantidad);
        JsonResponse::send(['ok' => true]);
    }

    private function remove(Carrito $model, int $usuarioId): void
    {
        $productoId = Input::postPositiveInt('id', 0);
        $model->quitar($usuarioId, $productoId);
        JsonResponse::send(['ok' => true]);
    }

    private function update(Carrito $model, int $usuarioId): void
    {
        $productoId = Input::postPositiveInt('id', 0);
        $cantidad = Input::postPositiveInt('qty', 1);
        if ($cantidad < 1) {
            $cantidad = 1;
        }
        $model->actualizarCantidad($usuarioId, $productoId, $cantidad);
        JsonResponse::send(['ok' => true]);
    }

    private function clear(Carrito $model, int $usuarioId): void
    {
        $model->vaciar($usuarioId);
        JsonResponse::send(['ok' => true]);
    }

    private function get(Carrito $model, int $usuarioId): void
    {
        $data = $model->obtenerConProductos($usuarioId);
        JsonResponse::send([
            'ok' => true,
            'carrito' => $data['carrito'],
            'subtotal' => $data['subtotal'],
            'total_items' => $data['total_items'],
        ]);
    }
}

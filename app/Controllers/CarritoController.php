<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller\AuthenticatedController;
use App\Core\Input;
use App\Models\Carrito;
use App\Models\Producto;

final class CarritoController extends AuthenticatedController
{
    public function handle(): void
    {
        $usuarioId = $this->requireAuth();
        if ($usuarioId === null) {
            return;
        }

        $action = $this->action();
        $model = new Carrito();

        if ($action === 'get') {
            $this->get($model, $usuarioId);
            return;
        }

        if (!in_array($action, ['add', 'remove', 'update', 'clear'], true)) {
            $this->jsonError('Acción no válida');
            return;
        }

        if (!$this->requirePostCsrf()) {
            return;
        }

        match ($action) {
            'add' => $this->add($model, $usuarioId),
            'remove' => $this->remove($model, $usuarioId),
            'update' => $this->update($model, $usuarioId),
            'clear' => $this->clear($model, $usuarioId),
            default => $this->jsonError('Acción no válida'),
        };
    }

    private function add(Carrito $model, int $usuarioId): void
    {
        $productoId = Input::postPositiveInt('id', 0);
        if ($productoId < 1) {
            $this->jsonError('Producto inválido', 400);
            return;
        }
        $cantidad = max(1, Input::postPositiveInt('qty', 1));

        $productoModel = new Producto();
        $stockDisponible = $productoModel->obtenerStock($productoId);
        $cantidadActual = $model->obtenerCantidadItem($usuarioId, $productoId);

        if (($cantidadActual + $cantidad) > $stockDisponible) {
            $this->jsonError('Stock insuficiente', 400);
            return;
        }

        $model->agregarOIncrementar($usuarioId, $productoId, $cantidad);
        $this->jsonOk();
    }

    private function remove(Carrito $model, int $usuarioId): void
    {
        $model->quitar($usuarioId, Input::postPositiveInt('id', 0));
        $this->jsonOk();
    }

    private function update(Carrito $model, int $usuarioId): void
    {
        $productoId = Input::postPositiveInt('id', 0);
        $cantidad = max(1, Input::postPositiveInt('qty', 1));

        $stockDisponible = (new Producto())->obtenerStock($productoId);
        if ($cantidad > $stockDisponible) {
            $this->jsonError('Stock insuficiente', 400);
            return;
        }

        $model->actualizarCantidad($usuarioId, $productoId, $cantidad);
        $this->jsonOk();
    }

    private function clear(Carrito $model, int $usuarioId): void
    {
        $model->vaciar($usuarioId);
        $this->jsonOk();
    }

    private function get(Carrito $model, int $usuarioId): void
    {
        $data = $model->obtenerConProductos($usuarioId);
        $this->jsonOk([
            'carrito' => $data['carrito'],
            'subtotal' => $data['subtotal'],
            'total_items' => $data['total_items'],
        ]);
    }
}

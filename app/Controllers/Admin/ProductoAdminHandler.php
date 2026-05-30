<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Input;
use App\Models\Producto;
use App\Services\ProductImageUploader;

final class ProductoAdminHandler extends AbstractAdminHandler
{
    protected function entityLabel(): string
    {
        return 'Producto';
    }

    protected function model(): Producto
    {
        return new Producto();
    }

    protected function onCreate(): void
    {
        $model = $this->model();
        $data = $this->parseProductoInput();
        if ($data === null) {
            return;
        }
        $img = (new ProductImageUploader())->resolveForSave(true, null);
        if ($img === null) {
            return;
        }
        $newId = $model->adminCrear(
            $data['nombre'],
            $data['descripcion'],
            $data['precio'],
            $img,
            $data['stock'],
            $data['activo']
        );
        $this->jsonOk(['id' => $newId]);
    }

    protected function onUpdate(int $id): void
    {
        $model = $this->model();
        $row = $model->adminObtener($id);
        if ($row === null) {
            $this->jsonNotFound('Producto no encontrado');
            return;
        }
        $data = $this->parseProductoInput();
        if ($data === null) {
            return;
        }
        $existingImg = isset($row['img']) ? (string) $row['img'] : '';
        $img = (new ProductImageUploader())->resolveForSave(false, $existingImg !== '' ? $existingImg : null);
        if ($img === null) {
            return;
        }
        $model->adminActualizar(
            $id,
            $data['nombre'],
            $data['descripcion'],
            $data['precio'],
            $img,
            $data['stock'],
            $data['activo']
        );
        $this->jsonOk();
    }

    protected function onDelete(int $id): void
    {
        $this->model()->adminBajaLogica($id);
        $this->jsonOk(['msg' => 'Producto desactivado']);
    }

    /** @return array{nombre: string, descripcion: ?string, precio: string, stock: int, activo: int}|null */
    private function parseProductoInput(): ?array
    {
        $nombre = Input::postPlainString('nombre', 120);
        $descRaw = Input::postPlainString('descripcion', 65535);
        $descripcion = $descRaw === '' ? null : $descRaw;
        $precio = Input::postMoneyDecimal('precio');
        $stock = Input::postNonNegativeInt('stock', 0);
        $activo = Input::postBit('activo');
        if (strlen($nombre) < 2 || $precio === null) {
            $this->jsonError('Datos de producto inválidos', 400);
            return null;
        }
        return [
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'precio' => $precio,
            'stock' => $stock,
            'activo' => $activo,
        ];
    }
}

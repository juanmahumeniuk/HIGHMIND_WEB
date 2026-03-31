<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Producto;

final class ProductoController
{
    public function index(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode((new Producto())->getActivos(), JSON_UNESCAPED_UNICODE);
    }
}

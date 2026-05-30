<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller\AbstractController;
use App\Models\Producto;

final class ProductoController extends AbstractController
{
    public function index(): void
    {
        $this->jsonRaw((new Producto())->getActivos());
    }
}

<?php
declare(strict_types=1);

namespace App\Core\Controller;

abstract class AuthenticatedController extends AbstractController
{
    protected function requireAuth(): ?int
    {
        $this->startSession();
        if (!isset($_SESSION['usuario_id'])) {
            $this->jsonError('No autorizado', 401);
            return null;
        }
        return (int) $_SESSION['usuario_id'];
    }
}

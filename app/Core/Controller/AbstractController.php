<?php
declare(strict_types=1);

namespace App\Core\Controller;

use App\Core\Csrf;
use App\Core\Input;
use App\Core\JsonResponse;
use App\Core\Session;

abstract class AbstractController
{
    protected function startSession(): void
    {
        Session::start();
    }

    protected function action(): string
    {
        return (string) ($_POST['action'] ?? $_GET['action'] ?? '');
    }

    protected function method(): string
    {
        return (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    protected function requireMethod(string $expected): bool
    {
        if ($this->method() !== $expected) {
            $this->jsonError('Método no permitido', 405);
            return false;
        }
        return true;
    }

    protected function requirePostCsrf(): bool
    {
        if ($this->method() !== 'POST') {
            $this->jsonError('Método no permitido', 405);
            return false;
        }
        $token = Input::postCsrfToken();
        if ($token === '' || !Csrf::validate($token)) {
            $this->jsonError('Token de seguridad inválido', 403);
            return false;
        }
        return true;
    }

    protected function requireJsonCsrf(array $payload): bool
    {
        $csrf = isset($payload['csrf_token']) && is_string($payload['csrf_token']) ? $payload['csrf_token'] : '';
        if ($csrf === '' || !Csrf::validate($csrf)) {
            $this->jsonError('Token CSRF inválido', 403);
            return false;
        }
        return true;
    }

    /** @return array<string, mixed> */
    protected function jsonBody(): array
    {
        $raw = file_get_contents('php://input');
        if (!is_string($raw) || $raw === '') {
            return [];
        }
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    /** @param array<string, mixed> $data */
    protected function jsonOk(array $data = [], int $code = 200): void
    {
        JsonResponse::send(['ok' => true] + $data, $code);
    }

    protected function jsonError(string $msg, int $code = 400): void
    {
        JsonResponse::send(['ok' => false, 'msg' => $msg], $code);
    }

    protected function jsonNotFound(string $msg = 'No encontrado'): void
    {
        $this->jsonError($msg, 404);
    }

    /** Respuesta JSON cruda (p. ej. catálogo como array). */
    protected function jsonRaw(mixed $data, int $code = 200): void
    {
        JsonResponse::send($data, $code);
    }
}

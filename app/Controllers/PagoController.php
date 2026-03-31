<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Env;
use App\Core\JsonResponse;
use App\Core\Session;
use App\Models\Carrito;

final class PagoController
{
    public function handle(): void
    {
        Session::start();
        if (!isset($_SESSION['usuario_id'])) {
            JsonResponse::send(['ok' => false, 'msg' => 'No autorizado'], 401);
            return;
        }

        $action = $_GET['action'] ?? $_POST['action'] ?? '';
        if ($action === 'config') {
            $this->config();
            return;
        }
        if ($action === 'create') {
            $this->create((int) $_SESSION['usuario_id']);
            return;
        }

        JsonResponse::send(['ok' => false, 'msg' => 'Acción no válida'], 400);
    }

    private function config(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            JsonResponse::send(['ok' => false, 'msg' => 'Método no permitido'], 405);
            return;
        }
        $publicKey = Env::get('MP_PUBLIC_KEY', '') ?? '';
        $accessToken = Env::get('MP_ACCESS_TOKEN', '') ?? '';
        $testMode = str_starts_with($publicKey, 'TEST-') || str_starts_with($accessToken, 'TEST-');
        JsonResponse::send([
            'ok' => $publicKey !== '',
            'public_key' => $publicKey,
            'test_mode' => $testMode,
            'msg' => $publicKey !== '' ? 'OK' : 'Falta configurar MP_PUBLIC_KEY',
        ]);
    }

    private function create(int $usuarioId): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            JsonResponse::send(['ok' => false, 'msg' => 'Método no permitido'], 405);
            return;
        }

        $payload = $this->jsonBody();
        $csrf = isset($payload['csrf_token']) && is_string($payload['csrf_token']) ? $payload['csrf_token'] : '';
        if ($csrf === '' || !Csrf::validate($csrf)) {
            JsonResponse::send(['ok' => false, 'msg' => 'Token CSRF inválido'], 403);
            return;
        }

        $token = isset($payload['token']) && is_string($payload['token']) ? trim($payload['token']) : '';
        $paymentMethodId = isset($payload['payment_method_id']) && is_string($payload['payment_method_id']) ? trim($payload['payment_method_id']) : '';
        $installments = isset($payload['installments']) ? (int) $payload['installments'] : 1;
        $payerEmail = isset($payload['payer_email']) && is_string($payload['payer_email']) ? trim($payload['payer_email']) : '';
        $issuerId = isset($payload['issuer_id']) ? (int) $payload['issuer_id'] : null;

        if ($token === '' || $paymentMethodId === '' || $installments < 1 || !filter_var($payerEmail, FILTER_VALIDATE_EMAIL)) {
            JsonResponse::send(['ok' => false, 'msg' => 'Datos de pago incompletos'], 400);
            return;
        }

        $carrito = (new Carrito())->obtenerConProductos($usuarioId);
        $amount = (float) $carrito['subtotal'];
        $items = (int) $carrito['total_items'];
        if ($amount <= 0 || $items < 1) {
            JsonResponse::send(['ok' => false, 'msg' => 'El carrito está vacío'], 400);
            return;
        }

        $accessToken = Env::get('MP_ACCESS_TOKEN', '') ?? '';
        if ($accessToken === '') {
            JsonResponse::send(['ok' => false, 'msg' => 'Falta configurar MP_ACCESS_TOKEN'], 500);
            return;
        }

        $description = 'Compra HIGHMIND (' . $items . ' item' . ($items === 1 ? '' : 's') . ')';
        $requestBody = [
            'transaction_amount' => round($amount, 2),
            'token' => $token,
            'description' => $description,
            'installments' => $installments,
            'payment_method_id' => $paymentMethodId,
            'payer' => [
                'email' => $payerEmail,
            ],
        ];
        if ($issuerId !== null && $issuerId > 0) {
            $requestBody['issuer_id'] = $issuerId;
        }
        if (isset($payload['identification_type'], $payload['identification_number'])
            && is_string($payload['identification_type'])
            && is_string($payload['identification_number'])
        ) {
            $requestBody['payer']['identification'] = [
                'type' => trim($payload['identification_type']),
                'number' => trim($payload['identification_number']),
            ];
        }

        [$statusCode, $response] = $this->mercadoPagoCreatePayment($accessToken, $requestBody);
        if ($statusCode < 200 || $statusCode >= 300 || !is_array($response)) {
            JsonResponse::send([
                'ok' => false,
                'msg' => 'No se pudo procesar el pago en este momento',
                'provider_status_code' => $statusCode,
                'provider_response' => $response,
            ], 502);
            return;
        }

        $status = (string) ($response['status'] ?? '');
        $detail = (string) ($response['status_detail'] ?? '');
        $paymentId = isset($response['id']) ? (string) $response['id'] : '';

        if ($status === 'approved') {
            (new Carrito())->vaciar($usuarioId);
            JsonResponse::send([
                'ok' => true,
                'status' => 'approved',
                'msg' => 'Pago aprobado',
                'payment_id' => $paymentId,
            ]);
            return;
        }

        if ($status === 'pending' || $status === 'in_process') {
            JsonResponse::send([
                'ok' => true,
                'status' => $status,
                'msg' => 'Pago pendiente. Estamos validando la operación.',
                'payment_id' => $paymentId,
                'status_detail' => $detail,
            ]);
            return;
        }

        JsonResponse::send([
            'ok' => false,
            'status' => $status === '' ? 'rejected' : $status,
            'msg' => 'Pago rechazado. Podés reintentar con otro medio.',
            'payment_id' => $paymentId,
            'status_detail' => $detail,
        ], 402);
    }

    /**
     * @return array{0:int,1:array<string,mixed>|null}
     */
    private function mercadoPagoCreatePayment(string $accessToken, array $body): array
    {
        $baseUrl = Env::get('MP_API_BASE', 'https://api.mercadopago.com') ?? 'https://api.mercadopago.com';
        $url = rtrim($baseUrl, '/') . '/v1/payments';

        $ch = curl_init($url);
        if ($ch === false) {
            return [0, null];
        }

        $json = json_encode($body, JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            return [0, null];
        }

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
            'X-Idempotency-Key: ' . bin2hex(random_bytes(16)),
        ];

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
        ]);

        $raw = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!is_string($raw) || $raw === '') {
            return [$status, null];
        }

        $decoded = json_decode($raw, true);
        return [$status, is_array($decoded) ? $decoded : null];
    }

    /**
     * @return array<string,mixed>
     */
    private function jsonBody(): array
    {
        $raw = file_get_contents('php://input');
        if (!is_string($raw) || $raw === '') {
            return [];
        }
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }
}

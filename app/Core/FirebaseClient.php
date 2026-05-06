<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Verifica ID tokens de Firebase usando la REST API de Google Identity Toolkit.
 * No requiere librerías externas: solo cURL.
 */
final class FirebaseClient
{
    private string $apiKey;
    private string $projectId;

    public function __construct()
    {
        $this->apiKey    = (string) ($_ENV['FIREBASE_API_KEY'] ?? '');
        $this->projectId = (string) ($_ENV['FIREBASE_PROJECT_ID'] ?? '');
    }

    /**
     * Verifica el ID token contra Firebase y devuelve los datos del usuario.
     *
     * @return array{uid: string, email: string, name: string}|null  null si el token es inválido
     */
    public function verifyIdToken(string $idToken): ?array
    {
        $url  = 'https://identitytoolkit.googleapis.com/v1/accounts:lookup?key=' . urlencode($this->apiKey);
        $body = json_encode(['idToken' => $idToken]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 10,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error !== '' || $httpCode !== 200) {
            return null;
        }

        $data = json_decode((string) $response, true);

        $user = $data['users'][0] ?? null;
        if ($user === null) {
            return null;
        }

        return [
            'uid'   => (string) ($user['localId'] ?? ''),
            'email' => (string) ($user['email'] ?? ''),
            'name'  => (string) ($user['displayName'] ?? $user['email'] ?? ''),
        ];
    }
}

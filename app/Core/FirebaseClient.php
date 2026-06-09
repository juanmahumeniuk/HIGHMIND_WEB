<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Cliente Firebase Identity Toolkit vía REST (sin librerías externas).
 */
final class FirebaseClient
{
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = (string) ($_ENV['FIREBASE_API_KEY'] ?? '');
    }

    /**
     * @return array{uid: string, email: string, name: string}|null
     */
    public function verifyIdToken(string $idToken): ?array
    {
        $data = $this->post('accounts:lookup', ['idToken' => $idToken]);
        if ($data === null) {
            return null;
        }

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

    /**
     * Crea un usuario en Firebase Authentication (Email/Password).
     *
     * @return array{uid: string, email: string}|null
     */
    public function signUp(string $email, string $password): ?array
    {
        $data = $this->post('accounts:signUp', [
            'email'             => $email,
            'password'          => $password,
            'returnSecureToken' => true,
        ]);
        if ($data === null) {
            return null;
        }

        $uid = (string) ($data['localId'] ?? '');
        if ($uid === '') {
            return null;
        }

        return [
            'uid'   => $uid,
            'email' => (string) ($data['email'] ?? $email),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>|null
     */
    private function post(string $endpoint, array $payload): ?array
    {
        if ($this->apiKey === '') {
            return null;
        }

        $url  = 'https://identitytoolkit.googleapis.com/v1/' . $endpoint . '?key=' . urlencode($this->apiKey);
        $body = json_encode($payload);

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
        return is_array($data) ? $data : null;
    }
}

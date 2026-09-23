<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class FirebaseCustomTokenService
{
    /**
     * Mint a short-lived Firebase custom token for the authenticated ERP user.
     * The service-account private key never leaves this server.
     */
    public function forUser(int $userId, bool $isAdmin): string
    {
        $credentials = $this->credentials();
        $now = time();

        return JWT::encode([
            'iss' => $credentials['client_email'],
            'sub' => $credentials['client_email'],
            'aud' => 'https://identitytoolkit.googleapis.com/google.identity.identitytoolkit.v1.IdentityToolkit',
            'iat' => $now,
            'exp' => $now + 3300,
            'uid' => 'erp_'.$userId,
            'claims' => [
                'erp_user_id' => $userId,
                'erp_is_admin' => $isAdmin,
            ],
        ], $credentials['private_key'], 'RS256');
    }

    private function credentials(): array
    {
        $path = config('firestore.credentials');
        $resolved = is_string($path) && str_starts_with($path, DIRECTORY_SEPARATOR)
            ? $path
            : base_path((string) $path);

        if (! is_file($resolved) || ! is_readable($resolved)) {
            throw new RuntimeException('File service account Firebase tidak dapat dibaca oleh server.');
        }

        $credentials = json_decode((string) file_get_contents($resolved), true);
        if (! is_array($credentials) || empty($credentials['client_email']) || empty($credentials['private_key'])) {
            throw new RuntimeException('Isi service account Firebase tidak valid.');
        }

        return $credentials;
    }
}

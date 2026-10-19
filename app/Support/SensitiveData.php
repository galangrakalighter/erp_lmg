<?php

namespace App\Support;

use Illuminate\Support\Str;

final class SensitiveData
{
    private const KEYS = [
        'password',
        'password_confirmation',
        'current_password',
        'remember_token',
        'token',
        'access_token',
        'refresh_token',
        'id_token',
        'auth_token',
        'api_key',
        'secret',
        'client_secret',
        'private_key',
        'authorization',
        'authorization_code',
        'code_verifier',
    ];

    public static function isSensitiveKey(string $key): bool
    {
        $normalized = (string) Str::of($key)
            ->snake()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_');

        foreach (self::KEYS as $sensitive) {
            if ($normalized === $sensitive || str_ends_with($normalized, '_'.$sensitive)) {
                return true;
            }
        }

        return false;
    }

    public static function redactKeys(mixed $value, int $depth = 0): mixed
    {
        if ($depth >= 20 || ! is_array($value)) {
            return $value;
        }

        foreach ($value as $key => $child) {
            $value[$key] = is_string($key) && self::isSensitiveKey($key)
                ? '[REDACTED]'
                : self::redactKeys($child, $depth + 1);
        }

        return $value;
    }
}

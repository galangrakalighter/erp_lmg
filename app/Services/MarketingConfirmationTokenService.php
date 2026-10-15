<?php

namespace App\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final class MarketingConfirmationTokenService
{
    public function issue(Authenticatable $user, string $action, string $module, int $objectId, string $payloadHash): string
    {
        $token = Str::random(64);
        Cache::put($this->key($token), ['user_id' => $user->getAuthIdentifier(), 'action' => $action, 'module' => $module, 'object_id' => $objectId, 'payload_hash' => $payloadHash], now()->addMinutes(5));
        return $token;
    }

    public function consume(Authenticatable $user, string $token, string $action, string $module, int $objectId, string $payloadHash): void
    {
        $key = $this->key($token);
        $data = Cache::pull($key);
        abort_unless($data && (string) $data['user_id'] === (string) $user->getAuthIdentifier()
            && $data['action'] === $action && $data['module'] === $module && (int) $data['object_id'] === $objectId
            && hash_equals($data['payload_hash'], $payloadHash), 422, 'Confirmation token tidak valid atau sudah kedaluwarsa.');
    }

    private function key(string $token): string { return 'marketing_confirmation:'.hash('sha256', $token); }
}

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
        $data = Cache::get($key);
        abort_unless($data, 422, 'confirmation_token_invalid');
        $matches = (string) $data['user_id'] === (string) $user->getAuthIdentifier()
            && $data['action'] === $action && $data['module'] === $module && (int) $data['object_id'] === $objectId
            && hash_equals($data['payload_hash'], $payloadHash);
        if (! $matches) { Cache::forget($key); abort(422, 'confirmation_payload_mismatch'); }
        Cache::forget($key);
    }

    private function key(string $token): string { return 'marketing_confirmation:'.hash('sha256', $token); }
}

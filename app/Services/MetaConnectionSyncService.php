<?php

namespace App\Services;

use App\Models\OAuthConnection;
use Illuminate\Support\Facades\Log;

class MetaConnectionSyncService
{
    /**
     * Meta may rotate the app-user token when the same Meta identity is
     * authorised for another client. Keep sibling client connections on the
     * newest credential while preserving each client's selected assets.
     */
    public function refreshSiblingConnections(int $currentClientId, array $connectionData): int
    {
        $providerUserId = $connectionData['provider_user_id'] ?? null;
        $accessToken = $connectionData['access_token'] ?? null;

        if (blank($providerUserId) || blank($accessToken)) {
            return 0;
        }

        $siblings = OAuthConnection::query()
            ->where('provider', 'meta')
            ->where('provider_user_id', $providerUserId)
            ->where('client_id', '!=', $currentClientId)
            ->get();

        foreach ($siblings as $sibling) {
            // Aset dan selected_assets sengaja tidak disentuh. Page access
            // token untuk data organik melekat pada Page masing-masing client;
            // menggantinya dengan daftar aset hasil login client lain membuat
            // Page lama tidak ditemukan dan metrik organik terlihat nol.
            $sibling->update([
                'access_token' => $accessToken,
                'token_type' => $connectionData['token_type'] ?? $sibling->token_type,
                'expires_at' => $connectionData['expires_at'] ?? null,
                'provider_user_name' => $connectionData['provider_user_name'] ?? $sibling->provider_user_name,
                'last_synced_at' => $connectionData['last_synced_at'] ?? now(),
            ]);
        }

        if ($siblings->isNotEmpty()) {
            Log::info('Kredensial Meta client terkait diperbarui', [
                'source_client_id' => $currentClientId,
                'updated_client_ids' => $siblings->pluck('client_id')->all(),
                'provider_user_id' => $providerUserId,
            ]);
        }

        return $siblings->count();
    }
}

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

        $updatedClientIds = collect();
        foreach ($siblings as $sibling) {
            $siblingAssets = $sibling->assets ?? [];
            $selection = $sibling->selected_assets ?? [];
            $freshPages = collect(data_get($connectionData, 'assets.pages', []))->keyBy(
                fn ($page) => (string) ($page['id'] ?? '')
            );
            $selectedPageId = (string) ($selection['page_id'] ?? '');
            $selectedInstagramId = (string) ($selection['instagram_account_id'] ?? '');
            $freshSelectedPage = $selectedPageId !== '' ? $freshPages->get($selectedPageId) : null;
            $freshInstagramPage = $freshPages->first(
                fn ($page) => (string) data_get($page, 'instagram_business_account.id') === $selectedInstagramId
            );

            // Token baru hanya aman dipakai bila respons OAuth baru memang
            // masih mencakup Page/Instagram yang dipilih client lama.
            if (! $freshSelectedPage && ! $freshInstagramPage) {
                Log::warning('Kredensial Meta client terkait tidak diperbarui karena aset tidak tercakup', [
                    'source_client_id' => $currentClientId,
                    'skipped_client_id' => $sibling->client_id,
                    'selected_page_id' => $selectedPageId ?: null,
                    'selected_instagram_id' => $selectedInstagramId ?: null,
                ]);
                continue;
            }

            $siblingAssets['pages'] = collect($siblingAssets['pages'] ?? [])->map(function ($oldPage) use ($freshPages) {
                $freshPage = $freshPages->get((string) ($oldPage['id'] ?? ''));
                return $freshPage ? array_replace_recursive($oldPage, $freshPage) : $oldPage;
            })->values()->all();

            $freshAdAccounts = collect(data_get($connectionData, 'assets.ad_accounts', []))->keyBy(
                fn ($account) => (string) ($account['id'] ?? '')
            );
            $siblingAssets['ad_accounts'] = collect($siblingAssets['ad_accounts'] ?? [])->map(function ($oldAccount) use ($freshAdAccounts) {
                $freshAccount = $freshAdAccounts->get((string) ($oldAccount['id'] ?? ''));
                return $freshAccount ? array_replace_recursive($oldAccount, $freshAccount) : $oldAccount;
            })->values()->all();

            $sibling->update([
                'access_token' => $accessToken,
                'token_type' => $connectionData['token_type'] ?? $sibling->token_type,
                'expires_at' => $connectionData['expires_at'] ?? null,
                'provider_user_name' => $connectionData['provider_user_name'] ?? $sibling->provider_user_name,
                'scopes' => $connectionData['scopes'] ?? $sibling->scopes,
                'assets' => $siblingAssets,
                'last_synced_at' => $connectionData['last_synced_at'] ?? now(),
            ]);
            $updatedClientIds->push($sibling->client_id);
        }

        if ($updatedClientIds->isNotEmpty()) {
            Log::info('Kredensial Meta client terkait diperbarui', [
                'source_client_id' => $currentClientId,
                'updated_client_ids' => $updatedClientIds->all(),
                'provider_user_id' => $providerUserId,
            ]);
        }

        return $updatedClientIds->count();
    }
}

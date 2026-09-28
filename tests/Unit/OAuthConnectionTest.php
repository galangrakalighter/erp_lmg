<?php

namespace Tests\Unit;

use App\Models\OAuthConnection;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class OAuthConnectionTest extends TestCase
{
    public function test_model_uses_the_oauth_connections_table(): void
    {
        $this->assertSame('oauth_connections', (new OAuthConnection())->getTable());
    }

    public function test_invalid_encrypted_oauth_data_does_not_break_the_page(): void
    {
        $connection = new OAuthConnection();
        $connection->setRawAttributes([
            'access_token' => 'token-from-a-different-app-key',
            'assets' => 'invalid-encrypted-assets',
            'selected_assets' => 'invalid-encrypted-selection',
            'scopes' => 'invalid-encrypted-scopes',
        ]);

        $this->assertNull($connection->access_token);
        $this->assertSame([], $connection->assets);
        $this->assertSame([], $connection->selected_assets);
        $this->assertSame([], $connection->scopes);
    }

    public function test_oauth_fields_are_still_encrypted_at_rest(): void
    {
        $connection = new OAuthConnection();
        $connection->access_token = 'secret-token';
        $connection->assets = ['pages' => [['id' => '123']]];

        $this->assertNotSame('secret-token', $connection->getAttributes()['access_token']);
        $this->assertSame('secret-token', Crypt::decryptString($connection->getAttributes()['access_token']));
        $this->assertSame(['pages' => [['id' => '123']]], $connection->assets);
    }
}

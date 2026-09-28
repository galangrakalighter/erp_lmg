<?php

namespace Tests\Unit;

use App\Models\OAuthConnection;
use PHPUnit\Framework\TestCase;

class OAuthConnectionTest extends TestCase
{
    public function test_model_uses_the_oauth_connections_table(): void
    {
        $this->assertSame('oauth_connections', (new OAuthConnection())->getTable());
    }
}

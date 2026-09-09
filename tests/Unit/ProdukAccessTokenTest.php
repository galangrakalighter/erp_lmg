<?php

namespace Tests\Unit;

use App\Http\Controllers\ProdukController;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class ProdukAccessTokenTest extends TestCase
{
    public function test_empty_token_input_preserves_existing_token_for_the_platform(): void
    {
        $result = $this->mergeTokens(
            [
                'platform' => ['Instagram', 'Facebook'],
                'access' => ['instagram-token', 'facebook-token'],
            ],
            ['Instagram', 'Facebook'],
            ['', 'new-facebook-token']
        );

        $this->assertSame(
            [
                'platform' => ['Instagram', 'Facebook'],
                'access' => ['instagram-token', 'new-facebook-token'],
            ],
            $result
        );
    }

    public function test_missing_platform_input_does_not_remove_existing_tokens(): void
    {
        $current = [
            'platform' => ['Instagram'],
            'access' => ['instagram-token'],
        ];

        $this->assertSame($current, $this->mergeTokens($current, [], []));
    }

    private function mergeTokens(?array $current, array $platforms, array $tokens): ?array
    {
        $method = new ReflectionMethod(ProdukController::class, 'mergeAccessTokens');

        return $method->invoke(new ProdukController(), $current, $platforms, $tokens);
    }
}

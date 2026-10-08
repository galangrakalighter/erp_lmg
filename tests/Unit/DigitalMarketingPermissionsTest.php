<?php

namespace Tests\Unit;

use App\Support\DigitalMarketingPermissions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DigitalMarketingPermissionsTest extends TestCase
{
    public static function contentPlanPermissions(): array
    {
        return [
            ['Busines Unit Head', 'organic', true],
            ['Digital Marketing Manager', 'paid', true],
            ['Sosial Media Spesialis', 'organic', true],
            ['Sosial Media Spesialis', 'paid', false],
            ['Advertising', 'paid', true],
            ['Advertising', 'organic', false],
            ['Digital Marketing', 'organic', false],
            ['Digital Marketing', 'paid', false],
        ];
    }

    #[DataProvider('contentPlanPermissions')]
    public function test_content_plan_permissions_follow_role_and_source(
        string $role,
        string $source,
        bool $expected,
    ): void {
        $this->assertSame(
            $expected,
            DigitalMarketingPermissions::canManageContentPlan($role, $source),
        );
    }

    public function test_mirroring_is_limited_to_crud_scopes(): void
    {
        $this->assertSame(
            ['market-research', 'branding'],
            DigitalMarketingPermissions::mirrorScopes('Digital Marketing'),
        );
        $this->assertSame(
            ['social-media-research', 'content-plan-organic'],
            DigitalMarketingPermissions::mirrorScopes('Sosial Media Spesialis'),
        );
        $this->assertSame(
            ['social-media-research', 'advertising-research', 'content-plan-paid'],
            DigitalMarketingPermissions::mirrorScopes('Advertising'),
        );
    }
}

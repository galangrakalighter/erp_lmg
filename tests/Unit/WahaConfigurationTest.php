<?php

namespace Tests\Unit;

use Tests\TestCase;

class WahaConfigurationTest extends TestCase
{
    public function test_waha_key_is_loaded_from_environment_configuration(): void
    {
        $services = file_get_contents(config_path('services.php'));
        $controller = file_get_contents(app_path('Http/Controllers/CRMController.php'));

        $this->assertStringContainsString("env('WAHA_API_KEY')", $services);
        $this->assertStringContainsString("config(\n                        'services.waha.api_key'", $controller);
        $this->assertStringNotContainsString('private const WAHA_API_KEY', $controller);
    }
}

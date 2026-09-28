<?php

namespace Tests\Feature;

use Tests\TestCase;

class OAuthPopupResultViewTest extends TestCase
{
    public function test_success_popup_result_can_be_rendered(): void
    {
        $html = view('oauth.popup-result', [
            'success' => true,
            'message' => 'Akun Meta berhasil dihubungkan.',
            'connection' => ['provider_user_name' => 'Tester'],
        ])->render();

        $this->assertStringContainsString('meta-oauth-success', $html);
        $this->assertStringContainsString('Akun Meta berhasil dihubungkan.', $html);
    }

    public function test_error_popup_result_can_be_rendered_without_connection(): void
    {
        $html = view('oauth.popup-result', [
            'success' => false,
            'message' => 'Koneksi dibatalkan.',
        ])->render();

        $this->assertStringContainsString('meta-oauth-error', $html);
        $this->assertStringContainsString('Koneksi dibatalkan.', $html);
    }
}

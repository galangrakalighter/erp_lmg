<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class FriendlyErrorPageTest extends TestCase
{
    public function test_web_exception_shows_friendly_page_without_technical_details(): void
    {
        Route::get('/test-friendly-error', function () {
            throw new \RuntimeException('rahasia stack trace internal');
        });

        $this->get('/test-friendly-error')
            ->assertStatus(500)
            ->assertSee('Terjadi kendala pada sistem')
            ->assertDontSee('rahasia stack trace internal')
            ->assertDontSee('RuntimeException');
    }

    public function test_missing_web_page_uses_friendly_not_found_page(): void
    {
        $this->get('/halaman-yang-tidak-ada-untuk-pengujian')
            ->assertStatus(404)
            ->assertSee('Halaman tidak ditemukan')
            ->assertDontSee('NotFoundHttpException');
    }
}

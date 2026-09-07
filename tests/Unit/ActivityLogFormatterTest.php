<?php

namespace Tests\Unit;

use App\Services\ActivityLogFormatter;
use PHPUnit\Framework\TestCase;

class ActivityLogFormatterTest extends TestCase
{
    public function test_non_crud_activity_has_no_detail(): void
    {
        $formatter = new ActivityLogFormatter;

        $this->assertNull($formatter->format([
            'action' => 'VISIT',
            'new_data' => ['path' => '/home', 'method' => 'GET'],
        ]));
    }

    public function test_update_is_formatted_as_human_readable_change(): void
    {
        $formatter = new ActivityLogFormatter;
        $detail = $formatter->format([
            'action' => 'UPDATE',
            'target_name' => 'Client Contoh',
            'old_data' => ['status' => 'Prospek', 'website' => 'https://old.example.com'],
            'new_data' => ['status' => 'Aktif', 'website' => 'https://new.example.com'],
        ]);

        $this->assertSame('Mengubah data Client Contoh:', $detail['summary']);
        $this->assertSame('Prospek', $detail['items'][0]['old']);
        $this->assertSame('Aktif', $detail['items'][0]['new']);
        $this->assertSame('https://new.example.com', $detail['items'][1]['new_url']);
    }

    public function test_create_is_formatted_with_attributes_and_clickable_link(): void
    {
        $formatter = new ActivityLogFormatter;
        $detail = $formatter->format([
            'action' => 'CREATE',
            'target_name' => 'Dokumen Baru',
            'new_data' => [
                'judul' => 'Proposal',
                'link' => 'https://example.com/proposal',
                'created_at' => '2026-09-07 10:00:00',
            ],
        ]);

        $this->assertSame('Menambahkan data Dokumen Baru beserta atributnya:', $detail['summary']);
        $this->assertCount(2, $detail['items']);
        $this->assertSame('https://example.com/proposal', $detail['items'][1]['url']);
    }
}

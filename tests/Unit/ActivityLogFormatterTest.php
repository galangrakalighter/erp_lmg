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

    public function test_master_folder_file_mentions_folder_file_and_link(): void
    {
        $formatter = new ActivityLogFormatter;
        $detail = $formatter->format([
            'action' => 'CREATE',
            'target_name' => 'Proposal.pdf',
            'context' => [
                'kind' => 'master_folder_file',
                'folder_name' => 'Dokumen Legal',
                'file_name' => 'Proposal.pdf',
                'folder_link' => 'https://erp.example.com/master-folder/10',
            ],
            'new_data' => [
                'master_folder_id' => 10,
                'original_name' => 'Proposal.pdf',
                'size' => 2048,
            ],
        ]);

        $this->assertSame(
            'Menambahkan file Proposal.pdf di folder Dokumen Legal:',
            $detail['summary'],
        );
        $this->assertSame('https://erp.example.com/master-folder/10', $detail['items'][0]['url']);
        $this->assertSame('2 KB', $detail['items'][2]['value']);
    }

    public function test_task_uses_task_name_and_human_readable_enums(): void
    {
        $formatter = new ActivityLogFormatter;
        $detail = $formatter->format([
            'action' => 'CREATE',
            'module' => 'task',
            'target_type' => 'task',
            'target_name' => 'Task #1700',
            'new_data' => [
                'nama_task' => 'Pikirkan strategi',
                'priority' => 2,
                'status' => 3,
            ],
        ]);

        $this->assertSame(
            'Menambahkan data Pikirkan strategi beserta atributnya:',
            $detail['summary'],
        );
        $this->assertSame('Sedang', $detail['items'][1]['value']);
        $this->assertSame('Tunggu Approval', $detail['items'][2]['value']);
    }
}

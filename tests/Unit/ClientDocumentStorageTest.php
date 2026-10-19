<?php

namespace Tests\Unit;

use Tests\TestCase;

class ClientDocumentStorageTest extends TestCase
{
    public function test_client_document_controllers_do_not_use_public_storage(): void
    {
        $pdfController = file_get_contents(app_path('Http/Controllers/PDFController.php'));
        $productController = file_get_contents(app_path('Http/Controllers/ProdukController.php'));

        $this->assertStringNotContainsString("Storage::disk('public')", $pdfController);
        $this->assertStringNotContainsString("Storage::disk('public')->put(\$path, \$pdfContent)", $productController);
        $this->assertStringContainsString("CLIENT_DOCUMENT_DISK = 'local'", $pdfController);
    }

    public function test_local_disk_is_outside_the_public_storage_directory(): void
    {
        $this->assertSame(storage_path('app/private'), config('filesystems.disks.local.root'));
        $this->assertNotSame(
            config('filesystems.disks.public.root'),
            config('filesystems.disks.local.root'),
        );
    }
}

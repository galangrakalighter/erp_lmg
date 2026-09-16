<?php

namespace Tests\Unit;

use App\Models\MasterFolder;
use App\Models\MasterFolderFile;
use App\Services\ActivityPageDescription;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use PHPUnit\Framework\TestCase;

class ActivityPageDescriptionTest extends TestCase
{
    public function test_master_folder_index_has_human_description(): void
    {
        $request = $this->requestWithRoute('/master-folder', 'master-folder.index');

        $this->assertSame(
            'Masuk ke halaman Master Folder',
            (new ActivityPageDescription)->describe($request),
        );
    }

    public function test_master_folder_detail_uses_folder_name_instead_of_id(): void
    {
        $folder = (new MasterFolder)->forceFill(['id' => 10, 'name' => 'Dokumen Legal']);
        $request = $this->requestWithRoute(
            '/master-folder/10',
            'master-folder.show',
            ['folder' => $folder],
        );

        $this->assertSame(
            'Masuk ke folder Dokumen Legal',
            (new ActivityPageDescription)->describe($request),
        );
    }

    public function test_file_view_mentions_file_and_folder_names(): void
    {
        $folder = (new MasterFolder)->forceFill(['id' => 10, 'name' => 'Dokumen Legal']);
        $file = (new MasterFolderFile)->forceFill(['id' => 20, 'original_name' => 'Kontrak.pdf']);
        $request = $this->requestWithRoute(
            '/master-folder/10/files/20/view',
            'master-folder.files.view',
            ['folder' => $folder, 'file' => $file],
        );

        $this->assertSame(
            'Membuka file Kontrak.pdf dari folder Dokumen Legal',
            (new ActivityPageDescription)->describe($request),
        );
    }

    public function test_stored_route_suffix_is_replaced_with_human_page_name(): void
    {
        $description = (new ActivityPageDescription)->describeStoredLog([
            'action' => 'VISIT',
            'description' => 'Mengunjungi halaman hc_daily.index',
            'new_data' => ['route_name' => 'hc_daily.index'],
        ]);

        $this->assertSame('Masuk ke halaman Daily Work', $description);

        $activityLog = (new ActivityPageDescription)->describeStoredLog([
            'action' => 'VISIT',
            'description' => 'Mengunjungi halaman activity-logs.index',
            'new_data' => ['route_name' => 'activity-logs.index'],
        ]);

        $this->assertSame('Masuk ke halaman Activity Log', $activityLog);
    }

    private function requestWithRoute(string $uri, string $name, array $parameters = []): Request
    {
        $request = Request::create($uri);
        $route = new Route(['GET'], ltrim($uri, '/'), fn () => null);
        $route->name($name);
        $route->bind($request);

        foreach ($parameters as $key => $value) {
            $route->setParameter($key, $value);
        }

        $request->setRouteResolver(fn () => $route);

        return $request;
    }
}

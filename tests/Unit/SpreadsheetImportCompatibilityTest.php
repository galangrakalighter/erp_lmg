<?php

namespace Tests\Unit;

use App\Imports\AffiliatorImport;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class SpreadsheetImportCompatibilityTest extends TestCase
{
    public function test_affiliator_import_is_compatible_with_laravel_excel_four(): void
    {
        $model = (new AffiliatorImport)->model([
            'username_tiktok' => 'creator.test',
            'followers_tiktok' => '1.250',
            'er' => '2,5%',
        ]);

        $this->assertSame('creator.test', $model->user_tt);
        $this->assertSame(1250, $model->fol_tt);
    }

    public function test_current_packages_can_write_and_read_xlsx(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'erp-xlsx-').'.xlsx';
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray([
            ['username_tiktok', 'followers_tiktok'],
            ['creator.test', 1250],
        ]);

        try {
            (new Xlsx($spreadsheet))->save($path);

            $rows = Excel::toArray(new class implements ToArray {
                public function array(array $array): void {}
            }, $path);

            $this->assertSame('username_tiktok', $rows[0][0][0]);
            $this->assertSame('creator.test', $rows[0][1][0]);
            $this->assertSame(1250, $rows[0][1][1]);
        } finally {
            $spreadsheet->disconnectWorksheets();
            @unlink($path);
        }
    }
}

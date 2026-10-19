<?php

namespace App\Imports;

use App\Models\Social_Creator;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
class AffiliatorImport implements ToModel, WithHeadingRow, WithChunkReading
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    public function headingRow(): int
    {
        return 4;
    }

    public function model(array $row): Model|array|null
    {
        $rawCategory = $row['kategory'] ?? null;
        $categoryArray = !empty($rawCategory) ? array_map('trim', explode(',', str_replace("\n", ",", $rawCategory))) : [];
        
        // Helper membersihkan angka ribuan
        $cleanNumber = function($value) {
            if ($value === null || $value === '') return 0;
            return (int) preg_replace('/[^0-9]/', '', $value);
        };

        // Helper khusus Float / ER (Bisa NULL)
        $cleanFloat = function($value) {
            if ($value === null || $value === '') return null;
            $value = str_replace(['%', ' '], '', $value);
            $value = str_replace(',', '.', $value);
            return (float) $value;
        };

        return new Social_Creator([
            'user_tt'      => $row['username_tiktok'] ?? null,
            'url_tt'       => $row['tiktok'] ?? null, // Sesuaikan dengan text di kolom B4
            'fol_tt'       => $cleanNumber($row['followers_tiktok'] ?? 0),
            
            'user_shopee'  => $row['username_shopee'] ?? null,
            'url_shopee'   => $row['shopee'] ?? null,
            'fol_shopee'   => $cleanNumber($row['followers_shopee'] ?? 0), // Laravel Excel otomatis menambah _2 jika nama kolom duplikat
            
            'user_ig'      => $row['username_instagram'] ?? null,
            'url_ig'       => $row['instagram'] ?? null,
            'fol_ig'       => $cleanNumber($row['followers_instagram'] ?? 0),
            
            'kategory'     => $categoryArray,
            'er'           => $cleanFloat($row['er'] ?? null), 
            
            'gmv_tt'       => $cleanNumber($row['gmv_tiktok'] ?? 0), 
            'gmv_shopee'   => $cleanNumber($row['gmv_shopee'] ?? 0),
            'provinsi'     => $row['provinsi'] ?? null,
            'domisili'     => $row['domisili'] ?? null,
            'alamat'       => $row['alamat'] ?? null,
            'gender'       => $row['gender'] ?? null,
            'contact'      => $row['contact'] ?? null,
            'detail'       => $row['detail'] ?? null,
            'reels'        => $row['reels'] ?? null,
            'vt'           => $row['vt'] ?? null,
            'mirroring'    => $row['mirroring'] ?? null,
            'kerkun'       => $row['kerkun'] ?? null,
            'story'        => $row['story'] ?? null,
        ]);
    }

    public function chunkSize(): int
    {
        return 100; 
    }
}

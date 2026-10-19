<?php

namespace App\Services;

class PlatformErrorPresenter
{
    public function message(string $platform, ?string $technicalMessage = null, ?int $code = null): string
    {
        $message = strtolower((string) $technicalMessage);

        if ($code === 190 || str_contains($message, 'access token') || str_contains($message, 'session') || str_contains($message, 'oauth')) {
            return "Sesi {$platform} sudah berakhir atau akses akun berubah. Silakan hubungkan ulang akun dari menu Edit Client, lalu coba lagi.";
        }

        if (in_array($code, [10, 200, 294], true) || str_contains($message, 'permission') || str_contains($message, 'scope')) {
            return "Izin {$platform} belum lengkap. Hubungkan ulang akun dari menu Edit Client dan izinkan seluruh akses yang dibutuhkan.";
        }

        if ($code === 100 || str_contains($message, 'unsupported') || str_contains($message, 'not found')) {
            return "Akun atau halaman {$platform} yang dipilih sudah tidak tersedia. Buka Edit Client, lalu pilih kembali akun yang benar.";
        }

        if (in_array($code, [4, 17, 32, 613], true) || str_contains($message, 'limit')) {
            return "{$platform} sedang membatasi permintaan data. Tunggu beberapa menit, lalu muat ulang halaman.";
        }

        return "Data {$platform} belum dapat dimuat. Periksa koneksi akun di menu Edit Client, lalu hubungkan ulang jika masalah tetap muncul.";
    }

    public function network(string $platform): string
    {
        return "Layanan {$platform} sedang sulit dihubungi. Tunggu beberapa saat, lalu muat ulang halaman.";
    }
}

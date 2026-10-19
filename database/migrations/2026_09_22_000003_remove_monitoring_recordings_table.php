<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('monitoring_recordings');
    }

    public function down(): void
    {
        // Live monitoring tidak menyimpan rekaman sehingga tabel tidak dibuat ulang.
    }
};

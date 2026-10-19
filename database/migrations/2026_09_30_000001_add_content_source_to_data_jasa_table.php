<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_jasa', function (Blueprint $table) {
            $table->string('content_source', 16)->default('organic')->after('platform');
            $table->index(['produk_client_id', 'platform', 'content_source'], 'data_jasa_content_source_index');
        });
    }

    public function down(): void
    {
        Schema::table('data_jasa', function (Blueprint $table) {
            $table->dropIndex('data_jasa_content_source_index');
            $table->dropColumn('content_source');
        });
    }
};

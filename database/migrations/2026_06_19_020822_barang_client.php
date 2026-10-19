<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('barang_client', function (Blueprint $table) {
            $table->id();
            $table->string('project');
            $table->json('barang');
            $table->json('shipping');
            $table->integer('total_harga_produk');
            $table->integer('total_shipping_fee');
            $table->integer('ongkir');
            $table->integer('biaya_layanan_pembeli');
            $table->integer('biaya_penanganan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_client');
    }
};

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
        Schema::create('social_commerce_creator', function (Blueprint $table) {
            $table->id();
            $table->string('user_tt')->nullable();
            $table->text('url_tt')->nullable();
            $table->integer('fol_tt')->nullable();
            $table->string('user_shopee')->nullable();
            $table->text('url_shopee')->nullable();
            $table->integer('fol_shopee')->nullable();
            $table->string('user_ig')->nullable();
            $table->text('url_ig')->nullable();
            $table->integer('fol_ig')->nullable();
            $table->string('category');
            $table->float('er');
            $table->integer('gmv_tt')->nullable();
            $table->integer('gmv_shopee')->nullable();
            $table->string('provinsi');
            $table->text('alamat');
            $table->string('gender');
            $table->string('contact');
            $table->text('detail');
            $table->string('vt');
            $table->string('mirroring');
            $table->string('kerkun');
            $table->string('story');
            $table->string('domisili');
            $table->string('reels');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_commerce_creator');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oauth_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('produk_client')->cascadeOnDelete();
            $table->foreignId('connected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('provider', 32);
            $table->text('access_token');
            $table->string('token_type', 32)->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('provider_user_id')->nullable();
            $table->string('provider_user_name')->nullable();
            $table->text('scopes')->nullable();
            $table->longText('assets')->nullable();
            $table->text('selected_assets')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['client_id', 'provider']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oauth_connections');
    }
};

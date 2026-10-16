<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private array $tables = ['analize_4p', 'analize_swot', 'segmentasi', 'target_audience', 'positioning'];
    public function up(): void { foreach ($this->tables as $name) Schema::table($name, function (Blueprint $t): void { $t->timestamp('archived_at')->nullable()->index(); $t->unsignedBigInteger('archived_by')->nullable(); }); }
    public function down(): void { foreach ($this->tables as $name) Schema::table($name, fn (Blueprint $t) => $t->dropColumn(['archived_at','archived_by'])); }
};

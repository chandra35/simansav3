<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('moodle_sync_runs', function (Blueprint $table) {
            $table->unsignedInteger('total_items')->default(0)->after('summary');
            $table->unsignedInteger('processed_items')->default(0)->after('total_items');
            $table->string('current_stage', 80)->nullable()->after('processed_items');
        });
    }

    public function down(): void
    {
        Schema::table('moodle_sync_runs', function (Blueprint $table) {
            $table->dropColumn(['total_items', 'processed_items', 'current_stage']);
        });
    }
};

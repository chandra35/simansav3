<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('moodle_check_results', function (Blueprint $table) {
            $table->string('resolution', 30)->nullable()->after('status');
            $table->text('resolution_note')->nullable()->after('resolution');
            $table->foreignUuid('verified_by')->nullable()->after('resolution_note')->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable()->after('verified_by');
            $table->index(['resolution', 'verified_at']);
        });
    }

    public function down(): void
    {
        Schema::table('moodle_check_results', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropIndex(['resolution', 'verified_at']);
            $table->dropColumn(['resolution', 'resolution_note', 'verified_by', 'verified_at']);
        });
    }
};

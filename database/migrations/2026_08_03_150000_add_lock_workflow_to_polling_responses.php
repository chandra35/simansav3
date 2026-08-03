<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('polling_responses', function (Blueprint $table) {
            $table->dateTime('locked_at')->nullable()->after('submitted_at');
            $table->dateTime('unlock_requested_at')->nullable()->after('locked_at')->index();
            $table->dateTime('unlocked_at')->nullable()->after('unlock_requested_at');
            $table->foreignUuid('unlocked_by')->nullable()->after('unlocked_at')->constrained('users')->nullOnDelete();
        });

        DB::table('polling_responses')->whereNull('locked_at')->update(['locked_at' => DB::raw('submitted_at')]);
    }

    public function down(): void
    {
        Schema::table('polling_responses', function (Blueprint $table) {
            $table->dropForeign(['unlocked_by']);
            $table->dropIndex(['unlock_requested_at']);
            $table->dropColumn(['locked_at', 'unlock_requested_at', 'unlocked_at', 'unlocked_by']);
        });
    }
};

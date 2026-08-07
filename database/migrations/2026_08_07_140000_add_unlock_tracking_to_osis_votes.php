<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('osis_ballots', function (Blueprint $table) {
            $table->foreignUuid('voter_id')->nullable()->after('election_id')->constrained('osis_voters')->cascadeOnDelete()->unique();
        });
        Schema::table('osis_voters', function (Blueprint $table) {
            $table->timestamp('unlocked_at')->nullable()->after('voted_at');
            $table->foreignUuid('unlocked_by')->nullable()->after('unlocked_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('osis_ballots', function (Blueprint $table) { $table->dropConstrainedForeignId('voter_id'); });
        Schema::table('osis_voters', function (Blueprint $table) { $table->dropConstrainedForeignId('unlocked_by'); $table->dropColumn('unlocked_at'); });
    }
};

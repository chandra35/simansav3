<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('osis_elections', function (Blueprint $table) {
            $table->boolean('include_gtk')->default(false)->after('eligible_levels');
        });

        Schema::table('osis_voters', function (Blueprint $table) {
            $table->foreignUuid('user_id')->nullable()->after('election_id')->constrained('users')->cascadeOnDelete();
            $table->string('participant_type', 20)->default('student')->after('siswa_id')->index();
            $table->uuid('siswa_id')->nullable()->change();
            $table->unique(['election_id', 'user_id'], 'osis_voters_election_user_unique');
        });

        DB::table('osis_voters')
            ->join('siswa', 'siswa.id', '=', 'osis_voters.siswa_id')
            ->whereNull('osis_voters.user_id')
            ->update(['osis_voters.user_id' => DB::raw('siswa.user_id')]);
    }

    public function down(): void
    {
        DB::table('osis_voters')->where('participant_type', 'gtk')->delete();
        Schema::table('osis_voters', function (Blueprint $table) {
            $table->dropUnique('osis_voters_election_user_unique');
            $table->dropIndex(['participant_type']);
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn('participant_type');
            $table->uuid('siswa_id')->nullable(false)->change();
        });
        Schema::table('osis_elections', fn (Blueprint $table) => $table->dropColumn('include_gtk'));
    }
};

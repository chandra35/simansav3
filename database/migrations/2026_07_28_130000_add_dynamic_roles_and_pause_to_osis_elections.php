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
            $table->json('candidate_roles')->nullable()->after('instructions');
            $table->timestamp('paused_at')->nullable()->after('published_at');
        });

        Schema::table('osis_packages', function (Blueprint $table) {
            $table->foreignUuid('vice_chairman_id')->nullable()->after('chairman_id')
                ->constrained('siswa')->restrictOnDelete();
            $table->uuid('chairman_id')->nullable()->change();
            $table->uuid('secretary_id')->nullable()->change();
            $table->uuid('treasurer_id')->nullable()->change();
        });

        DB::table('osis_elections')
            ->whereNull('candidate_roles')
            ->update(['candidate_roles' => json_encode(['chairman', 'secretary', 'treasurer'])]);
    }

    public function down(): void
    {
        Schema::table('osis_packages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vice_chairman_id');
        });

        DB::table('osis_packages')->whereNull('secretary_id')->update([
            'secretary_id' => DB::raw('chairman_id'),
        ]);
        DB::table('osis_packages')->whereNull('treasurer_id')->update([
            'treasurer_id' => DB::raw('chairman_id'),
        ]);

        Schema::table('osis_packages', function (Blueprint $table) {
            $table->uuid('chairman_id')->nullable(false)->change();
            $table->uuid('secretary_id')->nullable(false)->change();
            $table->uuid('treasurer_id')->nullable(false)->change();
        });

        Schema::table('osis_elections', function (Blueprint $table) {
            $table->dropColumn(['candidate_roles', 'paused_at']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check and drop FK if exists
        $fks = \Illuminate\Support\Facades\DB::select(
            "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME = 'hotspot_users'
             AND CONSTRAINT_TYPE = 'FOREIGN KEY'
             AND CONSTRAINT_NAME = 'hotspot_users_user_id_foreign'"
        );

        if (!empty($fks)) {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE hotspot_users DROP FOREIGN KEY hotspot_users_user_id_foreign');
        }

        Schema::table('hotspot_users', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });

        Schema::table('hotspot_users', function (Blueprint $table) {
            $table->char('user_id', 36)->nullable()->after('id');
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('hotspot_users', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('hotspot_users', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }
};

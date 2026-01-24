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
        Schema::table('custom_menus', function (Blueprint $table) {
            $table->boolean('has_personal_data')->default(false)->after('konten');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_menus', function (Blueprint $table) {
            $table->dropColumn('has_personal_data');
        });
    }
};

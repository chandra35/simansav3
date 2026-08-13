<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mutasi_gtk', function (Blueprint $table) {
            $table->boolean('status_sebelumnya')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('mutasi_gtk', function (Blueprint $table) {
            $table->boolean('status_sebelumnya')->default(false)->nullable(false)->change();
        });
    }
};

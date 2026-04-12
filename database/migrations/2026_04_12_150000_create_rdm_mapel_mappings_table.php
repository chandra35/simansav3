<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rdm_mapel_mappings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('rdm_mapel_id');
            $table->string('rdm_mapel_nama', 255);
            $table->uuid('mata_pelajaran_id');
            $table->uuid('mapped_by')->nullable();
            $table->timestamps();

            $table->unique('rdm_mapel_id');
            $table->foreign('mata_pelajaran_id')->references('id')->on('mata_pelajaran')->cascadeOnDelete();
            $table->foreign('mapped_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rdm_mapel_mappings');
    }
};

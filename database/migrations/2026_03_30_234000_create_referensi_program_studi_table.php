<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referensi_program_studi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('referensi_perguruan_tinggi_id')
                ->constrained('referensi_perguruan_tinggi')
                ->cascadeOnDelete();
            $table->string('nama');
            $table->string('jenjang', 50)->nullable();
            $table->string('fakultas')->nullable();
            $table->string('sumber_referensi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique([
                'referensi_perguruan_tinggi_id',
                'nama',
                'jenjang',
            ], 'ref_program_studi_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referensi_program_studi');
    }
};

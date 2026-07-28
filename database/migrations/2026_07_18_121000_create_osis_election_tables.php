<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('osis_elections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tahun_pelajaran_id')->constrained('tahun_pelajaran')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('theme')->nullable();
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->json('eligible_levels')->nullable();
            $table->string('candidate_voting_policy', 30)->default('except_own');
            $table->string('status', 20)->default('draft')->index();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('result_published_at')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['tahun_pelajaran_id', 'status']);
        });

        Schema::create('osis_packages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('election_id')->constrained('osis_elections')->cascadeOnDelete();
            $table->unsignedTinyInteger('number');
            $table->string('name')->nullable();
            $table->string('slogan')->nullable();
            $table->text('vision');
            $table->text('mission');
            $table->text('programs')->nullable();
            $table->text('message')->nullable();
            $table->foreignUuid('chairman_id')->constrained('siswa')->restrictOnDelete();
            $table->foreignUuid('secretary_id')->constrained('siswa')->restrictOnDelete();
            $table->foreignUuid('treasurer_id')->constrained('siswa')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['election_id', 'number']);
        });

        Schema::create('osis_voters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('election_id')->constrained('osis_elections')->cascadeOnDelete();
            $table->foreignUuid('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->boolean('is_candidate')->default(false);
            $table->boolean('has_voted')->default(false)->index();
            $table->timestamp('voted_at')->nullable();
            $table->string('receipt_code', 32)->nullable()->unique();
            $table->timestamps();
            $table->unique(['election_id', 'siswa_id']);
        });

        Schema::create('osis_ballots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('election_id')->constrained('osis_elections')->cascadeOnDelete();
            $table->foreignUuid('package_id')->constrained('osis_packages')->cascadeOnDelete();
            $table->timestamp('cast_at');
            $table->timestamps();
            $table->index(['election_id', 'package_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('osis_ballots');
        Schema::dropIfExists('osis_voters');
        Schema::dropIfExists('osis_packages');
        Schema::dropIfExists('osis_elections');
    }
};

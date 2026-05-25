<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('downloads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('category_id')->nullable();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            $table->enum('source', ['local', 'gdrive'])->default('local');
            $table->string('file_name_original');
            $table->string('file_extension', 20)->nullable();
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('file_size')->default(0);

            $table->string('local_disk', 50)->default('public');
            $table->string('local_path')->nullable();

            $table->string('gdrive_file_id')->nullable();
            $table->string('gdrive_file_url')->nullable();

            $table->boolean('is_published')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('download_count')->default(0);

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('category_id')->references('id')->on('download_categories')->nullOnDelete();

            $table->index(['is_published', 'published_at']);
            $table->index(['category_id', 'is_published']);
            $table->index('source');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('downloads');
    }
};

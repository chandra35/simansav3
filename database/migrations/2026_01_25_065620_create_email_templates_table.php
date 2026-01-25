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
        Schema::create('email_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique(); // password_reset, welcome, notification, etc
            $table->string('name'); // Display name
            $table->string('subject'); // Email subject with placeholders
            $table->longText('body'); // HTML body with placeholders
            $table->text('description')->nullable(); // Description for admin
            $table->json('available_placeholders')->nullable(); // List of available placeholders
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false); // System templates cannot be deleted
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            
            $table->index('code');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('download_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('default_storage', ['local', 'gdrive'])->default('local');
            $table->enum('gdrive_auth_mode', ['service_account', 'oauth'])->default('service_account');
            $table->string('gdrive_root_folder_id')->nullable();
            $table->string('gdrive_credentials_path')->nullable();
            $table->boolean('gdrive_make_public')->default(true);
            $table->string('gdrive_oauth_client_id')->nullable();
            $table->text('gdrive_oauth_client_secret')->nullable();
            $table->text('gdrive_oauth_refresh_token')->nullable();
            $table->string('gdrive_oauth_email')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('download_settings');
    }
};

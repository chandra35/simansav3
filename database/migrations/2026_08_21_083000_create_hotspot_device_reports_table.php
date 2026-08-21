<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotspot_device_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotspot_user_id')->nullable()->constrained('hotspot_users')->nullOnDelete();
            $table->string('username', 64)->index();
            $table->string('mac_address', 17);
            $table->string('last_ip', 45)->nullable();
            $table->string('vendor', 60)->nullable();
            $table->string('model', 100)->nullable();
            $table->string('marketing_name', 100)->nullable();
            $table->string('device_type', 20)->nullable();
            $table->string('platform', 60)->nullable();
            $table->string('platform_version', 40)->nullable();
            $table->string('browser', 60)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('client_hints')->nullable();
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['username', 'mac_address']);
            $table->index(['mac_address', 'last_seen_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotspot_device_reports');
    }
};

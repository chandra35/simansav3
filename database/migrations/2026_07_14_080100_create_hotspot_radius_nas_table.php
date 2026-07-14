<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotspot_radius_nas', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('nasname', 120)->unique();
            $table->string('shortname', 60)->nullable();
            $table->string('type', 40)->default('mikrotik');
            $table->unsignedInteger('ports')->nullable();
            $table->text('secret')->nullable();
            $table->string('server', 80)->nullable();
            $table->string('community', 80)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('last_synced_at')->nullable();
            $table->string('sync_status', 20)->default('pending');
            $table->text('sync_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotspot_radius_nas');
    }
};

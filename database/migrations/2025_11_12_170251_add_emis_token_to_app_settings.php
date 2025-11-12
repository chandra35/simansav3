<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create api_tokens table for storing API tokens
        Schema::create('api_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Token name/identifier
            $table->text('token'); // Token value
            $table->string('description')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        // Insert default EMIS token
        DB::table('api_tokens')->insert([
            'name' => 'emis_api_token',
            'token' => env('EMIS_BEARER_TOKEN', ''),
            'description' => 'Token Bearer untuk API EMIS Kemenag (Cek NISN)',
            'expires_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_tokens');
    }
};

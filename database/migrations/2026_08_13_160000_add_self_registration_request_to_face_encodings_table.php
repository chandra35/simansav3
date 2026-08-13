<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('face_encodings', function (Blueprint $table) {
            $table->timestamp('self_registration_requested_at')->nullable()->after('self_registration_unlocked_at');
            $table->string('self_registration_request_note', 500)->nullable()->after('self_registration_requested_at');
        });
    }

    public function down(): void
    {
        Schema::table('face_encodings', function (Blueprint $table) {
            $table->dropColumn(['self_registration_requested_at', 'self_registration_request_note']);
        });
    }
};

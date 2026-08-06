<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('face_encodings', function (Blueprint $table) {
            $table->string('registration_photo')->nullable()->after('quality_score');
            $table->timestamp('self_registration_unlocked_at')->nullable()->after('registration_photo');
        });
    }

    public function down(): void
    {
        Schema::table('face_encodings', function (Blueprint $table) {
            $table->dropColumn(['registration_photo', 'self_registration_unlocked_at']);
        });
    }
};

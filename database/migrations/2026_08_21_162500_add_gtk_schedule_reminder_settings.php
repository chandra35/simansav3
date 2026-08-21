<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            $table->boolean('gtk_schedule_reminder_enabled')->default(true)->after('activity_log_require_location');
            $table->unsignedTinyInteger('gtk_schedule_reminder_minutes')->default(5)->after('gtk_schedule_reminder_enabled');
            $table->boolean('gtk_schedule_salutation_enabled')->default(true)->after('gtk_schedule_reminder_minutes');
            $table->string('gtk_salutation_male_senior', 30)->default('Pak')->after('gtk_schedule_salutation_enabled');
            $table->string('gtk_salutation_female_senior', 30)->default('Bu')->after('gtk_salutation_male_senior');
            $table->string('gtk_salutation_male_young', 30)->default('Mas')->after('gtk_salutation_female_senior');
            $table->string('gtk_salutation_female_young', 30)->default('Mbak')->after('gtk_salutation_male_young');
        });
    }

    public function down(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            $table->dropColumn([
                'gtk_schedule_reminder_enabled', 'gtk_schedule_reminder_minutes', 'gtk_schedule_salutation_enabled',
                'gtk_salutation_male_senior', 'gtk_salutation_female_senior', 'gtk_salutation_male_young', 'gtk_salutation_female_young',
            ]);
        });
    }
};

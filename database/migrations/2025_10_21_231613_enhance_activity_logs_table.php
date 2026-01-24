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
        // Check if columns exist before adding
        $columns = Schema::getColumnListing('activity_logs');
        
        Schema::table('activity_logs', function (Blueprint $table) use ($columns) {
            // Device & Browser Info
            if (!in_array('device_type', $columns)) {
                $table->string('device_type', 20)->nullable()->after('user_agent');
            }
            if (!in_array('browser', $columns)) {
                $table->string('browser', 50)->nullable()->after('user_agent');
            }
            if (!in_array('browser_version', $columns)) {
                $table->string('browser_version', 20)->nullable()->after('user_agent');
            }
            if (!in_array('platform', $columns)) {
                $table->string('platform', 50)->nullable()->after('user_agent');
            }
            if (!in_array('platform_version', $columns)) {
                $table->string('platform_version', 20)->nullable()->after('user_agent');
            }
            
            // Geo Location
            if (!in_array('country', $columns)) {
                $table->string('country', 100)->nullable()->after('ip_address');
            }
            if (!in_array('country_code', $columns)) {
                $table->string('country_code', 5)->nullable()->after('ip_address');
            }
            if (!in_array('region', $columns)) {
                $table->string('region', 100)->nullable()->after('ip_address');
            }
            if (!in_array('city', $columns)) {
                $table->string('city', 100)->nullable()->after('ip_address');
            }
            if (!in_array('postal_code', $columns)) {
                $table->string('postal_code', 20)->nullable()->after('ip_address');
            }
            if (!in_array('latitude', $columns)) {
                $table->decimal('latitude', 10, 7)->nullable()->after('ip_address');
            }
            if (!in_array('longitude', $columns)) {
                $table->decimal('longitude', 10, 7)->nullable()->after('ip_address');
            }
            if (!in_array('timezone', $columns)) {
                $table->string('timezone', 50)->nullable()->after('ip_address');
            }
            
            // Change tracking
            if (!in_array('old_values', $columns)) {
                $table->json('old_values')->nullable()->after('description');
            }
            if (!in_array('new_values', $columns)) {
                $table->json('new_values')->nullable()->after('description');
            }
            if (!in_array('changed_fields', $columns)) {
                $table->json('changed_fields')->nullable()->after('description');
            }
            
            // Additional context
            if (!in_array('url', $columns)) {
                $table->string('url', 500)->nullable()->after('description');
            }
            if (!in_array('method', $columns)) {
                $table->string('method', 10)->nullable()->after('description');
            }
            if (!in_array('notes', $columns)) {
                $table->text('notes')->nullable()->after('description');
            }
        });
        
        // Add indexes if they don't exist
        $indexes = DB::select("SHOW INDEX FROM activity_logs");
        $indexNames = array_column($indexes, 'Key_name');
        
        Schema::table('activity_logs', function (Blueprint $table) use ($indexNames) {
            if (!in_array('activity_logs_device_type_index', $indexNames)) {
                $table->index('device_type');
            }
            if (!in_array('activity_logs_country_code_index', $indexNames)) {
                $table->index('country_code');
            }
            if (!in_array('activity_logs_created_at_index', $indexNames)) {
                $table->index('created_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropColumn([
                'device_type', 'browser', 'browser_version', 
                'platform', 'platform_version',
                'country', 'country_code', 'region', 'city', 
                'postal_code', 'latitude', 'longitude', 'timezone',
                'old_values', 'new_values', 'changed_fields',
                'url', 'method', 'notes'
            ]);
        });
    }
};

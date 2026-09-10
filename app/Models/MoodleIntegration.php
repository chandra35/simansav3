<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MoodleIntegration extends Model
{
    protected $fillable = ['name', 'base_url', 'webservice_token', 'enabled', 'sync_users', 'sync_cohorts', 'sync_categories', 'student_email_domain', 'last_tested_at', 'last_test_status', 'last_test_message'];

    protected $casts = [
        'webservice_token' => 'encrypted',
        'enabled' => 'boolean',
        'sync_users' => 'boolean',
        'sync_cohorts' => 'boolean',
        'sync_categories' => 'boolean',
        'last_tested_at' => 'datetime',
    ];

    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1], [
            'name' => 'Moodle E-Learning MAN 1 Metro',
            'base_url' => 'https://elearning.man1metro.sch.id',
            'student_email_domain' => '@man1metro.sch.id',
        ]);
    }

    public function runs()
    {
        return $this->hasMany(MoodleSyncRun::class);
    }
}

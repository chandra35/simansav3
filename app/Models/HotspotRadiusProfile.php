<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HotspotRadiusProfile extends Model
{
    protected $fillable = [
        'code',
        'name',
        'role',
        'rate_limit',
        'session_timeout',
        'idle_timeout',
        'simultaneous_use',
        'framed_pool',
        'address_list',
        'priority',
        'description',
        'is_default',
        'is_active',
        'last_synced_at',
        'sync_status',
        'sync_error',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    public function users()
    {
        return $this->hasMany(HotspotUser::class, 'hotspot_radius_profile_id');
    }

    public function syncToRadius(): bool
    {
        try {
            $db = DB::connection('mysql_radius');

            if (!$this->is_active) {
                $this->removeFromRadius();
                return true;
            }

            $replyAttributes = [
                'Mikrotik-Rate-Limit' => $this->rate_limit,
                'Session-Timeout' => $this->session_timeout,
                'Idle-Timeout' => $this->idle_timeout,
                'Framed-Pool' => $this->framed_pool,
                'Mikrotik-Address-List' => $this->address_list,
            ];

            foreach ($replyAttributes as $attribute => $value) {
                if ($value === null || $value === '') {
                    $db->table('radgroupreply')
                        ->where('groupname', $this->code)
                        ->where('attribute', $attribute)
                        ->delete();
                    continue;
                }

                $db->table('radgroupreply')->updateOrInsert(
                    ['groupname' => $this->code, 'attribute' => $attribute],
                    ['op' => ':=', 'value' => (string) $value]
                );
            }

            if ($this->simultaneous_use) {
                $db->table('radgroupcheck')->updateOrInsert(
                    ['groupname' => $this->code, 'attribute' => 'Simultaneous-Use'],
                    ['op' => ':=', 'value' => (string) $this->simultaneous_use]
                );
            } else {
                $db->table('radgroupcheck')
                    ->where('groupname', $this->code)
                    ->where('attribute', 'Simultaneous-Use')
                    ->delete();
            }

            $this->update([
                'last_synced_at' => now(),
                'sync_status' => 'synced',
                'sync_error' => null,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('[HotspotRadiusProfile] Sync failed', [
                'profile' => $this->code,
                'error' => $e->getMessage(),
            ]);

            $this->update([
                'sync_status' => 'error',
                'sync_error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function removeFromRadius(): void
    {
        try {
            $db = DB::connection('mysql_radius');
            $db->table('radgroupreply')->where('groupname', $this->code)->delete();
            $db->table('radgroupcheck')->where('groupname', $this->code)->delete();
        } catch (\Throwable $e) {
            Log::warning('[HotspotRadiusProfile] Remove from RADIUS failed', [
                'profile' => $this->code,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public static function defaultForRole(?string $role): ?self
    {
        return static::query()
            ->where('is_active', true)
            ->where('is_default', true)
            ->where('role', $role)
            ->orderBy('priority')
            ->first();
    }
}

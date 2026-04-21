<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HotspotUser extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'username',
        'role',
        'display_name',
        'is_active',
        'expired_at',
        'keterangan',
        'last_synced_at',
        'sync_status',
        'sync_error',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expired_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Sync akun ini ke database FreeRADIUS.
     * Dipanggil saat create/update atau saat password berubah.
     */
    public function syncToRadius(string $plainPassword): bool
    {
        try {
            $db = DB::connection('mysql_radius');

            // 1. Update atau insert radcheck (password)
            $db->table('radcheck')->updateOrInsert(
                ['username' => $this->username, 'attribute' => 'Cleartext-Password'],
                ['op' => ':=', 'value' => $plainPassword]
            );

            // 2. Aktif/nonaktif: jika nonaktif, set Auth-Type := Reject
            if (!$this->is_active || ($this->expired_at && $this->expired_at->isPast())) {
                $db->table('radcheck')->updateOrInsert(
                    ['username' => $this->username, 'attribute' => 'Auth-Type'],
                    ['op' => ':=', 'value' => 'Reject']
                );
            } else {
                $db->table('radcheck')
                    ->where('username', $this->username)
                    ->where('attribute', 'Auth-Type')
                    ->delete();
            }

            // 3. Pastikan user masuk group yang tepat
            $db->table('radusergroup')->updateOrInsert(
                ['username' => $this->username],
                ['groupname' => $this->role, 'priority' => 1]
            );

            $this->update([
                'last_synced_at' => now(),
                'sync_status' => 'synced',
                'sync_error' => null,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('[HotspotUser] Sync to RADIUS failed', [
                'username' => $this->username,
                'error' => $e->getMessage(),
            ]);

            $this->update([
                'sync_status' => 'error',
                'sync_error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Hapus akun dari database RADIUS.
     */
    public function removeFromRadius(): void
    {
        try {
            $db = DB::connection('mysql_radius');
            $db->table('radcheck')->where('username', $this->username)->delete();
            $db->table('radreply')->where('username', $this->username)->delete();
            $db->table('radusergroup')->where('username', $this->username)->delete();
        } catch (\Exception $e) {
            Log::error('[HotspotUser] Remove from RADIUS failed', [
                'username' => $this->username,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function isExpired(): bool
    {
        return $this->expired_at && $this->expired_at->isPast();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expired_at')->orWhere('expired_at', '>', now());
            });
    }

    public function scopeGuru($query)
    {
        return $query->where('role', 'guru');
    }

    public function scopeSiswa($query)
    {
        return $query->where('role', 'siswa');
    }

    public function scopeTamu($query)
    {
        return $query->where('role', 'tamu');
    }
}

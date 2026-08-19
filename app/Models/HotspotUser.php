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
        'hotspot_radius_profile_id',
        'display_name',
        'is_active',
        'blocked_at',
        'blocked_by',
        'block_reason',
        'expired_at',
        'keterangan',
        'last_synced_at',
        'sync_status',
        'sync_error',
        'password_secret',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'blocked_at' => 'datetime',
        'expired_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'password_secret' => 'encrypted',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function radiusProfile()
    {
        return $this->belongsTo(HotspotRadiusProfile::class, 'hotspot_radius_profile_id');
    }

    /**
     * Sync akun ini ke database FreeRADIUS.
     * Dipanggil saat create/update atau saat password berubah.
     */
    public function syncToRadius(string $plainPassword): bool
    {
        try {
            if ($this->role !== 'tamu' && $plainPassword !== '__DISABLED__' && !$this->isSecurePassword($plainPassword)) {
                $this->forceFill([
                    'is_active' => false,
                    'sync_error' => 'Password hotspot tidak aman. Reset password akun SIMANSA untuk mengaktifkan kembali.',
                ])->save();
                $plainPassword = '__DISABLED__';
            }

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

            // 3. Pastikan user masuk group/profile yang tepat
            $profile = $this->radiusProfile ?: HotspotRadiusProfile::defaultForRole($this->role);
            $groupName = $profile?->code ?: $this->role;
            $priority = $profile?->priority ?: 1;

            $db->table('radusergroup')->updateOrInsert(
                ['username' => $this->username],
                ['groupname' => $groupName, 'priority' => $priority]
            );

            // 4. Sinkronkan identitas untuk daloRADIUS dan halaman status MikroTik.
            $this->writeIdentityToRadius($db);

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
     * Sinkronkan nama tanpa menyentuh password atau status autentikasi.
     */
    public function syncIdentityToRadius(): bool
    {
        try {
            $this->writeIdentityToRadius(DB::connection('mysql_radius'));

            return true;
        } catch (\Exception $e) {
            Log::error('[HotspotUser] Identity sync to RADIUS failed', [
                'username' => $this->username,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function writeIdentityToRadius($db): void
    {
        $this->loadMissing(['radiusProfile', 'user.siswa.kelasAktif']);

        $displayName = trim((string) $this->display_name);
        $profile = $this->radiusProfile ?: HotspotRadiusProfile::defaultForRole($this->role);
        $profileLabel = trim((string) ($profile?->name ?: $profile?->code));
        $rombel = trim((string) ($this->user?->siswa?->kelasAktif?->first()?->nama_kelas));

        $db->table('userinfo')->updateOrInsert(
            ['username' => $this->username],
            [
                'firstname' => $displayName !== '' ? $displayName : null,
                'department' => $this->role ?: null,
                'company' => $profileLabel !== '' ? $profileLabel : null,
                'notes' => $rombel !== '' ? $rombel : null,
                'updatedate' => now(),
                'updateby' => 'simansav3',
            ]
        );

        if ($displayName === '') {
            $db->table('radreply')
                ->where('username', $this->username)
                ->where('attribute', 'Reply-Message')
                ->delete();

            return;
        }

        // Reply-Message hanya dipakai sebagai payload tampilan portal. Hindari
        // Filter-Id/Class karena keduanya memiliki arti kebijakan/accounting.
        $identity = array_filter([
            'n' => $displayName,
            'r' => $this->role,
            'k' => $rombel,
            'p' => $profileLabel,
        ], static fn ($value) => $value !== null && $value !== '');
        $encodedIdentity = base64_encode(json_encode($identity, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        // Nilai atribut RADIUS dibatasi 253 byte. Nama tetap diprioritaskan;
        // metadata opsional dikurangi hanya untuk identitas yang sangat panjang.
        foreach (['p', 'k', 'r'] as $optionalKey) {
            if (strlen($encodedIdentity) <= 253) {
                break;
            }
            unset($identity[$optionalKey]);
            $encodedIdentity = base64_encode(json_encode($identity, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        if (strlen($encodedIdentity) > 253) {
            $identity = ['n' => mb_strcut($displayName, 0, 165, 'UTF-8')];
            $encodedIdentity = base64_encode(json_encode($identity, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        $db->table('radreply')->updateOrInsert(
            ['username' => $this->username, 'attribute' => 'Reply-Message'],
            ['op' => ':=', 'value' => $encodedIdentity]
        );
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
            $db->table('userinfo')->where('username', $this->username)->delete();
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

    public function isSecurePassword(string $password): bool
    {
        if (mb_strlen($password) < 8) {
            return false;
        }

        if (!hash_equals($this->username, $password)) {
            return true;
        }

        // Kebijakan sekolah: akun GTK lama tetap boleh memakai NIK 16 digit.
        return $this->role === 'guru' && preg_match('/^\d{16}$/', $this->username) === 1;
    }

    public function isEligibleForRadius(): bool
    {
        if (!$this->user || !$this->user->is_active) {
            return $this->role === 'tamu';
        }

        if ($this->role === 'siswa') {
            $siswa = $this->user->siswa;

            return $siswa
                && $siswa->status_siswa === 'aktif'
                && !empty($siswa->nisn);
        }

        return in_array($this->role, ['guru', 'tamu'], true);
    }

    public function rejectFromRadius(string $reason = 'Tidak eligible untuk akses hotspot.'): void
    {
        $this->update([
            'is_active' => false,
            'sync_status' => 'pending',
            'sync_error' => $reason,
        ]);

        $this->syncToRadius('__DISABLED__');
    }
}

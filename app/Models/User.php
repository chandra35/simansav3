<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\HasUuid;
use App\Traits\HasActivityLog;
use App\Models\UserSession;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Crypt;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUuid, HasActivityLog, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'avatar',
        'is_first_login',
        'is_active',
        'phone',
        'encrypted_password',
        'password_reset_at',
        'password_reset_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'encrypted_password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_first_login' => 'boolean',
            'is_active' => 'boolean',
            'password_reset_at' => 'datetime',
        ];
    }

    /**
     * Cek apakah email masih email default sistem (bukan email pribadi user).
     */
    public function hasDefaultEmail(): bool
    {
        $email = strtolower((string) $this->email);
        if ($email === '') return true;
        return str_ends_with($email, '@siswa.simansa.sch.id')
            || str_ends_with($email, '@student.man1metro.sch.id');
    }

    /**
     * Get the decrypted readable password
     * This is used for displaying password in admin panel
     */
    public function getReadablePasswordAttribute(): ?string
    {
        if (empty($this->encrypted_password)) {
            return null;
        }

        try {
            return Crypt::decryptString($this->encrypted_password);
        } catch (\Exception $e) {
            // If decryption fails, return the value as-is (might be old plain password)
            return $this->encrypted_password;
        }
    }

    /**
     * Set password with encryption
     * Use: $user->readable_password = 'password123';
     */
    public function setReadablePasswordAttribute(string $value): void
    {
        $this->attributes['encrypted_password'] = Crypt::encryptString($value);
    }

    // Relations
    public function siswa()
    {
        return $this->hasOne(Siswa::class);
    }

    public function matrikulasiPeserta()
    {
        return $this->hasOne(MatrikulasiPeserta::class);
    }

    public function gtk()
    {
        return $this->hasOne(Gtk::class);
    }

    public function asramaSantri()
    {
        return $this->hasOneThrough(
            AsramaSantri::class,
            Siswa::class,
            'user_id',
            'siswa_id',
            'id',
            'id'
        );
    }

    public function asramaAsatidz()
    {
        return $this->hasOneThrough(
            AsramaAsatidz::class,
            Gtk::class,
            'user_id',
            'gtk_id',
            'id',
            'id'
        );
    }

    public function osisVoterRecords()
    {
        return $this->hasMany(OsisVoter::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function sessions()
    {
        return $this->hasMany(UserSession::class);
    }

    public function latestSession()
    {
        return $this->hasOne(UserSession::class)->latestOfMany('last_activity');
    }

    /**
     * Relationship: User has many tugas tambahan
     */
    public function tugasTambahan()
    {
        return $this->hasMany(TugasTambahan::class);
    }

    /**
     * Relationship: User has many active tugas tambahan
     */
    public function activeTugasTambahan()
    {
        return $this->hasMany(TugasTambahan::class)->where('is_active', true);
    }

    // Helper methods
    public function isSiswa()
    {
        // Check Spatie role first, fallback to old role column
        return $this->hasRole('Siswa') || $this->role === 'siswa';
    }

    public function isAdmin()
    {
        return in_array($this->role, ['super_admin', 'admin', 'gtk', 'operator']);
    }

    /**
     * Check if user has specific active tugas tambahan (additional role)
     */
    public function hasActiveTugasTambahan(string $roleName): bool
    {
        return $this->activeTugasTambahan()
            ->whereHas('role', function ($query) use ($roleName) {
                $query->where('name', $roleName);
            })
            ->exists();
    }

    /**
     * Get all active tugas tambahan role names
     */
    public function getActiveTugasTambahanRoles(): array
    {
        return $this->activeTugasTambahan()
            ->with('role')
            ->get()
            ->pluck('role.name')
            ->toArray();
    }

    /**
     * Get formatted tugas tambahan string (for display)
     */
    public function getTugasTambahanStringAttribute(): string
    {
        $roles = $this->getActiveTugasTambahanRoles();
        return empty($roles) ? '-' : implode(', ', $roles);
    }

    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return asset('storage/avatars/' . $this->avatar);
        }
        return asset('vendor/adminlte/dist/img/user2-160x160.jpg');
    }

    public function getIsOnlineAttribute(): bool
    {
        return $this->latestSession?->isStillOnline() ?? false;
    }
}

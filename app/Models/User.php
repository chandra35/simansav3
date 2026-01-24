<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\HasUuid;
use App\Traits\HasActivityLog;
use Spatie\Permission\Traits\HasRoles;

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
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
        ];
    }

    // Relations
    public function siswa()
    {
        return $this->hasOne(Siswa::class);
    }

    public function gtk()
    {
        return $this->hasOne(Gtk::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
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
}

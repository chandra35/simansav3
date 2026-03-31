<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Absensi extends Model
{
    use SoftDeletes;

    protected $table = 'absensis';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id', 'user_type', 'tahun_pelajaran_id', 'tanggal',
        'waktu_masuk', 'waktu_pulang',
        'status', 'status_pulang',
        'metode_masuk', 'metode_pulang',
        'face_confidence_masuk', 'face_confidence_pulang',
        'foto_masuk', 'foto_pulang',
        'location_id',
        'latitude_masuk', 'longitude_masuk',
        'latitude_pulang', 'longitude_pulang',
        'device_masuk', 'ip_masuk',
        'device_pulang', 'ip_pulang',
        'catatan', 'file_bukti',
        'input_by', 'edited_by', 'edited_at', 'edit_reason',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'waktu_masuk' => 'datetime',
        'waktu_pulang' => 'datetime',
        'edited_at' => 'datetime',
        'latitude_masuk' => 'decimal:8',
        'longitude_masuk' => 'decimal:8',
        'latitude_pulang' => 'decimal:8',
        'longitude_pulang' => 'decimal:8',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    // ============================================
    // RELATIONSHIPS
    // ============================================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class);
    }

    public function location()
    {
        return $this->belongsTo(AbsensiLocation::class, 'location_id');
    }

    public function inputBy()
    {
        return $this->belongsTo(User::class, 'input_by');
    }

    public function editedBy()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    public function logs()
    {
        return $this->hasMany(AbsensiLog::class);
    }

    // ============================================
    // SCOPES
    // ============================================

    public function scopeGtk($query)
    {
        return $query->where('user_type', 'gtk');
    }

    public function scopeSiswa($query)
    {
        return $query->where('user_type', 'siswa');
    }

    public function scopeTanggal($query, $date)
    {
        return $query->where('tanggal', Carbon::parse($date)->format('Y-m-d'));
    }

    public function scopeBulan($query, $month, $year)
    {
        return $query->whereMonth('tanggal', $month)->whereYear('tanggal', $year);
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // ============================================
    // HELPERS
    // ============================================

    /**
     * Determine status based on time settings
     */
    public static function determineStatus(Carbon $waktuMasuk): string
    {
        return static::determineStatusForType($waktuMasuk, 'gtk');
    }

    public static function determineStatusForType(Carbon $waktuMasuk, string $userType = 'gtk'): string
    {
        $jamMasuk = static::getJamMasukForType($userType);
        $toleransi = (int) AbsensiSetting::getValue('toleransi_terlambat', 15);

        $batasTepat = Carbon::parse($waktuMasuk->format('Y-m-d') . ' ' . $jamMasuk);
        $batasTerlambat = $batasTepat->copy()->addMinutes($toleransi);

        if ($waktuMasuk->lte($batasTerlambat)) {
            return $waktuMasuk->lte($batasTepat) ? 'hadir' : 'terlambat';
        }

        return 'terlambat';
    }

    public static function getJamMasukForType(string $userType = 'gtk'): string
    {
        $key = $userType === 'siswa' ? 'jam_masuk_siswa' : 'jam_masuk_gtk';
        return AbsensiSetting::getValue($key, '07:00');
    }

    public static function getJamPulangForType(string $userType = 'gtk'): string
    {
        $key = $userType === 'siswa' ? 'jam_pulang_siswa' : 'jam_pulang_gtk';
        return AbsensiSetting::getValue($key, $userType === 'siswa' ? '15:00' : '16:00');
    }

    /**
     * Get badge class for status
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'hadir' => 'success',
            'terlambat' => 'warning',
            'izin' => 'info',
            'sakit' => 'primary',
            'dinas_luar' => 'secondary',
            'cuti' => 'dark',
            default => 'danger', // alpa
        };
    }

    /**
     * Get formatted waktu masuk
     */
    public function getWaktuMasukFormattedAttribute(): string
    {
        return $this->waktu_masuk ? $this->waktu_masuk->format('H:i:s') : '-';
    }

    /**
     * Get formatted waktu pulang
     */
    public function getWaktuPulangFormattedAttribute(): string
    {
        return $this->waktu_pulang ? $this->waktu_pulang->format('H:i:s') : '-';
    }

    /**
     * Get durasi kerja
     */
    public function getDurasiKerjaAttribute(): ?string
    {
        if (!$this->waktu_masuk || !$this->waktu_pulang) return null;
        $diff = $this->waktu_masuk->diff($this->waktu_pulang);
        return $diff->format('%H jam %I menit');
    }
}

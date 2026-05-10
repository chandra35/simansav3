<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JadwalPelajaran extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'jadwal_pelajaran';

    protected $fillable = [
        'tahun_pelajaran_id',
        'kelas_id',
        'mapel_id',
        'gtk_id',
        'hari',
        'jam_ke',
        'jam_mulai',
        'jam_selesai',
        'ruangan',
        'semester',
        'catatan',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'jam_ke'    => 'integer',
        'semester'  => 'integer',
        'is_active' => 'boolean',
    ];

    const HARI = [
        'senin'  => 'Senin',
        'selasa' => 'Selasa',
        'rabu'   => 'Rabu',
        'kamis'  => 'Kamis',
        'jumat'  => 'Jumat',
        'sabtu'  => 'Sabtu',
    ];

    // Relations
    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class, 'mapel_id');
    }

    public function gtk()
    {
        return $this->belongsTo(Gtk::class, 'gtk_id');
    }

    // Scopes
    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeHari($query, $hari)
    {
        return $query->where('hari', $hari);
    }

    public function scopeSemester($query, $semester)
    {
        return $query->where('semester', $semester);
    }

    // Accessors
    public function getHariLabelAttribute(): string
    {
        return self::HARI[$this->hari] ?? $this->hari;
    }

    public function getJamAttribute(): string
    {
        $mulai   = $this->jam_mulai   ? substr($this->jam_mulai, 0, 5)   : '';
        $selesai = $this->jam_selesai ? substr($this->jam_selesai, 0, 5) : '';
        return $mulai && $selesai ? "{$mulai} - {$selesai}" : ($mulai ?: '-');
    }

    /**
     * Override resolveRouteBinding untuk include soft-deleted records.
     * Penting karena model pakai SoftDeletes, tapi implicit binding hanya cari non-deleted.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? $this->getRouteKeyName(), $value)
            ->withTrashed()
            ->firstOrFail();
    }
}
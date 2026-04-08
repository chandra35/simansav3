<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NilaiSiswa extends Model
{
    use SoftDeletes, HasUuids;

    protected $table = 'nilai_siswa';

    protected $fillable = [
        'siswa_id',
        'mata_pelajaran_id',
        'tahun_pelajaran_id',
        'semester',
        'nilai',
        'nilai_pengetahuan',
        'nilai_keterampilan',
        'predikat',
        'deskripsi_pengetahuan',
        'deskripsi_keterampilan',
        'sumber_data',
        'imported_at',
    ];

    protected $casts = [
        'semester' => 'integer',
        'nilai' => 'decimal:2',
        'nilai_pengetahuan' => 'decimal:2',
        'nilai_keterampilan' => 'decimal:2',
        'imported_at' => 'datetime',
    ];

    /**
     * Semester labels untuk display
     */
    public const SEMESTER_LABELS = [
        1 => 'Semester 1 - Kelas X',
        2 => 'Semester 2 - Kelas X',
        3 => 'Semester 3 - Kelas XI',
        4 => 'Semester 4 - Kelas XI',
        5 => 'Semester 5 - Kelas XII',
    ];

    /**
     * Get semester label
     */
    public function getSemesterLabelAttribute(): string
    {
        return self::SEMESTER_LABELS[$this->semester] ?? "Semester {$this->semester}";
    }

    /**
     * Hitung predikat berdasarkan nilai
     */
    public static function hitungPredikat($nilai): string
    {
        if ($nilai === null) return '-';
        if ($nilai >= 90) return 'A';
        if ($nilai >= 80) return 'B';
        if ($nilai >= 70) return 'C';
        if ($nilai >= 60) return 'D';
        return 'E';
    }

    // Relations
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class);
    }

    // Scopes
    public function scopeBySemester($query, $semester)
    {
        return $query->where('semester', $semester);
    }

    public function scopeByTahunPelajaran($query, $tahunPelajaranId)
    {
        return $query->where('tahun_pelajaran_id', $tahunPelajaranId);
    }

    public function scopeBySiswa($query, $siswaId)
    {
        return $query->where('siswa_id', $siswaId);
    }
}

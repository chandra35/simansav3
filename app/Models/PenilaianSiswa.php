<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PenilaianSiswa extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'penilaian_siswa';

    protected $fillable = [
        'tahun_pelajaran_id',
        'semester',
        'kelas_id',
        'mata_pelajaran_id',
        'gtk_id',
        'siswa_id',
        'kategori',
        'kode_penilaian',
        'nama_penilaian',
        'moodle_quiz_id',
        'moodle_quiz_archive_id',
        'nilai',
        'remedial',
        'nilai_akhir',
        'keterangan',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'semester' => 'integer',
        'moodle_quiz_id' => 'integer',
        'nilai' => 'decimal:2',
        'remedial' => 'decimal:2',
        'nilai_akhir' => 'decimal:2',
    ];

    public const KATEGORI_LABELS = [
        'harian' => 'Penilaian Harian (PH)',
        'tengah_semester' => 'Tengah Semester (PTS/STS/ATS)',
        'akhir_semester' => 'Akhir Semester (PAS/SAS/ASAS)',
        'tugas' => 'Tugas',
        'proyek' => 'Proyek',
    ];

    public function getKategoriLabelAttribute(): string
    {
        return self::KATEGORI_LABELS[$this->kategori] ?? ucfirst($this->kategori);
    }

    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }

    public function gtk(): BelongsTo
    {
        return $this->belongsTo(Gtk::class, 'gtk_id');
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function archive(): BelongsTo
    {
        return $this->belongsTo(MoodleQuizArchive::class, 'moodle_quiz_archive_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

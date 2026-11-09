<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\MoodleQuizArchiveScore;
use App\Models\PenilaianSiswa;

class MoodleQuizArchive extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'moodle_quiz_archives';

    protected $fillable = [
        'gtk_id',
        'user_id',
        'tahun_pelajaran_id',
        'semester',
        'mata_pelajaran_id',
        'kelas_id',
        'moodle_course_id',
        'moodle_course_name',
        'moodle_category_id',
        'moodle_category_name',
        'moodle_quiz_id',
        'moodle_quiz_name',
        'kategori_penilaian',
        'nama_penilaian',
        'statistics',
        'items_analysis',
        'is_locked',
        'locked_at',
        'locked_by',
        'catatan',
    ];

    protected $casts = [
        'semester' => 'integer',
        'moodle_course_id' => 'integer',
        'moodle_category_id' => 'integer',
        'moodle_quiz_id' => 'integer',
        'statistics' => 'array',
        'items_analysis' => 'array',
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
    ];

    public function gtk(): BelongsTo
    {
        return $this->belongsTo(Gtk::class, 'gtk_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function lockedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(MoodleQuizArchiveScore::class, 'archive_id')->orderBy('nama_siswa');
    }

    public function penilaianSiswa(): HasMany
    {
        return $this->hasMany(PenilaianSiswa::class, 'moodle_quiz_archive_id');
    }
}

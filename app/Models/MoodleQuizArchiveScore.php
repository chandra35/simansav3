<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MoodleQuizArchiveScore extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'moodle_quiz_archive_scores';

    protected $fillable = [
        'archive_id',
        'moodle_userid',
        'moodle_username',
        'siswa_id',
        'nama_siswa',
        'kelas_nama',
        'grade',
        'max_grade',
        'time_spent_seconds',
        'time_finished',
        'responses_summary',
    ];

    protected $casts = [
        'moodle_userid' => 'integer',
        'grade' => 'decimal:2',
        'max_grade' => 'decimal:2',
        'time_spent_seconds' => 'integer',
        'time_finished' => 'datetime',
        'responses_summary' => 'array',
    ];

    public function archive(): BelongsTo
    {
        return $this->belongsTo(MoodleQuizArchive::class, 'archive_id');
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }
}

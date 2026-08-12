<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PenugasanGtk extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'penugasan_gtk';

    protected $fillable = [
        'gtk_id', 'jenis_penugasan_id', 'tahun_pelajaran_id', 'semester',
        'unit_nama', 'mulai_tugas', 'selesai_tugas', 'nomor_sk', 'tanggal_sk',
        'file_sk', 'ekuivalensi_jtm', 'status', 'role_diberikan_otomatis', 'keterangan',
        'legacy_tugas_tambahan_id', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'semester' => 'integer',
        'ekuivalensi_jtm' => 'integer',
        'role_diberikan_otomatis' => 'boolean',
        'mulai_tugas' => 'date',
        'selesai_tugas' => 'date',
        'tanggal_sk' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $assignment) {
            $assignment->created_by ??= auth()->id();
            $assignment->updated_by ??= auth()->id();
        });
        static::updating(fn (self $assignment) => $assignment->updated_by = auth()->id() ?: $assignment->updated_by);
    }

    public function gtk()
    {
        return $this->belongsTo(Gtk::class, 'gtk_id');
    }

    public function jenis()
    {
        return $this->belongsTo(JenisPenugasanGtk::class, 'jenis_penugasan_id');
    }

    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->whereDate('mulai_tugas', '<=', today())
            ->where(fn ($q) => $q->whereNull('selesai_tugas')->orWhereDate('selesai_tugas', '>=', today()));
    }

    public function scopeForPeriod($query, string $yearId, ?int $semester = null)
    {
        return $query->where('tahun_pelajaran_id', $yearId)
            ->when($semester, fn ($q) => $q->where(fn ($period) => $period->whereNull('semester')->orWhere('semester', $semester)));
    }
}

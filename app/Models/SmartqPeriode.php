<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasUuid;

class SmartqPeriode extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'smartq_periodes';

    protected $fillable = [
        'nama',
        'tahun_pelajaran_id',
        'deskripsi',
        'kuota',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'moodle_base_url',
        'moodle_course_id',
        'moodle_quiz_id',
        'moodle_quiz_name',
    ];

    protected $casts = [
        'kuota' => 'integer',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'moodle_course_id' => 'integer',
        'moodle_quiz_id' => 'integer',
    ];

    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class);
    }

    public function komponenNilais()
    {
        return $this->hasMany(SmartqKomponenNilai::class, 'smartq_periode_id')->orderBy('urutan');
    }

    public function pesertas()
    {
        return $this->hasMany(SmartqPeserta::class, 'smartq_periode_id');
    }

    public function pesertaLulus()
    {
        return $this->pesertas()->where('status', 'lulus');
    }

    public function getTotalBobotAttribute(): float
    {
        return $this->komponenNilais->sum('bobot');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pendaftaran' => '<span class="badge badge-info"><i class="fas fa-clipboard-list"></i> Pendaftaran</span>',
            'seleksi' => '<span class="badge badge-warning"><i class="fas fa-tasks"></i> Seleksi</span>',
            'pengumuman' => '<span class="badge badge-primary"><i class="fas fa-bullhorn"></i> Pengumuman</span>',
            'selesai' => '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Selesai</span>',
            default => '<span class="badge badge-secondary">-</span>',
        };
    }

    public function hitungRanking(): void
    {
        $komponens = $this->komponenNilais;
        $pesertas = $this->pesertas()->with('nilais')->get();

        foreach ($pesertas as $peserta) {
            $totalNilaiTerbobot = 0;

            foreach ($komponens as $komponen) {
                $nilai = $peserta->nilais->where('smartq_komponen_nilai_id', $komponen->id)->first();
                $nilaiKonversi = $nilai?->nilai_konversi ?? 0;
                $totalNilaiTerbobot += ($nilaiKonversi * $komponen->bobot / 100);
            }

            $peserta->update(['total_nilai' => round($totalNilaiTerbobot, 2)]);
        }

        // Assign ranking
        $sorted = $this->pesertas()
            ->where('status', '!=', 'mengundurkan_diri')
            ->orderByDesc('total_nilai')
            ->get();

        $rank = 1;
        foreach ($sorted as $p) {
            $p->update(['ranking' => $rank++]);
        }
    }
}

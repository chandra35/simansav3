<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VerifikasiIjazah extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'verifikasi_ijazah';

    protected $fillable = [
        'siswa_id',
        'verifikator_id',
        'verifikator_nama',
        'status',
        'data_simansa',
        'data_emis_kemdikbud',
        'data_emis_kemenag',
        'data_emis_lembaga',
        'field_tidak_sesuai',
        'saran_perbaikan',
        'catatan',
        'verified_at',
    ];

    protected $casts = [
        'data_simansa'        => 'array',
        'data_emis_kemdikbud' => 'array',
        'data_emis_kemenag'   => 'array',
        'data_emis_lembaga'   => 'array',
        'field_tidak_sesuai'  => 'array',
        'saran_perbaikan'     => 'array',
        'verified_at'         => 'datetime',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifikator_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(VerifikasiIjazahLog::class, 'verifikasi_id')->latest();
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'sesuai'            => '<span class="badge badge-success">Sesuai</span>',
            'tidak_sesuai'      => '<span class="badge badge-danger">Tidak Sesuai</span>',
            'perlu_perbaikan'   => '<span class="badge badge-warning">Perlu Perbaikan</span>',
            default             => '<span class="badge badge-secondary">Belum Diverifikasi</span>',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'sesuai'            => 'Sesuai',
            'tidak_sesuai'      => 'Tidak Sesuai',
            'perlu_perbaikan'   => 'Perlu Perbaikan',
            default             => 'Belum Diverifikasi',
        };
    }

    public function sudahDiverifikasi(): bool
    {
        return $this->status !== 'belum_diverifikasi';
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MataPelajaran extends Model
{
    use SoftDeletes, HasUuids;

    protected $table = 'mata_pelajaran';

    protected $fillable = [
        'kurikulum_id',
        'tahun_pelajaran_id',
        'jurusan_id',
        'kode_mapel',
        'nama_mapel',
        'kelompok',
        'kategori',
        'kkm',
        'capaian_pembelajaran',
        'is_mapel_agama',
        'jenis_agama',
        'is_rumpun_pai',
        'sub_pai',
        'is_bahasa_arab',
        'is_mapel_pilihan',
        'is_projek_p5',
        'is_muatan_lokal',
        'jam_pelajaran',
        'tingkat',
        'semester',
        'deskripsi',
        'is_active',
    ];

    protected $casts = [
        'tingkat' => 'array',
        'semester' => 'array',
        'is_mapel_agama' => 'boolean',
        'is_rumpun_pai' => 'boolean',
        'is_bahasa_arab' => 'boolean',
        'is_mapel_pilihan' => 'boolean',
        'is_projek_p5' => 'boolean',
        'is_muatan_lokal' => 'boolean',
        'is_active' => 'boolean',
        'kkm' => 'integer',
        'jam_pelajaran' => 'integer',
    ];

    /**
     * Relationships
     */
    public function kurikulum(): BelongsTo
    {
        return $this->belongsTo(Kurikulum::class);
    }

    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class);
    }

    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class);
    }

    public function nilaiSiswa()
    {
        return $this->hasMany(NilaiSiswa::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeMapelAgama($query)
    {
        return $query->where('is_mapel_agama', true);
    }

    public function scopeRumpunPAI($query)
    {
        return $query->where('is_rumpun_pai', true);
    }

    public function scopeByKelompok($query, $kelompok)
    {
        return $query->where('kelompok', $kelompok);
    }

    public function scopeByTingkat($query, $tingkat)
    {
        return $query->whereJsonContains('tingkat', (int) $tingkat);
    }

    public function scopeByKurikulum($query, $kurikulumId)
    {
        return $query->where('kurikulum_id', $kurikulumId);
    }

    public function scopeByJurusan($query, $jurusanId)
    {
        return $query->where('jurusan_id', $jurusanId);
    }

    /**
     * Accessors
     */
    public function getKelompokBadgeAttribute()
    {
        $colors = [
            'A' => 'primary',
            'B' => 'success',
            'C' => 'warning',
            'umum' => 'info',
            'pilihan' => 'secondary',
        ];

        $color = $colors[$this->kelompok] ?? 'secondary';
        return "<span class='badge badge-{$color}'>{$this->kelompok}</span>";
    }

    public function getJenisAgamaBadgeAttribute()
    {
        if (!$this->is_mapel_agama) {
            return '';
        }

        $agama = ucfirst($this->jenis_agama);
        return "<span class='badge badge-info'><i class='fas fa-pray'></i> {$agama}</span>";
    }

    public function getSubPaiBadgeAttribute()
    {
        if (!$this->is_rumpun_pai) {
            return '';
        }

        $labels = [
            'quran_hadits' => 'QH',
            'akidah_akhlak' => 'AA',
            'fikih' => 'FIQ',
            'ski' => 'SKI',
        ];

        $label = $labels[$this->sub_pai] ?? strtoupper($this->sub_pai);
        return "<span class='badge badge-success'><i class='fas fa-mosque'></i> {$label}</span>";
    }

    public function getStatusBadgeAttribute()
    {
        $status = $this->is_active ? 'Aktif' : 'Non-aktif';
        $color = $this->is_active ? 'success' : 'danger';
        return "<span class='badge badge-{$color}'>{$status}</span>";
    }

    public function getTingkatTextAttribute()
    {
        if (!$this->tingkat) {
            return '-';
        }

        return implode(', ', $this->tingkat);
    }

    public function getSemesterTextAttribute()
    {
        if (!$this->semester) {
            return 'Genap';
        }

        return implode(', ', $this->semester);
    }

    /**
     * Helpers
     */
    public function isMadrasahMapel()
    {
        return $this->is_rumpun_pai || $this->is_bahasa_arab;
    }

    public function isPeminatan()
    {
        return $this->kategori === 'peminatan' && !is_null($this->jurusan_id);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MataPelajaran extends Model
{
    use SoftDeletes, HasUuids;

    protected $table = 'mata_pelajaran';

    protected $fillable = [
        'kurikulum_id',
        'tahun_pelajaran_id',
        'jurusan_id',
        'kode_mapel',
        'kode_jadwal',
        'nama_mapel',
        'kelompok',
        'kategori',
        'struktur_fase_e',
        'struktur_fase_f',
        'rumpun',
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
        'alokasi_jp',
        'regulasi',
        'is_schedulable',
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
        'is_schedulable' => 'boolean',
        'kkm' => 'integer',
        'jam_pelajaran' => 'integer',
        'alokasi_jp' => 'array',
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

    public function rdmMappings(): HasMany
    {
        return $this->hasMany(RdmMapelMapping::class, 'mata_pelajaran_id');
    }

    public function jadwalPelajaran(): HasMany
    {
        return $this->hasMany(JadwalPelajaran::class, 'mapel_id');
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
        $faseColumn = (int) $tingkat === 10 ? 'struktur_fase_e' : 'struktur_fase_f';

        return $query
            ->whereJsonContains('tingkat', (int) $tingkat)
            ->whereNotNull($faseColumn);
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
        $struktur = $this->struktur_label;
        if ($struktur !== 'Belum ditetapkan') {
            $colors = [
                'Wajib / Umum' => 'primary',
                'Pilihan' => 'warning',
                'Muatan Lokal' => 'success',
                'Penguatan Program' => 'info',
                'Kokurikuler' => 'purple',
            ];
            $color = $colors[$struktur] ?? 'secondary';

            return "<span class='badge badge-{$color}'>{$struktur}</span>";
        }

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

    public function getFaseTextAttribute(): string
    {
        $fase = [];
        if ($this->struktur_fase_e) {
            $fase[] = 'E · X';
        }
        if ($this->struktur_fase_f) {
            $fase[] = 'F · XI–XII';
        }

        return $fase ? implode(', ', $fase) : '-';
    }

    public function getKodeTampilJadwalAttribute(): string
    {
        return $this->kode_jadwal ?: $this->kode_mapel;
    }

    public function getStrukturLabelAttribute(): string
    {
        $structures = array_values(array_unique(array_filter([
            $this->struktur_fase_e,
            $this->struktur_fase_f,
        ])));

        if (!$structures) {
            return 'Belum ditetapkan';
        }

        if (count($structures) > 1) {
            return 'Lintas Fase';
        }

        return match ($structures[0]) {
            'wajib_umum' => 'Wajib / Umum',
            'pilihan' => 'Pilihan',
            'muatan_lokal' => 'Muatan Lokal',
            'penguatan_program' => 'Penguatan Program',
            'kokurikuler' => 'Kokurikuler',
            default => ucfirst(str_replace('_', ' ', $structures[0])),
        };
    }

    public function strukturUntukTingkat(int $tingkat): ?string
    {
        return $tingkat === 10 ? $this->struktur_fase_e : $this->struktur_fase_f;
    }

    public function jpUntukTingkat(int $tingkat): int
    {
        return (int) ($this->alokasi_jp[(string) $tingkat] ?? $this->jam_pelajaran ?? 0);
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

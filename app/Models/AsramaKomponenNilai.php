<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AsramaKomponenNilai extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'asrama_komponen_nilai';

    protected $fillable = [
        'tahun_pelajaran_id',
        'kode',
        'nama',
        'bobot',
        'urutan',
        'is_active',
        'keterangan',
    ];

    protected $casts = [
        'bobot' => 'decimal:2',
        'urutan' => 'integer',
        'is_active' => 'boolean',
    ];

    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('urutan')->orderBy('kode');
    }

    /**
     * Standard default components template.
     * Total bobot: 100%
     */
    public static function getDefaultDefinitions(): array
    {
        return [
            [
                'kode' => 'HARIAN',
                'nama' => 'Tugas & Formatif Harian',
                'bobot' => 30.00,
                'urutan' => 1,
                'is_active' => true,
                'keterangan' => 'Nilai tugas harian, keaktifan pembelajaran, dan kuis berkala.',
            ],
            [
                'kode' => 'ATS',
                'nama' => 'Asesmen Tengah Semester (ATS)',
                'bobot' => 30.00,
                'urutan' => 2,
                'is_active' => true,
                'keterangan' => 'Ujian atau asesmen tertulis/praktik tengah semester.',
            ],
            [
                'kode' => 'AAS',
                'nama' => 'Asesmen Akhir Semester (AAS)',
                'bobot' => 40.00,
                'urutan' => 3,
                'is_active' => true,
                'keterangan' => 'Ujian atau asesmen komprehensif akhir semester.',
            ],
        ];
    }

    /**
     * Ensure the given academic year has grade components.
     * If none exist (neither active nor soft-deleted), generate standard defaults automatically.
     */
    public static function ensureDefaultsForYear(string $tahunPelajaranId)
    {
        $existing = static::where('tahun_pelajaran_id', $tahunPelajaranId)->active()->ordered()->get();
        if ($existing->isNotEmpty()) {
            return $existing;
        }

        // Check if there are any records including soft-deleted ones
        $hasAny = static::withTrashed()->where('tahun_pelajaran_id', $tahunPelajaranId)->exists();
        if ($hasAny) {
            // If user explicitly deleted everything or deactivated, return empty collection to respect custom configuration
            return $existing;
        }

        return static::createDefaultTemplate($tahunPelajaranId);
    }

    /**
     * Create or restore standard default components for an academic year.
     */
    public static function createDefaultTemplate(string $tahunPelajaranId, bool $force = false)
    {
        if ($force) {
            static::where('tahun_pelajaran_id', $tahunPelajaranId)->delete();
        }

        foreach (static::getDefaultDefinitions() as $def) {
            static::firstOrCreate(
                [
                    'tahun_pelajaran_id' => $tahunPelajaranId,
                    'kode' => $def['kode'],
                ],
                $def
            );
        }

        return static::where('tahun_pelajaran_id', $tahunPelajaranId)
            ->active()
            ->ordered()
            ->get();
    }
}

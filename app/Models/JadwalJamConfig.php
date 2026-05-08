<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JadwalJamConfig extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'jadwal_jam_config';

    protected $fillable = [
        'tahun_pelajaran_id',
        'urutan',
        'jam_ke',
        'waktu_mulai',
        'waktu_selesai',
        'is_istirahat',
        'label',
    ];

    protected $casts = [
        'urutan'      => 'integer',
        'jam_ke'      => 'integer',
        'is_istirahat' => 'boolean',
    ];

    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function getWaktuAttribute(): string
    {
        $mulai   = $this->waktu_mulai   ? substr($this->waktu_mulai, 0, 5)   : '';
        $selesai = $this->waktu_selesai ? substr($this->waktu_selesai, 0, 5) : '';
        return $mulai && $selesai ? "{$mulai} – {$selesai}" : $mulai;
    }

    public function getLabelDisplayAttribute(): string
    {
        if ($this->is_istirahat) {
            return $this->label ?: 'Istirahat';
        }
        return 'Jam ke-' . $this->jam_ke;
    }

    /**
     * Generate baris jam config dari parameter. Return array rows, tidak disimpan.
     *
     * @param string $jamMulai    "07:00"
     * @param int    $durasiMenit  45
     * @param array  $istirahat   [["setelah_jam"=>3,"durasi"=>15,"label"=>"Istirahat"], ...]
     * @param string $jamPulang   "14:30" — generate jam hingga tidak melebihi jam pulang
     */
    public static function generateRows(string $jamMulai, int $durasiMenit, array $istirahat, string $jamPulang): array
    {
        $rows   = [];
        $urutan = 1;
        $jamKe  = 1;

        [$h,  $m]  = explode(':', $jamMulai);
        $current   = (int)$h * 60 + (int)$m;

        [$hp, $mp] = explode(':', $jamPulang);
        $pulang    = (int)$hp * 60 + (int)$mp;

        $breakMap = [];
        foreach ($istirahat as $b) {
            if (!empty($b['setelah_jam']) && !empty($b['durasi'])) {
                $breakMap[(int)$b['setelah_jam']] = $b;
            }
        }

        $iJam   = 1;
        $maxJam = 20;

        while ($iJam <= $maxJam) {
            if ($current + $durasiMenit > $pulang) {
                break;
            }

            $mulai   = sprintf('%02d:%02d', intdiv($current, 60), $current % 60);
            $current += $durasiMenit;
            $selesai = sprintf('%02d:%02d', intdiv($current, 60), $current % 60);

            $rows[] = [
                'urutan'        => $urutan++,
                'jam_ke'        => $jamKe++,
                'waktu_mulai'   => $mulai,
                'waktu_selesai' => $selesai,
                'is_istirahat'  => false,
                'label'         => null,
            ];

            if (isset($breakMap[$iJam])) {
                $break    = $breakMap[$iJam];
                $durasi   = (int)$break['durasi'];
                $bMulai   = $selesai;
                $current += $durasi;
                $bSelesai = sprintf('%02d:%02d', intdiv($current, 60), $current % 60);

                $rows[] = [
                    'urutan'        => $urutan++,
                    'jam_ke'        => null,
                    'waktu_mulai'   => $bMulai,
                    'waktu_selesai' => $bSelesai,
                    'is_istirahat'  => true,
                    'label'         => $break['label'] ?? 'Istirahat',
                ];
            }

            $iJam++;
        }

        return $rows;
    }
}
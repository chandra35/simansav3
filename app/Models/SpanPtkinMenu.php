<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpanPtkinMenu extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'nama_menu',
        'tahun_pelajaran_id',
        'konten_informasi',
        'is_active',
        'tanggal_mulai',
        'tanggal_berakhir',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'tanggal_mulai' => 'datetime',
        'tanggal_berakhir' => 'datetime',
    ];

    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class);
    }

    public function registrations()
    {
        return $this->hasMany(SpanPtkinRegistration::class);
    }

    public function isWithinPeriod(): bool
    {
        $now = now();

        if (!$this->tanggal_mulai && !$this->tanggal_berakhir) {
            return true;
        }

        if ($this->tanggal_mulai && $now->lt($this->tanggal_mulai)) {
            return false;
        }

        if ($this->tanggal_berakhir && $now->gt($this->tanggal_berakhir)) {
            return false;
        }

        return true;
    }

    public function getCountdownData(): ?array
    {
        $now = now();

        if ($this->tanggal_mulai && $now->lt($this->tanggal_mulai)) {
            return [
                'type' => 'not_started',
                'target' => $this->tanggal_mulai->toIso8601String(),
                'message' => 'Informasi akan ditampilkan dalam',
            ];
        }

        if ($this->tanggal_berakhir && $now->lt($this->tanggal_berakhir)) {
            return [
                'type' => 'active',
                'target' => $this->tanggal_berakhir->toIso8601String(),
                'message' => 'Informasi berakhir dalam',
            ];
        }

        if ($this->tanggal_berakhir && $now->gt($this->tanggal_berakhir)) {
            return [
                'type' => 'ended',
                'target' => null,
                'message' => 'Periode informasi telah berakhir',
            ];
        }

        return null;
    }

    public function isEditable(): bool
    {
        return (bool) ($this->tahunPelajaran && $this->tahunPelajaran->is_active);
    }

    public static function getActiveMenu(): ?self
    {
        $activeTahun = TahunPelajaran::where('is_active', true)->first();
        if (!$activeTahun) {
            return null;
        }

        return self::where('tahun_pelajaran_id', $activeTahun->id)
            ->where('is_active', true)
            ->first();
    }
}

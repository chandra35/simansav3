<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SnbpMenu extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'nama_menu',
        'tahun_pelajaran_id',
        'konten_eligible',
        'konten_not_eligible',
        'is_active',
        'tanggal_mulai',
        'tanggal_berakhir',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'tanggal_mulai' => 'datetime',
        'tanggal_berakhir' => 'datetime',
    ];

    /**
     * Check if content is within the display period
     */
    public function isWithinPeriod()
    {
        $now = now();
        
        // If no dates set, always show
        if (!$this->tanggal_mulai && !$this->tanggal_berakhir) {
            return true;
        }
        
        // Check start date
        if ($this->tanggal_mulai && $now->lt($this->tanggal_mulai)) {
            return false;
        }
        
        // Check end date
        if ($this->tanggal_berakhir && $now->gt($this->tanggal_berakhir)) {
            return false;
        }
        
        return true;
    }

    /**
     * Get countdown data for frontend
     */
    public function getCountdownData()
    {
        $now = now();
        
        // If hasn't started yet
        if ($this->tanggal_mulai && $now->lt($this->tanggal_mulai)) {
            return [
                'type' => 'not_started',
                'target' => $this->tanggal_mulai->toIso8601String(),
                'message' => 'Informasi akan ditampilkan dalam',
            ];
        }
        
        // If active and has end date
        if ($this->tanggal_berakhir && $now->lt($this->tanggal_berakhir)) {
            return [
                'type' => 'active',
                'target' => $this->tanggal_berakhir->toIso8601String(),
                'message' => 'Informasi berakhir dalam',
            ];
        }
        
        // If ended
        if ($this->tanggal_berakhir && $now->gt($this->tanggal_berakhir)) {
            return [
                'type' => 'ended',
                'target' => null,
                'message' => 'Periode informasi telah berakhir',
            ];
        }
        
        return null;
    }

    /**
     * Get the tahun pelajaran
     */
    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class);
    }

    /**
     * Get all assigned siswa (eligible and not eligible)
     */
    public function siswaAssignments()
    {
        return $this->hasMany(SnbpSiswa::class);
    }

    /**
     * Get eligible siswa
     */
    public function eligibleSiswa()
    {
        return $this->belongsToMany(Siswa::class, 'snbp_siswa')
                    ->wherePivot('is_eligible', true)
                    ->withPivot('id', 'is_eligible', 'created_at', 'updated_at')
                    ->withTimestamps();
    }

    /**
     * Get not eligible siswa
     */
    public function notEligibleSiswa()
    {
        return $this->belongsToMany(Siswa::class, 'snbp_siswa')
                    ->wherePivot('is_eligible', false)
                    ->withPivot('id', 'is_eligible', 'created_at', 'updated_at')
                    ->withTimestamps();
    }

    /**
     * Get all assigned siswa
     */
    public function allAssignedSiswa()
    {
        return $this->belongsToMany(Siswa::class, 'snbp_siswa')
                    ->withPivot('id', 'is_eligible', 'created_at', 'updated_at')
                    ->withTimestamps();
    }

    /**
     * Check if tahun pelajaran is active (editable)
     */
    public function isEditable()
    {
        return $this->tahunPelajaran && $this->tahunPelajaran->is_active;
    }

    /**
     * Get the active menu for current tahun pelajaran
     */
    public static function getActiveMenu()
    {
        $activeTahun = TahunPelajaran::where('is_active', true)->first();
        if (!$activeTahun) return null;

        return self::where('tahun_pelajaran_id', $activeTahun->id)
                   ->where('is_active', true)
                   ->first();
    }

    /**
     * Check if a siswa is eligible
     */
    public function isSiswaEligible($siswaId)
    {
        return $this->siswaAssignments()
                    ->where('siswa_id', $siswaId)
                    ->where('is_eligible', true)
                    ->exists();
    }

    /**
     * Check if a siswa is assigned (either eligible or not)
     */
    public function isSiswaAssigned($siswaId)
    {
        return $this->siswaAssignments()
                    ->where('siswa_id', $siswaId)
                    ->exists();
    }

    /**
     * Get siswa assignment status
     * Returns: true (eligible), false (not eligible), or null (not assigned)
     */
    public function getSiswaStatus($siswaId)
    {
        $assignment = $this->siswaAssignments()
                           ->where('siswa_id', $siswaId)
                           ->first();
        
        if (!$assignment) return null;
        
        return $assignment->is_eligible;
    }
}

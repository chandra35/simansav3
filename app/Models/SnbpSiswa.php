<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SnbpSiswa extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'snbp_siswa';

    protected $fillable = [
        'snbp_menu_id',
        'siswa_id',
        'is_eligible',
    ];

    protected $casts = [
        'is_eligible' => 'boolean',
    ];

    /**
     * Get the SNBP menu
     */
    public function snbpMenu()
    {
        return $this->belongsTo(SnbpMenu::class);
    }

    /**
     * Get the siswa
     */
    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}

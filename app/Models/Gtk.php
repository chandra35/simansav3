<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Laravolt\Indonesia\Models\Province;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Village;

class Gtk extends Model
{
    use HasFactory;

    protected $table = 'gtks';
    
    // UUID sebagai primary key
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'nama_lengkap',
        'nik',
        'nuptk',
        'nip',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'email',
        'nomor_hp',
        'alamat',
        'rt',
        'rw',
        'provinsi_id',
        'kabupaten_id',
        'kecamatan_id',
        'kelurahan_id',
        'kodepos',
        'kategori_ptk',
        'jenis_ptk',
        'status_kepegawaian',
        'jabatan',
        'tmt_kerja',
        'data_diri_completed',
        'data_kepegawaian_completed',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tmt_kerja' => 'date',
        'data_diri_completed' => 'boolean',
        'data_kepegawaian_completed' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Relasi dengan User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi dengan wilayah
     */
    public function provinsi()
    {
        return $this->belongsTo(Province::class, 'provinsi_id', 'code');
    }

    public function kabupaten()
    {
        return $this->belongsTo(City::class, 'kabupaten_id', 'code');
    }

    public function kecamatan()
    {
        return $this->belongsTo(District::class, 'kecamatan_id', 'code');
    }

    public function kelurahan()
    {
        return $this->belongsTo(Village::class, 'kelurahan_id', 'code');
    }

    /**
     * Accessor untuk data_kepeg_completed (alias)
     */
    public function getDataKepegCompletedAttribute()
    {
        return $this->attributes['data_kepegawaian_completed'] ?? false;
    }

    /**
     * Accessor untuk alamat lengkap
     */
    public function getAlamatLengkapAttribute()
    {
        $parts = array_filter([
            $this->alamat,
            $this->rt ? "RT {$this->rt}" : null,
            $this->rw ? "RW {$this->rw}" : null,
            $this->kelurahan?->name,
            $this->kecamatan?->name,
            $this->kabupaten?->name,
            $this->provinsi?->name,
            $this->kodepos,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Accessor untuk umur
     */
    public function getUmurAttribute()
    {
        if (!$this->tanggal_lahir) {
            return null;
        }
        
        return $this->tanggal_lahir->age;
    }

    /**
     * Scope untuk filter berdasarkan jenis kelamin
     */
    public function scopeJenisKelamin($query, $jenisKelamin)
    {
        return $query->where('jenis_kelamin', $jenisKelamin);
    }

    /**
     * Scope untuk filter berdasarkan status kepegawaian
     */
    public function scopeStatusKepegawaian($query, $status)
    {
        return $query->where('status_kepegawaian', $status);
    }

    /**
     * Relasi dengan creator dan updater
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

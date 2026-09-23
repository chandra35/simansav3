<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BukuTamu extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'buku_tamu';

    protected $fillable = [
        'tanggal_kunjungan',
        'jenis_tamu',
        'nama',
        'alamat_instansi',
        'nomor_hp',
        'alamat',
        'provinsi_code',
        'kota_code',
        'kecamatan_code',
        'kelurahan_code',
        'buku_tamu_qr_token_id',
        'keperluan',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'tanggal_kunjungan' => 'date',
    ];

    public function qrToken()
    {
        return $this->belongsTo(BukuTamuQrToken::class, 'buku_tamu_qr_token_id');
    }

    public function provinsi()
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\Province::class, 'provinsi_code', 'code');
    }

    public function kota()
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\City::class, 'kota_code', 'code');
    }

    public function kecamatan()
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\District::class, 'kecamatan_code', 'code');
    }

    public function kelurahan()
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\Village::class, 'kelurahan_code', 'code');
    }

    public function getAlamatLengkapAttribute(): string
    {
        return implode(', ', array_filter([
            $this->alamat,
            $this->kelurahan?->name,
            $this->kecamatan?->name,
            $this->kota?->name,
            $this->provinsi?->name,
        ]));
    }
}

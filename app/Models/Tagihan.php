<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tagihan extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'tagihan';

    protected $fillable = [
        'jenis_pembayaran_id',
        'siswa_id',
        'tahun_pelajaran_id',
        'bulan',
        'tahun',
        'nominal_tagihan',
        'nominal_terbayar',
        'tanggal_jatuh_tempo',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'bulan' => 'integer',
        'tahun' => 'integer',
        'nominal_tagihan' => 'decimal:2',
        'nominal_terbayar' => 'decimal:2',
        'tanggal_jatuh_tempo' => 'date',
    ];

    const STATUS = [
        'belum_bayar' => 'Belum Bayar',
        'cicilan' => 'Cicilan',
        'lunas' => 'Lunas',
    ];

    const BULAN = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    // Relations
    public function jenisPembayaran()
    {
        return $this->belongsTo(JenisPembayaran::class, 'jenis_pembayaran_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'tagihan_id');
    }

    // Scopes
    public function scopeBelumLunas($query)
    {
        return $query->whereIn('status', ['belum_bayar', 'cicilan']);
    }

    public function scopeLunas($query)
    {
        return $query->where('status', 'lunas');
    }

    public function scopeJatuhTempo($query)
    {
        return $query->whereDate('tanggal_jatuh_tempo', '<=', now())
            ->whereIn('status', ['belum_bayar', 'cicilan']);
    }

    // Accessors
    public function getSisaTagihanAttribute()
    {
        return $this->nominal_tagihan - $this->nominal_terbayar;
    }

    public function getBulanLabelAttribute()
    {
        return self::BULAN[$this->bulan] ?? $this->bulan;
    }

    public function getStatusLabelAttribute()
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'belum_bayar' => 'danger',
            'cicilan' => 'warning',
            'lunas' => 'success',
        ];
        return $badges[$this->status] ?? 'secondary';
    }

    public function getNominalTagihanFormatAttribute()
    {
        return 'Rp ' . number_format($this->nominal_tagihan, 0, ',', '.');
    }

    public function getNominalTerbayarFormatAttribute()
    {
        return 'Rp ' . number_format($this->nominal_terbayar, 0, ',', '.');
    }

    public function getSisaTagihanFormatAttribute()
    {
        return 'Rp ' . number_format($this->sisa_tagihan, 0, ',', '.');
    }

    // Update status based on pembayaran
    public function updateStatus()
    {
        $totalBayar = $this->pembayaran()->verified()->sum('jumlah_bayar');
        $this->nominal_terbayar = $totalBayar;

        if ($totalBayar >= $this->nominal_tagihan) {
            $this->status = 'lunas';
        } elseif ($totalBayar > 0) {
            $this->status = 'cicilan';
        } else {
            $this->status = 'belum_bayar';
        }

        $this->save();
    }
}

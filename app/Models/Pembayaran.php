<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pembayaran extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'pembayaran';

    protected $fillable = [
        'tagihan_id',
        'nomor_transaksi',
        'jumlah_bayar',
        'metode_pembayaran',
        'tanggal_bayar',
        'bukti_pembayaran',
        'catatan',
        'status',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'jumlah_bayar' => 'decimal:2',
        'tanggal_bayar' => 'date',
        'verified_at' => 'datetime',
    ];

    const METODE = [
        'tunai' => 'Tunai',
        'transfer' => 'Transfer Bank',
        'qris' => 'QRIS',
        'virtual_account' => 'Virtual Account',
    ];

    const STATUS = [
        'pending' => 'Pending',
        'verified' => 'Terverifikasi',
        'rejected' => 'Ditolak',
    ];

    // Relations
    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class, 'tagihan_id');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Generate nomor transaksi
    public static function generateNomorTransaksi()
    {
        $prefix = 'PAY';
        $date = now()->format('Ymd');
        $lastNumber = self::whereDate('created_at', today())
            ->count() + 1;
        return sprintf('%s-%s-%04d', $prefix, $date, $lastNumber);
    }

    // Accessors
    public function getMetodeLabelAttribute()
    {
        return self::METODE[$this->metode_pembayaran] ?? $this->metode_pembayaran;
    }

    public function getStatusLabelAttribute()
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'warning',
            'verified' => 'success',
            'rejected' => 'danger',
        ];
        return $badges[$this->status] ?? 'secondary';
    }
}

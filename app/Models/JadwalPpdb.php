<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class JadwalPpdb extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'jadwal_ppdb';

    /**
     * Indicates if the model's ID is auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The data type of the auto-incrementing ID.
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nama_kegiatan',
        'deskripsi',
        'tanggal_mulai',
        'tanggal_selesai',
        'warna',
        'icon',
        'urutan',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'tanggal_mulai' => 'datetime',
        'tanggal_selesai' => 'datetime',
        'is_active' => 'boolean',
        'urutan' => 'integer',
    ];

    /**
     * Bootstrap the model and its traits.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    /**
     * Scope a query to only include active jadwal.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include upcoming jadwal.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('tanggal_selesai', '>=', now());
    }

    /**
     * Scope a query to only include ongoing jadwal.
     */
    public function scopeOngoing($query)
    {
        return $query->where('tanggal_mulai', '<=', now())
                     ->where('tanggal_selesai', '>=', now());
    }

    /**
     * Scope a query to only include past jadwal.
     */
    public function scopePast($query)
    {
        return $query->where('tanggal_selesai', '<', now());
    }

    /**
     * Scope a query to order by urutan.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan')->orderBy('tanggal_mulai');
    }

    /**
     * Get formatted tanggal mulai.
     */
    public function getFormattedTanggalMulaiAttribute()
    {
        return $this->tanggal_mulai->format('d M Y');
    }

    /**
     * Get formatted tanggal selesai.
     */
    public function getFormattedTanggalSelesaiAttribute()
    {
        return $this->tanggal_selesai->format('d M Y');
    }

    /**
     * Get formatted date range.
     */
    public function getDateRangeAttribute()
    {
        if ($this->tanggal_mulai->isSameDay($this->tanggal_selesai)) {
            return $this->tanggal_mulai->format('d M Y');
        }
        
        if ($this->tanggal_mulai->isSameMonth($this->tanggal_selesai)) {
            return $this->tanggal_mulai->format('d') . ' - ' . $this->tanggal_selesai->format('d M Y');
        }
        
        return $this->tanggal_mulai->format('d M') . ' - ' . $this->tanggal_selesai->format('d M Y');
    }

    /**
     * Check if jadwal is currently ongoing.
     */
    public function isOngoing()
    {
        return now()->between($this->tanggal_mulai, $this->tanggal_selesai);
    }

    /**
     * Check if jadwal is upcoming.
     */
    public function isUpcoming()
    {
        return now()->lt($this->tanggal_mulai);
    }

    /**
     * Check if jadwal is past.
     */
    public function isPast()
    {
        return now()->gt($this->tanggal_selesai);
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute()
    {
        if ($this->isOngoing()) {
            return 'Sedang Berlangsung';
        } elseif ($this->isUpcoming()) {
            return 'Akan Datang';
        } else {
            return 'Selesai';
        }
    }

    /**
     * Get status color class.
     */
    public function getStatusColorAttribute()
    {
        if ($this->isOngoing()) {
            return 'success';
        } elseif ($this->isUpcoming()) {
            return 'info';
        } else {
            return 'secondary';
        }
    }
}

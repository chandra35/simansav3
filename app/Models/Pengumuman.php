<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pengumuman extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'pengumuman';

    protected $fillable = [
        'judul',
        'isi',
        'kategori',
        'prioritas',
        'target',
        'gambar',
        'lampiran',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_pinned',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'is_pinned' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('tanggal_mulai', '<=', now())
            ->where(function ($q) {
                $q->whereNull('tanggal_selesai')
                    ->orWhere('tanggal_selesai', '>=', now());
            });
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeForTarget($query, $target)
    {
        return $query->whereIn('target', ['semua', $target]);
    }

    // Relations
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Accessors
    public function getPrioritasBadgeAttribute()
    {
        return match($this->prioritas) {
            'urgent' => 'danger',
            'tinggi' => 'warning',
            'normal' => 'info',
            'rendah' => 'secondary',
            default => 'secondary'
        };
    }

    public function getKategoriBadgeAttribute()
    {
        return match($this->kategori) {
            'penting' => 'danger',
            'akademik' => 'primary',
            'kegiatan' => 'success',
            'umum' => 'info',
            default => 'secondary'
        };
    }
}

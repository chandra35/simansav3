<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KalenderAkademik extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'kalender_akademik';

    protected $fillable = [
        'tahun_pelajaran_id',
        'nama_kegiatan',
        'deskripsi',
        'kategori',
        'warna',
        'tanggal_mulai',
        'tanggal_selesai',
        'waktu_mulai',
        'waktu_selesai',
        'lokasi',
        'is_libur',
        'is_recurring',
        'recurring_type',
        'created_by',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'waktu_mulai' => 'datetime:H:i',
        'waktu_selesai' => 'datetime:H:i',
        'is_libur' => 'boolean',
        'is_recurring' => 'boolean',
    ];

    const KATEGORI = [
        'akademik' => 'Akademik',
        'libur' => 'Libur',
        'kegiatan' => 'Kegiatan',
        'ujian' => 'Ujian',
        'rapat' => 'Rapat',
        'lainnya' => 'Lainnya',
    ];

    const WARNA = [
        'akademik' => '#3788d8',
        'libur' => '#dc3545',
        'kegiatan' => '#28a745',
        'ujian' => '#ffc107',
        'rapat' => '#17a2b8',
        'lainnya' => '#6c757d',
    ];

    // Relations
    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeLibur($query)
    {
        return $query->where('is_libur', true);
    }

    public function scopeKategori($query, $kategori)
    {
        return $query->where('kategori', $kategori);
    }

    public function scopeBulan($query, $bulan, $tahun)
    {
        return $query->whereMonth('tanggal_mulai', $bulan)
            ->whereYear('tanggal_mulai', $tahun);
    }

    // For FullCalendar
    public function toCalendarEvent()
    {
        return [
            'id' => $this->id,
            'title' => $this->nama_kegiatan,
            'start' => $this->tanggal_mulai->format('Y-m-d') . ($this->waktu_mulai ? 'T' . $this->waktu_mulai->format('H:i:s') : ''),
            'end' => $this->tanggal_selesai ? $this->tanggal_selesai->format('Y-m-d') . ($this->waktu_selesai ? 'T' . $this->waktu_selesai->format('H:i:s') : '') : null,
            'color' => $this->warna,
            'allDay' => !$this->waktu_mulai,
            'extendedProps' => [
                'kategori' => $this->kategori,
                'lokasi' => $this->lokasi,
                'deskripsi' => $this->deskripsi,
                'is_libur' => $this->is_libur,
            ],
        ];
    }
}

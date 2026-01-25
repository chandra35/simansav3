<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TemplateSurat extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'template_surat';

    protected $fillable = [
        'nama',
        'kode',
        'kategori',
        'template_content',
        'variabel',
        'keterangan',
        'is_aktif',
    ];

    protected $casts = [
        'variabel' => 'array',
        'is_aktif' => 'boolean',
    ];

    const KATEGORI = [
        'keterangan_aktif' => 'Keterangan Aktif',
        'keterangan_lulus' => 'Keterangan Lulus',
        'keterangan_pindah' => 'Keterangan Pindah',
        'keterangan_berkelakuan_baik' => 'Keterangan Berkelakuan Baik',
        'rekomendasi' => 'Surat Rekomendasi',
        'lainnya' => 'Lainnya',
    ];

    // Default variables available
    const DEFAULT_VARIABEL = [
        '{{nama_siswa}}' => 'Nama Siswa',
        '{{nisn}}' => 'NISN',
        '{{nis}}' => 'NIS',
        '{{tempat_lahir}}' => 'Tempat Lahir',
        '{{tanggal_lahir}}' => 'Tanggal Lahir',
        '{{kelas}}' => 'Kelas',
        '{{alamat}}' => 'Alamat',
        '{{nama_ayah}}' => 'Nama Ayah',
        '{{nama_ibu}}' => 'Nama Ibu',
        '{{nomor_surat}}' => 'Nomor Surat',
        '{{tanggal_surat}}' => 'Tanggal Surat',
        '{{nama_madrasah}}' => 'Nama Madrasah',
        '{{alamat_madrasah}}' => 'Alamat Madrasah',
        '{{nama_kepala_madrasah}}' => 'Nama Kepala Madrasah',
        '{{nip_kepala_madrasah}}' => 'NIP Kepala Madrasah',
    ];

    // Relations
    public function suratKeterangan()
    {
        return $this->hasMany(SuratKeterangan::class, 'template_surat_id');
    }

    // Scopes
    public function scopeAktif($query)
    {
        return $query->where('is_aktif', true);
    }

    public function scopeKategori($query, $kategori)
    {
        return $query->where('kategori', $kategori);
    }

    // Accessors
    public function getKategoriLabelAttribute()
    {
        return self::KATEGORI[$this->kategori] ?? $this->kategori;
    }

    // Replace variables in template
    public function generateContent($siswa, $additionalData = [])
    {
        $content = $this->template_content;
        
        // Basic student data
        $replacements = [
            '{{nama_siswa}}' => $siswa->nama ?? '',
            '{{nisn}}' => $siswa->nisn ?? '',
            '{{nis}}' => $siswa->nis ?? '',
            '{{tempat_lahir}}' => $siswa->tempat_lahir ?? '',
            '{{tanggal_lahir}}' => $siswa->tanggal_lahir?->format('d F Y') ?? '',
            '{{kelas}}' => $siswa->kelasSaatIni?->nama ?? '',
            '{{alamat}}' => $siswa->alamat ?? '',
            '{{nama_ayah}}' => $siswa->ayah?->nama ?? '',
            '{{nama_ibu}}' => $siswa->ibu?->nama ?? '',
        ];

        // Merge with additional data
        $replacements = array_merge($replacements, $additionalData);

        foreach ($replacements as $key => $value) {
            $content = str_replace($key, $value, $content);
        }

        return $content;
    }
}

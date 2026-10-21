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
        'jenis',
        'konten',
        'kop_surat',
        'ttd_path',
        'stempel_path',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    const JENIS = [
        'sk_aktif' => 'Surat Keterangan Aktif',
        'sk_berkelakuan_baik' => 'Surat Berkelakuan Baik',
        'sk_pindah' => 'Surat Pindah',
        'sk_lulus' => 'Surat Keterangan Lulus',
        'sk_domisili' => 'Surat Domisili',
        'sk_tidak_mampu' => 'Surat Tidak Mampu',
        'sk_rekomendasi' => 'Surat Rekomendasi',
        'sk_mutasi' => 'Surat Mutasi',
        'sk_izin' => 'Surat Izin',
        'sk_lainnya' => 'Lainnya',
    ];

    // Default variables available
    const DEFAULT_VARIABEL = [
        '{{nama_pejabat}}' => 'Nama Pejabat Penandatangan',
        '{{nip_pejabat}}' => 'NIP Pejabat',
        '{{pangkat_golongan_pejabat}}' => 'Pangkat / Golongan Pejabat',
        '{{jabatan_pejabat}}' => 'Jabatan Pejabat',
        '{{instansi_pejabat}}' => 'Instansi Pejabat',
        '{{nama_siswa}}' => 'Nama Siswa',
        '{{nisn}}' => 'NISN',
        '{{nis}}' => 'NIS',
        '{{tempat_lahir}}' => 'Tempat Lahir',
        '{{tanggal_lahir}}' => 'Tanggal Lahir',
        '{{nama_ayah}}' => 'Nama Ayah',
        '{{nama_ibu}}' => 'Nama Ibu',
        '{{nama_sekolah}}' => 'Nama Madrasah',
        '{{kelas}}' => 'Kelas Siswa',
        '{{tahun_pelajaran}}' => 'Tahun Pelajaran',
        '{{keperluan}}' => 'Keperluan Surat',
        '{{nomor_surat}}' => 'Nomor Surat',
        '{{tanggal_surat}}' => 'Tanggal Surat',
        '{{kota_surat}}' => 'Kota Surat',
    ];

    // Relations
    public function suratKeterangan()
    {
        return $this->hasMany(SuratKeterangan::class, 'template_surat_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeJenis($query, $jenis)
    {
        return $query->where('jenis', $jenis);
    }

    // Accessors
    public function getJenisLabelAttribute()
    {
        return self::JENIS[$this->jenis] ?? $this->jenis;
    }

    public function getKategoriLabelAttribute()
    {
        return $this->jenis_label;
    }

    public function getTemplateContentAttribute()
    {
        return $this->konten;
    }

    public function setTemplateContentAttribute($value)
    {
        $this->attributes['konten'] = $value;
    }

    public function getIsAktifAttribute()
    {
        return $this->is_active;
    }

    public function setIsAktifAttribute($value)
    {
        $this->attributes['is_active'] = $value;
    }

    // Replace variables in template
    public function generateContent(array $replacements = []): string
    {
        $content = $this->konten ?? '';
        foreach ($replacements as $key => $value) {
            $content = str_replace($key, (string) $value, $content);
        }
        return $content;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DokumenPendaftaran extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'dokumen_pendaftaran';

    protected $fillable = [
        'pendaftaran_id',
        'jenis_dokumen',
        'nama_file',
        'path_file',
        'ukuran_file',
        'mime_type',
        'status_verifikasi',
        'catatan',
        'diverifikasi_oleh',
        'tanggal_verifikasi',
    ];

    protected $casts = [
        'tanggal_verifikasi' => 'datetime',
        'ukuran_file' => 'integer',
    ];

    // Jenis dokumen constants
    const JENIS_KK = 'kartu_keluarga';
    const JENIS_AKTA = 'akta_kelahiran';
    const JENIS_IJAZAH = 'ijazah';
    const JENIS_SKHUN = 'skhun';
    const JENIS_RAPOR = 'rapor';
    const JENIS_SKL = 'skl';
    const JENIS_PAS_FOTO = 'pas_foto';
    const JENIS_SURAT_KETERANGAN = 'surat_keterangan';
    const JENIS_PRESTASI = 'sertifikat_prestasi';
    const JENIS_SKTM = 'sktm';
    const JENIS_KIP = 'kip';
    const JENIS_LAINNYA = 'lainnya';

    // Status verifikasi constants
    const STATUS_PENDING = 'pending';
    const STATUS_VALID = 'valid';
    const STATUS_INVALID = 'invalid';
    const STATUS_REUPLOAD = 'reupload';

    /**
     * Get all jenis dokumen
     */
    public static function getJenisDokumenList(): array
    {
        return [
            self::JENIS_KK => [
                'nama' => 'Kartu Keluarga',
                'wajib' => true,
                'deskripsi' => 'Scan/foto Kartu Keluarga yang masih berlaku',
            ],
            self::JENIS_AKTA => [
                'nama' => 'Akta Kelahiran',
                'wajib' => true,
                'deskripsi' => 'Scan/foto Akta Kelahiran',
            ],
            self::JENIS_IJAZAH => [
                'nama' => 'Ijazah SMP/MTs',
                'wajib' => false,
                'deskripsi' => 'Scan/foto Ijazah SMP/MTs atau sederajat (bisa menyusul)',
            ],
            self::JENIS_SKHUN => [
                'nama' => 'SKHUN',
                'wajib' => false,
                'deskripsi' => 'Scan/foto SKHUN (bisa menyusul)',
            ],
            self::JENIS_RAPOR => [
                'nama' => 'Rapor Semester Terakhir',
                'wajib' => true,
                'deskripsi' => 'Scan/foto halaman nilai rapor semester 1-5',
            ],
            self::JENIS_SKL => [
                'nama' => 'Surat Keterangan Lulus',
                'wajib' => false,
                'deskripsi' => 'Scan/foto SKL dari sekolah asal',
            ],
            self::JENIS_PAS_FOTO => [
                'nama' => 'Pas Foto 3x4',
                'wajib' => true,
                'deskripsi' => 'Pas foto terbaru ukuran 3x4 cm, background merah',
            ],
            self::JENIS_PRESTASI => [
                'nama' => 'Sertifikat Prestasi',
                'wajib' => false,
                'deskripsi' => 'Scan/foto sertifikat prestasi akademik/non-akademik (jika ada)',
            ],
            self::JENIS_SKTM => [
                'nama' => 'SKTM',
                'wajib' => false,
                'deskripsi' => 'Surat Keterangan Tidak Mampu (untuk jalur afirmasi)',
            ],
            self::JENIS_KIP => [
                'nama' => 'Kartu Indonesia Pintar',
                'wajib' => false,
                'deskripsi' => 'Scan/foto KIP jika memiliki',
            ],
        ];
    }

    /**
     * Get wajib dokumen list
     */
    public static function getDokumenWajib(): array
    {
        return array_filter(self::getJenisDokumenList(), fn($doc) => $doc['wajib']);
    }

    /**
     * Get jenis dokumen label
     */
    public function getJenisDokumenLabelAttribute(): string
    {
        $list = self::getJenisDokumenList();
        return $list[$this->jenis_dokumen]['nama'] ?? $this->jenis_dokumen;
    }

    /**
     * Get status verifikasi label
     */
    public function getStatusVerifikasiLabelAttribute(): string
    {
        $labels = [
            self::STATUS_PENDING => 'Menunggu Verifikasi',
            self::STATUS_VALID => 'Valid',
            self::STATUS_INVALID => 'Tidak Valid',
            self::STATUS_REUPLOAD => 'Perlu Upload Ulang',
        ];
        
        return $labels[$this->status_verifikasi] ?? 'Unknown';
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            self::STATUS_PENDING => 'warning',
            self::STATUS_VALID => 'success',
            self::STATUS_INVALID => 'danger',
            self::STATUS_REUPLOAD => 'info',
        ];
        
        return $badges[$this->status_verifikasi] ?? 'secondary';
    }

    /**
     * Get file URL
     */
    public function getFileUrlAttribute(): ?string
    {
        if ($this->path_file) {
            return asset('storage/' . $this->path_file);
        }
        return null;
    }

    /**
     * Get formatted file size
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->ukuran_file;
        
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        
        return $bytes . ' bytes';
    }

    /**
     * Relationship to pendaftaran
     */
    public function pendaftaran()
    {
        return $this->belongsTo(PendaftaranPpdb::class, 'pendaftaran_id');
    }

    /**
     * Relationship to verifier
     */
    public function verifier()
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    /**
     * Scope for specific jenis dokumen
     */
    public function scopeOfJenis($query, string $jenis)
    {
        return $query->where('jenis_dokumen', $jenis);
    }

    /**
     * Scope for pending verification
     */
    public function scopePending($query)
    {
        return $query->where('status_verifikasi', self::STATUS_PENDING);
    }

    /**
     * Scope for valid documents
     */
    public function scopeValid($query)
    {
        return $query->where('status_verifikasi', self::STATUS_VALID);
    }

    /**
     * Check if document is an image
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    /**
     * Check if document is a PDF
     */
    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }
}

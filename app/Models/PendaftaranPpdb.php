<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendaftaranPpdb extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'pendaftaran_ppdb';

    protected $fillable = [
        'nomor_pendaftaran',
        'tahun_pelajaran_id',
        'nisn',
        'nik',
        'nama_lengkap',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'agama',
        'alamat',
        'rt',
        'rw',
        'kelurahan',
        'kecamatan',
        'kabupaten',
        'provinsi',
        'kode_pos',
        'no_hp',
        'email',
        'asal_sekolah',
        'npsn_asal_sekolah',
        'alamat_asal_sekolah',
        'tahun_lulus',
        'no_ijazah',
        'no_skhun',
        'nilai_rata_rata',
        'nama_ayah',
        'nik_ayah',
        'pekerjaan_ayah',
        'penghasilan_ayah',
        'no_hp_ayah',
        'nama_ibu',
        'nik_ibu',
        'pekerjaan_ibu',
        'penghasilan_ibu',
        'no_hp_ibu',
        'nama_wali',
        'nik_wali',
        'pekerjaan_wali',
        'penghasilan_wali',
        'no_hp_wali',
        'hubungan_wali',
        'alamat_orangtua',
        'jurusan_pilihan_1',
        'jurusan_pilihan_2',
        'jalur_pendaftaran',
        'status',
        'catatan_verifikasi',
        'diverifikasi_oleh',
        'tanggal_verifikasi',
        'diterima_di_jurusan',
        'pas_foto',
        'token',
        'step_terakhir',
        'data_sementara',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_verifikasi' => 'datetime',
        'data_sementara' => 'array',
        'nilai_rata_rata' => 'decimal:2',
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_VERIFIED = 'verified';
    const STATUS_REJECTED = 'rejected';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_ENROLLED = 'enrolled';

    // Jalur pendaftaran constants
    const JALUR_REGULER = 'reguler';
    const JALUR_PRESTASI = 'prestasi';
    const JALUR_AFIRMASI = 'afirmasi';
    const JALUR_ZONASI = 'zonasi';

    // Step constants
    const STEP_NISN = 1;
    const STEP_DATA_PRIBADI = 2;
    const STEP_DATA_ORANGTUA = 3;
    const STEP_UPLOAD_DOKUMEN = 4;
    const STEP_REVIEW = 5;

    /**
     * Generate nomor pendaftaran
     */
    public static function generateNomorPendaftaran($tahunPelajaranId = null): string
    {
        $tahun = date('Y');
        $prefix = 'PPDB' . $tahun;
        
        $lastPendaftaran = self::where('nomor_pendaftaran', 'like', $prefix . '%')
            ->orderBy('nomor_pendaftaran', 'desc')
            ->first();
        
        if ($lastPendaftaran) {
            $lastNumber = (int) substr($lastPendaftaran->nomor_pendaftaran, -5);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Generate unique token
     */
    public static function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Scope for draft status
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Scope for submitted status
     */
    public function scopeSubmitted($query)
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }

    /**
     * Scope for verified status
     */
    public function scopeVerified($query)
    {
        return $query->where('status', self::STATUS_VERIFIED);
    }

    /**
     * Scope for accepted status
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', self::STATUS_ACCEPTED);
    }

    /**
     * Scope for specific tahun pelajaran
     */
    public function scopeForTahunPelajaran($query, $tahunPelajaranId)
    {
        return $query->where('tahun_pelajaran_id', $tahunPelajaranId);
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        $labels = [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SUBMITTED => 'Menunggu Verifikasi',
            self::STATUS_VERIFIED => 'Terverifikasi',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_ACCEPTED => 'Diterima',
            self::STATUS_ENROLLED => 'Terdaftar',
        ];
        
        return $labels[$this->status] ?? 'Unknown';
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            self::STATUS_DRAFT => 'secondary',
            self::STATUS_SUBMITTED => 'warning',
            self::STATUS_VERIFIED => 'info',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_ACCEPTED => 'success',
            self::STATUS_ENROLLED => 'primary',
        ];
        
        return $badges[$this->status] ?? 'secondary';
    }

    /**
     * Get pas foto URL
     */
    public function getPasFotoUrlAttribute(): ?string
    {
        if ($this->pas_foto) {
            return asset('storage/' . $this->pas_foto);
        }
        return null;
    }

    /**
     * Relationship to TahunPelajaran
     */
    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    /**
     * Relationship to dokumen pendaftaran
     */
    public function dokumen()
    {
        return $this->hasMany(DokumenPendaftaran::class, 'pendaftaran_id');
    }

    /**
     * Relationship to verifier user
     */
    public function verifier()
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    /**
     * Check if can proceed to next step
     */
    public function canProceedToStep(int $step): bool
    {
        // Must complete previous steps
        return $this->step_terakhir >= ($step - 1);
    }

    /**
     * Check if can be edited
     */
    public function canBeEdited(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REJECTED]);
    }

    /**
     * Check if can be submitted
     */
    public function canBeSubmitted(): bool
    {
        return $this->step_terakhir >= self::STEP_REVIEW && $this->status === self::STATUS_DRAFT;
    }

    /**
     * Get list of penghasilan options
     */
    public static function getPenghasilanOptions(): array
    {
        return [
            'tidak_berpenghasilan' => 'Tidak Berpenghasilan',
            'kurang_dari_1jt' => 'Kurang dari Rp 1.000.000',
            '1jt_2jt' => 'Rp 1.000.000 - Rp 2.000.000',
            '2jt_3jt' => 'Rp 2.000.000 - Rp 3.000.000',
            '3jt_5jt' => 'Rp 3.000.000 - Rp 5.000.000',
            '5jt_10jt' => 'Rp 5.000.000 - Rp 10.000.000',
            'lebih_dari_10jt' => 'Lebih dari Rp 10.000.000',
        ];
    }

    /**
     * Get list of agama options
     */
    public static function getAgamaOptions(): array
    {
        return [
            'islam' => 'Islam',
            'kristen' => 'Kristen',
            'katolik' => 'Katolik',
            'hindu' => 'Hindu',
            'buddha' => 'Buddha',
            'konghucu' => 'Konghucu',
        ];
    }

    /**
     * Get list of pekerjaan options
     */
    public static function getPekerjaanOptions(): array
    {
        return [
            'tidak_bekerja' => 'Tidak Bekerja',
            'pns' => 'PNS/TNI/Polri',
            'pegawai_swasta' => 'Pegawai Swasta',
            'wiraswasta' => 'Wiraswasta/Wirausaha',
            'petani' => 'Petani',
            'nelayan' => 'Nelayan',
            'buruh' => 'Buruh',
            'pedagang' => 'Pedagang',
            'pensiunan' => 'Pensiunan',
            'ibu_rumah_tangga' => 'Ibu Rumah Tangga',
            'lainnya' => 'Lainnya',
        ];
    }
}

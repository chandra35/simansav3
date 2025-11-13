<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasUuid;
use App\Traits\HasActivityLog;
use App\Traits\HasCreatedUpdatedBy;
use Laravolt\Indonesia\Models\Province;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Village;

class Siswa extends Model
{
    use HasUuid, HasActivityLog, HasCreatedUpdatedBy, SoftDeletes;

    protected $table = 'siswa';

    protected $fillable = [
        'user_id',
        'nisn',
        'nama_lengkap',
        'jenis_kelamin',
        'foto_profile',
        'npsn_asal_sekolah',
        'nik',
        'tempat_lahir',
        'tanggal_lahir',
        'agama',
        'jumlah_saudara',
        'anak_ke',
        'hobi',
        'cita_cita',
        'nomor_hp',
        'alamat_sama_ortu',
        'jenis_tempat_tinggal',
        'alamat_siswa',
        'rt_siswa',
        'rw_siswa',
        'provinsi_id_siswa',
        'kabupaten_id_siswa',
        'kecamatan_id_siswa',
        'kelurahan_id_siswa',
        'kodepos_siswa',
        'data_ortu_completed',
        'data_diri_completed',
        'created_by',
        'updated_by',
        // Kolom akademik baru
        'tahun_masuk',
        'asal_siswa',
        'status_siswa',
        'kelas_saat_ini_id',
        'jurusan_pilihan_id',
        'ppdb_id',
        'ppdb_imported_at',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'alamat_sama_ortu' => 'boolean',
        'data_ortu_completed' => 'boolean',
        'data_diri_completed' => 'boolean',
        'tahun_masuk' => 'integer',
        'ppdb_imported_at' => 'datetime',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ortu()
    {
        return $this->hasOne(Ortu::class);
    }

    public function dokumen()
    {
        return $this->hasMany(DokumenSiswa::class);
    }

    // Address relations for siswa
    public function provinsiSiswa()
    {
        return $this->belongsTo(Province::class, 'provinsi_id_siswa');
    }

    public function kabupatenSiswa()
    {
        return $this->belongsTo(City::class, 'kabupaten_id_siswa');
    }

    public function kecamatanSiswa()
    {
        return $this->belongsTo(District::class, 'kecamatan_id_siswa');
    }

    public function kelurahanSiswa()
    {
        return $this->belongsTo(Village::class, 'kelurahan_id_siswa');
    }

    public function sekolahAsal()
    {
        return $this->belongsTo(Sekolah::class, 'npsn_asal_sekolah', 'npsn');
    }

    // Akademik relations
    public function kelasSaatIni()
    {
        return $this->belongsTo(Kelas::class, 'kelas_saat_ini_id');
    }

    public function jurusanPilihan()
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_pilihan_id');
    }

    // Alias for kelasHistory (untuk kompatibilitas)
    public function kelas()
    {
        return $this->belongsToMany(Kelas::class, 'siswa_kelas', 'siswa_id', 'kelas_id')
                    ->withPivot(['tahun_pelajaran_id', 'tanggal_masuk', 'tanggal_keluar', 'status', 'nomor_urut_absen', 'catatan_perpindahan'])
                    ->whereNull('siswa_kelas.deleted_at')
                    ->withTimestamps();
    }

    public function kelasHistory()
    {
        return $this->belongsToMany(Kelas::class, 'siswa_kelas', 'siswa_id', 'kelas_id')
                    ->withPivot(['tahun_pelajaran_id', 'tanggal_masuk', 'tanggal_keluar', 'status', 'nomor_urut_absen', 'catatan_perpindahan'])
                    ->whereNull('siswa_kelas.deleted_at')
                    ->withTimestamps()
                    ->orderByDesc('siswa_kelas.created_at');
    }

    public function kelasAktif()
    {
        return $this->belongsToMany(Kelas::class, 'siswa_kelas', 'siswa_id', 'kelas_id')
                    ->withPivot(['tahun_pelajaran_id', 'tanggal_masuk', 'tanggal_keluar', 'status', 'nomor_urut_absen', 'catatan_perpindahan'])
                    ->whereNull('siswa_kelas.deleted_at')
                    ->where('siswa_kelas.status', 'aktif')
                    ->withTimestamps()
                    ->orderByDesc('siswa_kelas.created_at');
    }

    public function mutasiHistory()
    {
        return $this->hasMany(MutasiSiswa::class);
    }

    public function mutasiMasuk()
    {
        return $this->mutasiHistory()->where('jenis_mutasi', 'masuk');
    }

    public function mutasiKeluar()
    {
        return $this->mutasiHistory()->where('jenis_mutasi', 'keluar');
    }

    // Helper methods akademik
    public function getKelasSekarang()
    {
        return $this->kelasAktif()->first();
    }

    public function isAktif(): bool
    {
        return $this->status_siswa === 'aktif';
    }

    public function isMutasi(): bool
    {
        return in_array($this->asal_siswa, ['mutasi_masuk']) || 
               $this->status_siswa === 'mutasi_keluar';
    }

    public function isPPDB(): bool
    {
        return $this->asal_siswa === 'ppdb';
    }

    public function getStatusBadge(): string
    {
        $badges = [
            'aktif' => '<span class="badge badge-success">Aktif</span>',
            'lulus' => '<span class="badge badge-primary">Lulus</span>',
            'keluar' => '<span class="badge badge-danger">Keluar</span>',
            'mutasi_keluar' => '<span class="badge badge-warning">Mutasi Keluar</span>',
            'alumni' => '<span class="badge badge-info">Alumni</span>',
        ];

        return $badges[$this->status_siswa] ?? '<span class="badge badge-secondary">Unknown</span>';
    }

    public function getAsalSiswaBadge(): string
    {
        $badges = [
            'ppdb' => '<span class="badge badge-primary"><i class="fas fa-user-graduate"></i> PPDB</span>',
            'mutasi_masuk' => '<span class="badge badge-warning"><i class="fas fa-exchange-alt"></i> Mutasi</span>',
            'reguler' => '<span class="badge badge-secondary"><i class="fas fa-user"></i> Reguler</span>',
        ];

        return $badges[$this->asal_siswa] ?? '<span class="badge badge-secondary">-</span>';
    }

    // Helper methods
    public function getFotoProfileUrlAttribute()
    {
        if ($this->foto_profile) {
            return asset('storage/' . $this->foto_profile);
        }
        
        // Generate avatar animasi dari nama siswa menggunakan UI Avatars
        $name = urlencode($this->nama_lengkap ?? 'Siswa');
        
        // Warna background berdasarkan jenis kelamin dengan gradasi lebih menarik
        if ($this->jenis_kelamin === 'L') {
            // Gradient warna biru untuk laki-laki
            $backgrounds = ['3498db', '2980b9', '2c3e50', '34495e', '16a085'];
        } else {
            // Gradient warna pink/ungu untuk perempuan
            $backgrounds = ['e74c3c', 'e91e63', '9b59b6', 'f39c12', 'c0392b'];
        }
        
        // Pilih warna berdasarkan hash dari nama (konsisten untuk nama yang sama)
        $index = abs(crc32($this->nama_lengkap ?? 'Siswa')) % count($backgrounds);
        $background = $backgrounds[$index];
        
        $color = 'FFFFFF';
        $size = 400;
        $fontSize = 0.45;
        $bold = true;
        $rounded = false; // Gunakan square untuk konsistensi dengan foto upload
        
        return "https://ui-avatars.com/api/?name={$name}&size={$size}&background={$background}&color={$color}&font-size={$fontSize}&bold=" . ($bold ? 'true' : 'false') . "&rounded=" . ($rounded ? 'true' : 'false');
    }

    // Original helper methods
    public function isDataComplete()
    {
        return $this->data_ortu_completed && $this->data_diri_completed;
    }

    public function getAlamatLengkapSiswa()
    {
        if ($this->alamat_sama_ortu && $this->ortu) {
            return $this->ortu->getAlamatLengkap();
        }
        
        $alamat = $this->alamat_siswa;
        if ($this->kelurahanSiswa) {
            $alamat .= ', ' . $this->kelurahanSiswa->name;
        }
        if ($this->kecamatanSiswa) {
            $alamat .= ', ' . $this->kecamatanSiswa->name;
        }
        if ($this->kabupatenSiswa) {
            $alamat .= ', ' . $this->kabupatenSiswa->name;
        }
        if ($this->provinsiSiswa) {
            $alamat .= ', ' . $this->provinsiSiswa->name;
        }
        if ($this->kodepos_siswa) {
            $alamat .= ' ' . $this->kodepos_siswa;
        }
        
        return $alamat;
    }
}

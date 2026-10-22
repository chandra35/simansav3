# Ketentuan Tata Naskah Dinas & Penomoran Surat Resmi MAN 1 Metro

Dokumen ini adalah **pedoman baku dan aturan wajib** bagi tata usaha, operator, dan pengembang (developer) SIMANSA dalam penerbitan nomor surat resmi, pembuatan modul surat menyurat, maupun penambahan fitur cetak dokumen di lingkungan Madrasah Aliyah Negeri 1 Metro.

---

## 1. Landasan Hukum & Regulasi

Penomoran dan tata naskah surat dinas di MAN 1 Metro mengacu pada regulasi resmi Kementerian Agama Republik Indonesia:
1. **Keputusan Menteri Agama (KMA) Nomor 9 Tahun 2016** tentang Pedoman Tata Naskah Dinas pada Kementerian Agama.
2. **Keputusan Menteri Agama (KMA) Nomor 168 Tahun 2022** tentang Tata Naskah Dinas Kementerian Agama.
3. Kebijakan Manajemen Tata Usaha MAN 1 Metro mengenai penatausahaan arsip dinas aktif terpusat.

---

## 2. Prinsip Pokok Penomoran Surat di SIMANSA

Untuk menjamin ketertiban administrasi, kepatuhan audit kearsipan, dan mencegah terjadinya nomor ganda atau nomor siluman, seluruh modul di SIMANSA wajib mematuhi 4 prinsip utama:

1. **Satu Urutan Nomor Berjalan Tunggal (Single Sequence Running Number)**:
   Seluruh surat keluar madrasah—baik Surat Keterangan Siswa, Mutasi, Surat Tugas, Surat Rekomendasi, Surat Keputusan, maupun surat manual yang diterbitkan oleh Tata Usaha—menggunakan **satu deret nomor urut berurutan tunggal (001, 002, 003, ...)** per tahun kalender.
2. **Pencatatan Otomatis ke Buku Agenda Surat Keluar (`buku_nomor_surat`)**:
   Setiap kali nomor surat diterbitkan oleh sistem atau diambil oleh petugas TU, sistem secara otomatis menyimpan rekam jejaknya ke tabel pusat `buku_nomor_surat` lengkap dengan informasi tanggal, kode klasifikasi, perihal, tujuan, penandatangan, modul asal, dan relasi polimorfik ke dokumen terkait.
3. **Integritas Konkurensi & Mutex Locking**:
   Penerbitan nomor dilakukan di dalam transaksi basis data dengan fasilitas *pessimistic locking* (`lockForUpdate()`) pada tabel `surat_nomor_counters` untuk memastikan nomor tidak pernah ganda meskipun diakses bersamaan oleh beberapa petugas pada detik yang sama.
4. **Prinsip Audit Pembatalan (Non-Destructive Cancellation)**:
   Nomor surat yang sudah diterbitkan **tidak boleh dihapus secara fisik** dari basis data. Jika ada kesalahan data, perubahan tujuan, atau pembatalan dokumen, nomor tersebut ditandai dengan status `dibatalkan` disertai pencatatan `alasan_dibatalkan`, user pembatal (`dibatalkan_oleh`), dan stempel waktu (`dibatalkan_at`). Hal ini agar urutan buku agenda fisik tetap sinkron dengan buku agenda digital.

---

## 3. Anatomi Struktur Nomor Surat Keluar

Format nomor surat keluar standar Kementerian Agama pada MAN 1 Metro adalah:

$$\mathbf{\{prefix\}-\{nomor\}/\{kode\_satuan\_kerja\}/\{kode\_klasifikasi\}/\{bulan\}/\{tahun\}}$$

**Contoh Riil:**
- `B-023/Ma.08.01/PP.00.6/10/2026` (Surat Keterangan Aktif Siswa bulan Oktober 2026)
- `B-024/Ma.08.01/KP.01.1/10/2026` (Surat Tugas Guru bulan Oktober 2026)
- `B-025/Ma.08.01/HM.00/10/2026` (Undangan Rapat Komite bulan Oktober 2026)

### Rincian Komponen Token:

| Token | Deskripsi | Standar Nilai di MAN 1 Metro |
|---|---|---|
| `{prefix}` | Sifat / Derajat Surat | `B` = Biasa (umum)<br>`R` = Rahasia<br>`SR` = Sangat Rahasia<br>`P` = Penting<br>`BA` = Berita Acara |
| `{nomor}` | Nomor Urut Surat | 3 digit angka berurutan per tahun kalender: `001`, `002`, `023`, `105`. |
| `{kode_satuan_kerja}` | Kode Induk Satker Kemenag | **`Ma.08.01`** (Kode baku MAN 1 Metro). |
| `{kode_klasifikasi}` | Klasifikasi Urusan / Masalah | Kode urusan naskah dinas Kemenag (lihat tabel di bawah). |
| `{bulan}` | Bulan Penetapan Surat | 2 digit angka bulan Masehi: `01` s.d. `12`. |
| `{tahun}` | Tahun Kalender Penetapan | 4 digit angka tahun Masehi: `2026`. |

---

## 4. Daftar Kode Klasifikasi Surat di MAN 1 Metro

Tabel berikut menjadi rujukan resmi bagi Tata Usaha dan modul sistem dalam memilih kode klasifikasi yang tepat:

| Kode Klasifikasi | Bidang / Kategori | Jenis Dokumen / Keperluan di SIMANSA |
|---|---|---|
| **`PP.00.6`** | **Kesiswaan** | - Surat Keterangan Aktif Siswa<br>- Surat Keterangan Pindah / Mutasi Siswa (Masuk/Keluar)<br>- Surat Rekomendasi Siswa Berprestasi / Lomba<br>- Surat Keterangan Kelakuan Baik Siswa<br>- Rekomendasi Beasiswa Siswa (PIP, BAZNAS, PTN) |
| **`PP.01.1`** | **Kurikulum & Pembelajaran** | - Surat Keterangan Lulus (SKL) Siswa<br>- Surat Pengantar Ujian / Asesmen Madrasah<br>- Surat Penyelenggaraan KBM / Kalender Akademik<br>- Pengantar / Penerbitan Ijazah & Rapor |
| **`KP.01.1`** | **Kepegawaian (Umum)** | - Surat Perintah Tugas (SPT) Guru & Tenaga Kependidikan<br>- Surat Perintah Perjalanan Dinas (SPPD)<br>- Penugasan Narasumber, Pengawas Ujian, Bimtek/Diklat |
| **`KP.01.2`** | **Cuti Kepegawaian** | - Surat Permohonan / Pemberitahuan Cuti Tahunan<br>- Cuti Melahirkan, Cuti Sakit, Cuti Alasan Penting |
| **`KS.01.2`** | **Bimbingan & Disiplin Siswa** | - Surat Panggilan Orang Tua / Wali Siswa (BP/BK)<br>- Surat Peringatan / Pembinaan Kedisiplinan Siswa<br>- Surat Dispensasi Kegiatan / Lomba di Luar Madrasah |
| **`HM.00`** | **Hubungan Masyarakat (Humas)** | - Undangan Rapat Orang Tua / Wali Murid<br>- Undangan Rapat Pengurus Komite Madrasah<br>- Surat Permohonan Kemitraan / Kerjasama Eksternal<br>- Pemberitahuan Kegiatan Sosial / Kemasyarakatan |
| **`KU.01.1`** | **Keuangan & Pembiayaan** | - Surat Bebas Administrasi Keuangan Siswa<br>- Surat Keterangan Lunas Biaya Pendidikan<br>- Surat Konfirmasi Pendanaan BOS / Komite |
| **`BA`** | **Berita Acara** | - Berita Acara Serah Terima (BAST) Barang / Gedung / Dokumen<br>- Berita Acara Pelaksanaan Asesmen / Ujian Madrasah<br>- Berita Acara Pemusnahan Blangko Ijazah |
| **`SK`** | **Surat Keputusan** | - SK Penetapan Pembagian Beban Tugas Guru & Pegawai<br>- SK Susunan Panitia Ujian / Kegiatan Madrasah<br>- SK Penetapan Kelulusan & Kenaikan Kelas Siswa |

---

## 5. Panduan Teknis untuk Pengembang (Developer Guide)

> ⚠️ **ATURAN MUTLAK:**
> **DILARANG KERAS** membuat logika penomoran sendiri, meng-increment counter lokal, atau menyimpan nomor surat keluar tanpa melalui `App\Services\SuratNumberService`!

### 5.1. Mengambil Nomor Baru dari Controller / Service
Setiap modul yang menerbitkan surat keluar wajib menyuntikkan `App\Services\SuratNumberService` dan memanggil method `generate()`:

```php
use App\Services\SuratNumberService;
use Carbon\Carbon;

class DokumenBaruController extends Controller
{
    public function store(Request $request, SuratNumberService $suratNumberService)
    {
        $tanggalSurat = Carbon::parse($request->input('tanggal_surat', now()));
        
        // 1. Simpan dokumen bisnis terlebih dahulu
        $dokumen = ModelDokumen::create([ ... ]);

        // 2. Terbitkan nomor surat resmi terpusat
        $nomorSurat = $suratNumberService->generate(
            'PP.00.6',        // Kode klasifikasi dari tabel surat_nomor_configs
            $tanggalSurat,    // Objek Carbon tanggal penetapan
            [
                'perihal'               => 'Surat Keterangan Aktif Siswa',
                'tujuan'                => $siswa->nama_lengkap,
                'modul_asal'            => 'surat_keterangan', // Nama modul
                'penandatangan_id'      => $gtkKepalaId,
                'nama_penandatangan'    => 'H. Ahmad Husin, S.Pd.',
                'jabatan_penandatangan' => 'Kepala Madrasah',
                'suratable'             => $dokumen,           // Relasi polimorfik ke model dokumen
                'created_by'            => auth()->id(),
                'keterangan'            => 'Penerbitan otomatis melalui modul kesiswaan',
            ]
        );

        // 3. Simpan nomor surat ke model dokumen
        $dokumen->update(['nomor_surat' => $nomorSurat]);
        
        return redirect()->back()->with('success', "Nomor {$nomorSurat} berhasil diterbitkan.");
    }
}
```

### 5.2. Menampilkan Preview Nomor Tanpa Mengambil Kuota
Gunakan method `preview()` untuk menunjukkan ke pengguna format nomor yang akan dihasilkan sebelum form disimpan:

```php
$service = app(\App\Services\SuratNumberService::class);
$preview = $service->preview('KP.01.1', now());
// Hasil: "B-024/Ma.08.01/KP.01.1/10/2026"
```

### 5.3. Membatalkan Nomor Surat
Jika pengguna membatalkan dokumen surat, panggil method `cancel()`:

```php
$service = app(\App\Services\SuratNumberService::class);
$buku = \App\Models\BukuNomorSurat::where('nomor_surat', $nomorSurat)->firstOrFail();

$service->cancel(
    $buku,
    'Surat dibatalkan karena siswa membatalkan pengajuan pindah sekolah.',
    auth()->id()
);
```

---

## 6. Standar Layout KOP & Margin Dokumen Resmi (DomPDF)

Seluruh template cetak surat dinas resmi di SIMANSA wajib mematuhi metrik presisi berikut agar tampilan cetak rapi, proporsional, dan tidak tumpang tindih:

```css
@page {
    size: A4 portrait;
    margin: 8mm 15mm 12mm 15mm;
}
body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 11pt;
    line-height: 1.35;
    color: #000;
}
.kop {
    position: relative;
    text-align: center;
    border-bottom: 3px double #000;
    padding: 0 0 6px 0;
    margin-bottom: 14px;
    height: 88px;
}
.kop-logo {
    position: absolute;
    top: 0;
    height: 82px;
    width: auto;
}
.kop-logo.left { left: 0; }
.kop-logo.right { right: 0; }
.kop-title {
    margin: 0 75px;
    line-height: 1.15;
}
.kop-title .line-1 { font-size: 15px; font-weight: bold; }
.kop-title .line-2 { font-size: 15px; font-weight: bold; }
.kop-title .line-3 { font-size: 16px; font-weight: bold; }
.kop-meta {
    font-size: 9px;
    line-height: 1.15;
    margin-top: 4px;
}
```

---

## 7. Lokasi Menu & Antarmuka di Aplikasi

1. **Menu Sidebar Tata Usaha**:
   - `Tata Usaha` &rarr; `Buku Nomor Surat` (`admin.tata-usaha.buku-nomor-surat.index`)
   - `Layanan Surat` &rarr; `Buku Nomor Surat` (`admin.tata-usaha.buku-nomor-surat.index`)
2. **Fitur Antarmuka Buku Agenda**:
   - Filter berdasarkan Tahun, Klasifikasi, Status (Aktif/Batal), dan Modul Asal.
   - Tombol Cepat **Salin Nomor Surat** ke clipboard.
   - Tombol **Ambil Nomor Baru** untuk penerbitan surat manual TU dengan live format preview.
   - Tombol **Batalkan Nomor** dengan pengisian alasan pembatalan audit.
   - Tombol **Cetak Agenda (A4)** untuk mencetak fisik Buku Agenda Surat Keluar tahunan.

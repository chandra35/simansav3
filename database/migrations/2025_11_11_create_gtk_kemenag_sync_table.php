<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('gtk_kemenag_sync', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('gtk_id');
            
            // ============================================
            // RAW DATA BACKUP
            // ============================================
            $table->json('raw_response')->nullable()->comment('Backup JSON lengkap dari API');
            
            // ============================================
            // DATA IDENTITAS
            // ============================================
            $table->string('nip', 20)->nullable()->comment('NIP Lama');
            $table->string('nip_baru', 20)->nullable()->comment('NIP Baru (18 digit)');
            $table->string('nama', 255)->nullable();
            $table->string('nama_lengkap', 255)->nullable()->comment('Nama dengan gelar');
            $table->string('nik', 16)->nullable();
            $table->string('agama', 50)->nullable();
            $table->string('tempat_lahir', 255)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->enum('status_kawin', ['Kawin', 'Belum Kawin', 'Cerai Hidup', 'Cerai Mati'])->nullable();
            
            // ============================================
            // DATA PENDIDIKAN
            // ============================================
            $table->string('pendidikan', 255)->nullable()->comment('Pendidikan lengkap dengan tahun');
            $table->string('jenjang_pendidikan', 100)->nullable()->comment('Jenjang pendidikan saja');
            $table->string('kode_bidang_studi', 50)->nullable();
            $table->string('bidang_studi', 255)->nullable();
            
            // ============================================
            // DATA KONTAK
            // ============================================
            $table->string('telepon', 20)->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->string('email', 255)->nullable()->comment('Email pribadi');
            $table->string('email_dinas', 255)->nullable()->comment('Email dinas');
            
            // ============================================
            // DATA ALAMAT
            // ============================================
            $table->string('alamat_1', 255)->nullable()->comment('Jalan/Dusun RT/RW');
            $table->string('alamat_2', 255)->nullable()->comment('Kelurahan/Desa');
            $table->string('kab_kota', 255)->nullable();
            $table->string('provinsi', 255)->nullable();
            $table->string('kode_pos', 10)->nullable();
            $table->string('kode_lokasi', 20)->nullable();
            $table->decimal('lat', 10, 8)->nullable()->comment('Latitude GPS');
            $table->decimal('lon', 11, 8)->nullable()->comment('Longitude GPS');
            
            // ============================================
            // DATA KEPEGAWAIAN
            // ============================================
            $table->string('status_pegawai', 50)->nullable()->comment('PNS/PPPK/dll');
            $table->string('kode_pangkat', 10)->nullable();
            $table->string('pangkat', 100)->nullable();
            $table->string('gol_ruang', 10)->nullable()->comment('Golongan Ruang');
            $table->date('tmt_cpns')->nullable()->comment('TMT CPNS');
            $table->date('tmt_pangkat')->nullable()->comment('TMT Pangkat');
            $table->date('tmt_pangkat_yad')->nullable()->comment('TMT Pangkat Yang Akan Datang');
            
            // ============================================
            // DATA JABATAN
            // ============================================
            $table->string('tipe_jabatan', 100)->nullable();
            $table->string('kode_jabatan', 50)->nullable();
            $table->string('tampil_jabatan', 255)->nullable()->comment('Nama jabatan tampilan');
            $table->string('kode_level_jabatan', 10)->nullable();
            $table->string('level_jabatan', 100)->nullable();
            $table->date('tmt_jabatan')->nullable()->comment('TMT Jabatan');
            
            // ============================================
            // DATA SATUAN KERJA (5 LEVEL HIERARKI)
            // ============================================
            $table->string('kode_satuan_kerja', 20)->nullable()->comment('Kode Satker Level 1');
            $table->string('satker_1', 255)->nullable()->comment('Satker Level 1 - Unit Kerja Langsung');
            $table->string('kode_satker_2', 20)->nullable();
            $table->string('satker_2', 255)->nullable()->comment('Satker Level 2');
            $table->string('kode_satker_3', 20)->nullable();
            $table->string('satker_3', 255)->nullable()->comment('Satker Level 3 - Kankemenag');
            $table->string('kode_satker_4', 20)->nullable();
            $table->string('satker_4', 255)->nullable()->comment('Satker Level 4 - Kanwil');
            $table->string('kode_satker_5', 20)->nullable();
            $table->string('satker_5', 255)->nullable()->comment('Satker Level 5 - Pusat');
            $table->string('kode_grup_satuan_kerja', 20)->nullable();
            $table->string('grup_satuan_kerja', 255)->nullable()->comment('Grup/Jenis Satker (MAN/MTsN/dll)');
            $table->text('keterangan_satuan_kerja')->nullable()->comment('Keterangan lengkap satker');
            $table->string('satker_kelola', 255)->nullable();
            
            // ============================================
            // DATA MASA KERJA
            // ============================================
            $table->integer('mk_tahun')->default(0)->comment('Masa Kerja Tahun');
            $table->integer('mk_bulan')->default(0)->comment('Masa Kerja Bulan');
            $table->integer('mk_tahun_1')->default(0)->comment('Masa Kerja Tahun (Alternatif)');
            $table->integer('mk_bulan_1')->default(0)->comment('Masa Kerja Bulan (Alternatif)');
            
            // ============================================
            // DATA GAJI
            // ============================================
            $table->decimal('gaji_pokok', 15, 2)->default(0)->comment('Gaji Pokok');
            $table->date('tmt_kgb_yad')->nullable()->comment('TMT KGB Yang Akan Datang');
            
            // ============================================
            // DATA PENSIUN
            // ============================================
            $table->integer('usia_pensiun')->default(58);
            $table->date('tmt_pensiun')->nullable()->comment('TMT Pensiun');
            
            // ============================================
            // DATA MADRASAH (untuk GTK Madrasah)
            // ============================================
            $table->string('nsm', 20)->nullable()->comment('Nomor Statistik Madrasah');
            $table->string('npsn', 20)->nullable()->comment('Nomor Pokok Sekolah Nasional');
            $table->string('kode_kua', 20)->nullable();
            $table->integer('hari_kerja')->default(5);
            
            // ============================================
            // DATA TAMBAHAN
            // ============================================
            $table->string('iso', 50)->nullable();
            $table->text('keterangan')->nullable()->comment('Keterangan lengkap pegawai');
            
            // ============================================
            // METADATA SYNC
            // ============================================
            $table->enum('sync_status', ['success', 'failed', 'partial'])->default('success');
            $table->text('sync_message')->nullable()->comment('Error message jika gagal');
            $table->timestamp('synced_at')->nullable();
            $table->uuid('synced_by')->nullable()->comment('User yang melakukan sync');
            
            // ============================================
            // STATUS PERBANDINGAN DENGAN DATA LOKAL
            // ============================================
            $table->boolean('has_differences')->default(false)->comment('Flag jika ada perbedaan dengan data lokal');
            $table->json('differences')->nullable()->comment('Detail perbedaan field per field');
            
            // ============================================
            // RIWAYAT UPDATE DARI DATA KEMENAG
            // ============================================
            $table->timestamp('last_applied_at')->nullable()->comment('Terakhir kali data Kemenag di-apply ke lokal');
            $table->uuid('applied_by')->nullable()->comment('User yang apply data');
            
            $table->timestamps();
            
            // ============================================
            // FOREIGN KEYS
            // ============================================
            $table->foreign('gtk_id')->references('id')->on('gtks')->onDelete('cascade');
            $table->foreign('synced_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('applied_by')->references('id')->on('users')->onDelete('set null');
            
            // ============================================
            // INDEXES
            // ============================================
            $table->index('gtk_id');
            $table->index('nip');
            $table->index('nip_baru');
            $table->index('sync_status');
            $table->index('has_differences');
            $table->index('synced_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gtk_kemenag_sync');
    }
};

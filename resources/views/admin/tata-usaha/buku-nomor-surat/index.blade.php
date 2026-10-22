@extends('adminlte::page')

@section('title', 'Buku Agenda Nomor Surat Keluar - SIMANSA')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark"><i class="fas fa-list-ol text-primary mr-2"></i>Buku Agenda Nomor Surat</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.tata-usaha.surat-masuk.index') }}">Tata Usaha</a></li>
                <li class="breadcrumb-item active">Buku Nomor Surat</li>
            </ol>
        </div>
    </div>
@stop

@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)
@section('plugins.Select2', true)

@section('content')
<div class="simansa-buku-nomor-surat">
    {{-- Hero Card Single Continuous Gradient (Baseline UI Standar SIMANSA) --}}
    <div class="card bg-gradient-primary text-white mb-4 shadow-sm">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h3 class="font-weight-bold mb-1"><i class="fas fa-book-open mr-2"></i>Registrasi Terpusat Nomor Surat Keluar</h3>
                    <p class="mb-2 text-white-50">
                        Sistem penomoran surat dinas terpadu MAN 1 Metro berpedoman pada Tata Naskah Dinas Kementerian Agama (KMA 9/2016 & KMA 168/2022).
                    </p>
                    <p class="mb-0 small text-light">
                        <i class="fas fa-info-circle mr-1"></i> Seluruh nomor surat keluar (Surat Keterangan, Mutasi, Surat Tugas, Surat Keputusan, & TU Manual) menggunakan satu urutan nomor berjalan tunggal per tahun kalender.
                    </p>
                </div>
                <div class="col-lg-5 mt-3 mt-lg-0">
                    <div class="row text-center">
                        <div class="col-6 col-md-3 col-lg-6 mb-2">
                            <div class="p-2 rounded bg-white-10" style="background: rgba(255,255,255,0.12);">
                                <div class="text-white-50 small text-uppercase font-weight-bold">Total Terbit</div>
                                <h3 class="mb-0 text-white font-weight-bold" id="stat-total">{{ number_format($stats['total']) }}</h3>
                                <small class="text-white-50">Tahun {{ $selectedYear }}</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 col-lg-6 mb-2">
                            <div class="p-2 rounded bg-white-10" style="background: rgba(255,255,255,0.12);">
                                <div class="text-white-50 small text-uppercase font-weight-bold">Surat Aktif</div>
                                <h3 class="mb-0 text-success font-weight-bold" style="color: #a3e635 !important;" id="stat-aktif">{{ number_format($stats['aktif']) }}</h3>
                                <small class="text-white-50">Berlaku sah</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 col-lg-6 mb-2">
                            <div class="p-2 rounded bg-white-10" style="background: rgba(255,255,255,0.12);">
                                <div class="text-white-50 small text-uppercase font-weight-bold">Dibatalkan</div>
                                <h3 class="mb-0 text-warning font-weight-bold" style="color: #fde047 !important;" id="stat-batal">{{ number_format($stats['dibatalkan']) }}</h3>
                                <small class="text-white-50">Tercatat audit</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 col-lg-6 mb-2">
                            <div class="p-2 rounded bg-white-10" style="background: rgba(255,255,255,0.12);">
                                <div class="text-white-50 small text-uppercase font-weight-bold">No. Berjalan</div>
                                <h3 class="mb-0 text-white font-weight-bold" id="stat-counter">{{ sprintf('%03d', $stats['counter']) }}</h3>
                                <small class="text-white-50">Urutan terakhir</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter & Operational Toolbar --}}
    <div class="card card-outline card-primary shadow-sm mb-3">
        <div class="card-header bg-white py-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div class="font-weight-bold text-dark mb-2 mb-md-0">
                    <i class="fas fa-filter text-primary mr-1"></i> Filter Data Buku Agenda
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-outline-info btn-sm mr-1" data-toggle="modal" data-target="#modalPanduan">
                        <i class="fas fa-book mr-1"></i> Panduan Klasifikasi
                    </button>
                    <a href="{{ route('admin.tata-usaha.buku-nomor-surat.print-agenda', ['tahun' => $selectedYear]) }}" target="_blank" id="btn-print-agenda" class="btn btn-outline-secondary btn-sm mr-1">
                        <i class="fas fa-print mr-1"></i> Cetak Agenda (A4)
                    </a>
                    @can('manage-layanan-surat')
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalAmbilNomor">
                            <i class="fas fa-plus-circle mr-1"></i> Ambil Nomor Baru
                        </button>
                    @endcan
                </div>
            </div>
        </div>
        <div class="card-body bg-light py-3">
            <div class="row">
                <div class="col-md-3 col-sm-6 mb-2">
                    <label class="small font-weight-bold text-muted mb-1">Tahun Agenda</label>
                    <select id="filter-tahun" class="form-control form-control-sm select2">
                        @foreach($years as $yr)
                            <option value="{{ $yr }}" {{ $yr == $selectedYear ? 'selected' : '' }}>Tahun {{ $yr }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-sm-6 mb-2">
                    <label class="small font-weight-bold text-muted mb-1">Klasifikasi Surat</label>
                    <select id="filter-klasifikasi" class="form-control form-control-sm select2">
                        <option value="">Semua Klasifikasi</option>
                        @foreach($configs as $cfg)
                            <option value="{{ $cfg->kode }}">{{ $cfg->kode }} - {{ $cfg->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-sm-6 mb-2">
                    <label class="small font-weight-bold text-muted mb-1">Modul / Asal Penerbit</label>
                    <select id="filter-modul" class="form-control form-control-sm">
                        <option value="">Semua Modul</option>
                        <option value="manual_tu">Manual TU (Buku Agenda)</option>
                        <option value="surat_keterangan">Surat Keterangan Siswa</option>
                        <option value="mutasi_siswa">Mutasi Siswa (Masuk/Keluar)</option>
                        <option value="surat_tugas">Surat Tugas Guru/Pegawai</option>
                        <option value="sk">Surat Keputusan (SK)</option>
                        <option value="undangan">Undangan Resmi</option>
                    </select>
                </div>
                <div class="col-md-3 col-sm-6 mb-2">
                    <label class="small font-weight-bold text-muted mb-1">Status Nomor</label>
                    <select id="filter-status" class="form-control form-control-sm">
                        <option value="">Semua Status</option>
                        <option value="aktif">Aktif (Sah)</option>
                        <option value="dibatalkan">Dibatalkan</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Table Card --}}
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-body p-3">
            <div class="table-responsive">
                <table id="table-buku-nomor" class="table table-bordered table-hover table-striped w-100">
                    <thead class="thead-light">
                        <tr>
                            <th width="35" class="text-center align-middle">No</th>
                            <th width="210" class="align-middle">Nomor Surat</th>
                            <th width="85" class="text-center align-middle">Tanggal</th>
                            <th width="110" class="align-middle">Klasifikasi</th>
                            <th class="align-middle">Perihal & Tujuan</th>
                            <th width="160" class="align-middle">Penandatangan</th>
                            <th width="115" class="text-center align-middle">Modul Asal</th>
                            <th width="85" class="text-center align-middle">Status</th>
                            <th width="85" class="text-center align-middle">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL AMBIL NOMOR BARU --}}
@can('manage-layanan-surat')
<div class="modal fade" id="modalAmbilNomor" tabindex="-1" role="dialog" aria-labelledby="modalAmbilNomorLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold" id="modalAmbilNomorLabel">
                    <i class="fas fa-plus-circle mr-2"></i> Ambil Nomor Surat Baru (Tata Usaha)
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-ambil-nomor" method="POST" action="{{ route('admin.tata-usaha.buku-nomor-surat.store') }}">
                @csrf
                <div class="modal-body">
                    {{-- Live Preview Box --}}
                    <div class="alert alert-info border-0 shadow-sm mb-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <small class="text-uppercase font-weight-bold d-block text-muted">Perkiraan Nomor Surat yang Akan Diterbitkan:</small>
                                <span class="h4 font-weight-bold text-primary mb-0" id="live-nomor-preview">
                                    {{ $previewNomor }}
                                </span>
                            </div>
                            <span class="badge badge-primary px-3 py-2"><i class="fas fa-magic mr-1"></i> Auto Number</span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="input-kode" class="font-weight-bold">Kode Klasifikasi <span class="text-danger">*</span></label>
                                <select name="kode" id="input-kode" class="form-control select2" required style="width: 100%;">
                                    @foreach($configs as $cfg)
                                        <option value="{{ $cfg->kode }}" {{ $cfg->kode == 'PP.00.6' ? 'selected' : '' }}>
                                            {{ $cfg->kode }} - {{ $cfg->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Format: {prefix}-{nomor}/Ma.08.01/{kode}/{bln}/{thn}</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="input-tanggal" class="font-weight-bold">Tanggal Surat <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_surat" id="input-tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                                <small class="form-text text-muted">Tanggal penetapan / tanggal resmi surat dinas.</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="input-perihal" class="font-weight-bold">Perihal Surat <span class="text-danger">*</span></label>
                        <input type="text" name="perihal" id="input-perihal" class="form-control" placeholder="Contoh: Surat Permohonan Narasumber Workshop Guru" required maxlength="255">
                    </div>

                    <div class="form-group">
                        <label for="input-tujuan" class="font-weight-bold">Tujuan / Kepada Yth.</label>
                        <input type="text" name="tujuan" id="input-tujuan" class="form-control" placeholder="Contoh: Kepala Kantor Kementerian Agama Kota Metro" maxlength="255">
                        <small class="form-text text-muted">Pihak penerima surat (instansi, perseorangan, wali siswa, dll).</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="input-penandatangan" class="font-weight-bold">Penandatangan (GTK)</label>
                                <select name="penandatangan_id" id="input-penandatangan" class="form-control select2" style="width: 100%;">
                                    <option value="">-- Pilih Guru / Tenaga Kependidikan --</option>
                                    @foreach($gtks as $gtk)
                                        <option value="{{ $gtk->id }}" {{ (isset($setting->nama_kepala_madrasah) && stripos($gtk->nama_lengkap, $setting->nama_kepala_madrasah) !== false) ? 'selected' : '' }}>
                                            {{ $gtk->nama_lengkap }} ({{ $gtk->nip ?: 'Non-NIP' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="input-jabatan" class="font-weight-bold">Jabatan Penandatangan</label>
                                <input type="text" name="jabatan_penandatangan" id="input-jabatan" class="form-control" value="Kepala Madrasah" placeholder="Kepala Madrasah / Plt. Kepala / Ka. TU">
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label for="input-keterangan" class="font-weight-bold">Catatan / Keterangan Tambahan</label>
                        <textarea name="keterangan" id="input-keterangan" rows="2" class="form-control" placeholder="Catatan internal arsip atau referensi berkas jika ada..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary" id="btn-submit-ambil">
                        <i class="fas fa-check mr-1"></i> Terbitkan & Catat Nomor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

{{-- MODAL DETAIL NOMOR SURAT --}}
<div class="modal fade" id="modalDetailNomor" tabindex="-1" role="dialog" aria-labelledby="modalDetailNomorLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title font-weight-bold" id="modalDetailNomorLabel">
                    <i class="fas fa-info-circle mr-2"></i> Detail Nomor Surat Keluar
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="p-3 bg-light border-bottom d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-uppercase font-weight-bold text-muted d-block">Nomor Surat Resmi:</small>
                        <span class="h5 font-weight-bold text-primary mb-0" id="detail-nomor-surat">-</span>
                        <button type="button" class="btn btn-xs btn-outline-secondary ml-2 btn-copy-nomor" id="detail-btn-copy" data-nomor="">
                            <i class="fas fa-copy mr-1"></i> Salin
                        </button>
                    </div>
                    <span class="badge badge-success px-3 py-2" id="detail-status-badge">Aktif</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <tbody>
                            <tr>
                                <th width="200" class="pl-3">Nomor Urut / Tahun</th>
                                <td id="detail-urut-tahun">-</td>
                            </tr>
                            <tr>
                                <th class="pl-3">Tanggal Surat</th>
                                <td id="detail-tanggal">-</td>
                            </tr>
                            <tr>
                                <th class="pl-3">Kode Klasifikasi</th>
                                <td id="detail-klasifikasi">-</td>
                            </tr>
                            <tr>
                                <th class="pl-3">Perihal Surat</th>
                                <td id="detail-perihal" class="font-weight-bold text-dark">-</td>
                            </tr>
                            <tr>
                                <th class="pl-3">Tujuan Surat</th>
                                <td id="detail-tujuan">-</td>
                            </tr>
                            <tr>
                                <th class="pl-3">Penandatangan</th>
                                <td id="detail-penandatangan">-</td>
                            </tr>
                            <tr>
                                <th class="pl-3">Modul Asal</th>
                                <td id="detail-modul">-</td>
                            </tr>
                            <tr>
                                <th class="pl-3">Petugas Pembuat</th>
                                <td id="detail-creator">-</td>
                            </tr>
                            <tr>
                                <th class="pl-3">Waktu Pencatatan</th>
                                <td id="detail-waktu">-</td>
                            </tr>
                            <tr>
                                <th class="pl-3">Keterangan / Catatan</th>
                                <td id="detail-keterangan">-</td>
                            </tr>
                            <tr id="row-detail-batal" style="display: none;" class="table-danger">
                                <th class="pl-3 text-danger"><i class="fas fa-ban mr-1"></i> Informasi Pembatalan</th>
                                <td id="detail-batal-info" class="text-danger">-</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light justify-content-between">
                <div id="detail-source-doc-link">
                    {{-- Dynamic button to open/print linked document --}}
                </div>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL BATALKAN NOMOR SURAT --}}
@can('manage-layanan-surat')
<div class="modal fade" id="modalBatalNomor" tabindex="-1" role="dialog" aria-labelledby="modalBatalNomorLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title font-weight-bold" id="modalBatalNomorLabel">
                    <i class="fas fa-exclamation-triangle mr-2"></i> Batalkan Nomor Surat
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-batal-nomor" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle mr-1"></i> Pembatalan tidak akan menghapus baris dari buku agenda. Nomor akan ditandai <strong>Dibatalkan</strong> dan alasan pembatalan tercatat permanen untuk kepatuhan audit kearsipan.
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Nomor Surat yang Dibatalkan:</label>
                        <div class="p-2 bg-light rounded font-weight-bold text-danger border" id="batal-nomor-display">-</div>
                    </div>
                    <div class="form-group mb-0">
                        <label for="input-alasan-batal" class="font-weight-bold">Alasan Pembatalan <span class="text-danger">*</span></label>
                        <textarea name="alasan_dibatalkan" id="input-alasan-batal" rows="3" class="form-control" placeholder="Contoh: Surat dibatalkan karena ada revisi data pemohon dan diterbitkan ulang..." required minlength="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger" id="btn-submit-batal">
                        <i class="fas fa-ban mr-1"></i> Ya, Batalkan Nomor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

{{-- MODAL PANDUAN KLASIFIKASI KEMENAG --}}
<div class="modal fade" id="modalPanduan" tabindex="-1" role="dialog" aria-labelledby="modalPanduanLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold" id="modalPanduanLabel">
                    <i class="fas fa-book mr-2"></i> Panduan Tata Naskah Dinas & Kode Klasifikasi Surat
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <h6 class="font-weight-bold text-primary mb-2"><i class="fas fa-barcode mr-1"></i> Struktur Format Nomor Surat Keluar:</h6>
                <div class="p-3 bg-dark text-white rounded mb-3 font-monospace" style="font-family: monospace;">
                    <strong>{prefix}-{nomor}/{kode_satker}/{kode_klasifikasi}/{bulan}/{tahun}</strong><br>
                    <span class="text-success">Contoh: B-023/Ma.08.01/PP.00.6/10/2026</span>
                </div>
                <ul class="small text-muted mb-4 pl-3">
                    <li><strong>{prefix}</strong>: Sifat surat (<code>B</code> = Biasa, <code>R</code> = Rahasia, <code>BA</code> = Berita Acara, dll).</li>
                    <li><strong>{nomor}</strong>: Nomor urut berurutan 3 digit (001, 002, ...) dalam 1 tahun kalender tunggal.</li>
                    <li><strong>{kode_satker}</strong>: Kode Satuan Kerja MAN 1 Metro adalah <code>Ma.08.01</code>.</li>
                    <li><strong>{kode_klasifikasi}</strong>: Kode urusan/substansi surat sesuai KMA 9/2016 & KMA 168/2022.</li>
                    <li><strong>{bulan} & {tahun}</strong>: 2 digit bulan penetapan dan 4 digit tahun penetapan.</li>
                </ul>

                <h6 class="font-weight-bold text-primary mb-2"><i class="fas fa-table mr-1"></i> Daftar Kode Klasifikasi Pokok di MAN 1 Metro:</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-striped small">
                        <thead class="thead-light">
                            <tr>
                                <th width="100">Kode</th>
                                <th width="180">Klasifikasi</th>
                                <th>Peruntukan di SIMANSA</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge badge-info">PP.00.6</span></td>
                                <td><strong>Kesiswaan</strong></td>
                                <td>Surat Keterangan Aktif Siswa, Surat Pindah/Mutasi, Rekomendasi Siswa Berprestasi, Beasiswa.</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-info">PP.01.1</span></td>
                                <td><strong>Kurikulum & Pembelajaran</strong></td>
                                <td>Surat Keterangan Lulus (SKL), Asesmen/Ujian, Penyelenggaraan Pembelajaran, Ijazah.</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-info">KP.01.1</span></td>
                                <td><strong>Kepegawaian (Umum)</strong></td>
                                <td>Surat Tugas Guru & Tenaga Kependidikan, Perjalanan Dinas (SPPD), Penugasan Pelatihan.</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-info">KP.01.2</span></td>
                                <td><strong>Cuti Guru & Pegawai</strong></td>
                                <td>Surat Permohonan / Persetujuan Cuti Tahunan, Melahirkan, Sakit, atau Alasan Penting.</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-info">KS.01.2</span></td>
                                <td><strong>Kedisiplinan & Bimbingan</strong></td>
                                <td>Surat Panggilan Orang Tua/Wali Siswa, Konseling BP/BK, Dispensasi Siswa.</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-info">HM.00</span></td>
                                <td><strong>Hubungan Masyarakat</strong></td>
                                <td>Undangan Rapat Komite/Wali Murid, Kerjasama Eksternal, Pemberitahuan Kegiatan.</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-info">KU.01.1</span></td>
                                <td><strong>Keuangan</strong></td>
                                <td>Surat Bebas Administrasi Keuangan, Konfirmasi Pembayaran Komite/BOS.</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-info">BA</span></td>
                                <td><strong>Berita Acara</strong></td>
                                <td>Berita Acara Serah Terima (BAST), Pelaksanaan Ujian/Asesmen, Pemusnahan Blangko.</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-info">SK</span></td>
                                <td><strong>Surat Keputusan</strong></td>
                                <td>SK Penetapan Pembagian Tugas Mengajar, SK Panitia, SK Kelulusan Siswa.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(function() {
    // Inisialisasi Select2
    $('.select2').select2({
        theme: 'bootstrap4'
    });

    // Setup DataTables
    var table = $('#table-buku-nomor').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.tata-usaha.buku-nomor-surat.index') }}",
            data: function (d) {
                d.tahun = $('#filter-tahun').val();
                d.kode_klasifikasi = $('#filter-klasifikasi').val();
                d.modul_asal = $('#filter-modul').val();
                d.status = $('#filter-status').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'nomor_urut', orderable: false, searchable: false, className: 'text-center align-middle' },
            { data: 'nomor_surat', name: 'nomor_surat', className: 'align-middle' },
            { data: 'tanggal_surat', name: 'tanggal_surat', className: 'text-center align-middle' },
            { data: 'klasifikasi', name: 'kode_klasifikasi', className: 'align-middle' },
            { data: 'perihal', name: 'perihal', className: 'align-middle' },
            { data: 'penandatangan_nama', name: 'nama_penandatangan', className: 'align-middle' },
            { data: 'modul_badge', name: 'modul_asal', className: 'text-center align-middle' },
            { data: 'status', name: 'status', className: 'text-center align-middle' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center align-middle' }
        ],
        order: [[1, 'desc']],
        pageLength: 25,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
        }
    });

    // Update filter event
    $('#filter-tahun, #filter-klasifikasi, #filter-modul, #filter-status').on('change', function() {
        table.ajax.reload();
        updatePrintAgendaLink();
    });

    function updatePrintAgendaLink() {
        var baseUrl = "{{ route('admin.tata-usaha.buku-nomor-surat.print-agenda') }}";
        var params = new URLSearchParams({
            tahun: $('#filter-tahun').val(),
            kode_klasifikasi: $('#filter-klasifikasi').val() || '',
            status: $('#filter-status').val() || ''
        });
        $('#btn-print-agenda').attr('href', baseUrl + '?' + params.toString());
    }

    // Live preview update in modal Ambil Nomor
    function updateLivePreview() {
        var kode = $('#input-kode').val();
        var tanggal = $('#input-tanggal').val();
        if (!kode) return;

        $.ajax({
            url: "{{ route('admin.tata-usaha.buku-nomor-surat.preview') }}",
            type: 'GET',
            data: { kode: kode, tanggal: tanggal },
            success: function(res) {
                if (res.success) {
                    $('#live-nomor-preview').text(res.preview);
                }
            }
        });
    }

    $('#input-kode, #input-tanggal').on('change', function() {
        updateLivePreview();
    });

    // Copy to clipboard helper
    $(document).on('click', '.btn-copy-nomor', function(e) {
        e.preventDefault();
        var text = $(this).data('nomor') || $(this).attr('data-nomor');
        if (!text) return;

        navigator.clipboard.writeText(text).then(function() {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Nomor surat disalin!',
                showConfirmButton: false,
                timer: 2000
            });
        }).catch(function() {
            prompt("Salin nomor surat manual:", text);
        });
    });

    // Submit Form Ambil Nomor Baru (AJAX)
    $('#form-ambil-nomor').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $('#btn-submit-ambil');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Terbitkan & Catat Nomor');
                if (res.success) {
                    $('#modalAmbilNomor').modal('hide');
                    form[0].reset();
                    $('#input-kode').val('PP.00.6').trigger('change');
                    table.ajax.reload();

                    Swal.fire({
                        title: 'Nomor Berhasil Diterbitkan!',
                        html: '<div class="alert alert-success mt-2"><strong>' + res.nomor_surat + '</strong></div><p class="small text-muted mb-0">Nomor telah dicatat dalam Buku Agenda dan counter nomor berjalan telah diperbarui.</p>',
                        icon: 'success',
                        showCancelButton: true,
                        confirmButtonText: '<i class="fas fa-copy mr-1"></i> Salin Nomor',
                        cancelButtonText: 'Tutup'
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            navigator.clipboard.writeText(res.nomor_surat);
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Nomor surat disalin!',
                                showConfirmButton: false,
                                timer: 2000
                            });
                        }
                    });

                    // Refresh hero counters if current selected year is active
                    setTimeout(function() { location.reload(); }, 2500);
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Terbitkan & Catat Nomor');
                var msg = 'Terjadi kesalahan saat menerbitkan nomor.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: msg
                });
            }
        });
    });

    // View Detail Modal
    $(document).on('click', '.btn-detail', function() {
        var id = $(this).data('id');
        var url = "{{ route('admin.tata-usaha.buku-nomor-surat.show', ':id') }}".replace(':id', id);

        $.get(url, function(res) {
            if (res.success) {
                var d = res.data;
                $('#detail-nomor-surat').text(d.nomor_surat);
                $('#detail-btn-copy').attr('data-nomor', d.nomor_surat);
                $('#detail-urut-tahun').text('No. Urut ' + d.nomor_urut + ' / Tahun ' + d.tahun);
                $('#detail-tanggal').text(d.tanggal_surat);
                $('#detail-klasifikasi').html('<span class="badge badge-info">' + d.kode_klasifikasi + '</span> - ' + d.nama_klasifikasi);
                $('#detail-perihal').text(d.perihal);
                $('#detail-tujuan').text(d.tujuan);
                $('#detail-penandatangan').text(d.penandatangan + ' (' + d.jabatan_penandatangan + ')');
                $('#detail-modul').text(d.modul_asal);
                $('#detail-creator').text(d.created_by);
                $('#detail-waktu').text(d.created_at);
                $('#detail-keterangan').text(d.keterangan);

                if (d.status_raw === 'dibatalkan') {
                    $('#detail-status-badge').removeClass('badge-success').addClass('badge-danger').text('Dibatalkan');
                    $('#row-detail-batal').show();
                    $('#detail-batal-info').html('Dibatalkan oleh <strong>' + d.dibatalkan_oleh + '</strong> pada ' + d.dibatalkan_at + '<br>Alasan: <em>"' + d.alasan_dibatalkan + '"</em>');
                } else {
                    $('#detail-status-badge').removeClass('badge-danger').addClass('badge-success').text('Aktif');
                    $('#row-detail-batal').hide();
                }

                if (d.surat_keterangan_print_url) {
                    $('#detail-source-doc-link').html('<a href="' + d.surat_keterangan_print_url + '" target="_blank" class="btn btn-outline-primary btn-sm"><i class="fas fa-print mr-1"></i> Cetak Dokumen Surat Keterangan</a>');
                } else {
                    $('#detail-source-doc-link').empty();
                }

                $('#modalDetailNomor').modal('show');
            }
        });
    });

    // Open Cancel Modal
    var activeCancelId = null;
    $(document).on('click', '.btn-cancel-nomor', function() {
        activeCancelId = $(this).data('id');
        var nomor = $(this).data('nomor');
        $('#batal-nomor-display').text(nomor);
        $('#input-alasan-batal').val('');
        $('#modalBatalNomor').modal('show');
    });

    // Submit Cancel Form
    $('#form-batal-nomor').on('submit', function(e) {
        e.preventDefault();
        if (!activeCancelId) return;

        var btn = $('#btn-submit-batal');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...');

        var url = "{{ route('admin.tata-usaha.buku-nomor-surat.cancel', ':id') }}".replace(':id', activeCancelId);

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                alasan_dibatalkan: $('#input-alasan-batal').val()
            },
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-ban mr-1"></i> Ya, Batalkan Nomor');
                $('#modalBatalNomor').modal('hide');
                table.ajax.reload();

                Swal.fire({
                    icon: 'success',
                    title: 'Nomor Dibatalkan',
                    text: res.message
                });
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-ban mr-1"></i> Ya, Batalkan Nomor');
                var msg = 'Gagal membatalkan nomor surat.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: msg
                });
            }
        });
    });
});
</script>
@stop

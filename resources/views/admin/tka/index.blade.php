@extends('adminlte::page')

@section('title', 'Manajemen Akun & Kartu TKA - SIMANSA')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="m-0 font-weight-bold text-dark">
                <i class="fas fa-id-card-alt text-primary mr-2"></i>Manajemen Akun & Kartu TKA
            </h1>
            <small class="text-muted">Kelola akun login, ruang, gelombang, sesi, dan jadwal Tes Kemampuan Akademik (TKA)</small>
        </div>
        <div class="mt-2 mt-md-0 d-flex flex-wrap" style="gap: 8px;">
            <button type="button" class="btn btn-outline-primary shadow-sm" data-toggle="modal" data-target="#modalImportPdf">
                <i class="fas fa-file-upload mr-1"></i> Impor PDF TKA
            </button>
            <a href="{{ route('admin.tka.cetak-massal', request()->query()) }}" target="_blank" class="btn btn-primary shadow-sm">
                <i class="fas fa-print mr-1"></i> Cetak Massal (4/Halaman)
            </a>
            <a href="{{ route('admin.tka.export-excel', request()->query()) }}" class="btn btn-success shadow-sm" data-no-overlay>
                <i class="fas fa-file-excel mr-1"></i> Ekspor Excel
            </a>
            <a href="{{ route('admin.tka.export', request()->query()) }}" class="btn btn-outline-secondary shadow-sm" data-no-overlay>
                <i class="fas fa-file-csv mr-1"></i> Ekspor CSV
            </a>
        </div>
    </div>
@stop

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <ul class="mb-0 pl-3">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

<!-- Statistik Ringkas -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info shadow-sm">
            <div class="inner">
                <h3>{{ number_format($stats['total_peserta']) }}</h3>
                <p>Total Peserta TKA {{ $selectedYear }}</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success shadow-sm">
            <div class="inner">
                <h3>{{ number_format($stats['matched_siswa']) }} <sup style="font-size: 16px;">/ {{ number_format($stats['total_peserta']) }}</sup></h3>
                <p>Terhubung ke Siswa SIMANSA</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-check"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary shadow-sm">
            <div class="inner">
                <h3>{{ number_format($stats['total_ruang']) }}</h3>
                <p>Total Ruang Ujian</p>
            </div>
            <div class="icon">
                <i class="fas fa-door-open"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning shadow-sm">
            <div class="inner">
                <h3>{{ number_format($stats['total_sesi']) }}</h3>
                <p>Varian Sesi Ujian</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>
</div>

<!-- Card Filter & Pencarian -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 border-bottom">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <h6 class="font-weight-bold text-dark mb-0">
                <i class="fas fa-filter text-primary mr-1"></i> Filter & Pencarian Cepat
            </h6>
            @if($stats['total_peserta'] > 0)
            <button type="button" class="btn btn-outline-danger btn-sm" data-toggle="modal" data-target="#modalHapusTahun">
                <i class="fas fa-trash-alt mr-1"></i> Hapus Semua Data Tahun {{ $selectedYear }}
            </button>
            @endif
        </div>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.tka.index') }}" id="filterForm">
            <div class="row">
                <div class="col-md-2 col-sm-6 mb-2">
                    <label class="small text-muted font-weight-bold">Tahun TKA</label>
                    <select name="tahun" class="form-control form-control-sm" onchange="this.form.submit()">
                        @if(empty($availableYears))
                            <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                        @else
                            @foreach($availableYears as $th)
                                <option value="{{ $th }}" {{ $selectedYear == $th ? 'selected' : '' }}>Tahun {{ $th }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-md-3 col-sm-6 mb-2">
                    <label class="small text-muted font-weight-bold">Pencarian (Nama / NISN / User)</label>
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control" placeholder="Ketik nama / NISN / user..." value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6 mb-2">
                    <label class="small text-muted font-weight-bold">Ruang</label>
                    <select name="ruang" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">-- Semua Ruang --</option>
                        @foreach($ruangOptions as $r)
                            <option value="{{ $r }}" {{ request('ruang') == $r ? 'selected' : '' }}>{{ $r }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 col-sm-6 mb-2">
                    <label class="small text-muted font-weight-bold">Gel.</label>
                    <select name="gelombang" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">Semua</option>
                        @foreach($gelombangOptions as $g)
                            <option value="{{ $g }}" {{ request('gelombang') == $g ? 'selected' : '' }}>{{ $g }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-sm-6 mb-2">
                    <label class="small text-muted font-weight-bold">Sesi</label>
                    <select name="sesi" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">-- Semua Sesi --</option>
                        @foreach($sesiOptions as $s)
                            <option value="{{ $s }}" {{ request('sesi') == $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-sm-6 mb-2">
                    <label class="small text-muted font-weight-bold">Kelas SIMANSA</label>
                    <select name="kelas_id" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">-- Semua Kelas 12 --</option>
                        @foreach($kelasOptions as $k)
                            <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @if(request()->anyFilled(['search', 'ruang', 'gelombang', 'sesi', 'kelas_id']))
                <div class="mt-2">
                    <a href="{{ route('admin.tka.index', ['tahun' => $selectedYear]) }}" class="btn btn-outline-secondary btn-xs">
                        <i class="fas fa-times mr-1"></i> Reset Filter
                    </a>
                    <span class="text-muted small ml-2">Menampilkan hasil yang difilter.</span>
                </div>
            @endif
        </form>
    </div>
</div>

<!-- Tabel Data Peserta TKA -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h6 class="font-weight-bold text-dark mb-0">
            <i class="fas fa-list text-primary mr-1"></i> Daftar Peserta TKA ({{ $pesertaList->total() }} Data)
        </h6>
        <small class="text-muted">Klik ikon salin pada username/password untuk menyalin instan saat hari ujian.</small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 text-nowrap">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 40px;" class="text-center">No</th>
                        <th>Peserta / Siswa</th>
                        <th>Kelas SIMANSA</th>
                        <th>Username (ID)</th>
                        <th>Password</th>
                        <th class="text-center">Ruang</th>
                        <th class="text-center">Gel.</th>
                        <th class="text-center">Sesi</th>
                        <th style="width: 120px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pesertaList as $idx => $p)
                        @php
                            $kelasNama = $p->siswa?->kelasSaatIni?->nama_kelas
                                ?? $p->siswa?->kelasAktif?->first()?->nama_kelas
                                ?? null;
                        @endphp
                        <tr>
                            <td class="text-center align-middle">{{ $pesertaList->firstItem() + $idx }}</td>
                            <td class="align-middle">
                                <div class="font-weight-bold text-dark">{{ $p->nama_peserta }}</div>
                                <div class="small text-muted">
                                    NISN: <span class="font-weight-bold text-monospace">{{ $p->nisn }}</span>
                                    @if($p->siswa)
                                        <span class="badge badge-success ml-1" title="Data terhubung ke akun siswa"><i class="fas fa-link"></i> Terhubung</span>
                                    @else
                                        <span class="badge badge-secondary ml-1" title="NISN tidak ditemukan di SIMANSA"><i class="fas fa-unlink"></i> Belum ada di DB</span>
                                    @endif
                                </div>
                            </td>
                            <td class="align-middle">
                                @if($kelasNama)
                                    <span class="badge badge-info">{{ $kelasNama }}</span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="align-middle">
                                <div class="d-flex align-items-center">
                                    <span class="font-weight-bold text-primary text-monospace mr-1" id="u-{{ $p->id }}">{{ $p->username }}</span>
                                    <button type="button" class="btn btn-xs btn-outline-primary py-0 px-1 btn-copy" data-target="u-{{ $p->id }}" title="Salin Username">
                                        <i class="far fa-copy"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="align-middle">
                                <div class="d-flex align-items-center">
                                    <span class="font-weight-bold text-danger text-monospace mr-1" id="p-{{ $p->id }}">{{ $p->password }}</span>
                                    <button type="button" class="btn btn-xs btn-outline-danger py-0 px-1 btn-copy" data-target="p-{{ $p->id }}" title="Salin Password">
                                        <i class="far fa-copy"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="text-center align-middle">
                                <strong class="text-dark">{{ $p->ruang ?: '-' }}</strong>
                                @if($p->id_proktor)
                                    <div class="small text-muted font-italic">{{ $p->id_proktor }}</div>
                                @endif
                            </td>
                            <td class="text-center align-middle font-weight-bold">{{ $p->gelombang ?: '-' }}</td>
                            <td class="text-center align-middle">
                                <span class="badge badge-success px-2 py-1">{{ $p->sesi ?: '-' }}</span>
                            </td>
                            <td class="text-center align-middle">
                                <a href="{{ route('admin.tka.cetak', $p->id) }}" target="_blank" class="btn btn-xs btn-outline-primary" title="Cetak Kartu Individual">
                                    <i class="fas fa-print"></i>
                                </a>
                                <form action="{{ route('admin.tka.destroy', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus data peserta {{ $p->nama_peserta }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-outline-danger" title="Hapus">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x text-secondary mb-2"></i>
                                <p class="mb-0">Tidak ada data peserta TKA ditemukan untuk filter ini.</p>
                                <p class="small text-muted">Silakan unggah file PDF TKA melalui tombol <strong>Impor PDF TKA</strong> di atas.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($pesertaList->hasPages())
    <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
        <small class="text-muted">
            Menampilkan {{ $pesertaList->firstItem() }} sampai {{ $pesertaList->lastItem() }} dari {{ $pesertaList->total() }} peserta
        </small>
        {{ $pesertaList->links() }}
    </div>
    @endif
</div>

<!-- Modal Impor PDF TKA -->
<div class="modal fade" id="modalImportPdf" tabindex="-1" role="dialog" aria-labelledby="modalImportPdfLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('admin.tka.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold" id="modalImportPdfLabel">
                        <i class="fas fa-file-upload mr-2"></i>Impor Kartu Login TKA dari PDF
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info small mb-3">
                        <i class="fas fa-info-circle mr-1"></i>
                        Unggah file PDF resmi kartu login Tes Kemampuan Akademik (misalnya: <code>Tes Kemampuan Akademik - Tahun 2026.pdf</code>).
                        Sistem akan otomatis mengekstrak seluruh data kartu, sesi, ruang, gelombang, username, password, dan menghubungkannya dengan akun siswa SIMANSA berdasarkan NISN.
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="font-weight-bold">File PDF TKA <span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" name="pdf_file" class="custom-file-input" id="pdfFileInput" accept=".pdf" required>
                            <label class="custom-file-label" for="pdfFileInput" id="pdfFileLabel">Pilih file PDF...</label>
                        </div>
                        <small class="form-text text-muted">Format: .pdf (Maksimal 50MB)</small>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Tahun TKA</label>
                        <input type="number" name="tahun" class="form-control" placeholder="Kosongkan untuk deteksi otomatis dari dokumen (cth: {{ date('Y') }})" min="2020" max="2050">
                        <small class="form-text text-muted">Jika dikosongkan, tahun akan dibaca otomatis dari judul kartu di PDF.</small>
                    </div>

                    <div class="form-group mb-0">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="replaceExisting" name="replace_existing" value="1">
                            <label class="custom-control-label font-weight-normal text-muted" for="replaceExisting">
                                Timpa (replace) seluruh data tahun tersebut jika sudah ada
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success font-weight-bold" id="btnSubmitImport">
                        <i class="fas fa-upload mr-1"></i> Mulai Impor Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Hapus Semua Data Tahun -->
<div class="modal fade" id="modalHapusTahun" tabindex="-1" role="dialog" aria-labelledby="modalHapusTahunLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('admin.tka.destroy-by-year') }}" method="POST">
                @csrf
                @method('DELETE')
                <input type="hidden" name="tahun" value="{{ $selectedYear }}">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title font-weight-bold" id="modalHapusTahunLabel">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Konfirmasi Hapus Data
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="text-danger mb-3" style="font-size: 3rem;">
                        <i class="fas fa-trash-alt"></i>
                    </div>
                    <h5>Hapus seluruh data TKA Tahun <strong>{{ $selectedYear }}</strong>?</h5>
                    <p class="text-muted small mb-0">
                        Tindakan ini akan menghapus <strong>{{ number_format($stats['total_peserta']) }}</strong> kartu peserta TKA tahun {{ $selectedYear }}. Anda tetap dapat mengimpor kembali file PDF jika dibutuhkan.
                    </p>
                </div>
                <div class="modal-footer bg-light justify-content-between">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger font-weight-bold">
                        <i class="fas fa-trash mr-1"></i> Ya, Hapus Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Custom file input label update
    $('#pdfFileInput').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $('#pdfFileLabel').text(fileName || 'Pilih file PDF...');
    });

    // Copy to clipboard
    $('.btn-copy').on('click', function() {
        var targetId = $(this).data('target');
        var text = $('#' + targetId).text().trim();
        var $btn = $(this);
        
        navigator.clipboard.writeText(text).then(function() {
            var originalHtml = $btn.html();
            $btn.html('<i class="fas fa-check text-success"></i>');
            setTimeout(function() {
                $btn.html(originalHtml);
            }, 1500);
        }).catch(function(err) {
            alert('Gagal menyalin: ' + text);
        });
    });

    // Loading indicator on import submit
    $('#modalImportPdf form').on('submit', function() {
        var $btn = $('#btnSubmitImport');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses PDF...');
        if (typeof window.showAppGlobalOverlay === 'function') {
            window.showAppGlobalOverlay('Sedang membaca dan memproses file PDF TKA... Mohon tunggu.', 180000);
        }
    });
});
</script>
@stop

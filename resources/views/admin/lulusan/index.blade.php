@extends('adminlte::page')

@section('title', 'Data Lulusan')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-user-graduate text-primary"></i> Data Lulusan</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item">Kesiswaan</li>
                <li class="breadcrumb-item active">Lulusan</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="simansa-lulusan-page">
    <div class="card bg-gradient-primary text-white simansa-lulusan-hero mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h3 class="mb-1"><i class="fas fa-university mr-1"></i> Rekap Studi Lanjut / PTN Kelas 12</h3>
                    <p class="mb-2 text-white-50">
                        Pantau pendataan siswa ke perguruan tinggi, hasil checker jalur PTN, serta kampus tujuan dalam satu laporan.
                    </p>
                    <p class="mb-0">Seluruh ringkasan, tabel, matriks, dan file export mengikuti tahun pelajaran yang dipilih.</p>
                </div>
                <div class="col-lg-4 mt-3 mt-lg-0">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="text-white-50 small text-uppercase font-weight-bold">Siswa Kelas 12</div>
                            <h3 class="mb-0 text-white" id="heroSummaryTotal">0</h3>
                        </div>
                        <div class="col-6">
                            <div class="text-white-50 small text-uppercase font-weight-bold">Sudah Mengisi</div>
                            <h3 class="mb-0 text-white" id="heroSummarySudahIsi">0</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-outline {{ $setting->lulusan_data_enabled ? 'card-success' : 'card-secondary' }}">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-user-clock mr-2"></i> Akses Pengisian Data PTN oleh Siswa
            </h3>
            <div class="card-tools">
                <span class="badge {{ $setting->lulusan_data_enabled ? 'badge-success' : 'badge-secondary' }} p-2">
                    {{ $setting->lulusan_data_enabled ? 'Dibuka' : 'Ditutup' }}
                </span>
            </div>
        </div>
        <form action="{{ route('admin.lulusan.student-access') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="alert alert-light border mb-3">
                    <i class="fas fa-info-circle text-primary mr-1"></i>
                    Menu <strong>Data Lulusan</strong> adalah pendataan kampus dan program studi. Menu hanya tampil untuk
                    siswa kelas 12 pada angkatan dan periode di bawah. Siswa berstatus lulus/alumni tetap dapat memperbarui
                    riwayatnya setelah periode siswa aktif ditutup.
                </div>
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="lulusan_data_tahun_pelajaran_id">Angkatan / Tahun Kelas 12</label>
                            <select id="lulusan_data_tahun_pelajaran_id" name="lulusan_data_tahun_pelajaran_id" class="form-control" required>
                                @foreach($tahunPelajaranList as $tahun)
                                    <option value="{{ $tahun->id }}" {{ old('lulusan_data_tahun_pelajaran_id', $setting->lulusan_data_tahun_pelajaran_id ?: optional($selectedTahun)->id) === $tahun->id ? 'selected' : '' }}>
                                        {{ $tahun->nama }}{{ $tahun->is_active ? ' (Aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="lulusan_data_starts_at">Mulai Tampil</label>
                            <input type="datetime-local" id="lulusan_data_starts_at" name="lulusan_data_starts_at"
                                class="form-control" value="{{ old('lulusan_data_starts_at', optional($setting->lulusan_data_starts_at)->format('Y-m-d\TH:i')) }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="lulusan_data_ends_at">Tutup untuk Siswa Aktif</label>
                            <input type="datetime-local" id="lulusan_data_ends_at" name="lulusan_data_ends_at"
                                class="form-control" value="{{ old('lulusan_data_ends_at', optional($setting->lulusan_data_ends_at)->format('Y-m-d\TH:i')) }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group d-flex flex-column">
                            <button type="submit" name="lulusan_data_enabled" value="{{ $setting->lulusan_data_enabled ? 1 : 0 }}" class="btn btn-outline-primary mb-2">
                                <i class="fas fa-save mr-1"></i> Simpan Periode
                            </button>
                            <button type="submit" name="lulusan_data_enabled" value="{{ $setting->lulusan_data_enabled ? 0 : 1 }}"
                                class="btn {{ $setting->lulusan_data_enabled ? 'btn-outline-secondary' : 'btn-success' }}">
                                <i class="fas {{ $setting->lulusan_data_enabled ? 'fa-eye-slash' : 'fa-eye' }} mr-1"></i>
                                {{ $setting->lulusan_data_enabled ? 'Tutup Akses' : 'Buka Akses' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter mr-2"></i> Filter dan Aksi Laporan</h3>
            <div class="card-tools">
                <span class="badge badge-primary p-2" id="selectedYearContext">
                    Tahun {{ optional($selectedTahun)->nama ?? '-' }}
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="simansa-filter-panel mb-0">
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="simansa-filter-label"><i class="fas fa-calendar-alt mr-1"></i> Tahun Pelajaran</label>
                        <select id="filterTahunPelajaran" class="form-control form-control-sm">
                            @foreach($tahunPelajaranList as $tahun)
                                <option value="{{ $tahun->id }}" {{ optional($selectedTahun)->id === $tahun->id ? 'selected' : '' }}>
                                    {{ $tahun->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="simansa-filter-label"><i class="fas fa-clipboard-check mr-1"></i> Status Pengisian</label>
                        <select id="filterStatusPengisian" class="form-control form-control-sm">
                            <option value="">Semua Status</option>
                            <option value="sudah_isi">Sudah Isi</option>
                            <option value="belum_isi">Belum Isi</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="simansa-filter-label"><i class="fas fa-tasks mr-1"></i> Mode Checker</label>
                        <select id="filterTrackerType" class="form-control form-control-sm">
                            <option value="ALL">Semua Jalur</option>
                            <option value="SNBP">SNBP</option>
                            <option value="SPAN-PTKIN">SPAN-PTKIN</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="simansa-filter-label"><i class="fas fa-route mr-1"></i> Jalur Masuk</label>
                        <select id="filterJalurMasuk" class="form-control form-control-sm">
                            <option value="">Semua Jalur</option>
                            @foreach($jalurMasukOptions as $jalur)
                                <option value="{{ $jalur }}">{{ $jalur }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="simansa-filter-label"><i class="fas fa-search mr-1"></i> Pencarian</label>
                        <input type="text" id="filterPencarian" class="form-control form-control-sm" placeholder="Nama, NISN, kampus, prodi">
                    </div>
                </div>
            </div>
            </div>
        </div>
        <div class="card-footer simansa-lulusan-toolbar">
            <div class="simansa-lulusan-toolbar__group">
                <button type="button" id="btnApplyFilter" class="btn simansa-btn-strong">
                    <i class="fas fa-filter mr-1"></i> Terapkan Filter
                </button>
                <button type="button" id="btnResetFilter" class="btn btn-secondary">
                    <i class="fas fa-redo mr-1"></i> Reset
                </button>
                <button type="button" id="btnSendGraduationEmail" class="btn btn-info">
                    <i class="fas fa-envelope mr-1"></i> Kirim Pengumuman
                </button>
                @can('kesiswaan-lulusan-access')
                    <a href="{{ route('admin.snbp-menu.index') }}" id="btnCheckerSnbp" class="btn btn-primary">
                        <i class="fas fa-graduation-cap mr-1"></i> Checker SNBP
                    </a>
                    <a href="{{ route('admin.span-ptkin-menu.index') }}" id="btnCheckerSpanPtkin" class="btn btn-success">
                        <i class="fas fa-mosque mr-1"></i> Checker SPAN-PTKIN
                    </a>
                @endcan
            </div>
            <div class="simansa-lulusan-toolbar__group">
                <a href="#" id="btnExportExcel" class="btn btn-success" data-no-overlay>
                    <i class="fas fa-file-excel mr-1"></i> Export XLS
                </a>
                <a href="#" id="btnExportPdf" class="btn btn-danger" data-no-overlay target="_blank" rel="noopener">
                    <i class="fas fa-file-pdf mr-1"></i> Export PDF
                </a>
            </div>
        </div>
    </div>

    @if($selectedTahun)
        <div class="row simansa-summary-grid">
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card card-outline card-primary h-100 simansa-summary-card">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase font-weight-bold">Total Kelas 12</div>
                        <h3 class="text-primary mb-1" id="summaryTotal">0</h3>
                        <div class="text-muted">Siswa kelas 12 pada tahun pelajaran terpilih.</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card card-outline card-success h-100 simansa-summary-card">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase font-weight-bold">Sudah Mengisi</div>
                        <h3 class="text-success mb-1" id="summarySudahIsi">0</h3>
                        <div class="text-muted">Data rencana studi lanjut yang sudah dilengkapi.</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card card-outline card-warning h-100 simansa-summary-card">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase font-weight-bold">Belum Mengisi</div>
                        <h3 class="text-warning mb-1" id="summaryBelumIsi">0</h3>
                        <div class="text-muted">Siswa yang masih perlu melengkapi data lulusan.</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card card-outline card-info h-100 simansa-summary-card">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase font-weight-bold">Universitas Tujuan</div>
                        <h3 class="text-info mb-1" id="summaryUniversitas">0</h3>
                        <div class="text-muted">Kampus unik yang dipilih oleh siswa.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row simansa-checker-grid mb-4">
            <div class="col-sm-6 col-md-4 col-xl-2 mb-3">
                <div class="card card-outline card-info h-100 simansa-mini-stat">
                    <div class="card-body">
                        <i class="fas fa-user-check text-info"></i>
                        <div><span class="simansa-mini-stat__label" id="summaryEligibleLabel">Peserta Checker</span><strong id="summaryEligible">0</strong></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-4 col-xl-2 mb-3">
                <div class="card card-outline card-secondary h-100 simansa-mini-stat">
                    <div class="card-body">
                        <i class="fas fa-id-card text-secondary"></i>
                        <div><span class="simansa-mini-stat__label" id="summaryEligibleIsiLabel">Sudah Ada Nomor</span><strong id="summaryEligibleIsi">0</strong></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-4 col-xl-2 mb-3">
                <div class="card card-outline card-success h-100 simansa-mini-stat">
                    <div class="card-body">
                        <i class="fas fa-award text-success"></i>
                        <div><span class="simansa-mini-stat__label" id="summaryEligibleLulusLabel">Lulus Checker</span><strong id="summaryEligibleLulus">0</strong></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-4 col-xl-2 mb-3">
                <div class="card card-outline card-danger h-100 simansa-mini-stat">
                    <div class="card-body">
                        <i class="fas fa-times-circle text-danger"></i>
                        <div><span class="simansa-mini-stat__label" id="summaryEligibleTidakLulusLabel">Tidak Lulus Checker</span><strong id="summaryEligibleTidakLulus">0</strong></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-4 col-xl-2 mb-3">
                <div class="card card-outline card-warning h-100 simansa-mini-stat">
                    <div class="card-body">
                        <i class="fas fa-exclamation-triangle text-warning"></i>
                        <div><span class="simansa-mini-stat__label" id="summaryEligibleGagalLabel">Gagal Cek</span><strong id="summaryEligibleGagal">0</strong></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-4 col-xl-2 mb-3">
                <div class="card card-outline card-secondary h-100 simansa-mini-stat">
                    <div class="card-body">
                        <i class="fas fa-hourglass-half text-dark"></i>
                        <div><span class="simansa-mini-stat__label" id="summaryEligibleBelumDicekLabel">Belum Dicek</span><strong id="summaryEligibleBelumDicek">0</strong></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4">
                <div class="card card-outline card-success h-100">
                    <div class="card-header">
                        <h3 class="card-title">Statistik per Jalur</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Jalur</th>
                                    <th class="text-right">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody id="perJalurTable">
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-3">Memuat statistik...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card card-outline card-secondary h-100">
                    <div class="card-header">
                        <h3 class="card-title" id="checkerStatusTitle">Status Checker SNBP</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th class="text-right">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody id="checkerStatusTable">
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-3">Memuat statistik...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card card-outline card-primary h-100">
                    <div class="card-header">
                        <h3 class="card-title" id="topTrackerUniversityTitle">Top PTN Diterima SNBP</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Perguruan Tinggi</th>
                                    <th class="text-right">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody id="topPtnSnbpTable">
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-3">Memuat statistik...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="card card-outline card-info h-100">
                    <div class="card-header">
                        <h3 class="card-title">Top Universitas Tujuan</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Universitas</th>
                                    <th class="text-right">Jumlah Siswa</th>
                                </tr>
                            </thead>
                            <tbody id="topUniversitasTable">
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-3">Memuat statistik...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card card-outline card-warning h-100">
                    <div class="card-header">
                        <h3 class="card-title" id="topTrackerProgramTitle">Top Prodi Diterima SNBP</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Program Studi</th>
                                    <th class="text-right">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody id="topProdiSnbpTable">
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-3">Memuat statistik...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-graduate mr-2"></i> Daftar Lulusan Kelas 12</h3>
            </div>
            <div class="card-body">
                <div class="simansa-table-note">
                    Daftar siswa, status pengisian, dan hasil checker di bawah ini mengikuti seluruh filter laporan yang sedang aktif.
                </div>
                <div class="simansa-table-scroll">
                    <table id="lulusanTable" class="table table-hover table-sm simansa-lulusan-table">
                        <thead>
                            <tr>
                                <th>NISN</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Status</th>
                                <th>Jalur</th>
                                <th id="checkerResultLabel">Hasil SNBP</th>
                                <th id="checkerColumnLabel">Checker SNBP</th>
                                <th>Universitas</th>
                                <th>Jurusan/Fakultas</th>
                                <th>Program Studi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

        <div class="card card-outline card-warning">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-table mr-2"></i> Matriks Laporan per Kelas dan Jalur</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover table-sm mb-0 simansa-lulusan-table" id="matrixTable">
                    <thead>
                        <tr>
                            <th>Kelas</th>
                            @foreach($jalurMasukOptions as $jalur)
                                <th class="text-center">{{ $jalur }}</th>
                            @endforeach
                            <th class="text-center">Eligible</th>
                            <th class="text-center" id="matrixTrackerLabel">Lulus SNBP</th>
                            <th class="text-center">Tidak Lulus</th>
                            <th class="text-center">Sudah Isi</th>
                            <th class="text-center">Belum Isi</th>
                            <th class="text-center">Total</th>
                        </tr>
                    </thead>
                    <tbody id="matrixTableBody">
                        <tr>
                            <td colspan="{{ count($jalurMasukOptions) + 6 }}" class="text-center text-muted py-3">Memuat matriks laporan...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="alert alert-warning">
            Belum ada tahun pelajaran yang tersedia.
        </div>
    @endif

    <div class="modal fade" id="graduationEmailModal" tabindex="-1" role="dialog" aria-labelledby="graduationEmailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title" id="graduationEmailModalLabel">
                        <i class="fas fa-envelope mr-1"></i> Kirim Email Pengumuman Kelulusan
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-light border">
                        Email akan dikirim ke siswa kelas 12 sesuai filter aktif dan memakai template email <code>graduation_announcement</code>.
                        Jika admin mengubah template di menu <strong>Template Email</strong>, isi email berikutnya akan ikut berubah.
                    </div>
                    <div class="form-group">
                        <label for="graduationEmailNote">Catatan Admin Tambahan</label>
                        <textarea id="graduationEmailNote" class="form-control" rows="5" placeholder="Tambahkan informasi tambahan untuk disisipkan ke placeholder [catatan_admin]."></textarea>
                        <small class="form-text text-muted">
                            Catatan ini opsional. Jika kosong, sistem akan memakai catatan default yang sopan dan informatif.
                        </small>
                    </div>
                    <div class="small text-muted" id="graduationEmailFilterSummary"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-info" id="btnSubmitGraduationEmail">
                        <i class="fas fa-paper-plane mr-1"></i> Kirim Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
    <link rel="stylesheet" href="//cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
    <style>
        .simansa-lulusan-page .simansa-lulusan-hero {
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 14px 30px rgba(37, 99, 235, .16);
            overflow: hidden;
        }

        .simansa-lulusan-page .simansa-lulusan-hero .card-body {
            padding: 1.35rem 1.5rem;
        }

        .simansa-lulusan-page .simansa-lulusan-toolbar,
        .simansa-lulusan-page .simansa-lulusan-toolbar__group {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .5rem;
        }

        .simansa-lulusan-page .simansa-lulusan-toolbar {
            justify-content: space-between;
        }

        .simansa-lulusan-page .simansa-lulusan-toolbar .btn {
            margin: 0 !important;
            border-radius: .5rem;
            font-size: .78rem;
            font-weight: 700;
            padding: .38rem .62rem;
        }

        .simansa-lulusan-page .simansa-summary-card {
            box-shadow: 0 8px 20px rgba(15, 23, 42, .05);
        }

        .simansa-lulusan-page .simansa-summary-card .text-muted:last-child {
            font-size: .82rem;
            line-height: 1.4;
        }

        .simansa-lulusan-page .simansa-mini-stat {
            margin-bottom: 0;
            box-shadow: 0 7px 18px rgba(15, 23, 42, .05);
        }

        .simansa-lulusan-page .simansa-mini-stat .card-body {
            display: flex;
            align-items: center;
            gap: .7rem;
            min-height: 76px;
        }

        .simansa-lulusan-page .simansa-mini-stat i {
            width: 1.6rem;
            flex: 0 0 1.6rem;
            font-size: 1.35rem;
            text-align: center;
        }

        .simansa-lulusan-page .simansa-mini-stat__label,
        .simansa-lulusan-page .simansa-mini-stat strong {
            display: block;
        }

        .simansa-lulusan-page .simansa-mini-stat__label {
            min-height: 2.15em;
            color: #64748b;
            font-size: .7rem;
            font-weight: 800;
            line-height: 1.08;
            letter-spacing: .035em;
            text-transform: uppercase;
        }

        .simansa-lulusan-page .simansa-mini-stat strong {
            margin-top: .25rem;
            color: #0f172a;
            font-size: 1.2rem;
            line-height: 1;
        }

        .simansa-lulusan-page .simansa-table-note {
            margin-bottom: .85rem;
            padding: .72rem .85rem;
            border: 1px solid #dbeafe;
            border-radius: .75rem;
            background: #eff6ff;
            color: #1e3a8a;
            font-size: .86rem;
            font-weight: 600;
            line-height: 1.45;
        }

        .simansa-lulusan-page .simansa-table-scroll {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .simansa-lulusan-page .simansa-lulusan-table thead th,
        .simansa-lulusan-page .card-body.p-0 .table thead th {
            padding: .62rem .65rem;
            border-top: 0;
            border-bottom: 1px solid #cbd5e1;
            background: #f1f5f9;
            color: #334155;
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .035em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .simansa-lulusan-page .simansa-lulusan-table tbody td {
            padding: .52rem .65rem;
            border-top: 0;
            border-bottom: 1px solid #f1f5f9;
            color: #0f172a;
            font-size: .82rem;
            vertical-align: middle;
        }

        .simansa-lulusan-page .simansa-lulusan-table tbody tr:hover td {
            background: #f0f7ff;
        }

        .simansa-lulusan-page #matrixTable th,
        .simansa-lulusan-page #matrixTable td {
            white-space: nowrap;
            vertical-align: middle;
        }

        @media (max-width: 767.98px) {
            .simansa-lulusan-page .simansa-lulusan-hero .card-body {
                padding: 1rem;
            }

            .simansa-lulusan-page .simansa-lulusan-toolbar,
            .simansa-lulusan-page .simansa-lulusan-toolbar__group {
                width: 100%;
            }

            .simansa-lulusan-page .simansa-lulusan-toolbar .btn {
                flex: 1 1 auto;
            }
        }
    </style>
@stop

@section('js')
    <script src="//cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="//cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
    <script>
        let lulusanTable;
        const jalurMasukOptions = @json($jalurMasukOptions);
        const checkerLinksByTahun = @json($checkerLinksByTahun);
        const defaultTrackerMeta = {
            summary_total_label: 'Peserta Checker',
            summary_number_label: 'Sudah Ada Nomor',
            summary_passed_label: 'Lulus Checker',
            summary_failed_label: 'Tidak Lulus Checker',
            summary_error_label: 'Gagal Cek',
            summary_pending_label: 'Belum Dicek',
            checker_title: 'Status Checker Semua Jalur',
            top_university_title: 'Top Kampus Diterima Checker',
            top_program_title: 'Top Prodi Diterima Checker',
            checker_column_label: 'Checker',
            result_column_label: 'Hasil Checker',
            matrix_tracker_label: 'Lulus Checker',
            empty_university_text: 'Belum ada siswa diterima via checker.',
            empty_program_text: 'Belum ada prodi dari checker.',
            type: 'Semua Jalur'
        };

        function getFilters() {
            return {
                tahun_pelajaran_id: $('#filterTahunPelajaran').val(),
                status_pengisian: $('#filterStatusPengisian').val(),
                tracker_type: $('#filterTrackerType').val(),
                jalur_masuk: $('#filterJalurMasuk').val(),
                q: $('#filterPencarian').val()
            };
        }

        function updateExportLinks() {
            const params = new URLSearchParams(getFilters()).toString();
            $('#btnExportExcel').attr('href', `{{ route('admin.lulusan.export-excel') }}?${params}`);
            $('#btnExportPdf').attr('href', `{{ route('admin.lulusan.export-pdf') }}?${params}`);
        }

        function updateSelectedYearContext() {
            const tahunId = $('#filterTahunPelajaran').val();
            const tahunLabel = $('#filterTahunPelajaran option:selected').text().trim() || '-';
            const checkerLinks = checkerLinksByTahun[tahunId] || {};

            $('#selectedYearContext').text(`Tahun ${tahunLabel}`);
            $('#btnCheckerSnbp')
                .attr('href', checkerLinks.snbp || `{{ route('admin.snbp-menu.index') }}`)
                .attr('title', checkerLinks.has_snbp ? `Buka checker SNBP ${tahunLabel}` : `Menu SNBP ${tahunLabel} belum tersedia`);
            $('#btnCheckerSpanPtkin')
                .attr('href', checkerLinks.span_ptkin || `{{ route('admin.span-ptkin-menu.index') }}`)
                .attr('title', checkerLinks.has_span_ptkin ? `Buka checker SPAN-PTKIN ${tahunLabel}` : `Menu SPAN-PTKIN ${tahunLabel} belum tersedia`);
        }

        function parseDownloadFilename(disposition, fallback) {
            if (!disposition) return fallback;

            const utfMatch = disposition.match(/filename\*=UTF-8''([^;]+)/i);
            if (utfMatch && utfMatch[1]) {
                try {
                    return decodeURIComponent(utfMatch[1].replace(/"/g, ''));
                } catch (e) {
                    return utfMatch[1].replace(/"/g, '');
                }
            }

            const regularMatch = disposition.match(/filename="?([^";]+)"?/i);
            return regularMatch && regularMatch[1] ? regularMatch[1] : fallback;
        }

        async function downloadLulusanExcel(url) {
            const $btn = $('#btnExportExcel');
            const originalHtml = $btn.html();

            if (window.showAppGlobalOverlay) {
                window.showAppGlobalOverlay('Menyiapkan export XLS...', 'File sedang dibuat, mohon tunggu');
            }

            $btn.addClass('disabled').attr('aria-disabled', 'true')
                .html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyiapkan...');

            try {
                const response = await fetch(url, {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const blob = await response.blob();
                const firstBytes = new Uint8Array(await blob.slice(0, 4).arrayBuffer());
                const isXlsx = firstBytes[0] === 0x50 && firstBytes[1] === 0x4B && firstBytes[2] === 0x03 && firstBytes[3] === 0x04;

                if (!response.ok || !isXlsx) {
                    let detail = `HTTP ${response.status}`;
                    try {
                        const text = await blob.text();
                        const cleaned = text.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
                        if (cleaned) {
                            detail = cleaned.substring(0, 300);
                        }
                    } catch (e) {
                        // Keep default detail.
                    }

                    throw new Error(detail || 'Server tidak mengirim file XLSX yang valid.');
                }

                const filename = parseDownloadFilename(
                    response.headers.get('Content-Disposition'),
                    `laporan_lulusan_${new Date().toISOString().slice(0, 10)}.xlsx`
                );
                const blobUrl = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = blobUrl;
                link.download = filename.endsWith('.xlsx') ? filename : `${filename}.xlsx`;
                document.body.appendChild(link);
                link.click();
                link.remove();

                setTimeout(() => URL.revokeObjectURL(blobUrl), 30000);
            } catch (error) {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Export XLS gagal',
                        text: error.message || 'File export tidak valid.'
                    });
                } else {
                    alert(error.message || 'Export XLS gagal.');
                }
            } finally {
                if (window.hideAppGlobalOverlay) {
                    window.hideAppGlobalOverlay();
                }

                $btn.removeClass('disabled').removeAttr('aria-disabled').html(originalHtml);
            }
        }

        function updateGraduationEmailSummary() {
            const filters = getFilters();
            const summary = [
                `Tahun: ${$('#filterTahunPelajaran option:selected').text() || '-'}`,
                `Status: ${$('#filterStatusPengisian option:selected').text() || 'Semua Status'}`,
                `Checker: ${$('#filterTrackerType option:selected').text() || 'Semua Jalur'}`,
                `Jalur: ${$('#filterJalurMasuk option:selected').text() || 'Semua Jalur'}`,
                `Pencarian: ${filters.q || '-'}`
            ];

            $('#graduationEmailFilterSummary').text(summary.join(' | '));
        }

        function renderPerJalur(perJalur) {
            const tbody = $('#perJalurTable');
            tbody.empty();

            if (!perJalur || Object.keys(perJalur).length === 0) {
                tbody.html('<tr><td colspan="2" class="text-center text-muted py-3">Tidak ada data.</td></tr>');
                return;
            }

            Object.entries(perJalur).forEach(([jalur, jumlah]) => {
                tbody.append(`
                    <tr>
                        <td>${jalur}</td>
                        <td class="text-right font-weight-bold">${jumlah}</td>
                    </tr>
                `);
            });
        }

        function renderTopUniversitas(topUniversitas) {
            const tbody = $('#topUniversitasTable');
            tbody.empty();

            if (!topUniversitas || topUniversitas.length === 0) {
                tbody.html('<tr><td colspan="2" class="text-center text-muted py-3">Belum ada data universitas.</td></tr>');
                return;
            }

            topUniversitas.forEach(item => {
                const label = item.label ?? item.nama_universitas ?? '-';
                tbody.append(`
                    <tr>
                        <td>${label}</td>
                        <td class="text-right font-weight-bold">${item.jumlah ?? 0}</td>
                    </tr>
                `);
            });
        }

        function renderCheckerStatus(checkerStatus) {
            const tbody = $('#checkerStatusTable');
            tbody.empty();

            const labels = {
                belum_dicek: 'Belum Dicek',
                lulus: 'Lulus',
                tidak_lulus: 'Tidak Lulus',
                gagal_cek: 'Gagal Cek'
            };

            if (!checkerStatus || Object.keys(checkerStatus).length === 0) {
                tbody.html('<tr><td colspan="2" class="text-center text-muted py-3">Tidak ada data.</td></tr>');
                return;
            }

            Object.entries(labels).forEach(([key, label]) => {
                tbody.append(`
                    <tr>
                        <td>${label}</td>
                        <td class="text-right font-weight-bold">${checkerStatus[key] ?? 0}</td>
                    </tr>
                `);
            });
        }

        function renderTopSimpleTable(selector, rows, emptyText = 'Belum ada data.') {
            const tbody = $(selector);
            tbody.empty();

            if (!rows || rows.length === 0) {
                tbody.html(`<tr><td colspan="2" class="text-center text-muted py-3">${emptyText}</td></tr>`);
                return;
            }

            rows.forEach(item => {
                const label = item.label ?? item.nama_universitas ?? item.program_studi ?? '-';
                tbody.append(`
                    <tr>
                        <td>${label}</td>
                        <td class="text-right font-weight-bold">${item.jumlah ?? 0}</td>
                    </tr>
                `);
            });
        }

        function applyTrackerMeta(meta) {
            const trackerMeta = Object.assign({}, defaultTrackerMeta, meta || {});
            $('#summaryEligibleLabel').text(trackerMeta.summary_total_label);
            $('#summaryEligibleIsiLabel').text(trackerMeta.summary_number_label);
            $('#summaryEligibleLulusLabel').text(trackerMeta.summary_passed_label);
            $('#summaryEligibleTidakLulusLabel').text(trackerMeta.summary_failed_label);
            $('#summaryEligibleGagalLabel').text(trackerMeta.summary_error_label);
            $('#summaryEligibleBelumDicekLabel').text(trackerMeta.summary_pending_label);
            $('#checkerStatusTitle').text(trackerMeta.checker_title);
            $('#topTrackerUniversityTitle').text(trackerMeta.top_university_title);
            $('#topTrackerProgramTitle').text(trackerMeta.top_program_title);
            $('#checkerColumnLabel').text(trackerMeta.checker_column_label);
            $('#checkerResultLabel').text(trackerMeta.result_column_label);
            $('#matrixTrackerLabel').text(trackerMeta.matrix_tracker_label);
        }

        function renderMatrix(perKelas) {
            const tbody = $('#matrixTableBody');
            tbody.empty();

            if (!perKelas || perKelas.length === 0) {
                tbody.html(`<tr><td colspan="${jalurMasukOptions.length + 7}" class="text-center text-muted py-3">Tidak ada data untuk filter ini.</td></tr>`);
                return;
            }

            perKelas.forEach(item => {
                const jalurCells = jalurMasukOptions.map(jalur => `<td class="text-center">${item.jalur[jalur] ?? 0}</td>`).join('');

                tbody.append(`
                    <tr>
                        <td>${item.kelas_nama}</td>
                        ${jalurCells}
                        <td class="text-center font-weight-bold text-info">${item.eligible ?? 0}</td>
                        <td class="text-center font-weight-bold text-primary">${item.eligible_lulus ?? 0}</td>
                        <td class="text-center font-weight-bold text-danger">${item.eligible_tidak_lulus ?? 0}</td>
                        <td class="text-center font-weight-bold text-success">${item.sudah_isi}</td>
                        <td class="text-center font-weight-bold text-warning">${item.belum_isi}</td>
                        <td class="text-center font-weight-bold">${item.total}</td>
                    </tr>
                `);
            });
        }

        function loadStats() {
            $.ajax({
                url: '{{ route('admin.lulusan.stats') }}',
                data: getFilters(),
                success: function(response) {
                    const trackerMeta = response.tracker_meta || defaultTrackerMeta;
                    applyTrackerMeta(trackerMeta);
                    $('#summaryTotal').text(response.summary.total ?? 0);
                    $('#summarySudahIsi').text(response.summary.sudah_isi ?? 0);
                    $('#heroSummaryTotal').text(response.summary.total ?? 0);
                    $('#heroSummarySudahIsi').text(response.summary.sudah_isi ?? 0);
                    $('#summaryBelumIsi').text(response.summary.belum_isi ?? 0);
                    $('#summaryUniversitas').text(response.summary.total_universitas ?? 0);
                    $('#summaryEligible').text(response.summary.eligible_total ?? 0);
                    $('#summaryEligibleIsi').text(response.summary.eligible_sudah_isi_nomor ?? 0);
                    $('#summaryEligibleLulus').text(response.summary.eligible_lulus ?? 0);
                    $('#summaryEligibleTidakLulus').text(response.summary.eligible_tidak_lulus ?? 0);
                    $('#summaryEligibleGagal').text(response.summary.eligible_gagal_cek ?? 0);
                    $('#summaryEligibleBelumDicek').text(response.summary.eligible_belum_dicek ?? 0);

                    renderPerJalur(response.per_jalur);
                    renderTopUniversitas(response.top_universitas);
                    renderCheckerStatus(response.checker_status);
                    renderTopSimpleTable('#topPtnSnbpTable', response.top_tracker_universitas, trackerMeta.empty_university_text || defaultTrackerMeta.empty_university_text);
                    renderTopSimpleTable('#topProdiSnbpTable', response.top_tracker_prodi, trackerMeta.empty_program_text || defaultTrackerMeta.empty_program_text);
                    renderMatrix(response.per_kelas);
                },
                error: function() {
                    applyTrackerMeta(defaultTrackerMeta);
                    $('#heroSummaryTotal, #heroSummarySudahIsi, #summaryTotal, #summarySudahIsi, #summaryBelumIsi, #summaryUniversitas, #summaryEligible, #summaryEligibleIsi, #summaryEligibleLulus, #summaryEligibleTidakLulus, #summaryEligibleGagal, #summaryEligibleBelumDicek').text('0');
                    renderPerJalur({});
                    renderTopUniversitas([]);
                    renderCheckerStatus({});
                    renderTopSimpleTable('#topPtnSnbpTable', []);
                    renderTopSimpleTable('#topProdiSnbpTable', []);
                    renderMatrix([]);
                }
            });
        }

        function reloadLulusanData() {
            const search = $('#filterPencarian').val();
            updateSelectedYearContext();
            lulusanTable.search(search).draw();
            updateExportLinks();
            loadStats();
        }

        $(function () {
            lulusanTable = $('#lulusanTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: '{{ route('admin.lulusan.data') }}',
                    data: function (d) {
                        Object.assign(d, getFilters());
                    }
                },
                columns: [
                    { data: 'nisn', name: 'siswa.nisn' },
                    { data: 'nama_lengkap', name: 'siswa.nama_lengkap' },
                    { data: 'kelas_nama', name: 'kelas.nama_kelas' },
                    { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false },
                    { data: 'jalur_badge', name: 'siswa_lulusan.jalur_masuk', orderable: false, searchable: false },
                    { data: 'result_badge', name: 'result_badge', orderable: false, searchable: false },
                    { data: 'checker_badge', name: 'checker_badge', orderable: false, searchable: false },
                    { data: 'nama_universitas', name: 'siswa_lulusan.nama_universitas' },
                    { data: 'jurusan_fakultas', name: 'siswa_lulusan.jurusan_fakultas' },
                    { data: 'program_studi', name: 'siswa_lulusan.program_studi' }
                ],
                order: [[1, 'asc']],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json'
                }
            });

            $('#btnApplyFilter').on('click', function () {
                reloadLulusanData();
            });

            $('#btnResetFilter').on('click', function () {
                $('#filterStatusPengisian').val('');
                $('#filterTrackerType').val('ALL');
                $('#filterJalurMasuk').val('');
                $('#filterPencarian').val('');
                lulusanTable.search('').ajax.reload();
                updateExportLinks();
                loadStats();
            });

            $('#filterPencarian').on('keyup', function (e) {
                if (e.key === 'Enter') {
                    reloadLulusanData();
                }
            });

            $('#filterJalurMasuk').on('change', function () {
                const selectedJalur = $(this).val();
                if (selectedJalur === 'SNBP' || selectedJalur === 'SPAN-PTKIN') {
                    $('#filterTrackerType').val(selectedJalur);
                }
                reloadLulusanData();
            });

            $('#filterTahunPelajaran, #filterStatusPengisian, #filterTrackerType').on('change', function () {
                reloadLulusanData();
            });

            $('#btnExportExcel').on('click', function (e) {
                e.preventDefault();
                const url = $(this).attr('href');
                if (!url || url === '#') {
                    updateExportLinks();
                }
                downloadLulusanExcel($(this).attr('href'));
            });

            $('#btnSendGraduationEmail').on('click', function () {
                updateGraduationEmailSummary();
                $('#graduationEmailModal').modal('show');
            });

            $('#btnSubmitGraduationEmail').on('click', function () {
                const filters = getFilters();
                const note = $('#graduationEmailNote').val();
                const $btn = $(this);

                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Mengirim...');

                $.ajax({
                    url: '{{ route('admin.lulusan.send-graduation-emails') }}',
                    method: 'POST',
                    data: {
                        ...filters,
                        catatan_admin: note,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        $('#graduationEmailModal').modal('hide');

                        const stats = response.stats || {};
                        let html = `
                            <div class="text-left">
                                <p class="mb-2">Proses kirim email selesai.</p>
                                <ul class="mb-0 pl-3">
                                    <li>Total target: ${stats.total ?? 0}</li>
                                    <li>Berhasil: ${stats.sent ?? 0}</li>
                                    <li>Gagal: ${stats.failed ?? 0}</li>
                                    <li>Dilewati: ${stats.skipped ?? 0}</li>
                                </ul>
                            </div>
                        `;

                        if (response.failures && response.failures.length) {
                            html += '<hr><div class="text-left"><strong>Contoh kegagalan:</strong><ul class="mb-0 pl-3">';
                            response.failures.forEach(item => {
                                html += `<li>${item.nama} (${item.email}): ${item.message}</li>`;
                            });
                            html += '</ul></div>';
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Email Diproses',
                            html: html
                        });
                    },
                    error: function (xhr) {
                        const message = xhr.responseJSON?.message || 'Gagal memproses pengiriman email pengumuman kelulusan.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: message
                        });
                    },
                    complete: function () {
                        $btn.prop('disabled', false).html('<i class="fas fa-paper-plane mr-1"></i> Kirim Sekarang');
                    }
                });
            });

            updateExportLinks();
            updateSelectedYearContext();
            loadStats();
        });
    </script>
@stop

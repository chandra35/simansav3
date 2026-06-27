@extends('adminlte::page')

@section('title', 'Matrikulasi PPDB')

@section('plugins.Select2', true)
@section('plugins.Sweetalert2', true)
@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugins', true)

@section('content_header')
    <div class="mat-page-head d-flex flex-column flex-md-row align-items-md-center justify-content-between">
        <div class="mat-page-title">
            <span class="mat-title-icon"><i class="fas fa-users-cog"></i></span>
            <div>
                <h1 class="mb-1">Matrikulasi PPDB</h1>
                <div class="text-muted">Staging calon siswa baru sebelum ditetapkan ke kelas reguler.</div>
            </div>
        </div>
        <ol class="breadcrumb mt-2 mt-md-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Matrikulasi PPDB</li>
        </ol>
    </div>
@stop

@section('content')
    <div class="mat-shell">
        <div class="mat-progress-overlay" id="matProgressOverlay" aria-live="polite">
            <div class="mat-progress-panel">
                <div class="mat-progress-icon">
                    <i class="fas fa-cloud-download-alt"></i>
                </div>
                <div class="mat-progress-copy">
                    <strong id="matProgressTitle">Memuat data PPDB</strong>
                    <span id="matProgressText">Menghubungi API PPDB...</span>
                </div>
                <div class="progress mat-progress-track">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" id="matProgressBar" style="width: 35%"></div>
                </div>
            </div>
        </div>

        <div class="mat-stats">
            <div class="mat-stat">
                <span>Peserta</span>
                <strong>{{ number_format($stats['total'] ?? 0) }}</strong>
            </div>
            <div class="mat-stat">
                <span>Kelompok</span>
                <strong>{{ number_format($stats['kelompok'] ?? 0) }}</strong>
            </div>
            <div class="mat-stat">
                <span>Dokumen</span>
                <strong>{{ number_format($stats['dokumen'] ?? 0) }}</strong>
            </div>
            <div class="mat-stat mat-stat-wide">
                <span>Periode</span>
                <strong>{{ $stats['periode']?->nama ?? 'Belum dipilih' }}</strong>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-4">
                <div class="card mat-card">
                    <div class="card-header border-0 mat-card-head">
                        <h3 class="card-title"><i class="fas fa-calendar-alt mr-2"></i>Periode & Kelompok</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="tahun_pelajaran_id">Tahun Pelajaran</label>
                            <select id="tahun_pelajaran_id" class="form-control">
                                @foreach($tahunPelajaran as $tp)
                                    <option value="{{ $tp->id }}" @selected($selectedTahunId === $tp->id)>
                                        {{ $tp->nama }} {{ $tp->is_active ? '(Aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="kelompok_id">Kelompok Matrikulasi</label>
                            <select id="kelompok_id" class="form-control">
                                <option value="">Pilih kelompok</option>
                                @foreach($kelompokMatrikulasi as $kelompok)
                                    <option value="{{ $kelompok->id }}">
                                        {{ $kelompok->nama }} - {{ $kelompok->label_kelas }}{{ $kelompok->kapasitas ? ' - '.$kelompok->pesertas_count.'/'.$kelompok->kapasitas : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mat-inline-create mat-class-builder">
                            <div class="mat-class-builder-head">
                                <div>
                                    <strong>Buat Kelas Matrikulasi</strong>
                                    <span>Kelas sementara, tidak masuk kelas reguler SIMANSA.</span>
                                </div>
                                <i class="fas fa-layer-group"></i>
                            </div>
                            <div class="form-row">
                                <div class="col-12 mb-2">
                                    <label for="new_kelompok_nama">Nama Kelas</label>
                                    <input id="new_kelompok_nama" class="form-control" placeholder="Madani, Al-Fath, Ibnu Sina">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label for="new_kelompok_jenis">Jenis</label>
                                    <select id="new_kelompok_jenis" class="form-control">
                                        <option value="reguler">Reguler</option>
                                        <option value="asrama">Asrama</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label for="new_kelompok_tingkat">Tingkat/Kode</label>
                                    <input id="new_kelompok_tingkat" class="form-control" placeholder="X1, X2, A1">
                                </div>
                                <div class="col-md-6">
                                    <label for="new_kelompok_kapasitas">Kapasitas</label>
                                    <input id="new_kelompok_kapasitas" type="number" min="1" class="form-control" placeholder="36">
                                </div>
                                <div class="col-md-6">
                                    <label for="new_kelompok_kode">Kode Internal</label>
                                    <input id="new_kelompok_kode" class="form-control" placeholder="Opsional">
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-block mt-2" id="btnCreateKelompok">
                                <i class="fas fa-plus mr-1"></i>Buat Kelas Matrikulasi
                            </button>
                        </div>

                        <div class="custom-control custom-switch mt-3">
                            <input type="checkbox" class="custom-control-input" id="include_documents" checked>
                            <label class="custom-control-label" for="include_documents">Salin dokumen PPDB ke staging SIMANSA</label>
                        </div>
                    </div>
                </div>

                <div class="card mat-card">
                    <div class="card-body">
                        <div class="mat-note">
                            <i class="fas fa-info-circle"></i>
                            <div>
                                Peserta matrikulasi tidak masuk menu Data Siswa reguler. Data akan menjadi siswa aktif hanya setelah proses penetapan kelas X final.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-8">
                <div class="card mat-card mat-sync-card">
                    <div class="mat-sync-hero">
                        <div class="mat-sync-title">
                            <span><i class="fas fa-cloud-download-alt"></i></span>
                            <div>
                                <h3>Sync Data PPDB</h3>
                                <p>Tarik data pendaftar ke staging matrikulasi sebelum ditetapkan sebagai siswa reguler.</p>
                            </div>
                        </div>
                        <span class="mat-sync-badge">Lulus + Registrasi Komite</span>
                    </div>
                    <div class="card-body">
                        <div class="mat-sync-workbench">
                            <div class="mat-quick-search">
                                <div>
                                    <label for="calon_siswa_ids">Quick Search Pendaftar</label>
                                    <small>Pilih manual beberapa pendaftar eligible, atau gunakan tabel browse untuk semua pendaftar.</small>
                                </div>
                                <select id="calon_siswa_ids" class="form-control" multiple></select>
                            </div>

                            <div class="mat-actions">
                                <button type="button" class="btn btn-outline-primary" id="btnLoadAll">
                                    <i class="fas fa-list mr-1"></i>Muat Semua PPDB
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="btnOpenAddModal">
                                    <i class="fas fa-table mr-1"></i>Browse Pendaftar
                                </button>
                                <button type="button" class="btn btn-secondary" id="btnPreview">
                                    <i class="fas fa-eye mr-1"></i>Preview
                                </button>
                                <button type="button" class="btn btn-primary" id="btnImport" disabled>
                                    <i class="fas fa-sync-alt mr-1"></i>Sync ke Matrikulasi
                                </button>
                            </div>
                        </div>

                        <div class="mat-preview-summary mt-3" id="previewSummary">
                            <i class="fas fa-info-circle mr-1"></i>
                            Belum ada data preview.
                        </div>

                        <div class="mat-preview-frame mt-3">
                            <div class="mat-preview-head">
                                <div>
                                    <strong>Preview Staging</strong>
                                    <span>Data yang akan disiapkan ke matrikulasi</span>
                                </div>
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mat-table" id="previewTable">
                                <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>NISN</th>
                                    <th>No.Tes</th>
                                    <th>Tahun</th>
                                    <th>Jurusan</th>
                                    <th class="text-center">Dok.</th>
                                    <th>Status</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Cari pendaftar PPDB, lalu klik Preview.</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        </div>

                        <div id="resultBox" class="mt-3" style="display:none;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mat-card mat-participant-card mt-3">
            <div class="mat-sync-hero">
                <div class="mat-sync-title">
                    <span><i class="fas fa-user-check"></i></span>
                    <div>
                        <h3>Peserta Matrikulasi</h3>
                        <p>Assign peserta yang sudah tersinkron ke kelas sementara dan siapkan akun login matrikulasi.</p>
                    </div>
                </div>
                <span class="mat-sync-badge">Staging Matrikulasi</span>
            </div>
            <div class="card-body">
                <div class="mat-participant-tools">
                    <div>
                        <label for="participant_kelompok_filter">Filter Kelompok</label>
                        <select id="participant_kelompok_filter" class="form-control">
                            <option value="">Semua kelompok</option>
                            @foreach($kelompokMatrikulasi as $kelompok)
                                <option value="{{ $kelompok->id }}">{{ $kelompok->nama }} - {{ $kelompok->label_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="assign_kelompok_id">Assign ke Kelompok</label>
                        <select id="assign_kelompok_id" class="form-control">
                            <option value="">Pilih kelompok tujuan</option>
                            @foreach($kelompokMatrikulasi as $kelompok)
                                <option value="{{ $kelompok->id }}">{{ $kelompok->nama }} - {{ $kelompok->label_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mat-participant-actions">
                        <button type="button" class="btn btn-outline-primary" id="btnAssignKelompok">
                            <i class="fas fa-random mr-1"></i>Assign Kelompok
                        </button>
                        <button type="button" class="btn btn-primary" id="btnGenerateAccounts">
                            <i class="fas fa-key mr-1"></i>Buat Akun
                        </button>
                    </div>
                </div>

                <div class="mat-search-foot mb-2">
                    <span id="participantSelectionInfo"><i class="fas fa-check-square mr-1"></i>Belum ada peserta dipilih</span>
                    <span><i class="fas fa-info-circle mr-1"></i>Akun matrikulasi tidak masuk tabel siswa reguler.</span>
                </div>

                <div class="table-responsive mat-browser-table-wrap">
                    <table class="table table-hover mb-0" id="matrikulasiPesertaTable">
                        <thead>
                        <tr>
                            <th class="text-center" style="width:42px;">
                                <input type="checkbox" id="checkAllParticipants" aria-label="Pilih semua peserta pada halaman">
                            </th>
                            <th>Peserta</th>
                            <th>No.Tes</th>
                            <th>Kelompok</th>
                            <th>Akun</th>
                            <th>Login</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addCandidateModal" tabindex="-1" role="dialog" aria-labelledby="addCandidateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content mat-modal">
                <div class="mat-modal-hero">
                    <div class="mat-modal-title">
                        <span><i class="fas fa-user-plus"></i></span>
                        <div>
                            <h5 class="modal-title" id="addCandidateModalLabel">Tambah Pendaftar PPDB</h5>
                            <small>Telusuri pendaftar tahun pelajaran terpilih, lalu masukkan ke preview matrikulasi.</small>
                        </div>
                    </div>
                    <button type="button" class="close mat-modal-close" data-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mat-browser-bar">
                        <div>
                            <i class="fas fa-calendar-check"></i>
                            <strong>Tahun Pelajaran</strong>
                            <span id="modalYearName">{{ optional($tahunPelajaran->firstWhere('id', $selectedTahunId))->nama ?? '-' }}</span>
                        </div>
                        <div>
                            <i class="fas fa-layer-group"></i>
                            <strong>Mode</strong>
                            <span>Semua pendaftar</span>
                        </div>
                    </div>

                    <div class="mat-search-panel">
                        <div class="mat-search-title">
                            <div>
                                <strong>Daftar Pendaftar</strong>
                                <span>Gunakan pencarian tabel untuk nama, NISN, atau No.Tes</span>
                            </div>
                            <i class="fas fa-table"></i>
                        </div>
                        <div class="table-responsive mat-browser-table-wrap">
                            <table class="table table-hover mb-0" id="candidateBrowserTable">
                                <thead>
                                <tr>
                                    <th class="text-center" style="width:42px;">
                                        <input type="checkbox" id="checkAllBrowserCandidates" aria-label="Pilih semua pada halaman">
                                    </th>
                                    <th>Pendaftar</th>
                                    <th>No.Tes</th>
                                    <th>Jurusan</th>
                                    <th class="text-center">Dok.</th>
                                    <th>Status</th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                        <div class="mat-search-foot">
                            <span id="browserSelectionInfo"><i class="fas fa-check-square mr-1"></i>Belum ada pendaftar dipilih</span>
                            <span><i class="fas fa-search mr-1"></i>Pencarian tabel membaca data PPDB langsung</span>
                        </div>
                    </div>

                    <div class="mat-browser-note mt-3">
                        <i class="fas fa-shield-alt"></i>
                        <span>Pendaftar yang belum registrasi komite akan diberi tanda dan wajib dikonfirmasi sebelum masuk preview/sync.</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" id="btnAddCandidatesPreview">
                        <i class="fas fa-plus mr-1"></i>Tambahkan ke Preview
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .mat-page-head {
            gap: 1rem;
        }
        .mat-page-title {
            display: flex;
            align-items: center;
            gap: .85rem;
        }
        .mat-page-title h1 {
            color: #111827;
            font-size: 1.55rem;
            font-weight: 800;
            letter-spacing: 0;
        }
        .mat-title-icon {
            width: 46px;
            height: 46px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            background: #0f766e;
            box-shadow: 0 10px 24px rgba(15, 118, 110, .22);
        }
        .mat-shell { padding-bottom: 1rem; }
        .mat-progress-overlay {
            position: fixed;
            inset: 0;
            z-index: 2050;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: rgba(15, 23, 42, .42);
            backdrop-filter: blur(2px);
        }
        .mat-progress-panel {
            width: min(420px, 100%);
            border: 1px solid #e6e8ef;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 18px 45px rgba(15, 23, 42, .18);
            padding: 1rem;
        }
        .mat-progress-icon {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            background: #0d6efd;
            margin-bottom: .75rem;
        }
        .mat-progress-copy strong,
        .mat-progress-copy span {
            display: block;
        }
        .mat-progress-copy strong {
            color: #1f2937;
            font-size: 1rem;
        }
        .mat-progress-copy span {
            color: #6c757d;
            margin-top: .15rem;
        }
        .mat-progress-track {
            height: .55rem;
            margin-top: .85rem;
            border-radius: 999px;
        }
        .mat-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: .75rem;
            margin-bottom: 1rem;
        }
        .mat-stat {
            background: #fff;
            border: 1px solid #e6e8ef;
            border-radius: 8px;
            padding: .9rem 1rem;
            min-height: 78px;
            box-shadow: 0 8px 22px rgba(15, 23, 42, .04);
        }
        .mat-stat span {
            display: block;
            color: #6c757d;
            font-size: .78rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .mat-stat strong {
            display: block;
            color: #1f2937;
            font-size: 1.35rem;
            line-height: 1.25;
            margin-top: .25rem;
        }
        .mat-card {
            border: 1px solid #e6e8ef;
            border-radius: 8px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, .05);
            overflow: hidden;
        }
        .mat-card-head {
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }
        .mat-card-head .card-title {
            color: #172033;
            font-weight: 800;
        }
        .mat-sync-card .card-body {
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }
        .mat-sync-hero {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 1.15rem;
            border-bottom: 1px solid #e6e8ef;
            background:
                linear-gradient(135deg, rgba(79, 70, 229, .08), rgba(15, 118, 110, .06)),
                #fff;
        }
        .mat-sync-title {
            display: flex;
            align-items: center;
            gap: .8rem;
        }
        .mat-sync-title > span {
            width: 42px;
            height: 42px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            background: #4f46e5;
            box-shadow: 0 10px 24px rgba(79, 70, 229, .22);
            flex: 0 0 auto;
        }
        .mat-sync-title h3 {
            color: #111827;
            font-size: 1rem;
            font-weight: 800;
            margin: 0;
        }
        .mat-sync-title p {
            color: #64748b;
            margin: .15rem 0 0;
            line-height: 1.35;
        }
        .mat-sync-badge {
            border: 1px solid #dbeafe;
            border-radius: 8px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: .75rem;
            font-weight: 800;
            padding: .35rem .55rem;
            white-space: nowrap;
        }
        .mat-sync-workbench {
            border: 1px solid #dbe4ee;
            border-radius: 8px;
            background: #fff;
            padding: .9rem;
            box-shadow: 0 8px 22px rgba(15, 23, 42, .04);
        }
        .mat-quick-search {
            display: grid;
            grid-template-columns: minmax(190px, .35fr) minmax(0, .65fr);
            gap: .85rem;
            align-items: start;
            margin-bottom: .85rem;
        }
        .mat-quick-search label,
        .mat-quick-search small {
            display: block;
        }
        .mat-quick-search label {
            color: #111827;
            font-weight: 800;
            margin-bottom: .12rem;
        }
        .mat-quick-search small {
            color: #64748b;
            line-height: 1.35;
        }
        .mat-quick-search .select2-container {
            width: 100% !important;
        }
        .mat-quick-search .select2-container--bootstrap4 .select2-selection--multiple,
        .mat-quick-search .select2-container--default .select2-selection--multiple {
            min-height: 48px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #f8fafc;
            padding: .34rem .55rem .34rem 2.35rem;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .85), 0 8px 20px rgba(15, 23, 42, .04);
            position: relative;
            transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
        }
        .mat-quick-search .select2-container--bootstrap4 .select2-selection--multiple::before,
        .mat-quick-search .select2-container--default .select2-selection--multiple::before {
            content: "\f002";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            position: absolute;
            left: .85rem;
            top: 50%;
            transform: translateY(-50%);
            color: #4f46e5;
            pointer-events: none;
        }
        .mat-quick-search .select2-container--bootstrap4.select2-container--focus .select2-selection,
        .mat-quick-search .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #4f46e5;
            background: #fff;
            box-shadow: 0 0 0 .2rem rgba(79, 70, 229, .13), 0 10px 24px rgba(15, 23, 42, .08);
        }
        .mat-quick-search .select2-container--bootstrap4 .select2-search--inline,
        .mat-quick-search .select2-container--default .select2-search--inline {
            width: 100%;
        }
        .mat-quick-search .select2-container--bootstrap4 .select2-search__field,
        .mat-quick-search .select2-container--default .select2-search__field {
            width: 100% !important;
            min-width: 220px;
            height: 32px;
            margin: .1rem 0;
            color: #0f172a;
            font-size: .92rem;
        }
        .mat-quick-search .select2-container--bootstrap4 .select2-search__field::placeholder,
        .mat-quick-search .select2-container--default .select2-search__field::placeholder {
            color: #64748b;
            opacity: 1;
        }
        .mat-quick-search .select2-container--bootstrap4 .select2-selection__choice,
        .mat-quick-search .select2-container--default .select2-selection__choice {
            border: 0;
            border-radius: 6px;
            background: #eef2ff;
            color: #3730a3;
            font-weight: 800;
            padding: .22rem .52rem;
            margin-top: .12rem;
        }
        .mat-quick-search .select2-container--bootstrap4 .select2-selection__choice__remove,
        .mat-quick-search .select2-container--default .select2-selection__choice__remove {
            color: #4338ca;
            margin-right: .35rem;
        }
        .mat-preview-frame {
            border: 1px solid #dbe4ee;
            border-radius: 8px;
            background: #fff;
            overflow: hidden;
            box-shadow: 0 8px 22px rgba(15, 23, 42, .04);
        }
        .mat-preview-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: .85rem .95rem;
            border-bottom: 1px solid #e6e8ef;
            background: #f8fafc;
        }
        .mat-preview-head strong,
        .mat-preview-head span {
            display: block;
        }
        .mat-preview-head strong {
            color: #111827;
            font-weight: 800;
        }
        .mat-preview-head span {
            color: #64748b;
            font-size: .82rem;
            margin-top: .05rem;
        }
        .mat-preview-head i {
            color: #0f766e;
        }
        .mat-participant-tools {
            display: grid;
            grid-template-columns: minmax(180px, .28fr) minmax(220px, .32fr) minmax(260px, .4fr);
            gap: .85rem;
            align-items: end;
            border: 1px solid #dbe4ee;
            border-radius: 8px;
            background: #fff;
            padding: .9rem;
            margin-bottom: .85rem;
            box-shadow: 0 8px 22px rgba(15, 23, 42, .04);
        }
        .mat-participant-tools label {
            color: #475569;
            font-size: .74rem;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: .25rem;
        }
        .mat-participant-tools .form-control {
            border-color: #d8e0ea;
            border-radius: 8px;
        }
        .mat-participant-actions {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            justify-content: flex-end;
        }
        .mat-participant-actions .btn {
            min-height: 38px;
        }
        .mat-inline-create {
            border: 1px solid #e9edf5;
            border-radius: 8px;
            padding: .85rem;
            background: #f8fafc;
        }
        .mat-class-builder {
            background:
                linear-gradient(135deg, rgba(15, 118, 110, .07), rgba(79, 70, 229, .05)),
                #fff;
            border-color: #dbe4ee;
            box-shadow: 0 8px 22px rgba(15, 23, 42, .04);
        }
        .mat-class-builder-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: .75rem;
            margin-bottom: .75rem;
        }
        .mat-class-builder-head strong,
        .mat-class-builder-head span {
            display: block;
        }
        .mat-class-builder-head strong {
            color: #111827;
            font-weight: 800;
        }
        .mat-class-builder-head span {
            color: #64748b;
            font-size: .78rem;
            margin-top: .1rem;
        }
        .mat-class-builder-head i {
            color: #0f766e;
            margin-top: .1rem;
        }
        .mat-class-builder label {
            color: #475569;
            font-size: .74rem;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: .25rem;
        }
        .mat-class-builder .form-control {
            border-color: #d8e0ea;
            border-radius: 8px;
        }
        .mat-class-builder .form-control:focus {
            border-color: #0f766e;
            box-shadow: 0 0 0 .18rem rgba(15, 118, 110, .12);
        }
        .mat-note {
            display: flex;
            gap: .75rem;
            color: #495057;
            line-height: 1.45;
        }
        .mat-note i { color: #0d6efd; margin-top: .2rem; }
        .mat-actions {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
        }
        .mat-actions .btn {
            min-height: 38px;
        }
        .mat-preview-summary {
            display: flex;
            align-items: center;
            gap: .25rem;
            border: 1px solid #e6e8ef;
            border-radius: 8px;
            background: #f8fafc;
            color: #495057;
            padding: .65rem .75rem;
            font-weight: 600;
        }
        .mat-preview-summary.is-ready {
            border-color: #b7dfc2;
            background: #edf8f0;
            color: #1b5e20;
        }
        .mat-preview-summary.is-warning {
            border-color: #ffe08a;
            background: #fff8df;
            color: #73510d;
        }
        .mat-result-card {
            border: 1px solid #c7d2fe;
            border-radius: 8px;
            background: #fff;
            overflow: hidden;
            box-shadow: 0 14px 34px rgba(79, 70, 229, .1);
        }
        .mat-result-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: .85rem .95rem;
            background:
                linear-gradient(135deg, rgba(79, 70, 229, .1), rgba(15, 118, 110, .06)),
                #f8fafc;
            border-bottom: 1px solid #e0e7ff;
        }
        .mat-result-title {
            display: flex;
            align-items: center;
            gap: .7rem;
            color: #111827;
        }
        .mat-result-title span {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            background: #4f46e5;
            box-shadow: 0 10px 22px rgba(79, 70, 229, .24);
            flex: 0 0 auto;
        }
        .mat-result-title strong,
        .mat-result-title small {
            display: block;
        }
        .mat-result-title strong {
            font-weight: 800;
        }
        .mat-result-title small {
            color: #64748b;
            margin-top: .08rem;
        }
        .mat-result-stats {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: .35rem;
        }
        .mat-result-stat {
            display: inline-flex;
            align-items: center;
            border-radius: 6px;
            padding: .25rem .52rem;
            font-size: .74rem;
            font-weight: 800;
            background: #eef2ff;
            color: #3730a3;
            white-space: nowrap;
        }
        .mat-result-stat.is-success {
            background: #dcfce7;
            color: #166534;
        }
        .mat-result-stat.is-danger {
            background: #fee2e2;
            color: #991b1b;
        }
        .mat-result-table {
            margin-bottom: 0;
        }
        .mat-result-table thead th {
            border-top: 0;
            border-bottom: 1px solid #dbe4ee;
            background: #f8fafc;
            color: #475569;
            font-size: .72rem;
            font-weight: 800;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .mat-result-table tbody td {
            border-top: 0;
            border-bottom: 1px solid #edf2f7;
            vertical-align: middle;
        }
        .mat-result-table tbody tr:last-child td {
            border-bottom: 0;
        }
        .mat-result-row-success {
            background: #f0fdf4;
        }
        .mat-result-row-danger {
            background: #fff1f2;
        }
        .mat-result-message {
            color: #334155;
            font-weight: 600;
        }
        .mat-table th {
            white-space: nowrap;
            border-top: 0;
            color: #475569;
            font-size: .76rem;
            text-transform: uppercase;
        }
        .mat-table td {
            vertical-align: middle;
        }
        .mat-modal {
            border: 0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 22px 64px rgba(15, 23, 42, .22);
        }
        .mat-modal-hero {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.25rem 1.35rem;
            border-bottom: 1px solid #dbe4ee;
            background:
                linear-gradient(135deg, rgba(15, 118, 110, .1), rgba(37, 99, 235, .06)),
                #f8fafc;
        }
        .mat-modal-title {
            display: flex;
            align-items: center;
            gap: .85rem;
        }
        .mat-modal-title > span {
            width: 46px;
            height: 46px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            background: #0f766e;
            box-shadow: 0 12px 26px rgba(15, 118, 110, .24);
            flex: 0 0 auto;
        }
        .mat-modal-title h5 {
            color: #111827;
            font-weight: 800;
            margin: 0;
        }
        .mat-modal-title small {
            display: block;
            color: #64748b;
            margin-top: .18rem;
        }
        .mat-modal-close {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: rgba(255, 255, 255, .72) !important;
            border: 1px solid #dbe4ee !important;
            color: #475569;
            opacity: 1;
            text-shadow: none;
        }
        .mat-modal .modal-body {
            padding: 1.1rem 1.35rem;
            background: #fff;
        }
        .mat-modal .modal-footer {
            background: #f8fafc;
            border-top-color: #e6e8ef;
        }
        .mat-browser-bar {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .75rem;
            margin-bottom: 1rem;
        }
        .mat-browser-bar > div {
            border: 1px solid #e6e8ef;
            border-radius: 8px;
            background: #fff;
            padding: .75rem .85rem;
            position: relative;
            overflow: hidden;
        }
        .mat-browser-bar > div i {
            position: absolute;
            right: .85rem;
            top: .8rem;
            color: #0f766e;
            opacity: .82;
        }
        .mat-browser-bar strong,
        .mat-browser-bar span {
            display: block;
        }
        .mat-browser-bar strong {
            color: #64748b;
            font-size: .73rem;
            text-transform: uppercase;
        }
        .mat-browser-bar span {
            color: #111827;
            font-weight: 800;
            margin-top: .12rem;
        }
        .mat-search-panel {
            border: 1px solid #dbe4ee;
            border-radius: 8px;
            background: #f8fafc;
            padding: .9rem;
        }
        .mat-search-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: .7rem;
        }
        .mat-search-title strong,
        .mat-search-title span {
            display: block;
        }
        .mat-search-title strong {
            color: #111827;
            font-weight: 800;
        }
        .mat-search-title span {
            color: #64748b;
            font-size: .82rem;
            margin-top: .05rem;
        }
        .mat-search-title > i {
            color: #0f766e;
        }
        .mat-search-foot {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: .5rem;
            color: #64748b;
            font-size: .78rem;
            margin-top: .65rem;
        }
        .mat-browser-table-wrap {
            border: 1px solid #dbe4ee;
            border-radius: 8px;
            background: #fff;
            overflow: hidden;
        }
        #candidateBrowserTable {
            width: 100% !important;
            border-collapse: separate !important;
            border-spacing: 0;
        }
        #matrikulasiPesertaTable {
            width: 100% !important;
        }
        #matrikulasiPesertaTable thead th,
        #matrikulasiPesertaTable tbody td {
            border-top: 0;
            vertical-align: middle;
        }
        #matrikulasiPesertaTable thead th {
            border-bottom: 1px solid #dbe4ee;
            background: #f8fafc;
            color: #475569;
            font-size: .72rem;
            font-weight: 800;
            text-transform: uppercase;
            white-space: nowrap;
            padding: .72rem .75rem;
        }
        #matrikulasiPesertaTable tbody td {
            border-bottom: 1px solid #edf2f7;
            padding: .78rem .75rem;
        }
        #matrikulasiPesertaTable tbody tr.is-selected {
            background: #ecfeff;
        }
        .account-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 6px;
            padding: .18rem .46rem;
            font-size: .7rem;
            font-weight: 800;
            white-space: nowrap;
            background: #fee2e2;
            color: #991b1b;
        }
        .account-pill.is-ready {
            background: #dcfce7;
            color: #166534;
        }
        .login-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 6px;
            padding: .18rem .46rem;
            font-size: .7rem;
            font-weight: 800;
            white-space: nowrap;
            background: #f1f5f9;
            color: #475569;
        }
        .login-pill.is-online {
            background: #dbeafe;
            color: #1d4ed8;
        }
        #candidateBrowserTable thead th {
            border-top: 0;
            border-bottom: 1px solid #dbe4ee;
            background: #f8fafc;
            color: #475569;
            font-size: .72rem;
            font-weight: 800;
            text-transform: uppercase;
            white-space: nowrap;
            padding: .72rem .75rem;
        }
        #candidateBrowserTable tbody td {
            border-top: 0;
            border-bottom: 1px solid #edf2f7;
            vertical-align: middle;
            padding: .78rem .75rem;
        }
        #candidateBrowserTable tbody tr:hover {
            background: #f8fafc;
        }
        #candidateBrowserTable tbody tr.is-selected {
            background: #ecfeff;
        }
        #candidateBrowserTable .browser-check {
            width: 18px;
            height: 18px;
            accent-color: #0f766e;
        }
        .browser-person {
            display: flex;
            align-items: center;
            gap: .65rem;
            min-width: 210px;
        }
        .browser-avatar {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #e0f2fe;
            color: #075985;
            font-weight: 900;
            flex: 0 0 auto;
        }
        .browser-person strong,
        .browser-person span {
            display: block;
        }
        .browser-person strong {
            color: #111827;
            font-weight: 800;
            line-height: 1.2;
        }
        .browser-person span {
            color: #64748b;
            font-size: .78rem;
            margin-top: .12rem;
        }
        .browser-no-tes {
            color: #1f2937;
            font-weight: 800;
            white-space: nowrap;
        }
        .browser-tags {
            display: flex;
            flex-wrap: wrap;
            gap: .3rem;
        }
        .browser-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 6px;
            padding: .18rem .46rem;
            font-size: .7rem;
            font-weight: 800;
            line-height: 1.2;
            white-space: nowrap;
            background: #f1f5f9;
            color: #475569;
        }
        .browser-pill.is-paid { background: #dcfce7; color: #166534; }
        .browser-pill.is-unpaid { background: #fee2e2; color: #991b1b; }
        .browser-pill.is-lulus { background: #eef2ff; color: #3730a3; }
        .browser-pill.is-not-lulus { background: #fff7ed; color: #9a3412; }
        .browser-doc {
            display: inline-flex;
            min-width: 30px;
            height: 28px;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            background: #f8fafc;
            color: #334155;
            font-weight: 800;
        }
        #addCandidateModal .dataTables_wrapper {
            padding: .75rem;
        }
        #addCandidateModal .dataTables_filter,
        #addCandidateModal .dataTables_length {
            margin-bottom: .75rem;
        }
        #addCandidateModal .dataTables_filter {
            float: none;
            text-align: left;
        }
        #addCandidateModal .dataTables_length {
            float: none;
        }
        #addCandidateModal .dataTables_filter label,
        #addCandidateModal .dataTables_length label {
            color: #64748b;
            font-weight: 700;
        }
        #addCandidateModal .dataTables_filter label {
            display: block;
            position: relative;
            width: min(460px, 100%);
        }
        #addCandidateModal .dataTables_filter label::before {
            content: "\f002";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            position: absolute;
            left: .85rem;
            bottom: .72rem;
            color: #0f766e;
            z-index: 1;
        }
        #addCandidateModal .dataTables_filter input,
        #addCandidateModal .dataTables_length select {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            box-shadow: none;
        }
        #addCandidateModal .dataTables_filter input {
            width: 100%;
            height: 44px;
            margin: .35rem 0 0;
            padding-left: 2.25rem;
            background: #fff;
        }
        #addCandidateModal .dataTables_length select {
            height: 38px;
            margin: 0 .25rem;
        }
        #addCandidateModal .dataTables_filter input:focus {
            border-color: #0f766e;
            box-shadow: 0 0 0 .18rem rgba(15, 118, 110, .12);
        }
        #addCandidateModal .dataTables_paginate {
            padding-top: .85rem;
        }
        #addCandidateModal .pagination {
            gap: .35rem;
            flex-wrap: wrap;
        }
        #addCandidateModal .page-link {
            min-width: 38px;
            height: 38px;
            border: 1px solid #dbe4ee;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #334155;
            background: #fff;
            font-weight: 800;
            box-shadow: 0 6px 14px rgba(15, 23, 42, .05);
        }
        #addCandidateModal .page-link:hover {
            border-color: #99f6e4;
            background: #ecfeff;
            color: #0f766e;
        }
        #addCandidateModal .page-item.active .page-link {
            background: linear-gradient(135deg, #0f766e, #4f46e5);
            border-color: #0f766e;
            color: #fff;
            box-shadow: 0 10px 20px rgba(15, 118, 110, .22);
        }
        #addCandidateModal .page-item.disabled .page-link {
            background: #f8fafc;
            color: #94a3b8;
            box-shadow: none;
        }
        .mat-browser-note {
            display: flex;
            align-items: center;
            gap: .55rem;
            border: 1px solid #fde68a;
            border-radius: 8px;
            background: #fffbeb;
            color: #7c4a03;
            padding: .7rem .8rem;
        }
        #addCandidateModal .select2-container {
            width: 100% !important;
        }
        #addCandidateModal .select2-container--bootstrap4 .select2-selection--multiple,
        #addCandidateModal .select2-container--default .select2-selection--multiple {
            min-height: 50px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #fff;
            padding: .28rem .45rem;
            box-shadow: 0 8px 18px rgba(15, 23, 42, .04);
        }
        #addCandidateModal .select2-container--bootstrap4.select2-container--focus .select2-selection,
        #addCandidateModal .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #0f766e;
            box-shadow: 0 0 0 .18rem rgba(15, 118, 110, .12);
        }
        #addCandidateModal .select2-search--inline {
            width: 100%;
        }
        #addCandidateModal .select2-search__field {
            width: 100% !important;
            min-width: 260px;
            height: 34px;
            margin-top: .15rem;
            font-size: .92rem;
        }
        #addCandidateModal .select2-selection__choice {
            border: 0;
            border-radius: 6px;
            background: #e0f2fe;
            color: #075985;
            font-weight: 700;
            padding: .22rem .5rem;
        }
        #addCandidateModal .select2-dropdown {
            border-color: #dbe4ee;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 18px 42px rgba(15, 23, 42, .16);
        }
        #addCandidateModal .select2-results__options {
            max-height: 390px;
        }
        #addCandidateModal .select2-results__option {
            padding: .72rem .85rem;
            border-bottom: 1px solid #edf2f7;
        }
        #addCandidateModal .select2-results__option:last-child {
            border-bottom: 0;
        }
        #addCandidateModal .select2-results__option--highlighted[aria-selected] {
            background: #ecfeff;
            color: #111827;
        }
        .candidate-option {
            display: grid;
            grid-template-columns: minmax(0, 1.25fr) minmax(190px, .75fr);
            gap: .75rem;
            align-items: center;
            padding: .05rem 0;
        }
        .candidate-option strong,
        .candidate-option small {
            display: block;
        }
        .candidate-option strong {
            color: #1f2937;
            font-size: .91rem;
        }
        .candidate-option small {
            color: #64748b;
            margin-top: .16rem;
        }
        .candidate-meta {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: .35rem;
        }
        .candidate-pill {
            border-radius: 6px;
            padding: .13rem .42rem;
            font-size: .7rem;
            font-weight: 800;
            white-space: nowrap;
            background: #eef2ff;
            color: #3730a3;
        }
        .candidate-pill.is-paid { background: #dcfce7; color: #166534; }
        .candidate-pill.is-unpaid { background: #fee2e2; color: #991b1b; }
        .candidate-pill.is-muted { background: #f1f5f9; color: #475569; }
        .mat-quick-search-dropdown {
            border: 1px solid #c7d2fe !important;
            border-radius: 8px !important;
            overflow: hidden;
            box-shadow: 0 18px 42px rgba(15, 23, 42, .18);
        }
        .mat-quick-search-dropdown .select2-results__option {
            padding: .75rem .85rem;
            border-bottom: 1px solid #edf2f7;
            background: #fff;
            color: #111827;
        }
        .mat-quick-search-dropdown .select2-results__option:last-child {
            border-bottom: 0;
        }
        .mat-quick-search-dropdown .select2-results__option--highlighted[aria-selected] {
            background: #eef2ff !important;
            color: #111827 !important;
        }
        .mat-quick-search-dropdown .select2-results__option[aria-selected=true] {
            background: #ecfeff;
            color: #111827;
        }
        .mat-quick-search-dropdown .select2-results__message {
            color: #64748b;
            padding: .85rem;
        }
        .select2-container--bootstrap4 .select2-results__option--highlighted[aria-selected],
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background: #eef2ff;
            color: #111827;
        }
        .select2-container--bootstrap4 .select2-results__option--highlighted[aria-selected] .candidate-option strong,
        .select2-container--default .select2-results__option--highlighted[aria-selected] .candidate-option strong {
            color: #111827;
        }
        .select2-container--bootstrap4 .select2-results__option--highlighted[aria-selected] .candidate-option small,
        .select2-container--default .select2-results__option--highlighted[aria-selected] .candidate-option small {
            color: #475569;
        }
        .payment-chip {
            display: inline-flex;
            align-items: center;
            padding: .18rem .5rem;
            border-radius: 6px;
            font-size: .75rem;
            font-weight: 800;
            white-space: nowrap;
            background: #fee2e2;
            color: #991b1b;
        }
        .payment-chip.is-paid {
            background: #dcfce7;
            color: #166534;
        }
        .select2-container--default .select2-selection--multiple {
            min-height: 42px;
            border-color: #ced4da;
        }
        .status-chip {
            display: inline-flex;
            align-items: center;
            padding: .18rem .5rem;
            border-radius: 6px;
            font-size: .75rem;
            font-weight: 700;
            white-space: nowrap;
        }
        .status-baru { background: #e8f5e9; color: #1b5e20; }
        .status-matrikulasi { background: #e0f2fe; color: #075985; }
        .status-dipromosikan { background: #dcfce7; color: #166534; }
        .status-dibatalkan { background: #fee2e2; color: #991b1b; }
        .status-sudah_matrikulasi { background: #e3f2fd; color: #0d47a1; }
        .status-sudah_jadi_siswa { background: #fff3cd; color: #7a4d00; }
        @media (max-width: 991.98px) {
            .mat-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 575.98px) {
            .mat-stats { grid-template-columns: 1fr; }
            .mat-actions .btn { width: 100%; }
            .mat-stat strong { font-size: 1.1rem; }
            .mat-sync-hero,
            .mat-sync-title,
            .mat-quick-search,
            .mat-participant-tools { display: block; }
            .mat-sync-title > span { margin-bottom: .65rem; }
            .mat-sync-badge { display: inline-flex; margin-top: .75rem; }
            .mat-participant-tools > div { margin-bottom: .7rem; }
            .mat-participant-actions .btn { width: 100%; }
            .mat-modal-hero,
            .mat-modal .modal-body { padding: 1rem; }
            .mat-modal-title { align-items: flex-start; }
            .candidate-option,
            .mat-browser-bar { grid-template-columns: 1fr; }
            .candidate-meta { justify-content: flex-start; }
            .mat-search-foot { display: block; }
            .mat-search-foot span { display: block; margin-top: .25rem; }
        }
    </style>
@stop

@section('js')
    <script>
        const routes = {
            candidates: @json(route('admin.matrikulasi-ppdb.candidates')),
            browserCandidates: @json(route('admin.matrikulasi-ppdb.browser-candidates')),
            peserta: @json(route('admin.matrikulasi-ppdb.peserta')),
            assignKelompok: @json(route('admin.matrikulasi-ppdb.assign-kelompok')),
            generateAccounts: @json(route('admin.matrikulasi-ppdb.generate-accounts')),
            preview: @json(route('admin.matrikulasi-ppdb.preview')),
            previewAll: @json(route('admin.matrikulasi-ppdb.preview-all')),
            import: @json(route('admin.matrikulasi-ppdb.import')),
            kelompokStore: @json(route('admin.matrikulasi-ppdb.kelompok.store')),
        };

        let previewIds = [];
        let currentPreviewRows = [];
        let browserTable = null;
        let participantTable = null;
        let selectedBrowserRows = new Map();
        let selectedParticipantRows = new Map();
        let suppressSelectionReset = false;

        function selectedIds() {
            return $('#calon_siswa_ids').val() || [];
        }

        function setProgressOverlay(show, text = 'Menghubungi API PPDB...', percent = 35) {
            $('#matProgressText').text(text);
            $('#matProgressBar').css('width', `${percent}%`);
            $('#matProgressOverlay').css('display', show ? 'flex' : 'none');
        }

        function setButtonLoading($button, loading, loadingText, normalHtml) {
            $button.prop('disabled', loading).html(loading ? `<i class="fas fa-spinner fa-spin mr-1"></i>${loadingText}` : normalHtml);
        }

        function escapeHtml(value) {
            return $('<div>').text(value ?? '').html();
        }

        function paymentChip(row) {
            return row.has_registrasi_komite
                ? '<span class="payment-chip is-paid">Sudah bayar</span>'
                : '<span class="payment-chip">Belum bayar</span>';
        }

        function updatePreviewSummary(rows, mode = 'idle') {
            const $summary = $('#previewSummary');
            $summary.removeClass('is-ready is-warning');

            if (!rows.length) {
                $summary.html('<i class="fas fa-info-circle mr-1"></i>Belum ada data preview.');
                return;
            }

            const locked = rows.filter(row => row.import_status === 'sudah_jadi_siswa').length;
            const documents = rows.reduce((total, row) => total + Number(row.documents_count || 0), 0);
            const icon = locked ? 'fa-exclamation-triangle' : 'fa-check-circle';
            const className = locked ? 'is-warning' : 'is-ready';
            const modeText = mode === 'all' ? 'dimuat dari PPDB' : 'siap dipreview';
            const lockedText = locked ? `, ${locked} sudah menjadi siswa reguler` : '';

            $summary.addClass(className).html(`<i class="fas ${icon} mr-1"></i>${rows.length} pendaftar ${modeText}, ${documents} dokumen terdeteksi${lockedText}.`);
        }

        function statusChip(status) {
            const label = {
                baru: 'Baru',
                matrikulasi: 'Matrikulasi',
                dipromosikan: 'Dipromosikan',
                dibatalkan: 'Dibatalkan',
                sudah_matrikulasi: 'Sudah Matrikulasi',
                sudah_jadi_siswa: 'Sudah Jadi Siswa',
            }[status] || status;

            return `<span class="status-chip status-${status}">${label}</span>`;
        }

        function initials(name) {
            const parts = String(name || '-').trim().split(/\s+/).filter(Boolean);
            return (parts[0]?.charAt(0) || '-') + (parts[1]?.charAt(0) || '');
        }

        function browserStatus(row) {
            const paidClass = row.has_registrasi_komite ? 'is-paid' : 'is-unpaid';
            const paidText = row.has_registrasi_komite ? 'Sudah bayar' : 'Belum bayar';
            const lulusClass = row.is_lulus ? 'is-lulus' : 'is-not-lulus';
            const lulusText = row.is_lulus ? 'Lulus' : 'Belum lulus';
            return `
                <div class="browser-tags">
                    <span class="browser-pill ${paidClass}">${paidText}</span>
                    <span class="browser-pill ${lulusClass}">${lulusText}</span>
                    <span class="browser-pill">${escapeHtml(row.import_status || '-')}</span>
                </div>
            `;
        }

        function updateBrowserSelectionInfo() {
            const count = selectedBrowserRows.size;
            $('#browserSelectionInfo').html(`<i class="fas fa-check-square mr-1"></i>${count ? `${count} pendaftar dipilih` : 'Belum ada pendaftar dipilih'}`);
            $('#checkAllBrowserCandidates').prop('checked', false);
        }

        function updateParticipantSelectionInfo() {
            const count = selectedParticipantRows.size;
            $('#participantSelectionInfo').html(`<i class="fas fa-check-square mr-1"></i>${count ? `${count} peserta dipilih` : 'Belum ada peserta dipilih'}`);
            $('#checkAllParticipants').prop('checked', false);
        }

        function syncBrowserChecks() {
            $('#candidateBrowserTable .browser-check').each(function () {
                const id = $(this).data('id');
                const checked = selectedBrowserRows.has(id);
                $(this).prop('checked', checked).closest('tr').toggleClass('is-selected', checked);
            });
        }

        function syncParticipantChecks() {
            $('#matrikulasiPesertaTable .participant-check').each(function () {
                const id = $(this).data('id');
                const checked = selectedParticipantRows.has(id);
                $(this).prop('checked', checked).closest('tr').toggleClass('is-selected', checked);
            });
        }

        function accountStatus(row) {
            if (!row.akun) {
                return '<span class="account-pill">Belum ada akun</span>';
            }

            return `<span class="account-pill is-ready">${escapeHtml(row.username || 'Akun aktif')}</span>`;
        }

        function loginStatus(row) {
            if (row.is_online) {
                return '<span class="login-pill is-online">Online</span>';
            }

            return `<span class="login-pill">${row.last_login_at ? escapeHtml(row.last_login_at) : 'Belum login'}</span>`;
        }

        function initBrowserTable() {
            if (browserTable) {
                return;
            }

            browserTable = $('#candidateBrowserTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                pageLength: 10,
                lengthMenu: [[10, 25, 50], [10, 25, 50]],
                order: [],
                ajax: {
                    url: routes.browserCandidates,
                    data: function (data) {
                        data.tahun_pelajaran_id = $('#tahun_pelajaran_id').val();
                    },
                    error: function (xhr) {
                        Swal.fire('Koneksi PPDB gagal', xhr.responseJSON?.error || 'Tidak bisa mengambil data browser pendaftar.', 'error');
                    }
                },
                columns: [
                    {
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function (id) {
                            return `<input type="checkbox" class="browser-check" data-id="${escapeHtml(id)}" aria-label="Pilih pendaftar">`;
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        render: function (row) {
                            return `
                                <div class="browser-person">
                                    <span class="browser-avatar">${escapeHtml(initials(row.nama_lengkap))}</span>
                                    <div>
                                        <strong>${escapeHtml(row.nama_lengkap || '-')}</strong>
                                        <span>NISN ${escapeHtml(row.nisn || '-')} | NIK ${escapeHtml(row.nik || '-')}</span>
                                    </div>
                                </div>
                            `;
                        }
                    },
                    {
                        data: 'nomor_tes',
                        orderable: false,
                        render: data => `<span class="browser-no-tes">${escapeHtml(data || '-')}</span>`
                    },
                    {
                        data: 'jurusan',
                        orderable: false,
                        render: data => escapeHtml(data || '-')
                    },
                    {
                        data: 'documents_count',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: data => `<span class="browser-doc">${Number(data || 0)}</span>`
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: browserStatus
                    }
                ],
                drawCallback: function () {
                    syncBrowserChecks();
                },
                language: {
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_',
                    processing: 'Memuat data PPDB...',
                    emptyTable: 'Tidak ada pendaftar pada tahun ini.',
                    zeroRecords: 'Tidak ada pendaftar yang cocok.',
                    info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ pendaftar',
                    infoEmpty: 'Tidak ada data',
                    infoFiltered: '(difilter dari _MAX_ total)',
                    paginate: {
                        first: 'Awal',
                        last: 'Akhir',
                        next: 'Berikutnya',
                        previous: 'Sebelumnya'
                    }
                }
            });
        }

        function initParticipantTable() {
            if (participantTable) {
                return;
            }

            participantTable = $('#matrikulasiPesertaTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                pageLength: 10,
                lengthMenu: [[10, 25, 50], [10, 25, 50]],
                order: [],
                ajax: {
                    url: routes.peserta,
                    data: function (data) {
                        data.tahun_pelajaran_id = $('#tahun_pelajaran_id').val();
                        data.kelompok_id = $('#participant_kelompok_filter').val();
                    },
                    error: function (xhr) {
                        Swal.fire('Gagal memuat peserta', xhr.responseJSON?.message || 'Tidak bisa mengambil data peserta matrikulasi.', 'error');
                    }
                },
                columns: [
                    {
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function (id) {
                            return `<input type="checkbox" class="browser-check participant-check" data-id="${escapeHtml(id)}" aria-label="Pilih peserta">`;
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        render: function (row) {
                            return `
                                <div class="browser-person">
                                    <span class="browser-avatar">${escapeHtml(initials(row.nama_lengkap))}</span>
                                    <div>
                                        <strong>${escapeHtml(row.nama_lengkap || '-')}</strong>
                                        <span>NISN ${escapeHtml(row.nisn || '-')} | ${escapeHtml(row.jenis_kelamin || '-')}</span>
                                    </div>
                                </div>
                            `;
                        }
                    },
                    {
                        data: 'nomor_tes',
                        orderable: false,
                        render: data => `<span class="browser-no-tes">${escapeHtml(data || '-')}</span>`
                    },
                    {
                        data: null,
                        orderable: false,
                        render: row => row.kelompok ? `${escapeHtml(row.kelompok)}<br><small class="text-muted">${escapeHtml(row.label_kelas || '-')}</small>` : '<span class="text-muted">Belum diassign</span>'
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: accountStatus
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: loginStatus
                    },
                    {
                        data: 'status',
                        orderable: false,
                        render: data => statusChip(data)
                    }
                ],
                drawCallback: syncParticipantChecks,
                language: {
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_',
                    processing: 'Memuat peserta...',
                    emptyTable: 'Belum ada peserta matrikulasi.',
                    zeroRecords: 'Tidak ada peserta yang cocok.',
                    info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ peserta',
                    infoEmpty: 'Tidak ada data',
                    infoFiltered: '(difilter dari _MAX_ total)',
                    paginate: {
                        first: 'Awal',
                        last: 'Akhir',
                        next: 'Berikutnya',
                        previous: 'Sebelumnya'
                    }
                }
            });
        }

        function renderPreview(rows) {
            const $tbody = $('#previewTable tbody');
            currentPreviewRows = rows;
            previewIds = rows.map(row => row.id);

            if (!rows.length) {
                $tbody.html('<tr><td colspan="7" class="text-center text-muted py-4">Tidak ada data preview.</td></tr>');
                $('#btnImport').prop('disabled', true);
                updatePreviewSummary([]);
                return;
            }

            const html = rows.map(row => `
                <tr>
                    <td><strong>${row.nama_lengkap || '-'}</strong><br><small class="text-muted">${row.nik || '-'}</small></td>
                    <td>${row.nisn || '-'}</td>
                    <td><strong>${row.nomor_tes || '-'}</strong></td>
                    <td>${row.tahun_ppdb || '-'}</td>
                    <td>${row.jurusan_final || row.jurusan_awal || '-'}<br>${paymentChip(row)}</td>
                    <td class="text-center">${row.documents_count || 0}</td>
                    <td>${statusChip(row.import_status)}</td>
                </tr>
            `).join('');

            $tbody.html(html);
            $('#btnImport').prop('disabled', rows.some(row => row.import_status === 'sudah_jadi_siswa'));
            updatePreviewSummary(rows);
        }

        function candidateOption(item) {
            if (!item.id) return item.text;

            const paidClass = item.has_registrasi_komite ? 'is-paid' : 'is-unpaid';
            const paidText = item.has_registrasi_komite ? 'Sudah bayar' : 'Belum bayar';
            const lulusText = item.is_lulus ? 'Lulus' : 'Belum lulus';

            return $(`
                <div class="candidate-option">
                    <div>
                        <strong>${item.nama_lengkap || item.text}</strong>
                        <small>${item.nisn || '-'} | No.Tes: ${item.nomor_tes || '-'}</small>
                    </div>
                    <div class="candidate-meta">
                        <span class="candidate-pill ${paidClass}">${paidText}</span>
                        <span class="candidate-pill">${lulusText}</span>
                        <span class="candidate-pill is-muted">${item.jurusan || '-'}</span>
                    </div>
                </div>
            `);
        }

        function normalizeSearchValue(value) {
            return String(value || '').trim().toLowerCase();
        }

        function splitSmartSearchTerms(text) {
            return String(text || '')
                .split(/\r?\n|;|\t/)
                .map(term => term.trim())
                .filter(Boolean)
                .filter((term, index, terms) => terms.findIndex(item => item.toLowerCase() === term.toLowerCase()) === index)
                .slice(0, 50);
        }

        function chooseBestCandidate(term, results) {
            const normalized = normalizeSearchValue(term);
            return (results || []).find(item => {
                return [item.nomor_tes, item.nisn, item.nama_lengkap, item.text]
                    .some(value => normalizeSearchValue(value) === normalized);
            }) || (results || [])[0] || null;
        }

        function addCandidateSelections(candidates) {
            const $select = $('#calon_siswa_ids');
            const existing = new Set($select.val() || []);

            candidates.forEach(item => {
                const id = String(item.id);
                const text = item.nama_lengkap || item.text || item.nomor_tes || item.nisn || id;

                if (!$select.find(`option[value="${id.replace(/"/g, '\\"')}"]`).length) {
                    $select.append(new Option(text, id, false, false));
                }

                existing.add(id);
            });

            $select.val(Array.from(existing)).trigger('change');
        }

        async function handleSmartPaste(text) {
            const terms = splitSmartSearchTerms(text);
            if (terms.length < 2) {
                return false;
            }

            setProgressOverlay(true, `Mencari ${terms.length} pendaftar dari teks yang ditempel...`, 30);

            try {
                const requests = terms.map(term => $.get(routes.candidates, {
                    q: term,
                    tahun_pelajaran_id: $('#tahun_pelajaran_id').val(),
                    include_all: 0
                }).then(response => ({
                    term,
                    match: chooseBestCandidate(term, response.results || [])
                })).catch(() => ({
                    term,
                    match: null
                })));

                const responses = await Promise.all(requests);
                const matches = responses.map(item => item.match).filter(Boolean);
                const uniqueMatches = matches.filter((item, index, rows) => rows.findIndex(row => String(row.id) === String(item.id)) === index);
                const notFound = responses.filter(item => !item.match).map(item => item.term);

                if (uniqueMatches.length) {
                    addCandidateSelections(uniqueMatches);
                }

                const message = notFound.length
                    ? `${uniqueMatches.length} pendaftar ditemukan. ${notFound.length} baris belum ketemu: ${notFound.slice(0, 5).join(', ')}${notFound.length > 5 ? ', ...' : ''}`
                    : `${uniqueMatches.length} pendaftar berhasil ditambahkan ke pilihan.`;

                Swal.fire({
                    title: uniqueMatches.length ? 'Pencarian multi data selesai' : 'Belum ada yang cocok',
                    text: message,
                    icon: uniqueMatches.length ? (notFound.length ? 'warning' : 'success') : 'warning'
                });
            } finally {
                setProgressOverlay(false);
            }

            return true;
        }

        function confirmUnpaid(rows, actionText) {
            const unpaid = rows.filter(row => !row.has_registrasi_komite);
            if (!unpaid.length) {
                return Promise.resolve(true);
            }

            return Swal.fire({
                title: 'Ada pendaftar belum bayar',
                html: `<div class="text-left">${unpaid.length} pendaftar belum tercatat registrasi komite.<br>Yakin tetap ${actionText}?</div>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, lanjutkan',
                cancelButtonText: 'Batal'
            }).then(result => result.isConfirmed);
        }

        function showResult(result) {
            const rows = (result.items || []).map(item => `
                <tr class="${item.status === 'success' ? 'mat-result-row-success' : 'mat-result-row-danger'}">
                    <td><strong>${escapeHtml(item.nama || '-')}</strong></td>
                    <td>${escapeHtml(item.nisn || '-')}</td>
                    <td class="mat-result-message">${escapeHtml(item.message || '-')}</td>
                    <td class="text-center">${item.documents_copied || 0}</td>
                </tr>
            `).join('');

            $('#resultBox').show().html(`
                <div class="mat-result-card">
                    <div class="mat-result-head">
                        <div class="mat-result-title">
                            <span><i class="fas fa-check-double"></i></span>
                            <div>
                                <strong>Hasil Sync Matrikulasi</strong>
                                <small>Ringkasan import data dan dokumen dari PPDB.</small>
                            </div>
                        </div>
                        <div class="mat-result-stats">
                            <span class="mat-result-stat is-success">${result.success || 0} berhasil</span>
                            <span class="mat-result-stat is-danger">${result.failed || 0} gagal</span>
                            <span class="mat-result-stat">${result.documents_copied || 0} dokumen</span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm mat-result-table">
                            <thead><tr><th>Nama</th><th>NISN</th><th>Pesan</th><th class="text-center">Dok.</th></tr></thead>
                            <tbody>${rows || '<tr><td colspan="4" class="text-center text-muted py-3">Tidak ada rincian hasil.</td></tr>'}</tbody>
                        </table>
                    </div>
                </div>
            `);
        }

        $(function () {
            $.ajaxSetup({
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || @json(csrf_token())}
            });

            $('#tahun_pelajaran_id').on('change', function () {
                const url = new URL(window.location.href);
                url.searchParams.set('tahun_pelajaran_id', this.value);
                window.location.href = url.toString();
            });

            $('#calon_siswa_ids').select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: 'Cari nama, NISN, nomor registrasi, atau nomor tes',
                dropdownCssClass: 'mat-quick-search-dropdown',
                minimumInputLength: 2,
                ajax: {
                    url: routes.candidates,
                    dataType: 'json',
                    delay: 300,
                    data: params => ({
                        q: params.term,
                        tahun_pelajaran_id: $('#tahun_pelajaran_id').val(),
                        include_all: 0
                    }),
                    processResults: data => data,
                    error: xhr => {
                        Swal.fire('Koneksi PPDB gagal', xhr.responseJSON?.message || 'Tidak bisa mengambil data dari PPDB.', 'error');
                    }
                },
                templateResult: function (item) {
                    if (!item.id) return item.text;
                    return candidateOption(item);
                }
            });

            $(document).on('paste', '.mat-quick-search .select2-search__field', function (event) {
                const clipboard = event.originalEvent?.clipboardData || window.clipboardData;
                const text = clipboard?.getData('text') || '';
                const terms = splitSmartSearchTerms(text);

                if (terms.length < 2) {
                    return;
                }

                event.preventDefault();
                $('#calon_siswa_ids').select2('close');
                handleSmartPaste(text);
            });

            $('#calon_siswa_ids').on('change', function () {
                if (suppressSelectionReset) {
                    return;
                }

                previewIds = [];
                currentPreviewRows = [];
                $('#btnImport').prop('disabled', true);
                updatePreviewSummary([]);
            });

            $('#btnOpenAddModal').on('click', function () {
                selectedBrowserRows.clear();
                updateBrowserSelectionInfo();
                $('#addCandidateModal').modal('show');
            });

            $('#addCandidateModal').on('shown.bs.modal', function () {
                initBrowserTable();
                browserTable.ajax.reload(null, true);
            });

            $('#candidateBrowserTable').on('change', '.browser-check', function () {
                const id = $(this).data('id');
                const row = browserTable.row($(this).closest('tr')).data();
                if (this.checked && row) {
                    selectedBrowserRows.set(id, row);
                } else {
                    selectedBrowserRows.delete(id);
                }

                $(this).closest('tr').toggleClass('is-selected', this.checked);
                updateBrowserSelectionInfo();
            });

            $('#checkAllBrowserCandidates').on('change', function () {
                const checked = this.checked;
                $('#candidateBrowserTable .browser-check').each(function () {
                    $(this).prop('checked', checked).trigger('change');
                });
            });

            initParticipantTable();

            $('#participant_kelompok_filter').on('change', function () {
                selectedParticipantRows.clear();
                updateParticipantSelectionInfo();
                participantTable.ajax.reload(null, true);
            });

            $('#matrikulasiPesertaTable').on('change', '.participant-check', function () {
                const id = $(this).data('id');
                const row = participantTable.row($(this).closest('tr')).data();
                if (this.checked && row) {
                    selectedParticipantRows.set(id, row);
                } else {
                    selectedParticipantRows.delete(id);
                }

                $(this).closest('tr').toggleClass('is-selected', this.checked);
                updateParticipantSelectionInfo();
            });

            $('#checkAllParticipants').on('change', function () {
                const checked = this.checked;
                $('#matrikulasiPesertaTable .participant-check').each(function () {
                    $(this).prop('checked', checked).trigger('change');
                });
            });

            $('#btnAssignKelompok').on('click', function () {
                const ids = Array.from(selectedParticipantRows.keys());
                const kelompokId = $('#assign_kelompok_id').val();
                if (!ids.length || !kelompokId) {
                    Swal.fire('Data belum lengkap', 'Centang peserta dan pilih kelompok tujuan.', 'warning');
                    return;
                }

                const $button = $(this);
                setButtonLoading($button, true, 'Assign...', '<i class="fas fa-random mr-1"></i>Assign Kelompok');
                $.post(routes.assignKelompok, {
                    peserta_ids: ids,
                    kelompok_id: kelompokId,
                    tahun_pelajaran_id: $('#tahun_pelajaran_id').val()
                }).done(response => {
                    selectedParticipantRows.clear();
                    updateParticipantSelectionInfo();
                    participantTable.ajax.reload(null, false);
                    Swal.fire('Berhasil', response.message || 'Peserta berhasil diassign.', 'success');
                }).fail(xhr => {
                    Swal.fire('Gagal assign', xhr.responseJSON?.message || 'Assign kelompok gagal.', 'error');
                }).always(() => {
                    setButtonLoading($button, false, 'Assign...', '<i class="fas fa-random mr-1"></i>Assign Kelompok');
                });
            });

            $('#btnGenerateAccounts').on('click', function () {
                const ids = Array.from(selectedParticipantRows.keys());
                if (!ids.length) {
                    Swal.fire('Belum ada peserta', 'Centang peserta yang akan dibuatkan akun matrikulasi.', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'Buat akun matrikulasi?',
                    text: 'Akun ini hanya untuk fase matrikulasi dan belum menjadi akun siswa reguler final.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, buat akun',
                    cancelButtonText: 'Batal'
                }).then(result => {
                    if (!result.isConfirmed) return;

                    const $button = $('#btnGenerateAccounts');
                    setButtonLoading($button, true, 'Membuat...', '<i class="fas fa-key mr-1"></i>Buat Akun');
                    $.post(routes.generateAccounts, {
                        peserta_ids: ids,
                        tahun_pelajaran_id: $('#tahun_pelajaran_id').val()
                    }).done(response => {
                        selectedParticipantRows.clear();
                        updateParticipantSelectionInfo();
                        participantTable.ajax.reload(null, false);
                        Swal.fire('Selesai', response.message || 'Generate akun selesai.', 'success');
                    }).fail(xhr => {
                        Swal.fire('Gagal membuat akun', xhr.responseJSON?.message || 'Generate akun gagal.', 'error');
                    }).always(() => {
                        setButtonLoading($button, false, 'Membuat...', '<i class="fas fa-key mr-1"></i>Buat Akun');
                    });
                });
            });

            $('#btnCreateKelompok').on('click', function () {
                const nama = $('#new_kelompok_nama').val().trim();
                if (!nama) {
                    Swal.fire('Nama belum diisi', 'Isi nama kelompok matrikulasi terlebih dahulu.', 'warning');
                    return;
                }

                $.post(routes.kelompokStore, {
                    tahun_pelajaran_id: $('#tahun_pelajaran_id').val(),
                    nama: nama,
                    kode: $('#new_kelompok_kode').val(),
                    tingkat_kelas: $('#new_kelompok_tingkat').val(),
                    jenis_kelompok: $('#new_kelompok_jenis').val(),
                    kapasitas: $('#new_kelompok_kapasitas').val()
                }).done(response => {
                    const data = response.data || {};
                    $('#kelompok_id').append(new Option(data.text, data.id, true, true)).trigger('change');
                    $('#participant_kelompok_filter').append(new Option(data.text, data.id, false, false));
                    $('#assign_kelompok_id').append(new Option(data.text, data.id, false, false));
                    $('#new_kelompok_nama').val('');
                    $('#new_kelompok_kode').val('');
                    $('#new_kelompok_tingkat').val('');
                    $('#new_kelompok_jenis').val('reguler');
                    $('#new_kelompok_kapasitas').val('');
                    Swal.fire('Berhasil', response.message || 'Kelompok dibuat.', 'success');
                }).fail(xhr => {
                    Swal.fire('Gagal', xhr.responseJSON?.message || 'Kelompok tidak bisa dibuat.', 'error');
                });
            });

            $('#btnPreview').on('click', function () {
                const ids = selectedIds();
                if (!ids.length) {
                    Swal.fire('Belum ada pendaftar', 'Pilih minimal satu pendaftar PPDB.', 'warning');
                    return;
                }

                const $button = $(this);
                setButtonLoading($button, true, 'Preview...', '<i class="fas fa-eye mr-1"></i>Preview');
                $('#btnImport').prop('disabled', true);

                $.post(routes.preview, {
                    calon_siswa_ids: ids,
                    tahun_pelajaran_id: $('#tahun_pelajaran_id').val(),
                    include_all: 0
                }).done(response => {
                    renderPreview(response.data || []);
                }).fail(xhr => {
                    Swal.fire('Preview gagal', xhr.responseJSON?.message || 'Gagal membuat preview.', 'error');
                }).always(() => {
                    setButtonLoading($button, false, 'Preview...', '<i class="fas fa-eye mr-1"></i>Preview');
                });
            });

            $('#btnLoadAll').on('click', function () {
                const $button = $(this);
                setButtonLoading($button, true, 'Memuat...', '<i class="fas fa-list mr-1"></i>Muat Semua PPDB');
                $('#btnPreview').prop('disabled', true);
                $('#btnImport').prop('disabled', true);
                setProgressOverlay(true, 'Mengambil semua pendaftar eligible dari PPDB...', 35);

                $.post(routes.previewAll, {
                    tahun_pelajaran_id: $('#tahun_pelajaran_id').val()
                }).done(response => {
                    const rows = response.data || [];
                    setProgressOverlay(true, 'Menyiapkan tabel preview...', 85);
                    suppressSelectionReset = true;
                    $('#calon_siswa_ids').val(null).trigger('change');
                    suppressSelectionReset = false;
                    renderPreview(rows);
                    updatePreviewSummary(rows, 'all');
                    Swal.fire('Data dimuat', `${rows.length} pendaftar PPDB siap dipreview.`, 'success');
                }).fail(xhr => {
                    Swal.fire('Gagal memuat', xhr.responseJSON?.message || 'Tidak bisa mengambil semua data PPDB.', 'error');
                }).always(() => {
                    setProgressOverlay(false);
                    setButtonLoading($button, false, 'Memuat...', '<i class="fas fa-list mr-1"></i>Muat Semua PPDB');
                    $('#btnPreview').prop('disabled', false);
                });
            });

            $('#btnAddCandidatesPreview').on('click', function () {
                const ids = Array.from(selectedBrowserRows.keys());
                if (!ids.length) {
                    Swal.fire('Belum ada pilihan', 'Centang minimal satu pendaftar dari tabel browse.', 'warning');
                    return;
                }

                const $button = $(this);
                setButtonLoading($button, true, 'Menambahkan...', '<i class="fas fa-plus mr-1"></i>Tambahkan ke Preview');
                setProgressOverlay(true, 'Mengambil detail pendaftar dari PPDB...', 45);

                $.post(routes.preview, {
                    calon_siswa_ids: ids,
                    tahun_pelajaran_id: $('#tahun_pelajaran_id').val(),
                    include_all: 1
                }).done(async response => {
                    const rows = response.data || [];
                    const confirmed = await confirmUnpaid(rows, 'menambahkan ke preview');
                    if (!confirmed) {
                        return;
                    }

                    const merged = [...currentPreviewRows];
                    rows.forEach(row => {
                        const index = merged.findIndex(existing => existing.id === row.id);
                        if (index >= 0) {
                            merged[index] = row;
                        } else {
                            merged.push(row);
                        }
                    });

                    renderPreview(merged);
                    $('#addCandidateModal').modal('hide');
                    Swal.fire('Ditambahkan', `${rows.length} pendaftar masuk preview.`, 'success');
                }).fail(xhr => {
                    Swal.fire('Gagal menambahkan', xhr.responseJSON?.message || 'Tidak bisa membuat preview pendaftar.', 'error');
                }).always(() => {
                    setProgressOverlay(false);
                    setButtonLoading($button, false, 'Menambahkan...', '<i class="fas fa-plus mr-1"></i>Tambahkan ke Preview');
                });
            });

            $('#btnImport').on('click', function () {
                const ids = previewIds.length ? previewIds : selectedIds();
                const kelompokId = $('#kelompok_id').val();
                if (!ids.length || !kelompokId) {
                    Swal.fire('Data belum lengkap', 'Pilih pendaftar dan kelompok matrikulasi tujuan.', 'warning');
                    return;
                }

                const unpaidCount = currentPreviewRows.filter(row => ids.includes(row.id) && !row.has_registrasi_komite).length;
                const confirmText = unpaidCount
                    ? `Data akan masuk staging matrikulasi. Ada ${unpaidCount} pendaftar belum registrasi komite.`
                    : 'Data akan masuk staging matrikulasi, belum menjadi siswa reguler.';

                Swal.fire({
                    title: 'Sync ke matrikulasi?',
                    text: confirmText,
                    icon: unpaidCount ? 'warning' : 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, sync',
                    cancelButtonText: 'Batal'
                }).then(result => {
                    if (!result.isConfirmed) return;

                    $('#btnImport').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Menyinkronkan...');
                    $.post(routes.import, {
                        calon_siswa_ids: ids,
                        tahun_pelajaran_id: $('#tahun_pelajaran_id').val(),
                        kelompok_id: kelompokId,
                        include_documents: $('#include_documents').is(':checked') ? 1 : 0,
                        allow_unpaid: unpaidCount ? 1 : 0
                    }).done(response => {
                        showResult(response.data || {});
                        if (participantTable) {
                            participantTable.ajax.reload(null, false);
                        }
                        Swal.fire('Selesai', response.message || 'Sync selesai.', 'success');
                    }).fail(xhr => {
                        Swal.fire('Sync gagal', xhr.responseJSON?.message || 'Gagal sync.', 'error');
                    }).always(() => {
                        $('#btnImport').prop('disabled', false).html('<i class="fas fa-sync-alt mr-1"></i>Sync ke Matrikulasi');
                    });
                });
            });
        });
    </script>
@stop

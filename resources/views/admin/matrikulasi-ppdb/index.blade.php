@extends('adminlte::page')

@section('title', 'Matrikulasi PPDB')

@section('plugins.Select2', true)
@section('plugins.Sweetalert2', true)

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
                                        {{ $kelompok->nama }}{{ $kelompok->kapasitas ? ' - '.$kelompok->pesertas_count.'/'.$kelompok->kapasitas : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mat-inline-create">
                            <div class="form-row">
                                <div class="col-12">
                                    <label for="new_kelompok_nama">Buat Kelompok Baru</label>
                                </div>
                                <div class="col-8">
                                    <input id="new_kelompok_nama" class="form-control" placeholder="Matrikulasi A">
                                </div>
                                <div class="col-4">
                                    <input id="new_kelompok_kapasitas" type="number" min="1" class="form-control" placeholder="Kapasitas">
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-block mt-2" id="btnCreateKelompok">
                                <i class="fas fa-plus mr-1"></i>Buat Kelompok
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
                <div class="card mat-card">
                    <div class="card-header border-0 d-flex align-items-center justify-content-between mat-card-head">
                        <h3 class="card-title"><i class="fas fa-cloud-download-alt mr-2"></i>Sync Data PPDB</h3>
                        <span class="badge badge-light">Lulus + Registrasi Komite</span>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="calon_siswa_ids">Pendaftar PPDB</label>
                            <select id="calon_siswa_ids" class="form-control" multiple></select>
                            <small class="form-text text-muted">
                                Pilih beberapa pendaftar untuk preview manual, atau muat semua data PPDB yang sudah lulus dan registrasi komite.
                            </small>
                        </div>

                        <div class="mat-actions">
                            <button type="button" class="btn btn-outline-primary" id="btnLoadAll">
                                <i class="fas fa-list mr-1"></i>Muat Semua PPDB
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="btnOpenAddModal">
                                <i class="fas fa-user-plus mr-1"></i>Tambah Pendaftar
                            </button>
                            <button type="button" class="btn btn-secondary" id="btnPreview">
                                <i class="fas fa-eye mr-1"></i>Preview
                            </button>
                            <button type="button" class="btn btn-primary" id="btnImport" disabled>
                                <i class="fas fa-sync-alt mr-1"></i>Sync ke Matrikulasi
                            </button>
                        </div>

                        <div class="mat-preview-summary mt-3" id="previewSummary">
                            <i class="fas fa-info-circle mr-1"></i>
                            Belum ada data preview.
                        </div>

                        <div class="table-responsive mt-3">
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

                        <div id="resultBox" class="mt-3" style="display:none;"></div>
                    </div>
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
                                <strong>Cari Pendaftar</strong>
                                <span>Nama, NISN, atau No.Tes</span>
                            </div>
                            <i class="fas fa-search"></i>
                        </div>
                        <select id="browse_candidate_ids" class="form-control" multiple></select>
                        <div class="mat-search-foot">
                            <span><i class="fas fa-mouse-pointer mr-1"></i>Pilih satu atau beberapa pendaftar</span>
                            <span><i class="fas fa-tags mr-1"></i>Status bayar tampil di kanan</span>
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
        .mat-inline-create {
            border: 1px solid #e9edf5;
            border-radius: 8px;
            padding: .85rem;
            background: #f8fafc;
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
        .status-sudah_matrikulasi { background: #e3f2fd; color: #0d47a1; }
        .status-sudah_jadi_siswa { background: #fff3cd; color: #7a4d00; }
        @media (max-width: 991.98px) {
            .mat-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 575.98px) {
            .mat-stats { grid-template-columns: 1fr; }
            .mat-actions .btn { width: 100%; }
            .mat-stat strong { font-size: 1.1rem; }
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
            preview: @json(route('admin.matrikulasi-ppdb.preview')),
            previewAll: @json(route('admin.matrikulasi-ppdb.preview-all')),
            import: @json(route('admin.matrikulasi-ppdb.import')),
            kelompokStore: @json(route('admin.matrikulasi-ppdb.kelompok.store')),
        };

        let previewIds = [];
        let currentPreviewRows = [];
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
                sudah_matrikulasi: 'Sudah Matrikulasi',
                sudah_jadi_siswa: 'Sudah Jadi Siswa',
            }[status] || status;

            return `<span class="status-chip status-${status}">${label}</span>`;
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
                <tr class="${item.status === 'success' ? 'table-success' : 'table-danger'}">
                    <td>${item.nama}</td>
                    <td>${item.nisn}</td>
                    <td>${item.message}</td>
                    <td class="text-center">${item.documents_copied || 0}</td>
                </tr>
            `).join('');

            $('#resultBox').show().html(`
                <div class="alert alert-info mb-2">
                    <strong>Hasil sync:</strong> ${result.success} berhasil, ${result.failed} gagal, ${result.documents_copied} dokumen disalin.
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead><tr><th>Nama</th><th>NISN</th><th>Pesan</th><th>Dok.</th></tr></thead>
                        <tbody>${rows}</tbody>
                    </table>
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

            $('#browse_candidate_ids').select2({
                theme: 'bootstrap4',
                width: '100%',
                dropdownParent: $('#addCandidateModal'),
                placeholder: 'Ketik nama, NISN, atau No.Tes',
                minimumInputLength: 0,
                ajax: {
                    url: routes.candidates,
                    dataType: 'json',
                    delay: 250,
                    data: params => ({
                        q: params.term || '',
                        tahun_pelajaran_id: $('#tahun_pelajaran_id').val(),
                        include_all: 1
                    }),
                    processResults: data => data,
                    error: xhr => {
                        Swal.fire('Koneksi PPDB gagal', xhr.responseJSON?.message || 'Tidak bisa mengambil data semua pendaftar.', 'error');
                    }
                },
                templateResult: candidateOption,
                templateSelection: item => item.nama_lengkap || item.text
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
                $('#browse_candidate_ids').val(null).trigger('change');
                $('#addCandidateModal').modal('show');
            });

            $('#addCandidateModal').on('shown.bs.modal', function () {
                $('#browse_candidate_ids').select2('open');
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
                    kapasitas: $('#new_kelompok_kapasitas').val()
                }).done(response => {
                    const data = response.data || {};
                    $('#kelompok_id').append(new Option(data.text, data.id, true, true)).trigger('change');
                    $('#new_kelompok_nama').val('');
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
                const ids = $('#browse_candidate_ids').val() || [];
                if (!ids.length) {
                    Swal.fire('Belum ada pilihan', 'Pilih minimal satu pendaftar dari daftar browse.', 'warning');
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

@extends('adminlte::page')

@section('title', 'Matrikulasi PPDB')

@section('plugins.Select2', true)
@section('plugins.Sweetalert2', true)

@section('content_header')
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between">
        <div>
            <h1 class="mb-1"><i class="fas fa-users-cog mr-2"></i>Matrikulasi PPDB</h1>
            <div class="text-muted">Staging calon siswa baru sebelum ditetapkan ke kelas reguler.</div>
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
                    <div class="card-header border-0">
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
                    <div class="card-header border-0 d-flex align-items-center justify-content-between">
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
                                    <th>No. Reg</th>
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
@stop

@section('css')
    <style>
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
            box-shadow: none;
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
                    <td>${row.nomor_registrasi || row.nomor_tes || '-'}</td>
                    <td>${row.tahun_ppdb || '-'}</td>
                    <td>${row.jurusan_final || row.jurusan_awal || '-'}</td>
                    <td class="text-center">${row.documents_count || 0}</td>
                    <td>${statusChip(row.import_status)}</td>
                </tr>
            `).join('');

            $tbody.html(html);
            $('#btnImport').prop('disabled', rows.some(row => row.import_status === 'sudah_jadi_siswa'));
            updatePreviewSummary(rows);
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
                        tahun_pelajaran_id: $('#tahun_pelajaran_id').val()
                    }),
                    processResults: data => data,
                    error: xhr => {
                        Swal.fire('Koneksi PPDB gagal', xhr.responseJSON?.message || 'Tidak bisa mengambil data dari PPDB.', 'error');
                    }
                },
                templateResult: function (item) {
                    if (!item.id) return item.text;
                    return $(`<div><strong>${item.text}</strong><br><small>${item.tahun || '-'} | Dokumen: ${item.documents_count || 0} | ${item.status || '-'}</small></div>`);
                }
            });

            $('#calon_siswa_ids').on('change', function () {
                if (suppressSelectionReset) {
                    return;
                }

                previewIds = [];
                $('#btnImport').prop('disabled', true);
                updatePreviewSummary([]);
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
                    tahun_pelajaran_id: $('#tahun_pelajaran_id').val()
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

            $('#btnImport').on('click', function () {
                const ids = previewIds.length ? previewIds : selectedIds();
                const kelompokId = $('#kelompok_id').val();
                if (!ids.length || !kelompokId) {
                    Swal.fire('Data belum lengkap', 'Pilih pendaftar dan kelompok matrikulasi tujuan.', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'Sync ke matrikulasi?',
                    text: 'Data akan masuk staging matrikulasi, belum menjadi siswa reguler.',
                    icon: 'question',
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
                        include_documents: $('#include_documents').is(':checked') ? 1 : 0
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

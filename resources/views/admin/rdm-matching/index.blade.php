@extends('adminlte::page')

@section('title', 'Matching Siswa RDM - SIMANSA')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-random"></i> Matching Data Siswa RDM</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('admin.rdm-sync.index') }}" class="btn btn-secondary">
                    <i class="fas fa-sync-alt"></i> Integrasi RDM
                </a>
            </div>
        </div>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-search mr-2"></i>Analisis Kecocokan Data Siswa</h3>
                <div class="card-tools">
                    @if($activeTahun)
                        <span class="badge badge-info">Tahun Ajaran RDM: {{ $activeTahun->tahunajaran_nama }}</span>
                    @else
                        <span class="badge badge-warning">Tidak ada tahun ajaran aktif di RDM</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle mr-1"></i>
                    Fitur ini membandingkan data siswa dari <strong>RDM (Rapor Digital Madrasah)</strong> dengan data di <strong>SIMANSA</strong>.
                        Proses dekripsi nama siswa dilakukan otomatis. Estimasi waktu proses: <strong>30–90 detik</strong> untuk semua tingkat.
                        <br><small class="text-muted">Smart Matching: selain NISN dan NIS, nama yang mirip/singkatan/variasi ejaan juga dideteksi otomatis.</small>
                <div class="d-flex align-items-center flex-wrap mb-4" style="gap: .75rem;">
                    <div>
                        <label class="mb-0 mr-2 font-weight-bold text-muted" style="font-size:.87rem;">Filter Tingkat:</label>
                        <select id="selectTingkat" class="form-control form-control-sm d-inline-block" style="width:auto; min-width:150px;">
                            @foreach($tingkatOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button id="btnRunMatching" class="btn btn-primary">
                        <i class="fas fa-play mr-1"></i> Jalankan Matching
                    </button>
                </div>

                {{-- Loading --}}
                <div id="loadingArea" class="text-center py-5" style="display:none;">
                    <div class="spinner-border text-primary" role="status" style="width:3rem;height:3rem;"></div>
                    <p class="mt-3 text-muted">Mengambil data RDM dan mendekripsi nama siswa…<br><small>Proses ini membutuhkan waktu 30–60 detik.</small></p>
                </div>

                {{-- Error --}}
                <div id="errorArea" class="alert alert-danger" style="display:none;"></div>

                {{-- Results --}}
                <div id="resultArea" style="display:none;">

                    {{-- Summary Cards --}}
                    <div class="row mb-4" id="summaryCards"></div>

                    {{-- Tabs --}}
                    <ul class="nav nav-tabs" id="matchingTabs" role="tablist" style="border-bottom: 2px solid #dee2e6;">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#tabRdmOnly" role="tab" style="color: #dc3545; font-weight:600;">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                Belum Ada di SIMANSA
                                <span class="badge badge-danger ml-1" id="badgeRdmOnly">0</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tabFuzzy" role="tab" style="color: #856404; font-weight:600;">
                                <i class="fas fa-magic mr-1"></i>
                                Perlu Verifikasi Nama
                                <span class="badge badge-warning ml-1" id="badgeFuzzy">0</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tabSimansaOnly" role="tab" style="color: #fd7e14; font-weight:600;">
                                <i class="fas fa-user-slash mr-1"></i>
                                Tidak Ada di RDM
                                <span class="badge badge-warning ml-1" id="badgeSimansaOnly">0</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tabMatched" role="tab" style="color: #28a745; font-weight:600;">
                                <i class="fas fa-check-circle mr-1"></i>
                                Cocok
                                <span class="badge badge-success ml-1" id="badgeMatched">0</span>
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content mt-3" id="matchingTabContent">
                        {{-- Tab: Belum Ada di SIMANSA (RDM Only) --}}
                        <div class="tab-pane fade show active" id="tabRdmOnly" role="tabpanel">
                            <p class="text-muted small mb-2">
                                Siswa yang tercatat di RDM (tahun ajaran aktif) tetapi <strong>tidak ditemukan sama sekali</strong> di SIMANSA — baik via NISN, NIS, maupun nama.
                                Perlu segera diverifikasi dan ditambahkan ke SIMANSA.
                            </p>
                            <div class="simansa-table-scroll">
                                <table id="tableRdmOnly" class="table table-sm table-hover simansa-match-table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Siswa (RDM)</th>
                                            <th>NIS</th>
                                            <th>NISN</th>
                                            <th>Tingkat</th>
                                            <th>Kelas RDM</th>
                                            <th>L/P</th>
                                            <th>Tgl Lahir</th>
                                        </tr>
                                    </thead>
                                    <tbody id="bodyRdmOnly"></tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Tab: Perlu Verifikasi Nama (Fuzzy) --}}
                        <div class="tab-pane fade" id="tabFuzzy" role="tabpanel">
                            <p class="text-muted small mb-2">
                                Siswa RDM yang <strong>tidak cocok via NISN/NIS</strong> tetapi ditemukan kandidat nama mirip di SIMANSA.
                                Admin perlu memverifikasi secara manual apakah ini siswa yang sama.
                                <span class="badge badge-success">Sangat Mirip ≥ 88%</span>
                                <span class="badge badge-warning text-dark">Mirip ≥ 72%</span>
                                <span class="badge badge-secondary">Potensi Mirip ≥ 60%</span>
                            </p>
                            <div class="simansa-table-scroll">
                                <table id="tableFuzzy" class="table table-sm table-hover simansa-match-table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Siswa (RDM)</th>
                                            <th>NIS</th>
                                            <th>Tingkat / Kelas RDM</th>
                                            <th>Kandidat di SIMANSA <small class="text-muted">(klik untuk detail)</small></th>
                                        </tr>
                                    </thead>
                                    <tbody id="bodyFuzzy"></tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Tab: Tidak Ada di RDM (SIMANSA Only) --}}
                        <div class="tab-pane fade" id="tabSimansaOnly" role="tabpanel">
                            <p class="text-muted small mb-2">
                                Siswa yang ada di SIMANSA tetapi <strong>tidak ditemukan</strong> di RDM. Bisa jadi belum diinput ke RDM, atau NISN/NIS belum cocok.
                            </p>
                            <div class="simansa-table-scroll">
                                <table id="tableSimansaOnly" class="table table-sm table-hover simansa-match-table">
                                    <thead>
                                        <tr>
                                            <th style="width:3rem">No</th>
                                            <th>Nama Siswa (SIMANSA)</th>
                                            <th style="width:8rem">NISN</th>
                                            <th style="width:7rem">Kelas</th>
                                            <th style="width:8rem">Data Lengkap</th>
                                            <th style="width:5rem">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="bodySimansaOnly"></tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Tab: Matched --}}
                        <div class="tab-pane fade" id="tabMatched" role="tabpanel">
                            <p class="text-muted small mb-2">
                                Siswa yang ditemukan di kedua sistem. Cocok via NISN atau NIS.
                            </p>
                            <div class="simansa-table-scroll">
                                <table id="tableMatched" class="table table-sm table-hover simansa-match-table">
                                    <thead>
                                        <tr>
                                            <th style="width:3rem">No</th>
                                            <th>Nama (RDM)</th>
                                            <th>Nama (SIMANSA)</th>
                                            <th style="width:8rem">NISN</th>
                                            <th style="width:6rem">NIS</th>
                                            <th style="width:7rem">Kelas RDM</th>
                                            <th style="width:7rem">Kelas SIMANSA</th>
                                            <th style="width:6rem">Match Via</th>
                                            <th style="width:8rem">Data Lengkap</th>
                                            <th style="width:5rem">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="bodyMatched"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    {{-- end tab-content --}}

                </div>
                {{-- end resultArea --}}

            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<link rel="stylesheet" href="//cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
<style>
    .simansa-match-table thead th {
        background: #f8fafc;
        color: #374151;
        font-size: .77rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        border-bottom: 2px solid #e5e7eb;
        white-space: nowrap;
        padding: .5rem .6rem;
    }
    .simansa-match-table tbody td {
        font-size: .85rem;
        vertical-align: middle;
        padding: .4rem .6rem;
        border-bottom: 1px solid #f1f5f9;
        border-top: none;
    }
    .simansa-match-table tbody tr:hover td {
        background: #f0f7ff;
    }
    .simansa-table-scroll {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .simansa-table-scroll > table {
        min-width: 700px;
    }

    .rdm-kpi {
        border-radius: 16px;
        padding: .9rem 1.1rem;
        background: #fff;
        border: 1px solid #e5e7eb;
        box-shadow: 0 8px 20px rgba(15,23,42,.05);
        display: flex;
        align-items: center;
        gap: .75rem;
    }
    .rdm-kpi__icon {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    .rdm-kpi__value { font-size: 1.6rem; font-weight: 700; color: #0f172a; line-height: 1.1; }
    .rdm-kpi__label { font-size: .83rem; color: #64748b; margin-top: .15rem; }
    .rdm-kpi--blue  .rdm-kpi__icon { background: #dbeafe; color: #1d4ed8; }
    .rdm-kpi--red   .rdm-kpi__icon { background: #fee2e2; color: #dc2626; }
    .rdm-kpi--orange .rdm-kpi__icon { background: #ffedd5; color: #ea580c; }
    .rdm-kpi--green .rdm-kpi__icon { background: #dcfce7; color: #16a34a; }
    .rdm-kpi--purple .rdm-kpi__icon { background: #ede9fe; color: #7c3aed; }

    #matchingTabs .nav-link { color: #6c757d !important; }
    #matchingTabs .nav-link.active { font-weight: 600 !important; border-bottom: 3px solid #1d4ed8; }

    .badge-match-nisn { background: #dbeafe; color: #1d4ed8; font-size: .78rem; padding: .25em .6em; border-radius: 6px; font-weight: 600; }
    .badge-match-nis  { background: #e9d5ff; color: #7c3aed; font-size: .78rem; padding: .25em .6em; border-radius: 6px; font-weight: 600; }
    .badge-data-ok    { background: #dcfce7; color: #16a34a; font-size: .78rem; padding: .25em .6em; border-radius: 6px; }
    .badge-data-no    { background: #fee2e2; color: #dc2626; font-size: .78rem; padding: .25em .6em; border-radius: 6px; }

    /* Fuzzy candidate chips */
    .fuzzy-chip {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        padding: .3rem .65rem;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 600;
        cursor: pointer;
        margin: .15rem .1rem;
        text-decoration: none;
        transition: opacity .15s, transform .1s;
        border: none;
        line-height: 1.4;
    }
    .fuzzy-chip:hover { opacity: .82; transform: translateY(-1px); text-decoration: none; }
    .fuzzy-chip--high   { background: #d1fae5; color: #065f46; }
    .fuzzy-chip--medium { background: #fef3c7; color: #92400e; }
    .fuzzy-chip--low    { background: #f1f5f9; color: #475569; }
    .fuzzy-chip .score-pct { font-size: .72rem; opacity: .8; font-weight: 700; margin-left: .1rem; }
</style>
@endsection

@section('js')
<script src="//cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="//cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
<script>
let dtRdmOnly = null;
let dtFuzzy = null;
let dtSimansaOnly = null;
let dtMatched = null;

$(function () {
    // Fix tab text color (AdminLTE accent-white override)
    $('#matchingTabs .nav-link').on('shown.bs.tab', function () {
        $('#matchingTabs .nav-link').css('color', '#6c757d');
        $(this).css('color', '#0f172a');
    });
    $('#matchingTabs .nav-link.active').css('color', '#0f172a');

    $('#btnRunMatching').on('click', function () {
        runMatching();
    });
});

function runMatching() {
    const tingkatId = $('#selectTingkat').val();

    $('#resultArea').hide();
    $('#errorArea').hide();
    $('#loadingArea').show();
    $('#btnRunMatching').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses…');

    $.ajax({
        url: '{{ route("admin.rdm-matching.run") }}',
        method: 'POST',
        data: { tingkat_id: tingkatId, _token: '{{ csrf_token() }}' },
        timeout: 300000, // 5 menit
        success: function (res) {
            if (res.status === 'success') {
                renderResult(res.data);
            } else {
                showError(res.message || 'Terjadi kesalahan.');
            }
        },
        error: function (xhr) {
            const msg = xhr.responseJSON?.message || 'Gagal menghubungi server. Coba lagi.';
            showError(msg);
        },
        complete: function () {
            $('#loadingArea').hide();
            $('#btnRunMatching').prop('disabled', false).html('<i class="fas fa-play mr-1"></i> Jalankan Matching');
        },
    });
}

function showError(msg) {
    $('#errorArea').text(msg).show();
    $('#loadingArea').hide();
}

function renderResult(data) {
    const s = data.stats;

    // Summary Cards
    const matchPct = s.total_rdm > 0 ? Math.round(s.total_matched / s.total_rdm * 100) : 0;
    $('#summaryCards').html(`
        <div class="col-6 col-xl mb-3">
            <div class="rdm-kpi rdm-kpi--blue">
                <div class="rdm-kpi__icon"><i class="fas fa-database"></i></div>
                <div><div class="rdm-kpi__value">${fmt(s.total_rdm)}</div><div class="rdm-kpi__label">Total Siswa RDM<br><small>${data.tahun_rdm || ''} · ${data.tingkat_label}</small></div></div>
            </div>
        </div>
        <div class="col-6 col-xl mb-3">
            <div class="rdm-kpi rdm-kpi--purple">
                <div class="rdm-kpi__icon"><i class="fas fa-users"></i></div>
                <div><div class="rdm-kpi__value">${fmt(s.total_simansa)}</div><div class="rdm-kpi__label">Total Siswa SIMANSA<br><small>${data.tingkat_label}</small></div></div>
            </div>
        </div>
        <div class="col-6 col-xl mb-3">
            <div class="rdm-kpi rdm-kpi--green">
                <div class="rdm-kpi__icon"><i class="fas fa-check-double"></i></div>
                <div><div class="rdm-kpi__value">${fmt(s.total_matched)}</div><div class="rdm-kpi__label">Cocok (Pasti)<br><small>${matchPct}% dari RDM</small></div></div>
            </div>
        </div>
        <div class="col-6 col-xl mb-3">
            <div class="rdm-kpi" style="border:1px solid #fde68a; background:#fffbeb; box-shadow:0 8px 20px rgba(180,83,9,.07);">
                <div class="rdm-kpi__icon" style="background:#fef3c7; color:#b45309;"><i class="fas fa-magic"></i></div>
                <div><div class="rdm-kpi__value" style="color:#92400e;">${fmt(s.total_fuzzy)}</div><div class="rdm-kpi__label">Perlu Verifikasi Nama<br><small>nama mirip, belum pasti</small></div></div>
            </div>
        </div>
        <div class="col-6 col-xl mb-3">
            <div class="rdm-kpi rdm-kpi--red">
                <div class="rdm-kpi__icon"><i class="fas fa-exclamation-circle"></i></div>
                <div><div class="rdm-kpi__value">${fmt(s.total_rdm_only)}</div><div class="rdm-kpi__label">Belum Ada di SIMANSA<br><small>tidak ada kandidat</small></div></div>
            </div>
        </div>
        <div class="col-6 col-xl mb-3">
            <div class="rdm-kpi rdm-kpi--orange">
                <div class="rdm-kpi__icon"><i class="fas fa-user-slash"></i></div>
                <div><div class="rdm-kpi__value">${fmt(s.total_simansa_only)}</div><div class="rdm-kpi__label">Di SIMANSA, Tidak di RDM<br><small>belum diinput RDM</small></div></div>
            </div>
        </div>
    `);

    // Badge counts
    $('#badgeRdmOnly').text(s.total_rdm_only);
    $('#badgeFuzzy').text(s.total_fuzzy);
    $('#badgeSimansaOnly').text(s.total_simansa_only);
    $('#badgeMatched').text(s.total_matched);

    // Destroy existing DataTables
    if (dtRdmOnly)     { dtRdmOnly.destroy(); }
    if (dtFuzzy)       { dtFuzzy.destroy(); }
    if (dtSimansaOnly) { dtSimansaOnly.destroy(); }
    if (dtMatched)     { dtMatched.destroy(); }

    // ── Tab: RDM Only ──
    const rowsRdmOnly = data.rdm_only.map((r, i) => `
        <tr>
            <td class="text-center text-muted">${i+1}</td>
            <td><strong>${esc(r.rdm_nama)}</strong></td>
            <td>${esc(r.rdm_nis)}</td>
            <td>${esc(r.rdm_nisn)}</td>
            <td>${esc(r.rdm_tingkat)}</td>
            <td><span class="badge badge-secondary">${esc(r.rdm_kelas)}</span></td>
            <td>${r.rdm_gender === 'L' ? '<span class="text-primary">L</span>' : '<span class="text-danger">P</span>'}</td>
            <td>${esc(r.rdm_tgllahir || '-')}</td>
        </tr>
    `).join('');
    $('#bodyRdmOnly').html(rowsRdmOnly || '<tr><td colspan="8" class="text-center text-muted py-3">Tidak ada — semua siswa RDM sudah ada/terdeteksi di SIMANSA 🎉</td></tr>');

    // ── Tab: Fuzzy Candidates ──
    const rowsFuzzy = data.fuzzy_candidates.map((r, i) => {
        const chips = r.candidates.map(c => {
            const chipClass = c.score >= 88 ? 'fuzzy-chip--high' : (c.score >= 72 ? 'fuzzy-chip--medium' : 'fuzzy-chip--low');
            const icon = c.score >= 88 ? '✔️' : (c.score >= 72 ? '🔍' : '❓');
            return `<a class="fuzzy-chip ${chipClass}" href="/admin/siswa/${c.simansa_id}" target="_blank" title="Kelas: ${esc(c.simansa_kelas || '-')} | NISN: ${esc(c.simansa_nisn || '-')}">
                ${icon} ${esc(c.simansa_nama)}
                <span class="score-pct">${c.score}%</span>
            </a>`;
        }).join('');
        return `<tr>
            <td class="text-center text-muted">${i+1}</td>
            <td><strong>${esc(r.rdm_nama)}</strong><br><small class="text-muted">NIS: ${esc(r.rdm_nis)}</small></td>
            <td>${esc(r.rdm_nisn || '-')}</td>
            <td><span class="badge badge-secondary">${esc(r.rdm_tingkat)}</span> ${esc(r.rdm_kelas)}</td>
            <td>${chips}</td>
        </tr>`;
    }).join('');
    $('#bodyFuzzy').html(rowsFuzzy || '<tr><td colspan="5" class="text-center text-muted py-3">Tidak ada kandidat fuzzy.</td></tr>');

    // ── Tab: SIMANSA Only ──
    const rowsSimansaOnly = data.simansa_only.map((r, i) => `
        <tr>
            <td class="text-center text-muted">${i+1}</td>
            <td><strong>${esc(r.simansa_nama)}</strong></td>
            <td>${esc(r.simansa_nisn || '-')}</td>
            <td><span class="badge badge-secondary">${esc(r.simansa_kelas || '-')}</span></td>
            <td>${badgeDataLengkap(r.simansa_data_lengkap)}</td>
            <td class="text-center">
                <a href="/admin/siswa/${r.simansa_id}" class="btn btn-sm btn-primary" title="Detail Siswa" target="_blank">
                    <i class="fas fa-eye"></i>
                </a>
            </td>
        </tr>
    `).join('');
    $('#bodySimansaOnly').html(rowsSimansaOnly || '<tr><td colspan="7" class="text-center text-muted py-3">Tidak ada data.</td></tr>');

    // ── Tab: Matched ──
    const rowsMatched = data.matched.map((r, i) => `
        <tr>
            <td class="text-center text-muted">${i+1}</td>
            <td>${esc(r.rdm_nama)}</td>
            <td>${esc(r.simansa_nama)}</td>
            <td>${esc(r.rdm_nisn || '-')}</td>
            <td>${esc(r.rdm_nis || '-')}</td>
            <td><span class="badge badge-secondary">${esc(r.rdm_kelas)}</span></td>
            <td>${esc(r.simansa_kelas || '-')}</td>
            <td>${r.match_by === 'nisn' ? '<span class="badge-match-nisn">NISN</span>' : '<span class="badge-match-nis">NIS</span>'}</td>
            <td>${badgeDataLengkap(r.simansa_data_lengkap)}</td>
            <td class="text-center">
                <a href="/admin/siswa/${r.simansa_id}" class="btn btn-sm btn-primary" title="Detail Siswa" target="_blank">
                    <i class="fas fa-eye"></i>
                </a>
            </td>
        </tr>
    `).join('');
    $('#bodyMatched').html(rowsMatched || '<tr><td colspan="10" class="text-center text-muted py-3">Tidak ada data cocok.</td></tr>');

    // Init DataTables
    dtRdmOnly = $('#tableRdmOnly').DataTable({
        language: dtLang(),
        order: [[4,'asc'],[5,'asc']],
        pageLength: 25,
    });
    dtFuzzy = $('#tableFuzzy').DataTable({
        language: dtLang(),
        order: [[3,'asc']],
        pageLength: 25,
        columnDefs: [{ orderable: false, targets: 4 }],
    });
    dtSimansaOnly = $('#tableSimansaOnly').DataTable({
        language: dtLang(),
        order: [[3,'asc']],
        pageLength: 25,
        columnDefs: [{ orderable: false, targets: 5 }],
    });
    dtMatched = $('#tableMatched').DataTable({
        language: dtLang(),
        order: [[5,'asc']],
        pageLength: 25,
    });

    $('#resultArea').show();

    // Auto-switch ke tab Fuzzy jika ada hasil fuzzy dan rdm_only kosong
    if (data.stats.total_fuzzy > 0 && data.stats.total_rdm_only === 0) {
        $('[href="#tabFuzzy"]').tab('show');
    }

    // Scroll ke hasil
    $('html, body').animate({ scrollTop: $('#resultArea').offset().top - 80 }, 400);
}

function badgeDataLengkap(ok) {
    return ok
        ? '<span class="badge-data-ok"><i class="fas fa-check mr-1"></i>Lengkap</span>'
        : '<span class="badge-data-no"><i class="fas fa-times mr-1"></i>Belum</span>';
}

function esc(str) {
    if (!str && str !== 0) return '-';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function fmt(n) {
    return new Intl.NumberFormat('id-ID').format(n);
}

function dtLang() {
    return {
        search: 'Cari:',
        lengthMenu: 'Tampilkan _MENU_ data',
        info: '_START_–_END_ dari _TOTAL_ data',
        infoEmpty: '0 data',
        paginate: { previous: '‹', next: '›' },
        zeroRecords: 'Tidak ada data.',
    };
}
</script>
@endsection

@extends('adminlte::page')

@section('title', 'Data Sekolah Asal')

@section('content_header')
    <div class="simansa-page-hero">
        <div>
            <div class="simansa-page-hero__eyebrow">
                <i class="fas fa-school mr-1"></i> {{ $isWaliScope ? 'Kelas Saya' : 'Manajemen Peserta Didik' }}
            </div>
            <h1>{{ $isWaliScope ? 'Sekolah Asal Siswa Kelas Saya' : 'Data Sekolah Asal' }}</h1>
            <p>{{ $isWaliScope ? 'Lihat sekolah asal yang tercatat pada siswa di rombel aktif yang Anda ampu.' : 'Kelola referensi sekolah asal siswa, lengkapi wilayah, NSM, SK, akreditasi, dan kontak dari sumber resmi.' }}</p>
        </div>
        @if($canEnrich)
        <div class="simansa-page-hero__actions">
            <button type="button" class="btn btn-light" id="btnBulkEnrichSchools">
                <i class="fas fa-sync-alt mr-1"></i>Bulk Lengkapi
            </button>
        </div>
        @endif
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6 col-xl-3 mb-4">
            <div class="simansa-kpi simansa-kpi--blue">
                <div class="simansa-kpi__icon"><i class="fas fa-school"></i></div>
                <div>
                    <div class="simansa-kpi__label">Total Sekolah</div>
                    <div class="simansa-kpi__value">{{ number_format($stats['total']) }}</div>
                    <div class="simansa-kpi__desc">{{ $isWaliScope ? 'Sekolah asal siswa pada rombel Anda.' : 'Sekolah asal yang sudah tercatat.' }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3 mb-4">
            <div class="simansa-kpi simansa-kpi--green">
                <div class="simansa-kpi__icon"><i class="fas fa-check-circle"></i></div>
                <div>
                    <div class="simansa-kpi__label">Wilayah Lengkap</div>
                    <div class="simansa-kpi__value">{{ number_format($stats['lengkap']) }}</div>
                    <div class="simansa-kpi__desc">Alamat, kecamatan, kota, dan provinsi terisi.</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3 mb-4">
            <div class="simansa-kpi simansa-kpi--violet">
                <div class="simansa-kpi__icon"><i class="fas fa-university"></i></div>
                <div>
                    <div class="simansa-kpi__label">Punya NSM</div>
                    <div class="simansa-kpi__value">{{ number_format($stats['nsm']) }}</div>
                    <div class="simansa-kpi__desc">Madrasah yang sudah memiliki NSM.</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3 mb-4">
            <div class="simansa-kpi simansa-kpi--amber">
                <div class="simansa-kpi__icon"><i class="fas fa-clock"></i></div>
                <div>
                    <div class="simansa-kpi__label">Belum Dicek</div>
                    <div class="simansa-kpi__value">{{ number_format($stats['perlu_update']) }}</div>
                    <div class="simansa-kpi__desc">Belum pernah disinkronkan ulang.</div>
                </div>
            </div>
        </div>
    </div>

    <section class="simansa-section">
        <div class="simansa-section-head">
            <div>
                <h3>Daftar Sekolah Asal</h3>
                <p>{{ $isWaliScope ? 'Daftar ini hanya memuat sekolah asal siswa pada rombel aktif Anda dan bersifat hanya-baca.' : 'Gunakan tombol sync untuk melengkapi data dari Referensi Kemendikdasmen dan EMIS Kemenag.' }}</p>
            </div>
            <div class="simansa-section-actions">
                <button type="button" class="btn btn-sm btn-outline-primary" id="btnReloadTable">
                    <i class="fas fa-redo mr-1"></i>Refresh
                </button>
            </div>
        </div>
        <div class="table-responsive simansa-table-shell">
            <table id="tableSekolah" class="table table-hover simansa-table w-100">
                <thead>
                    <tr>
                        <th width="48">#</th>
                        <th>Sekolah</th>
                        <th width="115">Status</th>
                        <th width="120">Bentuk</th>
                        <th width="220">Wilayah</th>
                        <th width="125">Kelengkapan</th>
                        <th width="105" class="text-right">Siswa</th>
                        <th width="95" class="text-right">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>

    @if($canEnrich)
    <div id="schoolEnrichOverlay" class="simansa-progress-overlay" aria-hidden="true">
        <div class="simansa-progress-modal">
            <div class="simansa-progress-modal__head">
                <div>
                    <div class="simansa-progress-eyebrow"><i class="fas fa-database mr-1"></i>Bulk Lengkapi Sekolah</div>
                    <h3>Melengkapi Data Sekolah</h3>
                    <p>Proses berjalan satu per satu agar sumber data tetap stabil.</p>
                </div>
                <button type="button" class="btn btn-sm btn-light" id="btnCloseSchoolOverlay" disabled>
                    <i class="fas fa-times mr-1"></i>Tutup
                </button>
            </div>
            <div class="simansa-progress-summary">
                <div>
                    <span id="schoolProgressText">Menyiapkan proses...</span>
                    <strong id="schoolProgressCounter">0/0</strong>
                </div>
                <div class="progress simansa-progress-bar">
                    <div id="schoolProgressBar" class="progress-bar bg-primary" role="progressbar" style="width: 0%"></div>
                </div>
            </div>
            <div class="simansa-progress-log" id="schoolProgressLog"></div>
        </div>
    </div>
    @endif
@stop

@section('css')
    <style>
        .content-wrapper {
            background: #f3f7fc;
        }

        .simansa-page-hero {
            align-items: center;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 56%, #168891 100%);
            border-radius: 16px;
            box-shadow: 0 16px 34px rgba(37, 99, 235, 0.2);
            color: #fff;
            display: flex;
            justify-content: space-between;
            min-height: 118px;
            padding: 22px 24px;
        }

        .simansa-page-hero__eyebrow {
            font-size: 0.82rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .simansa-page-hero h1 {
            color: #fff;
            font-size: 1.45rem;
            font-weight: 800;
            margin: 10px 0 6px;
        }

        .simansa-page-hero p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.98rem;
            margin: 0;
            max-width: 760px;
        }

        .simansa-page-hero__actions {
            flex: 0 0 auto;
        }

        .simansa-kpi {
            align-items: flex-start;
            background: #fff;
            border: 1px solid #dbe7f5;
            border-radius: 14px;
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.06);
            display: flex;
            gap: 14px;
            min-height: 128px;
            padding: 18px;
        }

        .simansa-kpi__icon {
            align-items: center;
            border-radius: 14px;
            color: #fff;
            display: flex;
            flex: 0 0 48px;
            font-size: 1.15rem;
            height: 48px;
            justify-content: center;
        }

        .simansa-kpi--blue .simansa-kpi__icon { background: #2563eb; }
        .simansa-kpi--green .simansa-kpi__icon { background: #16a34a; }
        .simansa-kpi--violet .simansa-kpi__icon { background: #5b45e5; }
        .simansa-kpi--amber .simansa-kpi__icon { background: #f59e0b; }

        .simansa-kpi__label {
            color: #64748b;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .simansa-kpi__value {
            color: #0f172a;
            font-size: 1.7rem;
            font-weight: 850;
            line-height: 1.1;
            margin-top: 4px;
        }

        .simansa-kpi__desc {
            color: #64748b;
            font-size: 0.86rem;
            line-height: 1.35;
            margin-top: 8px;
        }

        .simansa-section {
            background: #fff;
            border: 1px solid #dbe7f5;
            border-radius: 16px;
            box-shadow: 0 16px 32px rgba(15, 23, 42, 0.06);
            overflow: hidden;
        }

        .simansa-section-head {
            align-items: center;
            border-bottom: 1px solid #e5edf7;
            display: flex;
            justify-content: space-between;
            padding: 18px 20px;
        }

        .simansa-section-head h3 {
            color: #0f172a;
            font-size: 1.08rem;
            font-weight: 800;
            margin: 0 0 4px;
        }

        .simansa-section-head p {
            color: #64748b;
            margin: 0;
        }

        .simansa-table-shell {
            padding: 16px 20px 20px;
            position: relative;
        }

        .simansa-table-shell.simansa-action-dropdown-open,
        .simansa-table-shell.simansa-action-dropdown-open .dataTables_scrollBody {
            overflow: visible !important;
        }

        .simansa-table thead th {
            background: #f8fafc;
            border-bottom: 1px solid #dbe7f5;
            color: #64748b;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .simansa-table td {
            border-color: #edf2f7;
            color: #0f172a;
            vertical-align: middle;
        }

        .school-name {
            color: #0f172a;
            font-weight: 850;
            line-height: 1.3;
        }

        .school-meta {
            color: #64748b;
            font-size: 0.84rem;
            margin-top: 3px;
        }

        .school-meta span {
            color: #94a3b8;
            margin: 0 4px;
        }

        .btn {
            border-radius: 8px;
            font-weight: 700;
        }

        .simansa-school-action-menu .simansa-school-action-toggle {
            min-width: 78px;
            border-color: #60a5fa;
            border-radius: 7px;
            background: #fff;
            color: #1d4ed8;
            font-size: .72rem;
            font-weight: 800;
        }

        .simansa-school-action-menu.show .simansa-school-action-toggle,
        .simansa-school-action-menu .simansa-school-action-toggle:hover,
        .simansa-school-action-menu .simansa-school-action-toggle:focus {
            border-color: #2563eb;
            background: #eff6ff;
            color: #1d4ed8;
            box-shadow: 0 0 0 .15rem rgba(37, 99, 235, .12);
        }

        .simansa-school-action-dropdown {
            z-index: 1060;
            min-width: 190px;
            padding: .35rem;
            border: 1px solid #dbe4f0;
            border-radius: 9px;
            box-shadow: 0 12px 28px rgba(15, 23, 42, .16);
        }

        .simansa-school-action-item {
            align-items: center;
            display: flex;
            gap: .6rem;
            padding: .5rem .6rem;
            border: 0;
            border-radius: 6px;
            background: transparent;
            color: #334155;
            font-size: .75rem;
            font-weight: 700;
        }

        .simansa-school-action-item > i {
            width: 17px;
            text-align: center;
        }

        .simansa-school-action-item:hover,
        .simansa-school-action-item:focus {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .simansa-progress-overlay {
            align-items: center;
            background: rgba(15, 23, 42, 0.56);
            bottom: 0;
            display: none;
            justify-content: center;
            left: 0;
            padding: 24px;
            position: fixed;
            right: 0;
            top: 0;
            z-index: 2050;
        }

        .simansa-progress-overlay.is-active {
            display: flex;
        }

        .simansa-progress-modal {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.28);
            max-width: 760px;
            overflow: hidden;
            width: min(760px, 96vw);
        }

        .simansa-progress-modal__head {
            align-items: flex-start;
            background: #f8fafc;
            border-bottom: 1px solid #e5edf7;
            display: flex;
            justify-content: space-between;
            padding: 20px;
        }

        .simansa-progress-eyebrow {
            color: #2563eb;
            font-size: 0.8rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .simansa-progress-modal h3 {
            color: #0f172a;
            font-size: 1.2rem;
            font-weight: 850;
            margin: 7px 0 4px;
        }

        .simansa-progress-modal p {
            color: #64748b;
            margin: 0;
        }

        .simansa-progress-summary {
            padding: 18px 20px 10px;
        }

        .simansa-progress-summary > div:first-child {
            color: #334155;
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .simansa-progress-bar {
            border-radius: 999px;
            height: 10px;
            overflow: hidden;
        }

        .simansa-progress-log {
            max-height: 280px;
            overflow: auto;
            padding: 10px 20px 20px;
        }

        .simansa-progress-log__row {
            border: 1px solid #e5edf7;
            border-radius: 10px;
            margin-bottom: 8px;
            padding: 10px 12px;
        }

        .simansa-progress-log__row strong {
            color: #0f172a;
            display: block;
        }

        .simansa-progress-log__row span {
            color: #64748b;
            font-size: 0.88rem;
        }

        .simansa-progress-log__row.is-success { border-color: #bbf7d0; background: #f0fdf4; }
        .simansa-progress-log__row.is-danger { border-color: #fecaca; background: #fef2f2; }
        .simansa-progress-log__row.is-warning { border-color: #fde68a; background: #fffbeb; }
        .simansa-progress-log__row.is-info { border-color: #bfdbfe; background: #eff6ff; }

        @media (max-width: 768px) {
            .simansa-page-hero,
            .simansa-section-head {
                align-items: stretch;
                flex-direction: column;
                gap: 14px;
            }
        }
    </style>
@stop

@section('js')
<script>
$(document).ready(function() {
    const csrf = '{{ csrf_token() }}';
    const $schoolTable = $('#tableSekolah');
    const $schoolTableShell = $('.simansa-table-shell');
    const table = $('#tableSekolah').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('admin.sekolah-asal.index') }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'identity', name: 'identity' },
            { data: 'status_badge', name: 'status', orderable: false },
            { data: 'bentuk_pendidikan', name: 'bentuk_pendidikan', defaultContent: '-' },
            { data: 'wilayah', name: 'kabupaten_kota' },
            { data: 'kelengkapan_badge', name: 'last_fetched_at', searchable: false },
            { data: 'siswa_count_badge', name: 'siswa_count', searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[6, 'desc']],
        language: {
            processing: "Sedang memproses...",
            lengthMenu: "Tampilkan _MENU_ data",
            zeroRecords: "Tidak ditemukan data yang sesuai",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            infoFiltered: "(disaring dari _MAX_ total data)",
            search: "Cari:",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            }
        },
        responsive: true,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        pageLength: 25,
        drawCallback() {
            positionSchoolActionMenus();
        }
    });

    function closeSchoolActionMenus() {
        $schoolTable.find('.simansa-school-action-menu').removeClass('show')
            .find('.simansa-school-action-dropdown').removeClass('show');
        $schoolTable.find('.simansa-school-action-toggle').attr('aria-expanded', 'false');
        $schoolTableShell.removeClass('simansa-action-dropdown-open');
    }

    function positionSchoolActionMenus() {
        const $rows = $schoolTable.find('tbody tr');
        $rows.find('.simansa-school-action-menu').removeClass('dropup');
        $rows.slice(-3).find('.simansa-school-action-menu').addClass('dropup');
    }

    $schoolTable.on('click', '.simansa-school-action-toggle', function(event) {
        event.preventDefault();
        event.stopPropagation();

        const $menu = $(this).closest('.simansa-school-action-menu');
        const willOpen = !$menu.hasClass('show');
        closeSchoolActionMenus();
        if (willOpen) {
            $menu.addClass('show').find('.simansa-school-action-dropdown').addClass('show');
            $(this).attr('aria-expanded', 'true');
            $schoolTableShell.addClass('simansa-action-dropdown-open');
        }
    });

    $(document).on('click.schoolActions', function(event) {
        if (!$(event.target).closest('.simansa-school-action-menu').length) {
            closeSchoolActionMenus();
        }
    });

    $('#btnReloadTable').on('click', function() {
        table.ajax.reload(null, false);
    });

    function setProgress(done, total, text) {
        const percent = total > 0 ? Math.round((done / total) * 100) : 0;
        $('#schoolProgressText').text(text);
        $('#schoolProgressCounter').text(`${done}/${total}`);
        $('#schoolProgressBar').css('width', `${percent}%`).attr('aria-valuenow', percent);
    }

    function appendLog(type, title, message) {
        $('#schoolProgressLog').prepend(`
            <div class="simansa-progress-log__row is-${type}">
                <strong>${title}</strong>
                <span>${message}</span>
            </div>
        `);
    }

    function runEnrich(button, options = {}) {
        const url = button.data('url');
        const school = button.data('school') || 'Sekolah';
        const npsn = button.data('npsn') || '-';
        const originalHtml = button.html();

        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        return $.ajax({
            url,
            method: 'POST',
            data: {_token: csrf}
        }).done(function(response) {
            const complete = Boolean(response.complete);
            const warningText = (response.warnings || []).filter(Boolean).join(' ');
            button.removeClass('btn-primary btn-success btn-warning');

            if (complete) {
                button.addClass('btn-success').html('<i class="fas fa-check"></i>');
            } else {
                button.prop('disabled', false).addClass('btn-warning').html('<i class="fas fa-exclamation-triangle"></i>');
            }

            if (options.log) {
                const sources = (response.sources || []).join(' + ') || 'sumber resmi';
                const logMessage = complete
                    ? `Berhasil dilengkapi dari ${sources}.`
                    : `${response.message || 'Data baru terisi sebagian.'}${warningText ? ` ${warningText}` : ''}`;
                appendLog(complete ? 'success' : 'warning', `${school} (${npsn})`, logMessage);
            }

            if (!options.silent) {
                Swal.fire({
                    icon: complete ? 'success' : 'warning',
                    title: complete ? 'Data sekolah diperbarui' : 'Data baru terisi sebagian',
                    text: `${response.message || `${school} berhasil dilengkapi.`}${warningText ? ` ${warningText}` : ''}`,
                    confirmButtonText: 'OK'
                }).then(() => table.ajax.reload(null, false));
            }
        }).fail(function(xhr) {
            const message = xhr.responseJSON?.message || 'Data sekolah belum berhasil dilengkapi.';
            button.prop('disabled', false).removeClass('btn-success').addClass('btn-primary').html(originalHtml);

            if (options.log) {
                appendLog('danger', `${school} (${npsn})`, message);
            }

            if (!options.silent) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Belum berhasil',
                    text: message,
                    confirmButtonText: 'OK'
                });
            }

            throw xhr;
        });
    }

    $schoolTable.on('click', '.btn-enrich-school', function(event) {
        event.preventDefault();
        closeSchoolActionMenus();
        runEnrich($(this));
    });

    $('#btnCloseSchoolOverlay').on('click', function() {
        $('#schoolEnrichOverlay').removeClass('is-active').attr('aria-hidden', 'true');
        table.ajax.reload(null, false);
    });

    $('#btnBulkEnrichSchools').on('click', async function() {
        const buttons = $('.btn-enrich-school:visible').toArray().map((el) => $(el));
        const total = buttons.length;

        if (total === 0) {
            Swal.fire({
                icon: 'info',
                title: 'Tidak ada data',
                text: 'Tidak ada sekolah pada halaman tabel saat ini.',
                confirmButtonText: 'OK'
            });
            return;
        }

        $('#schoolProgressLog').empty();
        $('#schoolEnrichOverlay').addClass('is-active').attr('aria-hidden', 'false');
        $('#btnCloseSchoolOverlay').prop('disabled', true);
        $('#btnBulkEnrichSchools').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Bulk Lengkapi');
        setProgress(0, total, 'Menyiapkan pengecekan data sekolah...');

        let success = 0;
        let failed = 0;

        for (let i = 0; i < total; i++) {
            const button = buttons[i];
            const school = button.data('school') || 'Sekolah';
            const npsn = button.data('npsn') || '-';

            setProgress(i, total, `Melengkapi ${school} (${npsn})...`);
            appendLog('info', `${school} (${npsn})`, 'Mengambil data dari Referensi Kemendikdasmen dan EMIS jika madrasah.');

            try {
                await runEnrich(button, {silent: true, log: true});
                success++;
            } catch (error) {
                failed++;
            }

            setProgress(i + 1, total, `${i + 1} dari ${total} sekolah selesai.`);
        }

        $('#btnCloseSchoolOverlay').prop('disabled', false);
        $('#btnBulkEnrichSchools').prop('disabled', false).html('<i class="fas fa-sync-alt mr-1"></i>Bulk Lengkapi');
        setProgress(total, total, `Selesai: ${success} berhasil, ${failed} gagal.`);
    });
});
</script>
@stop

@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)

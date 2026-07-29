@extends('adminlte::page')

@section('title', 'Data GTK - SIMANSA')


@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-chalkboard-teacher text-primary"></i> Data GTK</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Data GTK</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="simansa-gtk-management">
<div class="card bg-gradient-primary text-white mb-4 simansa-gtk-hero">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h3 class="mb-1"><i class="fas fa-address-card mr-1"></i> Manajemen Guru & Tenaga Kependidikan</h3>
                <p class="mb-0 text-white-75">
                    Kelola identitas GTK, pantau kelengkapan data, dan jalankan sinkronisasi Kemenag dari satu halaman operasional.
                </p>
            </div>
            <div class="col-lg-4 mt-3 mt-lg-0">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="text-white-75 small text-uppercase font-weight-bold">Total GTK</div>
                        <h3 class="mb-0 text-white">{{ number_format($stats['total_gtk']) }}</h3>
                    </div>
                    <div class="col-6">
                        <div class="text-white-75 small text-uppercase font-weight-bold">Siap Sinkron</div>
                        <h3 class="mb-0 text-white">{{ number_format($stats['gtk_with_nip']) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card card-outline card-primary h-100 simansa-gtk-stat">
            <div class="card-body">
                <div class="text-muted small text-uppercase font-weight-bold">Total GTK</div>
                <h3 class="text-primary mb-1">{{ number_format($stats['total_gtk']) }}</h3>
                <div class="text-muted">Semua guru dan tenaga kependidikan yang tercatat di SIMANSA.</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card card-outline card-info h-100 simansa-gtk-stat">
            <div class="card-body">
                <div class="text-muted small text-uppercase font-weight-bold">Laki-Laki</div>
                <h3 class="text-info mb-1">{{ number_format($stats['laki_laki']) }}</h3>
                <div class="text-muted">Jumlah GTK laki-laki untuk monitoring personalia.</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card card-outline card-danger h-100 simansa-gtk-stat">
            <div class="card-body">
                <div class="text-muted small text-uppercase font-weight-bold">Perempuan</div>
                <h3 class="text-danger mb-1">{{ number_format($stats['perempuan']) }}</h3>
                <div class="text-muted">Jumlah GTK perempuan sesuai data aktif yang tersimpan.</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card card-outline card-success h-100 simansa-gtk-stat">
            <div class="card-body">
                <div class="text-muted small text-uppercase font-weight-bold">Data Lengkap</div>
                <h3 class="text-success mb-1">{{ number_format($stats['data_lengkap']) }}</h3>
                <div class="text-muted">GTK dengan data pribadi dan kepegawaian yang sudah lengkap.</div>
            </div>
        </div>
    </div>
</div>

<div class="card card-outline card-primary simansa-gtk-card">
    <div class="card-header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between">
        <h3 class="card-title mb-3 mb-lg-0">
            <i class="fas fa-list mr-2"></i>
            Daftar GTK
        </h3>
        <div class="card-tools ml-0 simansa-gtk-actions">
            @can('edit-gtk')
                <button type="button" class="btn btn-warning btn-sm mr-1" id="btnBulkSyncKemenag">
                    <i class="fas fa-sync-alt"></i> Sinkron Semua GTK Ber-NIP
                    <span class="badge badge-light ml-1">{{ $stats['gtk_with_nip'] }}</span>
                </button>
            @endcan
            @can('create-gtk')
                <a href="{{ route('admin.gtk.import') }}" class="btn btn-success btn-sm mr-1">
                    <i class="fas fa-file-excel"></i> Import GTK
                </a>
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addGtkModal">
                    <i class="fas fa-plus"></i> Tambah GTK
                </button>
            @endcan
        </div>
        </div>
    </div>
    <div class="card-body">
        {{-- Filter Section --}}
        <div class="simansa-filter-panel simansa-gtk-filter">
            <form id="filterForm">
                <div class="row">
                            <div class="col-md-6 col-xl-3 mb-3">
                                <label for="filterKategoriPtk" class="simansa-filter-label">
                                    <i class="fas fa-users mr-1"></i> Kategori PTK
                                </label>
                                <select id="filterKategoriPtk" class="form-control form-control-sm">
                                    <option value="">Semua</option>
                                    <option value="Pendidik">Pendidik (Guru)</option>
                                    <option value="Tenaga Kependidikan">Tenaga Kependidikan</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-xl-3 mb-3">
                                <label for="filterJenisPtk" class="simansa-filter-label">
                                    <i class="fas fa-user-tag mr-1"></i> Jenis PTK
                                </label>
                                <select id="filterJenisPtk" class="form-control form-control-sm">
                                    <option value="">Semua</option>
                                    <option value="Guru Mapel">Guru Mapel</option>
                                    <option value="Guru BK">Guru BK</option>
                                    <option value="Kepala TU">Kepala TU</option>
                                    <option value="Staff TU">Staff TU</option>
                                    <option value="Bendahara">Bendahara</option>
                                    <option value="Laboran">Laboran</option>
                                    <option value="Pustakawan">Pustakawan</option>
                                    <option value="Cleaning Service">Cleaning Service</option>
                                    <option value="Satpam">Satpam</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-xl-2 mb-3">
                                <label for="filterJenisKelamin" class="simansa-filter-label">
                                    <i class="fas fa-venus-mars mr-1"></i> Jenis Kelamin
                                </label>
                                <select id="filterJenisKelamin" class="form-control form-control-sm">
                                    <option value="">Semua</option>
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-xl-2 mb-3">
                                <label for="filterStatusKepegawaian" class="simansa-filter-label">
                                    <i class="fas fa-briefcase mr-1"></i> Status Kepeg
                                </label>
                                <select id="filterStatusKepegawaian" class="form-control form-control-sm">
                                    <option value="">Semua</option>
                                    @foreach($statusKepegawaianOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-xl-2 mb-3">
                                <label for="filterStatus" class="simansa-filter-label">
                                    <i class="fas fa-database mr-1"></i> Status Data
                                </label>
                                <select id="filterStatus" class="form-control form-control-sm">
                                    <option value="">Semua</option>
                                    <option value="lengkap">Data Lengkap</option>
                                    <option value="belum">Belum Lengkap</option>
                                </select>
                            </div>
                </div>
                <div class="simansa-gtk-filter-footer">
                    <span id="gtkFilterStatus" class="simansa-gtk-filter-status" aria-live="polite">
                        <i class="fas fa-bolt mr-1"></i> Filter memuat data secara otomatis
                    </span>
                    <button type="button" id="btnResetFilter" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-redo"></i> Reset Filter
                    </button>
                </div>
            </form>
        </div>

        <p class="simansa-gtk-table-note">
            Gunakan filter untuk memantau komposisi GTK, kelengkapan data, dan kesiapan sinkronisasi Kemenag tanpa meninggalkan halaman ini.
        </p>

        <div class="table-responsive simansa-gtk-table-wrap">
            <table id="gtk-table" class="table table-hover table-sm simansa-gtk-table">
                <thead>
                    <tr>
                        <th style="width: 40px;">No</th>
                        <th>Nama / Identitas GTK</th>
                        <th>Status Diri</th>
                        <th>Data Kepeg</th>
                        <th style="width: 150px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<div id="bulkSyncOverlay" class="bulk-sync-overlay d-none">
    <div class="bulk-sync-panel">
        <div class="bulk-sync-header">
            <div>
                <div class="bulk-sync-eyebrow">Sinkronisasi GTK Kemenag</div>
                <h4 class="bulk-sync-title mb-0">Memproses data GTK ber-NIP</h4>
            </div>
            <div class="bulk-sync-spinner">
                <i class="fas fa-sync-alt fa-spin"></i>
            </div>
        </div>

        <div class="bulk-sync-meta">
            <div class="bulk-sync-stat">
                <span class="bulk-sync-stat-label">Progress</span>
                <span class="bulk-sync-stat-value" id="bulkSyncProgressText">0 / 0</span>
            </div>
            <div class="bulk-sync-stat">
                <span class="bulk-sync-stat-label">Berhasil</span>
                <span class="bulk-sync-stat-value text-success" id="bulkSyncSuccessCount">0</span>
            </div>
            <div class="bulk-sync-stat">
                <span class="bulk-sync-stat-label">Perubahan</span>
                <span class="bulk-sync-stat-value text-info" id="bulkSyncChangedCount">0</span>
            </div>
            <div class="bulk-sync-stat">
                <span class="bulk-sync-stat-label">Gagal</span>
                <span class="bulk-sync-stat-value text-danger" id="bulkSyncFailedCount">0</span>
            </div>
        </div>

        <div class="bulk-sync-progress-wrap">
            <div class="progress bulk-sync-progress">
                <div id="bulkSyncProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-warning" style="width: 0%">0%</div>
            </div>
            <div class="bulk-sync-note" id="bulkSyncCurrentLabel">Menyiapkan sinkronisasi...</div>
        </div>

        <div class="bulk-sync-log card">
            <div class="card-header py-2">
                <strong><i class="fas fa-stream mr-1"></i> Aktivitas Terakhir</strong>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush" id="bulkSyncLogList">
                    <li class="list-group-item text-muted">Belum ada proses yang berjalan.</li>
                </ul>
            </div>
        </div>

        <div class="bulk-sync-footer">
            <div class="text-muted small">
                Sinkronisasi massal ini hanya mengambil dan menyimpan hasil perbandingan dari Kemenag. Data lokal tidak diubah otomatis.
            </div>
        </div>
    </div>
</div>

{{-- Modal Add GTK --}}
<div class="modal fade" id="addGtkModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title">
                    <i class="fas fa-plus"></i> Tambah GTK Baru
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="addGtkForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Informasi:</strong> Username akan dibuat otomatis dari NIK. Password default adalah NIK.
                    </div>
                    
                    <div class="form-group">
                        <label for="nama_lengkap">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
                        <span class="invalid-feedback d-block" id="error-nama_lengkap"></span>
                    </div>

                    <div class="form-group">
                        <label for="nik">NIK (16 digit) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nik" name="nik" maxlength="16" required>
                        <small class="form-text text-muted">NIK akan digunakan sebagai username dan password default</small>
                        <span class="invalid-feedback d-block" id="error-nik"></span>
                    </div>

                    <div class="form-group">
                        <label for="jenis_kelamin">Jenis Kelamin <span class="text-danger">*</span></label>
                        <select class="form-control" id="jenis_kelamin" name="jenis_kelamin" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                        <span class="invalid-feedback d-block" id="error-jenis_kelamin"></span>
                    </div>

                    <div class="form-group">
                        <label for="kategori_ptk">Kategori PTK <span class="text-danger">*</span></label>
                        <select class="form-control" id="kategori_ptk" name="kategori_ptk" required>
                            <option value="">Pilih Kategori PTK</option>
                            <option value="Pendidik">Pendidik (Guru)</option>
                            <option value="Tenaga Kependidikan">Tenaga Kependidikan (Staff TU, dll)</option>
                        </select>
                        <small class="form-text text-muted">Kategori PTK: Pendidik untuk Guru, Tenaga Kependidikan untuk Staff non-Guru</small>
                        <span class="invalid-feedback d-block" id="error-kategori_ptk"></span>
                    </div>

                    <div class="form-group">
                        <label for="jenis_ptk">Jenis PTK <span class="text-danger">*</span></label>
                        <select class="form-control" id="jenis_ptk" name="jenis_ptk" required disabled>
                            <option value="">Pilih Kategori PTK terlebih dahulu</option>
                        </select>
                        <small class="form-text text-muted">Jenis PTK akan muncul setelah memilih Kategori PTK</small>
                        <span class="invalid-feedback d-block" id="error-jenis_ptk"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal View GTK --}}
<div class="modal fade" id="viewGtkModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title">
                    <i class="fas fa-eye"></i> Detail GTK
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="viewGtkContent">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-3x"></i>
                    <p>Loading...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

</div>
@stop


@section('js')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
const BULK_SYNC_DELAY_MS = 350;

$(document).ready(function() {
    const $gtkTableElement = $('#gtk-table');
    const $gtkTableWrap = $('.simansa-gtk-table-wrap');
    const $gtkFilterStatus = $('#gtkFilterStatus');
    let filterReloadTimer = null;

    $gtkTableElement
        .on('preXhr.dt', function() {
            $gtkTableWrap.addClass('is-loading');
            $gtkFilterStatus
                .addClass('is-loading')
                .html('<i class="fas fa-circle-notch fa-spin mr-1"></i> Memuat data GTK...');
        })
        .on('xhr.dt', function() {
            $gtkTableWrap.removeClass('is-loading');
            $gtkFilterStatus
                .removeClass('is-loading')
                .html('<i class="fas fa-check-circle mr-1"></i> Data GTK sudah diperbarui');
        })
        .on('error.dt', function() {
            $gtkTableWrap.removeClass('is-loading');
            $gtkFilterStatus
                .removeClass('is-loading')
                .html('<i class="fas fa-exclamation-circle mr-1"></i> Data gagal dimuat');
        });

    let gtkTable = $('#gtk-table').DataTable({
        processing: true,
        serverSide: true,
        deferRender: true,
        searchDelay: 350,
        ajax: {
            url: '{{ route('admin.gtk.data') }}',
            data: function(d) {
                d.kategori_ptk = $('#filterKategoriPtk').val();
                d.jenis_ptk = $('#filterJenisPtk').val();
                d.jenis_kelamin = $('#filterJenisKelamin').val();
                d.status_kepegawaian = $('#filterStatusKepegawaian').val();
                d.status = $('#filterStatus').val();
            }
        },
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
        pageLength: 10,
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'identity', name: 'nama_lengkap' },
            { data: 'status_diri', name: 'status_diri', orderable: false, searchable: false },
            { data: 'status_kepeg', name: 'status_kepeg', orderable: false, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
        ],
        language: {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>',
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 - 0 dari 0 data",
            zeroRecords: "Data tidak ditemukan",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            }
        }
    });

    const reloadGtkTable = function(resetPaging = true) {
        window.clearTimeout(filterReloadTimer);
        $gtkFilterStatus
            .addClass('is-loading')
            .html('<i class="fas fa-circle-notch fa-spin mr-1"></i> Menyiapkan filter...');

        filterReloadTimer = window.setTimeout(function() {
            gtkTable.ajax.reload(null, resetPaging);
        }, 140);
    };

    // Filter functionality - Cascading Kategori PTK -> Jenis PTK
    const filterJenisPtkOptions = {
        'Pendidik': [
            { value: 'Guru Mapel', text: 'Guru Mapel' },
            { value: 'Guru BK', text: 'Guru BK' }
        ],
        'Tenaga Kependidikan': [
            { value: 'Kepala TU', text: 'Kepala TU' },
            { value: 'Staff TU', text: 'Staff TU' },
            { value: 'Bendahara', text: 'Bendahara' },
            { value: 'Laboran', text: 'Laboran' },
            { value: 'Pustakawan', text: 'Pustakawan' },
            { value: 'Cleaning Service', text: 'Cleaning Service' },
            { value: 'Satpam', text: 'Satpam' },
            { value: 'Lainnya', text: 'Lainnya' }
        ]
    };

    $('#filterKategoriPtk').on('change', function() {
        const kategori = $(this).val();
        const filterJenisPtk = $('#filterJenisPtk');
        // Reset jenis_ptk filter
        filterJenisPtk.empty();
        filterJenisPtk.append('<option value="">Semua</option>');
        
        if (kategori && filterJenisPtkOptions[kategori]) {
            filterJenisPtkOptions[kategori].forEach(function(option) {
                filterJenisPtk.append(`<option value="${option.value}">${option.text}</option>`);
            });
        } else {
            // If no kategori selected, show all jenis options
            Object.values(filterJenisPtkOptions).flat().forEach(function(option) {
                filterJenisPtk.append(`<option value="${option.value}">${option.text}</option>`);
            });
        }
        
        reloadGtkTable();
    });

    $('#filterJenisPtk, #filterJenisKelamin, #filterStatusKepegawaian, #filterStatus').on('change', function() {
        reloadGtkTable();
    });

    $('#btnResetFilter').on('click', function() {
        $('#filterKategoriPtk').val('');
        $('#filterJenisPtk').empty().append('<option value="">Semua</option>');
        // Repopulate all jenis options
        Object.values(filterJenisPtkOptions).flat().forEach(function(option) {
            $('#filterJenisPtk').append(`<option value="${option.value}">${option.text}</option>`);
        });
        $('#filterJenisKelamin').val('');
        $('#filterStatusKepegawaian').val('');
        $('#filterStatus').val('');
        reloadGtkTable();
    });

    $('#btnBulkSyncKemenag').on('click', async function() {
        const $button = $(this);

        try {
            $button.prop('disabled', true);

            const candidateResponse = await $.ajax({
                url: '{{ route('admin.gtk.sync-kemenag-candidates') }}',
                type: 'GET'
            });

            if (!candidateResponse.success || !candidateResponse.total) {
                Swal.fire({
                    icon: 'info',
                    title: 'Tidak Ada Data',
                    text: candidateResponse.message || 'Belum ada GTK ber-NIP yang bisa disinkronkan.'
                });
                return;
            }

            const candidates = candidateResponse.candidates || [];
            const previewNames = candidates.slice(0, 5).map(item => `<li>${escapeHtml(item.nama_lengkap)} <span class="text-muted">(${escapeHtml(item.nip)})</span></li>`).join('');
            const moreText = candidates.length > 5
                ? `<div class="mt-2 text-muted small">Dan ${candidates.length - 5} GTK lainnya akan diproses satu per satu.</div>`
                : '';

            const confirmResult = await Swal.fire({
                title: 'Sinkron Semua GTK Ber-NIP?',
                html: `
                    <div class="text-left">
                        <p class="mb-2">Sistem akan menyinkronkan <strong>${candidateResponse.total}</strong> GTK yang sudah memiliki NIP.</p>
                        <div class="alert alert-info py-2 px-3 mb-2">
                            <i class="fas fa-shield-alt mr-1"></i>
                            Mode aman aktif: proses ini hanya mengambil dan menyimpan hasil perbandingan dari Kemenag. Data lokal tidak akan diubah otomatis.
                        </div>
                        <div class="small text-muted mb-2">Contoh data yang akan diproses:</div>
                        <ul class="pl-3 mb-0">${previewNames}</ul>
                        ${moreText}
                    </div>
                `,
                icon: 'question',
                width: 680,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-sync-alt"></i> Ya, Mulai Sinkronisasi',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#f59e0b',
                cancelButtonColor: '#6b7280'
            });

            if (!confirmResult.isConfirmed) {
                return;
            }

            await runBulkSyncKemenag(candidates, gtkTable);
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal Menyiapkan Sinkronisasi',
                text: error?.responseJSON?.message || error?.message || 'Terjadi kesalahan saat menyiapkan data sinkronisasi.'
            });
        } finally {
            $button.prop('disabled', false);
        }
    });

    // Add GTK Form Submit
    $('#addGtkForm').on('submit', function(e) {
        e.preventDefault();
        
        // Clear previous errors
        $('.invalid-feedback').text('');
        
        $.ajax({
            url: '{{ route('admin.gtk.store') }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#addGtkModal').modal('hide');
                $('#addGtkForm')[0].reset();
                
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.message,
                    timer: 3000
                });
                
                gtkTable.ajax.reload();
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    for (let field in errors) {
                        $('#error-' + field).text(errors[field][0]);
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                    });
                }
            }
        });
    });

    // NIK input validation (only numbers, max 16)
    $('#nik').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '').substring(0, 16);
    });

    // Cascading Dropdown: Kategori PTK â†’ Jenis PTK
    const jenisPtkOptions = {
        'Pendidik': [
            { value: 'Guru Mapel', text: 'Guru Mata Pelajaran' },
            { value: 'Guru BK', text: 'Guru BK (Bimbingan Konseling)' }
        ],
        'Tenaga Kependidikan': [
            { value: 'Kepala TU', text: 'Kepala Tata Usaha' },
            { value: 'Staff TU', text: 'Staff Tata Usaha' },
            { value: 'Bendahara', text: 'Bendahara' },
            { value: 'Laboran', text: 'Laboran' },
            { value: 'Pustakawan', text: 'Pustakawan' },
            { value: 'Cleaning Service', text: 'Cleaning Service' },
            { value: 'Satpam', text: 'Satpam' },
            { value: 'Lainnya', text: 'Lainnya' }
        ]
    };

    $('#kategori_ptk').on('change', function() {
        const kategori = $(this).val();
        const jenisPtkSelect = $('#jenis_ptk');
        
        jenisPtkSelect.empty();
        jenisPtkSelect.prop('disabled', true);
        
        if (kategori && jenisPtkOptions[kategori]) {
            jenisPtkSelect.prop('disabled', false);
            jenisPtkSelect.append('<option value="">Pilih Jenis PTK</option>');
            
            jenisPtkOptions[kategori].forEach(function(option) {
                jenisPtkSelect.append(`<option value="${option.value}">${option.text}</option>`);
            });
        } else {
            jenisPtkSelect.append('<option value="">Pilih Kategori PTK terlebih dahulu</option>');
        }
    });
});

async function runBulkSyncKemenag(candidates, gtkTable) {
    const stats = {
        total: candidates.length,
        processed: 0,
        success: 0,
        failed: 0,
        changed: 0,
        unchanged: 0,
    };

    showBulkSyncOverlay();
    resetBulkSyncOverlay(stats.total);
    addBulkSyncLog('Memulai sinkronisasi massal GTK ber-NIP...', 'info');

    for (const candidate of candidates) {
        updateBulkSyncCurrent(candidate, stats);

        try {
            const response = await $.ajax({
                url: `/admin/gtk/${candidate.id}/sync-kemenag`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                }
            });

            stats.processed++;

            if (response.success) {
                stats.success++;

                if (response.has_differences) {
                    stats.changed++;
                    addBulkSyncLog(`${candidate.nama_lengkap}: sinkron berhasil, ditemukan ${response.applicable_differences_count ?? response.differences_count ?? 0} perubahan yang bisa ditinjau.`, 'success');
                } else {
                    stats.unchanged++;
                    addBulkSyncLog(`${candidate.nama_lengkap}: sinkron berhasil, tidak ada perubahan data.`, 'neutral');
                }
            } else {
                stats.failed++;
                addBulkSyncLog(`${candidate.nama_lengkap}: ${response.message || 'sinkronisasi gagal.'}`, 'danger');
            }
        } catch (error) {
            stats.processed++;
            stats.failed++;
            addBulkSyncLog(`${candidate.nama_lengkap}: ${error?.responseJSON?.message || 'terjadi kesalahan saat menghubungi server.'}`, 'danger');
        }

        renderBulkSyncStats(stats);

        if (BULK_SYNC_DELAY_MS > 0 && stats.processed < stats.total) {
            await wait(BULK_SYNC_DELAY_MS);
        }
    }

    updateBulkSyncCurrent(null, stats, 'Sinkronisasi selesai. Menyiapkan ringkasan hasil...');
    gtkTable.ajax.reload(null, false);

    await wait(250);
    hideBulkSyncOverlay();

    await Swal.fire({
        icon: stats.failed > 0 ? 'warning' : 'success',
        title: stats.failed > 0 ? 'Sinkronisasi Selesai Dengan Catatan' : 'Sinkronisasi Selesai',
        html: `
            <div class="text-left">
                <div class="row text-center mb-3">
                    <div class="col-6 col-md-3 mb-2"><div class="border rounded py-2"><div class="small text-muted">Total</div><div class="font-weight-bold">${stats.total}</div></div></div>
                    <div class="col-6 col-md-3 mb-2"><div class="border rounded py-2"><div class="small text-muted">Berhasil</div><div class="font-weight-bold text-success">${stats.success}</div></div></div>
                    <div class="col-6 col-md-3 mb-2"><div class="border rounded py-2"><div class="small text-muted">Perubahan</div><div class="font-weight-bold text-info">${stats.changed}</div></div></div>
                    <div class="col-6 col-md-3 mb-2"><div class="border rounded py-2"><div class="small text-muted">Gagal</div><div class="font-weight-bold text-danger">${stats.failed}</div></div></div>
                </div>
                <p class="mb-0">Hasil sinkronisasi sudah tersimpan per GTK. Kamu bisa membuka detail GTK tertentu untuk meninjau perubahan sebelum menerapkannya ke data lokal.</p>
            </div>
        `,
        confirmButtonText: 'Tutup'
    });
}

function showBulkSyncOverlay() {
    $('#bulkSyncOverlay').removeClass('d-none');
    $('body').addClass('overflow-hidden');
}

function hideBulkSyncOverlay() {
    $('#bulkSyncOverlay').addClass('d-none');
    $('body').removeClass('overflow-hidden');
}

function resetBulkSyncOverlay(total) {
    $('#bulkSyncProgressText').text(`0 / ${total}`);
    $('#bulkSyncSuccessCount').text('0');
    $('#bulkSyncChangedCount').text('0');
    $('#bulkSyncFailedCount').text('0');
    $('#bulkSyncProgressBar')
        .css('width', '0%')
        .text('0%');
    $('#bulkSyncCurrentLabel').text('Menyiapkan sinkronisasi...');
    $('#bulkSyncLogList').html('<li class="list-group-item text-muted">Belum ada aktivitas yang diproses.</li>');
}

function renderBulkSyncStats(stats) {
    const percent = stats.total > 0 ? Math.round((stats.processed / stats.total) * 100) : 0;

    $('#bulkSyncProgressText').text(`${stats.processed} / ${stats.total}`);
    $('#bulkSyncSuccessCount').text(stats.success);
    $('#bulkSyncChangedCount').text(stats.changed);
    $('#bulkSyncFailedCount').text(stats.failed);
    $('#bulkSyncProgressBar')
        .css('width', `${percent}%`)
        .text(`${percent}%`);
}

function updateBulkSyncCurrent(candidate, stats, customText = null) {
    if (customText) {
        $('#bulkSyncCurrentLabel').text(customText);
        return;
    }

    const currentNumber = Math.min(stats.processed + 1, stats.total);
    $('#bulkSyncCurrentLabel').text(`Memproses ${currentNumber}/${stats.total}: ${candidate.nama_lengkap} (${candidate.nip})`);
}

function addBulkSyncLog(message, type = 'info') {
    const colorClass = {
        success: 'text-success',
        danger: 'text-danger',
        neutral: 'text-secondary',
        info: 'text-primary',
    }[type] || 'text-primary';

    const iconClass = {
        success: 'fa-check-circle',
        danger: 'fa-times-circle',
        neutral: 'fa-minus-circle',
        info: 'fa-info-circle',
    }[type] || 'fa-info-circle';

    const $list = $('#bulkSyncLogList');
    const emptyState = $list.find('.text-muted').length === 1 && $list.children().length === 1;

    if (emptyState) {
        $list.empty();
    }

    const timestamp = new Date().toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });

    $list.prepend(`
        <li class="list-group-item">
            <div class="d-flex align-items-start">
                <i class="fas ${iconClass} ${colorClass} mt-1 mr-2"></i>
                <div>
                    <div>${escapeHtml(message)}</div>
                    <div class="small text-muted mt-1">${timestamp}</div>
                </div>
            </div>
        </li>
    `);

    if ($list.children().length > 8) {
        $list.children().last().remove();
    }
}

function wait(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

function escapeHtml(text) {
    return String(text ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// Show GTK Detail
function showGtk(id) {
    $('#viewGtkModal').modal('show');
    $('#viewGtkContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-3x"></i><p>Loading...</p></div>');
    
    $.ajax({
        url: '/admin/gtk/' + id,
        type: 'GET',
        success: function(response) {
            const gtk = response.data;
            let html = `
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2">Data Pribadi</h5>
                        <table class="table table-sm">
                            <tr><th width="150">Nama Lengkap</th><td>${gtk.nama_lengkap}</td></tr>
                            <tr><th>NIK</th><td>${gtk.nik}</td></tr>
                            <tr><th>NUPTK</th><td>${gtk.nuptk || '-'}</td></tr>
                            <tr><th>NIP</th><td>${gtk.nip || '-'}</td></tr>
                            <tr><th>Jenis Kelamin</th><td>${gtk.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan'}</td></tr>
                            <tr><th>Tempat, Tgl Lahir</th><td>${gtk.tempat_lahir || '-'}, ${gtk.tanggal_lahir || '-'}</td></tr>
                            <tr><th>Email</th><td>${gtk.email || '-'}</td></tr>
                            <tr><th>No HP</th><td>${gtk.nomor_hp || '-'}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2">Data Kepegawaian</h5>
                        <table class="table table-sm">
                            <tr><th width="150">Kategori PTK</th><td>${gtk.kategori_ptk ? '<span class="badge badge-' + (gtk.kategori_ptk === 'Pendidik' ? 'primary' : 'info') + '">' + gtk.kategori_ptk + '</span>' : '-'}</td></tr>
                            <tr><th>Jenis PTK</th><td>${gtk.jenis_ptk || '-'}</td></tr>
                            <tr><th>Status Kepegawaian</th><td>${gtk.status_kepegawaian || '-'}</td></tr>
                            <tr><th>Jabatan</th><td>${gtk.jabatan || '-'}</td></tr>
                            <tr><th>TMT Kerja</th><td>${gtk.tmt_kerja || '-'}</td></tr>
                        </table>
                        
                        <h5 class="border-bottom pb-2 mt-3">Alamat</h5>
                        <table class="table table-sm">
                            <tr><th width="150">Alamat</th><td>${gtk.alamat || '-'}</td></tr>
                            <tr><th>RT/RW</th><td>${gtk.rt || '-'} / ${gtk.rw || '-'}</td></tr>
                            <tr><th>Kelurahan</th><td>${gtk.kelurahan?.name || '-'}</td></tr>
                            <tr><th>Kecamatan</th><td>${gtk.kecamatan?.name || '-'}</td></tr>
                            <tr><th>Kabupaten</th><td>${gtk.kabupaten?.name || '-'}</td></tr>
                            <tr><th>Provinsi</th><td>${gtk.provinsi?.name || '-'}</td></tr>
                            <tr><th>Kode Pos</th><td>${gtk.kodepos || '-'}</td></tr>
                        </table>
                    </div>
                </div>
            `;
            $('#viewGtkContent').html(html);
        },
        error: function() {
            $('#viewGtkContent').html('<div class="alert alert-danger">Gagal memuat data</div>');
        }
    });
}

// Reset Password
function resetPassword(id) {
    Swal.fire({
        title: 'Reset Password?',
        text: 'Password akan direset menjadi NIK',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Reset!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/admin/gtk/' + id + '/reset-password',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        timer: 2000
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                    });
                }
            });
        }
    });
}

// Delete GTK
function deleteGtk(id) {
    Swal.fire({
        title: 'Hapus GTK?',
        text: 'Data GTK dan akun user akan dihapus permanen!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/admin/gtk/' + id,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        timer: 2000
                    });
                    
                    $('#gtk-table').DataTable().ajax.reload();
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                    });
                }
            });
        }
    });
}
</script>
@stop

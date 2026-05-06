@extends('adminlte::page')

@section('title', 'Siswa Tidak Mampu / PIP - SIMANSA')

@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugins', true)

@section('content_header')
    <div class="simansa-hero">
        <div class="simansa-hero__main">
            <div class="simansa-hero__eyebrow">
                <i class="fas fa-hand-holding-heart"></i>
                Manajemen Kesiswaan
            </div>
            <h1 class="simansa-hero__title">Siswa Tidak Mampu / PIP</h1>
            <p class="simansa-hero__subtitle">
                Daftar siswa yang memiliki dokumen PIP/KIP atau Surat Keterangan Tidak Mampu (SKTM).
            </p>
        </div>
        <div class="simansa-hero__side">
            <div class="simansa-hero-chip">
                <span class="simansa-hero-chip__label">Total Siswa</span>
                <span class="simansa-hero-chip__value">{{ number_format($stats['total']) }}</span>
            </div>
            <div class="simansa-hero-chip">
                <span class="simansa-hero-chip__label">PIP / KIP</span>
                <span class="simansa-hero-chip__value">{{ number_format($stats['pip']) }}</span>
            </div>
        </div>
    </div>
@stop

@section('content')

{{-- Stat Cards --}}
<div class="row mb-4">
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="simansa-stat-card simansa-stat-card--blue">
            <div class="simansa-stat-card__icon"><i class="fas fa-users"></i></div>
            <div class="simansa-stat-card__body">
                <div class="simansa-stat-card__label">Total Siswa</div>
                <div class="simansa-stat-card__value">{{ number_format($stats['total']) }}</div>
                <div class="simansa-stat-card__desc">Siswa dengan dokumen PIP atau SKTM.</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="simansa-stat-card simansa-stat-card--green">
            <div class="simansa-stat-card__icon"><i class="fas fa-id-card"></i></div>
            <div class="simansa-stat-card__body">
                <div class="simansa-stat-card__label">PIP / KIP</div>
                <div class="simansa-stat-card__value">{{ number_format($stats['pip']) }}</div>
                <div class="simansa-stat-card__desc">Punya dokumen kartu PIP atau KIP.</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="simansa-stat-card simansa-stat-card--amber">
            <div class="simansa-stat-card__icon"><i class="fas fa-file-alt"></i></div>
            <div class="simansa-stat-card__body">
                <div class="simansa-stat-card__label">SKTM</div>
                <div class="simansa-stat-card__value">{{ number_format($stats['sktm']) }}</div>
                <div class="simansa-stat-card__desc">Punya Surat Keterangan Tidak Mampu.</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="simansa-stat-card simansa-stat-card--cyan">
            <div class="simansa-stat-card__icon"><i class="fas fa-venus-mars"></i></div>
            <div class="simansa-stat-card__body">
                <div class="simansa-stat-card__label">L / P</div>
                <div class="simansa-stat-card__value">{{ number_format($stats['laki_laki']) }} / {{ number_format($stats['perempuan']) }}</div>
                <div class="simansa-stat-card__desc">Perbandingan laki-laki dan perempuan.</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card simansa-management-card">
            <div class="card-header">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between">
                    <h3 class="card-title mb-3 mb-lg-0">
                        <i class="fas fa-hand-holding-heart mr-2"></i>
                        Daftar Siswa Tidak Mampu / PIP
                    </h3>
                    <div class="card-tools ml-0">
                        <button type="button" id="btnExportExcel" class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                {{-- Filter --}}
                <div class="simansa-filter-panel">
                    <div class="row">
                        <div class="col-md-6 col-xl-3 mb-3">
                            <label class="simansa-filter-label">
                                <i class="fas fa-folder-open mr-1"></i> Jenis Dokumen
                            </label>
                            <select id="filterJenis" class="form-control form-control-sm">
                                <option value="">Semua (PIP + SKTM)</option>
                                <option value="pip">PIP / KIP saja</option>
                                <option value="sktm">SKTM saja</option>
                            </select>
                        </div>
                        <div class="col-md-6 col-xl-3 mb-3">
                            <label class="simansa-filter-label">
                                <i class="fas fa-layer-group mr-1"></i> Tingkat
                            </label>
                            <select id="filterTingkat" class="form-control form-control-sm">
                                <option value="">Semua</option>
                                @foreach($tingkatOptions as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 col-xl-3 mb-3">
                            <label class="simansa-filter-label">
                                <i class="fas fa-door-open mr-1"></i> Kelas
                            </label>
                            <select id="filterKelas" class="form-control form-control-sm" disabled>
                                <option value="">Pilih Tingkat Dulu</option>
                            </select>
                        </div>
                        <div class="col-md-6 col-xl-3 mb-3 d-flex align-items-end">
                            <button type="button" id="btnResetFilter" class="btn btn-sm btn-outline-secondary w-100">
                                <i class="fas fa-redo mr-1"></i> Reset Filter
                            </button>
                        </div>
                    </div>
                </div>

                <p class="text-muted small mb-3">
                    Menampilkan siswa yang telah mengupload dokumen PIP/KIP atau SKTM ke sistem.
                    Klik tombol detail untuk melihat dokumen lengkap.
                </p>

                <div class="table-responsive">
                    <table id="pip-table" class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>NISN</th>
                                <th>Nama Lengkap</th>
                                <th>Jenis Kelamin</th>
                                <th>Kelas</th>
                                <th>Dokumen</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@stop

@section('js')
<script>
$(function () {
    const kelasData = @json($kelasOptions);

    // ── DataTable ──────────────────────────────────────────────────────────────
    const table = $('#pip-table').DataTable({
        processing : true,
        serverSide : true,
        ajax: {
            url: '{{ route("admin.pip.data") }}',
            data: function (d) {
                d.jenis    = $('#filterJenis').val();
                d.tingkat  = $('#filterTingkat').val();
                d.kelas_id = $('#filterKelas').val();
            }
        },
        columns: [
            { data: null, render: function(data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; }, orderable: false, searchable: false },
            { data: 'nisn' },
            { data: 'nama_lengkap' },
            { data: 'jenis_kelamin' },
            { data: 'kelas' },
            { data: 'dokumen', orderable: false },
            { data: 'actions', orderable: false },
        ],
        order: [[2, 'asc']],
        language: {
            processing: '<i class="fas fa-spinner fa-spin"></i> Memuat data...',
            emptyTable:  'Tidak ada siswa dengan dokumen PIP / SKTM.',
            zeroRecords: 'Tidak ada siswa yang cocok dengan filter.',
            lengthMenu:  'Tampilkan _MENU_ data per halaman',
            info:        'Menampilkan _START_–_END_ dari _TOTAL_ siswa',
            infoEmpty:   'Tidak ada data.',
            search:      'Cari:',
            paginate:    { first: '«', last: '»', next: '›', previous: '‹' },
        },
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100, -1],
    });

    // ── Filter events ──────────────────────────────────────────────────────────
    $('#filterJenis, #filterTingkat, #filterKelas').on('change', function () {
        table.draw();
    });

    // Cascading tingkat → kelas
    $('#filterTingkat').on('change', function () {
        const tingkat = $(this).val();
        const $selKelas = $('#filterKelas');
        $selKelas.html('<option value="">Semua Kelas</option>');
        if (tingkat) {
            const filtered = kelasData.filter(k => k.tingkat == tingkat);
            filtered.forEach(k => {
                $selKelas.append(`<option value="${k.id}">${k.nama_kelas}</option>`);
            });
            $selKelas.prop('disabled', filtered.length === 0);
        } else {
            $selKelas.html('<option value="">Pilih Tingkat Dulu</option>').prop('disabled', true);
        }
        table.draw();
    });

    // Reset filter
    $('#btnResetFilter').on('click', function () {
        $('#filterJenis').val('');
        $('#filterTingkat').val('');
        $('#filterKelas').html('<option value="">Pilih Tingkat Dulu</option>').prop('disabled', true);
        table.draw();
    });

    // ── Export Excel (sederhana via print) ────────────────────────────────────
    $('#btnExportExcel').on('click', function () {
        table.button('.buttons-excel')?.trigger();
        // Fallback: buka URL dengan query string
        const params = new URLSearchParams({
            jenis:    $('#filterJenis').val(),
            tingkat:  $('#filterTingkat').val(),
            kelas_id: $('#filterKelas').val(),
            export:   'excel',
        });
        window.open('{{ route("admin.pip.data") }}?' + params.toString(), '_blank');
    });
});
</script>
@stop

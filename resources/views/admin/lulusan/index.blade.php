@extends('adminlte::page')

@section('title', 'Data Lulusan')

@section('content_header')
    <h1>Data Lulusan</h1>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Filter Rekap Lulusan</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tahun Pelajaran</label>
                        <select id="filterTahunPelajaran" class="form-control">
                            @foreach($tahunPelajaranList as $tahun)
                                <option value="{{ $tahun->id }}" {{ optional($selectedTahun)->id === $tahun->id ? 'selected' : '' }}>
                                    {{ $tahun->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Status Pengisian</label>
                        <select id="filterStatusPengisian" class="form-control">
                            <option value="">Semua Status</option>
                            <option value="sudah_isi">Sudah Isi</option>
                            <option value="belum_isi">Belum Isi</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Jalur Masuk</label>
                        <select id="filterJalurMasuk" class="form-control">
                            <option value="">Semua Jalur</option>
                            @foreach($jalurMasukOptions as $jalur)
                                <option value="{{ $jalur }}">{{ $jalur }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Pencarian</label>
                        <input type="text" id="filterPencarian" class="form-control" placeholder="Nama, NISN, kampus, prodi">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="button" id="btnApplyFilter" class="btn btn-primary">
                <i class="fas fa-filter mr-1"></i> Terapkan Filter
            </button>
            <button type="button" id="btnResetFilter" class="btn btn-default">
                Reset
            </button>
        </div>
    </div>

    @if($selectedTahun)
        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="info-box bg-info">
                    <span class="info-box-icon"><i class="fas fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Kelas 12</span>
                        <span class="info-box-number" id="summaryTotal">0</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="info-box bg-success">
                    <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Sudah Mengisi</span>
                        <span class="info-box-number" id="summarySudahIsi">0</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="info-box bg-warning">
                    <span class="info-box-icon"><i class="fas fa-edit"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Belum Mengisi</span>
                        <span class="info-box-number" id="summaryBelumIsi">0</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="info-box bg-primary">
                    <span class="info-box-icon"><i class="fas fa-university"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Universitas Tujuan</span>
                        <span class="info-box-number" id="summaryUniversitas">0</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-5">
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
            <div class="col-lg-7">
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
        </div>

        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Daftar Lulusan Kelas 12</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="lulusanTable" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>NISN</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Status</th>
                                <th>Jalur</th>
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
                <h3 class="card-title">Matriks Laporan per Kelas dan Jalur</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-bordered table-sm mb-0" id="matrixTable">
                    <thead>
                        <tr>
                            <th>Kelas</th>
                            @foreach($jalurMasukOptions as $jalur)
                                <th class="text-center">{{ $jalur }}</th>
                            @endforeach
                            <th class="text-center">Sudah Isi</th>
                            <th class="text-center">Belum Isi</th>
                            <th class="text-center">Total</th>
                        </tr>
                    </thead>
                    <tbody id="matrixTableBody">
                        <tr>
                            <td colspan="{{ count($jalurMasukOptions) + 4 }}" class="text-center text-muted py-3">Memuat matriks laporan...</td>
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
@stop

@section('css')
    <link rel="stylesheet" href="//cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
    <style>
        #matrixTable th,
        #matrixTable td {
            white-space: nowrap;
            vertical-align: middle;
        }
    </style>
@stop

@section('js')
    <script src="//cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="//cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
    <script>
        let lulusanTable;
        const jalurMasukOptions = @json($jalurMasukOptions);

        function getFilters() {
            return {
                tahun_pelajaran_id: $('#filterTahunPelajaran').val(),
                status_pengisian: $('#filterStatusPengisian').val(),
                jalur_masuk: $('#filterJalurMasuk').val(),
                q: $('#filterPencarian').val()
            };
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
                tbody.append(`
                    <tr>
                        <td>${item.nama_universitas}</td>
                        <td class="text-right font-weight-bold">${item.jumlah}</td>
                    </tr>
                `);
            });
        }

        function renderMatrix(perKelas) {
            const tbody = $('#matrixTableBody');
            tbody.empty();

            if (!perKelas || perKelas.length === 0) {
                tbody.html(`<tr><td colspan="${jalurMasukOptions.length + 4}" class="text-center text-muted py-3">Tidak ada data untuk filter ini.</td></tr>`);
                return;
            }

            perKelas.forEach(item => {
                const jalurCells = jalurMasukOptions.map(jalur => `<td class="text-center">${item.jalur[jalur] ?? 0}</td>`).join('');

                tbody.append(`
                    <tr>
                        <td>${item.kelas_nama}</td>
                        ${jalurCells}
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
                    $('#summaryTotal').text(response.summary.total ?? 0);
                    $('#summarySudahIsi').text(response.summary.sudah_isi ?? 0);
                    $('#summaryBelumIsi').text(response.summary.belum_isi ?? 0);
                    $('#summaryUniversitas').text(response.summary.total_universitas ?? 0);

                    renderPerJalur(response.per_jalur);
                    renderTopUniversitas(response.top_universitas);
                    renderMatrix(response.per_kelas);
                },
                error: function() {
                    $('#summaryTotal, #summarySudahIsi, #summaryBelumIsi, #summaryUniversitas').text('0');
                    renderPerJalur({});
                    renderTopUniversitas([]);
                    renderMatrix([]);
                }
            });
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
                lulusanTable.search($('#filterPencarian').val()).draw();
                loadStats();
            });

            $('#btnResetFilter').on('click', function () {
                $('#filterStatusPengisian').val('');
                $('#filterJalurMasuk').val('');
                $('#filterPencarian').val('');
                lulusanTable.search('').ajax.reload();
                loadStats();
            });

            $('#filterPencarian').on('keyup', function (e) {
                if (e.key === 'Enter') {
                    lulusanTable.search(this.value).draw();
                    loadStats();
                }
            });

            loadStats();
        });
    </script>
@stop

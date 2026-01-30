@extends('adminlte::page')

@section('title', 'Nilai ' . $semesterLabel)

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-chart-line"></i> Nilai {{ $semesterLabel }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.nilai.index') }}">Nilai Siswa</a></li>
                <li class="breadcrumb-item active">{{ $semesterLabel }}</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    {{-- Filter --}}
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filter</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.nilai.semester', $semester) }}" class="form-inline">
                <div class="form-group mr-3">
                    <label class="mr-2">Tahun Pelajaran:</label>
                    <select name="tahun_pelajaran_id" class="form-control" onchange="this.form.submit()">
                        <option value="">-- Semua Tahun --</option>
                        @foreach($tahunPelajarans as $tp)
                            <option value="{{ $tp->id }}" {{ ($selectedTahun && $selectedTahun->id == $tp->id) ? 'selected' : '' }}>
                                {{ $tp->nama }} {{ $tp->is_active ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @if($selectedTahun)
                <span class="text-muted ml-2">
                    <i class="fas fa-info-circle"></i> Menampilkan nilai tahun: {{ $selectedTahun->nama }}
                </span>
                @endif
            </form>
        </div>
    </div>

    {{-- Actions --}}
    <div class="card">
        <div class="card-body">
            <a href="{{ route('admin.nilai.upload-form') }}?semester={{ $semester }}" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Upload Nilai Semester {{ $semester }}
            </a>
            <a href="{{ route('admin.nilai.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            @if($mapelList->count() > 0)
            <button type="button" class="btn btn-danger float-right" id="btn-delete-semester">
                <i class="fas fa-trash"></i> Hapus Semua Nilai Semester Ini
            </button>
            @endif
        </div>
    </div>

    {{-- Data Table --}}
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-table"></i> Data Nilai</h3>
        </div>
        <div class="card-body">
            @if($mapelList->count() > 0)
            <div class="table-responsive">
                <table id="nilai-table" class="table table-bordered table-striped table-sm">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NISN</th>
                            <th>Nama</th>
                            @foreach($mapelList as $mapel)
                                <th title="{{ $mapel->nama_mapel }}">{{ $mapel->kode_mapel }}</th>
                            @endforeach
                            <th>Rata-rata</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
            @else
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Belum ada data nilai untuk semester ini. 
                <a href="{{ route('admin.nilai.upload-form') }}?semester={{ $semester }}">Upload nilai dari Excel</a>
            </div>
            @endif
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <style>
        #nilai-table th, #nilai-table td {
            text-align: center;
            vertical-align: middle;
            font-size: 12px;
        }
        #nilai-table th {
            white-space: nowrap;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            var mapelCodes = @json($mapelList->pluck('kode_mapel'));
            
            var columns = [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'nisn', name: 'nisn' },
                { data: 'nama', name: 'nama' }
            ];
            
            // Add mapel columns dynamically
            mapelCodes.forEach(function(kode) {
                columns.push({
                    data: 'nilai_list.' + kode,
                    name: kode,
                    orderable: false,
                    searchable: false,
                    render: function(data) {
                        return data ? data : '-';
                    }
                });
            });
            
            columns.push({ data: 'rata_rata', name: 'rata_rata', orderable: false });
            columns.push({ data: 'action', name: 'action', orderable: false, searchable: false });
            
            $('#nilai-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('admin.nilai.semester', $semester) }}',
                    data: function(d) {
                        d.tahun_pelajaran_id = '{{ $selectedTahun ? $selectedTahun->id : '' }}';
                    }
                },
                columns: columns,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                }
            });

            // Delete semester
            $('#btn-delete-semester').click(function() {
                Swal.fire({
                    title: 'Hapus Semua Nilai?',
                    text: 'Semua data nilai {{ $semesterLabel }} akan dihapus!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('admin.nilai.delete-semester', $semester) }}',
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}',
                                tahun_pelajaran_id: '{{ $selectedTahun ? $selectedTahun->id : '' }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('Berhasil!', response.message, 'success')
                                        .then(() => location.reload());
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@stop

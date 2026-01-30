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
            @if($selectedTahun && $mapelList->count() > 0)
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exportModal">
                <i class="fas fa-download"></i> Export Nilai
            </button>
            @endif
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

    {{-- Export Modal --}}
    @if($selectedTahun && $mapelList->count() > 0)
    <div class="modal fade" id="exportModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.nilai.export-semester-preview', $semester) }}" method="POST">
                    @csrf
                    <input type="hidden" name="tahun_pelajaran_id" value="{{ $selectedTahun->id }}">
                    
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title text-white">
                            <i class="fas fa-download"></i> Export Nilai {{ $semesterLabel }}
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Export nilai sesuai urutan NISN dan mapel yang dipilih.
                            Urutan akan sama persis dengan input NISN, cocok untuk template SPAN yang sudah ter-protect.
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fas fa-list-ol"></i> Daftar NISN (satu per baris) <span class="text-danger">*</span></label>
                                    <textarea name="nisn_list" class="form-control" rows="15" 
                                        placeholder="Masukkan NISN per baris, contoh:&#10;1234567890&#10;0987654321&#10;1122334455" required></textarea>
                                    <small class="text-muted">Copy-paste NISN dari template SPAN, satu NISN per baris</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fas fa-book"></i> Pilih Mata Pelajaran <span class="text-danger">*</span></label>
                                    <div class="mb-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllMapel">
                                            <i class="fas fa-check-square"></i> Pilih Semua
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllMapel">
                                            <i class="fas fa-square"></i> Hapus Semua
                                        </button>
                                    </div>
                                    <div class="border rounded p-2" style="max-height: 350px; overflow-y: auto;">
                                        <small class="text-muted d-block mb-2">
                                            <i class="fas fa-arrows-alt-v"></i> Drag untuk mengubah urutan
                                        </small>
                                        <ul class="list-group" id="mapelSortable">
                                            @foreach($mapelList as $mapel)
                                            <li class="list-group-item list-group-item-action py-1 px-2" data-kode="{{ $mapel->kode_mapel }}" style="cursor: move;">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-grip-vertical text-muted mr-2"></i>
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input mapel-check" 
                                                            id="mapel_{{ $mapel->kode_mapel }}" 
                                                            name="mapel_list[]" 
                                                            value="{{ $mapel->kode_mapel }}" checked>
                                                        <label class="custom-control-label" for="mapel_{{ $mapel->kode_mapel }}">
                                                            <strong>{{ $mapel->kode_mapel }}</strong> 
                                                            <small class="text-muted">- {{ $mapel->nama_mapel }}</small>
                                                        </label>
                                                    </div>
                                                </div>
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-download"></i> Export Excel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
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
        #mapelSortable .list-group-item {
            border-left: 3px solid #007bff;
        }
        #mapelSortable .list-group-item.ui-sortable-helper {
            background: #f8f9fa;
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
        }
        #mapelSortable .list-group-item.ui-sortable-placeholder {
            visibility: visible !important;
            background: #e9ecef;
            border: 2px dashed #adb5bd;
        }
        #exportModal textarea[name="nisn_list"] {
            resize: vertical;
            min-height: 200px;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
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

            // Make mapel list sortable
            $('#mapelSortable').sortable({
                placeholder: 'list-group-item ui-sortable-placeholder',
                handle: '.fa-grip-vertical',
                update: function(event, ui) {
                    // Optional: update order visual
                }
            });

            // Select all mapel
            $('#selectAllMapel').click(function() {
                $('.mapel-check').prop('checked', true);
            });

            // Deselect all mapel
            $('#deselectAllMapel').click(function() {
                $('.mapel-check').prop('checked', false);
            });

            // Validate export form
            $('#exportModal form').submit(function(e) {
                var nisnList = $('textarea[name="nisn_list"]').val().trim();
                var mapelChecked = $('.mapel-check:checked').length;
                
                if (!nisnList) {
                    e.preventDefault();
                    Swal.fire('Error', 'Masukkan minimal 1 NISN', 'error');
                    return false;
                }
                
                if (mapelChecked === 0) {
                    e.preventDefault();
                    Swal.fire('Error', 'Pilih minimal 1 mata pelajaran', 'error');
                    return false;
                }
                
                return true;
            });
        });
    </script>
@stop

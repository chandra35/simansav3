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
                <li class="breadcrumb-item"><a href="{{ route('admin.nilai.index') }}">Nilai Legger</a></li>
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

    <section class="simansa-semester-hero">
        <div class="simansa-semester-hero__content">
            <div>
                <div class="simansa-semester-hero__eyebrow">
                    <i class="fas fa-table"></i> Rekap Semester
                </div>
                <h2>Nilai {{ $semesterLabel }}</h2>
                <p>Lihat data nilai per semester, sesuaikan tahun pelajaran bila perlu, lalu masuk ke detail siswa atau export sesuai urutan mapel.</p>
            </div>
            <div class="simansa-semester-chip">
                <span class="simansa-semester-chip__label">Tahun Pelajaran</span>
                <strong>{{ $selectedTahun?->nama ?? 'Semua Tahun' }}</strong>
            </div>
        </div>
    </section>

    <div class="simansa-semester-panel">
        <div class="simansa-semester-panel__header">
            <div>
                <h3><i class="fas fa-filter"></i> Filter</h3>
                <p>Gunakan filter untuk menampilkan semester yang sama pada tahun pelajaran tertentu.</p>
            </div>
        </div>
        <div class="simansa-semester-panel__body">
            <form method="GET" action="{{ route('admin.nilai.semester', $semester) }}" class="form-inline">
                @if(request('tingkat'))
                    <input type="hidden" name="tingkat" value="{{ request('tingkat') }}">
                @endif
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

    <div class="simansa-semester-panel">
        <div class="simansa-semester-panel__header">
            <div>
                <h3><i class="fas fa-cog"></i> Aksi Semester</h3>
                <p>Akses cepat ke upload nilai, export, atau pembersihan data semester yang sedang ditampilkan.</p>
            </div>
        </div>
        <div class="simansa-semester-panel__body">
            <a href="{{ route('admin.nilai.upload-form') }}?semester={{ $semester }}@if(request('tingkat'))&tingkat={{ request('tingkat') }}@endif" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Upload Nilai Semester {{ $semester }}
            </a>
            @if($selectedTahun && $mapelList->count() > 0)
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exportModal">
                <i class="fas fa-download"></i> Export Nilai
            </button>
            @endif
            <a href="{{ request('tingkat') ? route('admin.nilai.index', ['tingkat' => request('tingkat')]) : route('admin.nilai.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            @if($mapelList->count() > 0)
            <button type="button" class="btn btn-danger float-right" id="btn-delete-semester">
                <i class="fas fa-trash"></i> Hapus Semua Nilai Semester Ini
            </button>
            @endif
        </div>
    </div>

    <div class="simansa-semester-panel">
        <div class="simansa-semester-panel__header">
            <div>
                <h3><i class="fas fa-table"></i> Data Nilai</h3>
                <p>Kolom mapel mengikuti nilai yang benar-benar tersimpan pada semester dan tahun terpilih.</p>
            </div>
            @if($mapelList->isNotEmpty())
                <div class="simansa-mapel-detection">
                    <strong>{{ $mapelList->count() }} mapel terdeteksi</strong>
                    <span>{{ $mapelList->pluck('kurikulum.kode')->filter()->unique()->implode(' + ') ?: 'Kurikulum historis' }}</span>
                </div>
            @endif
        </div>
        <div class="simansa-semester-panel__body">
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
                <a href="{{ route('admin.nilai.upload-form') }}?semester={{ $semester }}@if(request('tingkat'))&tingkat={{ request('tingkat') }}@endif">Upload nilai dari Excel</a>
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
        .simansa-semester-hero{margin-bottom:1.5rem;padding:1.35rem 1.5rem;border-radius:22px;background:linear-gradient(135deg,#2147cf 0%,#2f8d9c 100%);color:#fff;box-shadow:0 18px 40px rgba(33,71,207,.16)}
        .simansa-semester-hero__content{display:flex;justify-content:space-between;gap:1rem;align-items:flex-start}
        .simansa-semester-hero__eyebrow{display:inline-flex;align-items:center;gap:.45rem;font-size:.78rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.82);margin-bottom:.75rem}
        .simansa-semester-hero h2{margin:0 0 .35rem;font-size:1.75rem;font-weight:700}
        .simansa-semester-hero p{margin:0;max-width:760px;color:rgba(255,255,255,.92)}
        .simansa-semester-chip{padding:1rem 1.1rem;border-radius:18px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.18);min-width:220px}
        .simansa-semester-chip__label{display:block;margin-bottom:.35rem;font-size:.72rem;letter-spacing:.05em;text-transform:uppercase;color:rgba(255,255,255,.74)}
        .simansa-semester-panel{background:#fff;border-radius:22px;box-shadow:0 14px 34px rgba(15,23,42,.08);margin-bottom:1.5rem;overflow:hidden}
        .simansa-mapel-detection{display:flex;flex-direction:column;align-items:flex-end;padding:.55rem .8rem;border-radius:12px;background:#eef8f4;color:#147451}.simansa-mapel-detection strong{font-size:.8rem}.simansa-mapel-detection span{font-size:.68rem}
        .simansa-semester-panel__header{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1.35rem 1.5rem;border-bottom:1px solid rgba(148,163,184,.18)}
        .simansa-semester-panel__header h3{margin:0 0 .25rem;font-size:1.1rem;font-weight:700;color:#1f2a44}
        .simansa-semester-panel__header p{margin:0;color:#60708b;font-size:.92rem}
        .simansa-semester-panel__body{padding:1.5rem}
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
        @media (max-width:992px){.simansa-semester-hero__content{flex-direction:column;align-items:stretch}.simansa-semester-chip{min-width:0}}
        @media (max-width:767px){.simansa-semester-panel__header{align-items:flex-start;flex-direction:column}.simansa-mapel-detection{align-items:flex-start}}
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
                        d.tingkat = '{{ request('tingkat') }}';
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

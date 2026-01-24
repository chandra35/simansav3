@extends('adminlte::page')

@section('title', 'Tambah Kelas')

@section('css')
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- Select2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.5.4/dist/select2-bootstrap4.min.css">
    
    <style>
        /* Card Styling */
        .card {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            font-weight: 600;
            border: none;
        }
        
        .card-secondary .card-header {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        }
        
        /* Form Group Styling */
        .form-group label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }
        
        .form-group label .text-danger {
            font-weight: bold;
        }
        
        .form-control, .select2-container--bootstrap4 .select2-selection {
            border-radius: 6px;
            border: 1px solid #ced4da;
            padding: 0.6rem 0.75rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
        }
        
        .select2-container--bootstrap4 .select2-selection--single {
            height: calc(2.5rem + 2px) !important;
        }
        
        .select2-container--bootstrap4 .select2-selection__rendered {
            line-height: 2.5rem !important;
        }
        
        /* Info Box Styling */
        .info-box {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border-left: 4px solid #2196f3;
            border-radius: 6px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .info-box h5 {
            color: #1976d2;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .info-box ul {
            margin-bottom: 0;
            color: #424242;
        }
        
        /* Section Headers */
        .section-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-left: 4px solid #007bff;
            padding: 0.75rem 1rem;
            margin: 1.5rem -1rem 1rem -1rem;
            font-weight: 600;
            color: #495057;
        }
        
        .section-header:first-child {
            margin-top: -0.25rem;
        }
        
        .section-header i {
            color: #007bff;
            margin-right: 0.5rem;
        }
        
        /* Button Styling */
        .btn {
            border-radius: 6px;
            padding: 0.5rem 1.25rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .btn i {
            margin-right: 0.5rem;
        }
        
        /* Callout Improvements */
        .callout {
            border-radius: 6px;
            border-left-width: 4px;
        }
        
        .callout-info {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border-left-color: #2196f3;
        }
        
        /* Modal Styling */
        #kelasDeletedModal .modal-header {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: #fff;
            border-bottom: 3px solid #ff9800;
        }
        
        #kelasDeletedModal .modal-header .modal-title {
            font-weight: 600;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        
        #kelasDeletedModal .card-outline.card-warning {
            border-top: 3px solid #ffc107;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        #kelasDeletedModal .badge-lg {
            font-size: 1rem;
            padding: 0.5rem 0.75rem;
        }
        
        #kelasDeletedModal dl dt {
            font-weight: 600;
            color: #6c757d;
        }
        
        #kelasDeletedModal dl dd {
            color: #495057;
        }
        
        #kelasDeletedModal .callout {
            border-left-width: 4px;
        }
        
        #kelasDeletedModal .modal-footer {
            background-color: #f8f9fa;
            border-top: 2px solid #dee2e6;
        }
        
        #kelasDeletedModal .btn {
            font-weight: 500;
            padding: 0.5rem 1.25rem;
            border-radius: 0.25rem;
            transition: all 0.3s ease;
        }
        
        #kelasDeletedModal .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        /* Form Text Styling */
        .form-text {
            font-size: 0.875rem;
            color: #6c757d;
        }
        
        .form-text i {
            color: #17a2b8;
        }
        
        /* Required Field Indicator */
        .required-indicator {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
        }
        
        .required-indicator p {
            margin: 0;
            color: #856404;
            font-size: 0.9rem;
        }
    </style>
@stop

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-plus-circle"></i> Tambah Kelas</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.kelas.index') }}">Kelas</a></li>
                <li class="breadcrumb-item active">Tambah</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <strong>Terjadi kesalahan:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <form action="{{ route('admin.kelas.store') }}" method="POST" id="kelasForm">
        @csrf
        
        <div class="row">
            <div class="col-md-8">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-info-circle"></i> Informasi Kelas</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="tahun_pelajaran_id">Tahun Pelajaran <span class="text-danger">*</span></label>
                            <select class="form-control @error('tahun_pelajaran_id') is-invalid @enderror" 
                                id="tahun_pelajaran_id" name="tahun_pelajaran_id" required>
                                <option value="">Pilih Tahun Pelajaran</option>
                                @foreach($tahunPelajarans as $tp)
                                    <option value="{{ $tp->id }}" {{ old('tahun_pelajaran_id') == $tp->id ? 'selected' : '' }}>
                                        {{ $tp->nama }} {{ $tp->is_active ? '(Aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tahun_pelajaran_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="kurikulum_id">Kurikulum <span class="text-danger">*</span></label>
                                    <select class="form-control @error('kurikulum_id') is-invalid @enderror" 
                                        id="kurikulum_id" name="kurikulum_id" required>
                                        <option value="">Pilih Kurikulum</option>
                                        @foreach($kurikulums as $k)
                                            <option value="{{ $k->id }}" data-has-jurusan="{{ $k->has_jurusan }}" 
                                                {{ old('kurikulum_id') == $k->id ? 'selected' : '' }}>
                                                {{ $k->formatted_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('kurikulum_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group" id="jurusan-group" style="display: none;">
                                    <label for="jurusan_id">Jurusan/Peminatan</label>
                                    <select class="form-control @error('jurusan_id') is-invalid @enderror" 
                                        id="jurusan_id" name="jurusan_id">
                                        <option value="">Tanpa Jurusan</option>
                                        @foreach($jurusans as $j)
                                            <option value="{{ $j->id }}" {{ old('jurusan_id') == $j->id ? 'selected' : '' }}>
                                                {{ $j->nama_jurusan }} ({{ $j->singkatan }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('jurusan_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Kosongkan jika kurikulum tidak memiliki jurusan
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tingkat">Tingkat <span class="text-danger">*</span></label>
                                    <select class="form-control @error('tingkat') is-invalid @enderror" 
                                        id="tingkat" name="tingkat" required>
                                        <option value="">Pilih Tingkat</option>
                                        @foreach($tingkatOptions as $value => $label)
                                            <option value="{{ $value }}" {{ old('tingkat') == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('tingkat')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nama_kelas">Nama Kelas <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nama_kelas') is-invalid @enderror" 
                                        id="nama_kelas" name="nama_kelas" value="{{ old('nama_kelas') }}" 
                                        placeholder="Contoh: X IPA 1, XI IPS 2" required maxlength="50">
                                    @error('nama_kelas')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Format: Tingkat + Jurusan + Nomor
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="wali_kelas_id">Wali Kelas</label>
                            <select class="form-control @error('wali_kelas_id') is-invalid @enderror" 
                                id="wali_kelas_id" name="wali_kelas_id">
                                <option value="">Belum ditugaskan</option>
                                @foreach($waliKelas as $wk)
                                    <option value="{{ $wk->id }}" {{ old('wali_kelas_id') == $wk->id ? 'selected' : '' }}>
                                        {{ $wk->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('wali_kelas_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Guru yang bertanggung jawab atas kelas ini
                            </small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="kapasitas">Kapasitas <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('kapasitas') is-invalid @enderror" 
                                        id="kapasitas" name="kapasitas" value="{{ old('kapasitas', 45) }}" 
                                        required min="1" max="50">
                                    @error('kapasitas')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Maksimal jumlah siswa (1-50)
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ruang_kelas">Ruang Kelas</label>
                                    <input type="text" class="form-control @error('ruang_kelas') is-invalid @enderror" 
                                        id="ruang_kelas" name="ruang_kelas" value="{{ old('ruang_kelas') }}" 
                                        placeholder="Contoh: R.01, Lab Komputer" maxlength="50">
                                    @error('ruang_kelas')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                                id="deskripsi" name="deskripsi" rows="3" 
                                placeholder="Catatan tambahan tentang kelas...">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-cog"></i> Pengaturan</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="is_active">Status</label>
                            <select class="form-control" id="is_active" name="is_active">
                                <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ old('is_active') == 0 ? 'selected' : '' }}>Non-Aktif</option>
                            </select>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Kelas aktif dapat menerima siswa
                            </small>
                        </div>

                        <div class="callout callout-info">
                            <h5><i class="fas fa-info-circle"></i> Catatan:</h5>
                            <ul class="mb-0 pl-3">
                                <li>Kode kelas akan digenerate otomatis</li>
                                <li>Siswa dapat ditambahkan setelah kelas dibuat</li>
                                <li>Wali kelas dapat diubah kapan saja</li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('admin.kelas.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <button type="reset" class="btn btn-warning">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                        <button type="submit" class="btn btn-primary float-right">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- Modal Konfirmasi Kelas Terhapus --}}
    @if(session('warning') && session('soft_deleted_kelas'))
    <div class="modal fade" id="kelasDeletedModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h4 class="modal-title">
                        <i class="fas fa-exclamation-triangle"></i> Kelas dengan Kode yang Sama Ditemukan!
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning mb-3">
                        <h5 class="mb-2"><i class="fas fa-info-circle"></i> Informasi</h5>
                        <p class="mb-0">Sistem menemukan kelas yang sudah dihapus dengan kode yang sama. Anda dapat memilih untuk me-restore kelas lama atau membuat kelas baru dengan nomor urut yang berbeda.</p>
                    </div>

                    <div class="card card-outline card-warning">
                        <div class="card-header">
                            <h5 class="card-title"><i class="fas fa-archive"></i> Data Kelas yang Dihapus</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-5"><i class="fas fa-barcode text-muted"></i> Kode Kelas:</dt>
                                        <dd class="col-sm-7">
                                            <span class="badge badge-secondary badge-lg">{{ session('soft_deleted_kelas')['kode_kelas'] }}</span>
                                        </dd>
                                        
                                        <dt class="col-sm-5"><i class="fas fa-door-open text-muted"></i> Nama Kelas:</dt>
                                        <dd class="col-sm-7">{{ session('soft_deleted_kelas')['nama_kelas'] }}</dd>
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-5"><i class="fas fa-calendar-times text-muted"></i> Dihapus pada:</dt>
                                        <dd class="col-sm-7">{{ session('soft_deleted_kelas')['deleted_at'] }}</dd>
                                        
                                        <dt class="col-sm-5"><i class="fas fa-users text-muted"></i> Siswa (history):</dt>
                                        <dd class="col-sm-7">
                                            <span class="badge badge-info">{{ session('soft_deleted_kelas')['jumlah_siswa'] }} siswa</span>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="callout callout-info mt-3">
                        <h5><i class="fas fa-question-circle"></i> Apa yang ingin Anda lakukan?</h5>
                        <p class="mb-2">Silakan pilih salah satu opsi di bawah ini:</p>
                        <ul class="mb-0">
                            <li><strong>Restore:</strong> Mengembalikan kelas yang sudah dihapus beserta data siswa (jika ada)</li>
                            <li><strong>Buat Baru:</strong> Membuat kelas baru dengan nomor urut berikutnya (misal: nomor 2)</li>
                            <li><strong>Batal:</strong> Kembali ke halaman daftar kelas tanpa melakukan perubahan</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" id="btnCancelModal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <div>
                        <button type="button" class="btn btn-success" id="btnRestoreModal" data-id="{{ session('soft_deleted_kelas')['id'] }}">
                            <i class="fas fa-undo"></i> Restore Kelas yang Dihapus
                        </button>
                        <button type="button" class="btn btn-primary" id="btnForceCreateModal">
                            <i class="fas fa-plus-circle"></i> Buat Kelas Baru
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
@stop

@section('js')
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        $(document).ready(function() {
            // Show modal if there's soft deleted kelas warning
            @if(session('warning') && session('soft_deleted_kelas'))
                $('#kelasDeletedModal').modal('show');
            @endif

            // Toggle jurusan field based on kurikulum selection
            $('#kurikulum_id').on('change', function() {
                let hasJurusan = $(this).find(':selected').data('has-jurusan');
                if (hasJurusan == 1) {
                    $('#jurusan-group').show();
                    $('#jurusan_id').prop('required', true);
                } else {
                    $('#jurusan-group').hide();
                    $('#jurusan_id').prop('required', false).val('');
                }
            });

            // Trigger on page load if old value exists
            if ($('#kurikulum_id').val()) {
                $('#kurikulum_id').trigger('change');
            }

            // Handle Cancel Modal Button
            $('#btnCancelModal').on('click', function() {
                Swal.fire({
                    title: 'Konfirmasi Batal',
                    text: 'Anda akan kembali ke daftar kelas tanpa menyimpan perubahan.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#6c757d',
                    cancelButtonColor: '#007bff',
                    confirmButtonText: '<i class="fas fa-check"></i> Ya, Kembali',
                    cancelButtonText: '<i class="fas fa-times"></i> Tetap di Sini'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '{{ route('admin.kelas.index') }}';
                    }
                });
            });

            // Handle Restore Button in Modal
            $('#btnRestoreModal').on('click', function() {
                const kelasId = $(this).data('id');
                const $btn = $(this);
                
                Swal.fire({
                    title: 'Konfirmasi Restore',
                    html: '<p>Kelas yang sudah dihapus akan dikembalikan beserta data siswa (jika ada).</p><p class="text-muted">Yakin ingin me-restore kelas ini?</p>',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-undo"></i> Ya, Restore!',
                    cancelButtonText: '<i class="fas fa-times"></i> Batal',
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return $.ajax({
                            url: '{{ url("admin/kelas") }}/' + kelasId + '/restore',
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            }
                        });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed) {
                        if (result.value.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: result.value.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.href = result.value.redirect;
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: result.value.message
                            });
                        }
                    }
                }).catch((error) => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: error.responseJSON?.message || 'Terjadi kesalahan saat restore kelas.'
                    });
                });
            });
            
            // Handle Force Create Button in Modal
            $('#btnForceCreateModal').on('click', function() {
                Swal.fire({
                    title: 'Konfirmasi Buat Baru',
                    html: '<p>Kelas baru akan dibuat dengan nomor urut yang berbeda.</p><p class="text-muted">Kelas lama tetap dalam status terhapus.</p>',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#007bff',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-plus-circle"></i> Ya, Buat Baru!',
                    cancelButtonText: '<i class="fas fa-times"></i> Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Close modal first
                        $('#kelasDeletedModal').modal('hide');
                        
                        // Add hidden input for force_create flag
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'force_create',
                            value: '1'
                        }).appendTo('#kelasForm');
                        
                        // Show loading
                        Swal.fire({
                            title: 'Memproses...',
                            text: 'Mohon tunggu sebentar',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        
                        // Submit form
                        $('#kelasForm').submit();
                    }
                });
            });

            // Debug form submission
            $('#kelasForm').on('submit', function(e) {
                console.log('Form is being submitted...');
                console.log('Form action:', $(this).attr('action'));
                console.log('Form method:', $(this).attr('method'));
                
                // Check required fields
                let isValid = true;
                $(this).find('[required]').each(function() {
                    if (!$(this).val()) {
                        console.log('Missing required field:', $(this).attr('name'));
                        isValid = false;
                    }
                });
                
                if (!isValid) {
                    console.log('Form validation failed');
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Form Belum Lengkap',
                        text: 'Mohon lengkapi semua field yang wajib diisi (*)',
                        confirmButtonColor: '#007bff'
                    });
                    return false;
                }
                
                console.log('Form validation passed, submitting...');
                // Let the form submit normally
            });
        });
    </script>
@stop

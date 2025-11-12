@extends('adminlte::page')

@section('title', 'Profil GTK')

@section('content_header')
    <h1><i class="fas fa-user-circle"></i> Profil Saya</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            {{-- Tabs for Data Diri and Data Kepegawaian --}}
            <div class="card card-primary card-outline card-outline-tabs">
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs" id="custom-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="data-diri-tab" data-toggle="pill" href="#data-diri" role="tab">
                                <i class="fas fa-user"></i> Data Diri
                                @if(!$gtk->data_diri_completed)
                                    <span class="badge badge-warning ml-1">Belum Lengkap</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="data-kepeg-tab" data-toggle="pill" href="#data-kepeg" role="tab">
                                <i class="fas fa-briefcase"></i> Data Kepegawaian
                                @if(!$gtk->data_kepeg_completed)
                                    <span class="badge badge-warning ml-1">Belum Lengkap</span>
                                @endif
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="custom-tabs-tabContent">
                        {{-- DATA DIRI TAB --}}
                        <div class="tab-pane fade show active" id="data-diri" role="tabpanel">
                            <form id="formDataDiri" action="{{ route('admin.gtk.profile.diri.update') }}" method="POST">
                                @csrf
                                @method('PUT')

                                {{-- Card: Data Pribadi --}}
                                <div class="card card-outline card-primary">
                                    <div class="card-header">
                                        <h3 class="card-title"><i class="fas fa-id-card"></i> Data Pribadi</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="nama_lengkap">
                                                        <i class="fas fa-user text-primary"></i> Nama Lengkap 
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" 
                                                           class="form-control @error('nama_lengkap') is-invalid @enderror" 
                                                           id="nama_lengkap" 
                                                           name="nama_lengkap" 
                                                           value="{{ old('nama_lengkap', $gtk->nama_lengkap) }}"
                                                           placeholder="Nama lengkap sesuai KTP"
                                                           required>
                                                    @error('nama_lengkap')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="nik">
                                                        <i class="fas fa-id-card-alt text-primary"></i> NIK 
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" 
                                                           class="form-control @error('nik') is-invalid @enderror" 
                                                           id="nik" 
                                                           name="nik" 
                                                           value="{{ old('nik', $gtk->nik) }}"
                                                           maxlength="16"
                                                           placeholder="16 digit NIK"
                                                           required>
                                                    @error('nik')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                    <small class="text-muted">
                                                        <i class="fas fa-info-circle"></i> 16 digit angka sesuai KTP
                                                    </small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="jenis_kelamin">
                                                        <i class="fas fa-venus-mars text-primary"></i> Jenis Kelamin 
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <select class="form-control @error('jenis_kelamin') is-invalid @enderror" 
                                                            id="jenis_kelamin" 
                                                            name="jenis_kelamin"
                                                            required>
                                                        <option value="">-- Pilih --</option>
                                                        <option value="L" {{ old('jenis_kelamin', $gtk->jenis_kelamin) == 'L' ? 'selected' : '' }}>
                                                            <i class="fas fa-male"></i> Laki-laki
                                                        </option>
                                                        <option value="P" {{ old('jenis_kelamin', $gtk->jenis_kelamin) == 'P' ? 'selected' : '' }}>
                                                            <i class="fas fa-female"></i> Perempuan
                                                        </option>
                                                    </select>
                                                    @error('jenis_kelamin')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="tempat_lahir">
                                                        <i class="fas fa-map-marker-alt text-primary"></i> Tempat Lahir
                                                    </label>
                                                    <input type="text" 
                                                           class="form-control @error('tempat_lahir') is-invalid @enderror" 
                                                           id="tempat_lahir" 
                                                           name="tempat_lahir" 
                                                           value="{{ old('tempat_lahir', $gtk->tempat_lahir) }}"
                                                           placeholder="Kota/Kabupaten kelahiran">
                                                    @error('tempat_lahir')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="tanggal_lahir">
                                                        <i class="fas fa-calendar text-primary"></i> Tanggal Lahir
                                                    </label>
                                                    <input type="date" 
                                                           class="form-control @error('tanggal_lahir') is-invalid @enderror" 
                                                           id="tanggal_lahir" 
                                                           name="tanggal_lahir" 
                                                           value="{{ old('tanggal_lahir', $gtk->tanggal_lahir ? $gtk->tanggal_lahir->format('Y-m-d') : '') }}">
                                                    @error('tanggal_lahir')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Card: Alamat Tempat Tinggal --}}
                                <div class="card card-outline card-info">
                                    <div class="card-header">
                                        <h3 class="card-title"><i class="fas fa-home"></i> Alamat Tempat Tinggal</h3>
                                    </div>
                                    <div class="card-body">

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="provinsi_id">Provinsi</label>
                                            <select class="form-control select2" 
                                                    id="provinsi_id" 
                                                    name="provinsi_id">
                                                <option value="">-- Pilih Provinsi --</option>
                                                @foreach($provinsiList as $provinsi)
                                                    <option value="{{ $provinsi->code }}" 
                                                            {{ old('provinsi_id', $gtk->provinsi_id) == $provinsi->code ? 'selected' : '' }}>
                                                        {{ $provinsi->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="kabupaten_id">
                                                <i class="fas fa-city text-info"></i> Kabupaten/Kota
                                            </label>
                                            <select class="form-control select2" 
                                                    id="kabupaten_id" 
                                                    name="kabupaten_id">
                                                <option value="">-- Pilih Kabupaten/Kota --</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="kecamatan_id">
                                                <i class="fas fa-building text-info"></i> Kecamatan
                                            </label>
                                            <select class="form-control select2" 
                                                    id="kecamatan_id" 
                                                    name="kecamatan_id">
                                                <option value="">-- Pilih Kecamatan --</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="kelurahan_id">
                                                <i class="fas fa-map-marked-alt text-info"></i> Kelurahan/Desa
                                            </label>
                                            <select class="form-control select2" 
                                                    id="kelurahan_id" 
                                                    name="kelurahan_id">
                                                <option value="">-- Pilih Kelurahan/Desa --</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-7">
                                        <div class="form-group">
                                            <label for="alamat">
                                                <i class="fas fa-road text-info"></i> Alamat Lengkap 
                                                <small class="text-muted">(Jalan, RT/RW, No. Rumah)</small>
                                            </label>
                                            <textarea class="form-control" 
                                                      id="alamat" 
                                                      name="alamat" 
                                                      rows="2" 
                                                      placeholder="Contoh: Jl. Merdeka No. 123, RT 02/RW 05">{{ old('alamat', $gtk->alamat) }}</textarea>
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="rt">
                                                <i class="fas fa-hashtag text-info"></i> RT
                                            </label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="rt" 
                                                   name="rt" 
                                                   value="{{ old('rt', $gtk->rt) }}"
                                                   maxlength="3"
                                                   placeholder="001">
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="rw">
                                                <i class="fas fa-hashtag text-info"></i> RW
                                            </label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="rw" 
                                                   name="rw" 
                                                   value="{{ old('rw', $gtk->rw) }}"
                                                   maxlength="3"
                                                   placeholder="001">
                                        </div>
                                    </div>

                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <label for="kodepos">
                                                <i class="fas fa-envelope text-info"></i> Kodepos
                                            </label>
                                            <input type="text" 
                                                   class="form-control text-center font-weight-bold @error('kodepos') is-invalid @enderror" 
                                                   id="kodepos" 
                                                   name="kodepos" 
                                                   value="{{ old('kodepos', $gtk->kodepos) }}"
                                                   maxlength="5"
                                                   placeholder="00000"
                                                   readonly
                                                   style="background-color: #e9ecef;">
                                            @error('kodepos')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-info mb-0">
                                    <i class="icon fas fa-info-circle"></i>
                                    <strong>Petunjuk:</strong> 
                                    Pilih wilayah secara berurutan dari Provinsi → Kabupaten/Kota → Kecamatan → Kelurahan/Desa. 
                                    Kode pos akan terisi otomatis setelah memilih kelurahan.
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="row">
                            <div class="col-12">
                                <div class="float-right">
                                    <button type="submit" class="btn btn-primary btn-lg" id="btnSaveDataDiri">
                                        <i class="fas fa-save"></i> Simpan Data Diri
                                    </button>
                                    <a href="{{ route('admin.gtk.dashboard') }}" class="btn btn-default btn-lg">
                                        <i class="fas fa-times"></i> Batal
                                    </a>
                                </div>
                            </div>
                        </div>
                            </form>
                        </div>

                        {{-- DATA KEPEGAWAIAN TAB --}}
                        <div class="tab-pane fade" id="data-kepeg" role="tabpanel">
                            <form id="formDataKepeg" action="{{ route('admin.gtk.profile.kepeg.update') }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nuptk">NUPTK</label>
                                            <input type="text" 
                                                   class="form-control @error('nuptk') is-invalid @enderror" 
                                                   id="nuptk" 
                                                   name="nuptk" 
                                                   value="{{ old('nuptk', $gtk->nuptk) }}"
                                                   maxlength="16">
                                            @error('nuptk')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                            <small class="text-muted">16 digit angka (opsional)</small>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nip">NIP</label>
                                            <input type="text" 
                                                   class="form-control @error('nip') is-invalid @enderror" 
                                                   id="nip" 
                                                   name="nip" 
                                                   value="{{ old('nip', $gtk->nip) }}"
                                                   maxlength="18">
                                            @error('nip')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                            <small class="text-muted">Maksimal 18 karakter (opsional)</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="status_kepegawaian">Status Kepegawaian</label>
                                            <select class="form-control @error('status_kepegawaian') is-invalid @enderror" 
                                                    id="status_kepegawaian" 
                                                    name="status_kepegawaian">
                                                <option value="">-- Pilih Status --</option>
                                                @foreach($statusKepegOptions as $status)
                                                    <option value="{{ $status }}" 
                                                            {{ old('status_kepegawaian', $gtk->status_kepegawaian) == $status ? 'selected' : '' }}>
                                                        {{ $status }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('status_kepegawaian')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="jabatan">Jabatan</label>
                                            <select class="form-control @error('jabatan') is-invalid @enderror" 
                                                    id="jabatan" 
                                                    name="jabatan">
                                                <option value="">-- Pilih Jabatan --</option>
                                                @foreach($jabatanOptions as $jabatan)
                                                    <option value="{{ $jabatan }}" 
                                                            {{ old('jabatan', $gtk->jabatan) == $jabatan ? 'selected' : '' }}>
                                                        {{ $jabatan }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('jabatan')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="tmt_kerja">TMT Kerja</label>
                                            <input type="date" 
                                                   class="form-control @error('tmt_kerja') is-invalid @enderror" 
                                                   id="tmt_kerja" 
                                                   name="tmt_kerja" 
                                                   value="{{ old('tmt_kerja', $gtk->tmt_kerja ? $gtk->tmt_kerja->format('Y-m-d') : '') }}">
                                            @error('tmt_kerja')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                            <small class="text-muted">Terhitung Mulai Tanggal</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-info">
                                    <h5><i class="icon fas fa-info-circle"></i> Informasi</h5>
                                    <p class="mb-0">Lengkapi data kepegawaian Anda untuk keperluan administrasi dan pelaporan.</p>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-success btn-lg" id="btnSaveDataKepeg">
                                        <i class="fas fa-save"></i> Simpan Data Kepegawaian
                                    </button>
                                    <a href="{{ route('admin.gtk.dashboard') }}" class="btn btn-default btn-lg">
                                        <i class="fas fa-times"></i> Batal
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <style>
        .card-outline {
            border-top: 3px solid;
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .form-group label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        .form-group label i {
            margin-right: 5px;
        }
        .select2-container--bootstrap4 .select2-selection {
            height: calc(2.25rem + 2px) !important;
        }
        .alert-info {
            border-left: 4px solid #17a2b8;
        }
        #kodepos {
            font-size: 1.1rem;
            letter-spacing: 2px;
        }
        .btn-lg {
            padding: 0.5rem 1.5rem;
            font-size: 1rem;
        }
        .nav-tabs .nav-link {
            font-weight: 500;
        }
        .nav-tabs .nav-link.active {
            font-weight: 700;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        // Configure Toastr
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: "toast-top-right",
            showDuration: "300",
            hideDuration: "1000",
            timeOut: "3000",
            extendedTimeOut: "1000"
        };

        $(document).ready(function() {
            // Initialize Select2
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: function() {
                    return $(this).data('placeholder');
                }
            });

            // Load initial data if exists
            const initialKabupaten = '{{ old("kabupaten_id", $gtk->kabupaten_id) }}';
            const initialKecamatan = '{{ old("kecamatan_id", $gtk->kecamatan_id) }}';
            const initialKelurahan = '{{ old("kelurahan_id", $gtk->kelurahan_id) }}';

            // =============================
            // AJAX FORM SUBMISSION
            // =============================
            
            // Form Data Diri
            $('#formDataDiri').on('submit', function(e) {
                e.preventDefault();
                
                const $btn = $('#btnSaveDataDiri');
                const originalText = $btn.html();
                
                // Disable button and show loading
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
                
                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: $(this).serialize(),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        toastr.success(response.message, 'Berhasil!', {
                            closeButton: true,
                            progressBar: true,
                            timeOut: 3000
                        });
                        
                        // Reload after 1.5 seconds
                        setTimeout(function() {
                            window.location.reload();
                        }, 1500);
                    },
                    error: function(xhr) {
                        let errorMessage = 'Terjadi kesalahan saat menyimpan data.';
                        
                        if (xhr.status === 422) {
                            // Validation errors
                            const errors = xhr.responseJSON.errors;
                            let errorList = '<ul class="mb-0">';
                            $.each(errors, function(field, messages) {
                                $.each(messages, function(index, message) {
                                    errorList += '<li>' + message + '</li>';
                                });
                            });
                            errorList += '</ul>';
                            errorMessage = errorList;
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        
                        toastr.error(errorMessage, 'Gagal!', {
                            closeButton: true,
                            progressBar: true,
                            timeOut: 5000,
                            escapeHtml: false
                        });
                        
                        // Re-enable button
                        $btn.prop('disabled', false).html(originalText);
                    }
                });
            });
            
            // Form Data Kepegawaian
            $('#formDataKepeg').on('submit', function(e) {
                e.preventDefault();
                
                const $btn = $('#btnSaveDataKepeg');
                const originalText = $btn.html();
                
                // Disable button and show loading
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
                
                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: $(this).serialize(),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        toastr.success(response.message, 'Berhasil!', {
                            closeButton: true,
                            progressBar: true,
                            timeOut: 3000
                        });
                        
                        // Reload after 1.5 seconds
                        setTimeout(function() {
                            window.location.reload();
                        }, 1500);
                    },
                    error: function(xhr) {
                        let errorMessage = 'Terjadi kesalahan saat menyimpan data.';
                        
                        if (xhr.status === 422) {
                            // Validation errors
                            const errors = xhr.responseJSON.errors;
                            let errorList = '<ul class="mb-0">';
                            $.each(errors, function(field, messages) {
                                $.each(messages, function(index, message) {
                                    errorList += '<li>' + message + '</li>';
                                });
                            });
                            errorList += '</ul>';
                            errorMessage = errorList;
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        
                        toastr.error(errorMessage, 'Gagal!', {
                            closeButton: true,
                            progressBar: true,
                            timeOut: 5000,
                            escapeHtml: false
                        });
                        
                        // Re-enable button
                        $btn.prop('disabled', false).html(originalText);
                    }
                });
            });

            // =============================
            // CASCADE DROPDOWN WILAYAH
            // =============================

            // Cascade: Provinsi -> Kabupaten
            $('#provinsi_id').on('change', function() {
                const provinsiCode = $(this).val();
                $('#kabupaten_id, #kecamatan_id, #kelurahan_id').html('<option value="">-- Loading... --</option>').prop('disabled', true);
                $('#kodepos').val('');

                if (provinsiCode) {
                    $.get(`{{ url('admin/gtk/api/cities') }}/${provinsiCode}`, function(data) {
                        let options = '<option value="">-- Pilih Kabupaten/Kota --</option>';
                        data.forEach(function(item) {
                            const selected = item.code == initialKabupaten ? 'selected' : '';
                            options += `<option value="${item.code}" ${selected}>${item.name}</option>`;
                        });
                        $('#kabupaten_id').html(options).prop('disabled', false);
                        $('#kecamatan_id').html('<option value="">-- Pilih Kecamatan --</option>').prop('disabled', true);
                        $('#kelurahan_id').html('<option value="">-- Pilih Kelurahan/Desa --</option>').prop('disabled', true);
                        
                        if (initialKabupaten) {
                            $('#kabupaten_id').trigger('change');
                        }
                    }).fail(function() {
                        alert('Gagal memuat data kabupaten/kota');
                        $('#kabupaten_id').html('<option value="">-- Pilih Kabupaten/Kota --</option>').prop('disabled', false);
                    });
                } else {
                    $('#kabupaten_id').html('<option value="">-- Pilih Kabupaten/Kota --</option>').prop('disabled', false);
                    $('#kecamatan_id').html('<option value="">-- Pilih Kecamatan --</option>').prop('disabled', true);
                    $('#kelurahan_id').html('<option value="">-- Pilih Kelurahan/Desa --</option>').prop('disabled', true);
                }
            });

            // Cascade: Kabupaten -> Kecamatan
            $('#kabupaten_id').on('change', function() {
                const kabupatenCode = $(this).val();
                $('#kecamatan_id, #kelurahan_id').html('<option value="">-- Loading... --</option>').prop('disabled', true);
                $('#kodepos').val('');

                if (kabupatenCode) {
                    $.get(`{{ url('admin/gtk/api/districts') }}/${kabupatenCode}`, function(data) {
                        let options = '<option value="">-- Pilih Kecamatan --</option>';
                        data.forEach(function(item) {
                            const selected = item.code == initialKecamatan ? 'selected' : '';
                            options += `<option value="${item.code}" ${selected}>${item.name}</option>`;
                        });
                        $('#kecamatan_id').html(options).prop('disabled', false);
                        $('#kelurahan_id').html('<option value="">-- Pilih Kelurahan/Desa --</option>').prop('disabled', true);
                        
                        if (initialKecamatan) {
                            $('#kecamatan_id').trigger('change');
                        }
                    }).fail(function() {
                        alert('Gagal memuat data kecamatan');
                        $('#kecamatan_id').html('<option value="">-- Pilih Kecamatan --</option>').prop('disabled', false);
                    });
                } else {
                    $('#kecamatan_id').html('<option value="">-- Pilih Kecamatan --</option>').prop('disabled', false);
                    $('#kelurahan_id').html('<option value="">-- Pilih Kelurahan/Desa --</option>').prop('disabled', true);
                }
            });

            // Cascade: Kecamatan -> Kelurahan + Auto-fill Kodepos
            $('#kecamatan_id').on('change', function() {
                const kecamatanCode = $(this).val();
                $('#kelurahan_id').html('<option value="">-- Loading... --</option>').prop('disabled', true);
                $('#kodepos').val('');

                if (kecamatanCode) {
                                    if (kecamatanCode) {
                    $.get(`{{ url('admin/gtk/api/villages') }}/${kecamatanCode}`, function(data) {
                        let options = '<option value="">-- Pilih Kelurahan/Desa --</option>';
                        data.forEach(function(item) {
                            const selected = item.code == initialKelurahan ? 'selected' : '';
                            // Get postal code from meta if available
                            const postalCode = item.meta && item.meta.pos ? item.meta.pos : '';
                            options += `<option value="${item.code}" data-postal="${postalCode}" ${selected}>${item.name}</option>`;
                        });
                        $('#kelurahan_id').html(options).prop('disabled', false);
                        
                        // Trigger auto-fill kodepos if there's initial value
                        if (initialKelurahan) {
                            $('#kelurahan_id').trigger('change');
                        }
                    }).fail(function() {
                        alert('Gagal memuat data kelurahan/desa');
                        $('#kelurahan_id').html('<option value="">-- Pilih Kelurahan/Desa --</option>').prop('disabled', false);
                    });
                } else {
                    $('#kelurahan_id').html('<option value="">-- Pilih Kelurahan/Desa --</option>').prop('disabled', true);
                }
            });

            // Auto-fill Kodepos when Kelurahan selected
            $('#kelurahan_id').on('change', function() {
                const selectedOption = $(this).find(':selected');
                const kelurahanCode = selectedOption.val();
                
                if (kelurahanCode) {
                    // Get postal code from data attribute (from meta_json)
                    const postalCode = selectedOption.data('postal');
                    if (postalCode) {
                        $('#kodepos').val(postalCode);
                    } else {
                        // Fallback: don't auto-fill if not available
                        $('#kodepos').val('');
                    }
                } else {
                    $('#kodepos').val('');
                }
            });
                } else {
                    $('#kelurahan_id').html('<option value="">-- Pilih Kelurahan/Desa --</option>').prop('disabled', false);
                }
            });

            // Auto-fill Kodepos when Kelurahan selected
            $('#kelurahan_id').on('change', function() {
                const selectedOption = $(this).find(':selected');
                const kelurahanCode = selectedOption.val();
                
                if (kelurahanCode) {
                    // Extract postal code from village code (last 5 digits typically)
                    // Indonesian village codes are 10 digits: PPKKDDVVVV (Province, City, District, Village)
                    // We'll use a simple approach: get from meta or use default
                    const postalCode = selectedOption.data('postal') || kelurahanCode.substring(0, 5);
                    $('#kodepos').val(postalCode);
                } else {
                    $('#kodepos').val('');
                }
            });

            // Trigger initial load
            if ($('#provinsi_id').val()) {
                $('#provinsi_id').trigger('change');
            }

            // Hash navigation for tabs
            if (window.location.hash) {
                $(`.nav-link[href="${window.location.hash}"]`).tab('show');
            }
        });
    </script>
@stop

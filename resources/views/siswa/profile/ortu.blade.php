@extends('adminlte::page')

@section('title', 'Data Orangtua - SIMANSA')

@section('content_header')
    <h1><i class="fas fa-users"></i> Data Orangtua</h1>
@stop

@section('content')
<!-- Info Progress -->
<div class="row">
    <div class="col-12">
        <div class="callout callout-info">
            <h5><i class="fas fa-info-circle"></i> Petunjuk Pengisian</h5>
            <p class="mb-0">
                <strong>Langkah 1:</strong> Lengkapi data orangtua Anda dengan benar dan lengkap. Data ini digunakan untuk keperluan administrasi dan komunikasi dengan pihak sekolah. 
                Setelah selesai, Anda akan diarahkan untuk melengkapi <strong>Data Diri</strong> kemudian <strong>Upload Dokumen</strong>.
            </p>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('siswa.profile.ortu.update') }}">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-12">
            <!-- Kartu Keluarga -->
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-id-card-alt"></i> Data Kartu Keluarga
                    </h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="no_kk">
                            No. Kartu Keluarga
                            <small class="text-muted">(16 digit)</small>
                        </label>
                        <input type="text" name="no_kk" id="no_kk" 
                               class="form-control @error('no_kk') is-invalid @enderror" 
                               value="{{ old('no_kk', $ortu->no_kk ?? '') }}" 
                               placeholder="3401xxxxxxxxxxxx"
                               maxlength="16">
                        @error('no_kk')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <!-- Data Ayah -->
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-male"></i> Data Ayah
                    </h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="status_ayah">
                            Status Ayah 
                            <span class="text-danger">*</span>
                        </label>
                        <select name="status_ayah" id="status_ayah" 
                                class="form-control @error('status_ayah') is-invalid @enderror" required>
                            <option value="">Pilih Status</option>
                            <option value="masih_hidup" {{ old('status_ayah', $ortu->status_ayah ?? '') == 'masih_hidup' ? 'selected' : '' }}>
                                Masih Hidup
                            </option>
                            <option value="meninggal" {{ old('status_ayah', $ortu->status_ayah ?? '') == 'meninggal' ? 'selected' : '' }}>
                                Meninggal
                            </option>
                        </select>
                        @error('status_ayah')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="nama_ayah">
                            Nama Lengkap Ayah 
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nama_ayah" id="nama_ayah" 
                               class="form-control @error('nama_ayah') is-invalid @enderror" 
                               value="{{ old('nama_ayah', $ortu->nama_ayah ?? '') }}" 
                               placeholder="Nama lengkap ayah kandung" required>
                        @error('nama_ayah')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <!-- Form fields untuk ayah masih hidup -->
                    <div id="form-ayah-hidup" style="display: none;">
                        <hr class="my-3">
                        <small class="text-muted d-block mb-3">
                            <i class="fas fa-info-circle"></i> Lengkapi data berikut jika ayah masih hidup
                        </small>

                        <div class="form-group">
                            <label for="nik_ayah">
                                NIK Ayah
                                <small class="text-muted">(16 digit)</small>
                            </label>
                            <input type="text" name="nik_ayah" id="nik_ayah" 
                                   class="form-control @error('nik_ayah') is-invalid @enderror" 
                                   value="{{ old('nik_ayah', $ortu->nik_ayah ?? '') }}" 
                                   placeholder="3401xxxxxxxxxxxx"
                                   maxlength="16">
                            @error('nik_ayah')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="hp_ayah">No. HP Ayah</label>
                            <input type="text" name="hp_ayah" id="hp_ayah" 
                                   class="form-control @error('hp_ayah') is-invalid @enderror" 
                                   value="{{ old('hp_ayah', $ortu->hp_ayah ?? '') }}" 
                                   placeholder="08xxxxxxxxxx"
                                   maxlength="15">
                            @error('hp_ayah')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="pekerjaan_ayah">Pekerjaan Ayah</label>
                            <select name="pekerjaan_ayah" id="pekerjaan_ayah" 
                                    class="form-control @error('pekerjaan_ayah') is-invalid @enderror">
                                <option value="">Pilih Pekerjaan</option>
                                @foreach($pekerjaan as $key => $value)
                                    <option value="{{ $key }}" {{ old('pekerjaan_ayah', $ortu->pekerjaan_ayah ?? '') == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                            @error('pekerjaan_ayah')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="penghasilan_ayah">Penghasilan Ayah</label>
                            <select name="penghasilan_ayah" id="penghasilan_ayah" 
                                    class="form-control @error('penghasilan_ayah') is-invalid @enderror">
                                <option value="">Pilih Penghasilan</option>
                                @foreach($penghasilan as $key => $value)
                                    <option value="{{ $key }}" {{ old('penghasilan_ayah', $ortu->penghasilan_ayah ?? '') == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                            @error('penghasilan_ayah')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <!-- Data Ibu -->
            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-female"></i> Data Ibu
                    </h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="status_ibu">
                            Status Ibu 
                            <span class="text-danger">*</span>
                        </label>
                        <select name="status_ibu" id="status_ibu" 
                                class="form-control @error('status_ibu') is-invalid @enderror" required>
                            <option value="">Pilih Status</option>
                            <option value="masih_hidup" {{ old('status_ibu', $ortu->status_ibu ?? '') == 'masih_hidup' ? 'selected' : '' }}>
                                Masih Hidup
                            </option>
                            <option value="meninggal" {{ old('status_ibu', $ortu->status_ibu ?? '') == 'meninggal' ? 'selected' : '' }}>
                                Meninggal
                            </option>
                        </select>
                        @error('status_ibu')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="nama_ibu">
                            Nama Lengkap Ibu 
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nama_ibu" id="nama_ibu" 
                               class="form-control @error('nama_ibu') is-invalid @enderror" 
                               value="{{ old('nama_ibu', $ortu->nama_ibu ?? '') }}" 
                               placeholder="Nama lengkap ibu kandung" required>
                        @error('nama_ibu')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <!-- Form fields untuk ibu masih hidup -->
                    <div id="form-ibu-hidup" style="display: none;">
                        <hr class="my-3">
                        <small class="text-muted d-block mb-3">
                            <i class="fas fa-info-circle"></i> Lengkapi data berikut jika ibu masih hidup
                        </small>

                        <div class="form-group">
                            <label for="nik_ibu">
                                NIK Ibu
                                <small class="text-muted">(16 digit)</small>
                            </label>
                            <input type="text" name="nik_ibu" id="nik_ibu" 
                                   class="form-control @error('nik_ibu') is-invalid @enderror" 
                                   value="{{ old('nik_ibu', $ortu->nik_ibu ?? '') }}" 
                                   placeholder="3401xxxxxxxxxxxx"
                                   maxlength="16">
                            @error('nik_ibu')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="hp_ibu">No. HP Ibu</label>
                            <input type="text" name="hp_ibu" id="hp_ibu" 
                                   class="form-control @error('hp_ibu') is-invalid @enderror" 
                                   value="{{ old('hp_ibu', $ortu->hp_ibu ?? '') }}" 
                                   placeholder="08xxxxxxxxxx"
                                   maxlength="15">
                            @error('hp_ibu')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="pekerjaan_ibu">Pekerjaan Ibu</label>
                            <select name="pekerjaan_ibu" id="pekerjaan_ibu" 
                                    class="form-control @error('pekerjaan_ibu') is-invalid @enderror">
                                <option value="">Pilih Pekerjaan</option>
                                @foreach($pekerjaan as $key => $value)
                                    <option value="{{ $key }}" {{ old('pekerjaan_ibu', $ortu->pekerjaan_ibu ?? '') == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                            @error('pekerjaan_ibu')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="penghasilan_ibu">Penghasilan Ibu</label>
                            <select name="penghasilan_ibu" id="penghasilan_ibu" 
                                    class="form-control @error('penghasilan_ibu') is-invalid @enderror">
                                <option value="">Pilih Penghasilan</option>
                                @foreach($penghasilan as $key => $value)
                                    <option value="{{ $key }}" {{ old('penghasilan_ibu', $ortu->penghasilan_ibu ?? '') == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                            @error('penghasilan_ibu')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <!-- Alamat Orangtua -->
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-map-marked-alt"></i> Alamat Orangtua
                    </h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="alamat_ortu">
                            Alamat Lengkap 
                            <span class="text-danger">*</span>
                        </label>
                        <textarea name="alamat_ortu" id="alamat_ortu" rows="3" 
                                  class="form-control @error('alamat_ortu') is-invalid @enderror" 
                                  placeholder="Jalan, No. Rumah, Nama Perumahan/Kompleks" required>{{ old('alamat_ortu', $ortu->alamat_ortu ?? '') }}</textarea>
                        @error('alamat_ortu')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="rt_ortu">
                                    RT <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="rt_ortu" id="rt_ortu" 
                                       class="form-control @error('rt_ortu') is-invalid @enderror" 
                                       value="{{ old('rt_ortu', $ortu->rt_ortu ?? '') }}" 
                                       placeholder="001" 
                                       maxlength="3" required>
                                @error('rt_ortu')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="rw_ortu">
                                    RW <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="rw_ortu" id="rw_ortu" 
                                       class="form-control @error('rw_ortu') is-invalid @enderror" 
                                       value="{{ old('rw_ortu', $ortu->rw_ortu ?? '') }}" 
                                       placeholder="001"
                                       maxlength="3" required>
                                @error('rw_ortu')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="kodepos">
                                    Kode Pos <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="kodepos" id="kodepos" 
                                       class="form-control @error('kodepos') is-invalid @enderror" 
                                       value="{{ old('kodepos', $ortu->kodepos ?? '') }}" 
                                       placeholder="34xxx"
                                       maxlength="5" required>
                                @error('kodepos')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="provinsi_id">
                                    Provinsi <span class="text-danger">*</span>
                                </label>
                                <select name="provinsi_id" id="provinsi_id" 
                                        class="form-control @error('provinsi_id') is-invalid @enderror" required>
                                    <option value="">Pilih Provinsi</option>
                                    @foreach($provinces as $province)
                                        <option value="{{ $province->code }}" 
                                                {{ old('provinsi_id', $ortu->provinsi_id ?? '') == $province->code ? 'selected' : '' }}>
                                            {{ $province->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('provinsi_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="kabupaten_id">
                                    Kabupaten/Kota <span class="text-danger">*</span>
                                </label>
                                <select name="kabupaten_id" id="kabupaten_id" 
                                        class="form-control @error('kabupaten_id') is-invalid @enderror" required>
                                    <option value="">Pilih Kabupaten/Kota</option>
                                </select>
                                @error('kabupaten_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="kecamatan_id">
                                    Kecamatan <span class="text-danger">*</span>
                                </label>
                                <select name="kecamatan_id" id="kecamatan_id" 
                                        class="form-control @error('kecamatan_id') is-invalid @enderror" required>
                                    <option value="">Pilih Kecamatan</option>
                                </select>
                                @error('kecamatan_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="kelurahan_id">
                                    Kelurahan/Desa <span class="text-danger">*</span>
                                </label>
                                <select name="kelurahan_id" id="kelurahan_id" 
                                        class="form-control @error('kelurahan_id') is-invalid @enderror" required>
                                    <option value="">Pilih Kelurahan/Desa</option>
                                </select>
                                @error('kelurahan_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i> Simpan Data Orangtua
                    </button>
                    <a href="{{ route('siswa.dashboard') }}" class="btn btn-secondary btn-lg">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>
@stop

@section('css')
    <style>
        .callout {
            border-left: 5px solid #17a2b8;
            border-radius: 5px;
        }
        
        .card-outline {
            border-top: 3px solid;
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        }
        
        .card-outline.card-primary {
            border-top-color: #007bff;
        }
        
        .card-outline.card-info {
            border-top-color: #17a2b8;
        }
        
        .card-outline.card-warning {
            border-top-color: #ffc107;
        }
        
        .card-outline.card-success {
            border-top-color: #28a745;
        }
        
        .form-group label {
            font-weight: 500;
            color: #495057;
        }
        
        .text-danger {
            color: #dc3545;
        }
        
        .card-footer {
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }
        
        hr {
            border-top: 1px solid #dee2e6;
        }
        
        small.text-muted {
            font-size: 85%;
        }
    </style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Initialize conditional forms
    toggleAyahForm();
    toggleIbuForm();
    
    // Load existing address data if available
    @if(old('provinsi_id', $ortu->provinsi_id ?? ''))
        loadCities('{{ old('provinsi_id', $ortu->provinsi_id) }}', '{{ old('kabupaten_id', $ortu->kabupaten_id ?? '') }}');
    @endif
    
    @if(old('kabupaten_id', $ortu->kabupaten_id ?? ''))
        loadDistricts('{{ old('kabupaten_id', $ortu->kabupaten_id) }}', '{{ old('kecamatan_id', $ortu->kecamatan_id ?? '') }}');
    @endif
    
    @if(old('kecamatan_id', $ortu->kecamatan_id ?? ''))
        loadVillages('{{ old('kecamatan_id', $ortu->kecamatan_id) }}', '{{ old('kelurahan_id', $ortu->kelurahan_id ?? '') }}');
    @endif

    // Event handlers
    $('#status_ayah').change(function() {
        toggleAyahForm();
    });

    $('#status_ibu').change(function() {
        toggleIbuForm();
    });

    $('#provinsi_id').change(function() {
        const provinsiId = $(this).val();
        $('#kabupaten_id').empty().append('<option value="">Pilih Kabupaten/Kota</option>');
        $('#kecamatan_id').empty().append('<option value="">Pilih Kecamatan</option>');
        $('#kelurahan_id').empty().append('<option value="">Pilih Kelurahan/Desa</option>');
        
        if (provinsiId) {
            loadCities(provinsiId);
        }
    });

    $('#kabupaten_id').change(function() {
        const kabupatenId = $(this).val();
        $('#kecamatan_id').empty().append('<option value="">Pilih Kecamatan</option>');
        $('#kelurahan_id').empty().append('<option value="">Pilih Kelurahan/Desa</option>');
        
        if (kabupatenId) {
            loadDistricts(kabupatenId);
        }
    });

    $('#kecamatan_id').change(function() {
        const kecamatanId = $(this).val();
        $('#kelurahan_id').empty().append('<option value="">Pilih Kelurahan/Desa</option>');
        
        if (kecamatanId) {
            loadVillages(kecamatanId);
        }
    });
});

function toggleAyahForm() {
    const status = $('#status_ayah').val();
    if (status === 'masih_hidup') {
        $('#form-ayah-hidup').show();
        $('#form-ayah-hidup input, #form-ayah-hidup select').prop('disabled', false);
    } else {
        $('#form-ayah-hidup').hide();
        $('#form-ayah-hidup input, #form-ayah-hidup select').prop('disabled', true);
    }
}

function toggleIbuForm() {
    const status = $('#status_ibu').val();
    if (status === 'masih_hidup') {
        $('#form-ibu-hidup').show();
        $('#form-ibu-hidup input, #form-ibu-hidup select').prop('disabled', false);
    } else {
        $('#form-ibu-hidup').hide();
        $('#form-ibu-hidup input, #form-ibu-hidup select').prop('disabled', true);
    }
}

function loadCities(provinsiId, selectedValue = '') {
    $.get(`{{ url('siswa/api/cities') }}/${provinsiId}`)
        .done(function(cities) {
            $('#kabupaten_id').empty().append('<option value="">Pilih Kabupaten/Kota</option>');
            cities.forEach(function(city) {
                const selected = selectedValue === city.code ? 'selected' : '';
                $('#kabupaten_id').append(`<option value="${city.code}" ${selected}>${city.name}</option>`);
            });
            
            if (selectedValue) {
                loadDistricts(selectedValue, '{{ old('kecamatan_id', $ortu->kecamatan_id ?? '') }}');
            }
        })
        .fail(function() {
            console.error('Failed to load cities');
        });
}

function loadDistricts(kabupatenId, selectedValue = '') {
    $.get(`{{ url('siswa/api/districts') }}/${kabupatenId}`)
        .done(function(districts) {
            $('#kecamatan_id').empty().append('<option value="">Pilih Kecamatan</option>');
            districts.forEach(function(district) {
                const selected = selectedValue === district.code ? 'selected' : '';
                $('#kecamatan_id').append(`<option value="${district.code}" ${selected}>${district.name}</option>`);
            });
            
            if (selectedValue) {
                loadVillages(selectedValue, '{{ old('kelurahan_id', $ortu->kelurahan_id ?? '') }}');
            }
        })
        .fail(function() {
            console.error('Failed to load districts');
        });
}

function loadVillages(kecamatanId, selectedValue = '') {
    $.get(`{{ url('siswa/api/villages') }}/${kecamatanId}`)
        .done(function(villages) {
            $('#kelurahan_id').empty().append('<option value="">Pilih Kelurahan/Desa</option>');
            villages.forEach(function(village) {
                const selected = selectedValue === village.code ? 'selected' : '';
                $('#kelurahan_id').append(`<option value="${village.code}" ${selected}>${village.name}</option>`);
            });
        })
        .fail(function() {
            console.error('Failed to load villages');
        });
}
</script>
@stop
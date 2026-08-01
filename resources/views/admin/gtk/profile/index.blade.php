@extends('adminlte::page')

@section('title', 'Profil GTK')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-user-circle"></i> Profil Saya</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('admin.gtk.dashboard') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            {{-- Tabs for Data Diri and Data Kepegawaian --}}
            <div class="card card-primary card-outline card-outline-tabs">
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs" id="custom-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="data-diri-tab" data-toggle="pill" href="#data-diri" role="tab" style="color: #007bff;">
                                <i class="fas fa-user"></i> Data Diri
                                @if(!$gtk->data_diri_completed)
                                    <span class="badge badge-danger ml-1">Belum Lengkap</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="data-kepeg-tab" data-toggle="pill" href="#data-kepeg" role="tab" style="color: #495057;">
                                <i class="fas fa-briefcase"></i> Data Kepegawaian
                                @if(!$gtk->data_kepeg_completed)
                                    <span class="badge badge-danger ml-1">Belum Lengkap</span>
                                @endif
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="custom-tabs-tabContent">
                        {{-- DATA DIRI TAB --}}
                        <div class="tab-pane fade show active" id="data-diri" role="tabpanel">
                            <form id="formDataDiri" action="{{ route('admin.gtk.profile.diri.update') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                {{-- Foto Profile --}}
                                <div class="card card-outline card-info mb-3">
                                    <div class="card-header">
                                        <h3 class="card-title"><i class="fas fa-camera"></i> Foto Profile</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-12 col-md-auto text-center mb-3 mb-md-0">
                                                <div class="gtk-foto-frame" id="fotoFrame" title="Klik untuk ganti foto">
                                                    <img id="fotoPreview" src="{{ $gtk->foto_profile_url }}" alt="Foto">
                                                    <div class="gtk-foto-overlay">
                                                        <i class="fas fa-camera"></i>
                                                        <span>Ganti Foto</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="gtk-foto-drop" id="fotoDropZone">
                                                    <i class="fas fa-cloud-upload-alt fa-2x text-primary mb-2"></i>
                                                    <p class="mb-1"><strong>Klik untuk browse</strong> atau tarik &amp; letakkan foto di sini</p>
                                                    <small class="text-muted">JPG / PNG, maks 2 MB. Setelah dipilih Anda dapat <strong>crop, geser, zoom, putar,</strong> dan <strong>atur background</strong> (termasuk transparan).</small>
                                                </div>
                                                <input type="file" class="d-none" name="foto_profile" id="foto_profile"
                                                       accept="image/jpeg,image/png,image/jpg">
                                                <div id="fotoReadyBadge" class="mt-2" style="display:none;">
                                                    <span class="badge badge-success"><i class="fas fa-check"></i> Foto siap — jangan lupa klik <strong>Simpan Data Diri</strong></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

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
                                            <div class="col-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label>
                                                        <i class="fas fa-venus-mars text-primary"></i> Jenis Kelamin 
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="gtk-gender-toggle @error('jenis_kelamin') is-invalid @enderror">
                                                        <input type="radio" id="jk_l" name="jenis_kelamin" value="L" {{ old('jenis_kelamin', $gtk->jenis_kelamin) == 'L' ? 'checked' : '' }} required>
                                                        <label for="jk_l"><i class="fas fa-mars"></i> Laki-laki</label>
                                                        <input type="radio" id="jk_p" name="jenis_kelamin" value="P" {{ old('jenis_kelamin', $gtk->jenis_kelamin) == 'P' ? 'checked' : '' }}>
                                                        <label for="jk_p"><i class="fas fa-venus"></i> Perempuan</label>
                                                    </div>
                                                    @error('jenis_kelamin')
                                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-12 col-sm-6 col-md-4">
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

                                            <div class="col-12 col-md-4">
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
                                    <div class="col-12 col-sm-6 col-lg-3">
                                        <div class="form-group">
                                            <label for="provinsi_id"><i class="fas fa-map text-info"></i> Provinsi</label>
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

                                    <div class="col-12 col-sm-6 col-lg-3">
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

                                    <div class="col-12 col-sm-6 col-lg-3">
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

                                    <div class="col-12 col-sm-6 col-lg-3">
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
                                    <div class="col-12 col-lg-5">
                                        <div class="form-group">
                                            <label for="alamat">
                                                <i class="fas fa-road text-info"></i> Alamat Lengkap 
                                                <small class="text-muted">(Jalan, No. Rumah)</small>
                                            </label>
                                            <textarea class="form-control" 
                                                      id="alamat" 
                                                      name="alamat" 
                                                      rows="2" 
                                                      placeholder="Contoh: Jl. Merdeka No. 123, RT 02/RW 05">{{ old('alamat', $gtk->alamat) }}</textarea>
                                        </div>
                                    </div>

                                    <div class="col-4 col-lg-2">
                                        <div class="form-group">
                                            <label for="rt">
                                                <i class="fas fa-hashtag text-info"></i> RT
                                            </label>
                                            <input type="text" 
                                                   class="form-control text-center" 
                                                   id="rt" 
                                                   name="rt" 
                                                   value="{{ old('rt', $gtk->rt) }}"
                                                   maxlength="3"
                                                   inputmode="numeric"
                                                   placeholder="001">
                                        </div>
                                    </div>

                                    <div class="col-4 col-lg-2">
                                        <div class="form-group">
                                            <label for="rw">
                                                <i class="fas fa-hashtag text-info"></i> RW
                                            </label>
                                            <input type="text" 
                                                   class="form-control text-center" 
                                                   id="rw" 
                                                   name="rw" 
                                                   value="{{ old('rw', $gtk->rw) }}"
                                                   maxlength="3"
                                                   inputmode="numeric"
                                                   placeholder="001">
                                        </div>
                                    </div>

                                    <div class="col-4 col-lg-3">
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

                        {{-- Sticky Action Bar --}}
                        <div class="gtk-sticky-actions">
                            <div class="text-muted small d-none d-sm-block">
                                <i class="fas fa-info-circle"></i> Perubahan belum tersimpan sampai Anda klik Simpan.
                            </div>
                            <div class="d-flex" style="gap:.5rem;">
                                <a href="{{ route('admin.gtk.dashboard') }}" class="btn btn-default">
                                    <i class="fas fa-times"></i> Batal
                                </a>
                                <button type="submit" class="btn btn-primary px-4" id="btnSaveDataDiri">
                                    <i class="fas fa-save"></i> Simpan Data Diri
                                </button>
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

                                <div class="gtk-sticky-actions">
                                    <div class="text-muted small d-none d-sm-block">
                                        <i class="fas fa-info-circle"></i> Perubahan belum tersimpan sampai Anda klik Simpan.
                                    </div>
                                    <div class="d-flex" style="gap:.5rem;">
                                        <a href="{{ route('admin.gtk.dashboard') }}" class="btn btn-default">
                                            <i class="fas fa-times"></i> Batal
                                        </a>
                                        <button type="submit" class="btn btn-success px-4" id="btnSaveDataKepeg">
                                            <i class="fas fa-save"></i> Simpan Data Kepegawaian
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Crop Foto --}}
    <div class="modal fade" id="cropperModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title"><i class="fas fa-crop-alt"></i> Atur Foto Profil</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-2 p-sm-3">
                    <div class="gtk-crop-wrap">
                        <img id="cropperImage" src="" alt="Crop">
                    </div>
                    <div class="d-flex flex-wrap align-items-center justify-content-between mt-3" style="gap:.5rem;">
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary" id="cropZoomOut" title="Perkecil"><i class="fas fa-search-minus"></i></button>
                            <button type="button" class="btn btn-outline-secondary" id="cropZoomIn" title="Perbesar"><i class="fas fa-search-plus"></i></button>
                            <button type="button" class="btn btn-outline-secondary" id="cropRotateLeft" title="Putar kiri"><i class="fas fa-undo"></i></button>
                            <button type="button" class="btn btn-outline-secondary" id="cropRotateRight" title="Putar kanan"><i class="fas fa-redo"></i></button>
                            <button type="button" class="btn btn-outline-secondary" id="cropReset" title="Reset"><i class="fas fa-sync-alt"></i></button>
                        </div>
                        <div class="d-flex align-items-center" style="gap:.4rem;">
                            <span class="small text-muted mr-1"><i class="fas fa-fill-drip"></i> Background:</span>
                            <button type="button" class="gtk-bg-swatch active" data-bg="#ffffff" style="background:#fff;" title="Putih"></button>
                            <button type="button" class="gtk-bg-swatch" data-bg="#dc3545" style="background:#dc3545;" title="Merah"></button>
                            <button type="button" class="gtk-bg-swatch" data-bg="#007bff" style="background:#007bff;" title="Biru"></button>
                            <button type="button" class="gtk-bg-swatch gtk-bg-transparent" data-bg="transparent" title="Transparan"></button>
                            <input type="color" id="cropBgCustom" value="#ffffff" title="Warna custom" style="width:32px;height:32px;border:none;padding:0;background:none;cursor:pointer;">
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="fas fa-hand-paper"></i> Geser foto untuk mengatur posisi, scroll/cubit untuk zoom.
                        Background <strong>transparan</strong> akan disimpan sebagai PNG.
                    </small>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fas fa-times"></i> Batal</button>
                    <button type="button" class="btn btn-primary" id="cropApply"><i class="fas fa-check"></i> Gunakan Foto</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.css">
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
        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.15rem rgba(0, 123, 255, .15);
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
        .nav-tabs .nav-link {
            font-weight: 500;
        }
        .nav-tabs .nav-link.active {
            font-weight: 700;
        }

        /* ===== Foto profil ===== */
        .gtk-foto-frame {
            position: relative;
            width: 140px;
            height: 140px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid #007bff;
            box-shadow: 0 4px 12px rgba(0,0,0,.15);
            cursor: pointer;
            margin: 0 auto;
            background: repeating-conic-gradient(#e9ecef 0% 25%, #fff 0% 50%) 50% / 16px 16px;
        }
        .gtk-foto-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .gtk-foto-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,.45);
            color: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: .8rem;
            opacity: 0;
            transition: opacity .2s;
        }
        .gtk-foto-frame:hover .gtk-foto-overlay { opacity: 1; }
        .gtk-foto-drop {
            border: 2px dashed #adb5bd;
            border-radius: .5rem;
            padding: 1rem 1.25rem;
            text-align: center;
            cursor: pointer;
            transition: border-color .2s, background .2s;
        }
        .gtk-foto-drop:hover, .gtk-foto-drop.dragover {
            border-color: #007bff;
            background: rgba(0,123,255,.05);
        }

        /* ===== Cropper modal ===== */
        .gtk-crop-wrap {
            max-height: 55vh;
            background: repeating-conic-gradient(#e9ecef 0% 25%, #fff 0% 50%) 50% / 16px 16px;
        }
        .gtk-crop-wrap img { max-width: 100%; display: block; }
        .gtk-bg-swatch {
            width: 28px; height: 28px;
            border-radius: 50%;
            border: 2px solid #dee2e6;
            padding: 0;
            cursor: pointer;
        }
        .gtk-bg-swatch.active {
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0,123,255,.35);
        }
        .gtk-bg-transparent {
            background: repeating-conic-gradient(#ced4da 0% 25%, #fff 0% 50%) 50% / 10px 10px;
        }

        /* ===== Gender toggle ===== */
        .gtk-gender-toggle {
            display: flex;
            border: 1px solid #ced4da;
            border-radius: .35rem;
            overflow: hidden;
        }
        .gtk-gender-toggle input[type="radio"] { display: none; }
        .gtk-gender-toggle label {
            flex: 1;
            margin: 0;
            padding: .45rem .5rem;
            text-align: center;
            cursor: pointer;
            font-weight: 500;
            color: #6c757d;
            transition: all .15s;
        }
        .gtk-gender-toggle label + input + label { border-left: 1px solid #ced4da; }
        .gtk-gender-toggle input[value="L"]:checked + label {
            background: #007bff; color: #fff;
        }
        .gtk-gender-toggle input[value="P"]:checked + label {
            background: #e83e8c; color: #fff;
        }

        /* ===== Sticky action bar ===== */
        .gtk-sticky-actions {
            position: sticky;
            bottom: 0;
            z-index: 1020;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            background: rgba(255,255,255,.95);
            backdrop-filter: blur(4px);
            border-top: 1px solid #dee2e6;
            box-shadow: 0 -3px 10px rgba(0,0,0,.06);
            padding: .65rem .75rem;
            margin: 1rem -1.25rem -1.25rem;
            border-radius: 0 0 .25rem .25rem;
        }
        @media (max-width: 575.98px) {
            .gtk-sticky-actions { justify-content: flex-end; }
            .gtk-sticky-actions .btn { flex: 1; }
            .gtk-sticky-actions > .d-flex { width: 100%; }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.js"></script>
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
            // =============================
            // FOTO PROFIL: BROWSE + CROP + BACKGROUND
            // =============================
            let cropper = null;
            let cropBg = '#ffffff'; // 'transparent' atau warna hex
            let cropApplied = false;

            function openFilePicker() { $('#foto_profile').trigger('click'); }
            $('#fotoFrame, #fotoDropZone').on('click', openFilePicker);

            // Drag & drop
            $('#fotoDropZone')
                .on('dragover dragenter', function(e) { e.preventDefault(); $(this).addClass('dragover'); })
                .on('dragleave drop', function(e) { e.preventDefault(); $(this).removeClass('dragover'); })
                .on('drop', function(e) {
                    const files = e.originalEvent.dataTransfer.files;
                    if (files.length) handleFotoFile(files[0]);
                });

            $('#foto_profile').on('change', function() {
                if (this.files.length) handleFotoFile(this.files[0]);
            });

            function handleFotoFile(file) {
                const allowed = ['image/jpeg', 'image/png'];
                if (!allowed.includes(file.type)) {
                    toastr.error('File harus berupa foto (JPG atau PNG). File "' + file.name + '" ditolak.', 'Format tidak didukung');
                    $('#foto_profile').val('');
                    return;
                }
                if (file.size > 2 * 1024 * 1024) {
                    toastr.error('Ukuran foto maksimal 2 MB.', 'File terlalu besar');
                    $('#foto_profile').val('');
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#cropperImage').attr('src', e.target.result);
                    cropApplied = false;
                    $('#cropperModal').modal('show');
                };
                reader.readAsDataURL(file);
            }

            $('#cropperModal').on('shown.bs.modal', function() {
                if (cropper) cropper.destroy();
                cropper = new Cropper(document.getElementById('cropperImage'), {
                    aspectRatio: 1,
                    viewMode: 0,
                    dragMode: 'move',
                    autoCropArea: 0.9,
                    background: false,
                    responsive: true,
                    checkOrientation: true
                });
            }).on('hidden.bs.modal', function() {
                if (cropper) { cropper.destroy(); cropper = null; }
                if (!cropApplied) $('#foto_profile').val(''); // batal = kosongkan input
            });

            // Kontrol crop
            $('#cropZoomIn').on('click', function() { cropper && cropper.zoom(0.1); });
            $('#cropZoomOut').on('click', function() { cropper && cropper.zoom(-0.1); });
            $('#cropRotateLeft').on('click', function() { cropper && cropper.rotate(-90); });
            $('#cropRotateRight').on('click', function() { cropper && cropper.rotate(90); });
            $('#cropReset').on('click', function() { cropper && cropper.reset(); });

            // Pilihan background
            $('.gtk-bg-swatch').on('click', function() {
                $('.gtk-bg-swatch').removeClass('active');
                $(this).addClass('active');
                cropBg = $(this).data('bg');
            });
            $('#cropBgCustom').on('input change', function() {
                $('.gtk-bg-swatch').removeClass('active');
                cropBg = this.value;
            });

            // Terapkan hasil crop ke input file (via DataTransfer)
            $('#cropApply').on('click', function() {
                if (!cropper) return;
                const transparent = cropBg === 'transparent';
                const canvas = cropper.getCroppedCanvas(Object.assign(
                    { width: 600, height: 600, imageSmoothingQuality: 'high' },
                    transparent ? {} : { fillColor: cropBg }
                ));
                const mime = transparent ? 'image/png' : 'image/jpeg';
                canvas.toBlob(function(blob) {
                    if (!blob) { toastr.error('Gagal memproses foto.'); return; }
                    const ext = transparent ? 'png' : 'jpg';
                    const file = new File([blob], 'foto_profil.' + ext, { type: mime });
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    document.getElementById('foto_profile').files = dt.files;

                    $('#fotoPreview').attr('src', canvas.toDataURL(mime));
                    $('#fotoReadyBadge').show();
                    cropApplied = true;
                    $('#cropperModal').modal('hide');
                    toastr.info('Foto siap. Klik Simpan Data Diri untuk mengunggah.', 'Foto diperbarui');
                }, mime, 0.9);
            });

            // Initialize Select2
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: function() {
                    return $(this).data('placeholder');
                }
            });

            // Warna tab (fix accent-white AdminLTE)
            $('#custom-tabs .nav-link').on('shown.bs.tab', function() {
                $('#custom-tabs .nav-link').css('color', '#495057');
                $(this).css('color', '#007bff');
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
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
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

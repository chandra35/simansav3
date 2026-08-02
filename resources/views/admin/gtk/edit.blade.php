@extends('adminlte::page')

@section('title', 'Edit Data GTK')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-user-edit text-primary mr-1"></i> Edit Data GTK</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.gtk.index') }}">Data GTK</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="simansa-gtk-edit">
        <div class="card bg-gradient-primary text-white simansa-gtk-edit__hero">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <div class="simansa-gtk-edit__eyebrow">
                            <i class="fas fa-id-badge mr-1"></i> Profil GTK
                        </div>
                        <h2 id="gtkEditHeroName">{{ $gtk->nama_lengkap }}</h2>
                        <p class="mb-0">Perbarui identitas, kepegawaian, akun pengguna, dan integrasi Kemenag dalam satu ruang kerja.</p>
                    </div>
                    <div class="col-lg-4 mt-3 mt-lg-0">
                        <div class="simansa-gtk-edit__hero-meta">
                            <div class="simansa-gtk-edit__hero-stat">
                                <span>NIK</span>
                                <strong>{{ $gtk->nik ?? '-' }}</strong>
                            </div>
                            <div class="simansa-gtk-edit__hero-stat">
                                <span>Jenis PTK</span>
                                <strong>{{ $gtk->jenis_ptk ?? '-' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row gtk-edit-enterprise-layout">
            <div class="col-md-3">
                <div class="card card-outline card-primary gtk-edit-sidebar">
                    <div class="card-body">
                        @php
                            $hasFoto = !empty($gtk->foto_profile);
                            $initialsBg = $gtk->jenis_kelamin === 'P' ? '#f06292' : '#42a5f5';
                            $initials = collect(explode(' ', $gtk->nama_lengkap ?? 'G'))->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->join('');
                        @endphp

                        <div class="gtk-edit-photo-preview text-center" id="fotoContainer">
                            <img id="fotoPreview"
                                 src="{{ $hasFoto ? $gtk->foto_profile_url : '' }}"
                                 alt="Foto profil {{ $gtk->nama_lengkap }}"
                                 class="gtk-edit-avatar"
                                 style="{{ $hasFoto ? '' : 'display:none;' }}">
                            <div id="fotoInitials"
                                 class="gtk-edit-avatar gtk-edit-avatar--initials"
                                 style="background: {{ $initialsBg }}; display: {{ $hasFoto ? 'none' : 'flex' }};">
                                {{ $initials }}
                            </div>
                            <button type="button" id="btnDeleteFoto"
                                    class="btn btn-danger gtk-edit-photo-delete"
                                    style="{{ $hasFoto ? '' : 'display:none;' }}"
                                    title="Hapus foto" aria-label="Hapus foto profil">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <div class="gtk-edit-profile-name" id="gtkEditSidebarName">{{ $gtk->nama_lengkap }}</div>
                        <div class="gtk-edit-profile-role">{{ $gtk->jenis_ptk ?? 'Jenis PTK belum diisi' }}</div>

                        <div class="gtk-edit-dropzone gtk-edit-dropzone--compact" id="dropZone" role="button" tabindex="0" aria-label="Pilih atau seret foto profil">
                            <input type="file" id="fotoFileInput" accept="image/jpeg,image/png,image/jpg" hidden>
                            <div id="dropZoneContent">
                                <i class="fas fa-cloud-upload-alt text-primary gtk-edit-dropzone__icon"></i>
                                <span>Ganti foto</span>
                                <small>JPG/PNG, maks. 2 MB</small>
                            </div>
                            <div id="uploadProgress" style="display: none;" class="mt-2">
                                <div class="progress gtk-edit-upload-progress">
                                    <div id="uploadProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width: 0%;"></div>
                                </div>
                                <small id="uploadStatusText" class="text-muted mt-1 d-block">Mengupload...</small>
                            </div>
                        </div>

                        <div class="gtk-edit-identity-summary" aria-label="Ringkasan identitas GTK">
                            <div><span>NIK</span><strong>{{ $gtk->nik ?? '-' }}</strong></div>
                            <div><span>NUPTK</span><strong>{{ $gtk->nuptk ?? 'Belum diisi' }}</strong></div>
                        </div>

                        <div class="gtk-edit-nav-label">Kelola data</div>
                        <div class="nav nav-pills flex-column gtk-edit-tabs" id="gtkEditTabs" role="tablist" aria-orientation="vertical">
                            <a class="nav-link active" id="diri-tab" data-toggle="pill" href="#diri" role="tab" aria-controls="diri" aria-selected="true" data-title="Data Pribadi">
                                <i class="fas fa-user"></i><span>Data Pribadi</span><i class="fas fa-chevron-right gtk-edit-nav-arrow"></i>
                            </a>
                            <a class="nav-link" id="kepeg-tab" data-toggle="pill" href="#kepeg" role="tab" aria-controls="kepeg" aria-selected="false" data-title="Data Kepegawaian">
                                <i class="fas fa-briefcase"></i><span>Data Kepegawaian</span><i class="fas fa-chevron-right gtk-edit-nav-arrow"></i>
                            </a>
                            <a class="nav-link" id="akun-tab" data-toggle="pill" href="#akun" role="tab" aria-controls="akun" aria-selected="false" data-title="Akun User">
                                <i class="fas fa-user-lock"></i><span>Akun User</span><i class="fas fa-chevron-right gtk-edit-nav-arrow"></i>
                            </a>
                            <a class="nav-link" id="kemenag-tab" data-toggle="pill" href="#kemenag" role="tab" aria-controls="kemenag" aria-selected="false" data-title="Integrasi Kemenag">
                                <i class="fas fa-sync-alt"></i><span>Integrasi Kemenag</span>
                                @if($gtk->kemenagSync && $gtk->kemenagSync->has_differences)
                                    <span class="badge badge-warning ml-auto">{{ $gtk->kemenagSync->differences_count }}</span>
                                @else
                                    <i class="fas fa-chevron-right gtk-edit-nav-arrow"></i>
                                @endif
                            </a>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('admin.gtk.index') }}" class="btn btn-secondary btn-sm btn-block">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Data GTK
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <div class="card card-outline card-primary gtk-edit-main">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-edit text-primary mr-1"></i><span id="gtkEditSectionTitle">Data Pribadi</span></h3>
                        <small class="gtk-edit-main__subtitle">Lengkapi field yang diperlukan, lalu simpan perubahan.</small>
                    </div>
                    <div class="card-body p-0">
                    <div class="tab-content gtk-edit-content" id="gtkEditTabsContent">
                        <!-- Tab Data Pribadi -->
                        <div class="tab-pane fade show active" id="diri" role="tabpanel">
                            <form id="formDataDiri" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="tab" value="diri">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nik">NIK <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="nik" name="nik" 
                                                   value="{{ $gtk->nik }}" required maxlength="16">
                                            <small class="text-muted">16 digit</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nuptk">NUPTK</label>
                                            <input type="text" class="form-control" id="nuptk" name="nuptk" 
                                                   value="{{ $gtk->nuptk }}" maxlength="20">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="nama_lengkap">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" 
                                           value="{{ $gtk->nama_lengkap }}" required>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="jenis_kelamin">Jenis Kelamin <span class="text-danger">*</span></label>
                                            <select class="form-control" id="jenis_kelamin" name="jenis_kelamin" required>
                                                <option value="">-- Pilih --</option>
                                                <option value="L" {{ $gtk->jenis_kelamin == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                                <option value="P" {{ $gtk->jenis_kelamin == 'P' ? 'selected' : '' }}>Perempuan</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="tempat_lahir">Tempat Lahir</label>
                                            <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" 
                                                   value="{{ $gtk->tempat_lahir }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="tanggal_lahir">Tanggal Lahir</label>
                                            <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" 
                                                   value="{{ $gtk->tanggal_lahir ? \Carbon\Carbon::parse($gtk->tanggal_lahir)->format('Y-m-d') : '' }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nomor_hp">No. HP</label>
                                            <input type="text" class="form-control" id="nomor_hp" name="nomor_hp" 
                                                   value="{{ $gtk->nomor_hp }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   value="{{ $gtk->email }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="alamat">Alamat</label>
                                    <textarea class="form-control" id="alamat" name="alamat" rows="2">{{ $gtk->alamat }}</textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="rt">RT</label>
                                            <input type="text" class="form-control" id="rt" name="rt" 
                                                   value="{{ $gtk->rt }}" maxlength="5">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="rw">RW</label>
                                            <input type="text" class="form-control" id="rw" name="rw" 
                                                   value="{{ $gtk->rw }}" maxlength="5">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="kodepos">Kode Pos</label>
                                            <input type="text" class="form-control" id="kodepos" name="kodepos" 
                                                   value="{{ $gtk->kodepos }}" maxlength="10">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="provinsi_id">Provinsi</label>
                                            <select class="form-control select2" id="provinsi_id" name="provinsi_id">
                                                <option value="">-- Pilih Provinsi --</option>
                                                @foreach($provinces as $prov)
                                                    <option value="{{ $prov->code }}" {{ $gtk->provinsi_id == $prov->code ? 'selected' : '' }}>
                                                        {{ $prov->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="kabupaten_id">Kabupaten/Kota</label>
                                            <select class="form-control select2" id="kabupaten_id" name="kabupaten_id">
                                                <option value="">-- Pilih Kabupaten/Kota --</option>
                                                @foreach($cities as $city)
                                                    <option value="{{ $city->code }}" {{ $gtk->kabupaten_id == $city->code ? 'selected' : '' }}>
                                                        {{ $city->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="kecamatan_id">Kecamatan</label>
                                            <select class="form-control select2" id="kecamatan_id" name="kecamatan_id">
                                                <option value="">-- Pilih Kecamatan --</option>
                                                @foreach($districts as $district)
                                                    <option value="{{ $district->code }}" {{ $gtk->kecamatan_id == $district->code ? 'selected' : '' }}>
                                                        {{ $district->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="kelurahan_id">Kelurahan/Desa</label>
                                            <select class="form-control select2" id="kelurahan_id" name="kelurahan_id">
                                                <option value="">-- Pilih Kelurahan/Desa --</option>
                                                @foreach($villages as $village)
                                                    <option value="{{ $village->code }}" {{ $gtk->kelurahan_id == $village->code ? 'selected' : '' }}>
                                                        {{ $village->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group gtk-edit-form-actions">
                                    <button type="reset" class="btn btn-secondary mr-2">
                                        <i class="fas fa-undo mr-1"></i> Reset
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Simpan Data Pribadi
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Tab Data Kepegawaian -->
                        <div class="tab-pane fade" id="kepeg" role="tabpanel">
                            <form id="formDataKepeg">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="tab" value="kepeg">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nip">NIP</label>
                                            <input type="text" class="form-control" id="nip" name="nip" 
                                                   value="{{ $gtk->nip }}" maxlength="20">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="kategori_ptk">Kategori PTK <span class="text-danger">*</span></label>
                                            <select class="form-control" id="kategori_ptk" name="kategori_ptk" required>
                                                <option value="">-- Pilih --</option>
                                                <option value="Pendidik" {{ $gtk->kategori_ptk == 'Pendidik' ? 'selected' : '' }}>Pendidik</option>
                                                <option value="Tenaga Kependidikan" {{ $gtk->kategori_ptk == 'Tenaga Kependidikan' ? 'selected' : '' }}>Tenaga Kependidikan</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="jenis_ptk">Jenis PTK <span class="text-danger">*</span></label>
                                    <select class="form-control" id="jenis_ptk" name="jenis_ptk" required>
                                        <option value="">-- Pilih --</option>
                                        <option value="Guru Mapel" {{ $gtk->jenis_ptk == 'Guru Mapel' ? 'selected' : '' }}>Guru Mapel</option>
                                        <option value="Guru BK" {{ $gtk->jenis_ptk == 'Guru BK' ? 'selected' : '' }}>Guru BK</option>
                                        <option value="Kepala TU" {{ $gtk->jenis_ptk == 'Kepala TU' ? 'selected' : '' }}>Kepala TU</option>
                                        <option value="Staff TU" {{ $gtk->jenis_ptk == 'Staff TU' ? 'selected' : '' }}>Staff TU</option>
                                        <option value="Bendahara" {{ $gtk->jenis_ptk == 'Bendahara' ? 'selected' : '' }}>Bendahara</option>
                                        <option value="Laboran" {{ $gtk->jenis_ptk == 'Laboran' ? 'selected' : '' }}>Laboran</option>
                                        <option value="Pustakawan" {{ $gtk->jenis_ptk == 'Pustakawan' ? 'selected' : '' }}>Pustakawan</option>
                                        <option value="Cleaning Service" {{ $gtk->jenis_ptk == 'Cleaning Service' ? 'selected' : '' }}>Cleaning Service</option>
                                        <option value="Satpam" {{ $gtk->jenis_ptk == 'Satpam' ? 'selected' : '' }}>Satpam</option>
                                        <option value="Lainnya" {{ $gtk->jenis_ptk == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                    </select>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="status_kepegawaian">Status Kepegawaian</label>
                                            <select class="form-control" id="status_kepegawaian" name="status_kepegawaian">
                                                <option value="">-- Pilih --</option>
                                                <option value="PNS" {{ $gtk->status_kepegawaian == 'PNS' ? 'selected' : '' }}>PNS</option>
                                                <option value="PPPK" {{ $gtk->status_kepegawaian == 'PPPK' ? 'selected' : '' }}>PPPK</option>
                                                <option value="GTY" {{ $gtk->status_kepegawaian == 'GTY' ? 'selected' : '' }}>GTY (Guru Tetap Yayasan)</option>
                                                <option value="PTY" {{ $gtk->status_kepegawaian == 'PTY' ? 'selected' : '' }}>PTY (Pegawai Tetap Yayasan)</option>
                                                <option value="Honorer" {{ $gtk->status_kepegawaian == 'Honorer' ? 'selected' : '' }}>Honorer</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="jabatan">Jabatan</label>
                                            <input type="text" class="form-control" id="jabatan" name="jabatan" 
                                                   value="{{ $gtk->jabatan }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="tmt_kerja">TMT Kerja</label>
                                    <input type="date" class="form-control" id="tmt_kerja" name="tmt_kerja" 
                                           value="{{ $gtk->tmt_kerja ? \Carbon\Carbon::parse($gtk->tmt_kerja)->format('Y-m-d') : '' }}">
                                </div>

                                <div class="form-group gtk-edit-form-actions">
                                    <button type="reset" class="btn btn-secondary mr-2">
                                        <i class="fas fa-undo mr-1"></i> Reset
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Simpan Data Kepegawaian
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Tab Akun User -->
                        <div class="tab-pane fade" id="akun" role="tabpanel">
                            @if($gtk->user)
                                <form id="formAkun">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="tab" value="akun">
                                    
                                    <div class="form-group">
                                        <label for="name">Nama Lengkap <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="{{ $gtk->user->name }}" required>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="username">Username <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="username" name="username" 
                                                       value="{{ $gtk->user->username }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="user_email">Email <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control" id="user_email" name="email"
                                                       value="{{ $gtk->user->email }}" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="role">Role <span class="text-danger">*</span></label>
                                                <select class="form-control" id="role" name="role" required>
                                                    @foreach($roles as $role)
                                                        <option value="{{ $role->name }}" 
                                                            {{ $gtk->user->hasRole($role->name) ? 'selected' : '' }}>
                                                            {{ $role->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="is_active">Status Akun <span class="text-danger">*</span></label>
                                                <select class="form-control" id="is_active" name="is_active" required>
                                                    <option value="1" {{ $gtk->user->is_active ? 'selected' : '' }}>Aktif</option>
                                                    <option value="0" {{ !$gtk->user->is_active ? 'selected' : '' }}>Nonaktif</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> 
                                        <strong>Info:</strong> Untuk reset password, gunakan tombol "Reset Password" di halaman list GTK.
                                        Password akan direset menjadi NIK: <strong>{{ $gtk->nik }}</strong>
                                    </div>

                                    <div class="form-group gtk-edit-form-actions">
                                        <button type="reset" class="btn btn-secondary mr-2">
                                            <i class="fas fa-undo mr-1"></i> Reset
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Simpan Data Akun
                                        </button>
                                    </div>
                                </form>
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i> GTK ini belum memiliki akun user.
                                </div>
                            @endif
                        </div>

                        <!-- Tab Data Integrasi Kemenag -->
                        <div class="tab-pane fade" id="kemenag" role="tabpanel">
                            @if(empty($gtk->nip))
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle"></i> 
                                    <strong>Tidak dapat melakukan sinkronisasi!</strong><br>
                                    GTK ini tidak memiliki NIP. Silakan lengkapi data kepegawaian terlebih dahulu.
                                </div>
                            @else
                                <!-- Status Sinkronisasi -->
                                <div class="card">
                                    <div class="card-header bg-info">
                                        <h3 class="card-title"><i class="fas fa-info-circle"></i> Status Sinkronisasi</h3>
                                    </div>
                                    <div class="card-body">
                                        @if(!$gtk->kemenagSync)
                                            <!-- Belum Pernah Sync -->
                                            <div class="alert alert-warning">
                                                <i class="fas fa-exclamation-triangle"></i> 
                                                Data belum pernah disinkronisasi dengan Kemenag BE-PINTAR
                                            </div>
                                            <button type="button" class="btn btn-primary btn-lg" id="btnSyncKemenag">
                                                <i class="fas fa-sync-alt"></i> Sinkronisasi Sekarang
                                            </button>
                                        @else
                                            <!-- Sudah Pernah Sync -->
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <table class="table table-sm">
                                                        <tr>
                                                            <th width="40%">Status Sync:</th>
                                                            <td>
                                                                <span class="badge badge-{{ $gtk->kemenagSync->sync_status_badge }}">
                                                                    {{ strtoupper($gtk->kemenagSync->sync_status) }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Terakhir Sync:</th>
                                                            <td>{{ $gtk->kemenagSync->synced_at->format('d M Y, H:i') }} WIB</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Oleh:</th>
                                                            <td>{{ $gtk->kemenagSync->syncedBy->name ?? 'Unknown' }}</td>
                                                        </tr>
                                                    </table>
                                                </div>
                                                <div class="col-md-6">
                                                    <table class="table table-sm">
                                                        <tr>
                                                            <th width="40%">Perbedaan Data:</th>
                                                            <td>
                                                                @if($gtk->kemenagSync->has_differences)
                                                                    <span class="badge badge-warning">
                                                                        {{ $gtk->kemenagSync->differences_count }} Field Berbeda
                                                                    </span>
                                                                @else
                                                                    <span class="badge badge-success">Tidak Ada Perbedaan</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Terakhir Diterapkan:</th>
                                                            <td>
                                                                @if($gtk->kemenagSync->last_applied_at)
                                                                    {{ $gtk->kemenagSync->last_applied_at->format('d M Y, H:i') }} WIB
                                                                @else
                                                                    <span class="text-muted">Belum Pernah</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Data Segar:</th>
                                                            <td>
                                                                @if($gtk->kemenagSync->isFresh())
                                                                    <span class="badge badge-success">Ya (< 30 hari)</span>
                                                                @else
                                                                    <span class="badge badge-warning">Perlu Refresh</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                            
                                            <button type="button" class="btn btn-primary" id="btnSyncKemenag">
                                                <i class="fas fa-sync-alt"></i> Sinkronisasi Ulang
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                <!-- Perbandingan Data -->
                                @if($gtk->kemenagSync && $gtk->kemenagSync->sync_status === 'success')
                                    <div class="card">
                                        <div class="card-header bg-primary">
                                            <h3 class="card-title"><i class="fas fa-exchange-alt"></i> Perbandingan Data</h3>
                                        </div>
                                        <div class="card-body">
                                            @php
                                                // Hitung applicable differences (exclude info-only)
                                                $applicableDifferences = [];
                                                if (is_array($gtk->kemenagSync->differences)) {
                                                    foreach ($gtk->kemenagSync->differences as $field => $diff) {
                                                        if (!isset($diff['is_info_only']) || !$diff['is_info_only']) {
                                                            $applicableDifferences[$field] = $diff;
                                                        }
                                                    }
                                                }
                                                $hasApplicableDifferences = count($applicableDifferences) > 0;
                                            @endphp

                                            @if($hasApplicableDifferences)
                                                <div class="alert alert-warning">
                                                    <i class="fas fa-exclamation-triangle"></i> 
                                                    Ditemukan <strong>{{ count($applicableDifferences) }}</strong> perbedaan antara data lokal dengan data Kemenag yang akan diupdate
                                                </div>

                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-hover">
                                                        <thead class="bg-light">
                                                            <tr>
                                                                <th width="25%">Field</th>
                                                                <th width="35%">Data Lokal</th>
                                                                <th width="35%">Data Kemenag</th>
                                                                <th width="5%" class="text-center">Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($applicableDifferences as $field => $diff)
                                                                <tr>
                                                                    <td><strong>{{ $diff['field_label'] ?? $field }}</strong></td>
                                                                    <td>{{ $diff['local'] ?? '-' }}</td>
                                                                    <td class="bg-warning-light">
                                                                        <strong>{{ $diff['kemenag'] ?? '-' }}</strong>
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <i class="fas fa-sync-alt text-warning" title="Akan diupdate"></i>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>

                                                <div class="alert alert-info mt-3">
                                                    <i class="fas fa-info-circle"></i> 
                                                    <strong>Informasi:</strong> Field tambahan seperti Agama, Pendidikan, Pangkat, dan Golongan Ruang dari Kemenag 
                                                    dapat dilihat di bagian "Data Lengkap" di bawah, namun tidak akan diterapkan karena field tersebut 
                                                    tidak tersedia di database lokal.
                                                </div>

                                                <div class="mt-3">
                                                    <button type="button" class="btn btn-success btn-lg" id="btnApplyKemenagData">
                                                        <i class="fas fa-download"></i> Terapkan Data Kemenag ke Data Lokal ({{ count($applicableDifferences) }} Field)
                                                    </button>
                                                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modalRawJson">
                                                        <i class="fas fa-code"></i> Lihat JSON Lengkap
                                                    </button>
                                                </div>
                                            @else
                                                <div class="alert alert-success">
                                                    <i class="fas fa-check-circle"></i> 
                                                    Data lokal sudah sama dengan data Kemenag. Tidak ada perbedaan yang ditemukan.
                                                </div>
                                                
                                                <div class="alert alert-info mt-2">
                                                    <i class="fas fa-info-circle"></i> 
                                                    <strong>Catatan:</strong> Data tambahan dari Kemenag seperti Agama, Pendidikan, Pangkat, dan Golongan Ruang 
                                                    dapat dilihat di bagian "Data Lengkap" di bawah untuk referensi.
                                                </div>

                                                <div class="mt-3">
                                                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modalRawJson">
                                                        <i class="fas fa-code"></i> Lihat JSON Lengkap
                                                    </button>
                                                </div>
                                            @endif

                                            <!-- Data Lengkap dari Kemenag -->
                                            <div class="mt-4">
                                                <h5><i class="fas fa-database"></i> Data Lengkap dari Kemenag BE-PINTAR</h5>
                                                
                                                <!-- Accordion untuk organize data -->
                                                <div class="accordion" id="accordionKemenag">
                                                    
                                                    <!-- 1. Data Identitas -->
                                                    <div class="card">
                                                        <div class="card-header kemenag-accordion-header kemenag-accordion-primary" id="headingIdentitas" style="background: linear-gradient(135deg, #1d4ed8, #2563eb) !important;">
                                                            <h2 class="mb-0">
                                                                <button class="btn btn-link btn-block text-left text-white" type="button" data-toggle="collapse" data-target="#collapseIdentitas" style="color: #ffffff !important;">
                                                                    <i class="fas fa-id-card"></i> Data Identitas
                                                                </button>
                                                            </h2>
                                                        </div>
                                                        <div id="collapseIdentitas" class="collapse show" data-parent="#accordionKemenag">
                                                            <div class="card-body">
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <table class="table table-sm table-bordered">
                                                                            <tr><th width="45%">NIP Lama</th><td>{{ $gtk->kemenagSync->nip ?? '-' }}</td></tr>
                                                                            <tr><th>NIP Baru (18 digit)</th><td><strong>{{ $gtk->kemenagSync->nip_baru ?? '-' }}</strong></td></tr>
                                                                            <tr><th>Nama</th><td>{{ $gtk->kemenagSync->nama ?? '-' }}</td></tr>
                                                                            <tr><th>Nama Lengkap</th><td><strong>{{ $gtk->kemenagSync->nama_lengkap ?? '-' }}</strong></td></tr>
                                                                            <tr><th>Jenis Kelamin</th><td>{{ $gtk->kemenagSync->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td></tr>
                                                                        </table>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <table class="table table-sm table-bordered">
                                                                            <tr><th width="45%">Tempat Lahir</th><td>{{ $gtk->kemenagSync->tempat_lahir ?? '-' }}</td></tr>
                                                                            <tr><th>Tanggal Lahir</th><td>{{ $gtk->kemenagSync->tanggal_lahir ? $gtk->kemenagSync->tanggal_lahir->format('d-m-Y') : '-' }}</td></tr>
                                                                            <tr><th>Agama</th><td>{{ $gtk->kemenagSync->agama ?? '-' }}</td></tr>
                                                                            <tr><th>Status Kawin</th><td>{{ $gtk->kemenagSync->status_kawin ?? '-' }}</td></tr>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- 2. Data Pendidikan -->
                                                    <div class="card">
                                                        <div class="card-header kemenag-accordion-header kemenag-accordion-info" id="headingPendidikan" style="background: linear-gradient(135deg, #0f9fb7, #17a2b8) !important;">
                                                            <h2 class="mb-0">
                                                                <button class="btn btn-link btn-block text-left text-white collapsed" type="button" data-toggle="collapse" data-target="#collapsePendidikan" style="color: #ffffff !important;">
                                                                    <i class="fas fa-graduation-cap"></i> Data Pendidikan
                                                                </button>
                                                            </h2>
                                                        </div>
                                                        <div id="collapsePendidikan" class="collapse" data-parent="#accordionKemenag">
                                                            <div class="card-body">
                                                                <table class="table table-sm table-bordered">
                                                                    <tr><th width="30%">Pendidikan</th><td>{{ $gtk->kemenagSync->pendidikan ?? '-' }}</td></tr>
                                                                    <tr><th>Jenjang Pendidikan</th><td>{{ $gtk->kemenagSync->jenjang_pendidikan ?? '-' }}</td></tr>
                                                                    <tr><th>Kode Bidang Studi</th><td>{{ $gtk->kemenagSync->kode_bidang_studi ?? '-' }}</td></tr>
                                                                    <tr><th>Bidang Studi</th><td>{{ $gtk->kemenagSync->bidang_studi ?? '-' }}</td></tr>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- 3. Data Kepegawaian -->
                                                    <div class="card">
                                                        <div class="card-header kemenag-accordion-header kemenag-accordion-success" id="headingKepegawaian" style="background: linear-gradient(135deg, #15803d, #28a745) !important;">
                                                            <h2 class="mb-0">
                                                                <button class="btn btn-link btn-block text-left text-white collapsed" type="button" data-toggle="collapse" data-target="#collapseKepegawaian" style="color: #ffffff !important;">
                                                                    <i class="fas fa-briefcase"></i> Data Kepegawaian
                                                                </button>
                                                            </h2>
                                                        </div>
                                                        <div id="collapseKepegawaian" class="collapse" data-parent="#accordionKemenag">
                                                            <div class="card-body">
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <table class="table table-sm table-bordered">
                                                                            <tr><th width="45%">Status Pegawai</th><td><span class="badge badge-success">{{ $gtk->kemenagSync->status_pegawai ?? '-' }}</span></td></tr>
                                                                            <tr><th>Kode Pangkat</th><td>{{ $gtk->kemenagSync->kode_pangkat ?? '-' }}</td></tr>
                                                                            <tr><th>Pangkat</th><td>{{ $gtk->kemenagSync->pangkat ?: '-' }}</td></tr>
                                                                            <tr><th>Golongan Ruang</th><td><strong>{{ $gtk->kemenagSync->gol_ruang ?? '-' }}</strong></td></tr>
                                                                            <tr><th>TMT CPNS</th><td>{{ $gtk->kemenagSync->tmt_cpns ? $gtk->kemenagSync->tmt_cpns->format('d-m-Y') : '-' }}</td></tr>
                                                                            <tr><th>TMT Pangkat</th><td>{{ $gtk->kemenagSync->tmt_pangkat ? $gtk->kemenagSync->tmt_pangkat->format('d-m-Y') : '-' }}</td></tr>
                                                                            <tr><th>TMT Pangkat YAD</th><td>{{ $gtk->kemenagSync->tmt_pangkat_yad ? $gtk->kemenagSync->tmt_pangkat_yad->format('d-m-Y') : '-' }}</td></tr>
                                                                        </table>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <table class="table table-sm table-bordered">
                                                                            <tr><th width="45%">Masa Kerja</th><td>{{ $gtk->kemenagSync->mk_tahun ?? 0 }} Tahun {{ $gtk->kemenagSync->mk_bulan ?? 0 }} Bulan</td></tr>
                                                                            <tr><th>Masa Kerja (Alt)</th><td>{{ $gtk->kemenagSync->mk_tahun_1 ?? 0 }} Tahun {{ $gtk->kemenagSync->mk_bulan_1 ?? 0 }} Bulan</td></tr>
                                                                            <tr><th>Gaji Pokok</th><td><strong class="text-success">{{ $gtk->kemenagSync->gaji_poko_formatted ?? 'Rp 0' }}</strong></td></tr>
                                                                            <tr><th>TMT KGB YAD</th><td>{{ $gtk->kemenagSync->tmt_kgb_yad ? $gtk->kemenagSync->tmt_kgb_yad->format('d-m-Y') : '-' }}</td></tr>
                                                                            <tr><th>Usia Pensiun</th><td>{{ $gtk->kemenagSync->usia_pensiun ?? 58 }} Tahun</td></tr>
                                                                            <tr><th>TMT Pensiun</th><td><strong>{{ $gtk->kemenagSync->tmt_pensiun ? $gtk->kemenagSync->tmt_pensiun->format('d-m-Y') : '-' }}</strong></td></tr>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- 4. Data Jabatan -->
                                                    <div class="card">
                                                        <div class="card-header kemenag-accordion-header kemenag-accordion-warning" id="headingJabatan" style="background: linear-gradient(135deg, #d97706, #f59e0b) !important;">
                                                            <h2 class="mb-0">
                                                                <button class="btn btn-link btn-block text-left text-white collapsed" type="button" data-toggle="collapse" data-target="#collapseJabatan" style="color: #ffffff !important;">
                                                                    <i class="fas fa-user-tie"></i> Data Jabatan
                                                                </button>
                                                            </h2>
                                                        </div>
                                                        <div id="collapseJabatan" class="collapse" data-parent="#accordionKemenag">
                                                            <div class="card-body">
                                                                <table class="table table-sm table-bordered">
                                                                    <tr><th width="30%">Tipe Jabatan</th><td>{{ $gtk->kemenagSync->tipe_jabatan ?? '-' }}</td></tr>
                                                                    <tr><th>Kode Jabatan</th><td>{{ $gtk->kemenagSync->kode_jabatan ?? '-' }}</td></tr>
                                                                    <tr><th>Tampil Jabatan</th><td><strong>{{ $gtk->kemenagSync->tampil_jabatan ?? '-' }}</strong></td></tr>
                                                                    <tr><th>Kode Level Jabatan</th><td>{{ $gtk->kemenagSync->kode_level_jabatan ?? '-' }}</td></tr>
                                                                    <tr><th>Level Jabatan</th><td>{{ $gtk->kemenagSync->level_jabatan ?? '-' }}</td></tr>
                                                                    <tr><th>TMT Jabatan</th><td>{{ $gtk->kemenagSync->tmt_jabatan ? $gtk->kemenagSync->tmt_jabatan->format('d-m-Y') : '-' }}</td></tr>
                                                                    <tr><th>Keterangan</th><td><small>{{ $gtk->kemenagSync->keterangan ?? '-' }}</small></td></tr>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- 5. Data Satuan Kerja -->
                                                    <div class="card">
                                                        <div class="card-header kemenag-accordion-header kemenag-accordion-secondary" id="headingSatker" style="background: linear-gradient(135deg, #4b5563, #6c757d) !important;">
                                                            <h2 class="mb-0">
                                                                <button class="btn btn-link btn-block text-left text-white collapsed" type="button" data-toggle="collapse" data-target="#collapseSatker" style="color: #ffffff !important;">
                                                                    <i class="fas fa-building"></i> Data Satuan Kerja
                                                                </button>
                                                            </h2>
                                                        </div>
                                                        <div id="collapseSatker" class="collapse" data-parent="#accordionKemenag">
                                                            <div class="card-body">
                                                                <table class="table table-sm table-bordered">
                                                                    <tr class="table-primary"><th colspan="2"><i class="fas fa-school"></i> Satker Level 1 - Unit Kerja Langsung</th></tr>
                                                                    <tr><th width="30%">Kode Satker</th><td>{{ $gtk->kemenagSync->kode_satuan_kerja ?? '-' }}</td></tr>
                                                                    <tr><th>Nama Satker 1</th><td><strong>{{ $gtk->kemenagSync->satker_1 ?? '-' }}</strong></td></tr>
                                                                    
                                                                    <tr class="table-info"><th colspan="2"><i class="fas fa-building"></i> Satker Level 2</th></tr>
                                                                    <tr><th>Kode Satker 2</th><td>{{ $gtk->kemenagSync->kode_satker_2 ?? '-' }}</td></tr>
                                                                    <tr><th>Nama Satker 2</th><td>{{ $gtk->kemenagSync->satker_2 ?? '-' }}</td></tr>
                                                                    
                                                                    <tr class="table-warning"><th colspan="2"><i class="fas fa-building"></i> Satker Level 3 - Kankemenag</th></tr>
                                                                    <tr><th>Kode Satker 3</th><td>{{ $gtk->kemenagSync->kode_satker_3 ?? '-' }}</td></tr>
                                                                    <tr><th>Nama Satker 3</th><td>{{ $gtk->kemenagSync->satker_3 ?? '-' }}</td></tr>
                                                                    
                                                                    <tr class="table-success"><th colspan="2"><i class="fas fa-building"></i> Satker Level 4 - Kanwil</th></tr>
                                                                    <tr><th>Kode Satker 4</th><td>{{ $gtk->kemenagSync->kode_satker_4 ?? '-' }}</td></tr>
                                                                    <tr><th>Nama Satker 4</th><td>{{ $gtk->kemenagSync->satker_4 ?? '-' }}</td></tr>
                                                                    
                                                                    <tr class="table-danger"><th colspan="2"><i class="fas fa-building"></i> Satker Level 5 - Pusat</th></tr>
                                                                    <tr><th>Kode Satker 5</th><td>{{ $gtk->kemenagSync->kode_satker_5 ?? '-' }}</td></tr>
                                                                    <tr><th>Nama Satker 5</th><td>{{ $gtk->kemenagSync->satker_5 ?? '-' }}</td></tr>
                                                                    
                                                                    <tr class="table-secondary"><th colspan="2"><i class="fas fa-info-circle"></i> Info Tambahan</th></tr>
                                                                    <tr><th>Kode Grup Satker</th><td>{{ $gtk->kemenagSync->kode_grup_satuan_kerja ?? '-' }}</td></tr>
                                                                    <tr><th>Grup Satker</th><td>{{ $gtk->kemenagSync->grup_satuan_kerja ?? '-' }}</td></tr>
                                                                    <tr><th>Keterangan Satker</th><td>{{ $gtk->kemenagSync->keterangan_satuan_kerja ?? '-' }}</td></tr>
                                                                    <tr><th>Satker Kelola</th><td>{{ $gtk->kemenagSync->satker_kelola ?? '-' }}</td></tr>
                                                                    <tr><th>Hari Kerja</th><td>{{ $gtk->kemenagSync->hari_kerja ?? 5 }} Hari</td></tr>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- 6. Data Kontak & Alamat -->
                                                    <div class="card">
                                                        <div class="card-header kemenag-accordion-header kemenag-accordion-purple" id="headingKontak" style="background: linear-gradient(135deg, #7c3aed, #8b5cf6) !important;">
                                                            <h2 class="mb-0">
                                                                <button class="btn btn-link btn-block text-left text-white collapsed" type="button" data-toggle="collapse" data-target="#collapseKontak" style="color: #ffffff !important;">
                                                                    <i class="fas fa-address-book"></i> Data Kontak & Alamat
                                                                </button>
                                                            </h2>
                                                        </div>
                                                        <div id="collapseKontak" class="collapse" data-parent="#accordionKemenag">
                                                            <div class="card-body">
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <h6><i class="fas fa-phone"></i> Kontak</h6>
                                                                        <table class="table table-sm table-bordered">
                                                                            <tr><th width="40%">Telepon</th><td>{{ $gtk->kemenagSync->telepon ?? '-' }}</td></tr>
                                                                            <tr><th>No HP</th><td><strong>{{ $gtk->kemenagSync->no_hp ?? '-' }}</strong></td></tr>
                                                                            <tr><th>Email</th><td>{{ $gtk->kemenagSync->email ?? '-' }}</td></tr>
                                                                            <tr><th>Email Dinas</th><td>{{ $gtk->kemenagSync->email_dinas ?? '-' }}</td></tr>
                                                                        </table>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <h6><i class="fas fa-map-marker-alt"></i> Alamat</h6>
                                                                        <table class="table table-sm table-bordered">
                                                                            <tr><th width="40%">Alamat 1</th><td>{{ $gtk->kemenagSync->alamat_1 ?? '-' }}</td></tr>
                                                                            <tr><th>Alamat 2</th><td>{{ $gtk->kemenagSync->alamat_2 ?? '-' }}</td></tr>
                                                                            <tr><th>Kab/Kota</th><td>{{ $gtk->kemenagSync->kab_kota ?? '-' }}</td></tr>
                                                                            <tr><th>Provinsi</th><td>{{ $gtk->kemenagSync->provinsi ?? '-' }}</td></tr>
                                                                            <tr><th>Kode Pos</th><td>{{ $gtk->kemenagSync->kode_pos ?? '-' }}</td></tr>
                                                                            <tr><th>Kode Lokasi</th><td>{{ $gtk->kemenagSync->kode_lokasi ?? '-' }}</td></tr>
                                                                            <tr><th>Koordinat GPS</th><td>
                                                                                @if($gtk->kemenagSync->lat && $gtk->kemenagSync->lon)
                                                                                    <a href="https://www.google.com/maps?q={{ $gtk->kemenagSync->lat }},{{ $gtk->kemenagSync->lon }}" target="_blank">
                                                                                        {{ $gtk->kemenagSync->lat }}, {{ $gtk->kemenagSync->lon }} <i class="fas fa-external-link-alt"></i>
                                                                                    </a>
                                                                                @else
                                                                                    -
                                                                                @endif
                                                                            </td></tr>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- 7. Data Madrasah/Sekolah -->
                                                    <div class="card">
                                                        <div class="card-header kemenag-accordion-header kemenag-accordion-dark" id="headingMadrasah" style="background: linear-gradient(135deg, #111827, #1f2937) !important;">
                                                            <h2 class="mb-0">
                                                                <button class="btn btn-link btn-block text-left text-white collapsed" type="button" data-toggle="collapse" data-target="#collapseMadrasah" style="color: #ffffff !important;">
                                                                    <i class="fas fa-mosque"></i> Data Madrasah/Sekolah
                                                                </button>
                                                            </h2>
                                                        </div>
                                                        <div id="collapseMadrasah" class="collapse" data-parent="#accordionKemenag">
                                                            <div class="card-body">
                                                                <table class="table table-sm table-bordered">
                                                                    <tr><th width="30%">NSM</th><td>{{ $gtk->kemenagSync->nsm ?? '-' }}</td></tr>
                                                                    <tr><th>NPSN</th><td>{{ $gtk->kemenagSync->npsn ?? '-' }}</td></tr>
                                                                    <tr><th>Kode KUA</th><td>{{ $gtk->kemenagSync->kode_kua ?? '-' }}</td></tr>
                                                                    <tr><th>ISO</th><td>{{ $gtk->kemenagSync->iso ?? '-' }}</td></tr>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .kemenag-accordion-header {
            border-bottom: 0;
            padding: 0;
        }

        .kemenag-accordion-header h2 {
            margin: 0;
        }

        .kemenag-accordion-header .btn.btn-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 0.95rem 1.25rem;
            color: #fff !important;
            font-weight: 600;
            text-decoration: none !important;
            box-shadow: none !important;
        }

        .kemenag-accordion-header .btn.btn-link:hover,
        .kemenag-accordion-header .btn.btn-link:focus {
            color: #fff !important;
            text-decoration: none !important;
            opacity: 0.96;
        }

        .kemenag-accordion-primary {
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
        }

        .kemenag-accordion-info {
            background: linear-gradient(135deg, #0f9fb7, #17a2b8);
        }

        .kemenag-accordion-success {
            background: linear-gradient(135deg, #15803d, #28a745);
        }

        .kemenag-accordion-warning {
            background: linear-gradient(135deg, #d97706, #f59e0b);
        }

        .kemenag-accordion-secondary {
            background: linear-gradient(135deg, #4b5563, #6c757d);
        }

        .kemenag-accordion-purple {
            background: linear-gradient(135deg, #7c3aed, #8b5cf6);
        }

        .kemenag-accordion-dark {
            background: linear-gradient(135deg, #111827, #1f2937);
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // ===== FOTO UPLOAD AJAX =====
            const $dropZone = $('#dropZone');
            const $fileInput = $('#fotoFileInput');
            const $preview = $('#fotoPreview');
            const $initials = $('#fotoInitials');
            const $progress = $('#uploadProgress');
            const $progressBar = $('#uploadProgressBar');
            const $statusText = $('#uploadStatusText');
            const $dropContent = $('#dropZoneContent');
            const $btnDelete = $('#btnDeleteFoto');
            const uploadUrl = '{{ route("admin.gtk.upload-foto", $gtk->id) }}';
            const deleteUrl = '{{ route("admin.gtk.delete-foto", $gtk->id) }}';
            const csrfToken = '{{ csrf_token() }}';

            // Click to open file dialog
            $dropZone.on('click', function(e) {
                if (e.target === $fileInput[0]) return;
                $fileInput.trigger('click');
            }).on('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    $fileInput.trigger('click');
                }
            });

            // Drag & drop visual feedback
            $dropZone.on('dragover dragenter', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('is-dragging');
            });
            $dropZone.on('dragleave drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('is-dragging');
            });

            // Handle drop
            $dropZone.on('drop', function(e) {
                const files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) handleUpload(files[0]);
            });

            // Handle file input change
            $fileInput.on('change', function() {
                if (this.files.length > 0) handleUpload(this.files[0]);
            });

            function showPhoto(src) {
                $preview.attr('src', src).show();
                $initials.hide();
            }

            function showInitials() {
                $preview.hide().attr('src', '');
                $initials.show();
                $btnDelete.hide();
            }

            function handleUpload(file) {
                // Validate client-side
                const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                if (!validTypes.includes(file.type)) {
                    Swal.fire({ icon: 'error', title: 'Format Salah', text: 'Hanya file JPG, JPEG, dan PNG yang diizinkan.' });
                    return;
                }
                if (file.size > 2 * 1024 * 1024) {
                    Swal.fire({ icon: 'error', title: 'File Terlalu Besar', text: 'Ukuran file maksimal 2MB.' });
                    return;
                }

                // Show instant preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    showPhoto(e.target.result);
                    $preview.css('border-color', '#ffc107');
                };
                reader.readAsDataURL(file);

                // Prepare upload
                const formData = new FormData();
                formData.append('foto_profile', file);
                formData.append('_token', csrfToken);

                // Show progress
                $dropContent.hide();
                $progress.show();
                $progressBar.css('width', '0%').removeClass('bg-danger').addClass('bg-primary');
                $statusText.text('Mengupload...').removeClass('text-danger text-success').addClass('text-muted');

                $.ajax({
                    url: uploadUrl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    xhr: function() {
                        const xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener('progress', function(e) {
                            if (e.lengthComputable) {
                                const pct = Math.round((e.loaded / e.total) * 100);
                                $progressBar.css('width', pct + '%');
                                $statusText.text('Mengupload... ' + pct + '%');
                            }
                        });
                        return xhr;
                    },
                    success: function(res) {
                        $progressBar.css('width', '100%');
                        $statusText.text('Upload berhasil!').removeClass('text-muted text-danger').addClass('text-success');
                        showPhoto(res.foto_url);
                        $preview.css('border-color', '#28a745');
                        $btnDelete.show();

                        setTimeout(function() {
                            $progress.hide();
                            $dropContent.show();
                            $preview.css('border-color', '#dee2e6');
                        }, 1500);

                        Swal.fire({
                            toast: true, position: 'top-end', icon: 'success',
                            title: res.message, showConfirmButton: false, timer: 2000
                        });
                    },
                    error: function(xhr) {
                        $progressBar.css('width', '100%').removeClass('bg-primary').addClass('bg-danger');
                        let msg = 'Gagal mengupload foto.';
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            const errs = xhr.responseJSON.errors;
                            msg = Object.values(errs).flat().join(', ');
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        $statusText.text(msg).removeClass('text-muted text-success').addClass('text-danger');
                        $preview.css('border-color', '#dc3545');

                        setTimeout(function() {
                            $progress.hide();
                            $dropContent.show();
                            $progressBar.removeClass('bg-danger').addClass('bg-primary');
                            $preview.css('border-color', '#dee2e6');
                        }, 3000);
                    }
                });

                // Reset file input
                $fileInput.val('');
            }

            // Delete foto
            $btnDelete.on('click', function(e) {
                e.stopPropagation();
                Swal.fire({
                    title: 'Hapus Foto?',
                    text: 'Foto profile akan dihapus.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: deleteUrl,
                            type: 'DELETE',
                            data: { _token: csrfToken },
                            success: function(res) {
                                showInitials();
                                Swal.fire({
                                    toast: true, position: 'top-end', icon: 'success',
                                    title: res.message, showConfirmButton: false, timer: 2000
                                });
                            },
                            error: function() {
                                Swal.fire({ icon: 'error', title: 'Gagal', text: 'Tidak dapat menghapus foto.' });
                            }
                        });
                    }
                });
            });

            // Initialize Select2
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%'
            });

            $('#gtkEditTabs a[data-toggle="pill"]').on('shown.bs.tab', function() {
                $('#gtkEditSectionTitle').text($(this).data('title'));
            });

            $('.gtk-edit-content form').on('reset', function() {
                const $form = $(this);
                window.setTimeout(function() {
                    $form.find('.select2').trigger('change.select2');
                }, 0);
            });

            // Cascade dropdown wilayah
            $('#provinsi_id').on('change', function() {
                const provinsiId = $(this).val();
                $('#kabupaten_id').html('<option value="">Loading...</option>').prop('disabled', true);
                $('#kecamatan_id').html('<option value="">-- Pilih Kecamatan --</option>').prop('disabled', true);
                $('#kelurahan_id').html('<option value="">-- Pilih Kelurahan/Desa --</option>').prop('disabled', true);

                if (provinsiId) {
                    $.get(`/admin/api/cities/${provinsiId}`, function(data) {
                        let options = '<option value="">-- Pilih Kabupaten/Kota --</option>';
                        data.forEach(city => {
                            options += `<option value="${city.code}">${city.name}</option>`;
                        });
                        $('#kabupaten_id').html(options).prop('disabled', false);
                    });
                } else {
                    $('#kabupaten_id').html('<option value="">-- Pilih Kabupaten/Kota --</option>').prop('disabled', false);
                }
            });

            $('#kabupaten_id').on('change', function() {
                const kabupatenId = $(this).val();
                $('#kecamatan_id').html('<option value="">Loading...</option>').prop('disabled', true);
                $('#kelurahan_id').html('<option value="">-- Pilih Kelurahan/Desa --</option>').prop('disabled', true);

                if (kabupatenId) {
                    $.get(`/admin/api/districts/${kabupatenId}`, function(data) {
                        let options = '<option value="">-- Pilih Kecamatan --</option>';
                        data.forEach(district => {
                            options += `<option value="${district.code}">${district.name}</option>`;
                        });
                        $('#kecamatan_id').html(options).prop('disabled', false);
                    });
                } else {
                    $('#kecamatan_id').html('<option value="">-- Pilih Kecamatan --</option>').prop('disabled', false);
                }
            });

            $('#kecamatan_id').on('change', function() {
                const kecamatanId = $(this).val();
                $('#kelurahan_id').html('<option value="">Loading...</option>').prop('disabled', true);

                if (kecamatanId) {
                    $.get(`/admin/api/villages/${kecamatanId}`, function(data) {
                        let options = '<option value="">-- Pilih Kelurahan/Desa --</option>';
                        data.forEach(village => {
                            options += `<option value="${village.code}">${village.name}</option>`;
                        });
                        $('#kelurahan_id').html(options).prop('disabled', false);
                    });
                } else {
                    $('#kelurahan_id').html('<option value="">-- Pilih Kelurahan/Desa --</option>').prop('disabled', false);
                }
            });

            // Form Data Pribadi submit
            $('#formDataDiri').on('submit', function(e) {
                e.preventDefault();
                submitForm(this, 'diri');
            });

            // Form Data Kepegawaian submit
            $('#formDataKepeg').on('submit', function(e) {
                e.preventDefault();
                submitForm(this, 'kepeg');
            });

            // Form Akun submit
            $('#formAkun').on('submit', function(e) {
                e.preventDefault();
                submitForm(this, 'akun');
            });

            function submitForm(form, tab) {
                const $form = $(form);
                const $submitBtn = $form.find('button[type="submit"]');
                const originalBtnText = $submitBtn.html();
                
                // Disable button
                $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

                // Get form data
                const formData = new FormData(form);

                $.ajax({
                    url: '{{ route("admin.gtk.update", $gtk->id) }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });

                            // Update display if needed
                            if (tab === 'diri' && response.data.nama_lengkap) {
                                $('#gtkEditHeroName').text(response.data.nama_lengkap);
                                $('#gtkEditSidebarName').text(response.data.nama_lengkap);
                            }
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Terjadi kesalahan';
                        
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            errorMsg = '<ul class="text-left mb-0">';
                            $.each(errors, function(key, value) {
                                errorMsg += '<li>' + value[0] + '</li>';
                            });
                            errorMsg += '</ul>';
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            html: errorMsg
                        });
                    },
                    complete: function() {
                        $submitBtn.prop('disabled', false).html(originalBtnText);
                    }
                });
            }
        });

        // ============================================
        // KEMENAG SYNC HANDLERS
        // ============================================

        // Sync Kemenag Button
        $('#btnSyncKemenag').on('click', function() {
            const btn = $(this);
            const originalText = btn.html();
            
            Swal.fire({
                title: 'Konfirmasi Sinkronisasi',
                html: 'Anda akan melakukan sinkronisasi data GTK dengan API Kemenag BE-PINTAR.<br><br>' +
                      '<strong>NIP:</strong> {{ $gtk->nip }}<br>' +
                      '<strong>Nama:</strong> {{ $gtk->nama_lengkap }}',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-sync-alt"></i> Ya, Sinkronisasi',
                cancelButtonText: 'Batal',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return $.ajax({
                        url: '{{ route("admin.gtk.sync-kemenag", $gtk->id) }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        }
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    const response = result.value;
                    
                    if (response.success) {
                        let message = response.message + '<br><br>';
                        
                        if (response.has_differences && response.applicable_differences_count > 0) {
                            message += '<strong class="text-warning">Ditemukan ' + response.applicable_differences_count + ' perbedaan data yang akan diupdate</strong>';
                        } else if (response.has_differences) {
                            message += '<strong class="text-info">Tidak ada perbedaan pada field yang bisa diupdate</strong><br>' +
                                      '<small>Data tambahan (Agama, Pendidikan, dll) tersedia di bagian "Data Lengkap"</small>';
                        } else {
                            message += '<strong class="text-success">Tidak ada perbedaan data</strong>';
                        }
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            html: message,
                            confirmButtonText: 'Reload Halaman'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: response.message
                        });
                    }
                }
            }).catch((error) => {
                console.error('Sync error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: error.responseJSON?.message || 'Terjadi kesalahan saat melakukan sinkronisasi'
                });
            });
        });

        // Apply Kemenag Data Button
        $('#btnApplyKemenagData').on('click', function() {
            Swal.fire({
                title: 'Konfirmasi Penerapan Data',
                html: '<div class="text-left">' +
                      '<p>Anda akan menerapkan data dari Kemenag ke data lokal GTK.</p>' +
                      '<p><strong class="text-danger">PERHATIAN:</strong></p>' +
                      '<ul>' +
                      '<li>Data lokal akan di-update dengan data dari Kemenag</li>' +
                      '<li>Proses ini tidak dapat di-undo</li>' +
                      '<li>Pastikan Anda sudah mereview perbedaan data di atas</li>' +
                      '</ul>' +
                      '<p>Jumlah field yang akan diupdate: <strong>{{ $gtk->kemenagSync->applicable_differences_count ?? 0 }}</strong></p>' +
                      '</div>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-check"></i> Ya, Terapkan Data',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#28a745',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return $.ajax({
                        url: '{{ route("admin.gtk.apply-kemenag-data", $gtk->id) }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        }
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    const response = result.value;
                    
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            html: response.message + '<br><br>' +
                                  '<strong>Field yang diupdate: ' + response.updated_count + '</strong>',
                            confirmButtonText: 'Reload Halaman'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: response.message
                        });
                    }
                }
            }).catch((error) => {
                console.error('Apply error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: error.responseJSON?.message || 'Terjadi kesalahan saat menerapkan data'
                });
            });
        });
    </script>

    <!-- Modal Raw JSON -->
    @if($gtk->kemenagSync && $gtk->kemenagSync->raw_response)
    <div class="modal fade" id="modalRawJson" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title"><i class="fas fa-code"></i> Raw JSON Response dari API Kemenag</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <pre class="bg-dark text-white p-3" style="max-height: 500px; overflow-y: auto;"><code>{{ json_encode($gtk->kemenagSync->raw_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" onclick="copyToClipboard()">
                        <i class="fas fa-copy"></i> Copy JSON
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard() {
            const jsonText = @json($gtk->kemenagSync->raw_response);
            const tempInput = document.createElement('textarea');
            tempInput.value = JSON.stringify(jsonText, null, 2);
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);
            
            Swal.fire({
                icon: 'success',
                title: 'Tersalin!',
                text: 'JSON berhasil disalin ke clipboard',
                timer: 1500,
                showConfirmButton: false
            });
        }
    </script>
    @endif
@stop

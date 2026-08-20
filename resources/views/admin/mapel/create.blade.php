@extends('adminlte::page')

@section('title', 'Tambah Mata Pelajaran')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-plus-circle"></i> Tambah Mata Pelajaran</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.mapel.index') }}">Mata Pelajaran</a></li>
                <li class="breadcrumb-item active">Tambah</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <form action="{{ route('admin.mapel.store') }}" method="POST" id="form-mapel" class="mapel-form-page">
        @csrf

        <section class="mapel-form-hero">
            <div>
                <span class="mapel-form-hero__eyebrow"><i class="fas fa-book-open"></i> AKADEMIK · KATALOG MAPEL</span>
                <h2>Tambahkan mata pelajaran</h2>
                <p>Susun identitas, struktur kurikulum, dan kesiapan jadwal dalam satu alur yang jelas.</p>
            </div>
            <div class="mapel-form-hero__code"><i class="fas fa-plus-circle"></i><strong>Mapel baru</strong></div>
        </section>
        
        <div class="card card-primary card-outline mapel-form-card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Informasi Dasar</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="kurikulum_id">Kurikulum <span class="text-danger">*</span></label>
                            <select name="kurikulum_id" id="kurikulum_id" class="form-control @error('kurikulum_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Kurikulum --</option>
                                @foreach($kurikulums as $kurikulum)
                                    <option value="{{ $kurikulum->id }}" {{ old('kurikulum_id') == $kurikulum->id ? 'selected' : '' }}>
                                        {{ $kurikulum->nama_kurikulum }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kurikulum_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="jurusan_id">Jurusan (Peminatan)</label>
                            <select name="jurusan_id" id="jurusan_id" class="form-control @error('jurusan_id') is-invalid @enderror">
                                <option value="">-- Umum (Tidak Ada Jurusan) --</option>
                                @foreach($jurusans as $jurusan)
                                    <option value="{{ $jurusan->id }}" {{ old('jurusan_id') == $jurusan->id ? 'selected' : '' }}>
                                        {{ $jurusan->nama_jurusan }}
                                    </option>
                                @endforeach
                            </select>
                            @error('jurusan_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Kosongkan jika mapel umum untuk semua jurusan</small>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="kode_mapel">Kode Mapel <span class="text-danger">*</span></label>
                            <input type="text" name="kode_mapel" id="kode_mapel" class="form-control @error('kode_mapel') is-invalid @enderror" 
                                   value="{{ old('kode_mapel') }}" placeholder="Contoh: MAT, BIN" required maxlength="10">
                            @error('kode_mapel')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="kode_jadwal">Kode Jadwal Wakakur</label>
                            <input type="text" name="kode_jadwal" id="kode_jadwal" class="form-control @error('kode_jadwal') is-invalid @enderror"
                                   value="{{ old('kode_jadwal') }}" placeholder="A-Z" maxlength="1" style="text-transform:uppercase">
                            <small class="text-muted">Kosongkan bila mapel tidak memakai kode jadwal.</small>
                            @error('kode_jadwal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nama_mapel">Nama Mata Pelajaran <span class="text-danger">*</span></label>
                            <input type="text" name="nama_mapel" id="nama_mapel" class="form-control @error('nama_mapel') is-invalid @enderror" 
                                   value="{{ old('nama_mapel') }}" placeholder="Contoh: Matematika" required>
                            @error('nama_mapel')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="kelompok">Kelompok</label>
                            <select name="kelompok" id="kelompok" class="form-control @error('kelompok') is-invalid @enderror">
                                <option value="">-- Pilih Kelompok --</option>
                                <option value="A" {{ old('kelompok') == 'A' ? 'selected' : '' }}>Kelompok A (Umum)</option>
                                <option value="B" {{ old('kelompok') == 'B' ? 'selected' : '' }}>Kelompok B (Umum)</option>
                                <option value="C" {{ old('kelompok') == 'C' ? 'selected' : '' }}>Kelompok C (Peminatan)</option>
                            </select>
                            @error('kelompok')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Untuk Kurikulum 2013</small>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="kategori">Kategori</label>
                            <input type="text" name="kategori" id="kategori" class="form-control @error('kategori') is-invalid @enderror" 
                                   value="{{ old('kategori') }}" placeholder="Contoh: Wajib, Pilihan" maxlength="50">
                            @error('kategori')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="jam_pelajaran">Jam Pelajaran / Minggu <span class="text-danger">*</span></label>
                            <input type="number" name="jam_pelajaran" id="jam_pelajaran" class="form-control @error('jam_pelajaran') is-invalid @enderror" 
                                   value="{{ old('jam_pelajaran', 2) }}" min="1" max="10" required>
                            @error('jam_pelajaran')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="kkm">KKM (Kriteria Ketuntasan Minimal)</label>
                            <input type="number" name="kkm" id="kkm" class="form-control @error('kkm') is-invalid @enderror" 
                                   value="{{ old('kkm') }}" min="0" max="100" placeholder="Contoh: 75">
                            @error('kkm')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tingkat</label>
                            <div class="row">
                                @for($i = 1; $i <= 12; $i++)
                                    <div class="col-md-2 col-sm-4 col-6">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="tingkat{{ $i }}" 
                                                   name="tingkat[]" value="{{ $i }}" 
                                                   {{ is_array(old('tingkat')) && in_array($i, old('tingkat')) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="tingkat{{ $i }}">{{ $i }}</label>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                            <small class="text-muted">Pilih tingkat kelas yang diajar</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Semester</label>
                            <div class="custom-control custom-checkbox custom-control-inline">
                                <input type="checkbox" class="custom-control-input" id="semester1" name="semester[]" value="1" 
                                       {{ is_array(old('semester')) && in_array(1, old('semester')) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="semester1">Semester 1 (Ganjil)</label>
                            </div>
                            <div class="custom-control custom-checkbox custom-control-inline">
                                <input type="checkbox" class="custom-control-input" id="semester2" name="semester[]" value="2" 
                                       {{ is_array(old('semester')) && in_array(2, old('semester')) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="semester2">Semester 2 (Genap)</label>
                            </div>
                            <br>
                            <small class="text-muted">Pilih semester yang diajar</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Madrasah Specific Fields --}}
        <div class="card card-info card-outline mapel-form-card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-mosque"></i> Konfigurasi Madrasah (Kemenag)</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_mapel_agama" name="is_mapel_agama" value="1" 
                                   {{ old('is_mapel_agama') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_mapel_agama">Mapel Pendidikan Agama</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_rumpun_pai" name="is_rumpun_pai" value="1" 
                                   {{ old('is_rumpun_pai') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_rumpun_pai">Rumpun PAI (Madrasah)</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_bahasa_arab" name="is_bahasa_arab" value="1" 
                                   {{ old('is_bahasa_arab') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_bahasa_arab">Bahasa Arab</label>
                        </div>
                    </div>
                </div>

                <div class="row mt-3" id="jenis-agama-container" style="display: none;">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="jenis_agama">Jenis Agama</label>
                            <select name="jenis_agama" id="jenis_agama" class="form-control @error('jenis_agama') is-invalid @enderror">
                                <option value="">-- Pilih Jenis Agama --</option>
                                <option value="islam" {{ old('jenis_agama') == 'islam' ? 'selected' : '' }}>Islam</option>
                                <option value="kristen" {{ old('jenis_agama') == 'kristen' ? 'selected' : '' }}>Kristen</option>
                                <option value="katolik" {{ old('jenis_agama') == 'katolik' ? 'selected' : '' }}>Katolik</option>
                                <option value="hindu" {{ old('jenis_agama') == 'hindu' ? 'selected' : '' }}>Hindu</option>
                                <option value="buddha" {{ old('jenis_agama') == 'buddha' ? 'selected' : '' }}>Buddha</option>
                                <option value="khonghucu" {{ old('jenis_agama') == 'khonghucu' ? 'selected' : '' }}>Khonghucu</option>
                            </select>
                            @error('jenis_agama')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row" id="sub-pai-container" style="display: none;">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="sub_pai">Sub PAI (Madrasah)</label>
                            <select name="sub_pai" id="sub_pai" class="form-control @error('sub_pai') is-invalid @enderror">
                                <option value="">-- Pilih Sub PAI --</option>
                                <option value="quran_hadits" {{ old('sub_pai') == 'quran_hadits' ? 'selected' : '' }}>Al-Quran Hadits</option>
                                <option value="akidah_akhlak" {{ old('sub_pai') == 'akidah_akhlak' ? 'selected' : '' }}>Akidah Akhlak</option>
                                <option value="fikih" {{ old('sub_pai') == 'fikih' ? 'selected' : '' }}>Fikih</option>
                                <option value="ski" {{ old('sub_pai') == 'ski' ? 'selected' : '' }}>SKI (Sejarah Kebudayaan Islam)</option>
                            </select>
                            @error('sub_pai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kurikulum Merdeka & KTSP --}}
        <div class="card card-success card-outline mapel-form-card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-graduation-cap"></i> Konfigurasi Kurikulum Khusus</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_mapel_pilihan" name="is_mapel_pilihan" value="1" 
                                   {{ old('is_mapel_pilihan') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_mapel_pilihan">Mapel Pilihan (Merdeka)</label>
                        </div>
                        <small class="text-muted">Untuk Kurikulum Merdeka</small>
                    </div>
                    <div class="col-md-4">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_projek_p5" name="is_projek_p5" value="1" 
                                   {{ old('is_projek_p5') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_projek_p5">Projek P5 (Merdeka)</label>
                        </div>
                        <small class="text-muted">Projek Penguatan Profil Pelajar Pancasila</small>
                    </div>
                    <div class="col-md-4">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_muatan_lokal" name="is_muatan_lokal" value="1" 
                                   {{ old('is_muatan_lokal') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_muatan_lokal">Muatan Lokal (KTSP)</label>
                        </div>
                        <small class="text-muted">Untuk KTSP</small>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="capaian_pembelajaran">Capaian Pembelajaran</label>
                            <textarea name="capaian_pembelajaran" id="capaian_pembelajaran" rows="4" 
                                      class="form-control @error('capaian_pembelajaran') is-invalid @enderror" 
                                      placeholder="Tuliskan capaian pembelajaran untuk mata pelajaran ini...">{{ old('capaian_pembelajaran') }}</textarea>
                            @error('capaian_pembelajaran')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Capaian pembelajaran untuk Kurikulum Merdeka</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Additional Info --}}
        <div class="card card-secondary card-outline mapel-form-card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-alt"></i> Informasi Tambahan</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="deskripsi">Deskripsi</label>
                    <textarea name="deskripsi" id="deskripsi" rows="3" class="form-control @error('deskripsi') is-invalid @enderror" 
                              placeholder="Deskripsi singkat tentang mata pelajaran...">{{ old('deskripsi') }}</textarea>
                    @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" 
                           {{ old('is_active', 1) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="is_active">Aktif</label>
                </div>
            </div>
        </div>

        <div class="mapel-form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan
                </button>
                <a href="{{ route('admin.mapel.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
        </div>
    </form>
@stop

@section('css')
    <style>
        .mapel-form-page{max-width:1180px;margin:0 auto 28px}.mapel-form-hero{display:flex;align-items:center;justify-content:space-between;gap:22px;padding:25px 28px;margin-bottom:18px;border-radius:20px;color:#fff;background:linear-gradient(120deg,#3f63e9,#247f93);box-shadow:0 16px 34px rgba(43,75,153,.16)}
        .mapel-form-hero__eyebrow{display:block;font-size:.75rem;font-weight:800;letter-spacing:.06em}.mapel-form-hero h2{margin:8px 0 5px;font-size:1.65rem;font-weight:800}.mapel-form-hero p{margin:0;color:rgba(255,255,255,.86)}.mapel-form-hero__code{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:5px;min-width:130px;min-height:74px;padding:12px 16px;border:1px solid rgba(255,255,255,.25);border-radius:13px;background:rgba(16,37,105,.2);font-size:1.05rem}.mapel-form-hero__code strong{font-size:.85rem}
        .mapel-form-card{border:1px solid #dfe7f4;border-radius:17px;box-shadow:0 9px 25px rgba(31,53,91,.055);overflow:hidden}.mapel-form-card>.card-header{padding:15px 20px;border-bottom:1px solid #e7edf6;background:#fff}.mapel-form-card .card-title{font-size:1rem;font-weight:800;color:#20365f}.mapel-form-card .card-title i{margin-right:7px;color:#426deb}.mapel-form-card>.card-body{padding:22px 20px;background:#fff}.mapel-form-card .form-group{margin-bottom:1.2rem}.mapel-form-card label{font-size:.76rem;font-weight:800;letter-spacing:.025em;color:#526783}.mapel-form-card .form-control{min-height:41px;border-color:#d8e2f0;border-radius:9px;color:#30415e;font-size:.9rem;box-shadow:none}.mapel-form-card textarea.form-control{min-height:96px}.mapel-form-card .form-control:focus{border-color:#5279ed;box-shadow:0 0 0 .18rem rgba(82,121,237,.12)}.mapel-form-card .text-muted{font-size:.75rem;color:#7889a3!important}.mapel-form-card .custom-control{padding:14px 12px 12px 2rem;border-radius:10px;background:#f7f9fc}.mapel-form-card .custom-control-label{font-size:.82rem;color:#344966}.mapel-form-actions{display:flex;justify-content:flex-end;gap:9px;padding:16px 20px;margin-top:4px;border:1px solid #dfe7f4;border-radius:15px;background:#fff;box-shadow:0 9px 25px rgba(31,53,91,.05)}.mapel-form-actions .btn{border-radius:9px;font-weight:700;padding:.52rem 1rem}
        @media(max-width:767px){.mapel-form-hero{align-items:flex-start;flex-direction:column;padding:22px}.mapel-form-hero h2{font-size:1.4rem}.mapel-form-hero__code{width:100%;align-items:flex-start}.mapel-form-card>.card-header,.mapel-form-card>.card-body{padding-left:15px;padding-right:15px}.mapel-form-actions{justify-content:stretch;padding:14px}.mapel-form-actions .btn{flex:1}}
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Toggle jenis agama field
            function toggleJenisAgama() {
                if ($('#is_mapel_agama').is(':checked')) {
                    $('#jenis-agama-container').show();
                    $('#jenis_agama').prop('required', true);
                } else {
                    $('#jenis-agama-container').hide();
                    $('#jenis_agama').prop('required', false);
                    $('#jenis_agama').val('');
                }
            }

            // Toggle sub PAI field
            function toggleSubPai() {
                if ($('#is_rumpun_pai').is(':checked')) {
                    $('#sub-pai-container').show();
                    $('#sub_pai').prop('required', true);
                    // Auto check mapel agama and set to islam
                    $('#is_mapel_agama').prop('checked', true);
                    $('#jenis_agama').val('islam');
                    toggleJenisAgama();
                } else {
                    $('#sub-pai-container').hide();
                    $('#sub_pai').prop('required', false);
                    $('#sub_pai').val('');
                }
            }

            // Initialize
            toggleJenisAgama();
            toggleSubPai();

            // Event listeners
            $('#is_mapel_agama').change(toggleJenisAgama);
            $('#is_rumpun_pai').change(toggleSubPai);

            // Auto uppercase kode mapel
            $('#kode_mapel').on('input', function() {
                $(this).val($(this).val().toUpperCase());
            });

            // Form validation
            $('#form-mapel').submit(function(e) {
                if ($('#is_mapel_agama').is(':checked') && !$('#jenis_agama').val()) {
                    e.preventDefault();
                    alert('Jenis agama harus dipilih!');
                    $('#jenis_agama').focus();
                    return false;
                }

                if ($('#is_rumpun_pai').is(':checked') && !$('#sub_pai').val()) {
                    e.preventDefault();
                    alert('Sub PAI harus dipilih!');
                    $('#sub_pai').focus();
                    return false;
                }
            });
        });
    </script>
@stop

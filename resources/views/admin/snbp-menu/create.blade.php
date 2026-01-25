@extends('adminlte::page')

@section('title', 'Buat Menu SNBP')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-plus-circle"></i> Buat Menu SNBP</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.snbp-menu.index') }}">Menu SNBP</a></li>
                <li class="breadcrumb-item active">Buat Baru</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    @if($existingMenu)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Perhatian:</strong> Tahun pelajaran aktif ({{ $activeTahun->nama }}) sudah memiliki menu SNBP 
            "<strong>{{ $existingMenu->nama_menu }}</strong>". 
            <a href="{{ route('admin.snbp-menu.edit', $existingMenu) }}">Edit menu tersebut</a> atau tunggu tahun pelajaran baru.
        </div>
    @endif

    <div class="card">
        <div class="card-header bg-primary">
            <h3 class="card-title">
                <i class="fas fa-graduation-cap"></i> Form Menu SNBP Baru
            </h3>
        </div>
        <form action="{{ route('admin.snbp-menu.store') }}" method="POST">
            @csrf
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nama_menu">Nama Menu <span class="text-danger">*</span></label>
                            <input type="text" name="nama_menu" id="nama_menu" 
                                   class="form-control @error('nama_menu') is-invalid @enderror" 
                                   value="{{ old('nama_menu', 'SNBP') }}" 
                                   placeholder="Contoh: SNBP, SNMPTN, dll"
                                   required>
                            <small class="text-muted">Nama menu yang akan ditampilkan kepada siswa kelas 12</small>
                            @error('nama_menu')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tahun_pelajaran_id">Tahun Pelajaran <span class="text-danger">*</span></label>
                            <select name="tahun_pelajaran_id" id="tahun_pelajaran_id" 
                                    class="form-control @error('tahun_pelajaran_id') is-invalid @enderror" required>
                                @foreach($tahunPelajaranList as $tahun)
                                    @if($tahun->is_active)
                                        <option value="{{ $tahun->id }}" 
                                                {{ old('tahun_pelajaran_id', $activeTahun->id ?? '') == $tahun->id ? 'selected' : '' }}
                                                {{ !$tahun->is_active ? 'disabled' : '' }}>
                                            {{ $tahun->nama }} {{ $tahun->is_active ? '(Aktif)' : '' }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <small class="text-muted">Hanya dapat membuat menu untuk tahun pelajaran aktif</small>
                            @error('tahun_pelajaran_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" 
                               {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_active">
                            <strong>Status Aktif</strong> - Menu akan ditampilkan kepada siswa
                        </label>
                    </div>
                </div>

                <hr>

                <h5 class="mb-3"><i class="fas fa-calendar-alt"></i> Periode Tampil</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tanggal_mulai">Tanggal & Jam Mulai</label>
                            <input type="datetime-local" name="tanggal_mulai" id="tanggal_mulai" 
                                   class="form-control @error('tanggal_mulai') is-invalid @enderror"
                                   value="{{ old('tanggal_mulai') }}">
                            <small class="text-muted">Kosongkan jika ingin langsung tampil</small>
                            @error('tanggal_mulai')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tanggal_berakhir">Tanggal & Jam Berakhir</label>
                            <input type="datetime-local" name="tanggal_berakhir" id="tanggal_berakhir" 
                                   class="form-control @error('tanggal_berakhir') is-invalid @enderror"
                                   value="{{ old('tanggal_berakhir') }}">
                            <small class="text-muted">Kosongkan jika tidak ada batas waktu</small>
                            @error('tanggal_berakhir')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <hr>

                <h5 class="mb-3"><i class="fas fa-file-alt"></i> Konten</h5>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="konten_eligible">
                                <i class="fas fa-check-circle text-success"></i> 
                                Konten untuk Siswa <strong class="text-success">ELIGIBLE</strong>
                            </label>
                            <textarea name="konten_eligible" id="konten_eligible" 
                                      class="form-control summernote @error('konten_eligible') is-invalid @enderror" 
                                      rows="10">{{ old('konten_eligible', '<p>Selamat! Anda termasuk dalam daftar siswa <strong>ELIGIBLE</strong> untuk mengikuti program SNBP.</p><p>Silakan persiapkan dokumen yang diperlukan.</p>') }}</textarea>
                            <small class="text-muted">Konten yang ditampilkan kepada siswa yang eligible</small>
                            @error('konten_eligible')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="konten_not_eligible">
                                <i class="fas fa-times-circle text-danger"></i> 
                                Konten untuk Siswa <strong class="text-danger">TIDAK ELIGIBLE</strong>
                            </label>
                            <textarea name="konten_not_eligible" id="konten_not_eligible" 
                                      class="form-control summernote @error('konten_not_eligible') is-invalid @enderror" 
                                      rows="10">{{ old('konten_not_eligible', '<p>Mohon maaf, berdasarkan kriteria yang ditetapkan, Anda <strong>tidak termasuk</strong> dalam daftar siswa eligible untuk SNBP tahun ini.</p><p>Tetap semangat dan fokus pada jalur penerimaan lainnya!</p>') }}</textarea>
                            <small class="text-muted">Konten yang ditampilkan kepada siswa yang tidak eligible</small>
                            @error('konten_not_eligible')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary" {{ $existingMenu ? 'disabled' : '' }}>
                    <i class="fas fa-save"></i> Simpan
                </button>
                <a href="{{ route('admin.snbp-menu.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </form>
    </div>
</div>
@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
<script>
$(document).ready(function() {
    $('.summernote').summernote({
        height: 200,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'italic', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ]
    });
});
</script>
@stop

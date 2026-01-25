@extends('adminlte::page')

@section('title', 'Edit Menu SNBP')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-edit"></i> Edit Menu SNBP</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.snbp-menu.index') }}">Menu SNBP</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-warning">
            <h3 class="card-title">
                <i class="fas fa-graduation-cap"></i> Edit: {{ $snbpMenu->nama_menu }}
            </h3>
        </div>
        <form action="{{ route('admin.snbp-menu.update', $snbpMenu) }}" method="POST">
            @csrf
            @method('PUT')
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
                                   value="{{ old('nama_menu', $snbpMenu->nama_menu) }}" 
                                   placeholder="Contoh: SNBP, SNMPTN, dll"
                                   required>
                            @error('nama_menu')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tahun_pelajaran_id">Tahun Pelajaran</label>
                            <input type="text" class="form-control" 
                                   value="{{ $snbpMenu->tahunPelajaran->nama ?? '-' }}" 
                                   readonly disabled>
                            <small class="text-muted">Tahun pelajaran tidak dapat diubah</small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" 
                               {{ old('is_active', $snbpMenu->is_active) ? 'checked' : '' }}>
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
                                   value="{{ old('tanggal_mulai', $snbpMenu->tanggal_mulai ? $snbpMenu->tanggal_mulai->format('Y-m-d\TH:i') : '') }}">
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
                                   value="{{ old('tanggal_berakhir', $snbpMenu->tanggal_berakhir ? $snbpMenu->tanggal_berakhir->format('Y-m-d\TH:i') : '') }}">
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
                                      rows="10">{{ old('konten_eligible', $snbpMenu->konten_eligible) }}</textarea>
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
                                      rows="10">{{ old('konten_not_eligible', $snbpMenu->konten_not_eligible) }}</textarea>
                            @error('konten_not_eligible')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
                <a href="{{ route('admin.snbp-menu.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <button type="button" class="btn btn-danger float-right" onclick="confirmDelete()">
                    <i class="fas fa-trash"></i> Hapus Menu
                </button>
            </div>
        </form>
    </div>
</div>

<form id="deleteForm" action="{{ route('admin.snbp-menu.destroy', $snbpMenu) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

function confirmDelete() {
    Swal.fire({
        title: 'Hapus Menu SNBP?',
        text: 'Semua data assignment siswa akan ikut terhapus!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('deleteForm').submit();
        }
    });
}
</script>
@stop

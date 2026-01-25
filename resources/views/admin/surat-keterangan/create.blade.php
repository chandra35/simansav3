@extends('adminlte::page')

@section('title', 'Tambah Surat Keterangan')

@section('content_header')
    <h1><i class="fas fa-plus mr-2"></i>Tambah Surat Keterangan</h1>
@stop

@section('plugins.Select2', true)

@section('content')
    <div class="card">
        <form action="{{ route('admin.surat-keterangan.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="template_surat_id">Template Surat <span class="text-danger">*</span></label>
                            <select name="template_surat_id" id="template_surat_id" class="form-control @error('template_surat_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Template --</option>
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}" {{ old('template_surat_id') == $template->id ? 'selected' : '' }}>
                                        {{ $template->nama }} ({{ $template->kategori_label }})
                                    </option>
                                @endforeach
                            </select>
                            @error('template_surat_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="siswa_id">Siswa <span class="text-danger">*</span></label>
                            <select name="siswa_id" id="siswa_id" class="form-control select2 @error('siswa_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Siswa --</option>
                                @foreach($siswa as $s)
                                    <option value="{{ $s->id }}" {{ old('siswa_id') == $s->id ? 'selected' : '' }}>
                                        {{ $s->nisn }} - {{ $s->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('siswa_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="tanggal_surat">Tanggal Surat <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_surat" id="tanggal_surat" 
                                class="form-control @error('tanggal_surat') is-invalid @enderror" 
                                value="{{ old('tanggal_surat', date('Y-m-d')) }}" required>
                            @error('tanggal_surat')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="keperluan">Keperluan <span class="text-danger">*</span></label>
                            <input type="text" name="keperluan" id="keperluan" 
                                class="form-control @error('keperluan') is-invalid @enderror" 
                                value="{{ old('keperluan') }}" required 
                                placeholder="Contoh: Pendaftaran Beasiswa, Pengajuan KIP">
                            @error('keperluan')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="keterangan_tambahan">Keterangan Tambahan</label>
                            <textarea name="keterangan_tambahan" id="keterangan_tambahan" 
                                class="form-control @error('keterangan_tambahan') is-invalid @enderror" 
                                rows="4">{{ old('keterangan_tambahan') }}</textarea>
                            <small class="text-muted">Informasi tambahan yang akan dimasukkan ke surat</small>
                            @error('keterangan_tambahan')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="catatan">Catatan Internal</label>
                            <textarea name="catatan" id="catatan" 
                                class="form-control @error('catatan') is-invalid @enderror" 
                                rows="3">{{ old('catatan') }}</textarea>
                            <small class="text-muted">Catatan ini tidak akan ditampilkan di surat</small>
                            @error('catatan')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('admin.surat-keterangan.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
                <button type="submit" class="btn btn-primary float-right">
                    <i class="fas fa-save mr-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>
@stop

@section('js')
<script>
$(function() {
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });
});
</script>
@stop

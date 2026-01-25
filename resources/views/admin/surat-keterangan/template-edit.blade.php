@extends('adminlte::page')

@section('title', 'Edit Template Surat')

@section('content_header')
    <h1><i class="fas fa-edit mr-2"></i>Edit Template Surat</h1>
@stop

@section('content')
    <div class="card">
        <form action="{{ route('admin.surat-keterangan.template.update', $template->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nama">Nama Template <span class="text-danger">*</span></label>
                            <input type="text" name="nama" id="nama" 
                                class="form-control @error('nama') is-invalid @enderror" 
                                value="{{ old('nama', $template->nama) }}" required>
                            @error('nama')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="kode">Kode Template <span class="text-danger">*</span></label>
                            <input type="text" name="kode" id="kode" 
                                class="form-control @error('kode') is-invalid @enderror" 
                                value="{{ old('kode', $template->kode) }}" required>
                            @error('kode')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="kategori">Kategori <span class="text-danger">*</span></label>
                            <select name="kategori" id="kategori" class="form-control @error('kategori') is-invalid @enderror" required>
                                @foreach($kategori as $key => $label)
                                    <option value="{{ $key }}" {{ old('kategori', $template->kategori) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('kategori')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="keterangan">Keterangan</label>
                            <textarea name="keterangan" id="keterangan" class="form-control @error('keterangan') is-invalid @enderror" 
                                rows="2">{{ old('keterangan', $template->keterangan) }}</textarea>
                            @error('keterangan')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="is_aktif" id="is_aktif" class="custom-control-input" value="1" 
                                    {{ old('is_aktif', $template->is_aktif) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_aktif">
                                    Aktif
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <h5><i class="fas fa-info-circle"></i> Variabel yang tersedia:</h5>
                            <ul class="mb-0">
                                @foreach($variabel as $key => $label)
                                    <li><code>{!! '{' . $key . '}' !!}</code> - {{ $label }}</li>
                                @endforeach
                            </ul>
                        </div>
                        
                        <div class="alert alert-warning">
                            <h5><i class="fas fa-exclamation-triangle"></i> Petunjuk:</h5>
                            <ul class="mb-0">
                                <li>Gunakan variabel di dalam kurung kurawal, misal: <code>{nama}</code></li>
                                <li>Variabel akan diganti dengan data siswa saat surat dicetak</li>
                                <li>Pastikan variabel ditulis dengan benar (huruf kecil, tanpa spasi)</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="template_content">Isi Template <span class="text-danger">*</span></label>
                    <textarea name="template_content" id="template_content" 
                        class="form-control @error('template_content') is-invalid @enderror" 
                        rows="20" required>{{ old('template_content', $template->template_content) }}</textarea>
                    @error('template_content')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('admin.surat-keterangan.template') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
                <button type="submit" class="btn btn-warning float-right">
                    <i class="fas fa-save mr-1"></i> Update
                </button>
            </div>
        </form>
    </div>
@stop

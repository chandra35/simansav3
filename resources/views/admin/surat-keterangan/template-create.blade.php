@extends('adminlte::page')

@section('title', 'Tambah Template Surat')

@section('content_header')
    <h1><i class="fas fa-plus mr-2"></i>Tambah Template Surat</h1>
@stop

@section('content')
    <div class="card">
        <form action="{{ route('admin.surat-keterangan.template.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nama">Nama Template <span class="text-danger">*</span></label>
                            <input type="text" name="nama" id="nama" 
                                class="form-control @error('nama') is-invalid @enderror" 
                                value="{{ old('nama') }}" required>
                            @error('nama')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="kode">Kode Template <span class="text-danger">*</span></label>
                            <input type="text" name="kode" id="kode" 
                                class="form-control @error('kode') is-invalid @enderror" 
                                value="{{ old('kode') }}" required>
                            @error('kode')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="jenis">Kategori Surat <span class="text-danger">*</span></label>
                            <select name="jenis" id="jenis" class="form-control @error('jenis') is-invalid @enderror" required>
                                @foreach($jenis as $key => $label)
                                    <option value="{{ $key }}" {{ old('jenis') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Pilih kategori yang paling sesuai dengan isi surat. Kategori ini menentukan kode nomor surat secara otomatis.</small>
                            @error('jenis')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-0">
                            <label for="kode_klasifikasi">Kode nomor surat <span class="text-muted font-weight-normal">(opsional)</span></label>
                            <select name="kode_klasifikasi" id="kode_klasifikasi" class="form-control @error('kode_klasifikasi') is-invalid @enderror">
                                <option value="">Gunakan kode otomatis sesuai kategori surat</option>
                                @foreach($klasifikasiOptions as $klas)
                                    <option value="{{ $klas->kode_klasifikasi }}" {{ old('kode_klasifikasi') == $klas->kode_klasifikasi ? 'selected' : '' }}>
                                        {{ $klas->kode_klasifikasi }} — {{ $klas->nama }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Biarkan pilihan ini kosong. Ubah hanya bila pedoman tata naskah dinas mengharuskan kode yang berbeda.</small>
                            @error('kode_klasifikasi')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="keterangan">Keterangan</label>
                            <textarea name="keterangan" id="keterangan" class="form-control @error('keterangan') is-invalid @enderror" 
                                rows="2">{{ old('keterangan') }}</textarea>
                            @error('keterangan')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="is_active" id="is_active" class="custom-control-input" value="1" 
                                    {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">
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
                    <label for="konten">Isi Template <span class="text-danger">*</span></label>
                    <textarea name="konten" id="konten" 
                        class="form-control @error('konten') is-invalid @enderror" 
                        rows="15" required>{{ old('konten') }}</textarea>
                    @error('konten')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('admin.surat-keterangan.template') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
                <button type="submit" class="btn btn-primary float-right">
                    <i class="fas fa-save mr-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>
@stop

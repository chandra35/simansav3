@extends('adminlte::page')

@section('title', 'Edit Prestasi Siswa')

@section('content_header')
    <h1><i class="fas fa-edit mr-2"></i>Edit Prestasi Siswa</h1>
@stop

@section('plugins.Select2', true)

@section('content')
    <div class="card">
        <form action="{{ route('admin.prestasi-siswa.update', $prestasiSiswa->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="siswa_id">Siswa <span class="text-danger">*</span></label>
                            <select name="siswa_id" id="siswa_id" class="form-control select2 @error('siswa_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Siswa --</option>
                                @foreach($siswa as $s)
                                    <option value="{{ $s->id }}" {{ old('siswa_id', $prestasiSiswa->siswa_id) == $s->id ? 'selected' : '' }}>
                                        {{ $s->nisn }} - {{ $s->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('siswa_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="tahun_pelajaran_id">Tahun Pelajaran <span class="text-danger">*</span></label>
                            <select name="tahun_pelajaran_id" id="tahun_pelajaran_id" class="form-control @error('tahun_pelajaran_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Tahun Pelajaran --</option>
                                @foreach($tahunPelajaran as $tp)
                                    <option value="{{ $tp->id }}" {{ old('tahun_pelajaran_id', $prestasiSiswa->tahun_pelajaran_id) == $tp->id ? 'selected' : '' }}>
                                        {{ $tp->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tahun_pelajaran_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="nama_prestasi">Nama Prestasi <span class="text-danger">*</span></label>
                            <input type="text" name="nama_prestasi" id="nama_prestasi" 
                                class="form-control @error('nama_prestasi') is-invalid @enderror" 
                                value="{{ old('nama_prestasi', $prestasiSiswa->nama_prestasi) }}" required>
                            @error('nama_prestasi')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="jenis_prestasi">Jenis Prestasi <span class="text-danger">*</span></label>
                            <select name="jenis_prestasi" id="jenis_prestasi" class="form-control @error('jenis_prestasi') is-invalid @enderror" required>
                                @foreach($jenis as $key => $label)
                                    <option value="{{ $key }}" {{ old('jenis_prestasi', $prestasiSiswa->jenis_prestasi) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('jenis_prestasi')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="tingkat">Tingkat <span class="text-danger">*</span></label>
                            <select name="tingkat" id="tingkat" class="form-control @error('tingkat') is-invalid @enderror" required>
                                @foreach($tingkat as $key => $label)
                                    <option value="{{ $key }}" {{ old('tingkat', $prestasiSiswa->tingkat) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('tingkat')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="peringkat">Peringkat <span class="text-danger">*</span></label>
                            <select name="peringkat" id="peringkat" class="form-control @error('peringkat') is-invalid @enderror" required>
                                @foreach($peringkat as $key => $label)
                                    <option value="{{ $key }}" {{ old('peringkat', $prestasiSiswa->peringkat) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('peringkat')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="penyelenggara">Penyelenggara <span class="text-danger">*</span></label>
                            <input type="text" name="penyelenggara" id="penyelenggara" 
                                class="form-control @error('penyelenggara') is-invalid @enderror" 
                                value="{{ old('penyelenggara', $prestasiSiswa->penyelenggara) }}" required>
                            @error('penyelenggara')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="tanggal_prestasi">Tanggal Prestasi <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_prestasi" id="tanggal_prestasi" 
                                class="form-control @error('tanggal_prestasi') is-invalid @enderror" 
                                value="{{ old('tanggal_prestasi', $prestasiSiswa->tanggal_prestasi?->format('Y-m-d')) }}" required>
                            @error('tanggal_prestasi')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="tempat">Tempat</label>
                            <input type="text" name="tempat" id="tempat" 
                                class="form-control @error('tempat') is-invalid @enderror" 
                                value="{{ old('tempat', $prestasiSiswa->tempat) }}">
                            @error('tempat')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="pembina_id">Pembina</label>
                            <select name="pembina_id" id="pembina_id" class="form-control select2 @error('pembina_id') is-invalid @enderror">
                                <option value="">-- Pilih Pembina --</option>
                                @foreach($pembina as $p)
                                    <option value="{{ $p->id }}" {{ old('pembina_id', $prestasiSiswa->pembina_id) == $p->id ? 'selected' : '' }}>
                                        {{ $p->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('pembina_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea name="deskripsi" id="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" 
                                rows="3">{{ old('deskripsi', $prestasiSiswa->deskripsi) }}</textarea>
                            @error('deskripsi')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="sertifikat">Sertifikat</label>
                            @if($prestasiSiswa->sertifikat)
                                <div class="mb-2">
                                    <a href="{{ Storage::url($prestasiSiswa->sertifikat) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-file mr-1"></i> Lihat Sertifikat
                                    </a>
                                </div>
                            @endif
                            <input type="file" name="sertifikat" id="sertifikat" 
                                class="form-control-file @error('sertifikat') is-invalid @enderror" 
                                accept=".pdf,.jpg,.jpeg,.png">
                            <small class="text-muted">Format: PDF, JPG, PNG. Maks 5MB</small>
                            @error('sertifikat')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="foto">Foto Dokumentasi</label>
                            @if($prestasiSiswa->foto)
                                <div class="mb-2">
                                    <img src="{{ Storage::url($prestasiSiswa->foto) }}" alt="Foto" class="img-thumbnail" style="max-height: 100px;">
                                </div>
                            @endif
                            <input type="file" name="foto" id="foto" 
                                class="form-control-file @error('foto') is-invalid @enderror" 
                                accept="image/*">
                            <small class="text-muted">Format: JPG, PNG. Maks 2MB</small>
                            @error('foto')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('admin.prestasi-siswa.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
                <button type="submit" class="btn btn-primary float-right">
                    <i class="fas fa-save mr-1"></i> Update
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

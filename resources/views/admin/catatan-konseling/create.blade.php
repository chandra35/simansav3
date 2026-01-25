@extends('adminlte::page')

@section('title', 'Tambah Catatan Konseling')

@section('content_header')
    <h1><i class="fas fa-plus mr-2"></i>Tambah Catatan Konseling</h1>
@stop

@section('plugins.Select2', true)

@section('content')
    <div class="card">
        <form action="{{ route('admin.catatan-konseling.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="siswa_id">Siswa <span class="text-danger">*</span></label>
                            <select name="siswa_id" id="siswa_id" class="form-control select2 @error('siswa_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Siswa --</option>
                                @foreach($siswa as $s)
                                    <option value="{{ $s->id }}" {{ old('siswa_id') == $s->id ? 'selected' : '' }}>
                                        {{ $s->nis }} - {{ $s->nama }}
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
                                @foreach($tahunPelajaran as $tp)
                                    <option value="{{ $tp->id }}" {{ old('tahun_pelajaran_id') == $tp->id || $tp->is_aktif ? 'selected' : '' }}>
                                        {{ $tp->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tahun_pelajaran_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="konselor_id">Konselor <span class="text-danger">*</span></label>
                            <select name="konselor_id" id="konselor_id" class="form-control select2 @error('konselor_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Konselor --</option>
                                @foreach($konselor as $k)
                                    <option value="{{ $k->id }}" {{ old('konselor_id') == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('konselor_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="tanggal_konseling">Tanggal Konseling <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_konseling" id="tanggal_konseling" 
                                class="form-control @error('tanggal_konseling') is-invalid @enderror" 
                                value="{{ old('tanggal_konseling', date('Y-m-d')) }}" required>
                            @error('tanggal_konseling')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="jenis_konseling">Jenis Konseling <span class="text-danger">*</span></label>
                            <select name="jenis_konseling" id="jenis_konseling" class="form-control @error('jenis_konseling') is-invalid @enderror" required>
                                @foreach($jenis as $key => $label)
                                    <option value="{{ $key }}" {{ old('jenis_konseling') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('jenis_konseling')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="kategori_masalah">Kategori Masalah <span class="text-danger">*</span></label>
                            <select name="kategori_masalah" id="kategori_masalah" class="form-control @error('kategori_masalah') is-invalid @enderror" required>
                                @foreach($kategori as $key => $label)
                                    <option value="{{ $key }}" {{ old('kategori_masalah') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('kategori_masalah')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="deskripsi_masalah">Permasalahan <span class="text-danger">*</span></label>
                            <textarea name="deskripsi_masalah" id="deskripsi_masalah" 
                                class="form-control @error('deskripsi_masalah') is-invalid @enderror" 
                                rows="4" required>{{ old('deskripsi_masalah') }}</textarea>
                            @error('deskripsi_masalah')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="hasil_konseling">Hasil Konseling</label>
                            <textarea name="hasil_konseling" id="hasil_konseling" 
                                class="form-control @error('hasil_konseling') is-invalid @enderror" 
                                rows="4">{{ old('hasil_konseling') }}</textarea>
                            @error('hasil_konseling')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="tindak_lanjut">Tindak Lanjut / Rekomendasi</label>
                            <textarea name="tindak_lanjut" id="tindak_lanjut" 
                                class="form-control @error('tindak_lanjut') is-invalid @enderror" 
                                rows="3">{{ old('tindak_lanjut') }}</textarea>
                            @error('tindak_lanjut')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                @foreach($status as $key => $label)
                                    <option value="{{ $key }}" {{ old('status', 'proses') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('status')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="jadwal_tindak_lanjut">Jadwal Tindak Lanjut</label>
                            <input type="date" name="jadwal_tindak_lanjut" id="jadwal_tindak_lanjut" 
                                class="form-control @error('jadwal_tindak_lanjut') is-invalid @enderror" 
                                value="{{ old('jadwal_tindak_lanjut') }}">
                            @error('jadwal_tindak_lanjut')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="is_rahasia" id="is_rahasia" class="custom-control-input" value="1" 
                                    {{ old('is_rahasia') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_rahasia">
                                    <i class="fas fa-lock"></i> Catatan Rahasia
                                </label>
                            </div>
                            <small class="text-muted">Centang jika catatan ini bersifat sangat privat</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('admin.catatan-konseling.index') }}" class="btn btn-secondary">
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

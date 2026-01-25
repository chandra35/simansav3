@extends('adminlte::page')

@section('title', 'Edit Ekstrakurikuler')

@section('content_header')
    <h1><i class="fas fa-edit mr-2"></i>Edit Ekstrakurikuler</h1>
@stop

@section('plugins.Select2', true)

@section('content')
    <div class="card">
        <form action="{{ route('admin.ekstrakurikuler.update', $ekstrakurikuler->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tahun_pelajaran_id">Tahun Pelajaran <span class="text-danger">*</span></label>
                            <select name="tahun_pelajaran_id" id="tahun_pelajaran_id" class="form-control @error('tahun_pelajaran_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Tahun Pelajaran --</option>
                                @foreach($tahunPelajaran as $tp)
                                    <option value="{{ $tp->id }}" {{ old('tahun_pelajaran_id', $ekstrakurikuler->tahun_pelajaran_id) == $tp->id ? 'selected' : '' }}>
                                        {{ $tp->nama }} {{ $tp->is_aktif ? '(Aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tahun_pelajaran_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="nama">Nama Ekstrakurikuler <span class="text-danger">*</span></label>
                            <input type="text" name="nama" id="nama" 
                                class="form-control @error('nama') is-invalid @enderror" 
                                value="{{ old('nama', $ekstrakurikuler->nama) }}" required>
                            @error('nama')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea name="deskripsi" id="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" 
                                rows="3">{{ old('deskripsi', $ekstrakurikuler->deskripsi) }}</textarea>
                            @error('deskripsi')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="pembina_id">Pembina</label>
                            <select name="pembina_id" id="pembina_id" class="form-control select2 @error('pembina_id') is-invalid @enderror">
                                <option value="">-- Pilih Pembina --</option>
                                @foreach($pembina as $p)
                                    <option value="{{ $p->id }}" {{ old('pembina_id', $ekstrakurikuler->pembina_id) == $p->id ? 'selected' : '' }}>
                                        {{ $p->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('pembina_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="tempat">Tempat Kegiatan</label>
                            <input type="text" name="tempat" id="tempat" 
                                class="form-control @error('tempat') is-invalid @enderror" 
                                value="{{ old('tempat', $ekstrakurikuler->tempat) }}">
                            @error('tempat')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="hari_kegiatan">Hari Kegiatan</label>
                            <select name="hari_kegiatan" id="hari_kegiatan" class="form-control @error('hari_kegiatan') is-invalid @enderror">
                                <option value="">-- Pilih Hari --</option>
                                @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $hari)
                                    <option value="{{ $hari }}" {{ old('hari_kegiatan', $ekstrakurikuler->hari_kegiatan) == $hari ? 'selected' : '' }}>{{ $hari }}</option>
                                @endforeach
                            </select>
                            @error('hari_kegiatan')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="waktu_kegiatan">Waktu Kegiatan</label>
                            <input type="text" name="waktu_kegiatan" id="waktu_kegiatan" 
                                class="form-control @error('waktu_kegiatan') is-invalid @enderror" 
                                value="{{ old('waktu_kegiatan', $ekstrakurikuler->waktu_kegiatan) }}" placeholder="Contoh: 14:00 - 16:00">
                            @error('waktu_kegiatan')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="kuota_max">Kuota Maksimal</label>
                            <input type="number" name="kuota_max" id="kuota_max" 
                                class="form-control @error('kuota_max') is-invalid @enderror" 
                                value="{{ old('kuota_max', $ekstrakurikuler->kuota_max) }}" min="1">
                            <small class="text-muted">Kosongkan jika tidak ada batasan</small>
                            @error('kuota_max')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="biaya">Biaya</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input type="number" name="biaya" id="biaya" 
                                    class="form-control @error('biaya') is-invalid @enderror" 
                                    value="{{ old('biaya', $ekstrakurikuler->biaya) }}" min="0">
                            </div>
                            @error('biaya')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="is_wajib" id="is_wajib" class="custom-control-input" value="1" 
                                    {{ old('is_wajib', $ekstrakurikuler->is_wajib) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_wajib">
                                    Ekstrakurikuler Wajib
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="is_aktif" id="is_aktif" class="custom-control-input" value="1" 
                                    {{ old('is_aktif', $ekstrakurikuler->is_aktif) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_aktif">
                                    Aktif
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('admin.ekstrakurikuler.index') }}" class="btn btn-secondary">
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

@extends('adminlte::page')

@section('title', 'Edit Jurusan PPDB')

@section('content_header')
    <h1><i class="fas fa-graduation-cap mr-2"></i>Edit Jurusan PPDB</h1>
@stop

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <form action="{{ route('admin.settings.jurusan.update', $jurusan) }}" method="POST">
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
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="kode">Kode Jurusan <span class="text-danger">*</span></label>
                                <input type="text" name="kode" id="kode" class="form-control" 
                                       value="{{ old('kode', $jurusan->kode) }}" required maxlength="20"
                                       placeholder="Contoh: RPL, TKJ, MM">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="nama">Nama Jurusan <span class="text-danger">*</span></label>
                                <input type="text" name="nama" id="nama" class="form-control" 
                                       value="{{ old('nama', $jurusan->nama) }}" required
                                       placeholder="Contoh: Rekayasa Perangkat Lunak">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="deskripsi">Deskripsi</label>
                        <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3"
                                  placeholder="Deskripsi singkat tentang jurusan">{{ old('deskripsi', $jurusan->deskripsi) }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="kuota">Kuota <span class="text-danger">*</span></label>
                                <input type="number" name="kuota" id="kuota" class="form-control" 
                                       value="{{ old('kuota', $jurusan->kuota) }}" required min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="urutan">Urutan Tampil</label>
                                <input type="number" name="urutan" id="urutan" class="form-control" 
                                       value="{{ old('urutan', $jurusan->urutan) }}" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="is_active">Status</label>
                                <select name="is_active" id="is_active" class="form-control">
                                    <option value="1" {{ old('is_active', $jurusan->is_active) == 1 ? 'selected' : '' }}>Aktif</option>
                                    <option value="0" {{ old('is_active', $jurusan->is_active) == 0 ? 'selected' : '' }}>Non-aktif</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Statistik:</strong> 
                        {{ $jurusan->pendaftaran()->count() }} pendaftar terdaftar pada jurusan ini.
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.settings.jurusan.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary float-right">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

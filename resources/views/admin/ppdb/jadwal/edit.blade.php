@extends('adminlte::page')

@section('title', 'Edit Jadwal PPDB')

@section('content_header')
    <h1>Edit Jadwal PPDB</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Form Jadwal</h3>
                </div>
                <form action="{{ route('admin.settings.jadwal.update', $jadwal->id) }}" method="POST">
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

                        <div class="form-group">
                            <label for="nama_kegiatan">Nama Kegiatan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_kegiatan') is-invalid @enderror" 
                                   id="nama_kegiatan" name="nama_kegiatan" 
                                   value="{{ old('nama_kegiatan', $jadwal->nama_kegiatan) }}" required>
                            @error('nama_kegiatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                                      id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi', $jadwal->deskripsi) }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tanggal_mulai">Tanggal Mulai <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control @error('tanggal_mulai') is-invalid @enderror" 
                                           id="tanggal_mulai" name="tanggal_mulai" 
                                           value="{{ old('tanggal_mulai', $jadwal->tanggal_mulai->format('Y-m-d\TH:i')) }}" required>
                                    @error('tanggal_mulai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tanggal_selesai">Tanggal Selesai <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control @error('tanggal_selesai') is-invalid @enderror" 
                                           id="tanggal_selesai" name="tanggal_selesai" 
                                           value="{{ old('tanggal_selesai', $jadwal->tanggal_selesai->format('Y-m-d\TH:i')) }}" required>
                                    @error('tanggal_selesai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="warna">Warna</label>
                                    <input type="color" class="form-control @error('warna') is-invalid @enderror" 
                                           id="warna" name="warna" value="{{ old('warna', $jadwal->warna) }}" 
                                           style="height: 40px;">
                                    @error('warna')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="icon">Icon (FontAwesome)</label>
                                    <select class="form-control @error('icon') is-invalid @enderror" 
                                            id="icon" name="icon">
                                        <option value="fas fa-calendar" {{ old('icon', $jadwal->icon) == 'fas fa-calendar' ? 'selected' : '' }}>📅 Calendar</option>
                                        <option value="fas fa-edit" {{ old('icon', $jadwal->icon) == 'fas fa-edit' ? 'selected' : '' }}>✏️ Edit/Pendaftaran</option>
                                        <option value="fas fa-file-alt" {{ old('icon', $jadwal->icon) == 'fas fa-file-alt' ? 'selected' : '' }}>📄 Dokumen</option>
                                        <option value="fas fa-check-circle" {{ old('icon', $jadwal->icon) == 'fas fa-check-circle' ? 'selected' : '' }}>✅ Verifikasi</option>
                                        <option value="fas fa-clipboard-check" {{ old('icon', $jadwal->icon) == 'fas fa-clipboard-check' ? 'selected' : '' }}>📋 Seleksi</option>
                                        <option value="fas fa-bullhorn" {{ old('icon', $jadwal->icon) == 'fas fa-bullhorn' ? 'selected' : '' }}>📢 Pengumuman</option>
                                        <option value="fas fa-user-check" {{ old('icon', $jadwal->icon) == 'fas fa-user-check' ? 'selected' : '' }}>👤 Daftar Ulang</option>
                                        <option value="fas fa-graduation-cap" {{ old('icon', $jadwal->icon) == 'fas fa-graduation-cap' ? 'selected' : '' }}>🎓 Kelulusan</option>
                                        <option value="fas fa-money-bill" {{ old('icon', $jadwal->icon) == 'fas fa-money-bill' ? 'selected' : '' }}>💰 Pembayaran</option>
                                        <option value="fas fa-clock" {{ old('icon', $jadwal->icon) == 'fas fa-clock' ? 'selected' : '' }}>⏰ Waktu</option>
                                    </select>
                                    @error('icon')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="urutan">Urutan <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('urutan') is-invalid @enderror" 
                                           id="urutan" name="urutan" value="{{ old('urutan', $jadwal->urutan) }}" min="0" required>
                                    @error('urutan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is_active" 
                                       name="is_active" value="1" {{ old('is_active', $jadwal->is_active) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">
                                    Aktif (Tampilkan di halaman depan)
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update
                        </button>
                        <a href="{{ route('admin.settings.jadwal.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Status Jadwal</h3>
                </div>
                <div class="card-body">
                    <div class="info-box bg-{{ $jadwal->status_color }}">
                        <span class="info-box-icon"><i class="{{ $jadwal->icon }}"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">{{ $jadwal->nama_kegiatan }}</span>
                            <span class="info-box-number">{{ $jadwal->status_label }}</span>
                            <span class="progress-description">
                                {{ $jadwal->date_range }}
                            </span>
                        </div>
                    </div>
                    
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td><i class="fas fa-calendar-plus"></i> Dibuat:</td>
                            <td>{{ $jadwal->created_at->format('d M Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-edit"></i> Diupdate:</td>
                            <td>{{ $jadwal->updated_at->format('d M Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

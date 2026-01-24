@extends('adminlte::page')

@section('title', 'Tambah Jadwal PPDB')

@section('content_header')
    <h1>Tambah Jadwal PPDB</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Form Jadwal</h3>
                </div>
                <form action="{{ route('admin.settings.jadwal.store') }}" method="POST">
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

                        <div class="form-group">
                            <label for="nama_kegiatan">Nama Kegiatan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_kegiatan') is-invalid @enderror" 
                                   id="nama_kegiatan" name="nama_kegiatan" value="{{ old('nama_kegiatan') }}" 
                                   placeholder="Contoh: Pendaftaran Online" required>
                            @error('nama_kegiatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                                      id="deskripsi" name="deskripsi" rows="3" 
                                      placeholder="Deskripsi singkat kegiatan...">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tanggal_mulai">Tanggal Mulai <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control @error('tanggal_mulai') is-invalid @enderror" 
                                           id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}" required>
                                    @error('tanggal_mulai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tanggal_selesai">Tanggal Selesai <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control @error('tanggal_selesai') is-invalid @enderror" 
                                           id="tanggal_selesai" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}" required>
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
                                           id="warna" name="warna" value="{{ old('warna', '#007bff') }}" 
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
                                        <option value="fas fa-calendar" {{ old('icon', 'fas fa-calendar') == 'fas fa-calendar' ? 'selected' : '' }}>📅 Calendar</option>
                                        <option value="fas fa-edit" {{ old('icon') == 'fas fa-edit' ? 'selected' : '' }}>✏️ Edit/Pendaftaran</option>
                                        <option value="fas fa-file-alt" {{ old('icon') == 'fas fa-file-alt' ? 'selected' : '' }}>📄 Dokumen</option>
                                        <option value="fas fa-check-circle" {{ old('icon') == 'fas fa-check-circle' ? 'selected' : '' }}>✅ Verifikasi</option>
                                        <option value="fas fa-clipboard-check" {{ old('icon') == 'fas fa-clipboard-check' ? 'selected' : '' }}>📋 Seleksi</option>
                                        <option value="fas fa-bullhorn" {{ old('icon') == 'fas fa-bullhorn' ? 'selected' : '' }}>📢 Pengumuman</option>
                                        <option value="fas fa-user-check" {{ old('icon') == 'fas fa-user-check' ? 'selected' : '' }}>👤 Daftar Ulang</option>
                                        <option value="fas fa-graduation-cap" {{ old('icon') == 'fas fa-graduation-cap' ? 'selected' : '' }}>🎓 Kelulusan</option>
                                        <option value="fas fa-money-bill" {{ old('icon') == 'fas fa-money-bill' ? 'selected' : '' }}>💰 Pembayaran</option>
                                        <option value="fas fa-clock" {{ old('icon') == 'fas fa-clock' ? 'selected' : '' }}>⏰ Waktu</option>
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
                                           id="urutan" name="urutan" value="{{ old('urutan', 0) }}" min="0" required>
                                    @error('urutan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is_active" 
                                       name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">
                                    Aktif (Tampilkan di halaman depan)
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan
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
                    <h3 class="card-title">Preview</h3>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="time-label">
                            <span class="bg-info" id="previewDate">Tanggal</span>
                        </div>
                        <div>
                            <i id="previewIcon" class="fas fa-calendar" style="background-color: #007bff; color: white;"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header" id="previewNama">Nama Kegiatan</h3>
                                <div class="timeline-body" id="previewDeskripsi">
                                    Deskripsi kegiatan akan muncul di sini
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Contoh Jadwal</h3>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><i class="fas fa-edit text-primary"></i> Pendaftaran Online</li>
                        <li><i class="fas fa-file-alt text-info"></i> Upload Dokumen</li>
                        <li><i class="fas fa-check-circle text-success"></i> Verifikasi Berkas</li>
                        <li><i class="fas fa-clipboard-check text-warning"></i> Seleksi</li>
                        <li><i class="fas fa-bullhorn text-danger"></i> Pengumuman</li>
                        <li><i class="fas fa-user-check text-secondary"></i> Daftar Ulang</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Live preview
    function updatePreview() {
        $('#previewNama').text($('#nama_kegiatan').val() || 'Nama Kegiatan');
        $('#previewDeskripsi').text($('#deskripsi').val() || 'Deskripsi kegiatan akan muncul di sini');
        $('#previewIcon').attr('class', $('#icon').val()).css('background-color', $('#warna').val());
        
        if ($('#tanggal_mulai').val()) {
            let date = new Date($('#tanggal_mulai').val());
            $('#previewDate').text(date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }));
        }
    }
    
    $('#nama_kegiatan, #deskripsi, #icon, #warna, #tanggal_mulai').on('input change', updatePreview);
});
</script>
@stop

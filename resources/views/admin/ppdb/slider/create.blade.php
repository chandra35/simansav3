@extends('adminlte::page')

@section('title', 'Tambah Slider')

@section('content_header')
    <h1>Tambah Slider</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Form Tambah Slider</h3>
            <div class="card-tools">
                <a href="{{ route('admin.settings.slider.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
        <form action="{{ route('admin.settings.slider.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label for="judul">Judul <span class="text-danger">*</span></label>
                    <input type="text" 
                           name="judul" 
                           id="judul" 
                           class="form-control @error('judul') is-invalid @enderror" 
                           value="{{ old('judul') }}" 
                           required>
                    @error('judul')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="deskripsi">Deskripsi</label>
                    <textarea name="deskripsi" 
                              id="deskripsi" 
                              rows="3" 
                              class="form-control @error('deskripsi') is-invalid @enderror">{{ old('deskripsi') }}</textarea>
                    @error('deskripsi')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="gambar">Gambar Slider <span class="text-danger">*</span></label>
                    <div class="custom-file">
                        <input type="file" 
                               name="gambar" 
                               id="gambar" 
                               class="custom-file-input @error('gambar') is-invalid @enderror" 
                               accept="image/jpeg,image/jpg,image/png"
                               required
                               onchange="previewImage(event)">
                        <label class="custom-file-label" for="gambar">Pilih gambar...</label>
                        @error('gambar')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <small class="form-text text-muted">Format: JPG, JPEG, PNG. Maksimal: 2MB. Rekomendasi ukuran: 1920x500px</small>
                    
                    <div class="mt-3" id="preview-container" style="display: none;">
                        <img id="preview-image" src="" alt="Preview" style="max-width: 100%; max-height: 300px; border-radius: 8px;">
                    </div>
                </div>

                <div class="form-group">
                    <label for="link">Link (Optional)</label>
                    <input type="url" 
                           name="link" 
                           id="link" 
                           class="form-control @error('link') is-invalid @enderror" 
                           value="{{ old('link') }}" 
                           placeholder="https://example.com">
                    @error('link')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">Tombol "Selengkapnya" akan mengarah ke link ini</small>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="urutan">Urutan <span class="text-danger">*</span></label>
                            <input type="number" 
                                   name="urutan" 
                                   id="urutan" 
                                   class="form-control @error('urutan') is-invalid @enderror" 
                                   value="{{ old('urutan', 1) }}" 
                                   min="0"
                                   required>
                            @error('urutan')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">Slider dengan urutan lebih kecil akan tampil lebih dulu</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select name="status" 
                                    id="status" 
                                    class="form-control @error('status') is-invalid @enderror" 
                                    required>
                                <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan
                </button>
                <a href="{{ route('admin.settings.slider.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
        </form>
    </div>
@stop

@section('js')
<script>
function previewImage(event) {
    const input = event.target;
    const preview = document.getElementById('preview-image');
    const container = document.getElementById('preview-container');
    const label = input.nextElementSibling;
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            container.style.display = 'block';
        };
        
        reader.readAsDataURL(input.files[0]);
        label.textContent = input.files[0].name;
    }
}

// bs-custom-file-input
$(document).ready(function() {
    bsCustomFileInput.init();
});
</script>
@stop

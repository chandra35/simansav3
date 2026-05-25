@csrf

<div class="form-group">
    <label for="title">Judul</label>
    <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $download->title ?? '') }}" required>
    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label for="category_id">Kategori</label>
    <select id="category_id" name="category_id" class="form-control @error('category_id') is-invalid @enderror">
        <option value="">Tanpa Kategori</option>
        @foreach($categories as $category)
            <option value="{{ $category->id }}" {{ old('category_id', $download->category_id ?? '') === $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
        @endforeach
    </select>
    @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label for="description">Deskripsi</label>
    <textarea id="description" name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $download->description ?? '') }}</textarea>
    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label for="source">Upload Ke</label>
    <select id="source" name="source" class="form-control @error('source') is-invalid @enderror" required>
        <option value="local" {{ old('source', $download->source ?? $settings->default_storage) === 'local' ? 'selected' : '' }}>Local Simansa</option>
        <option value="gdrive" {{ old('source', $download->source ?? $settings->default_storage) === 'gdrive' ? 'selected' : '' }}>Google Drive</option>
    </select>
    @error('source')<div class="invalid-feedback">{{ $message }}</div>@enderror
    <small class="text-muted">Gunakan Google Drive untuk meringankan penyimpanan dan bandwidth VM.</small>
</div>

<div class="form-group">
    <label for="file">File {{ isset($download) ? '(kosongkan jika tidak ganti file)' : '' }}</label>
    <input type="file" id="file" name="file" class="form-control-file @error('file') is-invalid @enderror" {{ isset($download) ? '' : 'required' }}>
    @error('file')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
</div>

<div class="row">
    <div class="col-md-4">
        <div class="custom-control custom-switch">
            <input type="checkbox" id="is_published" name="is_published" value="1" class="custom-control-input" {{ old('is_published', $download->is_published ?? true) ? 'checked' : '' }}>
            <label class="custom-control-label" for="is_published">Published</label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="custom-control custom-switch">
            <input type="checkbox" id="is_featured" name="is_featured" value="1" class="custom-control-input" {{ old('is_featured', $download->is_featured ?? false) ? 'checked' : '' }}>
            <label class="custom-control-label" for="is_featured">Featured</label>
        </div>
    </div>
    <div class="col-md-4">
        <input type="datetime-local" name="published_at" class="form-control" value="{{ old('published_at', isset($download) && $download->published_at ? $download->published_at->format('Y-m-d\\TH:i') : now()->format('Y-m-d\\TH:i')) }}">
    </div>
</div>

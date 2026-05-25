@extends('adminlte::page')

@section('title', 'Kategori Download')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-folder-open"></i> Kategori Download</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('admin.downloads.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>
@endsection

@section('content')
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="row">
        <div class="col-md-4">
            <div class="card simansa-management-card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-plus"></i> Tambah Kategori</h3></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.download-categories.store') }}">
                        @csrf
                        <div class="form-group">
                            <label>Nama</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Icon (FontAwesome)</label>
                            <input type="text" name="icon" class="form-control" value="fas fa-folder-open">
                        </div>
                        <div class="form-group">
                            <label>Warna</label>
                            <input type="color" name="color" class="form-control" value="#0ea5e9">
                        </div>
                        <div class="form-group">
                            <label>Urutan</label>
                            <input type="number" min="0" name="sort_order" class="form-control" value="0">
                        </div>
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="is_active_new" name="is_active" value="1" checked>
                            <label class="custom-control-label" for="is_active_new">Aktif</label>
                        </div>
                        <button class="btn btn-primary btn-block" type="submit">Simpan</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card simansa-management-card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-list"></i> Daftar Kategori</h3></div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Icon</th>
                                <th>Warna</th>
                                <th>Urutan</th>
                                <th>File</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $item)
                                <tr>
                                    <td><strong>{{ $item->name }}</strong><br><small class="text-muted">{{ $item->description }}</small></td>
                                    <td><i class="{{ $item->icon }}"></i> <small>{{ $item->icon }}</small></td>
                                    <td><span class="badge" style="background: {{ $item->color }}; color:#fff;">{{ $item->color }}</span></td>
                                    <td>{{ $item->sort_order }}</td>
                                    <td>{{ $item->downloads_count }}</td>
                                    <td>{!! $item->is_active ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-secondary">Nonaktif</span>' !!}</td>
                                    <td>
                                        <button class="btn btn-xs btn-warning" data-toggle="modal" data-target="#editModal{{ $item->id }}"><i class="fas fa-edit"></i></button>
                                        <form action="{{ route('admin.download-categories.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus kategori ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-xs btn-danger" type="submit"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>

                                <div class="modal fade" id="editModal{{ $item->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('admin.download-categories.update', $item) }}">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Kategori</h5>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group"><label>Nama</label><input type="text" name="name" class="form-control" value="{{ $item->name }}" required></div>
                                                    <div class="form-group"><label>Deskripsi</label><textarea name="description" class="form-control" rows="2">{{ $item->description }}</textarea></div>
                                                    <div class="form-group"><label>Icon</label><input type="text" name="icon" class="form-control" value="{{ $item->icon }}"></div>
                                                    <div class="form-group"><label>Warna</label><input type="color" name="color" class="form-control" value="{{ $item->color }}"></div>
                                                    <div class="form-group"><label>Urutan</label><input type="number" min="0" name="sort_order" class="form-control" value="{{ $item->sort_order }}"></div>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" id="is_active_{{ $item->id }}" name="is_active" value="1" {{ $item->is_active ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="is_active_{{ $item->id }}">Aktif</label>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary">Update</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada kategori.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

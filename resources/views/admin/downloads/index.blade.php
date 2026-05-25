@extends('adminlte::page')

@section('title', 'Download Center')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-download"></i> Download Center</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('admin.download-settings.edit') }}" class="btn btn-secondary">
                    <i class="fas fa-cog"></i> Pengaturan Storage
                </a>
                <a href="{{ route('admin.download-categories.index') }}" class="btn btn-info">
                    <i class="fas fa-folder"></i> Kategori
                </a>
                <a href="{{ route('admin.downloads.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah File
                </a>
            </div>
        </div>
    </div>
@endsection

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card simansa-management-card mb-3">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filter</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.downloads.index') }}">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Cari judul/file...">
                    </div>
                    <div class="col-md-3 mb-2">
                        <select name="category" class="form-control">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category') === $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <select name="source" class="form-control">
                            <option value="">Semua Storage</option>
                            <option value="local" {{ request('source') === 'local' ? 'selected' : '' }}>Local</option>
                            <option value="gdrive" {{ request('source') === 'gdrive' ? 'selected' : '' }}>GDrive</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <select name="status" class="form-control">
                            <option value="">Semua Status</option>
                            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <button class="btn btn-primary btn-block" type="submit"><i class="fas fa-search"></i> Terapkan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card simansa-management-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-list"></i> Daftar File</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap mb-0">
                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Kategori</th>
                        <th>Ext</th>
                        <th>Ukuran</th>
                        <th>Storage</th>
                        <th>Status</th>
                        <th>Download</th>
                        <th style="width: 180px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($downloads as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->title }}</strong><br>
                                <small class="text-muted">{{ $item->file_name_original }}</small>
                            </td>
                            <td>{{ $item->category->name ?? '-' }}</td>
                            <td><span class="badge badge-secondary">{{ strtoupper($item->file_extension ?: 'FILE') }}</span></td>
                            <td>{{ $item->formatted_size }}</td>
                            <td>
                                @if($item->source === 'gdrive')
                                    <span class="badge badge-info">Google Drive</span>
                                @else
                                    <span class="badge badge-primary">Local</span>
                                @endif
                            </td>
                            <td>
                                @if($item->is_published)
                                    <span class="badge badge-success">Published</span>
                                @else
                                    <span class="badge badge-warning">Draft</span>
                                @endif
                            </td>
                            <td>{{ number_format($item->download_count, 0, ',', '.') }}</td>
                            <td>
                                <a href="{{ route('downloads.download', $item) }}" class="btn btn-xs btn-info" target="_blank">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.downloads.edit', $item) }}" class="btn btn-xs btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.downloads.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus file ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-xs btn-danger" type="submit"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Belum ada file download.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $downloads->links() }}
        </div>
    </div>
@endsection

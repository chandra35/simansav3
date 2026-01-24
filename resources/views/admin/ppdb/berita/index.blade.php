@extends('adminlte::page')

@section('title', 'Kelola Berita')

@section('content_header')
    <h1>Kelola Berita</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Berita</h3>
            <div class="card-tools">
                <a href="{{ route('admin.settings.berita.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Tambah Berita
                </a>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    {{ session('success') }}
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="beritaTable">
                    <thead>
                        <tr>
                            <th width="60">No</th>
                            <th>Gambar</th>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Penulis</th>
                            <th>Views</th>
                            <th>Status</th>
                            <th>Facebook</th>
                            <th width="180">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($beritas as $index => $berita)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>
                                    @if($berita->gambar && file_exists(public_path('storage/' . $berita->gambar)))
                                        <img src="{{ asset('storage/' . $berita->gambar) }}" 
                                             alt="{{ $berita->judul }}" 
                                             style="max-width: 100px; max-height: 60px; object-fit: cover;">
                                    @else
                                        <div style="width: 100px; height: 60px; background: #ddd; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-image fa-2x text-muted"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ Str::limit($berita->judul, 40) }}</strong>
                                    @if($berita->is_featured)
                                        <span class="badge badge-warning ml-1"><i class="fas fa-star"></i></span>
                                    @endif
                                    <br>
                                    <small class="text-muted">{{ $berita->created_at->format('d M Y H:i') }}</small>
                                </td>
                                <td>
                                    @if($berita->kategori)
                                        <span class="badge badge-info">{{ $berita->kategori }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $berita->penulis ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="badge badge-light">
                                        <i class="fas fa-eye"></i> {{ number_format($berita->views) }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-toggle-status {{ $berita->status === 'active' ? 'btn-success' : ($berita->status === 'draft' ? 'btn-warning' : 'btn-secondary') }}" 
                                            data-id="{{ $berita->id }}">
                                        <i class="fas {{ $berita->status === 'active' ? 'fa-check' : ($berita->status === 'draft' ? 'fa-pencil-alt' : 'fa-times') }}"></i>
                                        {{ ucfirst($berita->status) }}
                                    </button>
                                </td>
                                <td class="text-center">
                                    @if($berita->shared_to_facebook)
                                        <span class="badge badge-primary"><i class="fab fa-facebook"></i> Shared</span>
                                    @else
                                        <button class="btn btn-sm btn-outline-primary btn-share-facebook" data-id="{{ $berita->id }}">
                                            <i class="fab fa-facebook-f"></i>
                                        </button>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('ppdb.berita.detail', $berita->slug) }}" 
                                           target="_blank"
                                           class="btn btn-sm btn-info" title="Preview">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                        <a href="{{ route('admin.settings.berita.edit', $berita->id) }}" 
                                           class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.settings.berita.destroy', $berita->id) }}" 
                                              method="POST" 
                                              style="display: inline;"
                                              onsubmit="return confirm('Yakin ingin menghapus berita ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">Belum ada berita</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
.btn-toggle-status {
    min-width: 90px;
}
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Toggle Status
    $('.btn-toggle-status').on('click', function() {
        const btn = $(this);
        const id = btn.data('id');
        
        $.ajax({
            url: '{{ url("admin/ppdb/berita") }}/' + id + '/toggle-status',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            },
            error: function() {
                alert('Gagal mengubah status');
            }
        });
    });
    
    // Share to Facebook
    $('.btn-share-facebook').on('click', function() {
        const btn = $(this);
        const id = btn.data('id');
        
        if (!confirm('Share berita ini ke Facebook?')) return;
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        $.ajax({
            url: '{{ url("admin/ppdb/berita") }}/' + id + '/share-facebook',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    alert('Berhasil di-share ke Facebook!');
                    location.reload();
                } else {
                    alert(response.message || 'Gagal share ke Facebook');
                    btn.prop('disabled', false).html('<i class="fab fa-facebook-f"></i>');
                }
            },
            error: function() {
                alert('Gagal share ke Facebook');
                btn.prop('disabled', false).html('<i class="fab fa-facebook-f"></i>');
            }
        });
    });
});
</script>
@stop

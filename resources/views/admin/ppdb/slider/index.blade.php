@extends('adminlte::page')

@section('title', 'Kelola Slider')

@section('content_header')
    <h1>Kelola Slider</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Slider</h3>
            <div class="card-tools">
                <a href="{{ route('admin.settings.slider.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Tambah Slider
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

            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th width="60">Urutan</th>
                        <th>Gambar</th>
                        <th>Judul</th>
                        <th>Deskripsi</th>
                        <th>Link</th>
                        <th>Status</th>
                        <th width="200">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sliders as $slider)
                        <tr>
                            <td class="text-center">{{ $slider->urutan }}</td>
                            <td>
                                @if($slider->gambar && file_exists(public_path('storage/' . $slider->gambar)))
                                    <img src="{{ asset('storage/' . $slider->gambar) }}" 
                                         alt="{{ $slider->judul }}" 
                                         style="max-width: 120px; max-height: 80px; object-fit: cover;">
                                @else
                                    <div style="width: 120px; height: 80px; background: #ddd; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-image fa-2x text-muted"></i>
                                    </div>
                                @endif
                            </td>
                            <td><strong>{{ $slider->judul }}</strong></td>
                            <td>{{ Str::limit($slider->deskripsi, 50) }}</td>
                            <td>
                                @if($slider->link)
                                    <a href="{{ $slider->link }}" target="_blank">{{ Str::limit($slider->link, 30) }}</a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-sm btn-toggle-status {{ $slider->status === 'active' ? 'btn-success' : 'btn-secondary' }}" 
                                        data-id="{{ $slider->id }}">
                                    <i class="fas {{ $slider->status === 'active' ? 'fa-check' : 'fa-times' }}"></i>
                                    {{ ucfirst($slider->status) }}
                                </button>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.settings.slider.edit', $slider->id) }}" 
                                       class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.settings.slider.destroy', $slider->id) }}" 
                                          method="POST" 
                                          style="display: inline;"
                                          onsubmit="return confirm('Yakin ingin menghapus slider ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Belum ada slider</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('js')
<script>
$(document).ready(function() {
    $('.btn-toggle-status').click(function() {
        const btn = $(this);
        const sliderId = btn.data('id');
        
        $.ajax({
            url: `/admin/ppdb/slider/${sliderId}/toggle-status`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    if (response.status === 'active') {
                        btn.removeClass('btn-secondary').addClass('btn-success');
                        btn.html('<i class="fas fa-check"></i> Active');
                    } else {
                        btn.removeClass('btn-success').addClass('btn-secondary');
                        btn.html('<i class="fas fa-times"></i> Inactive');
                    }
                    
                    // Show toast notification
                    toastr.success(response.message);
                }
            },
            error: function() {
                toastr.error('Gagal mengubah status slider');
            }
        });
    });
});
</script>
@stop

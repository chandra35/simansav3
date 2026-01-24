@extends('adminlte::page')

@section('title', 'Kelola Jurusan PPDB')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-graduation-cap mr-2"></i>Kelola Jurusan PPDB</h1>
        <a href="{{ route('admin.settings.jurusan.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Jurusan
        </a>
    </div>
@stop

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        {{ session('error') }}
    </div>
@endif

<div class="card">
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th width="80">Urutan</th>
                    <th width="100">Kode</th>
                    <th>Nama Jurusan</th>
                    <th width="100">Kuota</th>
                    <th width="100">Terisi</th>
                    <th width="120">Progress</th>
                    <th width="100">Status</th>
                    <th width="150">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jurusan as $j)
                    <tr>
                        <td class="text-center">{{ $j->urutan }}</td>
                        <td><span class="badge badge-info">{{ $j->kode }}</span></td>
                        <td>
                            <strong>{{ $j->nama }}</strong>
                            @if($j->deskripsi)
                                <br><small class="text-muted">{{ Str::limit($j->deskripsi, 50) }}</small>
                            @endif
                        </td>
                        <td class="text-center">{{ $j->kuota }}</td>
                        <td class="text-center">{{ $j->terisi }}</td>
                        <td>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar {{ $j->persentase_terisi > 90 ? 'bg-danger' : ($j->persentase_terisi > 70 ? 'bg-warning' : 'bg-success') }}" 
                                     style="width: {{ $j->persentase_terisi }}%">
                                    {{ $j->persentase_terisi }}%
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <button type="button" 
                                    class="btn btn-xs {{ $j->is_active ? 'btn-success' : 'btn-secondary' }} toggle-status"
                                    data-id="{{ $j->id }}"
                                    title="{{ $j->is_active ? 'Aktif' : 'Nonaktif' }}">
                                <i class="fas {{ $j->is_active ? 'fa-check' : 'fa-times' }}"></i>
                            </button>
                        </td>
                        <td>
                            <a href="{{ route('admin.settings.jurusan.edit', $j) }}" class="btn btn-xs btn-warning" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if($j->terisi == 0)
                                <form action="{{ route('admin.settings.jurusan.destroy', $j) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Yakin hapus jurusan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">Belum ada data jurusan</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@stop

@section('js')
<script>
$(function() {
    $('.toggle-status').on('click', function() {
        var btn = $(this);
        var id = btn.data('id');
        
        $.ajax({
            url: '/admin/ppdb/jurusan/' + id + '/toggle-status',
            method: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    if (response.is_active) {
                        btn.removeClass('btn-secondary').addClass('btn-success');
                        btn.find('i').removeClass('fa-times').addClass('fa-check');
                    } else {
                        btn.removeClass('btn-success').addClass('btn-secondary');
                        btn.find('i').removeClass('fa-check').addClass('fa-times');
                    }
                }
            }
        });
    });
});
</script>
@stop

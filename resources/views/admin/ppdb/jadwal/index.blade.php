@extends('adminlte::page')

@section('title', 'Kelola Jadwal PPDB')

@section('content_header')
    <h1>Kelola Jadwal PPDB</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Timeline Jadwal PPDB</h3>
            <div class="card-tools">
                <a href="{{ route('admin.settings.jadwal.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Tambah Jadwal
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

            @if($jadwals->count() > 0)
                <div class="timeline">
                    @foreach($jadwals as $jadwal)
                        <div class="time-label">
                            <span class="bg-{{ $jadwal->status_color }}">
                                {{ $jadwal->formatted_tanggal_mulai }}
                            </span>
                        </div>
                        <div>
                            <i class="{{ $jadwal->icon }}" style="background-color: {{ $jadwal->warna }}; color: white;"></i>
                            <div class="timeline-item">
                                <span class="time">
                                    <i class="fas fa-clock"></i> 
                                    {{ $jadwal->date_range }}
                                    @if(!$jadwal->is_active)
                                        <span class="badge badge-secondary ml-2">Nonaktif</span>
                                    @endif
                                </span>
                                <h3 class="timeline-header">
                                    <a href="{{ route('admin.settings.jadwal.edit', $jadwal->id) }}">
                                        {{ $jadwal->nama_kegiatan }}
                                    </a>
                                    <span class="badge badge-{{ $jadwal->status_color }} ml-2">
                                        {{ $jadwal->status_label }}
                                    </span>
                                </h3>
                                @if($jadwal->deskripsi)
                                    <div class="timeline-body">
                                        {{ $jadwal->deskripsi }}
                                    </div>
                                @endif
                                <div class="timeline-footer">
                                    <button class="btn btn-sm btn-toggle-status {{ $jadwal->is_active ? 'btn-success' : 'btn-secondary' }}" 
                                            data-id="{{ $jadwal->id }}">
                                        <i class="fas {{ $jadwal->is_active ? 'fa-check' : 'fa-times' }}"></i>
                                        {{ $jadwal->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </button>
                                    <a href="{{ route('admin.settings.jadwal.edit', $jadwal->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('admin.settings.jadwal.destroy', $jadwal->id) }}" 
                                          method="POST" 
                                          style="display: inline;"
                                          onsubmit="return confirm('Yakin ingin menghapus jadwal ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    <div>
                        <i class="fas fa-flag-checkered bg-gray"></i>
                    </div>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Belum ada jadwal PPDB</p>
                    <a href="{{ route('admin.settings.jadwal.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Jadwal Pertama
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Table View -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Jadwal (Tabel)</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th width="60">Urutan</th>
                            <th>Nama Kegiatan</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Selesai</th>
                            <th>Status Waktu</th>
                            <th>Aktif</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="sortable-jadwal">
                        @forelse($jadwals as $jadwal)
                            <tr data-id="{{ $jadwal->id }}">
                                <td class="text-center">
                                    <span class="badge" style="background-color: {{ $jadwal->warna }}; color: white;">
                                        {{ $jadwal->urutan }}
                                    </span>
                                </td>
                                <td>
                                    <i class="{{ $jadwal->icon }}" style="color: {{ $jadwal->warna }};"></i>
                                    {{ $jadwal->nama_kegiatan }}
                                </td>
                                <td>{{ $jadwal->tanggal_mulai->format('d M Y H:i') }}</td>
                                <td>{{ $jadwal->tanggal_selesai->format('d M Y H:i') }}</td>
                                <td>
                                    <span class="badge badge-{{ $jadwal->status_color }}">
                                        {{ $jadwal->status_label }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($jadwal->is_active)
                                        <i class="fas fa-check-circle text-success"></i>
                                    @else
                                        <i class="fas fa-times-circle text-secondary"></i>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.settings.jadwal.edit', $jadwal->id) }}" 
                                           class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.settings.jadwal.destroy', $jadwal->id) }}" 
                                              method="POST" 
                                              style="display: inline;"
                                              onsubmit="return confirm('Yakin ingin menghapus?')">
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
                                <td colspan="7" class="text-center text-muted">Belum ada jadwal</td>
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
.timeline-item {
    border-left: 3px solid #dee2e6;
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
            url: '{{ url("admin/ppdb/jadwal") }}/' + id + '/toggle-status',
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
});
</script>
@stop

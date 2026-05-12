@extends('adminlte::page')

@section('title', 'Mutasi Siswa')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-exchange-alt"></i> Mutasi Siswa</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                @can('create-mutasi')
                <a href="{{ route('admin.mutasi-siswa.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Mutasi
                </a>
                @endcan
            </div>
        </div>
    </div>
@endsection

@section('content')

    {{-- Statistik --}}
    <div class="row">
        <div class="col-lg-2 col-6">
            <div class="small-box bg-secondary">
                <div class="inner"><h3>{{ $stats['total'] }}</h3><p>Total</p></div>
                <div class="icon"><i class="fas fa-exchange-alt"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-info">
                <div class="inner"><h3>{{ $stats['masuk'] }}</h3><p>Masuk</p></div>
                <div class="icon"><i class="fas fa-sign-in-alt"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-danger">
                <div class="inner"><h3>{{ $stats['keluar'] }}</h3><p>Keluar</p></div>
                <div class="icon"><i class="fas fa-sign-out-alt"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-warning">
                <div class="inner"><h3>{{ $stats['pending'] }}</h3><p>Pending</p></div>
                <div class="icon"><i class="fas fa-clock"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-success">
                <div class="inner"><h3>{{ $stats['approved'] }}</h3><p>Disetujui</p></div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-dark">
                <div class="inner"><h3>{{ $stats['rejected'] }}</h3><p>Ditolak</p></div>
                <div class="icon"><i class="fas fa-times-circle"></i></div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filter</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.mutasi-siswa.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Cari Siswa</label>
                            <input type="text" name="search" class="form-control" 
                                placeholder="Nama / NISN..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Jenis Mutasi</label>
                            <select name="jenis" class="form-control">
                                <option value="">Semua</option>
                                <option value="masuk" {{ request('jenis') === 'masuk' ? 'selected' : '' }}>Masuk</option>
                                <option value="keluar" {{ request('jenis') === 'keluar' ? 'selected' : '' }}>Keluar</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="">Semua</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
                                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tahun Pelajaran</label>
                            <select name="tahun_pelajaran_id" class="form-control">
                                <option value="">Semua</option>
                                @foreach($tahunPelajarans as $tp)
                                    <option value="{{ $tp->id }}" {{ request('tahun_pelajaran_id') === $tp->id ? 'selected' : '' }}>
                                        {{ $tp->nama_tahun_pelajaran ?? $tp->nama ?? $tp->id }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="form-group w-100">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i> Cari
                            </button>
                        </div>
                    </div>
                </div>
                @if(request()->hasAny(['search','jenis','status','tahun_pelajaran_id']))
                    <a href="{{ route('admin.mutasi-siswa.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-times"></i> Reset Filter
                    </a>
                @endif
            </form>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-list"></i> Daftar Mutasi</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th width="40">No</th>
                            <th>Siswa</th>
                            <th>Jenis</th>
                            <th>Sekolah Asal/Tujuan</th>
                            <th>Tanggal</th>
                            <th>Tahun Pelajaran</th>
                            <th>Status</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mutasiList as $i => $mutasi)
                        <tr>
                            <td>{{ $mutasiList->firstItem() + $i }}</td>
                            <td>
                                <strong>{{ $mutasi->siswa->nama_lengkap ?? '-' }}</strong><br>
                                <small class="text-muted">{{ $mutasi->siswa->nisn ?? '-' }}</small>
                            </td>
                            <td>
                                @if($mutasi->isMutasiMasuk())
                                    <span class="badge badge-info"><i class="fas fa-sign-in-alt"></i> Masuk</span>
                                @else
                                    <span class="badge badge-danger"><i class="fas fa-sign-out-alt"></i> Keluar</span>
                                @endif
                            </td>
                            <td>
                                <small>{{ $mutasi->namaSekolah }}</small><br>
                                @if($mutasi->npsn !== 'N/A')
                                    <small class="text-muted">NPSN: {{ $mutasi->npsn }}</small>
                                @endif
                            </td>
                            <td><small>{{ $mutasi->tanggal_mutasi?->format('d/m/Y') ?? '-' }}</small></td>
                            <td>
                                <small>{{ $mutasi->tahunPelajaran->nama_tahun_pelajaran ?? $mutasi->tahunPelajaran?->nama ?? '-' }}</small>
                            </td>
                            <td>
                                <span class="badge badge-{{ $mutasi->statusBadgeColor }}">
                                    {{ $mutasi->statusText }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.mutasi-siswa.show', $mutasi) }}" 
                                   class="btn btn-info btn-xs" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('edit-mutasi')
                                @if($mutasi->isPending())
                                <a href="{{ route('admin.mutasi-siswa.edit', $mutasi) }}" 
                                   class="btn btn-warning btn-xs" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif
                                @endcan
                                @can('delete-mutasi')
                                @if($mutasi->isPending())
                                <button class="btn btn-danger btn-xs btn-delete" 
                                        data-id="{{ $mutasi->id }}" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                Belum ada data mutasi
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($mutasiList->hasPages())
        <div class="card-footer">
            {{ $mutasiList->links() }}
        </div>
        @endif
    </div>

@endsection

@section('js')
<script>
$(function () {
    // Delete
    $(document).on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Hapus mutasi ini?',
            text: 'Data mutasi pending akan dihapus permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
        }).then(result => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/mutasi-siswa/${id}`,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: res => {
                        Swal.fire('Dihapus!', res.message, 'success').then(() => location.reload());
                    },
                    error: xhr => {
                        Swal.fire('Gagal!', xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endsection

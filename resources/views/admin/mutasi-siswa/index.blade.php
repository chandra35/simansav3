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
        <div class="col-xl-2 col-md-4 col-6">
            <div class="small-box bg-secondary">
                <div class="inner"><h3>{{ $stats['total'] }}</h3><p>Total</p></div>
                <div class="icon"><i class="fas fa-exchange-alt"></i></div>
                <a href="{{ route('admin.mutasi-siswa.index') }}" class="small-box-footer">
                    Semua <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="small-box bg-info">
                <div class="inner"><h3>{{ $stats['masuk'] }}</h3><p>Masuk</p></div>
                <div class="icon"><i class="fas fa-sign-in-alt"></i></div>
                <a href="{{ route('admin.mutasi-siswa.index', ['jenis' => 'masuk']) }}" class="small-box-footer">
                    Lihat <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="small-box bg-danger">
                <div class="inner"><h3>{{ $stats['keluar'] }}</h3><p>Keluar</p></div>
                <div class="icon"><i class="fas fa-sign-out-alt"></i></div>
                <a href="{{ route('admin.mutasi-siswa.index', ['jenis' => 'keluar']) }}" class="small-box-footer">
                    Lihat <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="small-box bg-warning">
                <div class="inner"><h3>{{ $stats['pending'] }}</h3><p>Pending</p></div>
                <div class="icon"><i class="fas fa-clock"></i></div>
                <a href="{{ route('admin.mutasi-siswa.index', ['status' => 'pending']) }}" class="small-box-footer">
                    Lihat <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="small-box bg-success">
                <div class="inner"><h3>{{ $stats['approved'] }}</h3><p>Disetujui</p></div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
                <a href="{{ route('admin.mutasi-siswa.index', ['status' => 'approved']) }}" class="small-box-footer">
                    Lihat <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="small-box bg-dark">
                <div class="inner"><h3>{{ $stats['rejected'] }}</h3><p>Ditolak</p></div>
                <div class="icon"><i class="fas fa-times-circle"></i></div>
                <a href="{{ route('admin.mutasi-siswa.index', ['status' => 'rejected']) }}" class="small-box-footer">
                    Lihat <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- Filter + Tabel --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-list"></i> Daftar Mutasi</h3>
        </div>

        {{-- Filter Bar --}}
        <div class="card-body border-bottom pb-3">
            <form method="GET" action="{{ route('admin.mutasi-siswa.index') }}" id="formFilter">
                <div class="row align-items-end">
                    <div class="col-md-4 mb-2">
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" name="search" class="form-control"
                                placeholder="Cari nama atau NISN..."
                                value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary">Cari</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 mb-2">
                        <select name="jenis" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="">Semua Jenis</option>
                            <option value="masuk" {{ request('jenis') === 'masuk' ? 'selected' : '' }}>↑ Masuk</option>
                            <option value="keluar" {{ request('jenis') === 'keluar' ? 'selected' : '' }}>↓ Keluar</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>⏳ Pending</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>✓ Disetujui</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>✗ Ditolak</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <select name="tahun_pelajaran_id" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="">Semua Tahun Pelajaran</option>
                            @foreach($tahunPelajarans as $tp)
                                <option value="{{ $tp->id }}" {{ request('tahun_pelajaran_id') === $tp->id ? 'selected' : '' }}>
                                    {{ $tp->nama_tahun_pelajaran ?? $tp->nama ?? $tp->id }}
                                    {{ $tp->is_active ? '(Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1 mb-2">
                        @if(request()->hasAny(['search','jenis','status','tahun_pelajaran_id']))
                        <a href="{{ route('admin.mutasi-siswa.index') }}" class="btn btn-secondary btn-sm btn-block" title="Reset filter">
                            <i class="fas fa-times"></i>
                        </a>
                        @endif
                    </div>
                </div>
            </form>
            @if(request()->hasAny(['search','jenis','status','tahun_pelajaran_id']))
            <small class="text-muted">
                <i class="fas fa-filter mr-1"></i>
                Menampilkan {{ $mutasiList->total() }} hasil
                @if(request('search')) &bull; Kata kunci: "<strong>{{ request('search') }}</strong>"@endif
                @if(request('jenis')) &bull; Jenis: <strong>{{ request('jenis') }}</strong>@endif
                @if(request('status')) &bull; Status: <strong>{{ request('status') }}</strong>@endif
            </small>
            @endif
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th width="40" class="text-center">No</th>
                            <th>Siswa</th>
                            <th width="110">Jenis</th>
                            <th>Sekolah</th>
                            <th width="90">Tanggal</th>
                            <th width="130">Tahun Pelajaran</th>
                            <th width="100">Status</th>
                            <th width="100" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mutasiList as $i => $mutasi)
                        <tr class="row-link" data-href="{{ route('admin.mutasi-siswa.show', $mutasi) }}">
                            <td class="text-center align-middle">{{ $mutasiList->firstItem() + $i }}</td>
                            <td class="align-middle">
                                <strong>{{ $mutasi->siswa?->nama_lengkap ?? '-' }}</strong>
                                <br>
                                <small class="text-muted">NISN: {{ $mutasi->siswa?->nisn ?? '-' }}</small>
                            </td>
                            <td class="align-middle">
                                @if($mutasi->isMutasiMasuk())
                                    <span class="badge badge-info badge-pill">
                                        <i class="fas fa-sign-in-alt mr-1"></i>Masuk
                                    </span>
                                @else
                                    <span class="badge badge-danger badge-pill">
                                        <i class="fas fa-sign-out-alt mr-1"></i>Keluar
                                    </span>
                                @endif
                            </td>
                            <td class="align-middle">
                                <small>{{ $mutasi->namaSekolah ?? '-' }}</small>
                                @if($mutasi->npsn && $mutasi->npsn !== 'N/A')
                                    <br><small class="text-muted">NPSN: {{ $mutasi->npsn }}</small>
                                @endif
                            </td>
                            <td class="align-middle">
                                <small>{{ $mutasi->tanggal_mutasi?->format('d/m/Y') ?? '-' }}</small>
                            </td>
                            <td class="align-middle">
                                <small>{{ $mutasi->tahunPelajaran?->nama_tahun_pelajaran ?? $mutasi->tahunPelajaran?->nama ?? '-' }}</small>
                            </td>
                            <td class="align-middle">
                                @php
                                    $statusIcon = match($mutasi->status_verifikasi) {
                                        'pending'  => 'fa-clock',
                                        'approved' => 'fa-check-circle',
                                        'rejected' => 'fa-times-circle',
                                        default    => 'fa-question-circle',
                                    };
                                @endphp
                                <span class="badge badge-{{ $mutasi->statusBadgeColor }} badge-pill">
                                    <i class="fas {{ $statusIcon }} mr-1"></i>{{ $mutasi->statusText }}
                                </span>
                            </td>
                            <td class="align-middle text-center no-row-link">
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
                                        data-id="{{ $mutasi->id }}"
                                        data-nama="{{ $mutasi->siswa?->nama_lengkap }}"
                                        title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8">
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block" style="opacity:.4"></i>
                                    <strong>Belum ada data mutasi</strong>
                                    @if(request()->hasAny(['search','jenis','status','tahun_pelajaran_id']))
                                        <br><small>Tidak ada hasil untuk filter yang dipilih.</small>
                                        <br>
                                        <a href="{{ route('admin.mutasi-siswa.index') }}" class="btn btn-sm btn-secondary mt-2">
                                            <i class="fas fa-times mr-1"></i>Reset Filter
                                        </a>
                                    @else
                                        @can('create-mutasi')
                                        <br>
                                        <a href="{{ route('admin.mutasi-siswa.create') }}" class="btn btn-sm btn-primary mt-2">
                                            <i class="fas fa-plus mr-1"></i>Tambah Mutasi Pertama
                                        </a>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($mutasiList->hasPages())
        <div class="card-footer d-flex align-items-center justify-content-between flex-wrap">
            <small class="text-muted mb-1">
                Menampilkan {{ $mutasiList->firstItem() }}–{{ $mutasiList->lastItem() }}
                dari {{ $mutasiList->total() }} data
            </small>
            {{ $mutasiList->links() }}
        </div>
        @endif
    </div>

@endsection

@section('css')
<style>
.row-link { cursor: pointer; }
.row-link:hover td { background-color: #f1f3f5 !important; }
</style>
@endsection

@section('js')
<script>
$(function () {
    // Clickable rows
    $(document).on('click', '.row-link', function (e) {
        if ($(e.target).closest('.no-row-link').length) return;
        window.location = $(this).data('href');
    });

    // Delete
    $(document).on('click', '.btn-delete', function (e) {
        e.stopPropagation();
        const id   = $(this).data('id');
        const nama = $(this).data('nama') || 'siswa ini';
        const btn  = $(this);
        Swal.fire({
            title: 'Hapus mutasi?',
            html: `Data mutasi <strong>${nama}</strong> akan dihapus permanen.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: '<i class="fas fa-trash mr-1"></i>Ya, Hapus',
            cancelButtonText: 'Batal',
        }).then(result => {
            if (result.isConfirmed) {
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
                $.ajax({
                    url: `/admin/mutasi-siswa/${id}`,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: res => {
                        Swal.fire({ title: 'Dihapus!', text: res.message, icon: 'success', timer: 1500, showConfirmButton: false })
                            .then(() => location.reload());
                    },
                    error: xhr => {
                        btn.prop('disabled', false).html('<i class="fas fa-trash"></i>');
                        Swal.fire('Gagal!', xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
                    }
                });
            }
        });
    });
});
</script>
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

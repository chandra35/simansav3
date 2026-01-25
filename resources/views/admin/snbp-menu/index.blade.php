@extends('adminlte::page')

@section('title', 'Kelola Menu SNBP')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-graduation-cap"></i> Kelola Menu SNBP</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Menu SNBP</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i> Daftar Menu SNBP
            </h3>
            <div class="card-tools">
                <a href="{{ route('admin.snbp-menu.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Buat Menu Baru
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                <strong>Info:</strong> Menu SNBP hanya ditampilkan untuk siswa kelas 12. 
                Data dari tahun pelajaran yang tidak aktif bersifat <strong>readonly</strong>.
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="snbpTable">
                    <thead class="bg-primary">
                        <tr>
                            <th width="5%">#</th>
                            <th>Nama Menu</th>
                            <th>Tahun Pelajaran</th>
                            <th>Periode Tampil</th>
                            <th width="10%">Status</th>
                            <th width="8%">Eligible</th>
                            <th width="8%">Tidak Eligible</th>
                            <th width="18%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($menus as $index => $menu)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $menu->nama_menu }}</strong>
                            </td>
                            <td>
                                {{ $menu->tahunPelajaran->nama ?? '-' }}
                                @if($menu->tahunPelajaran && $menu->tahunPelajaran->is_active)
                                    <span class="badge badge-primary">Aktif</span>
                                @endif
                            </td>
                            <td>
                                @if($menu->tanggal_mulai || $menu->tanggal_berakhir)
                                    <small>
                                        @if($menu->tanggal_mulai)
                                            <i class="fas fa-play text-success"></i> {{ $menu->tanggal_mulai->format('d/m/Y H:i') }}<br>
                                        @else
                                            <i class="fas fa-play text-muted"></i> Tidak ada batas mulai<br>
                                        @endif
                                        @if($menu->tanggal_berakhir)
                                            <i class="fas fa-stop text-danger"></i> {{ $menu->tanggal_berakhir->format('d/m/Y H:i') }}
                                        @else
                                            <i class="fas fa-stop text-muted"></i> Tidak ada batas akhir
                                        @endif
                                    </small>
                                @else
                                    <small class="text-muted">Selalu tampil</small>
                                @endif
                            </td>
                            <td>
                                @if($menu->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-secondary">Non-Aktif</span>
                                @endif
                                @if(!$menu->isEditable())
                                    <span class="badge badge-warning"><i class="fas fa-lock"></i> Readonly</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge badge-success badge-lg">{{ $menu->eligibleSiswa()->count() }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-danger badge-lg">{{ $menu->notEligibleSiswa()->count() }}</span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.snbp-menu.show', $menu) }}" class="btn btn-info btn-sm" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($menu->isEditable())
                                        <a href="{{ route('admin.snbp-menu.edit', $menu) }}" class="btn btn-warning btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('admin.snbp-menu.assign-eligible', $menu) }}" class="btn btn-success btn-sm" title="Assign Eligible">
                                            <i class="fas fa-user-check"></i>
                                        </a>
                                        <a href="{{ route('admin.snbp-menu.assign-not-eligible', $menu) }}" class="btn btn-secondary btn-sm" title="Assign Not Eligible">
                                            <i class="fas fa-user-times"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>Belum ada menu SNBP. <a href="{{ route('admin.snbp-menu.create') }}">Buat sekarang</a></p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .badge-lg {
        font-size: 1rem;
        padding: 0.5em 0.75em;
    }
</style>
@stop

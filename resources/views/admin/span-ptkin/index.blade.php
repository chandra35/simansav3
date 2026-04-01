@extends('adminlte::page')

@section('title', 'Menu SPAN-PTKIN')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-mosque"></i> Menu SPAN-PTKIN</h1>
        <a href="{{ route('admin.span-ptkin-menu.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Buat Menu
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card card-outline card-success">
        <div class="card-body">
            <p class="mb-1">Modul ini memonitor seluruh siswa kelas 12 aktif untuk jalur SPAN-PTKIN. Nomor pendaftaran tidak diisi siswa, melainkan diimpor dari PDF resmi sekolah.</p>
            <p class="text-muted mb-0">Tahun pelajaran aktif: <strong>{{ $activeTahun->nama ?? 'Belum ada' }}</strong></p>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if($menus->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-inbox fa-2x mb-2"></i>
                    <p>Belum ada menu SPAN-PTKIN.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Nama Menu</th>
                                <th>Tahun Pelajaran</th>
                                <th>Status</th>
                                <th>Periode</th>
                                <th style="width: 180px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($menus as $menu)
                                <tr>
                                    <td>{{ $menu->nama_menu }}</td>
                                    <td>{{ $menu->tahunPelajaran->nama ?? '-' }}</td>
                                    <td>
                                        @if($menu->is_active)
                                            <span class="badge badge-success">Aktif</span>
                                        @else
                                            <span class="badge badge-secondary">Non-Aktif</span>
                                        @endif
                                        @unless($menu->isEditable())
                                            <span class="badge badge-warning">Readonly</span>
                                        @endunless
                                    </td>
                                    <td>
                                        {{ $menu->tanggal_mulai?->format('d-m-Y H:i') ?? 'Tanpa batas mulai' }}
                                        <br>
                                        <small class="text-muted">{{ $menu->tanggal_berakhir?->format('d-m-Y H:i') ?? 'Tanpa batas akhir' }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.span-ptkin-menu.show', $menu) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($menu->isEditable())
                                            <a href="{{ route('admin.span-ptkin-menu.edit', $menu) }}" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@stop

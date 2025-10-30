@extends('adminlte::page')

@section('title', 'Menu Khusus')

@section('content_header')
    <h1><i class="fas fa-th-list"></i> Menu Khusus Saya</h1>
@stop

@section('content')
<div class="row">
    @forelse($customMenuGroups as $group => $menus)
        <div class="col-md-12 mb-3">
            <h4 class="text-uppercase text-muted">
                @if($group === 'akademik')
                    📚 Akademik
                @elseif($group === 'administrasi')
                    📋 Administrasi
                @elseif($group === 'hotspot')
                    📡 Hotspot & Akun
                @else
                    📌 Lainnya
                @endif
            </h4>
            <hr>
            
            <div class="row">
                @foreach($menus as $menu)
                    @php
                        $assignment = $menu->siswaAssigned->first();
                        $isRead = $assignment ? $assignment->is_read : false;
                    @endphp
                    
                    <div class="col-md-4 col-sm-6 mb-3">
                        <div class="card card-outline {{ $isRead ? 'card-secondary' : 'card-primary' }}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h5 class="card-title">
                                        <i class="{{ $menu->icon ?? 'fas fa-file-alt' }}"></i>
                                        {{ $menu->judul }}
                                    </h5>
                                    @if(!$isRead)
                                        <span class="badge badge-danger">NEW</span>
                                    @endif
                                </div>
                                
                                @if($menu->content_type === 'personal')
                                    <p class="text-muted mb-2">
                                        <small><i class="fas fa-user"></i> Informasi Personal</small>
                                    </p>
                                @endif
                                
                                <a href="{{ route('siswa.menu.show', $menu->slug) }}" 
                                   class="btn btn-sm btn-{{ $isRead ? 'secondary' : 'primary' }} btn-block">
                                    <i class="fas fa-eye"></i> Lihat Detail
                                </a>
                            </div>
                            @if($assignment && $assignment->read_at)
                                <div class="card-footer text-muted" style="font-size: 0.8em;">
                                    <i class="fas fa-check-circle"></i> 
                                    Dibaca: {{ $assignment->read_at->format('d M Y H:i') }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="col-md-12">
            <div class="callout callout-info">
                <h5><i class="fas fa-info-circle"></i> Tidak Ada Menu</h5>
                <p>Saat ini belum ada menu khusus yang tersedia untuk Anda.</p>
            </div>
        </div>
    @endforelse
</div>
@stop

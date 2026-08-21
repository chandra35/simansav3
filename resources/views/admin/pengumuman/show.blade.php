@extends('adminlte::page')

@section('title', 'Detail Pengumuman')

@section('content_header')
    <h1><i class="fas fa-bullhorn mr-2"></i>Detail Pengumuman</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ $pengumuman->judul }}</h3>
            <div class="card-tools">
                @if($pengumuman->is_pinned)
                    <span class="badge badge-warning"><i class="fas fa-thumbtack"></i> Pinned</span>
                @endif
                <span class="badge badge-{{ $pengumuman->kategori_badge }}">{{ ucfirst($pengumuman->kategori) }}</span>
                <span class="badge badge-{{ $pengumuman->prioritas_badge }}">{{ ucfirst($pengumuman->prioritas) }}</span>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="post">
                        <div class="user-block">
                            <span class="username ml-0">
                                <i class="fas fa-user"></i> {{ $pengumuman->creator->name ?? 'System' }}
                            </span>
                            <span class="description ml-0">
                                <i class="fas fa-calendar"></i> {{ $pengumuman->created_at->format('d M Y H:i') }}
                            </span>
                        </div>
                        <div class="mt-3">
                            {!! nl2br(e($pengumuman->isi)) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box bg-light">
                        <div class="info-box-content">
                            <span class="info-box-text text-muted">Target</span>
                            <span class="info-box-number text-muted">{{ ucfirst($pengumuman->target) }}</span>
                        </div>
                    </div>
                    
                    <div class="info-box bg-light">
                        <div class="info-box-content">
                            <span class="info-box-text text-muted">Periode Aktif</span>
                            <span class="info-box-number text-muted">
                                {{ $pengumuman->tanggal_mulai->format('d M Y') }}
                                @if($pengumuman->tanggal_selesai)
                                    - {{ $pengumuman->tanggal_selesai->format('d M Y') }}
                                @else
                                    - Tidak Terbatas
                                @endif
                            </span>
                        </div>
                    </div>
                    
                    <div class="info-box {{ $pengumuman->is_aktif ? 'bg-success' : 'bg-secondary' }}">
                        <div class="info-box-content">
                            <span class="info-box-text">Status</span>
                            <span class="info-box-number">{{ $pengumuman->is_aktif ? 'Aktif' : 'Tidak Aktif' }}</span>
                        </div>
                    </div>
                    
                    @if($pengumuman->lampiran)
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title"><i class="fas fa-paperclip"></i> Lampiran</h5>
                            </div>
                            <div class="card-body">
                                <a href="{{ Storage::disk('public')->url($pengumuman->lampiran) }}" target="_blank" class="btn btn-primary btn-block">
                                    <i class="fas fa-download mr-1"></i> Download File
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('admin.pengumuman.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
            <a href="{{ route('admin.pengumuman.edit', $pengumuman->id) }}" class="btn btn-warning float-right ml-2">
                <i class="fas fa-edit mr-1"></i> Edit
            </a>
        </div>
    </div>
@stop

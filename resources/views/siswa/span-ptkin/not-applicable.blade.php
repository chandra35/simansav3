@extends('adminlte::page')

@section('title', 'SPAN-PTKIN')

@section('content_header')
    <h1><i class="fas fa-mosque"></i> SPAN-PTKIN</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card card-warning">
        <div class="card-body text-center py-5">
            <i class="fas fa-info-circle fa-4x text-warning mb-3"></i>
            @if($reason === 'not_kelas_12')
                <h4>Menu ini hanya tersedia untuk siswa kelas 12</h4>
                <p class="text-muted mb-0">Kelas saat ini: <strong>{{ $kelasSaatIni->nama_kelas ?? '-' }}{{ $kelasSaatIni->asrama_suffix ?? '' }}</strong></p>
            @else
                <h4>Informasi SPAN-PTKIN belum dibuka</h4>
                <p class="text-muted mb-0">Silakan cek kembali setelah sekolah mengaktifkan menu SPAN-PTKIN.</p>
            @endif
        </div>
    </div>
</div>
@stop

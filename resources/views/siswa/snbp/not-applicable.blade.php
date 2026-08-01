@extends('adminlte::page')

@section('title', 'SNBP')

@section('content_header')
    <h1><i class="fas fa-graduation-cap"></i> SNBP</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-secondary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i> Informasi
                    </h3>
                </div>
                <div class="card-body">
                    <div class="text-center py-5">
                        @if($reason === 'not_kelas_12')
                        <div class="mb-3">
                            <i class="fas fa-user-graduate fa-5x text-secondary"></i>
                        </div>
                        <h4 class="text-secondary">Fitur Tidak Tersedia</h4>
                        <p class="text-muted">
                            Menu SNBP hanya tersedia untuk siswa kelas 12.<br>
                            Kelas Anda saat ini: <strong>{{ $kelasSaatIni->nama_kelas ?? 'Tidak ada kelas' }}{{ $kelasSaatIni->asrama_suffix ?? '' }}</strong>
                        </p>
                        @elseif($reason === 'no_menu')
                        <div class="mb-3">
                            <i class="fas fa-calendar-times fa-5x text-secondary"></i>
                        </div>
                        <h4 class="text-secondary">Belum Ada Pengumuman</h4>
                        <p class="text-muted">
                            Belum ada menu SNBP yang aktif untuk tahun pelajaran saat ini.<br>
                            Silakan hubungi pihak sekolah untuk informasi lebih lanjut.
                        </p>
                        @else
                        <div class="mb-3">
                            <i class="fas fa-question-circle fa-5x text-secondary"></i>
                        </div>
                        <h4 class="text-secondary">Menu Tidak Tersedia</h4>
                        <p class="text-muted">
                            Menu SNBP tidak tersedia saat ini.
                        </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

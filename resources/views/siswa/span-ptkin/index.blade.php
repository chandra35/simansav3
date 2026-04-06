@extends('adminlte::page')

@section('title', $spanPtkinMenu->nama_menu)

@section('content_header')
    <h1><i class="fas fa-mosque"></i> {{ $spanPtkinMenu->nama_menu }}</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Informasi SPAN-PTKIN</h3>
                </div>
                <div class="card-body">
                    <p class="mb-2">Menu ini berlaku untuk seluruh siswa kelas 12. Nomor pendaftaran akan diisikan sekolah melalui import daftar resmi SPAN-PTKIN.</p>
                    @if($spanPtkinMenu->konten_informasi)
                        <hr>
                        <div>{!! $spanPtkinMenu->konten_informasi !!}</div>
                    @endif
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card card-outline card-primary h-100">
                        <div class="card-header">
                            <h3 class="card-title">Status Registrasi Anda</h3>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-5">Tahun Pelajaran</dt>
                                <dd class="col-sm-7">{{ $spanPtkinMenu->tahunPelajaran->nama ?? '-' }}</dd>
                                <dt class="col-sm-5">Nomor Pendaftaran</dt>
                                <dd class="col-sm-7">
                                    @if($registration->exists && $registration->nomor_pendaftaran)
                                        <code>{{ $registration->nomor_pendaftaran }}</code>
                                    @else
                                        <span class="badge badge-secondary">Menunggu import sekolah</span>
                                    @endif
                                </dd>
                                <dt class="col-sm-5">Data PDF Terakhir</dt>
                                <dd class="col-sm-7">{{ $registration->imported_at?->format('d-m-Y H:i') ?? '-' }}</dd>
                                <dt class="col-sm-5">Status Checker</dt>
                                <dd class="col-sm-7">
                                    <span class="badge badge-{{ $registration->check_status === 'lulus' ? 'success' : ($registration->check_status === 'tidak_lulus' ? 'danger' : 'secondary') }}">
                                        {{ $registration->check_status_label }}
                                    </span>
                                </dd>
                                <dt class="col-sm-5">Update Terakhir</dt>
                                <dd class="col-sm-7">{{ $registration->last_checked_at?->format('d-m-Y H:i') ?? '-' }}</dd>
                                <dt class="col-sm-5">Catatan Checker</dt>
                                <dd class="col-sm-7">{{ $registration->last_check_message ?? '-' }}</dd>
                                <dt class="col-sm-5">PTKIN</dt>
                                <dd class="col-sm-7">{{ data_get($registration->last_check_payload, 'nm_ptain', '-') }}</dd>
                                <dt class="col-sm-5">Program Studi</dt>
                                <dd class="col-sm-7">{{ data_get($registration->last_check_payload, 'nm_prodi', '-') }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-outline card-info h-100">
                        <div class="card-header">
                            <h3 class="card-title">Data Siswa</h3>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-5">Nama</dt>
                                <dd class="col-sm-7">{{ $siswa->nama_lengkap }}</dd>
                                <dt class="col-sm-5">NISN</dt>
                                <dd class="col-sm-7">{{ $siswa->nisn }}</dd>
                                <dt class="col-sm-5">Kelas</dt>
                                <dd class="col-sm-7">{{ $kelasSaatIni->nama_kelas ?? '-' }}</dd>
                                <dt class="col-sm-5">Tanggal Lahir</dt>
                                <dd class="col-sm-7">{{ $siswa->tanggal_lahir?->translatedFormat('j F Y') ?? '-' }}</dd>
                                <dt class="col-sm-5">Status Lulusan</dt>
                                <dd class="col-sm-7">
                                    @if($linkedLulusan)
                                        <span class="badge badge-success">Sudah terhubung</span>
                                    @else
                                        <span class="badge badge-secondary">Belum ada data lulusan</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

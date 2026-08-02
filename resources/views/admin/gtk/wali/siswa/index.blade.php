@extends('adminlte::page')

@section('title', 'Daftar Siswa — Kelas Saya')
@section('plugins.Datatables', true)

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-user-graduate text-primary"></i> Daftar Siswa</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.gtk.dashboard') }}">Dashboard Saya</a></li>
                <li class="breadcrumb-item active">Daftar Siswa</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="gtk-wali-siswa-page">
    <div class="card bg-gradient-primary text-white mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h3 class="mb-1"><i class="fas fa-chalkboard-teacher mr-1"></i> Siswa Kelas Saya</h3>
                    <p class="mb-2 text-white-50">Data siswa rombel yang Anda ampu dalam tampilan hanya-baca.</p>
                    <p class="mb-0">Pantau kelengkapan data dan buka detail siswa dari satu daftar operasional.</p>
                </div>
                <div class="col-lg-4 mt-3 mt-lg-0">
                    <div class="row text-center">
                        <div class="col-7"><div class="text-white-50 small text-uppercase font-weight-bold">Rombel</div><h3 class="mb-0 text-white">{{ $kelas->nama_lengkap ?? $kelas->nama_kelas }}</h3></div>
                        <div class="col-5"><div class="text-white-50 small text-uppercase font-weight-bold">Total Siswa</div><h3 class="mb-0 text-white">{{ $siswa->count() }}</h3></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @includeWhen($kelasList->count() > 1, 'admin.gtk.wali.partials.kelas-switcher', ['route' => 'admin.gtk.wali.siswa.index'])

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user-graduate"></i> Siswa {{ $kelas->nama_kelas }}</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table id="tblSiswaWali" class="table table-hover table-striped mb-0" style="width:100%">
                <thead>
                    <tr>
                        <th style="width:48px">No</th>
                        <th style="width:56px">Foto</th>
                        <th>Nama / NISN</th>
                        <th style="width:64px">JK</th>
                        <th style="width:120px">Data Diri</th>
                        <th style="width:120px">Data Ortu</th>
                        <th style="width:150px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($siswa as $i => $s)
                        @php $absen = $s->pivot->nomor_urut_absen ?? ($i + 1); @endphp
                        <tr>
                            <td class="text-center">{{ $absen }}</td>
                            <td class="text-center">
                                @if($s->foto_profile)
                                    <img src="{{ asset('storage/'.$s->foto_profile) }}" alt="foto" class="img-circle" style="width:36px;height:36px;object-fit:cover;">
                                @else
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle text-white" style="width:36px;height:36px;background:#4F46E5;font-weight:600;">
                                        {{ strtoupper(substr($s->nama_lengkap, 0, 1)) }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="font-weight-600 text-dark">{{ $s->nama_lengkap }}</div>
                                <small class="text-muted">NISN {{ $s->nisn ?: '—' }}</small>
                                @if($s->pivot->is_ketua_kelas && $s->pivot->ketua_kelas_selesai_at === null)
                                    <span class="badge badge-warning ml-1"><i class="fas fa-crown"></i> Ketua</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($s->jenis_kelamin === 'L')
                                    <span class="badge" style="background:#dbeafe;color:#1e40af;"><i class="fas fa-mars"></i></span>
                                @else
                                    <span class="badge" style="background:#fce7f3;color:#be185d;"><i class="fas fa-venus"></i></span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($s->data_diri_completed)
                                    <span class="badge badge-success">Lengkap</span>
                                @else
                                    <span class="badge badge-danger">Belum</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($s->data_ortu_completed)
                                    <span class="badge badge-success">Lengkap</span>
                                @else
                                    <span class="badge badge-danger">Belum</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.gtk.wali.siswa.show', $s->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                                <a href="{{ route('admin.gtk.wali.catatan.index', ['kelas_id' => $kelas->id, 'siswa_id' => $s->id]) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-sticky-note"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .gtk-wali-siswa-page > .bg-gradient-primary { overflow:hidden; border:0; border-radius:16px; box-shadow:0 12px 28px rgba(15,23,42,.1); }
    .gtk-wali-siswa-page > .bg-gradient-primary .card-body { padding:1.2rem 1.25rem; }
    .gtk-wali-siswa-page > .bg-gradient-primary h3 { font-size:1.35rem; font-weight:700; overflow-wrap:anywhere; }
    @media (max-width:575.98px) {
        .gtk-wali-siswa-page > .bg-gradient-primary .card-body { padding:1rem; }
        .gtk-wali-siswa-page > .bg-gradient-primary h3 { font-size:1.1rem; }
    }
</style>
@stop

@section('js')
<script>
    $(function () {
        $('#tblSiswaWali').DataTable({
            paging: true,
            pageLength: 25,
            ordering: true,
            order: [[0, 'asc']],
            columnDefs: [{ orderable: false, targets: [1, 6] }],
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' }
        });
    });
</script>
@stop

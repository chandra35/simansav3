@extends('adminlte::page')

@section('title', 'Daftar Siswa — Kelas Saya')
@section('plugins.Datatables', true)

@section('content_header')
    <div class="simansa-hero">
        <div class="simansa-hero__main">
            <div class="simansa-hero__eyebrow"><i class="fas fa-chalkboard-teacher"></i> Kelas Saya</div>
            <h1 class="simansa-hero__title">Daftar Siswa</h1>
            <p class="simansa-hero__subtitle">Data siswa rombel yang Anda ampu. Tampilan hanya-baca untuk memantau kelengkapan data.</p>
        </div>
        <div class="simansa-hero__side">
            <div class="simansa-hero-chip">
                <span class="simansa-hero-chip__label">Rombel</span>
                <span class="simansa-hero-chip__value">{{ $kelas->nama_lengkap ?? $kelas->nama_kelas }}</span>
            </div>
            <div class="simansa-hero-chip">
                <span class="simansa-hero-chip__label">Total Siswa</span>
                <span class="simansa-hero-chip__value">{{ $siswa->count() }}</span>
            </div>
        </div>
    </div>
@stop

@section('content')
    @includeWhen($kelasList->count() > 1, 'admin.gtk.wali.partials.kelas-switcher', ['route' => 'admin.gtk.wali.siswa.index'])

    <div class="card simansa-management-card">
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

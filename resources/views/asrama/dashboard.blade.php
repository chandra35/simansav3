@extends('adminlte::page')
@section('title', 'Dashboard Asrama')
@section('content_header') @stop
@section('content')
    @include('asrama._alerts')
    @php
        $heroTitle = $santri ? 'Portal Santri Asrama' : ($asatidz ? 'Ruang Kerja Asatidz' : 'Dashboard Asrama');
        $heroDescription = $santri
            ? 'Lihat identitas kelas dan rapor asrama yang sudah diterbitkan.'
            : ($asatidz ? 'Kelola penugasan mengajar dan nilai santri sesuai tanggung jawab Anda.' : 'Pusat pengelolaan unit, santri, asatidz, kelas, nilai, dan rapor asrama.');
    @endphp
    @include('asrama._hero')

    <div class="row mb-4">
        <div class="col-lg-3 col-sm-6 mb-3"><div class="asrama-stat"><span>Unit Asrama</span><strong>{{ $stats['asrama'] }}</strong><small>Unit yang dapat diakses</small></div></div>
        <div class="col-lg-3 col-sm-6 mb-3"><div class="asrama-stat"><span>Santri Aktif</span><strong>{{ $stats['santri'] }}</strong><small>Terhubung ke master siswa</small></div></div>
        <div class="col-lg-3 col-sm-6 mb-3"><div class="asrama-stat"><span>Kelas Aktif</span><strong>{{ $stats['kelas'] }}</strong><small>{{ $tahunAktif?->nama ?? 'Tahun belum aktif' }}</small></div></div>
        <div class="col-lg-3 col-sm-6 mb-3"><div class="asrama-stat"><span>Penugasan Saya</span><strong>{{ $stats['pengampu'] }}</strong><small>Mata pelajaran aktif</small></div></div>
    </div>

    @if($isManager)
        <div class="asrama-panel"><div class="asrama-panel__header"><div><h3>Akses Cepat Pengelolaan</h3><p>Seluruh data asrama tetap terpisah dari akademik reguler.</p></div></div>
            <div class="asrama-panel__body"><div class="row">
                @can('manage-asrama-santri')<div class="col-md-3 mb-2"><a class="btn btn-outline-info btn-block" href="{{ route('asrama.santri.index') }}"><i class="fas fa-user-graduate mr-1"></i> Santri</a></div>@endcan
                @can('manage-asrama-asatidz')<div class="col-md-3 mb-2"><a class="btn btn-outline-info btn-block" href="{{ route('asrama.asatidz.index') }}"><i class="fas fa-chalkboard-teacher mr-1"></i> Asatidz</a></div>@endcan
                @can('manage-asrama-kelas')<div class="col-md-3 mb-2"><a class="btn btn-outline-info btn-block" href="{{ route('asrama.kelas.index') }}"><i class="fas fa-school mr-1"></i> Kelas</a></div>@endcan
                @can('manage-rapor-asrama')<div class="col-md-3 mb-2"><a class="btn btn-outline-info btn-block" href="{{ route('asrama.rapor.index') }}"><i class="fas fa-file-alt mr-1"></i> Rapor</a></div>@endcan
            </div></div>
        </div>
    @endif

    @if($asatidz)
        <div class="asrama-panel"><div class="asrama-panel__header"><div><h3>Penugasan Mengajar</h3><p>{{ $asatidz->jabatan }} · {{ $asatidz->asrama->nama }}</p></div></div>
            <div class="table-responsive"><table class="table asrama-table"><thead><tr><th>Kelas</th><th>Mapel</th><th>Semester</th><th></th></tr></thead><tbody>
                @forelse($assignments as $item)<tr><td>{{ $item->kelas->nama_kelas }}</td><td><span class="asrama-arab">{{ $item->mapel->nama_arab }}</span><br><small>{{ $item->mapel->nama_latin }}</small></td><td>{{ $item->semester }}</td><td class="text-right"><a href="{{ route('asrama.nilai.edit', $item) }}" class="btn btn-sm btn-info">Input Nilai</a></td></tr>
                @empty<tr><td colspan="4" class="asrama-empty"><i class="fas fa-book"></i>Belum ada penugasan mengajar.</td></tr>@endforelse
            </tbody></table></div>
        </div>
    @endif

    @if($santri)
        <div class="asrama-panel"><div class="asrama-panel__header"><div><h3>Identitas Asrama</h3><p>Data berasal dari keanggotaan asrama aktif.</p></div></div><div class="asrama-panel__body">
            <div class="row"><div class="col-md-4"><small>Nomor Induk Santri</small><h5>{{ $santri->nomor_induk_asrama }}</h5></div><div class="col-md-4"><small>Asrama</small><h5>{{ $santri->asrama->nama }}</h5></div><div class="col-md-4"><small>Kelas</small><h5>{{ $santri->kelasAktif?->kelas?->nama_kelas ?? '-' }}</h5></div></div>
        </div></div>
        <div class="asrama-panel"><div class="asrama-panel__header"><div><h3>Rapor Saya</h3><p>Hanya rapor yang sudah diterbitkan yang tersedia.</p></div></div>
            <div class="table-responsive"><table class="table asrama-table"><thead><tr><th>Tahun</th><th>Kelas</th><th>Semester</th><th>Terbit</th><th></th></tr></thead><tbody>
            @forelse($studentReports as $item)<tr><td>{{ $item->kelasSantri->kelas->tahunPelajaran->nama }}</td><td>{{ $item->kelasSantri->kelas->nama_kelas }}</td><td>{{ $item->semester }}</td><td>{{ $item->published_at?->format('d/m/Y') }}</td><td class="text-right"><a target="_blank" class="btn btn-sm btn-info" href="{{ route('asrama.rapor.print', $item) }}">Lihat Rapor</a></td></tr>
            @empty<tr><td colspan="5" class="asrama-empty"><i class="fas fa-file-alt"></i>Belum ada rapor yang diterbitkan.</td></tr>@endforelse
            </tbody></table></div>
        </div>
    @endif
@stop
@section('css') @include('asrama._styles') @stop

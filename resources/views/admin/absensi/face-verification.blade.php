@extends('adminlte::page')

@section('title', 'Verifikasi Wajah')
@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugins', true)

@section('content_header')
    <h1><i class="fas fa-user-check"></i> Data Registrasi Wajah {{ $subjectLabel }}</h1>
@stop

@section('content')
<div class="row">
    <div class="col-lg-3 col-6"><div class="small-box bg-info"><div class="inner"><h3>{{ $allFaces->count() }}</h3><p>Total Terdaftar</p></div><div class="icon"><i class="fas fa-users"></i></div></div></div>
    <div class="col-lg-3 col-6"><div class="small-box bg-success"><div class="inner"><h3>{{ $verified->total() }}</h3><p>Terverifikasi</p></div><div class="icon"><i class="fas fa-check-circle"></i></div></div></div>
    <div class="col-lg-3 col-6"><div class="small-box bg-warning"><div class="inner"><h3>{{ $pending->count() }}</h3><p>Menunggu Verifikasi</p></div><div class="icon"><i class="fas fa-clock"></i></div></div></div>
    <div class="col-lg-3 col-6"><div class="small-box bg-secondary"><div class="inner"><h3>{{ $allFaces->avg('quality_score') ? number_format($allFaces->avg('quality_score'), 0) : 0 }}%</h3><p>Rata-rata Quality</p></div><div class="icon"><i class="fas fa-chart-bar"></i></div></div></div>
</div>

<div class="card card-primary card-outline card-tabs">
    <div class="card-header p-0 pt-1">
        <ul class="nav nav-tabs">
            @foreach($typeOptions as $typeKey => $typeName)
                <li class="nav-item">
                    <a class="nav-link {{ $selectedType === $typeKey ? 'active' : '' }}" href="{{ route('admin.absensi.face-verification', ['type' => $typeKey]) }}">{{ $typeName }}</a>
                </li>
            @endforeach
        </ul>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <ul class="nav nav-pills">
                <li class="nav-item">
                    <a class="nav-link {{ $pending->count() > 0 ? 'active' : '' }}" data-toggle="tab" href="#tabPending">
                        <i class="fas fa-clock text-warning"></i> Menunggu Verifikasi
                        @if($pending->count() > 0)
                            <span class="badge badge-warning">{{ $pending->count() }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $pending->count() === 0 ? 'active' : '' }}" data-toggle="tab" href="#tabAll">
                        <i class="fas fa-list text-primary"></i> Semua Data Wajah
                    </a>
                </li>
            </ul>
        </div>

        <div class="tab-content">
            <div class="tab-pane {{ $pending->count() > 0 ? 'active' : '' }}" id="tabPending">
                @if($pending->count() > 0)
                    <div class="row">
                        @foreach($pending as $face)
                            @php
                                $profile = $face->user_type === 'gtk' ? $face->user->gtk : $face->user->siswa;
                                $name = $profile->nama_lengkap ?? $face->user->name ?? 'Unknown';
                                $identifier = $face->user_type === 'gtk' ? ($profile->nip ?? '-') : ($profile->nisn ?? '-');
                                $identifierLabelFace = $face->user_type === 'gtk' ? 'NIP' : 'NISN';
                            @endphp
                            <div class="col-lg-3 col-md-4 col-sm-6">
                                <div class="card card-outline card-warning">
                                    <div class="card-body text-center">
                                        <div class="mb-2"><img src="{{ $profile?->foto_profile_url ?? asset('vendor/adminlte/dist/img/user2-160x160.jpg') }}" class="img-circle" width="64" height="64" style="object-fit:cover;"></div>
                                        <h6 class="mb-0">{{ $name }}</h6>
                                        <small class="text-muted d-block">{{ $identifierLabelFace }}: {{ $identifier }}</small>
                                        <small class="text-muted d-block">{{ strtoupper($face->user_type) }}</small>
                                        <div class="mt-2">
                                            <span class="badge badge-info">{{ $face->total_captures }} capture</span>
                                            <span class="badge badge-secondary">Score: {{ number_format($face->quality_score, 0) }}%</span>
                                        </div>
                                        <div class="mt-1">
                                            @foreach($face->capture_angles ?? [] as $angle)
                                                <span class="badge badge-light">{{ $angle }}</span>
                                            @endforeach
                                        </div>
                                        <small class="text-muted d-block mt-1">{{ $face->created_at->format('d/m/Y H:i') }}</small>
                                        <div class="mt-3 btn-group">
                                            <form method="POST" action="{{ route('admin.absensi.face-verify', $face) }}">
                                                @csrf
                                                <input type="hidden" name="action" value="approve">
                                                <button class="btn btn-sm btn-success"><i class="fas fa-check"></i> Setujui</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.absensi.face-verify', $face) }}" class="ml-1">
                                                @csrf
                                                <input type="hidden" name="action" value="reject">
                                                <button class="btn btn-sm btn-danger" onclick="return confirm('Tolak data wajah ini?')"><i class="fas fa-times"></i> Tolak</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-check-circle fa-2x mb-2 text-success"></i><br>
                        Semua data wajah {{ strtolower($subjectLabel) }} sudah diverifikasi.
                    </div>
                @endif
            </div>

            <div class="tab-pane {{ $pending->count() === 0 ? 'active' : '' }}" id="tabAll">
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-sm" id="tabelWajah">
                        <thead>
                            <tr>
                                <th width="40">No</th>
                                <th>Nama</th>
                                <th>{{ $identifierLabel }}</th>
                                <th>Capture</th>
                                <th>Angle</th>
                                <th>Quality</th>
                                <th>Status</th>
                                <th>Diverifikasi</th>
                                <th>Tgl Registrasi</th>
                                <th width="140">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allFaces as $i => $face)
                                @php
                                    $profile = $face->user_type === 'gtk' ? $face->user->gtk : $face->user->siswa;
                                    $name = $profile->nama_lengkap ?? $face->user->name ?? '-';
                                    $identifier = $face->user_type === 'gtk' ? ($profile->nip ?? '-') : ($profile->nisn ?? '-');
                                @endphp
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $profile?->foto_profile_url ?? asset('vendor/adminlte/dist/img/user2-160x160.jpg') }}" class="img-circle mr-2" width="32" height="32" style="object-fit:cover;">
                                            <div>
                                                <div>{{ $name }}</div>
                                                <small class="text-muted">{{ strtoupper($face->user_type) }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $identifier }}</td>
                                    <td><span class="badge badge-info">{{ $face->total_captures }}</span></td>
                                    <td>@foreach($face->capture_angles ?? [] as $angle)<span class="badge badge-light">{{ $angle }}</span>@endforeach</td>
                                    <td>@php $q = $face->quality_score ?? 0; @endphp <span class="badge badge-{{ $q >= 80 ? 'success' : ($q >= 50 ? 'warning' : 'danger') }}">{{ number_format($q, 0) }}%</span></td>
                                    <td>
                                        @if($face->is_verified)
                                            <span class="badge badge-success"><i class="fas fa-check"></i> Verified</span>
                                        @else
                                            <span class="badge badge-warning"><i class="fas fa-clock"></i> Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($face->is_verified)
                                            {{ $face->verifier->name ?? '-' }}<br><small class="text-muted">{{ $face->verified_at?->format('d/m/Y H:i') }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $face->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.absensi.face-register', ['type' => $face->user_type, 'user_id' => $face->user_id]) }}" class="btn btn-info" title="Registrasi Ulang"><i class="fas fa-redo"></i></a>
                                            @if($face->is_verified)
                                                <form method="POST" action="{{ route('admin.absensi.face-encoding.reset', $face) }}" class="d-inline">
                                                    @csrf
                                                    <button class="btn btn-warning" title="Reset ke Pending" onclick="return confirm('Reset verifikasi ke pending?')"><i class="fas fa-undo"></i></button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('admin.absensi.face-encoding.destroy', $face) }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-danger" title="Hapus Data Wajah" onclick="return confirm('Hapus data wajah {{ $name }}? Data akan hilang permanen.')"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="text-center text-muted py-3">Belum ada data wajah terdaftar</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(function() {
    $('#tabelWajah').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' },
        pageLength: 25,
        order: [[8, 'desc']],
    });
});
</script>
@stop

@extends('adminlte::page')

@section('title', 'Verifikasi Wajah')

@section('content_header')
    <h1><i class="fas fa-user-check"></i> Data Registrasi Wajah</h1>
@stop

@section('content')
    {{-- Summary Cards --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner"><h3>{{ $allFaces->count() }}</h3><p>Total Terdaftar</p></div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner"><h3>{{ $verified->total() }}</h3><p>Terverifikasi</p></div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner"><h3>{{ $pending->count() }}</h3><p>Menunggu Verifikasi</p></div>
                <div class="icon"><i class="fas fa-clock"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-secondary">
                <div class="inner"><h3>{{ $allFaces->avg('quality_score') ? number_format($allFaces->avg('quality_score'), 0) : 0 }}%</h3><p>Rata-rata Quality</p></div>
                <div class="icon"><i class="fas fa-chart-bar"></i></div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ $pending->count() > 0 ? 'active' : '' }}" data-toggle="tab" href="#tabPending">
                        <i class="fas fa-clock text-warning"></i> Menunggu Verifikasi
                        @if($pending->count() > 0)
                            <span class="badge badge-warning">{{ $pending->count() }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $pending->count() == 0 ? 'active' : '' }}" data-toggle="tab" href="#tabAll">
                        <i class="fas fa-list text-primary"></i> Semua Data Wajah
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                {{-- Tab: Pending Verification --}}
                <div class="tab-pane {{ $pending->count() > 0 ? 'active' : '' }}" id="tabPending">
                    @if($pending->count() > 0)
                        <div class="row">
                            @foreach($pending as $face)
                                @php $gtk = $face->user->gtk ?? null; @endphp
                                <div class="col-lg-3 col-md-4 col-sm-6">
                                    <div class="card card-outline card-warning">
                                        <div class="card-body text-center">
                                            <div class="mb-2">
                                                @if($gtk && $gtk->foto_profile_url)
                                                    <img src="{{ $gtk->foto_profile_url }}" class="img-circle" width="64" height="64" style="object-fit:cover;">
                                                @else
                                                    <i class="fas fa-user-circle fa-3x text-muted"></i>
                                                @endif
                                            </div>
                                            <h6 class="mb-0">{{ $gtk->nama_lengkap ?? $face->user->name ?? 'Unknown' }}</h6>
                                            @if($gtk && $gtk->nip)
                                                <small class="text-muted d-block">NIP: {{ $gtk->nip }}</small>
                                            @endif
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
                                                    <button class="btn btn-sm btn-danger" onclick="return confirm('Tolak data wajah ini?')">
                                                        <i class="fas fa-times"></i> Tolak
                                                    </button>
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
                            Semua data wajah sudah diverifikasi.
                        </div>
                    @endif
                </div>

                {{-- Tab: All Face Data --}}
                <div class="tab-pane {{ $pending->count() == 0 ? 'active' : '' }}" id="tabAll">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-sm" id="tabelWajah">
                            <thead>
                                <tr>
                                    <th width="40">No</th>
                                    <th>Nama GTK</th>
                                    <th>NIP</th>
                                    <th>Capture</th>
                                    <th>Angle</th>
                                    <th>Quality</th>
                                    <th>Status</th>
                                    <th>Diverifikasi</th>
                                    <th>Tgl Registrasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($allFaces as $i => $face)
                                    @php $gtk = $face->user->gtk ?? null; @endphp
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($gtk && $gtk->foto_profile_url)
                                                    <img src="{{ $gtk->foto_profile_url }}" class="img-circle mr-2" width="32" height="32" style="object-fit:cover;">
                                                @else
                                                    <i class="fas fa-user-circle fa-2x text-muted mr-2"></i>
                                                @endif
                                                <span>{{ $gtk->nama_lengkap ?? $face->user->name ?? '-' }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $gtk->nip ?? '-' }}</td>
                                        <td><span class="badge badge-info">{{ $face->total_captures }}</span></td>
                                        <td>
                                            @foreach($face->capture_angles ?? [] as $angle)
                                                <span class="badge badge-light">{{ $angle }}</span>
                                            @endforeach
                                        </td>
                                        <td>
                                            @php $q = $face->quality_score ?? 0; @endphp
                                            <span class="badge badge-{{ $q >= 80 ? 'success' : ($q >= 50 ? 'warning' : 'danger') }}">
                                                {{ number_format($q, 0) }}%
                                            </span>
                                        </td>
                                        <td>
                                            @if($face->is_verified)
                                                <span class="badge badge-success"><i class="fas fa-check"></i> Verified</span>
                                            @else
                                                <span class="badge badge-warning"><i class="fas fa-clock"></i> Pending</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($face->is_verified)
                                                {{ $face->verifier->name ?? '-' }}<br>
                                                <small class="text-muted">{{ $face->verified_at?->format('d/m/Y H:i') }}</small>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>{{ $face->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="9" class="text-center text-muted py-3">Belum ada data wajah terdaftar</td></tr>
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

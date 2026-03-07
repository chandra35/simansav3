@extends('adminlte::page')

@section('title', 'Verifikasi Wajah')

@section('content_header')
    <h1><i class="fas fa-user-check"></i> Verifikasi Data Wajah</h1>
@stop

@section('content')
    {{-- Pending Verification --}}
    <div class="card card-warning card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-clock"></i> Menunggu Verifikasi ({{ $pending->count() }})</h3>
        </div>
        <div class="card-body">
            @if($pending->count() > 0)
                <div class="row">
                    @foreach($pending as $face)
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <div class="card card-outline card-warning">
                                <div class="card-body text-center">
                                    <div class="mb-2">
                                        <i class="fas fa-user-circle fa-3x text-muted"></i>
                                    </div>
                                    <h6>{{ $face->user->name ?? 'Unknown' }}</h6>
                                    <small class="text-muted">
                                        {{ $face->total_captures }} capture |
                                        Score: {{ number_format($face->quality_score, 0) }}%
                                    </small>
                                    <div class="mt-1">
                                        @foreach($face->capture_angles ?? [] as $angle)
                                            <span class="badge badge-light badge-sm">{{ $angle }}</span>
                                        @endforeach
                                    </div>
                                    <small class="text-muted d-block mt-1">{{ $face->created_at->diffForHumans() }}</small>
                                    <div class="mt-3">
                                        <form method="POST" action="{{ route('admin.absensi.face-verify', $face) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="action" value="approve">
                                            <button class="btn btn-sm btn-success"><i class="fas fa-check"></i> Setujui</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.absensi.face-verify', $face) }}" class="d-inline">
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
                <div class="text-center text-muted py-3">
                    <i class="fas fa-check-circle fa-2x mb-2"></i><br>
                    Semua data wajah sudah diverifikasi.
                </div>
            @endif
        </div>
    </div>

    {{-- Verified List --}}
    <div class="card card-success card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-check-circle"></i> Sudah Terverifikasi</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-sm">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Jumlah Capture</th>
                        <th>Quality Score</th>
                        <th>Diverifikasi Oleh</th>
                        <th>Tanggal Verifikasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($verified as $i => $face)
                        <tr>
                            <td>{{ $verified->firstItem() + $i }}</td>
                            <td>{{ $face->user->name ?? '-' }}</td>
                            <td>{{ $face->total_captures }}</td>
                            <td>{{ number_format($face->quality_score, 0) }}%</td>
                            <td>{{ $face->verifier->name ?? '-' }}</td>
                            <td>{{ $face->verified_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Belum ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($verified->hasPages())
            <div class="card-footer">{{ $verified->links() }}</div>
        @endif
    </div>
@stop

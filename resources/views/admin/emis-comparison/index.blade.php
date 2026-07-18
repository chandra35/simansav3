@extends('adminlte::page')

@section('title', 'Cek Data EMIS')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="mb-1"><i class="fas fa-exchange-alt mr-2 text-primary"></i>Cek Data EMIS</h1>
            <p class="text-muted mb-0">Rekonsiliasi data siswa aktif SIMANSA dengan snapshot EMIS Lembaga.</p>
        </div>
        @can('sync-emis-comparison')
            <form method="POST" action="{{ route('admin.emis-comparison.sync') }}" id="syncEmisForm">
                @csrf
                <button type="submit" class="btn btn-primary" {{ $tokenStatus['usable'] ? '' : 'disabled' }}>
                    <i class="fas fa-sync-alt mr-1"></i> Sinkronkan dari EMIS
                </button>
            </form>
        @endcan
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle mr-1"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-circle mr-1"></i>{{ session('error') }}
        </div>
    @endif

    @php
        $tokenAlert = match($tokenStatus['state']) {
            'active' => 'success',
            'expiring' => 'warning',
            default => 'danger',
        };
        $statusLabels = [
            'exact' => ['Sama', 'success'],
            'normalized' => ['Setara', 'info'],
            'similar' => ['Mirip', 'warning'],
            'different' => ['Berbeda', 'danger'],
            'only_simansa' => ['Hanya SIMANSA', 'secondary'],
            'only_emis' => ['Hanya EMIS', 'dark'],
        ];
        $detailLabels = \App\Services\SmartStudentComparator::LABELS;
    @endphp

    <div class="alert alert-{{ $tokenAlert }} d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <i class="fas {{ $tokenStatus['usable'] ? 'fa-key' : 'fa-lock' }} mr-2"></i>
            <strong>{{ $tokenStatus['message'] }}</strong>
            @if($tokenStatus['expires_at'])
                <span class="ml-1">Kedaluwarsa {{ $tokenStatus['expires_at']->format('d/m/Y H:i') }} WIB.</span>
            @endif
            @if(!$tokenStatus['usable'] && $latestSync?->finished_at)
                <span class="d-block small mt-1">Snapshot terakhir tetap dapat digunakan; tidak ada request yang dikirim ke EMIS.</span>
            @endif
        </div>
        @can('manage-settings')
            <a href="{{ route('admin.pengaturan.update-api-token.index') }}#emis-institusi" class="btn btn-sm btn-light mt-2 mt-md-0">
                <i class="fas fa-edit mr-1"></i> Perbarui Token Lembaga
            </a>
        @endcan
    </div>

    @if($latestSync)
        <div class="callout callout-{{ $latestSync->status === 'completed' ? 'info' : ($latestSync->status === 'failed' ? 'danger' : 'warning') }} py-2">
            <div class="d-flex justify-content-between flex-wrap">
                <span>
                    <strong>Sinkronisasi terakhir:</strong>
                    {{ optional($latestSync->finished_at ?? $latestSync->started_at)->format('d/m/Y H:i:s') }} WIB
                    &middot; {{ ucfirst($latestSync->status) }}
                    @if($latestSync->status === 'completed')
                        &middot; {{ number_format($latestSync->total_students) }} siswa EMIS
                    @endif
                </span>
                @if($latestSync->total_pages)
                    <span>{{ $latestSync->processed_pages }}/{{ $latestSync->total_pages }} halaman API</span>
                @endif
            </div>
        </div>
    @else
        <div class="callout callout-info">
            Belum ada snapshot EMIS. Tekan <strong>Sinkronkan dari EMIS</strong> untuk membuat pembandingan pertama.
        </div>
    @endif

    <div class="row">
        @foreach([
            ['key' => 'simansa', 'label' => 'Siswa Aktif SIMANSA', 'icon' => 'fa-users', 'color' => 'primary', 'filter' => 'all'],
            ['key' => 'emis', 'label' => 'Siswa Aktif EMIS', 'icon' => 'fa-cloud', 'color' => 'info', 'filter' => 'all'],
            ['key' => 'exact', 'label' => 'Sama / Setara', 'icon' => 'fa-check-circle', 'color' => 'success', 'filter' => 'exact'],
            ['key' => 'similar', 'label' => 'Nama Mirip', 'icon' => 'fa-adjust', 'color' => 'warning', 'filter' => 'similar'],
            ['key' => 'different', 'label' => 'Berbeda', 'icon' => 'fa-exclamation-triangle', 'color' => 'danger', 'filter' => 'different'],
            ['key' => 'only_simansa', 'label' => 'Hanya SIMANSA', 'icon' => 'fa-arrow-left', 'color' => 'secondary', 'filter' => 'only_simansa'],
            ['key' => 'only_emis', 'label' => 'Hanya EMIS', 'icon' => 'fa-arrow-right', 'color' => 'dark', 'filter' => 'only_emis'],
        ] as $card)
            <div class="col-6 col-md-4 col-xl">
                <a href="{{ route('admin.emis-comparison.index', ['status' => $card['filter']]) }}" class="text-decoration-none">
                    <div class="small-box bg-{{ $card['color'] }}">
                        <div class="inner">
                            <h3>{{ number_format($stats[$card['key']]) }}</h3>
                            <p>{{ $card['label'] }}</p>
                        </div>
                        <div class="icon"><i class="fas {{ $card['icon'] }}"></i></div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-list mr-1"></i>Daftar Pembandingan</h3>
        </div>
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('admin.emis-comparison.index') }}" class="form-row align-items-end">
                <div class="col-md-4 mb-2">
                    <label for="search">Nama atau NISN</label>
                    <input id="search" type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Cari data siswa...">
                </div>
                <div class="col-md-3 mb-2">
                    <label for="status">Status Pembandingan</label>
                    <select id="status" name="status" class="form-control">
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua siswa SIMANSA</option>
                        <option value="exact" {{ $status === 'exact' ? 'selected' : '' }}>Sama / Setara</option>
                        <option value="similar" {{ $status === 'similar' ? 'selected' : '' }}>Nama Mirip</option>
                        <option value="different" {{ $status === 'different' ? 'selected' : '' }}>Berbeda</option>
                        <option value="only_simansa" {{ $status === 'only_simansa' ? 'selected' : '' }}>Hanya di SIMANSA</option>
                        <option value="only_emis" {{ $status === 'only_emis' ? 'selected' : '' }}>Hanya di EMIS</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label for="kelas_id">Kelas SIMANSA</label>
                    <select id="kelas_id" name="kelas_id" class="form-control" {{ $listMode === 'emis' ? 'disabled' : '' }}>
                        <option value="">Semua kelas</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ $kelasId === $class->id ? 'selected' : '' }}>{{ $class->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-filter mr-1"></i>Filter</button>
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead>
                    <tr>
                        <th style="width:60px">No</th>
                        <th>Siswa</th>
                        <th>Kelas SIMANSA</th>
                        <th>Rombel EMIS</th>
                        <th>Status</th>
                        <th>Field Perlu Dicek</th>
                        <th style="width:120px">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($items as $index => $item)
                        @php
                            $siswa = $listMode === 'simansa' ? $item : null;
                            $snapshot = $listMode === 'simansa' ? $item->emisStudentSnapshot : $item;
                            $rowStatus = $snapshot?->comparison_status ?? 'only_simansa';
                            [$statusText, $statusColor] = $statusLabels[$rowStatus] ?? [ucfirst($rowStatus), 'secondary'];
                            $details = $snapshot?->comparison_details ?? [];
                            $differentFields = collect($details)->filter(fn($detail) => in_array($detail['status'] ?? '', ['different', 'similar', 'simansa_empty'], true))->keys();
                        @endphp
                        <tr>
                            <td>{{ $items->firstItem() + $index }}</td>
                            <td>
                                <strong>{{ $siswa?->nama_lengkap ?? $snapshot?->full_name ?? '-' }}</strong>
                                <small class="d-block text-muted">NISN: {{ $siswa?->nisn ?? $snapshot?->nisn ?? '-' }}</small>
                            </td>
                            <td>{{ $siswa?->kelasSaatIni?->nama_kelas ?? '-' }}</td>
                            <td>
                                {{ $snapshot?->study_group_name ?? '-' }}
                                @if($snapshot?->level_name)<small class="d-block text-muted">{{ $snapshot->level_name }}</small>@endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $statusColor }}">{{ $statusText }}</span>
                                @if($rowStatus === 'similar' && $snapshot?->name_similarity)
                                    <small class="d-block text-muted mt-1">{{ number_format($snapshot->name_similarity, 1) }}% mirip</small>
                                @endif
                            </td>
                            <td>
                                @forelse($differentFields as $field)
                                    <span class="badge badge-light border mr-1 mb-1">{{ $detailLabels[$field] ?? $field }}</span>
                                @empty
                                    <span class="text-muted">—</span>
                                @endforelse
                            </td>
                            <td>
                                <a href="{{ $siswa ? route('admin.emis-comparison.show', $siswa) : route('admin.emis-comparison.show-emis', $snapshot) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-columns mr-1"></i>Bandingkan
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-5">Tidak ada data untuk filter ini.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($items->hasPages())
            <div class="card-footer">{{ $items->links() }}</div>
        @endif
    </div>
@stop

@section('css')
<style>
    .small-box .inner p { min-height: 38px; margin-bottom: 0; }
    .small-box .icon > i { font-size: 55px; top: 14px; }
    @media (min-width: 1200px) { .col-xl { flex: 1 0 14.285%; max-width: 14.285%; } }
</style>
@stop

@section('js')
<script>
$(function () {
    $('#syncEmisForm').on('submit', function (event) {
        if (!confirm('Sinkronkan seluruh siswa aktif dari EMIS sekarang? Snapshot lama baru diganti setelah semua halaman berhasil diambil.')) {
            event.preventDefault();
            return;
        }
        $(this).find('button').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Menyinkronkan...');
    });
});
</script>
@stop

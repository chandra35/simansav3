@extends('adminlte::page')

@section('title', 'Rekap Absensi GTK')

@section('content_header')
    <h1><i class="fas fa-chart-bar"></i> Rekap Absensi GTK</h1>
@stop

@section('content')
    {{-- Filter --}}
    <div class="card card-outline card-primary">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.absensi.rekap') }}" class="form-inline">
                <div class="form-group mr-3">
                    <label class="mr-2">Bulan:</label>
                    <select name="bulan" class="form-control">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(null, $m)->isoFormat('MMMM') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="form-group mr-3">
                    <label class="mr-2">Tahun:</label>
                    <select name="tahun" class="form-control">
                        @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <button class="btn btn-primary"><i class="fas fa-search"></i> Tampilkan</button>
                <a href="{{ route('admin.absensi.export', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-success ml-2">
                    <i class="fas fa-file-excel"></i> Export Excel
                </a>
            </form>
        </div>
    </div>

    {{-- Hari Libur Info --}}
    @if($hariLibur->count() > 0)
        <div class="alert alert-warning">
            <i class="fas fa-calendar-times"></i> <strong>Hari Libur bulan ini:</strong>
            @foreach($hariLibur as $hl)
                <span class="badge badge-danger ml-1">{{ $hl->tanggal->format('d') }} - {{ $hl->nama }}</span>
            @endforeach
        </div>
    @endif

    {{-- Rekap Table --}}
    <div class="card">
        <div class="card-body table-responsive p-0">
            @php
                $daysInMonth = \Carbon\Carbon::create($tahun, $bulan, 1)->daysInMonth;
            @endphp
            <table class="table table-bordered table-sm table-hover" style="font-size:0.8rem;">
                <thead class="thead-dark">
                    <tr>
                        <th class="text-center" style="position:sticky; left:0; background:#343a40; z-index:2;" width="40">No</th>
                        <th style="position:sticky; left:40px; background:#343a40; z-index:2; min-width:180px;">Nama</th>
                        @for($d = 1; $d <= $daysInMonth; $d++)
                            @php
                                $date = \Carbon\Carbon::create($tahun, $bulan, $d);
                                $isWeekend = $date->isWeekend();
                                $isHoliday = $hariLibur->contains('tanggal', $date->format('Y-m-d'));
                            @endphp
                            <th class="text-center {{ ($isWeekend || $isHoliday) ? 'bg-danger text-white' : '' }}" width="28" title="{{ $date->isoFormat('dddd, D MMM') }}">
                                {{ $d }}
                            </th>
                        @endfor
                        <th class="text-center bg-success text-white" width="30" title="Total Hadir">H</th>
                        <th class="text-center bg-warning" width="30" title="Total Terlambat">T</th>
                        <th class="text-center bg-info text-white" width="30" title="Total Izin">I</th>
                        <th class="text-center bg-primary text-white" width="30" title="Total Sakit">S</th>
                        <th class="text-center bg-danger text-white" width="30" title="Total Alpa">A</th>
                    </tr>
                </thead>
                <tbody>
                    @php $no = 0; @endphp
                    @forelse($absensis as $userId => $userAbsensis)
                        @php
                            $no++;
                            $firstAbsensi = $userAbsensis->first();
                            $gtk = $firstAbsensi->user->gtk ?? null;
                            $statusCounts = $userAbsensis->groupBy('status')->map->count();
                        @endphp
                        <tr>
                            <td class="text-center" style="position:sticky; left:0; background:#fff; z-index:1;">{{ $no }}</td>
                            <td style="position:sticky; left:40px; background:#fff; z-index:1; white-space:nowrap;">
                                {{ $gtk?->nama_lengkap ?? $firstAbsensi->user->name }}
                            </td>
                            @for($d = 1; $d <= $daysInMonth; $d++)
                                @php
                                    $date = \Carbon\Carbon::create($tahun, $bulan, $d);
                                    $isWeekend = $date->isWeekend();
                                    $dayAbsensi = $userAbsensis->firstWhere('tanggal', $date->format('Y-m-d'));
                                    $isHoliday = $hariLibur->contains(fn($hl) => $hl->tanggal->format('Y-m-d') === $date->format('Y-m-d'));

                                    $cellClass = '';
                                    $cellText = '';
                                    if ($isWeekend || $isHoliday) {
                                        $cellClass = 'bg-light text-muted';
                                        $cellText = '-';
                                    } elseif ($dayAbsensi) {
                                        $cellText = strtoupper(substr($dayAbsensi->status, 0, 1));
                                        $cellClass = match($dayAbsensi->status) {
                                            'hadir' => 'bg-success text-white',
                                            'terlambat' => 'bg-warning',
                                            'izin' => 'bg-info text-white',
                                            'sakit' => 'bg-primary text-white',
                                            'dinas_luar' => 'bg-secondary text-white',
                                            'cuti' => 'bg-dark text-white',
                                            default => 'bg-danger text-white',
                                        };
                                    } elseif ($date->lte(now())) {
                                        $cellClass = 'bg-danger text-white';
                                        $cellText = 'A';
                                    }
                                @endphp
                                <td class="text-center {{ $cellClass }}" title="{{ $date->format('d/m') . ($dayAbsensi ? ': ' . ucfirst($dayAbsensi->status) : '') }}">
                                    {{ $cellText }}
                                </td>
                            @endfor
                            <td class="text-center font-weight-bold">{{ $statusCounts->get('hadir', 0) }}</td>
                            <td class="text-center font-weight-bold">{{ $statusCounts->get('terlambat', 0) }}</td>
                            <td class="text-center font-weight-bold">{{ $statusCounts->get('izin', 0) }}</td>
                            <td class="text-center font-weight-bold">{{ $statusCounts->get('sakit', 0) }}</td>
                            <td class="text-center font-weight-bold">{{ $statusCounts->get('alpa', 0) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $daysInMonth + 7 }}" class="text-center text-muted py-4">
                                Belum ada data absensi untuk bulan ini
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('css')
<style>
    .table-bordered th, .table-bordered td { vertical-align: middle !important; padding: 4px !important; }
</style>
@stop

@extends('adminlte::page')

@section('title', 'Timetable Jadwal Pelajaran')

@section('content_header')
    <h1><i class="fas fa-table mr-2"></i>Roster Jadwal Pelajaran</h1>
@stop

@section('css')
<style>
    .timetable th, .timetable td {
        text-align: center;
        vertical-align: middle;
        font-size: 12px;
    }
    .timetable .jam-cell {
        width: 80px;
        background-color: #f8f9fa;
    }
    .timetable .mapel-cell {
        min-width: 120px;
    }
    .timetable .mapel-box {
        background-color: #e3f2fd;
        border-radius: 4px;
        padding: 5px;
        margin: 2px;
    }
    .timetable .mapel-box .mapel-name {
        font-weight: bold;
        color: #1565c0;
    }
    .timetable .mapel-box .guru-name {
        font-size: 10px;
        color: #666;
    }
</style>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filter</h3>
        </div>
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tahun Pelajaran</label>
                        <select name="tahun_pelajaran_id" class="form-control">
                            @foreach($tahunPelajaran as $tp)
                                <option value="{{ $tp->id }}" {{ $tp->id == $selectedTahunPelajaran ? 'selected' : '' }}>
                                    {{ $tp->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Kelas</label>
                        <select name="kelas_id" class="form-control">
                            <option value="">-- Semua Kelas --</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id }}" {{ $k->id == $selectedKelas ? 'selected' : '' }}>
                                    {{ $k->nama_lengkap }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Semester</label>
                        <select name="semester" class="form-control">
                            <option value="1" {{ $selectedSemester == 1 ? 'selected' : '' }}>Semester 1</option>
                            <option value="2" {{ $selectedSemester == 2 ? 'selected' : '' }}>Semester 2</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-filter mr-1"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    @if($selectedKelas)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Jadwal Kelas {{ $kelasNama ?? '' }}</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-success" onclick="window.print()">
                        <i class="fas fa-print mr-1"></i> Cetak
                    </button>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-bordered timetable">
                    <thead class="thead-light">
                        <tr>
                            <th class="jam-cell">Jam Ke</th>
                            <th class="jam-cell">Waktu</th>
                            @foreach($hariList as $hari)
                                <th class="mapel-cell">{{ $hari }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @for($jam = 1; $jam <= 10; $jam++)
                            <tr>
                                <td class="jam-cell"><strong>{{ $jam }}</strong></td>
                                <td class="jam-cell">
                                    @php
                                        $jadwalJam = $jadwal->first(function($j) use ($jam) { return $j->jam_ke == $jam; });
                                    @endphp
                                    @if($jadwalJam)
                                        {{ $jadwalJam->waktu_mulai }} - {{ $jadwalJam->waktu_selesai }}
                                    @else
                                        -
                                    @endif
                                </td>
                                @foreach($hariList as $hari)
                                    <td class="mapel-cell">
                                        @php
                                            $jadwalItem = $jadwal->first(function($j) use ($hari, $jam) {
                                                return $j->hari == $hari && $j->jam_ke == $jam;
                                            });
                                        @endphp
                                        @if($jadwalItem)
                                            <div class="mapel-box">
                                                <div class="mapel-name">{{ $jadwalItem->mapel?->singkatan ?? $jadwalItem->mapel?->nama ?? '-' }}</div>
                                                <div class="guru-name">{{ $jadwalItem->guru?->nama ?? '-' }}</div>
                                                @if($jadwalItem->ruangan)
                                                    <div class="guru-name"><i class="fas fa-door-open"></i> {{ $jadwalItem->ruangan }}</div>
                                                @endif
                                            </div>
                                        @else
                                            -
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="alert alert-info">
            <i class="fas fa-info-circle mr-2"></i>
            Pilih kelas untuk melihat roster jadwal pelajaran
        </div>
    @endif
@stop

@extends('adminlte::page')

@section('title', 'Jadwal Pelajaran')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-chalkboard-teacher"></i> Jadwal Pelajaran</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                @can('manage-jadwal-pelajaran')
                <a href="{{ route('admin.jadwal-jam-config.index') }}" class="btn btn-secondary">
                    <i class="fas fa-clock"></i> Konfigurasi Jam
                </a>
                @endcan
            </div>
        </div>
    </div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter"></i> Pilih Kelas</h3>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.jadwal-pelajaran.timetable') }}" id="formPilihKelas">
            <div class="form-row align-items-end">
                <div class="form-group col-md-4">
                    <label>Tahun Pelajaran</label>
                    <select name="tahun_pelajaran_id" class="form-control select2" id="selTahun">
                        <option value="">-- Pilih --</option>
                        @foreach($tahunList as $t)
                            <option value="{{ $t->id }}" {{ $tahunId == $t->id ? 'selected' : '' }}>
                                {{ $t->tahun_pelajaran }}{{ $t->is_active ? ' (Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label>Kelas</label>
                    <select name="kelas_id" class="form-control select2" id="selKelas">
                        <option value="">-- Pilih kelas --</option>
                        @foreach($kelasList as $k)
                            <option value="{{ $k->id }}">
                                {{ $k->nama_kelas }}{{ $k->jurusan ? ' - '.$k->jurusan->nama_jurusan : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label>Semester</label>
                    <select name="semester" class="form-control">
                        <option value="1">1 (Ganjil)</option>
                        <option value="2">2 (Genap)</option>
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-table"></i> Lihat Jadwal
                    </button>
                </div>
            </div>
        </form>

        @if(!$hasJamConfig && $tahunId)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            Konfigurasi jam pelajaran untuk tahun ini belum ada.
            @can('manage-jadwal-pelajaran')
                <a href="{{ route('admin.jadwal-jam-config.index', ['tahun_pelajaran_id' => $tahunId]) }}" class="alert-link">
                    Buat konfigurasi jam sekarang &rarr;
                </a>
            @endcan
        </div>
        @endif
    </div>
</div>

@if($kelasList->isNotEmpty())
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-list"></i> Daftar Kelas</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Kelas</th>
                    <th>Jurusan</th>
                    <th>Wali Kelas</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($kelasList->groupBy('tingkat') as $tingkat => $kelasGroup)
                <tr class="bg-light"><td colspan="4" class="font-weight-bold small text-muted">Tingkat {{ $tingkat }}</td></tr>
                @foreach($kelasGroup as $k)
                <tr>
                    <td>{{ $k->nama_kelas }}</td>
                    <td>{{ $k->jurusan?->nama_jurusan ?? '-' }}</td>
                    <td>{{ $k->waliKelas?->nama_lengkap ?? '-' }}</td>
                    <td class="text-center">
                        <a href="{{ route('admin.jadwal-pelajaran.timetable', ['kelas_id' => $k->id, 'tahun_pelajaran_id' => $tahunId, 'semester' => 1]) }}"
                           class="btn btn-sm btn-primary">
                            <i class="fas fa-table"></i> Sem 1
                        </a>
                        <a href="{{ route('admin.jadwal-pelajaran.timetable', ['kelas_id' => $k->id, 'tahun_pelajaran_id' => $tahunId, 'semester' => 2]) }}"
                           class="btn btn-sm btn-info">
                            <i class="fas fa-table"></i> Sem 2
                        </a>
                    </td>
                </tr>
                @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection

@section('js')
<script>
$(function () {
    $('.select2').select2({ theme: 'bootstrap4', width: '100%' });
    $('#selTahun').on('change', function () {
        const tahunId = $(this).val();
        if (!tahunId) return;
        window.location.href = '{{ route("admin.jadwal-pelajaran.index") }}?tahun_pelajaran_id=' + tahunId;
    });
});
</script>
@endsection
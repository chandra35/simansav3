@extends('adminlte::page')

@section('title', 'Import Jadwal Wakakur')

@section('plugins.Sweetalert2', true)

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6"><h1><i class="fas fa-file-import text-primary"></i> Import Jadwal Wakakur</h1></div>
        <div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('admin.jadwal-pelajaran.index') }}">Jadwal Pelajaran</a></li><li class="breadcrumb-item active">Import</li></ol></div>
    </div>
@stop

@section('content')
<div class="simansa-jadwal-import">
    <div class="card bg-gradient-primary text-white mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="text-uppercase small font-weight-bold mb-2"><i class="fas fa-calendar-check"></i> Jadwal resmi Wakakur</div>
                    <h3 class="mb-1">Upload, periksa, lalu timpa jadwal semester</h3>
                    <p class="mb-0">Template membaca kode seperti <strong>56S</strong>: GTK 56 mengajar mapel S. Data hanya disimpan setelah seluruh mapping valid dan Anda mengonfirmasi penimpaan.</p>
                </div>
                <div class="col-lg-4 mt-3 mt-lg-0 text-lg-right">
                    <a href="{{ route('admin.jadwal-mapping.index', ['tahun_pelajaran_id' => $tahunId]) }}" class="btn btn-light btn-sm"><i class="fas fa-link"></i> Periksa Mapping Kode</a>
                </div>
            </div>
        </div>
    </div>

    @if(session('error'))<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>@endif

    <div class="card card-outline card-primary">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-upload"></i> Upload Template</h3></div>
        <form method="POST" action="{{ route('admin.jadwal-pelajaran.import.preview') }}" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                <div class="form-row align-items-end">
                    <div class="form-group col-md-4">
                        <label>Tahun Pelajaran</label>
                        <select class="form-control" name="tahun_pelajaran_id" required>
                            @foreach($tahunList as $tahun)
                                <option value="{{ $tahun->id }}" @selected($tahunId == $tahun->id)>{{ $tahun->nama }}{{ $tahun->is_active ? ' · Aktif' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Semester</label>
                        <select class="form-control" name="semester"><option value="1" @selected($semester === 1)>Ganjil</option><option value="2" @selected($semester === 2)>Genap</option></select>
                    </div>
                    <div class="form-group col-md-4">
                        <label>File jadwal</label>
                        <input class="form-control @error('file') is-invalid @enderror" type="file" name="file" accept=".xls,.xlsx" required>
                        <small class="text-muted">Format Excel .xls atau .xlsx, maksimal 10 MB.</small>
                        @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group col-md-2"><button class="btn btn-primary btn-block" type="submit"><i class="fas fa-search"></i> Buat Preview</button></div>
                </div>
            </div>
        </form>
    </div>

    @if($preview)
        <div class="row">
            <div class="col-md-3 col-6"><div class="info-box"><span class="info-box-icon bg-primary"><i class="fas fa-list"></i></span><div class="info-box-content"><span class="info-box-text">Slot terbaca</span><span class="info-box-number">{{ count($preview['rows']) }}</span></div></div></div>
            <div class="col-md-3 col-6"><div class="info-box"><span class="info-box-icon bg-success"><i class="fas fa-check"></i></span><div class="info-box-content"><span class="info-box-text">Siap impor</span><span class="info-box-number">{{ $preview['ready_count'] }}</span></div></div></div>
            <div class="col-md-3 col-6"><div class="info-box"><span class="info-box-icon {{ $preview['error_count'] ? 'bg-danger' : 'bg-secondary' }}"><i class="fas fa-exclamation-triangle"></i></span><div class="info-box-content"><span class="info-box-text">Perlu diperbaiki</span><span class="info-box-number">{{ $preview['error_count'] }}</span></div></div></div>
            <div class="col-md-3 col-6"><div class="info-box"><span class="info-box-icon bg-warning"><i class="fas fa-history"></i></span><div class="info-box-content"><span class="info-box-text">Akan ditimpa</span><span class="info-box-number">{{ $preview['existing_count'] }}</span></div></div></div>
        </div>

        @if(!empty($preview['day_max_jam']))
            <div class="callout callout-info py-2 mb-3">
                <small><i class="fas fa-clock mr-1"></i> Jumlah jam mengikuti file Wakakur:
                    @foreach($preview['day_max_jam'] as $hari => $jamTerakhir)
                        <strong>{{ ucfirst($hari) }} {{ $jamTerakhir }} jam</strong>{{ $loop->last ? '.' : ' · ' }}
                    @endforeach
                </small>
            </div>
        @endif

        @if($preview['warnings'] || $preview['ignored'])
            <div class="alert alert-warning"><i class="fas fa-info-circle"></i>
                {{ $preview['ignored'] ? $preview['ignored'].' slot kolom BK dilewati karena bukan jadwal kelas reguler.' : '' }}
                @if($preview['warnings'])<ul class="mb-0 mt-2">@foreach(array_slice($preview['warnings'], 0, 8) as $warning)<li>{{ $warning }}</li>@endforeach</ul>@endif
            </div>
        @endif
        @if($preview['attendance_count'])
            <div class="alert alert-danger"><i class="fas fa-lock"></i> {{ $preview['attendance_count'] }} sesi absensi siswa sudah memakai jadwal ini. Demi menjaga riwayat absensi, impor penimpaan dikunci.</div>
        @endif
        @if(count($preview['missing_time_slots']))
            <div class="alert alert-warning">
                <i class="fas fa-clock"></i>
                <strong>Konfigurasi slot jam belum lengkap.</strong>
                {{ count($preview['missing_time_slots']) }} kombinasi hari dan jam belum mempunyai waktu mulai/selesai.
                Atur slot jam terlebih dahulu agar jadwal yang diimpor langsung memiliki waktu yang benar.
                <a href="{{ route('admin.jadwal-pelajaran.index', ['tahun_pelajaran_id' => $preview['tahun']->id]) }}" class="alert-link">Buka Jadwal Pelajaran</a>
            </div>
        @endif

        <div class="card card-outline {{ $preview['error_count'] ? 'card-danger' : 'card-success' }}">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-clipboard-check"></i> Preview {{ $preview['file_name'] }}</h3></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead><tr><th>Hari/Jam</th><th>Kelas</th><th>Kode</th><th>GTK</th><th>Mapel</th><th>Status</th></tr></thead>
                        <tbody>
                        @foreach(array_slice($preview['rows'], 0, 150) as $row)
                            <tr><td>{{ ucfirst($row['hari']) }} · {{ $row['jam_ke'] }}</td><td>{{ $row['kelas_nama'] }}</td><td><code>{{ $row['kode_gtk'] }}{{ $row['kode_mapel'] }}</code></td><td>{{ $row['gtk_excel'] ?? '-' }}</td><td>{{ $row['mapel_excel'] ?? '-' }}</td><td>@if($row['ready'])<span class="badge badge-success">Siap</span>@else <span class="badge badge-danger">{{ implode(' ', $row['errors']) }}</span>@endif</td></tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @if(count($preview['rows']) > 150)<div class="p-3 text-muted">Menampilkan 150 dari {{ count($preview['rows']) }} slot. Seluruh slot tetap akan diimpor setelah valid.</div>@endif
            </div>
            <div class="card-footer d-flex flex-wrap align-items-center justify-content-between" style="gap:10px">
                <span class="text-muted">Target: {{ $preview['tahun']->nama }} · Semester {{ $preview['semester'] }}</span>
                @if($preview['error_count'] === 0 && ! $preview['attendance_count'] && ! count($preview['missing_time_slots']))
                    <form method="POST" action="{{ route('admin.jadwal-pelajaran.import.commit') }}" class="mb-0" id="wakakurImportCommit">
                        @csrf<input type="hidden" name="token" value="{{ $preview['token'] }}">
                        <div class="custom-control custom-checkbox d-inline-block mr-2"><input class="custom-control-input" type="checkbox" id="confirm_replace" name="confirm_replace" value="1" required><label class="custom-control-label" for="confirm_replace">Saya setuju jadwal semester ini ditimpa.</label></div>
                        <button type="submit" class="btn btn-danger"><i class="fas fa-file-import"></i> Konfirmasi & Import</button>
                    </form>
                @elseif($preview['error_count'])
                    <a class="btn btn-warning" href="{{ route('admin.jadwal-mapping.index', ['tahun_pelajaran_id' => $preview['tahun']->id]) }}"><i class="fas fa-link"></i> Lengkapi Mapping Dulu</a>
                @else
                    <a class="btn btn-warning" href="{{ route('admin.jadwal-pelajaran.index', ['tahun_pelajaran_id' => $preview['tahun']->id]) }}"><i class="fas fa-clock"></i> Atur Slot Jam Dulu</a>
                @endif
            </div>
        </div>
    @endif
</div>
@stop

@section('css')
<style>.simansa-jadwal-import .info-box{min-height:86px}.simansa-jadwal-import .info-box-number{font-size:1.35rem}@media(max-width:575.98px){.simansa-jadwal-import .info-box-content{padding-left:6px}.simansa-jadwal-import .info-box-icon{width:52px;font-size:1.25rem}}</style>
@stop

@section('js')
@if($preview)
<script>
document.getElementById('wakakurImportCommit')?.addEventListener('submit', function (event) {
    event.preventDefault();
    const form = this;

    Swal.fire({
        icon: 'warning',
        title: 'Timpa jadwal semester?',
        html: '<p class="mb-2">Jadwal yang ada pada semester ini akan diganti dengan hasil impor.</p>' +
            '<div class="text-left small bg-light rounded p-2">' +
                '<div><strong>Target:</strong> {{ $preview["tahun"]->nama }} · Semester {{ $preview["semester"] }}</div>' +
                '<div><strong>Slot impor:</strong> {{ count($preview["rows"]) }}</div>' +
                '<div><strong>Jadwal ditimpa:</strong> {{ $preview["existing_count"] }}</div>' +
            '</div>',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-file-import"></i> Ya, import jadwal',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#dc3545',
        focusCancel: true,
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
});
</script>
@endif
@stop

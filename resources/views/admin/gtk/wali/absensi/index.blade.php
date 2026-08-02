@extends('adminlte::page')

@section('title', 'Absensi Harian — Kelas Saya')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-clipboard-check text-primary"></i> Absensi Harian</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.gtk.dashboard') }}">Dashboard Saya</a></li>
                <li class="breadcrumb-item active">Absensi Harian</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="gtk-wali-absensi-page">
    <div class="card bg-gradient-primary text-white mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h3 class="mb-1"><i class="fas fa-clipboard-check mr-1"></i> Kehadiran Kelas Saya</h3>
                    <p class="mb-2 text-white-50">Catat kehadiran siswa {{ $kelas->nama_kelas }} untuk tanggal terpilih.</p>
                    <p class="mb-0">Simpan sebagai draft atau finalkan setelah seluruh status diperiksa.</p>
                </div>
                <div class="col-lg-4 mt-3 mt-lg-0 text-center">
                    <div class="text-white-50 small text-uppercase font-weight-bold mb-2">Laporan Kehadiran</div>
                    <a href="{{ route('admin.gtk.wali.absensi.rekap', ['kelas_id' => $kelas->id]) }}" class="btn btn-light">
                        <i class="fas fa-chart-bar mr-1"></i> Buka Rekap
                    </a>
                </div>
            </div>
        </div>
    </div>

    @includeWhen($kelasList->count() > 1, 'admin.gtk.wali.partials.kelas-switcher', ['route' => 'admin.gtk.wali.absensi.index', 'extraQuery' => ['tanggal' => $tanggal]])

    @if(session('success'))
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <div class="card simansa-filter-panel mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.gtk.wali.absensi.index') }}" class="form-inline">
                <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
                <label class="mr-2 mb-0 font-weight-600"><i class="fas fa-calendar-day mr-1"></i> Tanggal:</label>
                <input type="date" name="tanggal" value="{{ $tanggal }}" max="{{ date('Y-m-d') }}" class="form-control mr-2" onchange="this.form.submit()">
                <noscript><button type="submit" class="btn btn-primary">Muat</button></noscript>
            </form>
        </div>
    </div>

    @php $locked = $session && $session->status === 'final' && $session->locked_at && $session->locked_at->isPast(); @endphp

    @if($session && $session->status === 'final')
        <div class="alert {{ $locked ? 'alert-secondary' : 'alert-warning' }}">
            <i class="fas fa-lock"></i> Sesi ini sudah <strong>difinalkan</strong>{{ $locked ? ' dan dikunci (hubungi admin untuk koreksi).' : '. Perubahan memerlukan alasan revisi.' }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.gtk.wali.absensi.store') }}" id="formAbsensi">
        @csrf
        <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
        <input type="hidden" name="tanggal" value="{{ $tanggal }}">

        <div class="card card-outline card-primary">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0"><i class="fas fa-users"></i> {{ $students->count() }} Siswa · {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}</h3>
                @unless($locked)
                    <button type="button" class="btn btn-sm btn-success" id="btnHadirSemua">
                        <i class="fas fa-check-double"></i> Hadir Semua
                    </button>
                @endunless
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width:48px">No</th>
                            <th>Nama Siswa</th>
                            <th style="width:180px">Status</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $i => $s)
                            @php $rec = $existing->get($s->id); @endphp
                            <tr>
                                <td class="text-center">{{ $s->pivot->nomor_urut_absen ?? ($i + 1) }}</td>
                                <td>
                                    <div class="font-weight-600">{{ $s->nama_lengkap }}</div>
                                    <small class="text-muted">NISN {{ $s->nisn ?: '—' }}</small>
                                </td>
                                <td>
                                    <select name="statuses[{{ $s->id }}]" class="form-control form-control-sm status-select" {{ $locked ? 'disabled' : '' }}>
                                        @foreach($statuses as $st)
                                            <option value="{{ $st }}" {{ ($rec->status ?? 'hadir') === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="notes[{{ $s->id }}]" value="{{ $rec->notes ?? '' }}" maxlength="500" class="form-control form-control-sm" placeholder="opsional" {{ $locked ? 'disabled' : '' }}>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @unless($locked)
                <div class="card-footer">
                    <div class="form-group">
                        <label class="font-weight-600">Catatan Sesi (opsional)</label>
                        <input type="text" name="session_notes" value="{{ $session->notes ?? '' }}" maxlength="1000" class="form-control">
                    </div>
                    @if($session && $session->status === 'final')
                        <div class="form-group">
                            <label class="font-weight-600 text-warning">Alasan Revisi <span class="text-danger">*</span></label>
                            <input type="text" name="revision_reason" maxlength="500" class="form-control" placeholder="Wajib diisi karena sesi sudah final">
                        </div>
                    @endif
                    <button type="submit" name="submit_action" value="draft" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Draft
                    </button>
                    <button type="submit" name="submit_action" value="final" class="btn btn-success" onclick="return confirm('Finalkan absensi? Sesi akan dikunci otomatis dalam 24 jam.')">
                        <i class="fas fa-lock"></i> Finalkan
                    </button>
                </div>
            @endunless
        </div>
    </form>
</div>
@stop

@section('css')
<style>
    .gtk-wali-absensi-page > .bg-gradient-primary { overflow:hidden; border:0; border-radius:16px; box-shadow:0 12px 28px rgba(15,23,42,.1); }
    .gtk-wali-absensi-page > .bg-gradient-primary .card-body { padding:1.2rem 1.25rem; }
    .gtk-wali-absensi-page > .bg-gradient-primary h3 { font-size:1.35rem; font-weight:700; }
    @media (max-width:575.98px) {
        .gtk-wali-absensi-page > .bg-gradient-primary .card-body { padding:1rem; }
        .gtk-wali-absensi-page > .bg-gradient-primary h3 { font-size:1.1rem; }
        .gtk-wali-absensi-page .form-inline label,
        .gtk-wali-absensi-page .form-inline .form-control { width:100%; margin-right:0 !important; margin-bottom:.5rem !important; }
        .gtk-wali-absensi-page .card-header.d-flex { align-items:stretch !important; flex-direction:column; gap:.65rem; }
    }
</style>
@stop

@section('js')
<script>
    $(function () {
        $('#btnHadirSemua').on('click', function () {
            $('.status-select').val('hadir');
        });
    });
</script>
@stop

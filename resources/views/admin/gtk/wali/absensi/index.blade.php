@extends('adminlte::page')

@section('title', 'Absensi Harian — Kelas Saya')
@section('plugins.Sweetalert2', true)

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
@php
    $statusOptions = [
        'hadir' => ['label' => 'Hadir', 'class' => 'success', 'icon' => 'fa-check-circle'],
        'terlambat' => ['label' => 'Terlambat', 'class' => 'warning', 'icon' => 'fa-clock'],
        'izin' => ['label' => 'Izin', 'class' => 'info', 'icon' => 'fa-envelope-open-text'],
        'sakit' => ['label' => 'Sakit', 'class' => 'primary', 'icon' => 'fa-notes-medical'],
        'alpa' => ['label' => 'Alpa', 'class' => 'danger', 'icon' => 'fa-times-circle'],
        'dispen' => ['label' => 'Dispen', 'class' => 'secondary', 'icon' => 'fa-id-badge'],
        'keluar_awal' => ['label' => 'Keluar Awal', 'class' => 'dark', 'icon' => 'fa-sign-out-alt'],
    ];
@endphp
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
        <div class="callout {{ $locked ? 'callout-info' : 'callout-warning' }}">
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
                            <th style="width:135px">Durasi</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $i => $s)
                            @php $rec = $existing->get($s->id); @endphp
                            <tr>
                                <td class="text-center" data-label="No">{{ $s->pivot->nomor_urut_absen ?? ($i + 1) }}</td>
                                <td>
                                    <div class="font-weight-600">{{ $s->nama_lengkap }}</div>
                                    <small class="text-muted">NISN {{ $s->nisn ?: '—' }}</small>
                                </td>
                                <td data-label="Status">
                                    <select name="statuses[{{ $s->id }}]" class="form-control form-control-sm status-select" {{ $locked ? 'disabled' : '' }}>
                                        @foreach($statuses as $st)
                                            <option value="{{ $st }}" {{ ($rec->status ?? 'hadir') === $st ? 'selected' : '' }}>{{ $statusOptions[$st]['label'] ?? ucfirst($st) }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td data-label="Durasi" class="duration-cell">
                                    <div class="duration-field late-field {{ ($rec->status ?? 'hadir') === 'terlambat' ? '' : 'd-none' }}">
                                        <input type="number" min="1" max="600" name="late_minutes[{{ $s->id }}]" value="{{ $rec->late_minutes ?? '' }}" class="form-control form-control-sm" placeholder="Menit" {{ $locked ? 'disabled' : '' }}>
                                        <small>menit terlambat</small>
                                    </div>
                                    <div class="duration-field early-field {{ ($rec->status ?? 'hadir') === 'keluar_awal' ? '' : 'd-none' }}">
                                        <input type="number" min="1" max="600" name="left_early_minutes[{{ $s->id }}]" value="{{ $rec->left_early_minutes ?? '' }}" class="form-control form-control-sm" placeholder="Menit" {{ $locked ? 'disabled' : '' }}>
                                        <small>menit lebih awal</small>
                                    </div>
                                    <span class="duration-empty {{ in_array($rec->status ?? 'hadir', ['terlambat', 'keluar_awal']) ? 'd-none' : '' }}">—</span>
                                </td>
                                <td data-label="Catatan">
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
                    <button type="button" class="btn btn-success" id="btnFinalkanAbsensi">
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
    .gtk-wali-absensi-page .attendance-table th,
    .gtk-wali-absensi-page .attendance-table td { vertical-align:middle; }
    .gtk-wali-absensi-page .attendance-table thead th { background:#f8fafc; color:#526078; font-size:.73rem; text-transform:uppercase; letter-spacing:.03em; }
    .gtk-wali-absensi-page .attendance-table tbody td { border-color:#edf1f6; }
    .gtk-wali-absensi-page .duration-field small { display:block; margin-top:.15rem; color:#7b8797; font-size:.66rem; }
    .gtk-wali-absensi-page .duration-cell { min-width:135px; }
    .gtk-wali-absensi-page .status-select { min-width:150px; }
    .gtk-wali-absensi-page .form-control:focus { border-color:#6688d8; box-shadow:0 0 0 .15rem rgba(79,110,247,.12); }
    @media (max-width:767.98px) {
        .gtk-wali-absensi-page .card-body.table-responsive { overflow:visible; }
        .gtk-wali-absensi-page .attendance-table,
        .gtk-wali-absensi-page .attendance-table tbody,
        .gtk-wali-absensi-page .attendance-table tr,
        .gtk-wali-absensi-page .attendance-table td { display:block; width:100%; }
        .gtk-wali-absensi-page .attendance-table { min-width:0; }
        .gtk-wali-absensi-page .attendance-table thead { position:absolute; width:1px; height:1px; overflow:hidden; clip:rect(0 0 0 0); }
        .gtk-wali-absensi-page .attendance-table tbody tr { margin:0; padding:.8rem .9rem; border-bottom:1px solid #e8edf4; }
        .gtk-wali-absensi-page .attendance-table tbody td { display:grid; grid-template-columns:90px minmax(0,1fr); gap:.65rem; align-items:center; padding:.35rem 0; border:0; text-align:left !important; }
        .gtk-wali-absensi-page .attendance-table tbody td::before { content:attr(data-label); color:#718096; font-size:.68rem; font-weight:800; text-transform:uppercase; letter-spacing:.03em; }
        .gtk-wali-absensi-page .attendance-table tbody td:nth-child(2) { display:block; padding:.15rem 0 .55rem 0; }
        .gtk-wali-absensi-page .attendance-table tbody td:nth-child(2)::before { display:none; }
        .gtk-wali-absensi-page .attendance-table .status-select,
        .gtk-wali-absensi-page .attendance-table .form-control { width:100%; min-width:0; }
        .gtk-wali-absensi-page .attendance-table .duration-cell { min-width:0; }
    }
</style>
@stop

@section('js')
<script>
    $(function () {
        var successMessage = @json(session('success'));
        var validationErrors = @json($errors->all());

        if (successMessage) {
            Swal.fire({ icon: 'success', title: 'Berhasil', text: successMessage, timer: 2200, showConfirmButton: false });
        }
        if (validationErrors.length) {
            Swal.fire({ icon: 'error', title: 'Data Belum Valid', text: validationErrors.join('\n'), confirmButtonText: 'Periksa Kembali' });
        }

        $('#btnHadirSemua').on('click', function () {
            $('.status-select').val('hadir');
            $('.late-field, .early-field').addClass('d-none');
            $('.duration-empty').removeClass('d-none');
        });

        $('.status-select').on('change', function () {
            var cell = $(this).closest('tr');
            var status = $(this).val();
            cell.find('.late-field').toggleClass('d-none', status !== 'terlambat');
            cell.find('.early-field').toggleClass('d-none', status !== 'keluar_awal');
            cell.find('.duration-empty').toggleClass('d-none', status === 'terlambat' || status === 'keluar_awal');
        });

        $('#btnFinalkanAbsensi').on('click', function () {
            Swal.fire({
                icon: 'warning',
                title: 'Finalkan absensi?',
                text: 'Sesi akan dikunci otomatis dalam 24 jam.',
                showCancelButton: true,
                confirmButtonText: 'Ya, Finalkan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#16a34a'
            }).then(function (result) {
                if (!result.isConfirmed) return;
                $('<input>', { type: 'hidden', name: 'submit_action', value: 'final' }).appendTo('#formAbsensi');
                document.getElementById('formAbsensi').requestSubmit();
            });
        });
    });
</script>
@stop

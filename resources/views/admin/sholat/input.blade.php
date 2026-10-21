@extends('adminlte::page')

@section('title', 'Input Presensi Sholat')

@section('content_header')
<div class="row align-items-center">
    <div class="col-sm-7">
        <h1 class="m-0"><i class="fas fa-clipboard-check text-primary mr-2"></i>Input Presensi Sholat Siswa</h1>
    </div>
    <div class="col-sm-5">
        <ol class="breadcrumb float-sm-right mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Input Presensi Sholat</li>
        </ol>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid pb-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Card Filter Pemilihan Kelas & Sholat -->
    <div class="card card-default shadow-sm mb-3">
        <div class="card-body">
            <form action="{{ route('admin.sholat.input') }}" method="GET" class="form-row align-items-end">
                <div class="form-group col-md-3 mb-2">
                    <label class="font-weight-bold"><i class="far fa-calendar-alt mr-1 text-primary"></i> Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="{{ $tanggal }}" required>
                </div>
                <div class="form-group col-md-3 mb-2">
                    <label class="font-weight-bold"><i class="fas fa-mosque mr-1 text-success"></i> Sholat / Agenda</label>
                    <select name="sholat_setting_id" class="form-control" required>
                        @foreach($activeSettings as $s)
                            <option value="{{ $s->id }}" {{ $selectedSetting && $selectedSetting->id === $s->id ? 'selected' : '' }}>
                                {{ $s->nama_sholat }} ({{ substr($s->jam_mulai, 0, 5) }} - {{ substr($s->jam_selesai, 0, 5) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4 mb-2">
                    <label class="font-weight-bold"><i class="fas fa-users mr-1 text-info"></i> Kelas / Rombel</label>
                    <select name="kelas_id" class="form-control select2" required>
                        @foreach($accessibleKelas as $k)
                            <option value="{{ $k->id }}" {{ $selectedKelas && $selectedKelas->id === $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-2 mb-2">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-search mr-1"></i> Tampilkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($selectedSetting && $selectedKelas)
    <form action="{{ route('admin.sholat.store') }}" method="POST">
        @csrf
        <input type="hidden" name="sholat_setting_id" value="{{ $selectedSetting->id }}">
        <input type="hidden" name="kelas_id" value="{{ $selectedKelas->id }}">
        <input type="hidden" name="tanggal" value="{{ $tanggal }}">

        <!-- Panel Status & Info Sesi -->
        <div class="card card-outline card-primary shadow-sm mb-3">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between py-2">
                <div>
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-users-class mr-1"></i> {{ $selectedKelas->nama_kelas }} &middot; Sholat {{ $selectedSetting->nama_sholat }}
                    </h3>
                    <div class="text-muted small mt-1">
                        <i class="far fa-calendar mr-1"></i>{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}
                        &bull; Waktu: {{ substr($selectedSetting->jam_mulai, 0, 5) }} - {{ substr($selectedSetting->jam_selesai, 0, 5) }} WIB
                    </div>
                </div>
                <div class="mt-2 mt-md-0">
                    @if($existingSession)
                        <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Sudah Tersimpan</span>
                        <small class="text-muted ml-2">Pencatat: {{ $existingSession->petugasUser->name ?? '-' }} ({{ $existingSession->updated_at->diffForHumans() }})</small>
                    @else
                        <span class="badge badge-warning px-2 py-1"><i class="fas fa-hourglass-half mr-1"></i>Belum Diinput (Draft)</span>
                    @endif
                </div>
            </div>

            <!-- Quick Batch Buttons Toolbar -->
            <div class="card-body bg-light py-2 border-bottom">
                <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap: .5rem;">
                    <div class="d-flex flex-wrap align-items-center" style="gap: .35rem;">
                        <span class="text-muted font-weight-bold small mr-2">Set Cepat Satu Kelas:</span>
                        <button type="button" class="btn btn-sm btn-outline-success font-weight-bold" id="btnSetAllBerjamaah">
                            <i class="fas fa-check-double mr-1"></i> Semua Berjamaah
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning font-weight-bold" id="btnSetAllMasbuq">
                            <i class="fas fa-running mr-1"></i> Semua Masbuq
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnResetDefault">
                            <i class="fas fa-undo mr-1"></i> Reset Checklist
                        </button>
                    </div>
                    <div>
                        <span class="badge badge-info py-1 px-2 font-weight-bold">
                            Total: {{ count($studentRows) }} Siswa
                        </span>
                    </div>
                </div>
            </div>

            <!-- Checklist Table -->
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="thead-dark text-nowrap">
                        <tr>
                            <th style="width: 40px;" class="text-center">No</th>
                            <th style="width: 140px;">NISN / NIS</th>
                            <th>Nama Siswa</th>
                            <th style="width: 50px;" class="text-center">L/P</th>
                            <th style="min-width: 480px;" class="text-center">Status Kehadiran Sholat</th>
                            <th style="min-width: 180px;">Catatan (Opsional)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($studentRows as $idx => $row)
                        @php
                            $siswa = $row['siswa'];
                            $currentStatus = $row['status'];
                            $hasUdzur = $row['has_udzur'];
                        @endphp
                        <tr class="{{ $hasUdzur ? 'table-warning' : '' }}">
                            <td class="text-center font-weight-bold">{{ $idx + 1 }}</td>
                            <td>
                                <code>{{ $siswa->nisn ?? $siswa->nis_lokal ?? '-' }}</code>
                            </td>
                            <td>
                                <strong class="text-dark">{{ $siswa->nama_lengkap }}</strong>
                                @if($hasUdzur)
                                    <div>
                                        <span class="badge badge-warning text-dark font-weight-bold">
                                            <i class="fas fa-female mr-1"></i>Udzur Syar'i (Klaim Mandiri)
                                        </span>
                                    </div>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($siswa->jenis_kelamin === 'L')
                                    <span class="badge badge-primary">L</span>
                                @else
                                    <span class="badge badge-danger">P</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-toggle d-flex flex-wrap justify-content-center" data-toggle="buttons" style="gap: 2px;">
                                    {{-- Berjamaah --}}
                                    <label class="btn btn-xs btn-outline-success {{ $currentStatus === 'berjamaah' ? 'active font-weight-bold' : '' }}">
                                        <input type="radio" name="records[{{ $siswa->id }}][status]" value="berjamaah" {{ $currentStatus === 'berjamaah' ? 'checked' : '' }} class="status-radio radio-berjamaah" data-siswa="{{ $siswa->id }}" data-gender="{{ $siswa->jenis_kelamin }}" data-udzur="{{ $hasUdzur ? '1' : '0' }}">
                                        Berjamaah
                                    </label>
                                    {{-- Masbuq --}}
                                    <label class="btn btn-xs btn-outline-warning text-dark {{ $currentStatus === 'masbuq' ? 'active font-weight-bold' : '' }}">
                                        <input type="radio" name="records[{{ $siswa->id }}][status]" value="masbuq" {{ $currentStatus === 'masbuq' ? 'checked' : '' }} class="status-radio radio-masbuq" data-siswa="{{ $siswa->id }}" data-gender="{{ $siswa->jenis_kelamin }}" data-udzur="{{ $hasUdzur ? '1' : '0' }}">
                                        Masbuq
                                    </label>
                                    {{-- Munfarid --}}
                                    <label class="btn btn-xs btn-outline-info {{ $currentStatus === 'munfarid' ? 'active font-weight-bold' : '' }}">
                                        <input type="radio" name="records[{{ $siswa->id }}][status]" value="munfarid" {{ $currentStatus === 'munfarid' ? 'checked' : '' }} class="status-radio radio-munfarid" data-siswa="{{ $siswa->id }}" data-gender="{{ $siswa->jenis_kelamin }}" data-udzur="{{ $hasUdzur ? '1' : '0' }}">
                                        Munfarid
                                    </label>
                                    {{-- Udzur (khusus putri atau jika ada udzur) --}}
                                    <label class="btn btn-xs btn-outline-secondary {{ $currentStatus === 'udzur' ? 'active font-weight-bold' : '' }}">
                                        <input type="radio" name="records[{{ $siswa->id }}][status]" value="udzur" {{ $currentStatus === 'udzur' ? 'checked' : '' }} class="status-radio radio-udzur" data-siswa="{{ $siswa->id }}" data-gender="{{ $siswa->jenis_kelamin }}" data-udzur="{{ $hasUdzur ? '1' : '0' }}">
                                        Udzur
                                    </label>
                                    {{-- Sakit --}}
                                    <label class="btn btn-xs btn-outline-primary {{ $currentStatus === 'sakit' ? 'active font-weight-bold' : '' }}">
                                        <input type="radio" name="records[{{ $siswa->id }}][status]" value="sakit" {{ $currentStatus === 'sakit' ? 'checked' : '' }} class="status-radio" data-siswa="{{ $siswa->id }}">
                                        Sakit
                                    </label>
                                    {{-- Izin --}}
                                    <label class="btn btn-xs btn-outline-dark {{ $currentStatus === 'izin' ? 'active font-weight-bold' : '' }}">
                                        <input type="radio" name="records[{{ $siswa->id }}][status]" value="izin" {{ $currentStatus === 'izin' ? 'checked' : '' }} class="status-radio" data-siswa="{{ $siswa->id }}">
                                        Izin
                                    </label>
                                    {{-- Alpa --}}
                                    <label class="btn btn-xs btn-outline-danger {{ $currentStatus === 'alpa' ? 'active font-weight-bold' : '' }}">
                                        <input type="radio" name="records[{{ $siswa->id }}][status]" value="alpa" {{ $currentStatus === 'alpa' ? 'checked' : '' }} class="status-radio" data-siswa="{{ $siswa->id }}">
                                        Alpa
                                    </label>
                                </div>
                            </td>
                            <td>
                                <input type="text" name="records[{{ $siswa->id }}][catatan]" class="form-control form-control-sm" placeholder="Catatan..." value="{{ $row['catatan'] }}">
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                Tidak ada siswa terdaftar di rombel ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Footer Card with Save Button -->
            <div class="card-footer bg-white text-right py-3">
                <button type="submit" class="btn btn-success btn-lg px-4 shadow">
                    <i class="fas fa-save mr-1"></i> Simpan Data Presensi Sholat
                </button>
            </div>
        </div>
    </form>
    @else
    <div class="card card-outline card-secondary shadow-sm">
        <div class="card-body text-center py-5 text-muted">
            <i class="fas fa-info-circle fa-3x mb-3 text-secondary"></i>
            <h5>Pilih tanggal, sholat, dan kelas untuk membuka lembar presensi.</h5>
        </div>
    </div>
    @endif
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    if ($.fn.select2) {
        $('.select2').select2({ theme: 'bootstrap4' });
    }

    // Set semua siswa ke 'Berjamaah' kecuali yang sedang Udzur Syar'i
    $('#btnSetAllBerjamaah').on('click', function() {
        $('tbody tr').each(function() {
            const isUdzur = $(this).find('.radio-berjamaah').data('udzur') === 1 || $(this).find('.radio-berjamaah').data('udzur') === '1';
            if (isUdzur) {
                // Biarkan udzur
                const $radioUdzur = $(this).find('.radio-udzur');
                $radioUdzur.prop('checked', true).trigger('change');
                $(this).find('.btn-group-toggle label').removeClass('active font-weight-bold');
                $radioUdzur.closest('label').addClass('active font-weight-bold');
            } else {
                const $radio = $(this).find('.radio-berjamaah');
                $radio.prop('checked', true).trigger('change');
                $(this).find('.btn-group-toggle label').removeClass('active font-weight-bold');
                $radio.closest('label').addClass('active font-weight-bold');
            }
        });
    });

    // Set semua siswa ke 'Masbuq' kecuali yang sedang Udzur
    $('#btnSetAllMasbuq').on('click', function() {
        $('tbody tr').each(function() {
            const isUdzur = $(this).find('.radio-masbuq').data('udzur') === 1 || $(this).find('.radio-masbuq').data('udzur') === '1';
            if (isUdzur) {
                const $radioUdzur = $(this).find('.radio-udzur');
                $radioUdzur.prop('checked', true).trigger('change');
                $(this).find('.btn-group-toggle label').removeClass('active font-weight-bold');
                $radioUdzur.closest('label').addClass('active font-weight-bold');
            } else {
                const $radio = $(this).find('.radio-masbuq');
                $radio.prop('checked', true).trigger('change');
                $(this).find('.btn-group-toggle label').removeClass('active font-weight-bold');
                $radio.closest('label').addClass('active font-weight-bold');
            }
        });
    });

    // Reset default
    $('#btnResetDefault').on('click', function() {
        $('#btnSetAllBerjamaah').trigger('click');
    });

    // Update styling on radio change
    $('input.status-radio').on('change', function() {
        $(this).closest('.btn-group-toggle').find('label').removeClass('active font-weight-bold');
        $(this).closest('label').addClass('active font-weight-bold');
    });
});
</script>
@stop

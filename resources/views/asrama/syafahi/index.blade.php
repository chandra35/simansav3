@extends('adminlte::page')

@section('title', 'Penilaian Ujian Syafahi (Lisan)')

@section('plugins.Select2', true)
@section('plugins.Sweetalert2', true)

@section('content')
@include('asrama._alerts')

@php
    $heroTitle = 'Penilaian Ujian Syafahi (Lisan)';
    $heroDescription = 'Input dan kelola nilai ujian lisan asrama (Bahasa Arab, Bahasa Inggris, Fiqh & Al-Qur\'an) secara langsung saat ujian berlangsung atau cetak blangko kelompok.';
@endphp
@include('asrama._hero')

<!-- Filter Panel -->
<div class="asrama-panel mb-3">
    <div class="asrama-panel__header">
        <div>
            <h3><i class="fas fa-filter mr-1"></i> Filter Ujian Syafahi</h3>
            <p>Pilih kelas, semester, dan kelompok ujian untuk memulai penilaian langsung atau mencetak blangko.</p>
        </div>
    </div>
    <div class="asrama-panel__body asrama-form">
        <form method="get" class="row align-items-end" id="filterForm">
            <div class="col-md-3 mb-2">
                <label>Tahun Pelajaran</label>
                <select name="tahun_pelajaran_id" class="form-control asrama-select" onchange="this.form.submit()">
                    @foreach($years as $yr)
                        <option value="{{ $yr->id }}" @selected($tahunId == $yr->id)>
                            {{ $yr->nama }}
                            @if($activeYear && $activeYear->id === $yr->id) ★ [Aktif] @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 mb-2">
                <label>Kelas Asrama</label>
                <select name="asrama_kelas_id" class="form-control asrama-select" onchange="this.form.submit()">
                    @forelse($classes as $item)
                        <option value="{{ $item->id }}" @selected($selectedKelas?->id === $item->id)>
                            {{ $item->asrama->nama }} · {{ $item->nama_kelas }}
                            @if($item->is_tahfidz) [Tahfizh] @else [Reguler] @endif
                        </option>
                    @empty
                        <option value="">(Belum ada kelas asrama)</option>
                    @endforelse
                </select>
            </div>
            <div class="col-md-2 col-6 mb-2">
                <label>Semester</label>
                <select name="semester" class="form-control" onchange="this.form.submit()">
                    <option @selected($semester === 'Ganjil')>Ganjil</option>
                    <option @selected($semester === 'Genap')>Genap</option>
                </select>
            </div>
            <div class="col-md-3 col-6 mb-2">
                <button type="submit" class="btn btn-info btn-block">
                    <i class="fas fa-sync-alt mr-1"></i> Muat Data
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Group Nav Tabs -->
<div class="card card-primary card-outline card-outline-tabs mb-3">
    <div class="card-header p-0 border-bottom-0">
        <ul class="nav nav-tabs" id="syafahiTabs" role="tablist">
            @foreach($groups as $k => $grp)
                <li class="nav-item">
                    <a class="nav-link @if($kelompok === $k) active font-weight-bold @endif"
                       href="{{ request()->fullUrlWithQuery(['kelompok' => $k]) }}">
                        <i class="{{ $grp['icon'] }} mr-1"></i> {{ $grp['label'] }}
                        <small class="badge badge-{{ $grp['color'] }} ml-1">{{ $grp['label_arab'] }}</small>
                    </a>
                </li>
            @endforeach
            <li class="nav-item">
                <a class="nav-link @if($kelompok === 'all') active font-weight-bold @endif"
                   href="{{ request()->fullUrlWithQuery(['kelompok' => 'all']) }}">
                    <i class="fas fa-th-list mr-1"></i> Semua Mapel Syafahi
                </a>
            </li>
        </ul>
    </div>
</div>

@if(!$selectedKelas)
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle mr-1"></i> Silakan pilih kelas asrama untuk memulai pengisian nilai syafahi.
    </div>
@else

    @if($isPublished)
        <div class="alert alert-danger mb-3">
            <i class="fas fa-lock mr-1"></i> <strong>Rapor Kelas Terkunci:</strong> Rapor pada semester ini sudah diterbitkan. Nilai syafahi tidak dapat diubah kembali kecuali penerbitan rapor dibatalkan terlebih dahulu oleh admin.
        </div>
    @endif

    <!-- Action Bar & Stats -->
    <div class="row align-items-center mb-3">
        <div class="col-md-6 mb-2">
            <h4 class="m-0 text-dark font-weight-bold">
                {{ $selectedKelas->nama_kelas }}
                <span class="badge @if($selectedKelas->is_tahfidz) badge-success @else badge-secondary @endif ml-1">
                    {{ $selectedKelas->is_tahfidz ? 'Program Tahfizh' : 'Program Reguler' }}
                </span>
                <span class="badge badge-light border ml-1">{{ $selectedKelas->asrama->nama }}</span>
            </h4>
            <small class="text-muted">
                Jumlah Santri: <strong>{{ $members->count() }}</strong> orang · Semester <strong>{{ $semester }}</strong>
            </small>
        </div>
        <div class="col-md-6 mb-2 text-md-right">
            <div class="btn-group" role="group">
                <a href="{{ route('asrama.syafahi.blanko', ['asrama_kelas_id' => $selectedKelas->id, 'semester' => $semester, 'kelompok' => $kelompok, 'mode' => 'empty']) }}"
                   target="_blank" class="btn btn-outline-secondary btn-sm font-weight-bold">
                    <i class="fas fa-print mr-1"></i> Cetak Blangko Kosong
                </a>
                <a href="{{ route('asrama.syafahi.blanko', ['asrama_kelas_id' => $selectedKelas->id, 'semester' => $semester, 'kelompok' => $kelompok, 'mode' => 'filled']) }}"
                   target="_blank" class="btn btn-outline-info btn-sm font-weight-bold">
                    <i class="fas fa-file-pdf mr-1"></i> Cetak Hasil Nilai
                </a>
            </div>
            @if(!$isPublished)
                <button type="submit" form="syafahiForm" class="btn btn-success btn-sm font-weight-bold ml-1">
                    <i class="fas fa-save mr-1"></i> Simpan Semua
                </button>
            @endif
        </div>
    </div>

    <!-- Live Scoring Table -->
    <form id="syafahiForm" method="post" action="{{ route('asrama.syafahi.store') }}">
        @csrf
        <input type="hidden" name="asrama_kelas_id" value="{{ $selectedKelas->id }}">
        <input type="hidden" name="semester" value="{{ $semester }}">
        <input type="hidden" name="kelompok" value="{{ $kelompok }}">

        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center py-2">
                <h5 class="card-title font-weight-bold m-0">
                    <i class="fas fa-pen-alt mr-1 text-primary"></i>
                    Lembar Penilaian Langsung:
                    @if($kelompok !== 'all' && isset($groups[$kelompok]))
                        {{ $groups[$kelompok]['label'] }} ({{ $groups[$kelompok]['label_arab'] }})
                    @else
                        Semua Aspek Ujian Syafahi
                    @endif
                </h5>
                <span class="badge badge-success" id="autoSaveIndicator" style="display:none;">
                    <i class="fas fa-check-circle mr-1"></i> Tersimpan otomatis
                </span>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover mb-0 text-center align-middle" id="liveScoreTable">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 45px;">No</th>
                            <th class="text-left" style="min-width: 180px;">Nama Santri</th>
                            <th style="width: 100px;">NISN / Induk</th>

                            @foreach($aspectCodes as $kode)
                                @php
                                    $mapel = $mapels->get($kode);
                                    // Custom title for Tahfiz vs Non-Tahfiz on ASR-023
                                    $colTitleArab = $mapel?->nama_arab ?? $kode;
                                    $colTitleLatin = $mapel?->nama_latin ?? $kode;
                                    if ($kode === 'ASR-023') {
                                        $colTitleArab = $selectedKelas->is_tahfidz ? 'تحسين القرآن' : 'الترجمة';
                                        $colTitleLatin = $selectedKelas->is_tahfidz ? 'Tahsin Al-Qur\'an' : 'Tarjamah';
                                    }
                                @endphp
                                <th style="min-width: 120px;">
                                    <div class="font-weight-bold" style="font-size: 15px; direction: rtl;">{{ $colTitleArab }}</div>
                                    <div class="text-muted small">{{ $colTitleLatin }}</div>
                                    <span class="badge badge-light border">Skala 0-10</span>
                                </th>
                            @endforeach

                            <th style="width: 85px;" class="bg-secondary text-white">Rata-rata</th>
                            <th style="width: 70px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($members as $index => $member)
                            @php
                                $memberScores = $scores[$member->id] ?? [];
                                $rowValues = [];
                            @endphp
                            <tr data-member-id="{{ $member->id }}">
                                <td>{{ $index + 1 }}</td>
                                <td class="text-left">
                                    <strong>{{ $member->santri->siswa->nama_lengkap ?? '-' }}</strong>
                                    @if($member->santri->kamarSantriAktif?->kamar)
                                        <div class="text-muted small">
                                            <i class="fas fa-door-open mr-1"></i> {{ $member->santri->kamarSantriAktif->kamar->nama }}
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $member->santri->nomor_induk_asrama ?: ($member->santri->siswa->nisn ?: '-') }}</td>

                                @foreach($aspectCodes as $kode)
                                    @php
                                        $pengampu = $pengampuList[$kode] ?? null;
                                        $currentVal = null;
                                        if ($pengampu && isset($memberScores[$pengampu->id])) {
                                            $currentVal = $memberScores[$pengampu->id]->nilai;
                                            if ($currentVal !== null) {
                                                $rowValues[] = (float) $currentVal;
                                            }
                                        }
                                    @endphp
                                    <td class="p-1 position-relative">
                                        <input type="text"
                                               name="nilai[{{ $member->id }}][{{ $kode }}]"
                                               value="{{ $currentVal !== null ? (float) $currentVal : '' }}"
                                               class="form-control form-control-sm text-center font-weight-bold score-input"
                                               placeholder="0 - 10"
                                               data-member-id="{{ $member->id }}"
                                               data-kode="{{ $kode }}"
                                               data-original="{{ $currentVal !== null ? (float) $currentVal : '' }}"
                                               @if($isPublished) disabled @endif
                                               autocomplete="off">
                                        <span class="save-status-icon text-success position-absolute" style="right: 5px; top: 10px; display: none;">
                                            <i class="fas fa-check"></i>
                                        </span>
                                    </td>
                                @endforeach

                                @php
                                    $avg = count($rowValues) > 0 ? round(array_sum($rowValues) / count($rowValues), 2) : null;
                                @endphp
                                <td class="font-weight-bold bg-light row-avg">
                                    {{ $avg !== null ? number_format($avg, 1, ',', '.') : '-' }}
                                </td>
                                <td class="row-status">
                                    @if(count($rowValues) === count($aspectCodes) && count($aspectCodes) > 0)
                                        <span class="badge badge-success"><i class="fas fa-check"></i> Lengkap</span>
                                    @elseif(count($rowValues) > 0)
                                        <span class="badge badge-warning">{{ count($rowValues) }}/{{ count($aspectCodes) }}</span>
                                    @else
                                        <span class="badge badge-light border text-muted">Kosong</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($aspectCodes) + 5 }}" class="text-center py-4 text-muted">
                                    <i class="fas fa-users-slash fa-2x mb-2 d-block"></i>
                                    Belum ada santri aktif yang terdaftar di kelas asrama ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-light d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    <i class="fas fa-info-circle mr-1"></i> Nilai otomatis tersimpan saat Anda berpindah kotak (blur / enter). Gunakan tombol panah bawah atau Enter untuk pindah santri.
                </small>
                @if(!$isPublished && $members->isNotEmpty())
                    <button type="submit" class="btn btn-success font-weight-bold">
                        <i class="fas fa-save mr-1"></i> Simpan Semua Perubahan
                    </button>
                @endif
            </div>
        </div>
    </form>

@endif

@stop

@section('js')
<script>
$(document).ready(function() {
    $('.asrama-select').select2({ theme: 'bootstrap4' });

    var quickSaveUrl = "{{ route('asrama.syafahi.quick-save') }}";
    var csrfToken = "{{ csrf_token() }}";
    var kelasId = "{{ $selectedKelas?->id }}";
    var semester = "{{ $semester }}";

    // Auto-save on blur or change
    $('.score-input').on('change', function() {
        var $input = $(this);
        var originalVal = $input.data('original');
        var newVal = $input.val().trim();

        if (originalVal === newVal) {
            return; // No change
        }

        var memberId = $input.data('member-id');
        var kode = $input.data('kode');
        var $cell = $input.closest('td');
        var $icon = $cell.find('.save-status-icon');

        $input.addClass('border-warning');

        $.ajax({
            url: quickSaveUrl,
            method: 'POST',
            data: {
                _token: csrfToken,
                asrama_kelas_id: kelasId,
                asrama_kelas_santri_id: memberId,
                kode_mapel: kode,
                semester: semester,
                nilai: newVal
            },
            success: function(res) {
                $input.removeClass('border-warning').addClass('border-success');
                $input.data('original', newVal);
                $icon.fadeIn(200).delay(1500).fadeOut(400);

                setTimeout(function() {
                    $input.removeClass('border-success');
                }, 2000);

                $('#autoSaveIndicator').fadeIn(150).delay(1200).fadeOut(300);

                // Recalculate row average and status
                updateRowStats($input.closest('tr'));
            },
            error: function(xhr) {
                $input.removeClass('border-warning').addClass('border-danger');
                var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menyimpan nilai.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menyimpan',
                        text: msg,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                } else {
                    alert(msg);
                }
            }
        });
    });

    // Enter / Arrow down to navigate to next row in same column
    $('.score-input').on('keydown', function(e) {
        if (e.which === 13 || e.which === 40) { // Enter or Down Arrow
            e.preventDefault();
            var $this = $(this);
            var colIndex = $this.closest('td').index();
            var $nextRow = $this.closest('tr').next('tr');
            if ($nextRow.length) {
                var $target = $nextRow.children().eq(colIndex).find('.score-input');
                if ($target.length) {
                    $this.trigger('change');
                    $target.focus().select();
                }
            }
        } else if (e.which === 38) { // Up Arrow
            e.preventDefault();
            var $this = $(this);
            var colIndex = $this.closest('td').index();
            var $prevRow = $this.closest('tr').prev('tr');
            if ($prevRow.length) {
                var $target = $prevRow.children().eq(colIndex).find('.score-input');
                if ($target.length) {
                    $this.trigger('change');
                    $target.focus().select();
                }
            }
        }
    });

    function updateRowStats($tr) {
        var scores = [];
        var totalAspects = 0;
        $tr.find('.score-input').each(function() {
            totalAspects++;
            var v = $(this).val().trim().replace(',', '.');
            if (v !== '' && !isNaN(v)) {
                scores.push(parseFloat(v));
            }
        });

        var $avgCell = $tr.find('.row-avg');
        var $statusCell = $tr.find('.row-status');

        if (scores.length > 0) {
            var sum = scores.reduce(function(a, b) { return a + b; }, 0);
            var avg = (sum / scores.length).toFixed(1).replace('.', ',');
            $avgCell.text(avg);
        } else {
            $avgCell.text('-');
        }

        if (scores.length === totalAspects && totalAspects > 0) {
            $statusCell.html('<span class="badge badge-success"><i class="fas fa-check"></i> Lengkap</span>');
        } else if (scores.length > 0) {
            $statusCell.html('<span class="badge badge-warning">' + scores.length + '/' + totalAspects + '</span>');
        } else {
            $statusCell.html('<span class="badge badge-light border text-muted">Kosong</span>');
        }
    }
});
</script>
@stop

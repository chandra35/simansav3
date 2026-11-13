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
            <h3><i class="fas fa-filter mr-1 text-teal"></i> Filter Ujian Syafahi</h3>
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
                <button type="submit" class="btn btn-info btn-block font-weight-bold">
                    <i class="fas fa-sync-alt mr-1"></i> Muat Data
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Kelompok Tabs -->
<div class="card card-primary card-outline card-outline-tabs mb-3 shadow-sm">
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
                    <i class="fas fa-th-list mr-1"></i> Semua Aspek Syafahi
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
        <div class="alert alert-danger mb-3 shadow-sm border-left" style="border-left-width: 5px !important;">
            <i class="fas fa-lock mr-2"></i> <strong>Rapor Kelas Terkunci:</strong> Rapor pada semester ini sudah diterbitkan. Nilai syafahi tidak dapat diubah kembali kecuali penerbitan rapor dibatalkan terlebih dahulu oleh admin.
        </div>
    @endif

    <!-- SMART STATS KPI RIBBON -->
    <div class="row mb-3" id="kpiRibbon">
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="asrama-stat asrama-stat--success">
                <span>Progress Penilaian</span>
                <div class="d-flex align-items-baseline justify-content-between">
                    <strong id="kpiProgressText">0 / {{ $members->count() }}</strong>
                    <span class="badge badge-success font-weight-bold" id="kpiProgressPercent">0%</span>
                </div>
                <div class="progress mt-2" style="height: 6px;">
                    <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" id="kpiProgressBar" style="width: 0%;"></div>
                </div>
                <small class="text-muted mt-1 d-block" id="kpiProgressSub">Memuat status santri...</small>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="asrama-stat asrama-stat--info">
                <span>Rata-Rata Kelas Saat Ini</span>
                <strong id="kpiClassAvg">-</strong>
                <small class="text-muted d-flex align-items-center justify-content-between">
                    <span>Predikat Umum:</span>
                    <span class="badge badge-info" id="kpiClassPred">-</span>
                </small>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="asrama-stat asrama-stat--primary">
                <span>Nilai Tertinggi & Terendah</span>
                <div class="d-flex align-items-baseline gap-2">
                    <span class="text-success font-weight-bold" style="font-size: 1.5rem;" id="kpiMaxScore">-</span>
                    <span class="text-muted mx-1">/</span>
                    <span class="text-danger font-weight-bold" style="font-size: 1.5rem;" id="kpiMinScore">-</span>
                </div>
                <small class="text-muted d-block">Skala penilaian baku 0 - 10</small>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="asrama-stat asrama-stat--warning">
                <span>Distribusi Capaian</span>
                <div class="d-flex flex-wrap gap-1 mt-1">
                    <span class="badge badge-success px-2 py-1 filter-badge" data-filter-pred="mumtaz" role="button" title="Nilai >= 8.5">
                        Mumtaz: <strong id="kpiCountMumtaz">0</strong>
                    </span>
                    <span class="badge badge-info px-2 py-1 filter-badge" data-filter-pred="jayyid_jiddan" role="button" title="Nilai 7.0 - 8.4">
                        J. Jiddan: <strong id="kpiCountJayyidJiddan">0</strong>
                    </span>
                    <span class="badge badge-warning text-dark px-2 py-1 filter-badge" data-filter-pred="jayyid" role="button" title="Nilai 6.0 - 6.9">
                        Jayyid: <strong id="kpiCountJayyid">0</strong>
                    </span>
                    <span class="badge badge-danger px-2 py-1 filter-badge" data-filter-pred="maqbul" role="button" title="Nilai < 6.0">
                        Maqbul: <strong id="kpiCountMaqbul">0</strong>
                    </span>
                </div>
                <small class="text-muted mt-1 d-block">Klik predikat untuk filter santri</small>
            </div>
        </div>
    </div>

    <!-- SMART TOOLBAR -->
    <div class="card card-outline card-primary shadow-sm mb-3">
        <div class="card-body p-3">
            <div class="row align-items-center">
                <!-- Search Box -->
                <div class="col-lg-4 col-md-5 mb-2 mb-md-0">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="text" id="santriSearchInput" class="form-control border-left-0 pl-0" placeholder="Cari santri, NISN, kamar...">
                        <div class="input-group-append" id="clearSearchBtn" style="display:none;">
                            <button class="btn btn-outline-secondary border-left-0" type="button"><i class="fas fa-times"></i></button>
                        </div>
                    </div>
                </div>

                <!-- Status Filter Pills -->
                <div class="col-lg-5 col-md-7 mb-2 mb-md-0">
                    <div class="btn-group btn-group-sm w-100" role="group" id="filterStatusGroup">
                        <button type="button" class="btn btn-outline-primary active font-weight-bold" data-status="all">
                            Semua (<span id="countFilterAll">{{ $members->count() }}</span>)
                        </button>
                        <button type="button" class="btn btn-outline-success font-weight-bold" data-status="lengkap">
                            <i class="fas fa-check-circle mr-1"></i>Lengkap (<span id="countFilterLengkap">0</span>)
                        </button>
                        <button type="button" class="btn btn-outline-warning font-weight-bold" data-status="belum_lengkap">
                            <i class="fas fa-clock mr-1"></i>Belum Lengkap (<span id="countFilterBelum">0</span>)
                        </button>
                        <button type="button" class="btn btn-outline-secondary font-weight-bold" data-status="kosong">
                            <i class="fas fa-minus-circle mr-1"></i>Kosong (<span id="countFilterKosong">0</span>)
                        </button>
                    </div>
                </div>

                <!-- View Switcher & Action Tools -->
                <div class="col-lg-3 col-md-12 text-md-right mt-2 mt-lg-0">
                    <div class="btn-group btn-group-sm mr-1" role="group">
                        <button type="button" class="btn btn-info active" id="btnViewTable" title="Tampilan Tabel Grid">
                            <i class="fas fa-table mr-1"></i> Grid
                        </button>
                        <button type="button" class="btn btn-outline-info" id="btnViewCard" title="Tampilan Kartu Penguji (Touch/HP)">
                            <i class="fas fa-address-card mr-1"></i> Kartu
                        </button>
                    </div>

                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-cog mr-1"></i> Aksi
                        </button>
                        <div class="dropdown-menu dropdown-menu-right shadow">
                            <h6 class="dropdown-header">Cetak Dokumen Syafahi</h6>
                            <a class="dropdown-item" href="{{ route('asrama.syafahi.blanko', ['asrama_kelas_id' => $selectedKelas->id, 'semester' => $semester, 'kelompok' => $kelompok, 'mode' => 'empty']) }}" target="_blank">
                                <i class="fas fa-print text-muted mr-2"></i> Cetak Blangko Kosong
                            </a>
                            <a class="dropdown-item" href="{{ route('asrama.syafahi.blanko', ['asrama_kelas_id' => $selectedKelas->id, 'semester' => $semester, 'kelompok' => $kelompok, 'mode' => 'filled']) }}" target="_blank">
                                <i class="fas fa-file-pdf text-danger mr-2"></i> Cetak Lembar Nilai Terisi
                            </a>
                            @if(!$isPublished)
                                <div class="dropdown-divider"></div>
                                <h6 class="dropdown-header">Bantuan Cepat</h6>
                                <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#bulkFillModal">
                                    <i class="fas fa-magic text-warning mr-2"></i> Isi Cepat (Bulk Fill)
                                </a>
                            @endif
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#keyboardHelpModal">
                                <i class="fas fa-keyboard text-info mr-2"></i> Panduan Shortcut Keyboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top small text-muted">
                <span id="filterResultInfo">Menampilkan <strong>{{ $members->count() }}</strong> santri</span>
                <span>
                    <i class="fas fa-info-circle mr-1 text-teal"></i>
                    Nilai otomatis disimpan saat berpindah kotak (blur / enter).
                </span>
            </div>
        </div>
    </div>

    <!-- MAIN SCORING INTERFACE (DUAL MODE) -->
    <form id="syafahiForm" method="post" action="{{ route('asrama.syafahi.store') }}">
        @csrf
        <input type="hidden" name="asrama_kelas_id" value="{{ $selectedKelas->id }}">
        <input type="hidden" name="semester" value="{{ $semester }}">
        <input type="hidden" name="kelompok" value="{{ $kelompok }}">

        <!-- MODE 1: RESPONSIVE TABLE GRID WITH STICKY COLUMNS -->
        <div id="tableContainer" class="card card-outline card-teal shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center py-2 bg-light">
                <div class="font-weight-bold text-dark">
                    <i class="fas fa-pen-nib mr-1 text-teal"></i>
                    Lembar Penilaian:
                    @if($kelompok !== 'all' && isset($groups[$kelompok]))
                        {{ $groups[$kelompok]['label'] }} <span class="badge badge-light border ml-1">{{ $groups[$kelompok]['label_arab'] }}</span>
                    @else
                        Semua Aspek Ujian Syafahi
                    @endif
                </div>
                <div class="text-muted small">
                    Skala: <strong>0 - 10</strong> (Dapat menggunakan koma / desimal, cth: <code>8,5</code> atau <code>7.75</code>)
                </div>
            </div>

            <div class="table-responsive asrama-table-sticky" style="max-height: 75vh; overflow-y: auto;">
                <table class="table table-bordered table-hover mb-0 text-center align-middle" id="liveScoreTable">
                    <thead class="bg-light sticky-top" style="z-index: 20;">
                        <tr>
                            <th class="sticky-col-first" style="width: 45px;">No</th>
                            <th class="sticky-col-second text-left" style="min-width: 200px;">
                                Nama Santri
                                <div class="text-muted" style="font-size: 11px; font-weight: normal;">Kamar & NISN</div>
                            </th>

                            @foreach($aspectCodes as $kode)
                                @php
                                    $mapel = $mapels->get($kode);
                                    $colTitleArab = $mapel?->nama_arab ?? $kode;
                                    $colTitleLatin = $mapel?->nama_latin ?? $kode;
                                    if ($kode === 'ASR-023') {
                                        $colTitleArab = $selectedKelas->is_tahfidz ? 'تحسين القرآن' : 'الترجمة';
                                        $colTitleLatin = $selectedKelas->is_tahfidz ? 'Tahsin Al-Qur\'an' : 'Tarjamah';
                                    }
                                    $pengampuItem = $pengampuList[$kode] ?? null;
                                @endphp
                                <th style="min-width: 135px;" class="col-aspect" data-aspect-kode="{{ $kode }}">
                                    <div class="asrama-arab text-teal font-weight-bold" style="font-size: 1.15rem;">{{ $colTitleArab }}</div>
                                    <div class="font-weight-bold text-dark" style="font-size: 12px;">{{ $colTitleLatin }}</div>
                                    @if($pengampuItem)
                                        <div class="text-muted" style="font-size: 10px;" title="Pengampu: {{ $pengampuItem->asatidz->gtk->nama_lengkap ?? '-' }}">
                                            <i class="fas fa-user-check text-info"></i> {{ Str::limit($pengampuItem->asatidz->gtk->nama_lengkap ?? '', 16) }}
                                        </div>
                                    @endif
                                </th>
                            @endforeach

                            <th style="width: 95px;" class="bg-light text-dark font-weight-bold">
                                Rata-rata
                                <div class="text-muted" style="font-size: 10px;">Predikat</div>
                            </th>
                            <th style="width: 85px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($members as $index => $member)
                            @php
                                $memberScores = $scores[$member->id] ?? [];
                                $rowValues = [];
                                $santriNama = $member->santri->siswa->nama_lengkap ?? '-';
                                $kamarNama = $member->santri->kamarSantriAktif?->kamar?->nama ?? ($member->santri->kamar?->nama ?? '-');
                                $nisn = $member->santri->siswa->nisn ?: ($member->santri->nomor_induk_asrama ?: '-');
                            @endphp
                            <tr class="santri-row"
                                data-member-id="{{ $member->id }}"
                                data-index="{{ $index }}"
                                data-nama="{{ strtolower($santriNama) }}"
                                data-nisn="{{ strtolower($nisn) }}"
                                data-kamar="{{ strtolower($kamarNama) }}">

                                <td class="sticky-col-first font-weight-bold text-muted">{{ $index + 1 }}</td>
                                <td class="sticky-col-second text-left">
                                    <div class="font-weight-bold text-dark text-truncate" style="max-width: 220px;" title="{{ $santriNama }}">
                                        {{ $santriNama }}
                                    </div>
                                    <div class="text-muted small d-flex align-items-center gap-2">
                                        <span title="Kamar Asrama"><i class="fas fa-door-open text-teal mr-1"></i>{{ $kamarNama }}</span>
                                        <span>·</span>
                                        <span title="NISN / Induk">{{ $nisn }}</span>
                                    </div>
                                </td>

                                @foreach($aspectCodes as $colIdx => $kode)
                                    @php
                                        $pengampu = $pengampuList[$kode] ?? null;
                                        $currentVal = null;
                                        if ($pengampu && isset($memberScores[$pengampu->id])) {
                                            $currentVal = $memberScores[$pengampu->id]->nilai;
                                            if ($currentVal !== null) {
                                                $rowValues[] = (float) $currentVal;
                                            }
                                        }
                                        $valFloat = $currentVal !== null ? (float) $currentVal : null;
                                        $scoreClass = '';
                                        if ($valFloat !== null) {
                                            if ($valFloat >= 8.5) $scoreClass = 'score-mumtaz';
                                            elseif ($valFloat >= 7.0) $scoreClass = 'score-jayyid-jiddan';
                                            elseif ($valFloat >= 6.0) $scoreClass = 'score-jayyid';
                                            else $scoreClass = 'score-maqbul';
                                        }
                                    @endphp
                                    <td class="p-1 position-relative cell-score" data-aspect-kode="{{ $kode }}">
                                        <input type="text"
                                               inputmode="decimal"
                                               name="nilai[{{ $member->id }}][{{ $kode }}]"
                                               value="{{ $valFloat !== null ? $valFloat : '' }}"
                                               class="form-control form-control-sm smart-score-input score-input {{ $scoreClass }}"
                                               placeholder="-"
                                               data-member-id="{{ $member->id }}"
                                               data-kode="{{ $kode }}"
                                               data-row-index="{{ $index }}"
                                               data-col-index="{{ $colIdx }}"
                                               data-original="{{ $valFloat !== null ? $valFloat : '' }}"
                                               @if($isPublished) disabled @endif
                                               autocomplete="off">
                                        <span class="save-status-icon text-success position-absolute" style="right: 8px; top: 10px; display: none; font-size: 11px;">
                                            <i class="fas fa-check-circle"></i>
                                        </span>
                                    </td>
                                @endforeach

                                @php
                                    $avg = count($rowValues) > 0 ? round(array_sum($rowValues) / count($rowValues), 2) : null;
                                    $predikat = '-';
                                    $predClass = 'badge-light border';
                                    if ($avg !== null) {
                                        if ($avg >= 8.5) { $predikat = 'ممتاز (A)'; $predClass = 'badge-success'; }
                                        elseif ($avg >= 7.0) { $predikat = 'جيد جدا (B)'; $predClass = 'badge-info'; }
                                        elseif ($avg >= 6.0) { $predikat = 'جيد (C)'; $predClass = 'badge-warning text-dark'; }
                                        else { $predikat = 'مقبول (D)'; $predClass = 'badge-danger'; }
                                    }
                                @endphp
                                <td class="bg-light align-middle text-center">
                                    <div class="font-weight-bold row-avg text-dark" style="font-size: 1.05rem;">
                                        {{ $avg !== null ? number_format($avg, 1, ',', '.') : '-' }}
                                    </div>
                                    <span class="badge {{ $predClass }} row-pred" style="font-size: 10px;">
                                        {{ $predikat }}
                                    </span>
                                </td>
                                <td class="row-status align-middle text-center">
                                    @if(count($rowValues) === count($aspectCodes) && count($aspectCodes) > 0)
                                        <span class="badge badge-success"><i class="fas fa-check"></i> Lengkap</span>
                                    @elseif(count($rowValues) > 0)
                                        <span class="badge badge-warning text-dark">{{ count($rowValues) }}/{{ count($aspectCodes) }}</span>
                                    @else
                                        <span class="badge badge-light border text-muted">Kosong</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($aspectCodes) + 5 }}" class="text-center py-5 text-muted">
                                    <i class="fas fa-users-slash fa-3x mb-3 text-secondary d-block"></i>
                                    <h5>Belum Ada Santri Terdaftar</h5>
                                    <p class="mb-0">Tidak ditemukan santri aktif di kelas asrama ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center py-3">
                <div class="small text-muted mb-2 mb-md-0">
                    <span class="mr-3"><i class="fas fa-lightbulb text-warning mr-1"></i> Tip: Gunakan tombol <kbd>↑</kbd> <kbd>↓</kbd> atau <kbd>Enter</kbd> untuk berpindah santri di aspek yang sama.</span>
                </div>
                @if(!$isPublished && $members->isNotEmpty())
                    <button type="submit" class="btn btn-success font-weight-bold px-4">
                        <i class="fas fa-save mr-1"></i> Simpan Semua Perubahan
                    </button>
                @endif
            </div>
        </div>

        <!-- MODE 2: EXAMINER TOUCH CARDS (MOBILE & TABLET EXAMINER VIEW) -->
        <div id="cardContainer" style="display: none;" class="mb-4">
            <div class="row">
                @foreach($members as $index => $member)
                    @php
                        $memberScores = $scores[$member->id] ?? [];
                        $santriNama = $member->santri->siswa->nama_lengkap ?? '-';
                        $kamarNama = $member->santri->kamarSantriAktif?->kamar?->nama ?? ($member->santri->kamar?->nama ?? '-');
                        $nisn = $member->santri->siswa->nisn ?: ($member->santri->nomor_induk_asrama ?: '-');
                    @endphp
                    <div class="col-lg-6 col-12 mb-3 santri-card-item"
                         data-member-id="{{ $member->id }}"
                         data-index="{{ $index }}"
                         data-nama="{{ strtolower($santriNama) }}"
                         data-nisn="{{ strtolower($nisn) }}"
                         data-kamar="{{ strtolower($kamarNama) }}">
                        <div class="examiner-card">
                            <div class="examiner-card__header d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="badge badge-dark px-2 py-1 font-weight-bold" style="font-size: 1rem; border-radius: 8px;">
                                        #{{ $index + 1 }}
                                    </span>
                                    <div>
                                        <h5 class="font-weight-bold text-dark mb-0">{{ $santriNama }}</h5>
                                        <small class="text-muted">
                                            <i class="fas fa-door-open text-teal mr-1"></i>{{ $kamarNama }} · NISN: {{ $nisn }}
                                        </small>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-weight-bold text-teal card-item-avg" style="font-size: 1.25rem;">-</div>
                                    <span class="badge badge-light border card-item-status" style="font-size: 10px;">Status</span>
                                </div>
                            </div>
                            <div class="card-body p-3">
                                @foreach($aspectCodes as $kode)
                                    @php
                                        $mapel = $mapels->get($kode);
                                        $colTitleArab = $mapel?->nama_arab ?? $kode;
                                        $colTitleLatin = $mapel?->nama_latin ?? $kode;
                                        if ($kode === 'ASR-023') {
                                            $colTitleArab = $selectedKelas->is_tahfidz ? 'تحسين القرآن' : 'الترجمة';
                                            $colTitleLatin = $selectedKelas->is_tahfidz ? 'Tahsin Al-Qur\'an' : 'Tarjamah';
                                        }
                                        $pengampu = $pengampuList[$kode] ?? null;
                                        $currentVal = null;
                                        if ($pengampu && isset($memberScores[$pengampu->id])) {
                                            $currentVal = $memberScores[$pengampu->id]->nilai;
                                        }
                                        $valFloat = $currentVal !== null ? (float) $currentVal : null;
                                    @endphp
                                    <div class="border rounded p-2 mb-2 bg-light">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <div>
                                                <strong class="text-dark">{{ $colTitleLatin }}</strong>
                                                <span class="asrama-arab text-teal ml-1" style="font-size: 1.1rem;">{{ $colTitleArab }}</span>
                                            </div>
                                            <div class="w-25 text-right">
                                                <input type="text"
                                                       inputmode="decimal"
                                                       class="form-control form-control-sm text-center font-weight-bold score-card-input"
                                                       value="{{ $valFloat !== null ? $valFloat : '' }}"
                                                       placeholder="0-10"
                                                       data-member-id="{{ $member->id }}"
                                                       data-kode="{{ $kode }}"
                                                       @if($isPublished) disabled @endif>
                                            </div>
                                        </div>
                                        <!-- Quick Score Preset Pills (Touch-Friendly) -->
                                        @if(!$isPublished)
                                            <div class="d-flex flex-wrap gap-1 mt-1 justify-content-end quick-score-toolbar">
                                                @foreach(['6.0', '6.5', '7.0', '7.5', '8.0', '8.5', '9.0', '9.5', '10'] as $preset)
                                                    <button type="button"
                                                            class="examiner-score-pill @if($valFloat == (float)$preset) active @endif"
                                                            data-preset="{{ $preset }}"
                                                            data-member-id="{{ $member->id }}"
                                                            data-kode="{{ $kode }}">
                                                        {{ $preset }}
                                                    </button>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </form>

    <!-- FLOATING AUTO-SAVE STATUS BAR -->
    <div class="asrama-floating-bar" id="floatingActionBar">
        <div class="status-text">
            <span id="floatingSaveSpinner" style="display:none;"><i class="fas fa-spinner fa-spin text-warning"></i></span>
            <span id="floatingSaveSuccess"><i class="fas fa-check-circle text-success"></i></span>
            <span id="floatingSaveText" class="font-weight-bold">Tersimpan otomatis</span>
        </div>
        <div class="border-left border-secondary pl-3 hide-sm">
            <span id="floatingProgressBadge" class="badge badge-light text-dark font-weight-bold px-2 py-1">0/{{ $members->count() }} Santri</span>
        </div>
        @if(!$isPublished && $members->isNotEmpty())
            <button type="button" class="btn btn-sm btn-success font-weight-bold ml-2 px-3" onclick="$('#syafahiForm').submit()">
                <i class="fas fa-save mr-1"></i> Simpan
            </button>
        @endif
    </div>

    <!-- MODAL: BULK FILL (ISI CEPAT) -->
    @if(!$isPublished)
        <div class="modal fade" id="bulkFillModal" tabindex="-1" role="dialog" aria-labelledby="bulkFillModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-weight-bold" id="bulkFillModalLabel">
                            <i class="fas fa-magic text-warning mr-2"></i> Isi Nilai Seragam (Bulk Fill)
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small">
                            Fitur ini berguna untuk mengisi nilai dasar dengan cepat ke semua santri yang nilainya masih kosong pada aspek tertentu.
                        </p>
                        <div class="form-group">
                            <label class="font-weight-bold">Pilih Aspek Penilaian</label>
                            <select id="bulkAspectSelect" class="form-control">
                                <option value="all">-- Semua Aspek di Kelompok Ini --</option>
                                @foreach($aspectCodes as $kode)
                                    @php $mapel = $mapels->get($kode); @endphp
                                    <option value="{{ $kode }}">{{ $mapel?->nama_latin ?? $kode }} ({{ $mapel?->nama_arab ?? $kode }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Nilai yang Diberikan (Skala 0 - 10)</label>
                            <div class="input-group">
                                <input type="number" step="0.1" min="0" max="10" id="bulkScoreValue" class="form-control font-weight-bold text-center" value="7.5">
                                <div class="input-group-append">
                                    <span class="input-group-text">Skala 0-10</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="bulkOnlyEmptyCheck" checked>
                                <label class="custom-control-label" for="bulkOnlyEmptyCheck">
                                    Hanya isi santri yang nilainya masih kosong (tidak menimpa nilai yang sudah ada)
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-warning font-weight-bold text-dark" id="btnExecuteBulkFill">
                            <i class="fas fa-bolt mr-1"></i> Terapkan Nilai
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL: KEYBOARD SHORTCUT HELP -->
    <div class="modal fade" id="keyboardHelpModal" tabindex="-1" role="dialog" aria-labelledby="keyboardHelpModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold" id="keyboardHelpModalLabel">
                        <i class="fas fa-keyboard text-info mr-2"></i> Pintasan Keyboard Lembar Nilai
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <table class="table table-striped table-bordered mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 45%;">Tombol</th>
                                <th>Fungsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><kbd>Enter</kbd> atau <kbd>↓</kbd></td>
                                <td>Menyimpan nilai dan pindah ke santri berikutnya pada aspek yang sama</td>
                            </tr>
                            <tr>
                                <td><kbd>↑</kbd></td>
                                <td>Pindah ke santri sebelumnya pada aspek yang sama</td>
                            </tr>
                            <tr>
                                <td><kbd>Tab</kbd> atau <kbd>→</kbd></td>
                                <td>Pindah ke aspek berikutnya pada santri yang sama</td>
                            </tr>
                            <tr>
                                <td><kbd>Shift</kbd> + <kbd>Tab</kbd> atau <kbd>←</kbd></td>
                                <td>Pindah ke aspek sebelumnya pada santri yang sama</td>
                            </tr>
                            <tr>
                                <td><kbd>Esc</kbd></td>
                                <td>Batalkan edit dan kembalikan ke nilai awal</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Saya Mengerti</button>
                </div>
            </div>
        </div>
    </div>

@endif

@stop

@section('css')
@include('asrama._styles')
@stop

@section('js')
<script>
$(document).ready(function() {
    $('.asrama-select').select2({ theme: 'bootstrap4' });

    var quickSaveUrl = "{{ route('asrama.syafahi.quick-save') }}";
    var csrfToken = "{{ csrf_token() }}";
    var kelasId = "{{ $selectedKelas?->id }}";
    var semester = "{{ $semester }}";
    var totalAspectCount = {{ count($aspectCodes) }};
    var isPublished = {{ $isPublished ? 'true' : 'false' }};

    // Auto-select content on input focus
    $(document).on('focus', '.smart-score-input, .score-card-input', function() {
        $(this).select();
        $(this).closest('tr').addClass('asrama-row-active');
    }).on('blur', '.smart-score-input, .score-card-input', function() {
        $(this).closest('tr').removeClass('asrama-row-active');
    });

    // Helper: format and style score class
    function applyScoreStyle($input, val) {
        $input.removeClass('score-mumtaz score-jayyid-jiddan score-jayyid score-maqbul');
        if (val !== null && val !== '' && !isNaN(val)) {
            var num = parseFloat(val);
            if (num >= 8.5) $input.addClass('score-mumtaz');
            else if (num >= 7.0) $input.addClass('score-jayyid-jiddan');
            else if (num >= 6.0) $input.addClass('score-jayyid');
            else $input.addClass('score-maqbul');
        }
    }

    // Recalculate everything across the class
    function recalculateClassAnalytics() {
        var totalStudents = $('.santri-row').length;
        var completeCount = 0;
        var partialCount = 0;
        var emptyCount = 0;

        var allFilledScores = [];
        var mumtazCount = 0;
        var jayyidJiddanCount = 0;
        var jayyidCount = 0;
        var maqbulCount = 0;

        $('.santri-row').each(function() {
            var $row = $(this);
            var memberId = $row.data('member-id');
            var rowScores = [];

            $row.find('.score-input').each(function() {
                var v = $(this).val().trim().replace(',', '.');
                if (v !== '' && !isNaN(v)) {
                    var n = parseFloat(v);
                    rowScores.push(n);
                    allFilledScores.push(n);
                }
            });

            // Update row stats
            var $avgCell = $row.find('.row-avg');
            var $predBadge = $row.find('.row-pred');
            var $statusCell = $row.find('.row-status');

            var $cardItem = $(`.santri-card-item[data-member-id="${memberId}"]`);
            var $cardAvg = $cardItem.find('.card-item-avg');
            var $cardStatus = $cardItem.find('.card-item-status');

            if (rowScores.length > 0) {
                var sum = rowScores.reduce(function(a, b) { return a + b; }, 0);
                var avg = (sum / rowScores.length);
                var avgStr = avg.toFixed(1).replace('.', ',');
                $avgCell.text(avgStr);
                $cardAvg.text(avgStr);

                // Predicate
                var predText = '-';
                var predClass = 'badge-light border';
                if (avg >= 8.5) {
                    predText = 'ممتاز (A)';
                    predClass = 'badge-success';
                    mumtazCount++;
                } else if (avg >= 7.0) {
                    predText = 'جيد جدا (B)';
                    predClass = 'badge-info';
                    jayyidJiddanCount++;
                } else if (avg >= 6.0) {
                    predText = 'جيد (C)';
                    predClass = 'badge-warning text-dark';
                    jayyidCount++;
                } else {
                    predText = 'مقبول (D)';
                    predClass = 'badge-danger';
                    maqbulCount++;
                }
                $predBadge.attr('class', 'badge ' + predClass + ' row-pred').text(predText);

                if (rowScores.length === totalAspectCount && totalAspectCount > 0) {
                    completeCount++;
                    $row.attr('data-status', 'lengkap');
                    $cardItem.attr('data-status', 'lengkap');
                    $statusCell.html('<span class="badge badge-success"><i class="fas fa-check"></i> Lengkap</span>');
                    $cardStatus.attr('class', 'badge badge-success card-item-status').html('<i class="fas fa-check"></i> Lengkap');
                } else {
                    partialCount++;
                    $row.attr('data-status', 'belum_lengkap');
                    $cardItem.attr('data-status', 'belum_lengkap');
                    $statusCell.html('<span class="badge badge-warning text-dark">' + rowScores.length + '/' + totalAspectCount + '</span>');
                    $cardStatus.attr('class', 'badge badge-warning text-dark card-item-status').html(rowScores.length + '/' + totalAspectCount);
                }
            } else {
                emptyCount++;
                $row.attr('data-status', 'kosong');
                $cardItem.attr('data-status', 'kosong');
                $avgCell.text('-');
                $cardAvg.text('-');
                $predBadge.attr('class', 'badge badge-light border row-pred').text('-');
                $statusCell.html('<span class="badge badge-light border text-muted">Kosong</span>');
                $cardStatus.attr('class', 'badge badge-light border text-muted card-item-status').text('Kosong');
            }
        });

        // Update KPI ribbon
        var percent = totalStudents > 0 ? Math.round((completeCount / totalStudents) * 100) : 0;
        $('#kpiProgressText').text(completeCount + ' / ' + totalStudents);
        $('#kpiProgressPercent').text(percent + '%');
        $('#kpiProgressBar').css('width', percent + '%');
        $('#kpiProgressSub').text(partialCount + ' Belum Lengkap · ' + emptyCount + ' Kosong');

        $('#countFilterAll').text(totalStudents);
        $('#countFilterLengkap').text(completeCount);
        $('#countFilterBelum').text(partialCount);
        $('#countFilterKosong').text(emptyCount);

        $('#floatingProgressBadge').text(completeCount + '/' + totalStudents + ' Santri Lengkap');

        if (allFilledScores.length > 0) {
            var sumAll = allFilledScores.reduce(function(a, b) { return a + b; }, 0);
            var classAvg = (sumAll / allFilledScores.length);
            $('#kpiClassAvg').text(classAvg.toFixed(2).replace('.', ','));

            var predGeneral = 'Maqbul';
            var predGeneralBadge = 'badge-danger';
            if (classAvg >= 8.5) { predGeneral = 'Mumtaz (A)'; predGeneralBadge = 'badge-success'; }
            else if (classAvg >= 7.0) { predGeneral = 'Jayyid Jiddan (B)'; predGeneralBadge = 'badge-info'; }
            else if (classAvg >= 6.0) { predGeneral = 'Jayyid (C)'; predGeneralBadge = 'badge-warning text-dark'; }
            $('#kpiClassPred').attr('class', 'badge ' + predGeneralBadge).text(predGeneral);

            var max = Math.max.apply(null, allFilledScores);
            var min = Math.min.apply(null, allFilledScores);
            $('#kpiMaxScore').text(max.toFixed(1).replace('.', ','));
            $('#kpiMinScore').text(min.toFixed(1).replace('.', ','));
        } else {
            $('#kpiClassAvg').text('-');
            $('#kpiClassPred').attr('class', 'badge badge-secondary').text('-');
            $('#kpiMaxScore').text('-');
            $('#kpiMinScore').text('-');
        }

        $('#kpiCountMumtaz').text(mumtazCount);
        $('#kpiCountJayyidJiddan').text(jayyidJiddanCount);
        $('#kpiCountJayyid').text(jayyidCount);
        $('#kpiCountMaqbul').text(maqbulCount);
    }

    // Initialize stats
    recalculateClassAnalytics();

    // Trigger Quick Save AJAX
    function executeQuickSave($input, memberId, kode, newVal, onDone) {
        var $cell = $input.closest('td');
        var $icon = $cell.find('.save-status-icon');

        $('#floatingSaveSpinner').show();
        $('#floatingSaveSuccess').hide();
        $('#floatingSaveText').text('Menyimpan perubahan...');

        applyScoreStyle($input, newVal);

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
                $input.data('original', newVal);
                $icon.fadeIn(150).delay(1200).fadeOut(300);

                // Sync card view input and preset pills if any
                var $cardInput = $(`.score-card-input[data-member-id="${memberId}"][data-kode="${kode}"]`);
                if ($cardInput.length) {
                    $cardInput.val(newVal);
                    var $pillGroup = $cardInput.closest('.border').find('.examiner-score-pill');
                    $pillGroup.removeClass('active');
                    $pillGroup.filter(`[data-preset="${newVal}"]`).addClass('active');
                }

                $('#floatingSaveSpinner').hide();
                $('#floatingSaveSuccess').show();
                $('#floatingSaveText').text('Tersimpan otomatis');

                recalculateClassAnalytics();
                if (typeof onDone === 'function') onDone(true);
            },
            error: function(xhr) {
                $('#floatingSaveSpinner').hide();
                $('#floatingSaveSuccess').hide();
                $('#floatingSaveText').text('Gagal menyimpan!');

                var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menyimpan nilai.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Peringatan',
                        text: msg,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3500
                    });
                } else {
                    alert(msg);
                }
                if (typeof onDone === 'function') onDone(false);
            }
        });
    }

    // Grid score change
    $(document).on('change', '.score-input', function() {
        var $input = $(this);
        var originalVal = String($input.data('original') || '').trim();
        var newVal = $input.val().trim();

        if (originalVal === newVal) return;

        var memberId = $input.data('member-id');
        var kode = $input.data('kode');
        executeQuickSave($input, memberId, kode, newVal);
    });

    // Card score change
    $(document).on('change', '.score-card-input', function() {
        var $cardInput = $(this);
        var memberId = $cardInput.data('member-id');
        var kode = $cardInput.data('kode');
        var newVal = $cardInput.val().trim();

        var $gridInput = $(`.score-input[data-member-id="${memberId}"][data-kode="${kode}"]`);
        if ($gridInput.length) {
            $gridInput.val(newVal);
        }
        executeQuickSave($cardInput, memberId, kode, newVal);
    });

    // Preset Pill Click (Examiner Touch)
    $(document).on('click', '.examiner-score-pill', function() {
        if (isPublished) return;
        var $pill = $(this);
        var preset = $pill.data('preset');
        var memberId = $pill.data('member-id');
        var kode = $pill.data('kode');

        $pill.siblings('.examiner-score-pill').removeClass('active');
        $pill.addClass('active');

        var $cardInput = $(`.score-card-input[data-member-id="${memberId}"][data-kode="${kode}"]`);
        $cardInput.val(preset);

        var $gridInput = $(`.score-input[data-member-id="${memberId}"][data-kode="${kode}"]`);
        if ($gridInput.length) {
            $gridInput.val(preset);
            applyScoreStyle($gridInput, preset);
        }

        executeQuickSave($cardInput, memberId, kode, preset);
    });

    // Keyboard Navigation in Grid Mode
    $(document).on('keydown', '.score-input', function(e) {
        var $this = $(this);
        var currentRow = parseInt($this.data('row-index'), 10);
        var currentCol = parseInt($this.data('col-index'), 10);

        if (e.which === 13 || e.which === 40) { // Enter or Down Arrow -> Next student, same aspect
            e.preventDefault();
            $this.trigger('change');
            var $target = $(`.score-input[data-row-index="${currentRow + 1}"][data-col-index="${currentCol}"]`);
            if ($target.length) {
                $target.focus().select();
            }
        } else if (e.which === 38) { // Up Arrow -> Prev student, same aspect
            e.preventDefault();
            $this.trigger('change');
            var $target = $(`.score-input[data-row-index="${currentRow - 1}"][data-col-index="${currentCol}"]`);
            if ($target.length) {
                $target.focus().select();
            }
        } else if (e.which === 39) { // Right Arrow -> Next aspect, same student
            var cursorPos = this.selectionEnd;
            if (cursorPos === this.value.length) {
                var $target = $(`.score-input[data-row-index="${currentRow}"][data-col-index="${currentCol + 1}"]`);
                if ($target.length) {
                    e.preventDefault();
                    $this.trigger('change');
                    $target.focus().select();
                }
            }
        } else if (e.which === 37) { // Left Arrow -> Prev aspect, same student
            var cursorPos = this.selectionStart;
            if (cursorPos === 0) {
                var $target = $(`.score-input[data-row-index="${currentRow}"][data-col-index="${currentCol - 1}"]`);
                if ($target.length) {
                    e.preventDefault();
                    $this.trigger('change');
                    $target.focus().select();
                }
            }
        }
    });

    // Live Search Filter
    $('#santriSearchInput').on('keyup input', function() {
        var query = $(this).val().toLowerCase().trim();
        if (query.length > 0) {
            $('#clearSearchBtn').show();
        } else {
            $('#clearSearchBtn').hide();
        }
        applyFilters();
    });

    $('#clearSearchBtn').on('click', function() {
        $('#santriSearchInput').val('').trigger('input').focus();
    });

    // Status Filter Tabs
    $('#filterStatusGroup button').on('click', function() {
        $('#filterStatusGroup button').removeClass('active btn-primary btn-success btn-warning btn-secondary')
            .each(function() {
                var st = $(this).data('status');
                if (st === 'all') $(this).addClass('btn-outline-primary');
                else if (st === 'lengkap') $(this).addClass('btn-outline-success');
                else if (st === 'belum_lengkap') $(this).addClass('btn-outline-warning');
                else $(this).addClass('btn-outline-secondary');
            });

        var st = $(this).data('status');
        $(this).addClass('active');
        if (st === 'all') $(this).removeClass('btn-outline-primary').addClass('btn-primary');
        else if (st === 'lengkap') $(this).removeClass('btn-outline-success').addClass('btn-success');
        else if (st === 'belum_lengkap') $(this).removeClass('btn-outline-warning').addClass('btn-warning');
        else $(this).removeClass('btn-outline-secondary').addClass('btn-secondary');

        applyFilters();
    });

    // Predicate filter clicks
    $('.filter-badge').on('click', function() {
        var pred = $(this).data('filter-pred');
        var query = '';
        if (pred === 'mumtaz') query = 'ممتاز';
        else if (pred === 'jayyid_jiddan') query = 'جيد جدا';
        else if (pred === 'jayyid') query = 'جيد';
        else if (pred === 'maqbul') query = 'مقبول';

        var visibleCount = 0;
        $('.santri-row').each(function() {
            var $row = $(this);
            var memberId = $row.data('member-id');
            var predText = $row.find('.row-pred').text();
            var match = predText.indexOf(query) !== -1;
            var $cardItem = $(`.santri-card-item[data-member-id="${memberId}"]`);
            if (match) {
                $row.show();
                $cardItem.show();
                visibleCount++;
            } else {
                $row.hide();
                $cardItem.hide();
            }
        });
        $('#filterResultInfo').html('Menampilkan <strong>' + visibleCount + '</strong> santri berpredikat ' + query);
    });

    function applyFilters() {
        var query = $('#santriSearchInput').val().toLowerCase().trim();
        var selectedStatus = $('#filterStatusGroup button.active').data('status');
        var visibleCount = 0;

        $('.santri-row').each(function() {
            var $row = $(this);
            var memberId = $row.data('member-id');
            var nama = $row.data('nama');
            var nisn = $row.data('nisn');
            var kamar = $row.data('kamar');
            var status = $row.attr('data-status') || 'kosong';

            var matchSearch = query === '' || nama.indexOf(query) !== -1 || nisn.indexOf(query) !== -1 || kamar.indexOf(query) !== -1;
            var matchStatus = selectedStatus === 'all' || status === selectedStatus;

            var $cardItem = $(`.santri-card-item[data-member-id="${memberId}"]`);

            if (matchSearch && matchStatus) {
                $row.show();
                $cardItem.show();
                visibleCount++;
            } else {
                $row.hide();
                $cardItem.hide();
            }
        });

        $('#filterResultInfo').html('Menampilkan <strong>' + visibleCount + '</strong> dari ' + $('.santri-row').length + ' santri');
    }

    // Dual View Mode Toggle
    $('#btnViewTable').on('click', function() {
        $(this).addClass('active btn-info').removeClass('btn-outline-info');
        $('#btnViewCard').removeClass('active btn-info').addClass('btn-outline-info');
        $('#tableContainer').fadeIn(200);
        $('#cardContainer').hide();
    });

    $('#btnViewCard').on('click', function() {
        $(this).addClass('active btn-info').removeClass('btn-outline-info');
        $('#btnViewTable').removeClass('active btn-info').addClass('btn-outline-info');
        $('#cardContainer').fadeIn(200);
        $('#tableContainer').hide();
    });

    // Auto-detect mobile screen and suggest card mode
    if ($(window).width() < 768) {
        $('#btnViewCard').trigger('click');
    }

    // Bulk Fill Action
    $('#btnExecuteBulkFill').on('click', function() {
        var aspect = $('#bulkAspectSelect').val();
        var scoreVal = parseFloat($('#bulkScoreValue').val());
        var onlyEmpty = $('#bulkOnlyEmptyCheck').is(':checked');

        if (isNaN(scoreVal) || scoreVal < 0 || scoreVal > 10) {
            alert('Masukkan nilai yang valid antara 0 sampai 10.');
            return;
        }

        var targets = [];
        $('.score-input').each(function() {
            var $inp = $(this);
            var kode = $inp.data('kode');
            if (aspect === 'all' || kode === aspect) {
                var currentVal = $inp.val().trim();
                if (!onlyEmpty || currentVal === '') {
                    targets.push($inp);
                }
            }
        });

        if (targets.length === 0) {
            alert('Tidak ada kotak nilai yang memenuhi kriteria pengisian.');
            return;
        }

        $('#bulkFillModal').modal('hide');

        var confirmMsg = 'Akan mengisi ' + targets.length + ' kotak nilai dengan ' + scoreVal + '. Lanjutkan?';
        if (confirm(confirmMsg)) {
            targets.forEach(function($inp) {
                $inp.val(scoreVal);
                var memberId = $inp.data('member-id');
                var kode = $inp.data('kode');
                executeQuickSave($inp, memberId, kode, scoreVal);
            });
        }
    });
});
</script>
@stop

@extends('adminlte::page')
@section('title', 'Nilai ' . $pengampu->mapel->nama_latin)
@section('plugins.Sweetalert2', true)
@section('content')
@include('asrama._alerts')
@php
    $heroTitle = 'Input Nilai · ' . $pengampu->mapel->nama_latin;
    $heroDescription = $pengampu->kelas->nama_kelas . ' · ' . $pengampu->kelas->tahunPelajaran->nama . ' · Semester ' . $pengampu->semester;
    $heroAction = '<div class="btn-group">'
        . '<a class="btn btn-light font-weight-bold mr-2" href="' . route('asrama.nilai.index', ['tahun_pelajaran_id' => $pengampu->kelas->tahun_pelajaran_id]) . '"><i class="fas fa-arrow-left mr-1"></i> Kembali ke Penugasan</a>';
    if (!empty($canManageKomponen)) {
        $heroAction .= '<a class="btn btn-warning text-dark font-weight-bold" href="' . route('asrama.komponen-nilai.index', ['tahun_pelajaran_id' => $pengampu->kelas->tahun_pelajaran_id]) . '"><i class="fas fa-sliders-h mr-1"></i> Pengaturan Komponen</a>';
    }
    $heroAction .= '</div>';

    $members = $pengampu->kelas->anggotaAktif->sortBy('santri.siswa.nama_lengkap')->values();
@endphp
@include('asrama._hero')

@if($isPublished)
    <div class="alert alert-warning asrama-alert mb-3 shadow-sm border-left" style="border-left-width: 5px !important;">
        <i class="fas fa-lock mr-2"></i>
        <strong>Rapor Sudah Diterbitkan:</strong> Nilai pada kelas dan semester ini sudah diterbitkan pada rapor santri. Perubahan nilai dinonaktifkan untuk menjaga validitas data rapor.
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
            <span>Rata-Rata Kelas</span>
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
            <small class="text-muted d-block">Skala maksimum: 0 - {{ $pengampu->mapel->skala_maksimum }}</small>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-2">
        <div class="asrama-stat asrama-stat--warning">
            <span>Distribusi Capaian</span>
            <div class="d-flex flex-wrap gap-1 mt-1">
                <span class="badge badge-success px-2 py-1 filter-badge" data-filter-pred="mumtaz" role="button" title="Nilai >= 85%">
                    Mumtaz: <strong id="kpiCountMumtaz">0</strong>
                </span>
                <span class="badge badge-info px-2 py-1 filter-badge" data-filter-pred="jayyid_jiddan" role="button" title="Nilai 70-84%">
                    J. Jiddan: <strong id="kpiCountJayyidJiddan">0</strong>
                </span>
                <span class="badge badge-warning text-dark px-2 py-1 filter-badge" data-filter-pred="jayyid" role="button" title="Nilai 60-69%">
                    Jayyid: <strong id="kpiCountJayyid">0</strong>
                </span>
                <span class="badge badge-danger px-2 py-1 filter-badge" data-filter-pred="maqbul" role="button" title="Nilai < 60%">
                    Maqbul: <strong id="kpiCountMaqbul">0</strong>
                </span>
            </div>
            <small class="text-muted mt-1 d-block">Berdasarkan nilai akhir santri</small>
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
                    <input type="text" id="santriSearchInput" class="form-control border-left-0 pl-0" placeholder="Cari nama santri, induk, kamar...">
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

            <!-- Component Filter or Action info -->
            <div class="col-lg-3 col-md-12 text-md-right mt-2 mt-lg-0">
                <div class="d-flex justify-content-end align-items-center">
                    <span class="text-muted small">
                        <i class="fas fa-bolt text-warning mr-1"></i> Auto-Save Aktif
                    </span>
                </div>
            </div>
        </div>

        @if($komponenList->isNotEmpty())
            <div class="d-flex flex-wrap align-items-center justify-content-between mt-3 pt-2 border-top">
                <div class="d-flex align-items-center flex-wrap">
                    <span class="font-weight-bold text-dark mr-2 small"><i class="fas fa-eye text-teal mr-1"></i> Fokus Komponen:</span>
                    <div class="btn-group btn-group-sm mr-2 mb-1" id="btn-group-filter-komponen">
                        <button type="button" class="btn btn-info font-weight-bold btn-filter-komponen active" data-kode="all">
                            Semua ({{ $komponenList->count() }})
                        </button>
                        @foreach($komponenList as $kmp)
                            <button type="button" class="btn btn-outline-info font-weight-bold btn-filter-komponen" data-kode="{{ $kmp->kode }}">
                                {{ $kmp->kode }} <span class="badge badge-light border ml-1">{{ number_format($kmp->bobot, 0) }}%</span>
                            </button>
                        @endforeach
                    </div>
                </div>
                <div class="text-muted small">
                    <i class="fas fa-info-circle mr-1 text-teal"></i> Nilai otomatis disimpan saat berpindah kotak (blur / enter).
                </div>
            </div>
        @endif
    </div>
</div>

<form id="formNilaiAsrama" method="post" action="{{ route('asrama.nilai.update', $pengampu) }}" data-asrama-loading data-loading-title="Menyimpan nilai santri" data-loading-text="Nilai sedang divalidasi dan disimpan.">
    @csrf
    @method('PUT')
    <div class="asrama-panel mb-4 shadow-sm">
        <div class="asrama-panel__header">
            <div>
                <h3>
                    <span class="asrama-arab mr-2 text-teal">{{ $pengampu->mapel->nama_arab }}</span>
                    <span class="text-muted">({{ $pengampu->mapel->nama_latin }})</span>
                </h3>
                <p>
                    Skala nilai: 0–{{ $pengampu->mapel->skala_maksimum }}.
                    @if($komponenList->isNotEmpty())
                        Mode: <strong>Penilaian Dinamis Berbobot</strong> ({{ $komponenList->count() }} komponen aktif, total bobot {{ number_format($totalBobot, 1) }}%). Nilai akhir akan dihitung otomatis.
                    @else
                        Mode: <strong>Nilai Tunggal Flat</strong> (Skala 0–{{ $pengampu->mapel->skala_maksimum }}).
                    @endif
                </p>
            </div>
            @unless($isPublished)
                <button type="submit" class="btn btn-success font-weight-bold px-4">
                    <i class="fas fa-save mr-1"></i> Simpan Semua Nilai
                </button>
            @endunless
        </div>

        <div class="table-responsive">
            <table class="table asrama-table asrama-table-sticky mb-0" id="table-nilai-asrama">
                <thead>
                    <tr>
                        <th class="sticky-col-first" style="width: 45px;">No</th>
                        <th class="sticky-col-second text-left" style="min-width: 220px;">
                            Nama Santri
                            <div class="text-muted" style="font-size: 11px; font-weight: normal;">Kamar & NISN</div>
                        </th>
                        @if($komponenList->isNotEmpty())
                            @foreach($komponenList as $colIdx => $kmp)
                                <th style="width: 135px;" class="text-center th-komponen" data-col-kode="{{ $kmp->kode }}">
                                    <div class="font-weight-bold text-truncate" title="{{ $kmp->nama }}">{{ $kmp->nama }}</div>
                                    <small class="badge badge-info font-weight-bold">{{ $kmp->kode }} · {{ number_format($kmp->bobot, 1) }}%</small>
                                </th>
                            @endforeach
                            <th style="width: 125px;" class="text-center bg-light th-final-score">
                                Nilai Akhir
                                <br>
                                <small class="text-muted font-weight-bold">(Tertimbang)</small>
                            </th>
                        @else
                            <th style="width: 150px;" class="text-center">Nilai (0–{{ $pengampu->mapel->skala_maksimum }})</th>
                        @endif
                        <th style="min-width: 200px;">Catatan</th>
                        <th style="width: 110px;" class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($members as $index => $member)
                        @php
                            $value = $values->get($member->id);
                            $detail = is_array($value?->detail_nilai) ? $value->detail_nilai : [];
                            $santriNama = $member->santri->siswa->nama_lengkap ?? '-';
                            $kamarNama = $member->santri->kamarSantriAktif?->kamar?->nama ?? ($member->santri->kamar?->nama ?? '-');
                            $induk = $member->santri->nomor_induk_asrama ?: ($member->santri->siswa->nisn ?: '-');
                        @endphp
                        <tr class="santri-row"
                            data-member-id="{{ $member->id }}"
                            data-index="{{ $index }}"
                            data-nama="{{ strtolower($santriNama) }}"
                            data-induk="{{ strtolower($induk) }}"
                            data-kamar="{{ strtolower($kamarNama) }}">
                            
                            <td class="sticky-col-first font-weight-bold text-muted text-center">{{ $member->nomor_urut ?? $index + 1 }}</td>
                            <td class="sticky-col-second text-left">
                                <div class="font-weight-bold text-dark text-truncate" style="max-width: 220px;" title="{{ $santriNama }}">
                                    {{ $santriNama }}
                                </div>
                                <div class="text-muted small d-flex align-items-center gap-2">
                                    <span title="Kamar Asrama"><i class="fas fa-door-open text-teal mr-1"></i>{{ $kamarNama }}</span>
                                    <span>·</span>
                                    <span title="Nomor Induk"><code>{{ $induk }}</code></span>
                                </div>
                            </td>

                            @if($komponenList->isNotEmpty())
                                @foreach($komponenList as $colIdx => $kmp)
                                    @php
                                        $valKomponen = old('detail_nilai.' . $member->id . '.' . $kmp->kode, $detail[$kmp->kode] ?? null);
                                        $valFloat = ($valKomponen !== null && $valKomponen !== '') ? (float) $valKomponen : null;
                                        $scoreClass = '';
                                        if ($valFloat !== null) {
                                            $ratio = $valFloat / max(1, (float) $pengampu->mapel->skala_maksimum);
                                            if ($ratio >= 0.85) $scoreClass = 'score-mumtaz';
                                            elseif ($ratio >= 0.70) $scoreClass = 'score-jayyid-jiddan';
                                            elseif ($ratio >= 0.60) $scoreClass = 'score-jayyid';
                                            else $scoreClass = 'score-maqbul';
                                        }
                                    @endphp
                                    <td class="text-center td-komponen position-relative p-1" data-col-kode="{{ $kmp->kode }}">
                                        <input type="text"
                                            inputmode="decimal"
                                            name="detail_nilai[{{ $member->id }}][{{ $kmp->kode }}]"
                                            value="{{ $valKomponen }}"
                                            class="form-control text-center smart-score-input input-komponen font-weight-bold {{ $scoreClass }}"
                                            data-member="{{ $member->id }}"
                                            data-kode="{{ $kmp->kode }}"
                                            data-bobot="{{ $kmp->bobot }}"
                                            data-row-index="{{ $index }}"
                                            data-col-index="{{ $colIdx }}"
                                            data-original="{{ $valKomponen !== null ? $valKomponen : '' }}"
                                            placeholder="-"
                                            autocomplete="off"
                                            @disabled($isPublished)>
                                        <span class="save-status-icon text-success position-absolute" style="right: 6px; top: 8px; display: none; font-size: 11px;">
                                            <i class="fas fa-check-circle"></i>
                                        </span>
                                    </td>
                                @endforeach
                                <td class="text-center bg-light align-middle td-final-score">
                                    <span class="badge {{ $value?->nilai !== null ? 'badge-info' : 'badge-light border' }} px-2 py-1 final-score-badge"
                                        id="badge-final-{{ $member->id }}"
                                        data-final-score="{{ $value?->nilai !== null ? (float) $value->nilai : '' }}"
                                        style="font-size: 1rem; min-width: 60px;">
                                        {{ $value?->nilai !== null ? number_format((float) $value->nilai, 2, ',', '.') : '-' }}
                                    </span>
                                </td>
                            @else
                                @php
                                    $valFlat = old('nilai.' . $member->id, $value?->nilai);
                                    $valFlatNum = ($valFlat !== null && $valFlat !== '') ? (float) $valFlat : null;
                                    $scoreClass = '';
                                    if ($valFlatNum !== null) {
                                        $ratio = $valFlatNum / max(1, (float) $pengampu->mapel->skala_maksimum);
                                        if ($ratio >= 0.85) $scoreClass = 'score-mumtaz';
                                        elseif ($ratio >= 0.70) $scoreClass = 'score-jayyid-jiddan';
                                        elseif ($ratio >= 0.60) $scoreClass = 'score-jayyid';
                                        else $scoreClass = 'score-maqbul';
                                    }
                                @endphp
                                <td class="position-relative p-1 text-center">
                                    <input type="text"
                                        inputmode="decimal"
                                        name="nilai[{{ $member->id }}]"
                                        value="{{ $valFlat }}"
                                        class="form-control text-center smart-score-input input-nilai-flat font-weight-bold {{ $scoreClass }}"
                                        data-member="{{ $member->id }}"
                                        data-row-index="{{ $index }}"
                                        data-original="{{ $valFlat !== null ? $valFlat : '' }}"
                                        placeholder="0–{{ $pengampu->mapel->skala_maksimum }}"
                                        autocomplete="off"
                                        @disabled($isPublished)>
                                    <span class="save-status-icon text-success position-absolute" style="right: 8px; top: 10px; display: none; font-size: 11px;">
                                        <i class="fas fa-check-circle"></i>
                                    </span>
                                </td>
                            @endif

                            <td class="position-relative p-1">
                                <input type="text"
                                    name="catatan[{{ $member->id }}]"
                                    value="{{ old('catatan.' . $member->id, $value?->catatan) }}"
                                    class="form-control form-control-sm input-catatan"
                                    data-member="{{ $member->id }}"
                                    data-original="{{ old('catatan.' . $member->id, $value?->catatan) }}"
                                    placeholder="Catatan santri..."
                                    autocomplete="off"
                                    @disabled($isPublished)>
                                <span class="save-status-icon text-success position-absolute" style="right: 8px; top: 8px; display: none; font-size: 11px;">
                                    <i class="fas fa-check-circle"></i>
                                </span>
                            </td>

                            <td class="row-status align-middle text-center">
                                <span class="badge badge-light border text-muted">Memuat...</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $komponenList->isNotEmpty() ? 5 + $komponenList->count() : 5 }}" class="asrama-empty py-5">
                                <i class="fas fa-users-slash fa-3x mb-3 text-secondary d-block"></i>
                                <h5>Belum Ada Santri di Kelas Ini</h5>
                                <p class="mb-0">Tidak ditemukan santri aktif di kelas asrama ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($members->isNotEmpty() && !$isPublished)
            <div class="asrama-panel__footer d-flex flex-wrap justify-content-between align-items-center p-3 bg-white border-top">
                <span class="text-muted small mb-2 mb-md-0">
                    <i class="fas fa-check-double text-success mr-1"></i> Nilai otomatis tersimpan secara real-time via AJAX. Tombol ini untuk menyimpan seluruh form.
                </span>
                <button type="submit" class="btn btn-success font-weight-bold px-4">
                    <i class="fas fa-save mr-1"></i> Simpan Semua Perubahan
                </button>
            </div>
        @endif
    </div>
</form>

<!-- FLOATING AUTO-SAVE STATUS BAR -->
<div class="asrama-floating-bar" id="asramaFloatingBar">
    <div class="status-text">
        <span id="floatingSaveSpinner" style="display:none;"><i class="fas fa-spinner fa-spin text-warning"></i></span>
        <span id="floatingSaveSuccess"><i class="fas fa-check-circle text-success"></i></span>
        <span id="floatingSaveText" class="font-weight-bold">Tersimpan otomatis</span>
    </div>
    <div class="border-left border-secondary pl-3 hide-sm">
        <span id="floatingProgressBadge" class="badge badge-light text-dark font-weight-bold px-2 py-1">0/{{ $members->count() }} Santri</span>
    </div>
    @if(!$isPublished && $members->isNotEmpty())
        <button type="button" class="btn btn-sm btn-success font-weight-bold ml-2 px-3" onclick="$('#formNilaiAsrama').submit()">
            <i class="fas fa-save mr-1"></i> Simpan
        </button>
    @endif
</div>

@include('asrama._scripts')
@push('js')
<script>
$(document).ready(function(){
    const quickSaveUrl = "{{ route('asrama.nilai.quick-save') }}";
    const csrfToken = "{{ csrf_token() }}";
    const pengampuId = "{{ $pengampu->id }}";
    const skalaMaksimum = {{ (float) ($pengampu->mapel->skala_maksimum ?? 100) }};
    const hasComponents = {{ $komponenList->isNotEmpty() ? 'true' : 'false' }};
    const totalComponents = {{ $komponenList->count() }};
    const isPublished = {{ $isPublished ? 'true' : 'false' }};

    // Auto-select text on focus and highlight active row
    $(document).on('focus', '.smart-score-input, .input-catatan', function() {
        $(this).select();
        $(this).closest('tr').addClass('asrama-row-active');
    }).on('blur', '.smart-score-input, .input-catatan', function() {
        $(this).closest('tr').removeClass('asrama-row-active');
    });

    // Helper: apply score style color class
    function applyScoreStyle($input, val) {
        $input.removeClass('score-mumtaz score-jayyid-jiddan score-jayyid score-maqbul');
        if (val !== null && val !== '' && !isNaN(val)) {
            const num = parseFloat(String(val).replace(',', '.'));
            const ratio = num / Math.max(1, skalaMaksimum);
            if (ratio >= 0.85) $input.addClass('score-mumtaz');
            else if (ratio >= 0.70) $input.addClass('score-jayyid-jiddan');
            else if (ratio >= 0.60) $input.addClass('score-jayyid');
            else $input.addClass('score-maqbul');
        }
    }

    // Recalculate whole class analytics and update row status
    function recalculateClassAnalytics() {
        const totalStudents = $('.santri-row').length;
        let completeCount = 0;
        let partialCount = 0;
        let emptyCount = 0;

        const allFinalScores = [];
        let countMumtaz = 0;
        let countJayyidJiddan = 0;
        let countJayyid = 0;
        let countMaqbul = 0;

        $('.santri-row').each(function() {
            const $row = $(this);
            const memberId = $row.data('member-id');
            const $statusCell = $row.find('.row-status');

            let isRowComplete = false;
            let isRowEmpty = true;
            let finalScoreNum = null;

            if (hasComponents) {
                let filledComp = 0;
                let totalWeighted = 0;
                let totalWeights = 0;

                $row.find('.input-komponen').each(function() {
                    const raw = $(this).val().trim().replace(',', '.');
                    if (raw !== '' && !isNaN(raw)) {
                        filledComp++;
                        isRowEmpty = false;
                        const v = parseFloat(raw);
                        const w = parseFloat($(this).data('bobot')) || 0;
                        totalWeighted += (v * w);
                        totalWeights += w;
                    }
                });

                if (totalWeights > 0) {
                    finalScoreNum = Math.round((totalWeighted / totalWeights) * 100) / 100;
                    $(`#badge-final-${memberId}`)
                        .text(finalScoreNum.toFixed(2).replace('.', ','))
                        .data('final-score', finalScoreNum)
                        .removeClass('badge-light border')
                        .addClass('badge-info');
                } else {
                    $(`#badge-final-${memberId}`)
                        .text('-')
                        .data('final-score', '')
                        .removeClass('badge-info')
                        .addClass('badge-light border');
                }

                if (filledComp === totalComponents && totalComponents > 0) {
                    isRowComplete = true;
                    $row.attr('data-status', 'lengkap');
                    $statusCell.html('<span class="badge badge-success"><i class="fas fa-check"></i> Lengkap</span>');
                } else if (filledComp > 0) {
                    $row.attr('data-status', 'belum_lengkap');
                    $statusCell.html('<span class="badge badge-warning text-dark">' + filledComp + '/' + totalComponents + '</span>');
                } else {
                    $row.attr('data-status', 'kosong');
                    $statusCell.html('<span class="badge badge-light border text-muted">Kosong</span>');
                }
            } else {
                const $flatInput = $row.find('.input-nilai-flat');
                const rawVal = $flatInput.val().trim().replace(',', '.');
                if (rawVal !== '' && !isNaN(rawVal)) {
                    isRowEmpty = false;
                    isRowComplete = true;
                    finalScoreNum = parseFloat(rawVal);
                    $row.attr('data-status', 'lengkap');
                    $statusCell.html('<span class="badge badge-success"><i class="fas fa-check"></i> Terisi</span>');
                } else {
                    $row.attr('data-status', 'kosong');
                    $statusCell.html('<span class="badge badge-light border text-muted">Kosong</span>');
                }
            }

            if (isRowComplete) {
                completeCount++;
            } else if (!isRowEmpty) {
                partialCount++;
            } else {
                emptyCount++;
            }

            if (finalScoreNum !== null && !isNaN(finalScoreNum)) {
                allFinalScores.push(finalScoreNum);
                const ratio = finalScoreNum / Math.max(1, skalaMaksimum);
                if (ratio >= 0.85) {
                    countMumtaz++;
                    $row.attr('data-pred', 'mumtaz');
                } else if (ratio >= 0.70) {
                    countJayyidJiddan++;
                    $row.attr('data-pred', 'jayyid_jiddan');
                } else if (ratio >= 0.60) {
                    countJayyid++;
                    $row.attr('data-pred', 'jayyid');
                } else {
                    countMaqbul++;
                    $row.attr('data-pred', 'maqbul');
                }
            } else {
                $row.attr('data-pred', '');
            }
        });

        // Update KPI ribbon
        const pct = totalStudents > 0 ? Math.round((completeCount / totalStudents) * 100) : 0;
        $('#kpiProgressText').text(completeCount + ' / ' + totalStudents);
        $('#kpiProgressPercent').text(pct + '%');
        $('#kpiProgressBar').css('width', pct + '%');
        $('#kpiProgressSub').text(partialCount + ' Belum Lengkap · ' + emptyCount + ' Kosong');

        $('#countFilterAll').text(totalStudents);
        $('#countFilterLengkap').text(completeCount);
        $('#countFilterBelum').text(partialCount);
        $('#countFilterKosong').text(emptyCount);

        $('#floatingProgressBadge').text(completeCount + '/' + totalStudents + ' Santri Lengkap');

        if (allFinalScores.length > 0) {
            const sum = allFinalScores.reduce((a, b) => a + b, 0);
            const classAvg = sum / allFinalScores.length;
            $('#kpiClassAvg').text(classAvg.toFixed(2).replace('.', ','));

            const ratioAvg = classAvg / Math.max(1, skalaMaksimum);
            let predGeneral = 'Maqbul';
            let predBadgeClass = 'badge-danger';
            if (ratioAvg >= 0.85) { predGeneral = 'Mumtaz (A)'; predBadgeClass = 'badge-success'; }
            else if (ratioAvg >= 0.70) { predGeneral = 'Jayyid Jiddan (B)'; predBadgeClass = 'badge-info'; }
            else if (ratioAvg >= 0.60) { predGeneral = 'Jayyid (C)'; predBadgeClass = 'badge-warning text-dark'; }
            $('#kpiClassPred').attr('class', 'badge ' + predBadgeClass).text(predGeneral);

            const maxScore = Math.max.apply(null, allFinalScores);
            const minScore = Math.min.apply(null, allFinalScores);
            $('#kpiMaxScore').text(maxScore.toFixed(1).replace('.', ','));
            $('#kpiMinScore').text(minScore.toFixed(1).replace('.', ','));
        } else {
            $('#kpiClassAvg').text('-');
            $('#kpiClassPred').attr('class', 'badge badge-secondary').text('-');
            $('#kpiMaxScore').text('-');
            $('#kpiMinScore').text('-');
        }

        $('#kpiCountMumtaz').text(countMumtaz);
        $('#kpiCountJayyidJiddan').text(countJayyidJiddan);
        $('#kpiCountJayyid').text(countJayyid);
        $('#kpiCountMaqbul').text(countMaqbul);
    }

    // Initial analytics calculation
    recalculateClassAnalytics();

    // AJAX Quick Save function
    function executeQuickSave($input, memberId, type, kode, value) {
        if (isPublished) return;

        const $cell = $input.closest('td');
        const $icon = $cell.find('.save-status-icon');

        $('#floatingSaveSpinner').show();
        $('#floatingSaveSuccess').hide();
        $('#floatingSaveText').text('Menyimpan perubahan...');

        if (type === 'komponen' || type === 'nilai') {
            applyScoreStyle($input, value);
        }

        $.ajax({
            url: quickSaveUrl,
            method: 'POST',
            data: {
                _token: csrfToken,
                asrama_pengampu_id: pengampuId,
                asrama_kelas_santri_id: memberId,
                type: type,
                kode: kode,
                value: value
            },
            success: function(res) {
                $input.data('original', value);
                $icon.fadeIn(150).delay(1200).fadeOut(300);

                if (hasComponents && res.final_score_formatted) {
                    const $badge = $(`#badge-final-${memberId}`);
                    $badge.text(res.final_score_formatted);
                    if (res.final_score !== null) {
                        $badge.removeClass('badge-light border').addClass('badge-info');
                    } else {
                        $badge.removeClass('badge-info').addClass('badge-light border');
                    }
                }

                $('#floatingSaveSpinner').hide();
                $('#floatingSaveSuccess').show();
                $('#floatingSaveText').text('Tersimpan otomatis');

                recalculateClassAnalytics();
            },
            error: function(xhr) {
                $('#floatingSaveSpinner').hide();
                $('#floatingSaveSuccess').hide();
                $('#floatingSaveText').text('Gagal menyimpan!');

                const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menyimpan nilai.';
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
            }
        });
    }

    // Component input change / blur
    $(document).on('change', 'input.input-komponen', function() {
        const $input = $(this);
        const originalVal = String($input.data('original') || '').trim();
        const newVal = $input.val().trim();
        if (originalVal === newVal) return;

        const memberId = $input.data('member');
        const kode = $input.data('kode');
        executeQuickSave($input, memberId, 'komponen', kode, newVal);
    });

    // Flat score change / blur
    $(document).on('change', 'input.input-nilai-flat', function() {
        const $input = $(this);
        const originalVal = String($input.data('original') || '').trim();
        const newVal = $input.val().trim();
        if (originalVal === newVal) return;

        const memberId = $input.data('member');
        executeQuickSave($input, memberId, 'nilai', null, newVal);
    });

    // Notes change / blur
    $(document).on('change', 'input.input-catatan', function() {
        const $input = $(this);
        const originalVal = String($input.data('original') || '').trim();
        const newVal = $input.val().trim();
        if (originalVal === newVal) return;

        const memberId = $input.data('member');
        executeQuickSave($input, memberId, 'catatan', null, newVal);
    });

    // Filter / Fokus Komponen Penilaian
    $('.btn-filter-komponen').on('click', function(){
        const kode = $(this).data('kode');
        $('.btn-filter-komponen').removeClass('active btn-info').addClass('btn-outline-info');
        $(this).removeClass('btn-outline-info').addClass('active btn-info');

        if (kode === 'all') {
            $('.th-komponen, .td-komponen').show();
        } else {
            $('.th-komponen, .td-komponen').each(function() {
                if ($(this).data('col-kode') === kode) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
    });

    // Excel-style Keyboard navigation
    $(document).on('keydown', '.smart-score-input, .input-catatan', function(e) {
        const $input = $(this);
        const currentRow = parseInt($input.data('row-index'), 10);
        const isKomponen = $input.hasClass('input-komponen');
        const kode = $input.data('kode');

        if (e.key === 'Enter' || e.key === 'ArrowDown') {
            e.preventDefault();
            let $next = null;
            if (isKomponen) {
                $next = $(`input.input-komponen[data-kode="${kode}"][data-row-index="${currentRow + 1}"]`);
            } else if ($input.hasClass('input-nilai-flat')) {
                $next = $(`input.input-nilai-flat[data-row-index="${currentRow + 1}"]`);
            } else if ($input.hasClass('input-catatan')) {
                const nextRow = $input.closest('tr').next('.santri-row:visible');
                $next = nextRow.find('.input-catatan');
            }
            if ($next && $next.length) {
                $next.focus().select();
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            let $prev = null;
            if (isKomponen) {
                $prev = $(`input.input-komponen[data-kode="${kode}"][data-row-index="${currentRow - 1}"]`);
            } else if ($input.hasClass('input-nilai-flat')) {
                $prev = $(`input.input-nilai-flat[data-row-index="${currentRow - 1}"]`);
            } else if ($input.hasClass('input-catatan')) {
                const prevRow = $input.closest('tr').prev('.santri-row:visible');
                $prev = prevRow.find('.input-catatan');
            }
            if ($prev && $prev.length) {
                $prev.focus().select();
            }
        } else if (e.key === 'ArrowRight' && !e.target.selectionStart !== e.target.selectionEnd) {
            if ($input.val().length === 0 || $input[0].selectionStart === $input.val().length) {
                const $allInputs = $input.closest('tr').find('input:visible');
                const idx = $allInputs.index($input);
                if (idx !== -1 && idx < $allInputs.length - 1) {
                    e.preventDefault();
                    $allInputs.eq(idx + 1).focus().select();
                }
            }
        } else if (e.key === 'ArrowLeft') {
            if ($input.val().length === 0 || $input[0].selectionStart === 0) {
                const $allInputs = $input.closest('tr').find('input:visible');
                const idx = $allInputs.index($input);
                if (idx > 0) {
                    e.preventDefault();
                    $allInputs.eq(idx - 1).focus().select();
                }
            }
        }
    });

    // Client-side Live Search
    function applyFilters() {
        const query = $('#santriSearchInput').val().trim().toLowerCase();
        const statusFilter = $('#filterStatusGroup button.active').data('status');
        const predFilter = $('.filter-badge.active').data('filter-pred');

        $('.santri-row').each(function() {
            const $row = $(this);
            const nama = $row.data('nama') || '';
            const induk = $row.data('induk') || '';
            const kamar = $row.data('kamar') || '';
            const status = $row.attr('data-status') || 'kosong';
            const pred = $row.attr('data-pred') || '';

            let matchQuery = true;
            if (query !== '') {
                matchQuery = nama.includes(query) || induk.includes(query) || kamar.includes(query);
            }

            let matchStatus = true;
            if (statusFilter && statusFilter !== 'all') {
                matchStatus = (status === statusFilter);
            }

            let matchPred = true;
            if (predFilter) {
                matchPred = (pred === predFilter);
            }

            if (matchQuery && matchStatus && matchPred) {
                $row.show();
            } else {
                $row.hide();
            }
        });
    }

    $('#santriSearchInput').on('input', function() {
        if ($(this).val().trim() !== '') {
            $('#clearSearchBtn').show();
        } else {
            $('#clearSearchBtn').hide();
        }
        applyFilters();
    });

    $('#clearSearchBtn').on('click', function() {
        $('#santriSearchInput').val('');
        $(this).hide();
        applyFilters();
    });

    // Status filter buttons
    $('#filterStatusGroup button').on('click', function() {
        $('#filterStatusGroup button').removeClass('active');
        $(this).addClass('active');
        $('.filter-badge').removeClass('active ring-2');
        applyFilters();
    });

    // Predicate filter badges
    $('.filter-badge').on('click', function() {
        if ($(this).hasClass('active')) {
            $(this).removeClass('active');
        } else {
            $('.filter-badge').removeClass('active');
            $(this).addClass('active');
            $('#filterStatusGroup button').removeClass('active');
            $('#filterStatusGroup button[data-status="all"]').addClass('active');
        }
        applyFilters();
    });
});
</script>
@endpush
@stop
@section('css') @include('asrama._styles') @stop

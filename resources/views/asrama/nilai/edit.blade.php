@extends('adminlte::page')
@section('title', 'Nilai ' . $pengampu->mapel->nama_latin)
@section('content')
@include('asrama._alerts')
@php
    $heroTitle = 'Input Nilai · ' . $pengampu->mapel->nama_latin;
    $heroDescription = $pengampu->kelas->nama_kelas . ' · ' . $pengampu->kelas->tahunPelajaran->nama . ' · Semester ' . $pengampu->semester;
    $heroAction = '<a class="btn btn-light" href="' . route('asrama.nilai.index', ['tahun_pelajaran_id' => $pengampu->kelas->tahun_pelajaran_id]) . '"><i class="fas fa-arrow-left mr-1"></i> Penugasan</a>';
@endphp
@include('asrama._hero')

@if($isPublished)
    <div class="alert alert-warning asrama-alert mb-3">
        <i class="fas fa-lock mr-2"></i>
        <strong>Rapor Sudah Diterbitkan:</strong> Nilai pada kelas dan semester ini sudah diterbitkan pada rapor santri. Perubahan nilai dinonaktifkan untuk menjaga validitas data rapor.
    </div>
@endif

<form method="post" action="{{ route('asrama.nilai.update', $pengampu) }}" data-asrama-loading data-loading-title="Menyimpan nilai santri" data-loading-text="Nilai sedang divalidasi dan disimpan.">
    @csrf
    @method('PUT')
    <div class="asrama-panel">
        <div class="asrama-panel__header">
            <div>
                <h3>
                    <span class="asrama-arab mr-2">{{ $pengampu->mapel->nama_arab }}</span>
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
                <button type="submit" class="btn btn-info font-weight-bold px-4">
                    <i class="fas fa-save mr-1"></i> Simpan Nilai
                </button>
            @endunless
        </div>

        @if($komponenList->isNotEmpty())
            <div class="p-3 bg-light border-bottom d-flex flex-wrap align-items-center justify-content-between">
                <div>
                    <span class="font-weight-bold text-dark mr-2"><i class="fas fa-info-circle text-info mr-1"></i> Komponen Aktif:</span>
                    @foreach($komponenList as $kmp)
                        <span class="badge badge-info px-2 py-1 mr-1">
                            {{ $kmp->nama }} [{{ $kmp->kode }}]: {{ number_format($kmp->bobot, 1) }}%
                        </span>
                    @endforeach
                </div>
                <div class="mt-2 mt-md-0 text-muted small">
                    <i class="fas fa-calculator mr-1"></i> Nilai Akhir = Σ (Nilai × Bobot) / Σ Bobot terisi
                </div>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table asrama-table mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Santri</th>
                        <th style="width: 140px;">Nomor Induk</th>
                        @if($komponenList->isNotEmpty())
                            @foreach($komponenList as $kmp)
                                <th style="width: 120px;" class="text-center">
                                    {{ $kmp->nama }}
                                    <br>
                                    <small class="text-info font-weight-bold">{{ $kmp->kode }} ({{ number_format($kmp->bobot, 1) }}%)</small>
                                </th>
                            @endforeach
                            <th style="width: 120px;" class="text-center bg-light">
                                Nilai Akhir
                                <br>
                                <small class="text-muted">(Tertimbang)</small>
                            </th>
                        @else
                            <th style="width: 150px;" class="text-center">Nilai (0–{{ $pengampu->mapel->skala_maksimum }})</th>
                        @endif
                        <th style="min-width: 180px;">Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $members = $pengampu->kelas->anggotaAktif->sortBy('santri.siswa.nama_lengkap')->values();
                    @endphp
                    @forelse($members as $index => $member)
                        @php
                            $value = $values->get($member->id);
                            $detail = is_array($value?->detail_nilai) ? $value->detail_nilai : [];
                        @endphp
                        <tr>
                            <td class="text-center">{{ $member->nomor_urut ?? $index + 1 }}</td>
                            <td>
                                <strong>{{ $member->santri->siswa->nama_lengkap }}</strong>
                                @if($member->santri->kamar)
                                    <br><small class="text-muted">{{ $member->santri->kamar->nama }}</small>
                                @endif
                            </td>
                            <td><code>{{ $member->santri->nomor_induk_asrama }}</code></td>

                            @if($komponenList->isNotEmpty())
                                @foreach($komponenList as $kmp)
                                    @php
                                        $valKomponen = old('detail_nilai.' . $member->id . '.' . $kmp->kode, $detail[$kmp->kode] ?? null);
                                    @endphp
                                    <td class="text-center">
                                        <input type="number"
                                            step="0.01"
                                            min="0"
                                            max="{{ $pengampu->mapel->skala_maksimum }}"
                                            name="detail_nilai[{{ $member->id }}][{{ $kmp->kode }}]"
                                            value="{{ $valKomponen }}"
                                            class="form-control text-center input-komponen"
                                            data-member="{{ $member->id }}"
                                            data-bobot="{{ $kmp->bobot }}"
                                            placeholder="-"
                                            @disabled($isPublished)>
                                    </td>
                                @endforeach
                                <td class="text-center bg-light align-middle">
                                    <span class="badge {{ $value?->nilai !== null ? 'badge-info' : 'badge-light border' }} px-2 py-1 final-score-badge"
                                        id="badge-final-{{ $member->id }}"
                                        style="font-size: 1rem; min-width: 55px;">
                                        {{ $value?->nilai !== null ? number_format((float) $value->nilai, 2) : '-' }}
                                    </span>
                                </td>
                            @else
                                <td>
                                    <input type="number"
                                        step=".01"
                                        min="0"
                                        max="{{ $pengampu->mapel->skala_maksimum }}"
                                        name="nilai[{{ $member->id }}]"
                                        value="{{ old('nilai.' . $member->id, $value?->nilai) }}"
                                        class="form-control text-center font-weight-bold"
                                        placeholder="0–{{ $pengampu->mapel->skala_maksimum }}"
                                        @disabled($isPublished)>
                                </td>
                            @endif

                            <td>
                                <input type="text"
                                    name="catatan[{{ $member->id }}]"
                                    value="{{ old('catatan.' . $member->id, $value?->catatan) }}"
                                    class="form-control"
                                    placeholder="Catatan perkembangan..."
                                    @disabled($isPublished)>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $komponenList->isNotEmpty() ? 5 + $komponenList->count() : 5 }}" class="asrama-empty">
                                <i class="fas fa-users"></i>
                                Belum ada santri aktif di kelas ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($members->isNotEmpty() && !$isPublished)
            <div class="asrama-panel__footer d-flex justify-content-between align-items-center p-3 bg-white border-top">
                <span class="text-muted small">
                    <i class="fas fa-check-double text-success mr-1"></i> Pastikan semua nilai telah terisi sebelum menyimpan.
                </span>
                <button type="submit" class="btn btn-info font-weight-bold px-4">
                    <i class="fas fa-save mr-1"></i> Simpan Nilai
                </button>
            </div>
        @endif
    </div>
</form>

@include('asrama._scripts')
@push('js')
<script>
$(function(){
    function recalculateRow(memberId) {
        let totalWeighted = 0;
        let totalWeights = 0;
        $(`input.input-komponen[data-member="${memberId}"]`).each(function() {
            const raw = $(this).val();
            const val = parseFloat(raw);
            const bobot = parseFloat($(this).data('bobot')) || 0;
            if (!isNaN(val) && raw !== '') {
                totalWeighted += (val * bobot);
                totalWeights += bobot;
            }
        });
        const badge = $(`#badge-final-${memberId}`);
        if (totalWeights > 0) {
            const finalScore = totalWeighted / totalWeights;
            badge.text(finalScore.toFixed(2))
                 .removeClass('badge-light border')
                 .addClass('badge-info');
        } else {
            badge.text('-')
                 .removeClass('badge-info')
                 .addClass('badge-light border');
        }
    }

    $(document).on('input change', 'input.input-komponen', function() {
        const memberId = $(this).data('member');
        recalculateRow(memberId);
    });
});
</script>
@endpush
@stop
@section('css') @include('asrama._styles') @stop

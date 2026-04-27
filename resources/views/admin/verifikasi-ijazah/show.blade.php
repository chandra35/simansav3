@extends('adminlte::page')

@section('title', 'Verifikasi Ijazah: {{ $siswa->nama_lengkap }}')

@section('css')
<style>
    .verif-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    @media(max-width:768px){ .verif-grid { grid-template-columns:1fr; } }

    .data-source-card {
        border-radius: 12px;
        border: 2px solid transparent;
        overflow: hidden;
    }
    .data-source-card.simansa  { border-color: #4e73df; }
    .data-source-card.emis     { border-color: #1cc88a; }
    .data-source-card .card-header { font-weight: 700; font-size: .85rem; padding: .5rem .9rem; }
    .data-source-card.simansa .card-header { background: #4e73df; color:#fff; }
    .data-source-card.emis    .card-header { background: #1cc88a; color:#fff; }

    .field-row {
        display: grid;
        grid-template-columns: 140px 1fr;
        gap: .25rem;
        align-items: center;
        padding: .35rem .9rem;
        border-bottom: 1px solid #f0f0f0;
        font-size: .82rem;
    }
    .field-row:last-child { border-bottom: none; }
    .field-row .label { color: #6c757d; font-weight: 600; font-size: .75rem; text-transform: uppercase; }
    .field-row .value { color: #212529; }

    .field-beda   { background: #fff3cd !important; }
    .field-beda .value { color: #856404; font-weight: 600; }
    .field-beda .label { color: #856404; }

    .check-table th, .check-table td { font-size: .82rem; vertical-align: middle; }
    .check-table .field-label { font-weight: 600; color: #495057; }

    .history-item { border-left: 3px solid #dee2e6; padding: .4rem .8rem; margin-bottom: .5rem; font-size: .82rem; }
    .history-item.created        { border-color: #4e73df; }
    .history-item.status_changed { border-color: #f6c23e; }
    .history-item.catatan_updated{ border-color: #36b9cc; }
    .history-item.data_refreshed { border-color: #1cc88a; }

    .dokumen-thumb { width:80px; height:80px; object-fit:cover; border-radius:8px; border:2px solid #dee2e6; cursor:pointer; transition: transform .15s; }
    .dokumen-thumb:hover { transform:scale(1.05); border-color:#4e73df; }
    .dokumen-link  { display:block; padding:.3rem .5rem; border-radius:6px; border:1px solid #dee2e6; font-size:.78rem; text-align:center; }

    .emis-tabs .nav-link { font-size:.8rem; padding:.3rem .75rem; }
    .emis-tabs .nav-link.active { font-weight: 700; }

    .btn-refresh-emis { font-size: .78rem; }
</style>
@endsection

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap:.5rem;">
        <div>
            <a href="{{ route('admin.verifikasi-ijazah.index') }}" class="btn btn-sm btn-outline-secondary mr-2">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <strong style="font-size:1rem;">Verifikasi Ijazah:</strong>
            <span class="text-purple font-weight-bold">{{ $siswa->nama_lengkap }}</span>
        </div>
        <div>
            @if($verifikasi)
                {!! $verifikasi->status_badge !!}
            @else
                <span class="badge badge-secondary">Belum Diverifikasi</span>
            @endif
        </div>
    </div>
@endsection

@section('content')
<div class="container-fluid px-0">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="row">
        {{-- ── KOLOM KIRI: Data Simansa & EMIS ───────────────────────────────── --}}
        <div class="col-md-8">

            {{-- Info singkat siswa --}}
            <div class="card shadow-sm mb-3">
                <div class="card-body py-2 px-3">
                    <div class="d-flex align-items-center flex-wrap" style="gap:.5rem;">
                        <div>
                            <strong>{{ $siswa->nama_lengkap }}</strong>
                            <span class="badge badge-light text-muted ml-1">{{ $siswa->nisn ?? 'No NISN' }}</span>
                            <span class="badge badge-light text-muted ml-1">{{ $siswa->kelasSaatIni?->nama_lengkap ?? $siswa->kelasSaatIni?->nama_kelas ?? 'Tanpa Kelas' }}</span>
                        </div>
                        <div class="ml-auto">
                            <button type="button" class="btn btn-sm btn-outline-success btn-refresh-emis" id="btnRefreshEmis">
                                <i class="fas fa-sync-alt mr-1"></i> Refresh Data EMIS
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- EMIS error warning --}}
            @if($emisError)
            <div class="alert alert-warning py-2">
                <i class="fas fa-exclamation-triangle mr-1"></i> <strong>EMIS:</strong> {{ $emisError }}
            </div>
            @endif

            {{-- Tabel perbandingan data --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header py-2">
                    <strong><i class="fas fa-balance-scale mr-1"></i> Perbandingan Data</strong>
                    <small class="text-muted ml-2">— field berwarna kuning = berbeda antara Simansa vs EMIS</small>
                </div>
                <div class="card-body p-0">
                    <table class="table check-table mb-0" id="compareTable">
                        <thead class="thead-light">
                            <tr>
                                <th width="160">Field</th>
                                <th>
                                    <i class="fas fa-database text-primary mr-1"></i> Simansa
                                </th>
                                <th>
                                    <i class="fas fa-cloud-download-alt text-success mr-1"></i> EMIS Kemenag
                                    <small class="text-muted">(utama)</small>
                                </th>
                                <th>
                                    <i class="fas fa-cloud text-info mr-1"></i> EMIS Kemdikbud
                                </th>
                                <th width="80" class="text-center">Centang</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($verifikasiFields as $field => $label)
                                @php
                                    $valSimansa  = $dataSimansa[$field] ?? '-';
                                    $valKemenag  = $dataEmis['kemenag'][$field]   ?? '-';
                                    $valKemdikbud = $dataEmis['kemdikbud'][$field] ?? '-';
                                    $isBeda = in_array($field, $fieldBeda);
                                    $isChecked = $verifikasi ? in_array($field, $verifikasi->field_tidak_sesuai ?? []) : $isBeda;
                                    $saranVal = $verifikasi?->saran_perbaikan[$field] ?? '';
                                @endphp
                                <tr class="{{ $isBeda ? 'table-warning' : '' }}" data-field="{{ $field }}">
                                    <td class="field-label">
                                        {{ $label }}
                                        @if($isBeda)
                                            <i class="fas fa-exclamation-circle text-warning ml-1" title="Berbeda"></i>
                                        @endif
                                    </td>
                                    <td><span class="val-simansa">{{ $valSimansa ?: '-' }}</span></td>
                                    <td>
                                        <span class="val-kemenag {{ $isBeda && $valKemenag !== '-' ? 'text-success font-weight-bold' : '' }}">
                                            {{ $valKemenag ?: '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="val-kemdikbud text-muted small">
                                            {{ $valKemdikbud ?: '-' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" class="field-check"
                                               name="field_tidak_sesuai[]"
                                               value="{{ $field }}"
                                               form="formVerifikasi"
                                               {{ $isChecked ? 'checked' : '' }}>
                                    </td>
                                </tr>
                                {{-- Baris saran perbaikan (tersembunyi jika tidak ada) --}}
                                <tr class="saran-row {{ $isChecked ? '' : 'd-none' }}" data-for="{{ $field }}">
                                    <td colspan="5" class="py-1 px-3" style="background:#fffdf0;">
                                        <div class="d-flex align-items-center" style="gap:.5rem;">
                                            <small class="text-muted" style="white-space:nowrap;">Nilai yang benar:</small>
                                            <input type="text" class="form-control form-control-sm"
                                                   name="saran_perbaikan[{{ $field }}]"
                                                   form="formVerifikasi"
                                                   value="{{ $saranVal }}"
                                                   placeholder="Isi nilai yang benar jika diketahui...">
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Form verifikasi --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header py-2">
                    <strong><i class="fas fa-pen mr-1"></i> Hasil Verifikasi</strong>
                </div>
                <div class="card-body">
                    <form id="formVerifikasi" action="{{ route('admin.verifikasi-ijazah.store', $siswa->id) }}" method="POST">
                        @csrf
                        {{-- hidden field data EMIS snapshot --}}
                        <input type="hidden" name="data_emis_kemdikbud" id="hiddenKemdikbud"
                               value="{{ json_encode($dataEmis['kemdikbud'] ?? null) }}">
                        <input type="hidden" name="data_emis_kemenag" id="hiddenKemenag"
                               value="{{ json_encode($dataEmis['kemenag'] ?? null) }}">

                        <div class="form-row">
                            <div class="form-group col-md-5">
                                <label class="font-weight-bold" style="font-size:.82rem;">Status Verifikasi <span class="text-danger">*</span></label>
                                <select name="status" class="form-control" id="statusSelect" required>
                                    <option value="">-- Pilih Status --</option>
                                    <option value="sesuai" {{ ($verifikasi?->status === 'sesuai') ? 'selected' : '' }}>
                                        ✅ Sesuai — data cocok dengan ijazah/KK
                                    </option>
                                    <option value="tidak_sesuai" {{ ($verifikasi?->status === 'tidak_sesuai') ? 'selected' : '' }}>
                                        ❌ Tidak Sesuai — ada perbedaan data
                                    </option>
                                    <option value="perlu_perbaikan" {{ ($verifikasi?->status === 'perlu_perbaikan') ? 'selected' : '' }}>
                                        ⚠️ Perlu Perbaikan — butuh koreksi di Vervalpd
                                    </option>
                                </select>
                            </div>
                            <div class="form-group col-md-7">
                                <label class="font-weight-bold" style="font-size:.82rem;">Catatan</label>
                                <textarea name="catatan" class="form-control" rows="2"
                                          placeholder="Tuliskan catatan verifikasi, field yang berbeda, atau saran perbaikan...">{{ $verifikasi?->catatan }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex align-items-center" style="gap:.5rem;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Simpan Verifikasi
                            </button>
                            <span class="text-muted small">
                                Centang field yang tidak sesuai di tabel di atas, lalu pilih status dan simpan.
                            </span>
                        </div>
                    </form>
                </div>
            </div>

        </div>

        {{-- ── KOLOM KANAN: Dokumen + History ──────────────────────────────── --}}
        <div class="col-md-4">

            {{-- Dokumen Ijazah --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header py-2">
                    <strong><i class="fas fa-file-alt text-danger mr-1"></i> Dokumen Ijazah SMP/MTs</strong>
                </div>
                <div class="card-body py-2">
                    @if($dokumenIjazah->isEmpty())
                        <p class="text-muted small mb-0"><i class="fas fa-exclamation-triangle text-warning mr-1"></i> Belum ada dokumen ijazah diupload.</p>
                    @else
                        <div class="d-flex flex-wrap" style="gap:.5rem;">
                            @foreach($dokumenIjazah as $dok)
                                @php
                                    $ext = strtolower(pathinfo($dok->original_name ?? $dok->nama_file, PATHINFO_EXTENSION));
                                    $isImg = in_array($ext, ['jpg','jpeg','png','webp']);
                                @endphp
                                @if($isImg)
                                    <a href="{{ route('admin.siswa.dokumen', $siswa->id) }}" target="_blank">
                                        <div class="text-center">
                                            <div class="dokumen-link" style="width:80px;">
                                                <i class="fas fa-file-image fa-2x text-primary"></i><br>
                                                <span style="font-size:.7rem;">{{ Str::limit($dok->original_name ?? $dok->nama_file, 12) }}</span>
                                            </div>
                                        </div>
                                    </a>
                                @else
                                    <a href="{{ route('admin.siswa.dokumen', $siswa->id) }}" target="_blank" class="dokumen-link" style="width:80px;">
                                        <i class="fas fa-file-pdf fa-2x text-danger"></i><br>
                                        <span>{{ Str::limit($dok->original_name ?? $dok->nama_file, 12) }}</span>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Dokumen KK --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header py-2">
                    <strong><i class="fas fa-users text-success mr-1"></i> Kartu Keluarga (KK)</strong>
                </div>
                <div class="card-body py-2">
                    @if($dokumenKK->isEmpty())
                        <p class="text-muted small mb-0"><i class="fas fa-exclamation-triangle text-warning mr-1"></i> Belum ada dokumen KK diupload.</p>
                    @else
                        <div class="d-flex flex-wrap" style="gap:.5rem;">
                            @foreach($dokumenKK as $dok)
                                <a href="{{ route('admin.siswa.dokumen', $siswa->id) }}" target="_blank" class="dokumen-link" style="width:80px;">
                                    <i class="fas fa-id-card fa-2x text-success"></i><br>
                                    <span>{{ Str::limit($dok->original_name ?? $dok->nama_file, 12) }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                    <div class="mt-2">
                        <a href="{{ route('admin.siswa.dokumen', $siswa->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary btn-sm">
                            <i class="fas fa-folder-open mr-1"></i> Lihat Semua Dokumen
                        </a>
                    </div>
                </div>
            </div>

            {{-- History Verifikasi --}}
            <div class="card shadow-sm">
                <div class="card-header py-2">
                    <strong><i class="fas fa-history mr-1"></i> Riwayat Verifikasi</strong>
                </div>
                <div class="card-body py-2" style="max-height:320px;overflow-y:auto;">
                    @if($verifikasi && $verifikasi->logs->isNotEmpty())
                        @foreach($verifikasi->logs as $log)
                            <div class="history-item {{ $log->aksi }}">
                                <div class="d-flex justify-content-between">
                                    <strong>{{ $log->aksi_label }}</strong>
                                    <small class="text-muted">{{ $log->created_at->format('d/m/Y H:i') }}</small>
                                </div>
                                <div>
                                    <small class="text-primary"><i class="fas fa-user mr-1"></i>{{ $log->user_nama }}</small>
                                </div>
                                @if($log->status_lama && $log->status_baru && $log->status_lama !== $log->status_baru)
                                    <div class="mt-1">
                                        <span class="badge badge-secondary" style="font-size:.7rem;">{{ $log->status_lama }}</span>
                                        <i class="fas fa-arrow-right text-muted mx-1" style="font-size:.7rem;"></i>
                                        <span class="badge badge-primary" style="font-size:.7rem;">{{ $log->status_baru }}</span>
                                    </div>
                                @endif
                                @if($log->keterangan)
                                    <p class="mb-0 mt-1 text-muted" style="font-size:.78rem;">{{ $log->keterangan }}</p>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted small mb-0">Belum ada riwayat verifikasi.</p>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(function () {
    // Toggle saran-perbaikan row saat checkbox berubah
    $(document).on('change', '.field-check', function () {
        const field = $(this).val();
        const $saran = $('[data-for="' + field + '"]');
        if ($(this).is(':checked')) {
            $saran.removeClass('d-none');
        } else {
            $saran.addClass('d-none');
            $saran.find('input[type=text]').val('');
        }
    });

    // Refresh EMIS
    $('#btnRefreshEmis').on('click', function () {
        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Mengambil data...');

        $.ajax({
            url: '{{ route("admin.verifikasi-ijazah.refresh-emis", $siswa->id) }}',
            method: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function (res) {
                if (res.success) {
                    // Update hidden inputs
                    $('#hiddenKemdikbud').val(JSON.stringify(res.kemdikbud));
                    $('#hiddenKemenag').val(JSON.stringify(res.kemenag));

                    // Update tampilan nilai EMIS di tabel
                    if (res.kemenag) {
                        $.each(res.kemenag, function (field, val) {
                            if (field === 'raw') return;
                            $('[data-field="' + field + '"] .val-kemenag').text(val || '-');
                        });
                    }
                    if (res.kemdikbud) {
                        $.each(res.kemdikbud, function (field, val) {
                            if (field === 'raw') return;
                            $('[data-field="' + field + '"] .val-kemdikbud').text(val || '-');
                        });
                    }

                    // Highlight field beda
                    $('#compareTable tbody tr[data-field]').removeClass('table-warning');
                    $.each(res.field_beda, function (_, field) {
                        $('[data-field="' + field + '"]').addClass('table-warning');
                    });

                    toastr.success('Data EMIS berhasil diperbarui.');
                } else {
                    toastr.error(res.message || 'Gagal mengambil data EMIS.');
                }
            },
            error: function () {
                toastr.error('Terjadi kesalahan saat menghubungi server.');
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="fas fa-sync-alt mr-1"></i> Refresh Data EMIS');
            }
        });
    });

    // Auto-pilih status sesuai kondisi checkbox
    $('#formVerifikasi').on('submit', function () {
        const hasChecked = $('.field-check:checked').length > 0;
        const status = $('#statusSelect').val();
        if (!status) {
            toastr.warning('Pilih status verifikasi terlebih dahulu.');
            return false;
        }
        return true;
    });
});
</script>
@endsection

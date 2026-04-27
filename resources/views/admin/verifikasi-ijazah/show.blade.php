@extends('adminlte::page')

@section('title', 'Verifikasi Ijazah: {{ $siswa->nama_lengkap }}')

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css">
<style>
/* ── Student Header ─────────────────────────── */
.student-header {
    background: linear-gradient(135deg, #1a237e 0%, #283593 60%, #3949ab 100%);
    border-radius: 12px;
    color: #fff;
    padding: 1rem 1.25rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}
.student-avatar {
    width: 52px; height: 52px;
    border-radius: 50%;
    background: rgba(255,255,255,.2);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; font-weight: 700; color: #fff;
    flex-shrink: 0;
    border: 2px solid rgba(255,255,255,.4);
}
.student-header .name   { font-size: 1.05rem; font-weight: 700; line-height: 1.2; }
.student-header .meta   { font-size: .8rem; opacity: .85; margin-top: .15rem; }
.student-header .badges { display: flex; gap: .4rem; flex-wrap: wrap; margin-top: .35rem; }
.student-header .s-badge {
    background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.35);
    border-radius: 20px; padding: .15rem .65rem; font-size: .73rem; font-weight: 600;
}

/* ── EMIS alert ─────────────────────────────── */
.emis-alert-warn {
    background: #fff8e1; border-left: 4px solid #f59e0b;
    border-radius: 8px; padding: .6rem .9rem;
    font-size: .82rem; color: #78350f;
    margin-bottom: 1rem;
}

/* ── Compare Table ──────────────────────────── */
.compare-card { border-radius: 12px; overflow: hidden; box-shadow: 0 1px 6px rgba(0,0,0,.07); margin-bottom: 1rem; }
.compare-card .card-head {
    background: #f8f9fa; border-bottom: 1px solid #e9ecef;
    padding: .6rem 1rem;
    display: flex; align-items: center; justify-content: space-between;
}
.compare-card .card-head strong { font-size: .85rem; }
.compare-card .legend { font-size: .73rem; color: #6c757d; }

.ctable { width: 100%; border-collapse: collapse; font-size: .82rem; }
.ctable th {
    font-size: .72rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .03em; padding: .5rem .75rem;
    border-bottom: 2px solid #dee2e6;
    white-space: nowrap;
}
.ctable th.th-field    { color: #6c757d; background: #f8f9fa; width: 145px; }
.ctable th.th-simansa  { color: #1e40af; background: #eff6ff; }
.ctable th.th-kemenag  { color: #065f46; background: #f0fdf4; }
.ctable th.th-kemdikbud{ color: #374151; background: #f9fafb; }
.ctable th.th-lembaga  { color: #5b21b6; background: #f5f3ff; }
.ctable th.th-check    { background: #f8f9fa; text-align: center; width: 56px; }

.ctable td {
    padding: .45rem .75rem;
    border-bottom: 1px solid #f0f0f0;
    vertical-align: middle;
}
.ctable tr:last-child td { border-bottom: none; }
.ctable .td-field {
    font-weight: 600; color: #374151; font-size: .78rem;
    background: #fafafa;
}
.ctable .td-simansa { color: #1e3a5f; }
.ctable .td-kemenag { color: #064e3b; }
.ctable .td-kemdikbud { color: #6b7280; font-size: .78rem; }
.ctable .td-lembaga { color: #5b21b6; font-size: .78rem; }

/* Beda row */
.row-beda .td-field    { background: #fef9c3 !important; color: #78350f; }
.row-beda .td-simansa  { background: #fff7ed; }
.row-beda .td-kemenag  { background: #f0fdf4; }
.row-beda .td-kemdikbud{ background: #fafafa; }
.row-beda .td-lembaga  { background: #faf5ff; }
.row-beda .td-check    { background: #fef9c3 !important; }
.row-beda .beda-icon   { color: #f59e0b; margin-left: 4px; }
.row-beda .val-kemenag { font-weight: 700; color: #065f46; }

/* Saran row */
.saran-row td {
    background: #fffde7 !important;
    padding: .3rem .75rem .45rem 145px !important;
    border-bottom: 1px solid #fde68a;
}
.saran-row .saran-inner {
    display: flex; align-items: center; gap: .5rem;
}
.saran-row .saran-label { font-size: .72rem; color: #92400e; white-space: nowrap; font-weight: 600; }
.saran-row input { font-size: .78rem; border-color: #fcd34d; background: #fffbeb; }
.saran-row input:focus { border-color: #f59e0b; box-shadow: 0 0 0 2px rgba(245,158,11,.2); }

.ctable .td-check { text-align: center; }
.ctable .td-check input[type=checkbox] { width: 16px; height: 16px; cursor: pointer; accent-color: #f59e0b; }

/* dash value */
.dash { color: #d1d5db; }

/* ── Action Card ────────────────────────────── */
.action-card {
    border-radius: 12px; overflow: hidden;
    box-shadow: 0 1px 6px rgba(0,0,0,.07);
    margin-bottom: 1rem;
    border: none;
}
.action-card .card-header {
    background: #f8f9fa; border-bottom: 1px solid #e9ecef;
    padding: .6rem 1rem; font-size: .85rem;
}

/* ── Right Sidebar ──────────────────────────── */
.side-card {
    border-radius: 12px; overflow: hidden;
    box-shadow: 0 1px 6px rgba(0,0,0,.07);
    margin-bottom: 1rem; border: none;
}
.side-card .card-header {
    padding: .55rem 1rem; font-size: .8rem; font-weight: 700;
    border-bottom: 1px solid #f0f0f0;
}
.dok-item {
    display: flex; align-items: center; gap: .65rem;
    padding: .45rem .9rem; border-bottom: 1px solid #f4f4f4;
    font-size: .8rem; text-decoration: none; color: #374151;
    transition: background .15s;
}
.dok-item:last-child { border-bottom: none; }
.dok-item:hover { background: #f0f9ff; color: #1d4ed8; text-decoration: none; }
.dok-icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1rem; }
.dok-icon.img  { background: #dbeafe; color: #1d4ed8; }
.dok-icon.pdf  { background: #fee2e2; color: #dc2626; }
.dok-icon.other{ background: #e5e7eb; color: #6b7280; }
.dok-empty     { padding: .5rem 1rem; font-size: .78rem; color: #9ca3af; }
.dok-section-title {
    font-size: .7rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .05em; color: #6b7280;
    padding: .35rem 1rem;
    background: #f8f9fa; border-bottom: 1px solid #f0f0f0;
    border-top: 1px solid #f0f0f0;
    display: flex; justify-content: space-between; align-items: center;
}
.dok-section-title:first-of-type { border-top: none; }
.dok-info { flex: 1; min-width: 0; }
.dok-name { font-size: .78rem; font-weight: 600; color: #374151;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.dok-meta { font-size: .68rem; color: #9ca3af; margin-top: 2px; }
.dok-actions { display: flex; gap: .25rem; flex-shrink: 0; }
.dok-actions .btn-dok {
    width: 26px; height: 26px; border-radius: 6px; border: none;
    display: flex; align-items: center; justify-content: center;
    font-size: .7rem; cursor: pointer; transition: all .15s;
    text-decoration: none;
}
.btn-dok.view     { background: #eff6ff; color: #1d4ed8; }
.btn-dok.view:hover { background: #1d4ed8; color: #fff; }
.btn-dok.dl       { background: #f0fdf4; color: #16a34a; }
.btn-dok.dl:hover { background: #16a34a; color: #fff; }

/* Timeline history */
.tl-item { display: flex; gap: .75rem; padding: .5rem 1rem; border-bottom: 1px solid #f4f4f4; }
.tl-item:last-child { border-bottom: none; }
.tl-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; margin-top: 4px; }
.tl-dot.created        { background: #3b82f6; }
.tl-dot.status_changed { background: #f59e0b; }
.tl-dot.catatan_updated{ background: #06b6d4; }
.tl-dot.data_refreshed { background: #10b981; }
.tl-body { flex: 1; }
.tl-action { font-size: .78rem; font-weight: 600; color: #374151; }
.tl-meta   { font-size: .72rem; color: #9ca3af; margin-top: 1px; }
.tl-status { font-size: .7rem; display: inline-block; margin-top: .2rem; }
.tl-note   { font-size: .72rem; color: #6b7280; margin-top: .2rem; }

/* Refresh button */
.btn-refresh {
    font-size: .78rem; padding: .3rem .8rem;
    border: 1.5px solid rgba(255,255,255,.5);
    color: #fff; background: transparent; border-radius: 20px;
    transition: all .15s;
}
.btn-refresh:hover { background: rgba(255,255,255,.15); color: #fff; }
.btn-refresh:disabled { opacity: .6; }
</style>
@endsection

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-certificate"></i> Verifikasi Ijazah SMP/MTs</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('admin.verifikasi-ijazah.index') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left"></i> Kembali ke Verifikasi Ijazah
                </a>
            </div>
        </div>
    </div>
@endsection

@section('content')
<div class="container-fluid px-0">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="row">
        {{-- ══ KIRI ══════════════════════════════════════════════════════════ --}}
        <div class="col-md-8">

            {{-- Student Header --}}
            <div class="student-header">
                <div class="student-avatar">
                    {{ strtoupper(substr($siswa->nama_lengkap, 0, 1)) }}
                </div>
                <div class="flex-grow-1">
                    <div class="name">{{ $siswa->nama_lengkap }}</div>
                    <div class="meta">NIK: {{ $siswa->nik ?? '-' }}</div>
                    <div class="badges">
                        <span class="s-badge"><i class="fas fa-hashtag mr-1" style="opacity:.7;"></i>{{ $siswa->nisn ?? 'No NISN' }}</span>
                        <span class="s-badge"><i class="fas fa-school mr-1" style="opacity:.7;"></i>{{ $siswa->kelasSaatIni?->nama_kelas ?? 'Tanpa Kelas' }}</span>
                        @if($verifikasi)
                            <span class="s-badge">{!! $verifikasi->status_badge !!}</span>
                        @else
                            <span class="badge badge-secondary" style="font-size:.72rem;">Belum Diverifikasi</span>
                        @endif
                    </div>
                </div>
                <div class="flex-shrink-0">
                    <button type="button" class="btn-refresh" id="btnRefreshEmis">
                        <i class="fas fa-sync-alt mr-1"></i> Refresh EMIS
                    </button>
                </div>
            </div>

            {{-- EMIS Warning --}}
            @if($emisError)
            <div class="emis-alert-warn">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                <strong>EMIS:</strong> {{ $emisError }}
            </div>
            @endif

            {{-- Tabel Perbandingan --}}
            <div class="compare-card">
                <div class="card-head">
                    <strong><i class="fas fa-balance-scale text-secondary mr-1"></i> Perbandingan Data</strong>
                    <span class="legend"><span style="display:inline-block;width:10px;height:10px;background:#fef9c3;border:1px solid #fcd34d;border-radius:2px;margin-right:4px;"></span>Berbeda antara Simansa &amp; EMIS</span>
                </div>
                <div style="overflow-x:auto;">
                    <table class="ctable" id="compareTable">
                        <thead>
                            <tr>
                                <th class="th-field">Field</th>
                                <th class="th-simansa"><i class="fas fa-database mr-1"></i>Simansa</th>
                                <th class="th-kemenag"><i class="fas fa-cloud-download-alt mr-1"></i>EMIS Kemenag</th>
                                <th class="th-kemdikbud"><i class="fas fa-cloud mr-1"></i>EMIS Kemdikbud</th>
                                @if($dataEmisLembaga)
                                <th class="th-lembaga"><i class="fas fa-school mr-1"></i>EMIS Lembaga</th>
                                @endif
                                <th class="th-check"><i class="fas fa-exclamation-circle text-warning"></i></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($verifikasiFields as $field => $label)
                                @php
                                    $valSimansa   = $dataSimansa[$field]              ?? null;
                                    $valKemenag   = $dataEmis['kemenag'][$field]      ?? null;
                                    $valKemdikbud = $dataEmis['kemdikbud'][$field]    ?? null;
                                    $valLembaga   = $dataEmisLembaga[$field]          ?? null;
                                    $isBeda   = in_array($field, $fieldBeda);
                                    $isChecked = $verifikasi ? in_array($field, $verifikasi->field_tidak_sesuai ?? []) : $isBeda;
                                    $saranVal  = $verifikasi?->saran_perbaikan[$field] ?? '';
                                    $colspan   = $dataEmisLembaga ? 6 : 5;
                                @endphp
                                <tr class="{{ $isBeda ? 'row-beda' : '' }}" data-field="{{ $field }}">
                                    <td class="td-field">
                                        {{ $label }}
                                        @if($isBeda)<i class="fas fa-circle beda-icon" style="font-size:.45rem;vertical-align:middle;"></i>@endif
                                    </td>
                                    <td class="td-simansa val-simansa">
                                        @if($valSimansa)<span>{{ $valSimansa }}</span>@else<span class="dash">—</span>@endif
                                    </td>
                                    <td class="td-kemenag">
                                        <span class="val-kemenag">
                                            @if($valKemenag){{ $valKemenag }}@else<span class="dash">—</span>@endif
                                        </span>
                                    </td>
                                    <td class="td-kemdikbud">
                                        <span class="val-kemdikbud">
                                            @if($valKemdikbud){{ $valKemdikbud }}@else<span class="dash">—</span>@endif
                                        </span>
                                    </td>
                                    @if($dataEmisLembaga)
                                    <td class="td-lembaga">
                                        <span class="val-lembaga">
                                            @if($valLembaga){{ $valLembaga }}@else<span class="dash">—</span>@endif
                                        </span>
                                    </td>
                                    @endif
                                    <td class="td-check">
                                        <input type="checkbox" class="field-check"
                                               name="field_tidak_sesuai[]"
                                               value="{{ $field }}"
                                               form="formVerifikasi"
                                               {{ $isChecked ? 'checked' : '' }}
                                               title="Tandai tidak sesuai">
                                    </td>
                                </tr>
                                <tr class="saran-row {{ $isChecked ? '' : 'd-none' }}" data-for="{{ $field }}">
                                    <td colspan="{{ $colspan }}">
                                        <div class="saran-inner">
                                            <span class="saran-label"><i class="fas fa-pen mr-1"></i>Nilai benar:</span>
                                            <input type="text" class="form-control form-control-sm"
                                                   name="saran_perbaikan[{{ $field }}]"
                                                   form="formVerifikasi"
                                                   value="{{ $saranVal }}"
                                                   placeholder="Tulis nilai yang seharusnya...">
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Form Verifikasi --}}
            <div class="action-card card">
                <div class="card-header">
                    <strong><i class="fas fa-clipboard-check text-primary mr-1"></i> Hasil Verifikasi</strong>
                </div>
                <div class="card-body">
                    <form id="formVerifikasi" action="{{ route('admin.verifikasi-ijazah.store', $siswa->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="data_emis_kemdikbud" id="hiddenKemdikbud" value="{{ json_encode($dataEmis['kemdikbud'] ?? null) }}">
                        <input type="hidden" name="data_emis_kemenag"   id="hiddenKemenag"   value="{{ json_encode($dataEmis['kemenag']   ?? null) }}">
                        <input type="hidden" name="data_emis_lembaga"   id="hiddenLembaga"   value="{{ json_encode($dataEmisLembaga        ?? null) }}">

                        <div class="form-row">
                            <div class="form-group col-md-5 mb-2">
                                <label class="font-weight-bold" style="font-size:.8rem;">Status Verifikasi <span class="text-danger">*</span></label>
                                <select name="status" class="form-control form-control-sm" id="statusSelect" required>
                                    <option value="">— Pilih Status —</option>
                                    <option value="sesuai"           {{ ($verifikasi?->status==='sesuai')          ? 'selected':'' }}>✅ Sesuai</option>
                                    <option value="tidak_sesuai"     {{ ($verifikasi?->status==='tidak_sesuai')    ? 'selected':'' }}>❌ Tidak Sesuai</option>
                                    <option value="perlu_perbaikan"  {{ ($verifikasi?->status==='perlu_perbaikan') ? 'selected':'' }}>⚠️ Perlu Perbaikan</option>
                                </select>
                            </div>
                            <div class="form-group col-md-7 mb-2">
                                <label class="font-weight-bold" style="font-size:.8rem;">Catatan</label>
                                <textarea name="catatan" class="form-control form-control-sm" rows="2"
                                          placeholder="Catatan verifikasi, field yang berbeda, atau saran perbaikan...">{{ $verifikasi?->catatan }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex align-items-center flex-wrap" style="gap:.5rem;">
                            <button type="submit" class="btn btn-primary btn-sm px-4">
                                <i class="fas fa-save mr-1"></i> Simpan Verifikasi
                            </button>
                            <small class="text-muted">Centang baris yang tidak sesuai, pilih status, lalu simpan.</small>
                        </div>
                    </form>
                </div>
            </div>

        </div>

        {{-- ══ KANAN ══════════════════════════════════════════════════════════ --}}
        <div class="col-md-4">

            {{-- Dokumen Pendukung --}}
            @php
                $totalDok = $dokumenIjazah->count() + $dokumenKK->count() + ($dokumenLain->count() ?? 0);
            @endphp
            <div class="side-card card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-folder-open text-warning mr-1"></i> Dokumen Pendukung</span>
                    @if($totalDok > 0)
                        <span class="badge badge-warning text-dark">{{ $totalDok }} file</span>
                    @endif
                </div>
                <div>
                    {{-- Ijazah SMP/MTs --}}
                    <div class="dok-section-title">
                        <span><i class="fas fa-file-alt text-danger mr-1"></i> Ijazah SMP/MTs</span>
                        @if($dokumenIjazah->isNotEmpty())<span class="badge badge-danger" style="font-size:.65rem;">{{ $dokumenIjazah->count() }}</span>@endif
                    </div>
                    @if($dokumenIjazah->isEmpty())
                        <div class="dok-empty"><i class="fas fa-minus-circle text-muted mr-1"></i> Belum diunggah</div>
                    @else
                        @foreach($dokumenIjazah as $dok)
                            @php
                                $ext  = $dok->getFileExtension();
                                $isPdf = $ext === 'pdf';
                                $isImg = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                                $iconClass = $isPdf ? 'pdf fas fa-file-pdf' : ($isImg ? 'img fas fa-file-image' : 'other fas fa-file-alt');
                                [$iconBg, $iconIcon] = explode(' ', $iconClass . ' x', 3);
                                $fileUrl  = $dok->getFileUrl();
                                $fileName = Str::limit($dok->original_name ?? $dok->nama_file, 30);
                                $fileMeta = strtoupper($ext) . ($dok->file_size ? ' · ' . $dok->getFileSizeFormatted() : '');
                                $fileDate = $dok->created_at?->format('d/m/Y');
                            @endphp
                            <div class="dok-item">
                                <div class="dok-icon {{ $isPdf ? 'pdf' : ($isImg ? 'img' : 'other') }}">
                                    <i class="fas {{ $isPdf ? 'fa-file-pdf' : ($isImg ? 'fa-file-image' : 'fa-file-alt') }}"></i>
                                </div>
                                <div class="dok-info">
                                    <div class="dok-name" title="{{ $dok->original_name ?? $dok->nama_file }}">{{ $fileName }}</div>
                                    <div class="dok-meta">{{ $fileMeta }}@if($fileDate) · {{ $fileDate }}@endif</div>
                                </div>
                                <div class="dok-actions">
                                    <a href="{{ $fileUrl }}" target="_blank" class="btn-dok view" title="Lihat"><i class="fas fa-eye"></i></a>
                                </div>
                            </div>
                        @endforeach
                    @endif

                    {{-- Kartu Keluarga --}}
                    <div class="dok-section-title">
                        <span><i class="fas fa-id-card text-success mr-1"></i> Kartu Keluarga</span>
                        @if($dokumenKK->isNotEmpty())<span class="badge badge-success" style="font-size:.65rem;">{{ $dokumenKK->count() }}</span>@endif
                    </div>
                    @if($dokumenKK->isEmpty())
                        <div class="dok-empty"><i class="fas fa-minus-circle text-muted mr-1"></i> Belum diunggah</div>
                    @else
                        @foreach($dokumenKK as $dok)
                            @php
                                $ext  = $dok->getFileExtension();
                                $isPdf = $ext === 'pdf';
                                $isImg = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                                $fileUrl  = $dok->getFileUrl();
                                $fileName = Str::limit($dok->original_name ?? $dok->nama_file, 30);
                                $fileMeta = strtoupper($ext) . ($dok->file_size ? ' · ' . $dok->getFileSizeFormatted() : '');
                                $fileDate = $dok->created_at?->format('d/m/Y');
                            @endphp
                            <div class="dok-item">
                                <div class="dok-icon {{ $isPdf ? 'pdf' : ($isImg ? 'img' : 'other') }}">
                                    <i class="fas {{ $isPdf ? 'fa-file-pdf' : ($isImg ? 'fa-file-image' : 'fa-file-alt') }}"></i>
                                </div>
                                <div class="dok-info">
                                    <div class="dok-name" title="{{ $dok->original_name ?? $dok->nama_file }}">{{ $fileName }}</div>
                                    <div class="dok-meta">{{ $fileMeta }}@if($fileDate) · {{ $fileDate }}@endif</div>
                                </div>
                                <div class="dok-actions">
                                    <a href="{{ $fileUrl }}" target="_blank" class="btn-dok view" title="Lihat"><i class="fas fa-eye"></i></a>
                                </div>
                            </div>
                        @endforeach
                    @endif

                    {{-- Lainnya (jika ada) --}}
                    @if(isset($dokumenLain) && $dokumenLain->isNotEmpty())
                        <div class="dok-section-title">
                            <span><i class="fas fa-paperclip text-secondary mr-1"></i> Dokumen Lain</span>
                            <span class="badge badge-secondary" style="font-size:.65rem;">{{ $dokumenLain->count() }}</span>
                        </div>
                        @foreach($dokumenLain as $dok)
                            @php
                                $ext  = $dok->getFileExtension();
                                $isPdf = $ext === 'pdf';
                                $isImg = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                                $fileUrl  = $dok->getFileUrl();
                                $fileName = Str::limit($dok->original_name ?? $dok->nama_file, 30);
                                $fileMeta = $dok->getJenisDokumenLabel() . ($dok->file_size ? ' · ' . $dok->getFileSizeFormatted() : '');
                            @endphp
                            <div class="dok-item">
                                <div class="dok-icon {{ $isPdf ? 'pdf' : ($isImg ? 'img' : 'other') }}">
                                    <i class="fas {{ $isPdf ? 'fa-file-pdf' : ($isImg ? 'fa-file-image' : 'fa-file-alt') }}"></i>
                                </div>
                                <div class="dok-info">
                                    <div class="dok-name" title="{{ $dok->original_name ?? $dok->nama_file }}">{{ $fileName }}</div>
                                    <div class="dok-meta">{{ $fileMeta }}</div>
                                </div>
                                <div class="dok-actions">
                                    <a href="{{ $fileUrl }}" target="_blank" class="btn-dok view" title="Lihat"><i class="fas fa-eye"></i></a>
                                </div>
                            </div>
                        @endforeach
                    @endif

                    {{-- Footer --}}
                    <div style="padding:.6rem 1rem;border-top:1px solid #f0f0f0;">
                        <a href="{{ route('siswa.dokumen', $siswa->id) }}" target="_blank"
                           class="btn btn-sm btn-outline-secondary btn-block" style="font-size:.78rem;">
                            <i class="fas fa-folder-open mr-1"></i> Kelola Semua Dokumen
                        </a>
                    </div>
                </div>
            </div>

            {{-- Riwayat Verifikasi --}}
            <div class="side-card card">
                <div class="card-header">
                    <i class="fas fa-history text-secondary mr-1"></i> Riwayat Verifikasi
                </div>
                <div style="max-height:320px;overflow-y:auto;">
                    @if($verifikasi && $verifikasi->logs->isNotEmpty())
                        @foreach($verifikasi->logs as $log)
                            <div class="tl-item">
                                <div class="tl-dot {{ $log->aksi }}"></div>
                                <div class="tl-body">
                                    <div class="tl-action">{{ $log->aksi_label }}</div>
                                    <div class="tl-meta">
                                        <i class="fas fa-user mr-1"></i>{{ $log->user_nama }}
                                        &nbsp;·&nbsp;{{ $log->created_at->format('d/m/Y H:i') }}
                                    </div>
                                    @if($log->status_lama && $log->status_baru && $log->status_lama !== $log->status_baru)
                                        <div class="tl-status">
                                            <span class="badge badge-secondary" style="font-size:.65rem;">{{ $log->status_lama }}</span>
                                            <i class="fas fa-arrow-right text-muted mx-1" style="font-size:.6rem;"></i>
                                            <span class="badge badge-primary" style="font-size:.65rem;">{{ $log->status_baru }}</span>
                                        </div>
                                    @endif
                                    @if($log->keterangan)
                                        <div class="tl-note">{{ $log->keterangan }}</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="dok-empty">Belum ada riwayat verifikasi.</div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>
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
            timeout: 35000,
            data: { _token: '{{ csrf_token() }}' },
            success: function (res) {
                if (res.success) {
                    // Update hidden inputs
                    $('#hiddenKemdikbud').val(JSON.stringify(res.kemdikbud));
                    $('#hiddenKemenag').val(JSON.stringify(res.kemenag));
                    if (res.lembaga) {
                        $('#hiddenLembaga').val(JSON.stringify(res.lembaga));
                    }

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
                    if (res.lembaga) {
                        $.each(res.lembaga, function (field, val) {
                            if (field === 'raw') return;
                            $('[data-field="' + field + '"] .val-lembaga').text(val || '-');
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

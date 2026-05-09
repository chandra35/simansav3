@extends('adminlte::page')

@section('title', 'Timetable Jadwal' . ($kelasObj ? ' – ' . $kelasObj->nama_kelas : ''))

@section('plugins.Sweetalert2', true)

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-7">
            <h1><i class="fas fa-table"></i> Timetable Jadwal</h1>
        </div>
        <div class="col-sm-5">
            <div class="float-sm-right d-flex gap-2" style="gap:.5rem">
                @can('manage-jadwal-pelajaran')
                <a href="{{ route('admin.jadwal-jam-config.index', ['tahun_pelajaran_id' => $tahunId]) }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-sliders-h"></i> Konfigurasi Jam
                </a>
                @endcan
                <a href="{{ route('admin.jadwal-pelajaran.index', ['tahun_pelajaran_id' => $tahunId]) }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Daftar Kelas
                </a>
            </div>
        </div>
    </div>
@endsection

@section('content')

{{-- ===== KELAS INFO BAR ===== --}}
@if($kelasObj)
<div class="simansa-tt-infobar">
    <div class="simansa-tt-infobar__main">
        <div class="simansa-tt-infobar__icon"><i class="fas fa-chalkboard"></i></div>
        <div>
            <div class="simansa-tt-infobar__title">{{ $kelasObj->nama_kelas }}</div>
            <div class="simansa-tt-infobar__sub">
                @if($kelasObj->jurusan) <span>{{ $kelasObj->jurusan->nama_jurusan }}</span> @endif
                @if($kelasObj->tahunPelajaran) <span>&middot; {{ $kelasObj->tahunPelajaran->tahun_pelajaran }}</span> @endif
                <span>&middot; Semester <strong>{{ $semester == 1 ? '1 (Ganjil)' : '2 (Genap)' }}</strong></span>
            </div>
        </div>
    </div>
    <div class="simansa-tt-infobar__chips">
        @php $totalSlotKelas = collect($jadwalMap)->flatten(1)->count(); @endphp
        <div class="simansa-tt-chip">
            <span class="simansa-tt-chip__label">Slot Terisi</span>
            <strong>{{ $totalSlotKelas }}</strong>
        </div>
        <div class="simansa-tt-chip">
            <span class="simansa-tt-chip__label">Jam Tersedia</span>
            <strong>{{ $jamConfig->where('is_istirahat', false)->count() }}</strong>
        </div>
        <div class="simansa-tt-chip simansa-tt-chip--sem">
            <span class="simansa-tt-chip__label">Semester</span>
            <strong>{{ $semester }}</strong>
        </div>
    </div>
</div>
@endif

{{-- ===== FILTER BAR ===== --}}
<div class="simansa-jadwal-panel mb-3">
    <div class="simansa-jadwal-panel__body" style="padding:.9rem 1.35rem">
        <form method="GET" action="{{ route('admin.jadwal-pelajaran.timetable') }}" id="formFilter">
            <div class="form-row align-items-end">
                <div class="form-group col-md-3 mb-md-0">
                    <label class="simansa-jadwal-label">Tahun Pelajaran</label>
                    <select name="tahun_pelajaran_id" class="form-control form-control-sm select2" id="ftTahun">
                        @foreach($tahunList as $t)
                            <option value="{{ $t->id }}" {{ $tahunId == $t->id ? 'selected' : '' }}>
                                {{ $t->nama }}{{ $t->is_active ? ' ✓' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4 mb-md-0">
                    <label class="simansa-jadwal-label">Kelas</label>
                    <select name="kelas_id" class="form-control form-control-sm select2" id="ftKelas">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelasList->groupBy('tingkat') as $tgk => $kgrp)
                            <optgroup label="Tingkat {{ $tgk }}">
                                @foreach($kgrp as $k)
                                <option value="{{ $k->id }}" {{ $kelasId == $k->id ? 'selected' : '' }}>
                                    {{ $k->nama_kelas }}{{ $k->jurusan ? ' – '.$k->jurusan->nama_jurusan : '' }}
                                </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-2 mb-md-0">
                    <label class="simansa-jadwal-label">Semester</label>
                    <select name="semester" class="form-control form-control-sm" id="ftSemester">
                        <option value="1" {{ $semester == 1 ? 'selected' : '' }}>1 — Ganjil</option>
                        <option value="2" {{ $semester == 2 ? 'selected' : '' }}>2 — Genap</option>
                    </select>
                </div>
                <div class="form-group col-md-2 mb-md-0 d-flex gap-2" style="gap:.5rem">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                        <i class="fas fa-sync-alt"></i> Tampilkan
                    </button>
                    @can('manage-jadwal-pelajaran')
                    @if($kelasObj)
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnPrint" title="Cetak Jadwal">
                        <i class="fas fa-print"></i>
                    </button>
                    @endif
                    @endcan
                </div>
            </div>
        </form>
    </div>
</div>

@if(!$kelasObj)
<div class="simansa-tt-empty">
    <i class="fas fa-mouse-pointer"></i>
    <p>Pilih kelas di atas untuk menampilkan timetable jadwal pelajaran.</p>
</div>
@elseif(!$hasJamConfig)
<div class="simansa-jadwal-alert">
    <div class="simansa-jadwal-alert__icon"><i class="fas fa-exclamation-triangle"></i></div>
    <div>
        <strong>Konfigurasi jam belum ada</strong><br>
        <span class="text-muted">Jadwal tidak dapat ditampilkan sebelum konfigurasi jam dibuat untuk tahun ini.</span>
    </div>
    @can('manage-jadwal-pelajaran')
    <a href="{{ route('admin.jadwal-jam-config.index', ['tahun_pelajaran_id' => $tahunId]) }}" class="btn btn-warning btn-sm ml-3 flex-shrink-0">
        <i class="fas fa-clock"></i> Atur Jam
    </a>
    @endcan
</div>
@else

{{-- ===== COPY JADWAL BUTTON ===== --}}
@can('manage-jadwal-pelajaran')
<div class="simansa-jadwal-panel mb-3" id="copyJadwalSection">
    <div class="simansa-jadwal-panel__body d-flex align-items-center justify-content-between" style="padding:.75rem 1.35rem">
        <span class="text-muted small"><i class="fas fa-copy mr-1"></i> Salin semua jadwal dari tahun lain ke tahun ini (matching nama kelas)</span>
        <button class="btn btn-outline-secondary btn-sm" id="btnCopyJadwal">
            <i class="fas fa-copy"></i> Salin dari Tahun Lain
        </button>
    </div>
</div>
@endcan

{{-- ===== TIMETABLE GRID ===== --}}
<div class="simansa-jadwal-panel" id="timetablePanel">
    <div class="simansa-jadwal-panel__header">
        <div>
            <h3><i class="fas fa-table"></i>
                {{ $kelasObj->nama_kelas }}{{ $kelasObj->jurusan ? ' – '.$kelasObj->jurusan->nama_jurusan : '' }}
                &mdash; Semester {{ $semester }}
            </h3>
            <p class="text-muted small">
                @can('manage-jadwal-pelajaran')Klik sel kosong untuk <strong>tambah</strong>, klik sel terisi untuk <strong>edit/hapus</strong>.@else Jadwal pelajaran (read-only).@endcan
            </p>
        </div>
        @can('manage-jadwal-pelajaran')
        <div class="d-flex" style="gap:.5rem">
            <button class="btn btn-sm btn-outline-danger" id="btnClearAll" title="Hapus semua slot jadwal kelas ini">
                <i class="fas fa-trash-alt"></i> Kosongkan
            </button>
        </div>
        @endcan
    </div>
    <div class="simansa-tt-wrap">
        <table class="simansa-tt-table" id="timetableGrid">
            <thead>
                <tr>
                    <th class="simansa-tt-th-jam">Jam</th>
                    @foreach($hariList as $hari)
                        <th class="simansa-tt-th-hari">{{ ucfirst($hari) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($jamConfig as $jam)
                    @if($jam->is_istirahat)
                    <tr class="simansa-tt-row-istirahat">
                        <td class="simansa-tt-jam-cell simansa-tt-jam-cell--break">
                            <div class="simansa-tt-jam-label"><i class="fas fa-coffee"></i></div>
                            <div class="simansa-tt-jam-time">{{ $jam->waktu_mulai }}</div>
                            <div class="simansa-tt-jam-time simansa-tt-jam-time--end">{{ $jam->waktu_selesai }}</div>
                        </td>
                        <td colspan="{{ count($hariList) }}" class="simansa-tt-break-cell">
                            <i class="fas fa-coffee mr-1"></i> {{ $jam->label ?? 'Istirahat' }}
                        </td>
                    </tr>
                    @else
                    <tr data-jam-ke="{{ $jam->jam_ke }}">
                        <td class="simansa-tt-jam-cell">
                            <div class="simansa-tt-jam-label">Jam <strong>{{ $jam->jam_ke }}</strong></div>
                            <div class="simansa-tt-jam-time">{{ $jam->waktu_mulai }}</div>
                            <div class="simansa-tt-jam-time simansa-tt-jam-time--end">{{ $jam->waktu_selesai }}</div>
                        </td>
                        @foreach($hariList as $hari)
                            @php $slot = $jadwalMap[$hari][$jam->jam_ke] ?? null; @endphp
                            <td class="simansa-tt-cell {{ $slot ? 'has-jadwal' : 'empty-cell' }}"
                                data-hari="{{ $hari }}"
                                data-jam-ke="{{ $jam->jam_ke }}"
                                data-jadwal-id="{{ $slot?->id ?? '' }}"
                                @can('manage-jadwal-pelajaran') onclick="handleCellClick(this)" @endcan>
                                @if($slot)
                                    @php $colorIdx = ((abs(crc32($slot->mapel_id)) % 12) + 1); @endphp
                                    <div class="simansa-tt-slot mc-{{ $colorIdx }}" data-id="{{ $slot->id }}">
                                        <div class="simansa-tt-slot__mapel">{{ $slot->mataPelajaran?->kode_mapel ?? $slot->mataPelajaran?->nama_mapel ?? '?' }}</div>
                                        <div class="simansa-tt-slot__guru">{{ $slot->gtk?->nama_lengkap ?? '-' }}</div>
                                        @if($slot->ruangan)
                                        <div class="simansa-tt-slot__room"><i class="fas fa-door-open"></i> {{ $slot->ruangan }}</div>
                                        @endif
                                    </div>
                                @else
                                    @can('manage-jadwal-pelajaran')
                                    <div class="simansa-tt-add-btn"><i class="fas fa-plus"></i></div>
                                    @endcan
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- ===== LEGENDA ===== --}}
@php
    $mapelSet = [];
    foreach($jadwalMap as $hariData) {
        foreach($hariData as $slot) {
            if ($slot->mapel_id && !isset($mapelSet[$slot->mapel_id])) { $mapelSet[$slot->mapel_id] = $slot; }
        }
    }
@endphp
@if(!empty($mapelSet))
<div class="simansa-jadwal-panel">
    <div class="simansa-jadwal-panel__header">
        <div><h3><i class="fas fa-palette"></i> Legenda Mata Pelajaran</h3><p>Warna berdasarkan kode mapel</p></div>
    </div>
    <div class="simansa-jadwal-panel__body simansa-tt-legenda">
        @foreach($mapelSet as $mid => $slot)
            @php $ci = ((abs(crc32($mid)) % 12) + 1); @endphp
            <div class="simansa-tt-legenda-item mc-{{ $ci }}">
                <strong>{{ $slot->mataPelajaran?->kode_mapel ?? '?' }}</strong>
                <span>{{ $slot->mataPelajaran?->nama_mapel }}</span>
            </div>
        @endforeach
    </div>
</div>
@endif
@endif {{-- end if kelasObj && hasJamConfig --}}

{{-- ===== MODAL SLOT ===== --}}
@can('manage-jadwal-pelajaran')
<div class="modal fade" id="modalSlot" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content simansa-tt-modal">
            <div class="simansa-tt-modal__header">
                <div>
                    <div class="simansa-tt-modal__eyebrow" id="modalEyebrow">Tambah Jadwal</div>
                    <div class="simansa-tt-modal__title" id="modalHariJam">—</div>
                </div>
                <button type="button" class="simansa-tt-modal__close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-0">
                <form id="formSlot" class="simansa-tt-modal__body">
                    <input type="hidden" id="slotId" name="slot_id">
                    <input type="hidden" id="slotHari" name="hari">
                    <input type="hidden" id="slotJamKe" name="jam_ke">
                    <input type="hidden" id="slotKelasId" name="kelas_id" value="{{ $kelasId }}">
                    <input type="hidden" id="slotTahunId" name="tahun_pelajaran_id" value="{{ $tahunId }}">
                    <input type="hidden" id="slotSemester" name="semester" value="{{ $semester }}">

                    <div class="form-group">
                        <label class="simansa-jadwal-label">Mata Pelajaran <span class="text-danger">*</span></label>
                        <select class="form-control select2" id="selMapel" name="mapel_id" required>
                            <option value="">— Memuat… —</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="simansa-jadwal-label">Guru / GTK <span class="text-danger">*</span></label>
                        <select class="form-control select2" id="selGuru" name="gtk_id" required>
                            <option value="">— Memuat… —</option>
                        </select>
                        <div id="konflikGuruNote" class="simansa-tt-konflik d-none">
                            <i class="fas fa-exclamation-triangle"></i> <span></span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-7">
                            <label class="simansa-jadwal-label">Ruangan</label>
                            <input type="text" class="form-control" name="ruangan" id="slotRuangan" maxlength="50" placeholder="cth: Lab IPA">
                        </div>
                        <div class="form-group col-5">
                            <label class="simansa-jadwal-label">Catatan</label>
                            <input type="text" class="form-control" name="catatan" id="slotCatatan" maxlength="100">
                        </div>
                    </div>
                </form>
            </div>
            <div class="simansa-tt-modal__footer">
                <button type="button" class="btn btn-outline-danger d-none" id="btnHapusSlot">
                    <i class="fas fa-trash"></i> Hapus Slot
                </button>
                <div class="ml-auto d-flex" style="gap:.5rem">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSimpanSlot">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL COPY JADWAL --}}
<div class="modal fade" id="modalCopy" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content simansa-tt-modal">
            <div class="simansa-tt-modal__header">
                <div>
                    <div class="simansa-tt-modal__eyebrow">Copy Jadwal</div>
                    <div class="simansa-tt-modal__title">Salin dari Tahun Lain</div>
                </div>
                <button type="button" class="simansa-tt-modal__close" data-dismiss="modal">&times;</button>
            </div>
            <div class="simansa-tt-modal__body">
                <p class="text-muted small mb-3">Salin semua jadwal dari tahun sumber ke tahun tujuan, dicocokkan berdasarkan nama kelas yang sama.</p>
                <div class="form-group">
                    <label class="simansa-jadwal-label">Tahun Sumber</label>
                    <select class="form-control select2" id="copyAsal">
                        @foreach($tahunList as $t)
                            <option value="{{ $t->id }}" {{ $tahunId != $t->id ? '' : 'selected' }}>{{ $t->tahun_pelajaran }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="simansa-jadwal-label">Tahun Tujuan</label>
                    <select class="form-control select2" id="copyTujuan">
                        @foreach($tahunList as $t)
                            <option value="{{ $t->id }}" {{ $tahunId == $t->id ? 'selected' : '' }}>{{ $t->tahun_pelajaran }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="simansa-tt-modal__footer">
                <button type="button" class="btn btn-secondary ml-auto" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary ml-2" id="btnDoCopy">
                    <i class="fas fa-copy"></i> Salin Sekarang
                </button>
            </div>
        </div>
    </div>
</div>
@endcan

@endsection

@section('css')
<style>
/* ===== INFO BAR ===== */
.simansa-tt-infobar{display:flex;justify-content:space-between;align-items:center;gap:1rem;background:linear-gradient(135deg,#1f4fd1 0%,#2f8ca3 100%);border-radius:20px;padding:1.2rem 1.5rem;color:#fff;margin-bottom:1.25rem;box-shadow:0 16px 38px rgba(31,79,209,.18)}
.simansa-tt-infobar__main{display:flex;align-items:center;gap:1rem}
.simansa-tt-infobar__icon{width:52px;height:52px;border-radius:14px;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0}
.simansa-tt-infobar__title{font-size:1.35rem;font-weight:700;line-height:1.2;margin-bottom:.25rem}
.simansa-tt-infobar__sub{font-size:.84rem;color:rgba(255,255,255,.84)}
.simansa-tt-infobar__sub span{margin-right:.35rem}
.simansa-tt-infobar__chips{display:flex;gap:.75rem;flex-shrink:0}
.simansa-tt-chip{padding:.65rem 1rem;border-radius:14px;background:rgba(255,255,255,.12);backdrop-filter:blur(6px);border:1px solid rgba(255,255,255,.18);text-align:center;min-width:72px}
.simansa-tt-chip__label{display:block;font-size:.68rem;letter-spacing:.05em;text-transform:uppercase;color:rgba(255,255,255,.74);margin-bottom:.2rem}
.simansa-tt-chip strong{font-size:1.1rem;color:#fff;display:block}
.simansa-tt-chip--sem strong{font-size:1.35rem}

/* ===== EMPTY STATE ===== */
.simansa-tt-empty{text-align:center;padding:3rem 1rem;color:#94a3b8}
.simansa-tt-empty i{font-size:2.5rem;display:block;margin-bottom:.75rem;color:#cbd5e1}
.simansa-tt-empty p{font-size:1rem;margin:0}

/* ===== TIMETABLE TABLE ===== */
.simansa-tt-wrap{overflow-x:auto;padding:1rem 1.25rem}
.simansa-tt-table{min-width:680px;width:100%;border-collapse:separate;border-spacing:0 0}
.simansa-tt-table th,.simansa-tt-table td{border:1px solid #e2e8f0}
.simansa-tt-th-jam{width:80px;background:#f1f5f9;text-align:center;font-size:.72rem;font-weight:700;color:#64748b;letter-spacing:.04em;text-transform:uppercase;padding:8px 4px}
.simansa-tt-th-hari{text-align:center;background:#f8fafc;font-size:.78rem;font-weight:700;color:#475569;letter-spacing:.04em;text-transform:uppercase;padding:8px 6px}

/* Jam cell */
.simansa-tt-jam-cell{background:#f8fafc;text-align:center;padding:6px 4px;border-right:2px solid #e2e8f0;white-space:nowrap;vertical-align:middle}
.simansa-tt-jam-cell--break{background:#fef9c3}
.simansa-tt-jam-label{font-size:.7rem;color:#64748b;margin-bottom:2px}
.simansa-tt-jam-label strong{color:#1e293b}
.simansa-tt-jam-time{font-size:.72rem;color:#475569}
.simansa-tt-jam-time--end{color:#94a3b8}

/* Break row */
.simansa-tt-row-istirahat td{border-top:1px solid #fde68a}
.simansa-tt-break-cell{background:#fef9c3;text-align:center;font-size:.8rem;color:#92400e;font-weight:600;padding:6px;letter-spacing:.03em}

/* Slot cell */
.simansa-tt-cell{min-height:64px;padding:4px 5px;cursor:pointer;vertical-align:top;position:relative;transition:background .12s}
.simansa-tt-cell.empty-cell:hover{background:rgba(99,102,241,.05)}
.simansa-tt-cell.empty-cell:hover .simansa-tt-add-btn{color:#6366f1}
.simansa-tt-add-btn{height:62px;display:flex;align-items:center;justify-content:center;color:#cbd5e1;font-size:.9rem;transition:color .12s}

/* Slot card */
.simansa-tt-slot{border-radius:8px;padding:5px 7px;font-size:.76rem;line-height:1.35;height:100%;min-height:56px}
.simansa-tt-slot__mapel{font-weight:700;font-size:.78rem;margin-bottom:1px}
.simansa-tt-slot__guru{color:rgba(0,0,0,.62);font-size:.72rem}
.simansa-tt-slot__room{color:rgba(0,0,0,.45);font-size:.68rem;margin-top:2px}
.simansa-tt-slot__room i{font-size:.6rem}

/* Mapel colors */
.mc-1{background:#dbeafe;border-left:3px solid #3b82f6}
.mc-2{background:#dcfce7;border-left:3px solid #22c55e}
.mc-3{background:#fef3c7;border-left:3px solid #f59e0b}
.mc-4{background:#fce7f3;border-left:3px solid #ec4899}
.mc-5{background:#ede9fe;border-left:3px solid #8b5cf6}
.mc-6{background:#e0f2fe;border-left:3px solid #0284c7}
.mc-7{background:#d1fae5;border-left:3px solid #059669}
.mc-8{background:#ffedd5;border-left:3px solid #ea580c}
.mc-9{background:#f0fdf4;border-left:3px solid #16a34a}
.mc-10{background:#fdf4ff;border-left:3px solid #a855f7}
.mc-11{background:#fff7ed;border-left:3px solid #d97706}
.mc-12{background:#ecfeff;border-left:3px solid #06b6d4}

/* ===== LEGENDA ===== */
.simansa-tt-legenda{display:flex;flex-wrap:wrap;gap:.6rem;padding:1rem 1.35rem}
.simansa-tt-legenda-item{display:flex;align-items:center;gap:.45rem;border-radius:8px;padding:.35rem .7rem;font-size:.8rem}
.simansa-tt-legenda-item strong{font-size:.78rem;font-weight:700;white-space:nowrap}
.simansa-tt-legenda-item span{font-size:.75rem;color:rgba(0,0,0,.65)}

/* ===== MODAL ===== */
.simansa-tt-modal{border-radius:22px;overflow:hidden;border:none}
.simansa-tt-modal__header{display:flex;justify-content:space-between;align-items:flex-start;padding:1.25rem 1.5rem;background:linear-gradient(135deg,#1f4fd1,#2f8ca3);color:#fff}
.simansa-tt-modal__eyebrow{font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.75);margin-bottom:.2rem}
.simansa-tt-modal__title{font-size:1.15rem;font-weight:700}
.simansa-tt-modal__close{background:rgba(255,255,255,.15);border:none;border-radius:8px;color:#fff;font-size:1.25rem;line-height:1;padding:.2rem .55rem;cursor:pointer;transition:background .12s}
.simansa-tt-modal__close:hover{background:rgba(255,255,255,.28)}
.simansa-tt-modal__body{padding:1.35rem 1.5rem}
.simansa-tt-modal__footer{display:flex;align-items:center;padding:.9rem 1.35rem;border-top:1px solid #e2e8f0;background:#f8fafc}
.simansa-tt-konflik{display:flex;align-items:center;gap:.4rem;background:#fff8e1;border:1px solid #ffe082;border-radius:8px;padding:.45rem .75rem;margin-top:.4rem;font-size:.82rem;color:#92400e}
.simansa-tt-konflik i{color:#f59e0b}

/* ===== PANEL (shared) ===== */
.simansa-jadwal-panel{background:#fff;border-radius:22px;box-shadow:0 14px 34px rgba(15,23,42,.08);margin-bottom:1.5rem;overflow:hidden}
.simansa-jadwal-panel__header{display:flex;justify-content:space-between;gap:1rem;align-items:center;padding:1.15rem 1.5rem;border-bottom:1px solid rgba(148,163,184,.18)}
.simansa-jadwal-panel__header h3{margin:0 0 .15rem;font-size:1rem;font-weight:700;color:#1f2a44}
.simansa-jadwal-panel__header p{margin:0;color:#60708b;font-size:.85rem}
.simansa-jadwal-panel__body{padding:1rem 1.35rem}
.simansa-jadwal-label{font-size:.82rem;font-weight:600;color:#475569;margin-bottom:.35rem;display:block}
.simansa-jadwal-alert{display:flex;align-items:center;gap:1rem;background:#fff8e1;border:1px solid #ffe082;border-left:4px solid #f4ac08;border-radius:12px;padding:1rem 1.25rem;margin-bottom:1.5rem;font-size:.92rem}
.simansa-jadwal-alert__icon{font-size:1.4rem;color:#f4ac08;flex-shrink:0}

/* ===== PRINT ===== */
@media print{
    .simansa-tt-infobar,.simansa-jadwal-panel__header .d-flex,
    #formFilter,#copyJadwalSection,.simansa-tt-modal,
    .simansa-tt-add-btn,.btn,.modal,
    .sidebar,.navbar,.main-header,.main-footer,
    .simansa-tt-slot__room .fa-door-open{display:none!important}
    .simansa-tt-cell{cursor:default}
    .simansa-tt-slot{-webkit-print-color-adjust:exact;print-color-adjust:exact}
    .simansa-jadwal-panel{box-shadow:none;border:1px solid #e2e8f0;border-radius:8px}
}

@media(max-width:768px){
    .simansa-tt-infobar{flex-direction:column;align-items:stretch}
    .simansa-tt-infobar__chips{justify-content:space-between}
    .simansa-jadwal-panel__header{flex-direction:column;align-items:stretch}
}
</style>
@endsection

@section('js')
<!-- SweetAlert2 Fallback (jika plugin AdminLTE belum load) -->
<script>
if (typeof Swal === 'undefined') {
    document.write('<script src="https:\/\/cdn.jsdelivr.net\/npm\/sweetalert2@11\/dist\/sweetalert2.all.min.js"><\/script>');
}
</script>

<script>
const CSRF = '{{ csrf_token() }}';
const TAHUN_ID = '{{ $tahunId }}';
const KELAS_ID = '{{ $kelasId }}';
const SEMESTER = {{ $semester }};

$(function () {
    $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

    $('#btnPrint').on('click', function () { window.print(); });

    $('#btnCopyJadwal').on('click', function () { $('#modalCopy').modal('show'); });

    $('#btnDoCopy').on('click', function () {
        const asal = $('#copyAsal').val();
        const tujuan = $('#copyTujuan').val();
        if (!asal || !tujuan || asal === tujuan) {
            toastr.warning('Pilih tahun sumber dan tujuan yang berbeda.'); return;
        }
        $('#btnDoCopy').prop('disabled', true);
        $.post('{{ route("admin.jadwal-pelajaran.copy") }}', {
            tahun_asal_id: asal, tahun_tujuan_id: tujuan, _token: CSRF
        }).done(function (res) {
            toastr.success(res.message);
            $('#modalCopy').modal('hide');
            if (res.disalin > 0) setTimeout(() => location.reload(), 800);
        }).fail(function (xhr) {
            toastr.error(xhr.responseJSON?.message || 'Gagal menyalin.');
        }).always(() => $('#btnDoCopy').prop('disabled', false));
    });

    $('#btnClearAll').on('click', function () {
        Swal.fire({
            title: 'Kosongkan semua slot?',
            html: 'Semua jadwal kelas ini (semester ' + SEMESTER + ') akan dihapus.<br><br><small class="text-muted">Tindakan ini tidak dapat dibatalkan.</small>',
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545',
            confirmButtonText: '<i class="fas fa-trash-alt"></i> Ya, Kosongkan', cancelButtonText: 'Batal'
        }).then(result => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: '{{ route("admin.jadwal-pelajaran.store") }}',
                method: 'DELETE',
                data: { kelas_id: KELAS_ID, tahun_pelajaran_id: TAHUN_ID, semester: SEMESTER, _token: CSRF, _method: 'DELETE', bulk: 1 },
                success: function () { location.reload(); },
                error: function (xhr) { toastr.error(xhr.responseJSON?.message || 'Gagal.'); }
            });
        });
    });
});

// ===== CELL CLICK =====
function handleCellClick(cell) {
    const $cell = $(cell);
    const hari    = $cell.data('hari');
    const jamKe   = $cell.data('jam-ke');
    const jadwalId = $cell.data('jadwal-id') || null;

    $('#slotHari').val(hari);
    $('#slotJamKe').val(jamKe);
    $('#slotId').val(jadwalId || '');
    $('#modalEyebrow').text(jadwalId ? 'Edit Jadwal' : 'Tambah Jadwal');
    $('#modalHariJam').text(capitalize(hari) + ', Jam ke-' + jamKe);
    $('#btnHapusSlot').toggleClass('d-none', !jadwalId);
    $('#konflikGuruNote').addClass('d-none');
    $('#slotRuangan').val('');
    $('#slotCatatan').val('');

    Promise.all([loadMapelOptions(), loadGuruOptions(hari, jamKe, jadwalId)])
        .then(() => {
            if (jadwalId) {
                $.get('{{ route("admin.jadwal-pelajaran.show", ":id") }}'.replace(':id', jadwalId))
                    .done(function (res) {
                        if (res.success) {
                            const d = res.data;
                            setSelect2Val('#selMapel', d.mapel_id, d.mapel_nama);
                            setSelect2Val('#selGuru', d.gtk_id, d.gtk_nama);
                            $('#slotRuangan').val(d.ruangan || '');
                            $('#slotCatatan').val(d.catatan || '');
                        }
                    });
            } else {
                setSelect2Val('#selMapel', null, '');
                setSelect2Val('#selGuru', null, '');
            }
        });

    $('#modalSlot').modal('show');
}

function loadMapelOptions() {
    return $.get('{{ route("admin.jadwal-pelajaran.mapel-options") }}', {
        tahun_pelajaran_id: TAHUN_ID, kelas_id: KELAS_ID
    }).done(function (res) {
        const sel = $('#selMapel');
        sel.empty().append('<option value="">— Pilih Mapel —</option>');
        if (res.success) {
            const groups = {};
            res.data.forEach(m => {
                const grp = m.kelompok || 'Lainnya';
                if (!groups[grp]) groups[grp] = [];
                groups[grp].push(m);
            });
            Object.entries(groups).forEach(([grp, items]) => {
                let html = `<optgroup label="${grp}">`;
                items.forEach(m => { html += `<option value="${m.id}" data-kode="${m.kode}">[${m.kode || '-'}] ${m.nama}</option>`; });
                html += '</optgroup>';
                sel.append(html);
            });
        }
        sel.trigger('change');
    });
}

function loadGuruOptions(hari, jamKe, excludeId) {
    return $.get('{{ route("admin.jadwal-pelajaran.guru-options") }}', {
        tahun_pelajaran_id: TAHUN_ID, hari: hari, jam_ke: jamKe,
        semester: SEMESTER, exclude_id: excludeId || ''
    }).done(function (res) {
        const sel = $('#selGuru');
        sel.empty().append('<option value="">— Pilih Guru —</option>');
        if (res.success) {
            res.data.forEach(g => {
                const label = g.kode ? `[${g.kode}] ${g.nama}` : g.nama;
                const suffix = g.konflik ? ' ⚠ (jadwal bentrok)' : '';
                sel.append(`<option value="${g.id}" data-konflik="${g.konflik ? 1 : 0}">${label}${suffix}</option>`);
            });
        }
        sel.trigger('change');
    });
}

$('#selGuru').on('change', function () {
    const isBentrok = $(this).find('option:selected').data('konflik') == 1;
    const $note = $('#konflikGuruNote');
    if (isBentrok) {
        $note.find('span').text('Guru sudah mengajar di kelas lain pada jam ini. Lanjutkan hanya jika yakin.');
        $note.removeClass('d-none');
    } else {
        $note.addClass('d-none');
    }
});

function setSelect2Val(selector, val, text) {
    const $sel = $(selector);
    if (!val) { $sel.val(null).trigger('change'); return; }
    if ($sel.find(`option[value="${val}"]`).length === 0) { $sel.append(new Option(text, val, true, true)); }
    $sel.val(val).trigger('change');
}

$('#btnSimpanSlot').on('click', function () {
    const jadwalId = $('#slotId').val();
    const isEdit   = !!jadwalId;
    const url = isEdit ? `/admin/jadwal-pelajaran/${jadwalId}` : '{{ route("admin.jadwal-pelajaran.store") }}';
    const method = isEdit ? 'PUT' : 'POST';

    $('#btnSimpanSlot').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan…');
    $.ajax({
        url, method, data: $('#formSlot').serialize(),
        headers: { 'X-CSRF-TOKEN': CSRF },
        success: function (res) {
            if (res.success) {
                toastr.success(res.message);
                $('#modalSlot').modal('hide');
                refreshCell(res.data, $('#slotHari').val(), $('#slotJamKe').val());
            } else { toastr.error(res.message || 'Gagal menyimpan.'); }
        },
        error: function (xhr) { toastr.error(xhr.responseJSON?.message || 'Terjadi kesalahan.'); },
        complete: function () { $('#btnSimpanSlot').prop('disabled', false).html('<i class="fas fa-save"></i> Simpan'); }
    });
});

$('#btnHapusSlot').on('click', function () {
    const jadwalId = $('#slotId').val();
    if (!jadwalId) return;
    Swal.fire({
        title: 'Hapus slot ini?', icon: 'question',
        showCancelButton: true, confirmButtonColor: '#dc3545',
        confirmButtonText: 'Hapus', cancelButtonText: 'Batal'
    }).then(result => {
        if (!result.isConfirmed) return;
        $.ajax({
            url: `/admin/jadwal-pelajaran/${jadwalId}`, method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF },
            success: function (res) {
                if (res.success) {
                    toastr.success(res.message);
                    $('#modalSlot').modal('hide');
                    clearCell($('#slotHari').val(), $('#slotJamKe').val());
                }
            },
            error: function (xhr) { toastr.error(xhr.responseJSON?.message || 'Gagal.'); }
        });
    });
});

function refreshCell(data, hari, jamKe) {
    const $cell = $(`[data-hari="${hari}"][data-jam-ke="${jamKe}"]`);
    $cell.attr('data-jadwal-id', data.id).addClass('has-jadwal').removeClass('empty-cell');
    const ci = hashStr(data.id);
    $cell.html(`<div class="simansa-tt-slot mc-${ci}" data-id="${data.id}">
        <div class="simansa-tt-slot__mapel">${data.mapel_kode || data.mapel_nama}</div>
        <div class="simansa-tt-slot__guru">${data.gtk_nama}</div>
        ${data.ruangan ? `<div class="simansa-tt-slot__room"><i class="fas fa-door-open"></i> ${data.ruangan}</div>` : ''}
    </div>`);
}

function clearCell(hari, jamKe) {
    const $cell = $(`[data-hari="${hari}"][data-jam-ke="${jamKe}"]`);
    $cell.attr('data-jadwal-id', '').removeClass('has-jadwal').addClass('empty-cell');
    $cell.html('<div class="simansa-tt-add-btn"><i class="fas fa-plus"></i></div>');
}

function hashStr(str) { let h=0; for(let i=0;i<str.length;i++){h=Math.imul(31,h)+str.charCodeAt(i)|0;} return Math.abs(h)%12+1; }
function capitalize(s) { return s.charAt(0).toUpperCase()+s.slice(1); }
</script>
@endsection
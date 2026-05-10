@extends('adminlte::page')

@section('title', 'Timetable Jadwal' . ($kelasObj ? ' – ' . $kelasObj->nama_kelas : ''))

@section('plugins.Sweetalert2', true)

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-7">
            <h1><i class="fas fa-table"></i> Timetable Jadwal</h1>
        </div>
        <div class="col-sm-5">
            <div class="float-sm-right">
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
                <div class="form-group col-md-2 mb-md-0 d-flex" style="gap:.5rem">
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
@else

{{-- ===== ACTION BAR ===== --}}
@can('manage-jadwal-pelajaran')
<div class="simansa-jadwal-panel mb-3">
    <div class="simansa-jadwal-panel__body d-flex align-items-center justify-content-between flex-wrap" style="padding:.65rem 1.35rem;gap:.5rem">
        <span class="text-muted small">
            <i class="fas fa-info-circle mr-1"></i>
            Klik slot terisi untuk <strong>edit/hapus</strong>. Ikon <i class="fas fa-plus-circle text-success"></i> untuk tambah jadwal.
            Ikon <i class="fas fa-clock text-primary"></i> di header hari untuk <strong>tambah/hapus baris jam</strong>.
        </span>
        <div class="d-flex" style="gap:.5rem">
            <button class="btn btn-sm btn-success" id="btnGenerateDefault">
                <i class="fas fa-magic"></i> Generate Jam Default
            </button>
            <button class="btn btn-sm btn-outline-secondary" id="btnCopyJadwal">
                <i class="fas fa-copy"></i> Salin dari Tahun Lain
            </button>
            <button class="btn btn-sm btn-outline-danger" id="btnClearAll">
                <i class="fas fa-trash-alt"></i> Kosongkan
            </button>
        </div>
    </div>
</div>
@endcan

{{-- ===== TIMETABLE FLEX GRID ===== --}}
<div class="simansa-jadwal-panel" id="timetablePanel">
    <div class="simansa-jadwal-panel__header">
        <div>
            <h3><i class="fas fa-table"></i>
                {{ $kelasObj->nama_kelas }}{{ $kelasObj->jurusan ? ' – '.$kelasObj->jurusan->nama_jurusan : '' }}
                &mdash; Semester {{ $semester }}
            </h3>
            <p class="text-muted small">Tiap hari bisa punya jumlah jam berbeda. Scroll horizontal jika perlu.</p>
        </div>
    </div>
    <div class="simansa-tt-flex-wrap">
        @foreach($hariList as $hari)
        @php $daySlots = $hariJamMap[$hari] ?? []; @endphp
        <div class="simansa-tt-day-col" data-hari="{{ $hari }}">
            {{-- Day header --}}
            <div class="simansa-tt-day-header">
                <span>{{ ucfirst($hari) }}</span>
                @can('manage-jadwal-pelajaran')
                <button class="simansa-tt-btn-addslot" title="Tambah baris jam ke {{ ucfirst($hari) }}"
                    onclick="openAddSlotModal('{{ $hari }}')">
                    <i class="fas fa-clock"></i>
                </button>
                @endcan
            </div>

            {{-- Slot rows --}}
            <div class="simansa-tt-slot-list" id="slotList-{{ $hari }}">
                @forelse($daySlots as $slot)
                    @if($slot->tipe === 'pelajaran')
                        @php $jadwal = $jadwalMap[$hari][$slot->jam_ke] ?? null; @endphp
                        <div class="simansa-tt-row simansa-tt-row--pelajaran {{ $jadwal ? 'has-jadwal' : 'empty' }}"
                            data-slot-id="{{ $slot->id }}"
                            data-jam-ke="{{ $slot->jam_ke }}"
                            data-hari="{{ $hari }}"
                            data-jadwal-id="{{ $jadwal?->id ?? '' }}"
                            @can('manage-jadwal-pelajaran') onclick="handleCellClick(this)" @endcan>
                            <div class="simansa-tt-row__header">
                                <span class="simansa-tt-row__jam">Jam {{ $slot->jam_ke }}</span>
                                <span class="simansa-tt-row__time">
                                    {{ $slot->waktu_mulai ? substr($slot->waktu_mulai,0,5) : '' }}
                                    {{ $slot->waktu_selesai ? '–'.substr($slot->waktu_selesai,0,5) : '' }}
                                </span>
                                @can('manage-jadwal-pelajaran')
                                <button class="simansa-tt-row__del-slot" title="Hapus baris jam ini"
                                    onclick="deleteSlot(event, '{{ $slot->id }}', '{{ $hari }}')">
                                    <i class="fas fa-times"></i>
                                </button>
                                @endcan
                            </div>
                            <div class="simansa-tt-row__body">
                                @if($jadwal)
                                    @php $ci = ((abs(crc32($jadwal->mapel_id)) % 12) + 1); @endphp
                                    <div class="simansa-tt-slot mc-{{ $ci }}">
                                        <div class="simansa-tt-slot__mapel">{{ $jadwal->mataPelajaran?->kode_mapel ?? $jadwal->mataPelajaran?->nama_mapel ?? '?' }}</div>
                                        <div class="simansa-tt-slot__guru">{{ $jadwal->gtk?->nama_lengkap ?? '-' }}</div>
                                        @if($jadwal->ruangan)<div class="simansa-tt-slot__room"><i class="fas fa-door-open"></i> {{ $jadwal->ruangan }}</div>@endif
                                    </div>
                                @else
                                    @can('manage-jadwal-pelajaran')
                                    <div class="simansa-tt-add-btn"><i class="fas fa-plus-circle"></i></div>
                                    @endcan
                                @endif
                            </div>
                        </div>
                    @else
                        {{-- Non-pelajaran: istirahat, upacara, khusus --}}
                        <div class="simansa-tt-row simansa-tt-row--special simansa-tt-row--{{ $slot->tipe }}"
                            data-slot-id="{{ $slot->id }}"
                            data-hari="{{ $hari }}">
                            <div class="simansa-tt-row__header">
                                <span class="simansa-tt-row__icon">
                                    @if($slot->tipe === 'istirahat')<i class="fas fa-coffee"></i>
                                    @elseif($slot->tipe === 'upacara')<i class="fas fa-flag"></i>
                                    @else<i class="fas fa-star"></i>
                                    @endif
                                </span>
                                <span class="simansa-tt-row__time">
                                    {{ $slot->waktu_mulai ? substr($slot->waktu_mulai,0,5) : '' }}
                                    {{ $slot->waktu_selesai ? '–'.substr($slot->waktu_selesai,0,5) : '' }}
                                </span>
                                @can('manage-jadwal-pelajaran')
                                <button class="simansa-tt-row__del-slot" title="Hapus baris ini"
                                    onclick="deleteSlot(event, '{{ $slot->id }}', '{{ $hari }}')">
                                    <i class="fas fa-times"></i>
                                </button>
                                @endcan
                            </div>
                            <div class="simansa-tt-row__special-label">{{ $slot->displayLabel() }}</div>
                        </div>
                    @endif
                @empty
                    <div class="simansa-tt-day-empty">
                        <span>Belum ada jam</span>
                    </div>
                @endforelse
            </div>
        </div>
        @endforeach
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
        <div><h3><i class="fas fa-palette"></i> Legenda Mata Pelajaran</h3></div>
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

{{-- ===== JTM REKAP GURU ===== --}}
<div class="simansa-jadwal-panel">
    <div class="simansa-jadwal-panel__header">
        <div>
            <h3><i class="fas fa-chart-bar"></i> Rekap JTM Guru</h3>
            <p class="text-muted small">
                JTM = Jam Tatap Muka per minggu. MA/MAN: 1 slot = 45 menit = 1 JTM.
                Min sertifikasi: <strong>24 JTM</strong>, Maks: <strong>40 JTM</strong>.
                Ekuivalensi: Wali Kelas +6, Wakasek +12, Ka. Lab/Perpus +12.
            </p>
        </div>
        <button class="btn btn-sm btn-outline-primary" id="btnLoadJtm">
            <i class="fas fa-sync-alt"></i> Tampilkan
        </button>
    </div>
    <div id="jtmPanel" class="simansa-jadwal-panel__body" style="display:none">
        <div id="jtmLoading" class="text-center py-3 text-muted d-none">
            <i class="fas fa-spinner fa-spin"></i> Memuat data JTM...
        </div>
        <div id="jtmContent"></div>
    </div>
</div>

@endif {{-- end if kelasObj --}}

{{-- ===== MODAL: ASSIGN JADWAL ===== --}}
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

                    {{-- GURU DULU --}}
                    <div class="form-group">
                        <label class="simansa-jadwal-label">Guru / GTK <span class="text-danger">*</span></label>
                        <select class="form-control select2" id="selGuru" name="gtk_id" required>
                            <option value="">— Pilih Guru —</option>
                        </select>
                        <div id="konflikGuruNote" class="simansa-tt-konflik d-none">
                            <i class="fas fa-exclamation-triangle"></i> <span></span>
                        </div>
                        <div id="jtmGuruInfo" class="simansa-tt-jtm-info d-none mt-2">
                            <span class="simansa-tt-jtm-info__label">JTM minggu ini:</span>
                            <span class="simansa-tt-jtm-badge" id="jtmBadge">0</span>
                            <span class="simansa-tt-jtm-info__note" id="jtmNote"></span>
                        </div>
                    </div>

                    {{-- MAPEL (auto-fill dari guru) --}}
                    <div class="form-group">
                        <label class="simansa-jadwal-label">Mata Pelajaran <span class="text-danger">*</span></label>
                        <select class="form-control select2" id="selMapel" name="mapel_id" required>
                            <option value="">— Pilih Mapel —</option>
                        </select>
                        <div id="autoFillNote" class="d-none mt-1">
                            <small class="text-success"><i class="fas fa-magic"></i> Mapel diisi otomatis dari jadwal guru di kelas ini.</small>
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

{{-- MODAL: TAMBAH BARIS JAM KE HARI --}}
<div class="modal fade" id="modalAddSlot" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content simansa-tt-modal">
            <div class="simansa-tt-modal__header">
                <div>
                    <div class="simansa-tt-modal__eyebrow">Tambah Baris Jam</div>
                    <div class="simansa-tt-modal__title" id="addSlotHariLabel">Hari</div>
                </div>
                <button type="button" class="simansa-tt-modal__close" data-dismiss="modal">&times;</button>
            </div>
            <div class="simansa-tt-modal__body">
                <input type="hidden" id="addSlotHari">
                <div class="form-group">
                    <label class="simansa-jadwal-label">Jenis Slot</label>
                    <select class="form-control" id="addSlotTipe">
                        <option value="pelajaran">Jam Pelajaran</option>
                        <option value="istirahat">Istirahat</option>
                        <option value="upacara">Upacara</option>
                        <option value="khusus">Khusus</option>
                    </select>
                </div>
                <div class="form-group" id="addSlotLabelWrap">
                    <label class="simansa-jadwal-label">Label <small class="text-muted">(opsional)</small></label>
                    <input type="text" class="form-control" id="addSlotLabel" maxlength="60" placeholder="cth: Istirahat Sholat">
                </div>
                <div class="form-row">
                    <div class="form-group col-6">
                        <label class="simansa-jadwal-label">Mulai</label>
                        <input type="time" class="form-control" id="addSlotMulai">
                    </div>
                    <div class="form-group col-6">
                        <label class="simansa-jadwal-label">Selesai</label>
                        <input type="time" class="form-control" id="addSlotSelesai">
                    </div>
                </div>
            </div>
            <div class="simansa-tt-modal__footer">
                <button type="button" class="btn btn-secondary ml-auto" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary ml-2" id="btnDoAddSlot">
                    <i class="fas fa-plus"></i> Tambahkan
                </button>
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
                            <option value="{{ $t->id }}">{{ $t->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="simansa-jadwal-label">Tahun Tujuan</label>
                    <select class="form-control select2" id="copyTujuan">
                        @foreach($tahunList as $t)
                            <option value="{{ $t->id }}" {{ $tahunId == $t->id ? 'selected' : '' }}>{{ $t->nama }}</option>
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

/* ===== FLEX GRID ===== */
.simansa-tt-flex-wrap{display:flex;overflow-x:auto;padding:1rem 1.25rem 1.5rem;gap:.75rem;min-height:200px}
.simansa-tt-day-col{flex:1;min-width:130px;max-width:220px;display:flex;flex-direction:column}

/* Day header */
.simansa-tt-day-header{display:flex;align-items:center;justify-content:space-between;padding:.5rem .65rem;background:linear-gradient(135deg,#1f4fd1,#2f8ca3);border-radius:10px 10px 0 0;color:#fff;font-weight:700;font-size:.85rem;letter-spacing:.04em;text-transform:uppercase}
.simansa-tt-btn-addslot{background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.3);border-radius:6px;color:#fff;width:24px;height:24px;display:flex;align-items:center;justify-content:center;font-size:.75rem;cursor:pointer;transition:background .12s;padding:0}
.simansa-tt-btn-addslot:hover{background:rgba(255,255,255,.28)}

/* Slot list */
.simansa-tt-slot-list{flex:1;display:flex;flex-direction:column;gap:0;border:1px solid #e2e8f0;border-top:none;border-radius:0 0 10px 10px;overflow:hidden;background:#f8fafc}

/* Day empty */
.simansa-tt-day-empty{padding:1.2rem .5rem;text-align:center;color:#94a3b8;font-size:.8rem;font-style:italic;flex:1;display:flex;align-items:center;justify-content:center}

/* Row (each jam) */
.simansa-tt-row{border-bottom:1px solid #e2e8f0;background:#fff}
.simansa-tt-row:last-child{border-bottom:none}
.simansa-tt-row--pelajaran{cursor:pointer;transition:background .1s}
.simansa-tt-row--pelajaran:hover{background:#f0f4ff}
.simansa-tt-row__header{display:flex;align-items:center;padding:.25rem .5rem;background:#f8fafc;border-bottom:1px solid #f1f5f9;gap:.3rem}
.simansa-tt-row__jam{font-size:.68rem;font-weight:700;color:#475569;white-space:nowrap}
.simansa-tt-row__icon{font-size:.75rem;color:#64748b}
.simansa-tt-row__time{font-size:.63rem;color:#94a3b8;flex:1;text-align:right;white-space:nowrap}
.simansa-tt-row__del-slot{background:none;border:none;color:transparent;padding:0 2px;cursor:pointer;font-size:.68rem;line-height:1;transition:color .12s;flex-shrink:0}
.simansa-tt-row:hover .simansa-tt-row__del-slot{color:#dc3545}
.simansa-tt-row__body{padding:.35rem .45rem;min-height:48px}
.simansa-tt-row__special-label{padding:.3rem .5rem;font-size:.78rem;font-weight:600;text-align:center}

/* Special rows */
.simansa-tt-row--istirahat .simansa-tt-row__header{background:#fef9c3}
.simansa-tt-row--istirahat .simansa-tt-row__special-label{color:#92400e}
.simansa-tt-row--upacara .simansa-tt-row__header{background:#dbeafe}
.simansa-tt-row--upacara .simansa-tt-row__special-label{color:#1d4ed8}
.simansa-tt-row--khusus .simansa-tt-row__header{background:#ede9fe}
.simansa-tt-row--khusus .simansa-tt-row__special-label{color:#6d28d9}

/* Add button (empty pelajaran slot) */
.simansa-tt-add-btn{height:42px;display:flex;align-items:center;justify-content:center;color:#cbd5e1;font-size:1rem;transition:color .12s}
.simansa-tt-row--pelajaran:hover .simansa-tt-add-btn{color:#6366f1}

/* Slot card (filled) */
.simansa-tt-slot{border-radius:7px;padding:4px 6px;font-size:.76rem;line-height:1.3}
.simansa-tt-slot__mapel{font-weight:700;font-size:.78rem;margin-bottom:1px}
.simansa-tt-slot__guru{color:rgba(0,0,0,.62);font-size:.72rem}
.simansa-tt-slot__room{color:rgba(0,0,0,.45);font-size:.65rem;margin-top:2px}

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

/* ===== PANEL ===== */
.simansa-jadwal-panel{background:#fff;border-radius:22px;box-shadow:0 14px 34px rgba(15,23,42,.08);margin-bottom:1.5rem;overflow:hidden}
.simansa-jadwal-panel__header{display:flex;justify-content:space-between;gap:1rem;align-items:center;padding:1.15rem 1.5rem;border-bottom:1px solid rgba(148,163,184,.18)}
.simansa-jadwal-panel__header h3{margin:0 0 .15rem;font-size:1rem;font-weight:700;color:#1f2a44}
.simansa-jadwal-panel__header p{margin:0;color:#60708b;font-size:.85rem}
.simansa-jadwal-panel__body{padding:1rem 1.35rem}
.simansa-jadwal-label{font-size:.82rem;font-weight:600;color:#475569;margin-bottom:.35rem;display:block}

/* ===== JTM INFO IN MODAL ===== */
.simansa-tt-jtm-info{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap}
.simansa-tt-jtm-info__label{font-size:.78rem;color:#64748b}
.simansa-tt-jtm-badge{display:inline-flex;align-items:center;justify-content:center;min-width:36px;height:24px;border-radius:12px;padding:0 8px;font-size:.78rem;font-weight:700;background:#e2e8f0;color:#475569}
.simansa-tt-jtm-badge.kurang{background:#fee2e2;color:#b91c1c}
.simansa-tt-jtm-badge.normal{background:#dcfce7;color:#166534}
.simansa-tt-jtm-badge.lebih{background:#fef3c7;color:#92400e}
.simansa-tt-jtm-info__note{font-size:.73rem;color:#94a3b8}

/* ===== JTM REKAP TABLE ===== */
.simansa-jtm-row{display:flex;align-items:center;padding:.45rem .75rem;border-bottom:1px solid #f1f5f9;gap:.75rem;flex-wrap:wrap}
.simansa-jtm-row:last-child{border-bottom:none}
.simansa-jtm-row:hover{background:#f8fafc}
.simansa-jtm-nama{flex:1;min-width:140px}
.simansa-jtm-nama strong{font-size:.83rem;display:block}
.simansa-jtm-nama small{font-size:.72rem;color:#94a3b8}
.simansa-jtm-bar-wrap{flex:2;min-width:120px}
.simansa-jtm-bar-track{height:8px;background:#e2e8f0;border-radius:4px;overflow:hidden}
.simansa-jtm-bar-fill{height:100%;border-radius:4px;transition:width .3s}
.simansa-jtm-bar-fill.kurang{background:#ef4444}
.simansa-jtm-bar-fill.normal{background:#22c55e}
.simansa-jtm-bar-fill.lebih{background:#f59e0b}
.simansa-jtm-nums{display:flex;flex-direction:column;align-items:flex-end;min-width:64px}
.simansa-jtm-nums .total{font-size:.9rem;font-weight:700;line-height:1}
.simansa-jtm-nums .detail{font-size:.68rem;color:#94a3b8}
.simansa-jtm-status{min-width:60px;text-align:center}
.simansa-jtm-badge{display:inline-block;padding:.2rem .55rem;border-radius:20px;font-size:.68rem;font-weight:700;letter-spacing:.03em}
.simansa-jtm-badge.kurang{background:#fee2e2;color:#b91c1c}
.simansa-jtm-badge.normal{background:#dcfce7;color:#166534}
.simansa-jtm-badge.lebih{background:#fef3c7;color:#92400e}
.simansa-jtm-tugas{font-size:.7rem;color:#6366f1;max-width:160px}

/* ===== PRINT ===== */
@media print{
    .simansa-tt-infobar,.simansa-jadwal-panel__header .d-flex,
    #formFilter,.simansa-tt-modal,.modal,
    .simansa-tt-add-btn,.simansa-tt-btn-addslot,.simansa-tt-row__del-slot,.btn,
    .sidebar,.navbar,.main-header,.main-footer{display:none!important}
    .simansa-tt-row--pelajaran{cursor:default}
    .simansa-tt-slot{-webkit-print-color-adjust:exact;print-color-adjust:exact}
    .simansa-jadwal-panel{box-shadow:none;border:1px solid #e2e8f0;border-radius:8px}
}

@media(max-width:768px){
    .simansa-tt-infobar{flex-direction:column;align-items:stretch}
    .simansa-tt-infobar__chips{justify-content:space-between}
    .simansa-tt-day-col{min-width:110px}
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
const CSRF         = '{{ csrf_token() }}';
const TAHUN_ID     = '{{ $tahunId }}';
const KELAS_ID     = '{{ $kelasId }}';
const SEMESTER     = {{ $semester }};
const URL_GURU     = '{{ route("admin.jadwal-pelajaran.guru-options") }}';
const URL_MAPEL    = '{{ route("admin.jadwal-pelajaran.mapel-options") }}';
const URL_AUTOFILL = '{{ route("admin.jadwal-pelajaran.guru-mapel-in-kelas") }}';
const URL_STORE    = '{{ route("admin.jadwal-pelajaran.store") }}';
const URL_COPY     = '{{ route("admin.jadwal-pelajaran.copy") }}';
const URL_CLEARALL = '{{ route("admin.jadwal-pelajaran.clear-all") }}';
const URL_HARI_JAM = '{{ route("admin.jadwal-hari-jam.store") }}';
const URL_HARI_DEL = '/admin/jadwal-hari-jam/';
const URL_GENERATE = '{{ route("admin.jadwal-hari-jam.generate-default") }}';
const URL_JTM      = '{{ route("admin.jadwal-pelajaran.guru-jtm-summary") }}';

$(function () {
    $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

    $('#btnPrint').on('click', () => window.print());
    $('#btnCopyJadwal').on('click', () => $('#modalCopy').modal('show'));

    // Copy jadwal dari tahun lain
    $('#btnDoCopy').on('click', function () {
        const asal = $('#copyAsal').val(), tujuan = $('#copyTujuan').val();
        if (!asal || !tujuan || asal === tujuan) { toastr.warning('Pilih tahun sumber dan tujuan berbeda.'); return; }
        $(this).prop('disabled', true);
        $.post(URL_COPY, { tahun_asal_id: asal, tahun_tujuan_id: tujuan, _token: CSRF })
            .done(res => {
                toastr.success(res.message);
                $('#modalCopy').modal('hide');
                if (res.disalin > 0) setTimeout(() => location.reload(), 800);
            })
            .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Gagal.'))
            .always(() => $(this).prop('disabled', false));
    });

    // Kosongkan semua
    $('#btnClearAll').on('click', function () {
        Swal.fire({
            title: 'Kosongkan semua slot?',
            html: 'Semua jadwal kelas ini (semester ' + SEMESTER + ') akan dihapus.<br><small class="text-muted">Tidak dapat dibatalkan.</small>',
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545',
            confirmButtonText: '<i class="fas fa-trash-alt"></i> Ya, Kosongkan', cancelButtonText: 'Batal'
        }).then(result => {
            if (!result.isConfirmed) return;
            $.post(URL_CLEARALL, { kelas_id: KELAS_ID, tahun_pelajaran_id: TAHUN_ID, semester: SEMESTER, _token: CSRF })
                .done(res => { toastr.success(res.message); setTimeout(() => location.reload(), 600); })
                .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Gagal.'));
        });
    });

    // Tipe slot toggle label field
    $('#addSlotTipe').on('change', function () {
        $('#addSlotLabelWrap').toggle($(this).val() !== 'pelajaran');
    }).trigger('change');

    // Generate Jam Default
    $('#btnGenerateDefault').on('click', function () {
        Swal.fire({
            title: 'Generate jam default?',
            html: 'Akan dibuat slot jam per hari:<br>' +
                  '<ul style="text-align:left;margin:.5rem 0 0 1rem">' +
                  '<li><strong>Senin</strong>: Upacara + 8 jam pelajaran</li>' +
                  '<li><strong>Selasa–Jumat</strong>: 8 jam pelajaran</li>' +
                  '<li><strong>Sabtu</strong>: 6 jam pelajaran</li></ul>' +
                  '<small class="text-muted">Hari yang sudah punya slot akan dilewati.</small>',
            icon: 'question', showCancelButton: true,
            confirmButtonText: '<i class="fas fa-magic"></i> Ya, Generate',
            cancelButtonText: 'Batal'
        }).then(r => {
            if (!r.isConfirmed) return;
            const btn = $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Generating...');
            $.post(URL_GENERATE, { _token: CSRF, tahun_pelajaran_id: TAHUN_ID, semester: SEMESTER })
                .done(res => {
                    toastr.success(res.message);
                    if (res.created > 0) setTimeout(() => location.reload(), 800);
                })
                .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Gagal generate jam.'))
                .always(() => btn.prop('disabled', false).html('<i class="fas fa-magic"></i> Generate Jam Default'));
        });
    });

    // JTM Rekap Panel
    let jtmLoaded = false;
    $('#btnLoadJtm').on('click', function () {
        const panel = $('#jtmPanel');
        if (panel.is(':visible') && jtmLoaded) { panel.slideUp(); $(this).html('<i class="fas fa-sync-alt"></i> Tampilkan'); return; }
        panel.slideDown();
        $(this).html('<i class="fas fa-sync-alt fa-spin"></i> Memuat...');
        $('#jtmLoading').removeClass('d-none');
        $.get(URL_JTM, { tahun_pelajaran_id: TAHUN_ID, semester: SEMESTER })
            .done(res => {
                jtmLoaded = true;
                $('#btnLoadJtm').html('<i class="fas fa-times"></i> Tutup');
                renderJtmPanel(res.data || []);
            })
            .fail(xhr => {
                $('#btnLoadJtm').html('<i class="fas fa-sync-alt"></i> Tampilkan');
                toastr.error(xhr.responseJSON?.message || 'Gagal memuat data JTM.');
                panel.slideUp();
            })
            .always(() => $('#jtmLoading').addClass('d-none'));
    });

    function renderJtmPanel(data) {
        if (!data.length) { $('#jtmContent').html('<p class="text-muted p-3">Belum ada data JTM.</p>'); return; }
        let html = '<div class="simansa-jadwal-panel__body-inner">';
        data.forEach(g => {
            const pct = Math.min(100, Math.round(g.jtm_total / 40 * 100));
            const tugasStr = g.tugas_tambahan.join(', ');
            html += `<div class="simansa-jtm-row">
                <div class="simansa-jtm-nama">
                    <strong>${g.nama}</strong>
                    <small>${g.kode || ''}${tugasStr ? ' — ' + tugasStr : ''}</small>
                </div>
                <div class="simansa-jtm-bar-wrap">
                    <div class="simansa-jtm-bar-track">
                        <div class="simansa-jtm-bar-fill ${g.status}" style="width:${pct}%"></div>
                    </div>
                    <div class="d-flex justify-content-between" style="font-size:.65rem;color:#94a3b8;margin-top:2px">
                        <span>0</span><span>24</span><span>40</span>
                    </div>
                </div>
                <div class="simansa-jtm-nums">
                    <span class="total">${g.jtm_total}</span>
                    <span class="detail">${g.jtm_mengajar}+${g.jtm_ekuivalensi}</span>
                </div>
                <div class="simansa-jtm-status">
                    <span class="simansa-jtm-badge ${g.status}">${g.status === 'kurang' ? 'Kurang' : (g.status === 'lebih' ? 'Lebih' : 'Normal')}</span>
                </div>
            </div>`;
        });
        html += '</div>';
        $('#jtmContent').html(html);
    }
});

// ===== KLIK ROW PELAJARAN (assign jadwal) =====
function handleCellClick(row) {
    const $row     = $(row);
    const hari     = $row.data('hari');
    const jamKe    = $row.data('jam-ke');
    const jadwalId = $row.data('jadwal-id') || null;

    $('#slotHari').val(hari);
    $('#slotJamKe').val(jamKe);
    $('#slotId').val(jadwalId || '');
    $('#modalEyebrow').text(jadwalId ? 'Edit Jadwal' : 'Tambah Jadwal');
    $('#modalHariJam').text(capitalize(hari) + ', Jam ke-' + jamKe);
    $('#btnHapusSlot').toggleClass('d-none', !jadwalId);
    $('#konflikGuruNote').addClass('d-none');
    $('#jtmGuruInfo').addClass('d-none');
    $('#autoFillNote').addClass('d-none');
    $('#slotRuangan').val('');
    $('#slotCatatan').val('');

    loadGuruOptions(hari, jamKe, jadwalId).then(() => {
        if (jadwalId) {
            $.get('/admin/jadwal-pelajaran/' + jadwalId).done(res => {
                if (!res.success) return;
                const d = res.data;
                setSelect2Val('#selGuru', d.gtk_id, d.gtk_nama);
                loadMapelOptions(d.gtk_id, false).then(() => {
                    setSelect2Val('#selMapel', d.mapel_id, d.mapel_nama);
                });
                $('#slotRuangan').val(d.ruangan || '');
                $('#slotCatatan').val(d.catatan || '');
            });
        } else {
            setSelect2Val('#selGuru', null, '');
            setSelect2Val('#selMapel', null, '');
        }
    });

    $('#modalSlot').modal('show');
}

function loadGuruOptions(hari, jamKe, excludeId) {
    return $.get(URL_GURU, {
        tahun_pelajaran_id: TAHUN_ID, hari: hari,
        jam_ke: jamKe, semester: SEMESTER, exclude_id: excludeId || ''
    }).done(res => {
        const sel = $('#selGuru');
        sel.empty().append('<option value="">— Pilih Guru —</option>');
        if (res.success) {
            res.data.forEach(g => {
                const label = g.kode ? `[${g.kode}] ${g.nama}` : g.nama;
                const suf = g.konflik ? ' ⚠ (jadwal bentrok)' : '';
                sel.append(`<option value="${g.id}" data-konflik="${g.konflik ? 1 : 0}" data-jtm="${g.jtm ?? 0}" data-jtm-status="${g.jtm_status ?? ''}">${label}${suf}</option>`);
            });
        }
        sel.trigger('change');
    });
}

function loadMapelOptions(gtkId, doAutoFill) {
    return $.get(URL_MAPEL, { tahun_pelajaran_id: TAHUN_ID, kelas_id: KELAS_ID })
        .done(res => {
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
                    items.forEach(m => { html += `<option value="${m.id}" data-kode="${m.kode}">[${m.kode||'-'}] ${m.nama}</option>`; });
                    html += '</optgroup>';
                    sel.append(html);
                });
            }
            sel.trigger('change');

            // Auto-fill mapel berdasarkan jadwal guru di kelas ini
            if (doAutoFill && gtkId) {
                $.get(URL_AUTOFILL, {
                    gtk_id: gtkId, kelas_id: KELAS_ID,
                    tahun_pelajaran_id: TAHUN_ID, semester: SEMESTER
                }).done(r => {
                    if (r.success && r.data) {
                        setSelect2Val('#selMapel', r.data.mapel_id, `[${r.data.mapel_kode||'-'}] ${r.data.mapel_nama}`);
                        $('#autoFillNote').removeClass('d-none');
                    }
                });
            }
        });
}

// Guru change → load mapel + auto-fill
$('#selGuru').on('change', function () {
    const gtkId   = $(this).val();
    const konflik = $(this).find('option:selected').data('konflik') == 1;
    const isEdit  = !!$('#slotId').val();
    const $note   = $('#konflikGuruNote');

    if (konflik) {
        $note.find('span').text('Guru sudah mengajar di kelas lain pada jam ini. Lanjutkan hanya jika yakin.');
        $note.removeClass('d-none');
    } else { $note.addClass('d-none'); }

    // JTM badge
    const $opt = $(this).find('option:selected');
    const jtm = parseInt($opt.data('jtm') ?? 0);
    const jtmSt = $opt.data('jtm-status') || '';
    if (gtkId) {
        const $badge = $('#jtmBadge');
        $badge.attr('class', 'simansa-tt-jtm-badge' + (jtmSt ? ' ' + jtmSt : '')).text(jtm + ' JTM');
        const notes = { kurang: 'Di bawah minimum sertifikasi (24 JTM)', lebih: 'Melebihi batas maksimum (40 JTM)', normal: 'Dalam batas normal' };
        $('#jtmNote').text(notes[jtmSt] || '');
        $('#jtmGuruInfo').removeClass('d-none');
    } else { $('#jtmGuruInfo').addClass('d-none'); }

    if (!isEdit) {
        $('#autoFillNote').addClass('d-none');
        setSelect2Val('#selMapel', null, '');
        if (gtkId) loadMapelOptions(gtkId, true);
    }
});

function setSelect2Val(selector, val, text) {
    const $sel = $(selector);
    if (!val) { $sel.val(null).trigger('change'); return; }
    if ($sel.find(`option[value="${val}"]`).length === 0) { $sel.append(new Option(text, val, true, true)); }
    $sel.val(val).trigger('change');
}

// ===== SIMPAN JADWAL =====
$('#btnSimpanSlot').on('click', function () {
    const jadwalId = $('#slotId').val();
    const url    = jadwalId ? `/admin/jadwal-pelajaran/${jadwalId}` : URL_STORE;
    const method = jadwalId ? 'PUT' : 'POST';

    $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
    $.ajax({ url, method, data: $('#formSlot').serialize(), headers: { 'X-CSRF-TOKEN': CSRF } })
        .done(res => {
            if (res.success) {
                toastr.success(res.message);
                $('#modalSlot').modal('hide');
                refreshRow(res.data, $('#slotHari').val(), $('#slotJamKe').val());
            } else { toastr.error(res.message || 'Gagal.'); }
        })
        .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Terjadi kesalahan.'))
        .always(() => $(this).prop('disabled', false).html('<i class="fas fa-save"></i> Simpan'));
});

// ===== HAPUS JADWAL (slot assignment) =====
$('#btnHapusSlot').on('click', function () {
    const jadwalId = $('#slotId').val();
    if (!jadwalId) return;
    Swal.fire({ title: 'Hapus assignment ini?', icon: 'question', showCancelButton: true,
        confirmButtonColor: '#dc3545', confirmButtonText: 'Hapus', cancelButtonText: 'Batal'
    }).then(res => {
        if (!res.isConfirmed) return;
        $.ajax({ url: `/admin/jadwal-pelajaran/${jadwalId}`, method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF } })
            .done(r => {
                if (r.success) { toastr.success(r.message); $('#modalSlot').modal('hide'); clearRow($('#slotHari').val(), $('#slotJamKe').val()); }
            })
            .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Gagal.'));
    });
});

// ===== UPDATE DOM setelah simpan =====
function refreshRow(data, hari, jamKe) {
    const $row = $(`[data-hari="${hari}"][data-jam-ke="${jamKe}"]`);
    $row.attr('data-jadwal-id', data.id).removeClass('empty').addClass('has-jadwal');
    const ci = hashStr(data.id);
    $row.find('.simansa-tt-row__body').html(`
        <div class="simansa-tt-slot mc-${ci}">
            <div class="simansa-tt-slot__mapel">${data.mapel_kode || data.mapel_nama}</div>
            <div class="simansa-tt-slot__guru">${data.gtk_nama}</div>
            ${data.ruangan ? `<div class="simansa-tt-slot__room"><i class="fas fa-door-open"></i> ${data.ruangan}</div>` : ''}
        </div>`);
}

function clearRow(hari, jamKe) {
    const $row = $(`[data-hari="${hari}"][data-jam-ke="${jamKe}"]`);
    $row.attr('data-jadwal-id', '').removeClass('has-jadwal').addClass('empty');
    $row.find('.simansa-tt-row__body').html('<div class="simansa-tt-add-btn"><i class="fas fa-plus-circle"></i></div>');
}

// ===== TAMBAH BARIS JAM KE HARI =====
function openAddSlotModal(hari) {
    $('#addSlotHari').val(hari);
    $('#addSlotHariLabel').text(capitalize(hari));
    $('#addSlotTipe').val('pelajaran').trigger('change');
    $('#addSlotLabel').val('');
    $('#addSlotMulai').val('');
    $('#addSlotSelesai').val('');
    $('#modalAddSlot').modal('show');
}

$('#btnDoAddSlot').on('click', function () {
    const hari = $('#addSlotHari').val();
    const data = {
        _token:             CSRF,
        tahun_pelajaran_id: TAHUN_ID,
        semester:           SEMESTER,
        hari:               hari,
        tipe:               $('#addSlotTipe').val(),
        label:              $('#addSlotLabel').val() || null,
        waktu_mulai:        $('#addSlotMulai').val() || null,
        waktu_selesai:      $('#addSlotSelesai').val() || null,
    };

    $(this).prop('disabled', true);
    $.post(URL_HARI_JAM, data)
        .done(res => {
            if (res.success) {
                toastr.success(res.message);
                $('#modalAddSlot').modal('hide');
                setTimeout(() => location.reload(), 500);
            } else { toastr.error(res.message || 'Gagal.'); }
        })
        .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Gagal menambah slot.'))
        .always(() => $(this).prop('disabled', false));
});

// ===== HAPUS BARIS JAM (slot hari_jam) =====
function deleteSlot(event, slotId, hari) {
    event.stopPropagation();
    Swal.fire({
        title: 'Hapus baris jam ini?',
        text: 'Jadwal yang ada di slot ini juga akan dihapus.',
        icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545',
        confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal'
    }).then(res => {
        if (!res.isConfirmed) return;
        $.ajax({ url: URL_HARI_DEL + slotId, method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF } })
            .done(r => {
                if (r.success) { toastr.success(r.message); setTimeout(() => location.reload(), 400); }
            })
            .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Gagal.'));
    });
}

function hashStr(str) { let h=0; for(let i=0;i<str.length;i++){h=Math.imul(31,h)+str.charCodeAt(i)|0;} return Math.abs(h)%12+1; }
function capitalize(s) { return s.charAt(0).toUpperCase()+s.slice(1); }
</script>
@endsection

@extends('adminlte::page')

@section('title', 'Jadwal Pelajaran')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-chalkboard-teacher"></i> Jadwal Pelajaran</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Jadwal Pelajaran</li>
            </ol>
        </div>
    </div>
@endsection

@section('content')

{{-- ===== HERO ===== --}}
<section class="simansa-jadwal-hero">
    <div class="simansa-jadwal-hero__content">
        <div>
            <div class="simansa-jadwal-hero__eyebrow">
                <i class="fas fa-calendar-alt"></i> Kurikulum &amp; Pembelajaran SIMANSA
            </div>
            <h2>Jadwal Pelajaran</h2>
            <p>Kelola jadwal mengajar tiap kelas, konfigurasi jam, dan pantau konflik guru dalam satu tampilan terpadu.</p>
        </div>
        <div class="simansa-jadwal-hero__meta">
            <div class="simansa-jadwal-chip">
                <span class="simansa-jadwal-chip__label">Tahun Aktif</span>
                <strong>{{ $tahunAktif?->nama ?? 'Belum diatur' }}</strong>
            </div>
            <div class="simansa-jadwal-chip">
                <span class="simansa-jadwal-chip__label">Total Kelas</span>
                <strong>{{ $kelasList->count() }}</strong>
            </div>
            <div class="simansa-jadwal-chip">
                <span class="simansa-jadwal-chip__label">Slot Jadwal</span>
                <strong>{{ $stats['total_slots'] ?? 0 }}</strong>
            </div>
        </div>
    </div>
</section>

{{-- ===== STATS ===== --}}
<div class="row">
    <div class="col-lg-3 col-md-6">
        <div class="simansa-jadwal-stat simansa-jadwal-stat--primary">
            <span class="simansa-jadwal-stat__label">Total Kelas</span>
            <strong>{{ $kelasList->count() }}</strong>
            <small>Kelas pada tahun pelajaran ini</small>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="simansa-jadwal-stat simansa-jadwal-stat--success">
            <span class="simansa-jadwal-stat__label">Kelas Ada Jadwal</span>
            <strong>{{ $stats['kelas_with_jadwal'] }}</strong>
            <small>Sudah terisi minimal 1 slot jadwal</small>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="simansa-jadwal-stat simansa-jadwal-stat--warning">
            <span class="simansa-jadwal-stat__label">Total Slot Jadwal</span>
            <strong>{{ $stats['total_slots'] }}</strong>
            <small>Jam pelajaran yang sudah diisi</small>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="simansa-jadwal-stat simansa-jadwal-stat--info">
            <span class="simansa-jadwal-stat__label">Total Slot</span>
            <strong>{{ $stats['total_slots'] ?? 0 }}</strong>
            <small>Slot jadwal yang sudah terisi</small>
        </div>
    </div>
</div>

{{-- ===== ALERT jam config kosong ===== --}}


{{-- ===== PANEL FILTER ===== --}}
<div class="simansa-jadwal-panel">
    <div class="simansa-jadwal-panel__header">
        <div>
            <h3><i class="fas fa-search"></i> Buka Jadwal Kelas</h3>
            <p>Pilih tahun pelajaran, kelas, dan semester untuk membuka timetable.</p>
        </div>
        <div class="d-flex flex-wrap" style="gap:8px">
            <a href="{{ route('admin.jadwal-pelajaran.monitor') }}" class="btn btn-success btn-sm">
                <i class="fas fa-tv"></i> Monitor Jadwal
            </a>
            <div class="dropdown">
                <button class="btn btn-outline-success btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-file-excel"></i> Export EMIS GTK
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="{{ route('admin.jadwal-pelajaran.export-emis-gtk', ['tahun_pelajaran_id' => $tahunId, 'semester' => 1]) }}">
                        <i class="fas fa-download text-success mr-2"></i>Semester 1 (Ganjil)
                    </a>
                    <a class="dropdown-item" href="{{ route('admin.jadwal-pelajaran.export-emis-gtk', ['tahun_pelajaran_id' => $tahunId, 'semester' => 2]) }}">
                        <i class="fas fa-download text-success mr-2"></i>Semester 2 (Genap)
                    </a>
                </div>
            </div>
            @can('view-jadwal-mapping')
                <a href="{{ route('admin.jadwal-mapping.index', ['tahun_pelajaran_id' => $tahunId]) }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-link"></i> Mapping Kode
                </a>
            @endcan
            @can('manage-jadwal-pelajaran')
                <a href="{{ route('admin.jadwal-pelajaran.import') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-file-import"></i> Import Jadwal Wakakur
                </a>
                <a href="{{ route('admin.jadwal-jam-config.index', ['tahun_pelajaran_id' => $tahunId]) }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-sliders-h"></i> Konfigurasi Jam
                </a>
            @endcan
        </div>
    </div>
    <div class="simansa-jadwal-panel__body">
        <form method="GET" action="{{ route('admin.jadwal-pelajaran.timetable') }}" id="formPilihKelas">
            <div class="form-row align-items-end">
                <div class="form-group col-md-4">
                    <label class="simansa-jadwal-label">Tahun Pelajaran</label>
                    <select name="tahun_pelajaran_id" class="form-control select2" id="selTahun">
                        <option value="">-- Pilih Tahun --</option>
                        @foreach($tahunList as $t)
                            <option value="{{ $t->id }}" {{ $tahunId == $t->id ? 'selected' : '' }}>
                                {{ $t->nama }}{{ $t->is_active ? ' ✓' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label class="simansa-jadwal-label">Kelas</label>
                    <select name="kelas_id" class="form-control select2" id="selKelas">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelasList->groupBy('tingkat') as $tgk => $kgrp)
                            <optgroup label="Tingkat {{ $tgk }}">
                                @foreach($kgrp as $k)
                                <option value="{{ $k->id }}">
                                    {{ $k->nama_kelas }}{{ $k->jurusan ? ' – '.$k->jurusan->nama_jurusan : '' }}{{ $k->asrama_suffix }}
                                </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label class="simansa-jadwal-label">Semester</label>
                    <select name="semester" class="form-control" id="selSemester">
                        <option value="1">1 — Ganjil</option>
                        <option value="2">2 — Genap</option>
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-table"></i> Lihat Jadwal
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ===== DAFTAR KELAS (CARD GRID) ===== --}}
@if($kelasList->isNotEmpty())
<div class="simansa-jadwal-panel">
    <div class="simansa-jadwal-panel__header">
        <div>
            <h3><i class="fas fa-th-large"></i> Daftar Kelas</h3>
            <p>Klik tombol <strong>Sem 1</strong> atau <strong>Sem 2</strong> untuk membuka timetable jadwal kelas.</p>
        </div>
    </div>
    <div class="simansa-jadwal-panel__body p-0">
        @foreach($kelasList->groupBy('tingkat') as $tingkat => $kelasGroup)
        <div class="simansa-jadwal-tingkat-header">
            <i class="fas fa-layer-group"></i>
            Kelas {{ $tingkat }}
            <span class="badge badge-secondary ml-2">{{ $kelasGroup->count() }}</span>
        </div>
        <div class="simansa-jadwal-kelas-grid">
            @foreach($kelasGroup as $k)
            @php $hasJ = in_array($k->id, $stats['kelas_ids_with_jadwal']); @endphp
            <div class="simansa-jadwal-kelas-card {{ $hasJ ? 'has-jadwal' : '' }}">
                <div class="simansa-jadwal-kelas-card__top">
                    <span class="simansa-jadwal-kelas-card__nama">{{ $k->nama_kelas }}{!! $k->asrama_badge !!}</span>
                    @if($k->jurusan)
                    <span class="simansa-jadwal-kelas-card__jurusan">{{ $k->jurusan->singkatan ?? $k->jurusan->nama_jurusan }}</span>
                    @endif
                    @if($hasJ)
                    <span class="simansa-jadwal-kelas-card__badge"><i class="fas fa-check-circle"></i></span>
                    @endif
                </div>
                @if($k->waliKelas)
                <div class="simansa-jadwal-kelas-card__wali">
                    <i class="fas fa-user-tie"></i> {{ $k->waliKelas->name ?? '-' }}
                </div>
                @else
                <div class="simansa-jadwal-kelas-card__wali text-muted"><i class="fas fa-user-slash"></i> Belum ada wali kelas</div>
                @endif
                <div class="simansa-jadwal-kelas-card__wali {{ $k->ketuaKelasRecord?->siswa ? '' : 'text-muted' }}">
                    <i class="fas fa-crown text-warning"></i>
                    {{ $k->ketuaKelasRecord?->siswa?->nama_lengkap ?? 'Belum ada ketua kelas' }}
                </div>
                <div class="simansa-jadwal-kelas-card__actions">
                    <a href="{{ route('admin.jadwal-pelajaran.timetable', ['kelas_id' => $k->id, 'tahun_pelajaran_id' => $tahunId, 'semester' => 1]) }}"
                       class="btn btn-sm btn-primary flex-grow-1">
                        <i class="fas fa-table"></i> Sem 1
                    </a>
                    <a href="{{ route('admin.jadwal-pelajaran.timetable', ['kelas_id' => $k->id, 'tahun_pelajaran_id' => $tahunId, 'semester' => 2]) }}"
                       class="btn btn-sm btn-outline-primary flex-grow-1">
                        Sem 2
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @endforeach
    </div>
</div>
@endif

@endsection

@section('css')
<style>
/* ===== HERO ===== */
.simansa-jadwal-hero{margin-bottom:1.5rem;padding:1.75rem 1.8rem;border-radius:24px;background:linear-gradient(135deg,#1f4fd1 0%,#2f8ca3 100%);color:#fff;box-shadow:0 20px 45px rgba(31,79,209,.18)}
.simansa-jadwal-hero__content{display:flex;justify-content:space-between;gap:1.5rem;align-items:flex-start}
.simansa-jadwal-hero__eyebrow{display:inline-flex;align-items:center;gap:.45rem;font-size:.78rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.84);margin-bottom:.7rem}
.simansa-jadwal-hero h2{margin:0 0 .4rem;font-size:2rem;font-weight:700}
.simansa-jadwal-hero p{margin:0;max-width:700px;color:rgba(255,255,255,.9);font-size:1rem}
.simansa-jadwal-hero__meta{display:grid;grid-template-columns:repeat(3,minmax(140px,1fr));gap:.9rem;min-width:420px}
.simansa-jadwal-chip{padding:.9rem 1.1rem;border-radius:18px;background:rgba(255,255,255,.12);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.18)}
.simansa-jadwal-chip__label{display:block;margin-bottom:.35rem;font-size:.72rem;letter-spacing:.05em;text-transform:uppercase;color:rgba(255,255,255,.74)}
.simansa-jadwal-chip strong{font-size:.95rem;color:#fff}

/* ===== STATS ===== */
.simansa-jadwal-stat{height:100%;padding:1.3rem 1.25rem;border-radius:22px;color:#fff;box-shadow:0 16px 36px rgba(15,23,42,.09);margin-bottom:1rem}
.simansa-jadwal-stat--primary{background:linear-gradient(135deg,#6268f3 0%,#5b76d6 100%)}
.simansa-jadwal-stat--success{background:linear-gradient(135deg,#46c98a 0%,#57d2aa 100%)}
.simansa-jadwal-stat--warning{background:linear-gradient(135deg,#f4ac08 0%,#f6c453 100%);color:#17324d}
.simansa-jadwal-stat--danger{background:linear-gradient(135deg,#f37f88 0%,#ee8e98 100%)}
.simansa-jadwal-stat--info{background:linear-gradient(135deg,#36b0e8 0%,#5ac8f0 100%)}
.simansa-jadwal-stat__label{display:block;font-size:.78rem;font-weight:700;letter-spacing:.04em;text-transform:uppercase;margin-bottom:.65rem;opacity:.88}
.simansa-jadwal-stat strong{display:block;font-size:2rem;line-height:1;margin-bottom:.5rem}
.simansa-jadwal-stat small{display:block;font-size:.85rem;opacity:.88}

/* ===== PANEL ===== */
.simansa-jadwal-panel{background:#fff;border-radius:22px;box-shadow:0 14px 34px rgba(15,23,42,.08);margin-bottom:1.5rem;overflow:hidden}
.simansa-jadwal-panel__header{display:flex;justify-content:space-between;gap:1rem;align-items:center;padding:1.25rem 1.5rem;border-bottom:1px solid rgba(148,163,184,.18)}
.simansa-jadwal-panel__header h3{margin:0 0 .2rem;font-size:1.05rem;font-weight:700;color:#1f2a44}
.simansa-jadwal-panel__header p{margin:0;color:#60708b;font-size:.88rem}
.simansa-jadwal-panel__body{padding:1.35rem 1.5rem}
.simansa-jadwal-panel__body.p-0{padding:0}

/* ===== ALERT ===== */
.simansa-jadwal-alert{display:flex;align-items:center;gap:1rem;background:#fff8e1;border:1px solid #ffe082;border-left:4px solid #f4ac08;border-radius:12px;padding:1rem 1.25rem;margin-bottom:1.5rem;font-size:.92rem}
.simansa-jadwal-alert__icon{font-size:1.4rem;color:#f4ac08;flex-shrink:0}

/* ===== TINGKAT HEADER ===== */
.simansa-jadwal-tingkat-header{padding:.65rem 1.5rem;background:#f8fafc;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;font-size:.82rem;font-weight:700;color:#475569;letter-spacing:.04em;text-transform:uppercase}
.simansa-jadwal-tingkat-header:first-child{border-top:none}

/* ===== KELAS CARD GRID ===== */
.simansa-jadwal-kelas-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1rem;padding:1rem 1.5rem 1.5rem}
.simansa-jadwal-kelas-card{background:#fff;border:1.5px solid #e2e8f0;border-radius:16px;padding:1rem 1.1rem;display:flex;flex-direction:column;gap:.6rem;transition:border-color .15s,box-shadow .15s}
.simansa-jadwal-kelas-card:hover{border-color:#93c5fd;box-shadow:0 8px 22px rgba(31,79,209,.1)}
.simansa-jadwal-kelas-card.has-jadwal{border-color:#6ee7b7;background:linear-gradient(135deg,#f0fdf4,#fff)}
.simansa-jadwal-kelas-card__top{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap}
.simansa-jadwal-kelas-card__nama{font-size:1rem;font-weight:700;color:#1f2a44}
.simansa-jadwal-kelas-card__jurusan{font-size:.72rem;font-weight:700;background:#eff6ff;color:#3b82f6;border-radius:6px;padding:.15rem .45rem;letter-spacing:.03em}
.simansa-jadwal-kelas-card__badge{margin-left:auto;color:#10b981;font-size:.95rem}
.simansa-jadwal-kelas-card__wali{font-size:.8rem;color:#64748b}
.simansa-jadwal-kelas-card__actions{display:flex;gap:.5rem;margin-top:.25rem}

/* ===== FORM LABEL ===== */
.simansa-jadwal-label{font-size:.82rem;font-weight:600;color:#475569;margin-bottom:.35rem}

/* ===== RESPONSIVE ===== */
@media(max-width:992px){
    .simansa-jadwal-hero__content,.simansa-jadwal-panel__header{flex-direction:column;align-items:stretch}
    .simansa-jadwal-hero__meta{grid-template-columns:1fr 1fr;min-width:0}
    .simansa-jadwal-kelas-grid{grid-template-columns:repeat(auto-fill,minmax(180px,1fr))}
}
@media(max-width:576px){.simansa-jadwal-hero__meta{grid-template-columns:1fr}}
</style>
@endsection

@section('js')
<script>
$(function () {
    $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

    // Saat ganti tahun: reload halaman dengan tahun baru
    $('#selTahun').on('change', function () {
        const tahunId = $(this).val();
        if (!tahunId) return;
        window.location.href = '{{ route("admin.jadwal-pelajaran.index") }}?tahun_pelajaran_id=' + tahunId;
    });
});
</script>
@endsection

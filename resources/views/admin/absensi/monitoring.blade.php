@extends('adminlte::page')

@section('title', 'Pemantauan Absensi Siswa')

@php
    $statusMeta = [
        'hadir' => ['label' => 'Hadir', 'class' => 'success', 'icon' => 'fa-check'],
        'terlambat' => ['label' => 'Terlambat', 'class' => 'warning', 'icon' => 'fa-clock'],
        'izin' => ['label' => 'Izin', 'class' => 'info', 'icon' => 'fa-envelope-open-text'],
        'sakit' => ['label' => 'Sakit', 'class' => 'primary', 'icon' => 'fa-notes-medical'],
        'alpa' => ['label' => 'Alpa', 'class' => 'danger', 'icon' => 'fa-times'],
        'dispen' => ['label' => 'Dispen', 'class' => 'secondary', 'icon' => 'fa-id-badge'],
        'keluar_awal' => ['label' => 'Keluar Awal', 'class' => 'dark', 'icon' => 'fa-sign-out-alt'],
        'belum_direkam' => ['label' => 'Belum Direkam', 'class' => 'light', 'icon' => 'fa-minus'],
    ];
@endphp

@section('content_header')
    <div class="attendance-monitor-heading">
        <div>
            <div class="attendance-monitor-heading__eyebrow">
                <i class="fas fa-clipboard-list mr-1"></i>PEMANTAUAN ADMIN
            </div>
            <h1>Absensi Seluruh Siswa</h1>
            <p>Roster tahun aktif ditampilkan utuh, termasuk siswa yang absensinya belum direkam.</p>
        </div>
        <div class="attendance-monitor-heading__actions">
            <span><small>Tahun Aktif</small><strong>{{ $tahunPelajaran?->nama ?? 'Belum tersedia' }}</strong></span>
            <a href="{{ route('admin.absensi-siswa.index', ['tanggal' => $tanggal, 'mode' => 'harian']) }}"
               class="btn btn-primary">
                <i class="fas fa-pen mr-1"></i>Input Absensi
            </a>
        </div>
    </div>
@stop

@section('content')
    @if(!$tahunPelajaran)
        <div class="attendance-monitor-empty">
            <i class="fas fa-calendar-times"></i>
            <h3>Tahun pelajaran aktif belum tersedia</h3>
            <p>Tetapkan tahun pelajaran aktif agar roster siswa dapat dipantau.</p>
        </div>
    @else
        <div class="attendance-monitor-kpis">
            <div class="attendance-monitor-kpi is-total">
                <span>Total Roster</span><strong>{{ number_format($stats['total']) }}</strong>
                <small>Semua siswa pada {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d M Y') }}</small>
            </div>
            <div class="attendance-monitor-kpi is-recorded">
                <span>Sudah Direkam</span><strong>{{ number_format($stats['recorded']) }}</strong>
                <small>{{ $stats['total'] ? round($stats['recorded'] / $stats['total'] * 100, 1) : 0 }}% dari roster</small>
            </div>
            <div class="attendance-monitor-kpi is-present">
                <span>Hadir/Mengikuti</span><strong>{{ number_format($stats['present']) }}</strong>
                <small>Termasuk terlambat dan keluar awal</small>
            </div>
            <div class="attendance-monitor-kpi is-exception">
                <span>Perlu Keterangan</span><strong>{{ number_format($stats['exceptions']) }}</strong>
                <small>Sakit, izin, alpa, atau dispensasi</small>
            </div>
            <div class="attendance-monitor-kpi is-pending">
                <span>Belum Direkam</span><strong>{{ number_format($stats['unrecorded']) }}</strong>
                <small>Perlu tindak lanjut wali kelas</small>
            </div>
        </div>

        <section class="attendance-monitor-panel">
            <div class="attendance-monitor-filter">
                <div>
                    <h2><i class="fas fa-filter mr-2"></i>Filter Pemantauan</h2>
                    <p>Default menampilkan tahun aktif dan tanggal hari ini.</p>
                </div>
                <form method="GET" action="{{ route('admin.absensi-siswa.monitoring') }}" id="monitoringFilterForm">
                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="date" name="tanggal" value="{{ $tanggal }}" max="{{ now()->format('Y-m-d') }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Kelas</label>
                        <select name="kelas_id" class="form-control">
                            <option value="">Semua kelas</option>
                            @foreach($kelasOptions as $kelas)
                                <option value="{{ $kelas->id }}" @selected(request('kelas_id') === $kelas->id)>
                                    {{ $kelas->tingkat }} · {{ $kelas->nama_kelas }}{{ $kelas->asrama_suffix }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="">Semua status</option>
                            @foreach($statusMeta as $value => $meta)
                                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $meta['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group attendance-monitor-filter__search">
                        <label>Nama/NISN</label>
                        <div class="input-group">
                            <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari siswa...">
                            <div class="input-group-append">
                                <button class="btn btn-primary"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('admin.absensi-siswa.monitoring') }}" class="btn btn-outline-secondary attendance-monitor-reset">
                        <i class="fas fa-undo mr-1"></i>Reset
                    </a>
                </form>
            </div>

            <div class="attendance-monitor-table-head">
                <div>
                    <h2>Daftar Kehadiran</h2>
                    <p>{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}</p>
                </div>
                <span>{{ method_exists($students, 'total') ? number_format($students->total()) : 0 }} siswa sesuai filter</span>
            </div>

            <div class="table-responsive">
                <table class="table attendance-monitor-table">
                    <thead>
                    <tr>
                        <th>Siswa</th>
                        <th>Kelas</th>
                        <th>Status</th>
                        <th>Catatan</th>
                        <th>Dicatat Oleh</th>
                        <th>Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($students as $student)
                        @php
                            $status = $student->attendance_status ?: 'belum_direkam';
                            $meta = $statusMeta[$status] ?? $statusMeta['belum_direkam'];
                            $initials = collect(explode(' ', $student->nama_lengkap))
                                ->filter()->take(2)->map(fn ($word) => mb_substr($word, 0, 1))->implode('');
                        @endphp
                        <tr>
                            <td>
                                <div class="attendance-monitor-student">
                                    <span class="attendance-monitor-avatar {{ $student->jenis_kelamin === 'P' ? 'is-female' : '' }}">{{ $initials }}</span>
                                    <span>
                                        <strong>{{ $student->nama_lengkap }}</strong>
                                        <small>NISN {{ $student->nisn ?: '-' }}</small>
                                    </span>
                                </div>
                            </td>
                            <td><strong>{{ $student->nama_kelas }}</strong><small class="d-block text-muted">Tingkat {{ $student->tingkat }}</small></td>
                            <td>
                                <span class="attendance-monitor-status badge-{{ $meta['class'] }}">
                                    <i class="fas {{ $meta['icon'] }}"></i>{{ $meta['label'] }}
                                </span>
                                @if($student->session_status)
                                    <small class="d-block mt-1 text-muted">Sesi {{ ucfirst($student->session_status) }}</small>
                                @endif
                            </td>
                            <td>
                                @if($student->attendance_notes)
                                    <button type="button" class="attendance-note-view"
                                            data-student="{{ $student->nama_lengkap }}"
                                            data-note="{{ $student->attendance_notes }}">
                                        <i class="fas fa-comment-alt mr-1"></i>{{ \Illuminate\Support\Str::limit($student->attendance_notes, 42) }}
                                    </button>
                                @else
                                    <span class="text-muted small">Tidak ada catatan</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $student->checked_by_name ?: '-' }}</strong>
                                <small class="d-block text-muted">
                                    {{ $student->checked_at ? \Carbon\Carbon::parse($student->checked_at)->format('H:i') : 'Belum direkam' }}
                                </small>
                            </td>
                            <td>
                                <a href="{{ route('admin.absensi-siswa.index', ['tanggal' => $tanggal, 'mode' => 'harian', 'kelas_id' => $student->kelas_id]) }}"
                                   class="btn btn-sm btn-outline-primary" title="Buka sesi absensi">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <a href="{{ route('admin.absensi-siswa.analytics.student', $student->id) }}"
                                   class="btn btn-sm btn-outline-secondary" title="Riwayat siswa">
                                    <i class="fas fa-chart-line"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="attendance-monitor-no-results"><i class="fas fa-search"></i><strong>Data tidak ditemukan</strong><span>Ubah filter atau kata pencarian.</span></div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($students, 'links'))
                <div class="attendance-monitor-pagination">{{ $students->links() }}</div>
            @endif
        </section>
    @endif

    <div class="modal fade" id="attendanceNoteViewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content attendance-note-modal">
                <div class="attendance-note-modal__top">
                    <span><i class="fas fa-comment-dots"></i></span>
                    <div><small>CATATAN KEHADIRAN</small><h4 id="attendanceNoteViewStudent">Siswa</h4></div>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body"><p id="attendanceNoteViewText"></p></div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button></div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
.attendance-monitor-heading{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:.35rem .1rem}.attendance-monitor-heading__eyebrow{color:#3267d6;font-size:.7rem;font-weight:800;letter-spacing:.08em}.attendance-monitor-heading h1{margin:.2rem 0 .15rem;color:#17233b;font-size:1.55rem;font-weight:800}.attendance-monitor-heading p{margin:0;color:#64748b}.attendance-monitor-heading__actions{display:flex;align-items:center;gap:.75rem}.attendance-monitor-heading__actions>span{padding:.55rem .8rem;border:1px solid #dce5f1;border-radius:10px;background:#fff}.attendance-monitor-heading__actions small,.attendance-monitor-heading__actions strong{display:block}.attendance-monitor-heading__actions small{color:#7a8799;font-size:.65rem}.attendance-monitor-heading__actions strong{color:#24344f;font-size:.82rem}.attendance-monitor-kpis{display:grid;grid-template-columns:repeat(5,1fr);gap:.8rem;margin-bottom:1rem}.attendance-monitor-kpi{padding:1rem;border:1px solid #dfe6ef;border-top:3px solid;border-radius:13px;background:#fff;box-shadow:0 8px 24px rgba(15,23,42,.04)}.attendance-monitor-kpi span,.attendance-monitor-kpi small{display:block}.attendance-monitor-kpi span{color:#68758a;font-size:.69rem;font-weight:800;text-transform:uppercase}.attendance-monitor-kpi strong{display:block;margin:.15rem 0;color:#162238;font-size:1.65rem}.attendance-monitor-kpi small{color:#8490a2;font-size:.7rem}.attendance-monitor-kpi.is-total{border-top-color:#4f6ef7}.attendance-monitor-kpi.is-recorded{border-top-color:#14b8a6}.attendance-monitor-kpi.is-present{border-top-color:#22c55e}.attendance-monitor-kpi.is-exception{border-top-color:#ef4444}.attendance-monitor-kpi.is-pending{border-top-color:#f59e0b}.attendance-monitor-panel,.attendance-monitor-empty{border:1px solid #dfe6ef;border-radius:16px;background:#fff;box-shadow:0 12px 34px rgba(15,23,42,.055)}.attendance-monitor-filter{padding:1rem 1.15rem;border-bottom:1px solid #e8edf3;background:#f8fafc}.attendance-monitor-filter>div{display:flex;align-items:baseline;gap:.6rem;margin-bottom:.7rem}.attendance-monitor-filter h2,.attendance-monitor-table-head h2{margin:0;color:#1f2d45;font-size:1rem;font-weight:800}.attendance-monitor-filter p,.attendance-monitor-table-head p{margin:0;color:#7d899a;font-size:.76rem}.attendance-monitor-filter form{display:grid;grid-template-columns:160px minmax(180px,1fr) minmax(170px,1fr) minmax(210px,1.25fr) auto;gap:.65rem;align-items:end}.attendance-monitor-filter .form-group{margin:0}.attendance-monitor-filter label{margin-bottom:.25rem;color:#657186;font-size:.68rem;font-weight:800;text-transform:uppercase}.attendance-monitor-reset{height:38px}.attendance-monitor-table-head{display:flex;align-items:center;justify-content:space-between;padding:.9rem 1.15rem}.attendance-monitor-table-head span{padding:.3rem .6rem;border-radius:999px;color:#4d607c;background:#eef3fa;font-size:.73rem;font-weight:700}.attendance-monitor-table{min-width:1000px;margin:0}.attendance-monitor-table thead th{padding:.65rem .8rem;border-top:1px solid #edf1f6;border-bottom:1px solid #dfe6ef;color:#66748b;background:#f8fafc;font-size:.67rem;text-transform:uppercase}.attendance-monitor-table tbody td{padding:.7rem .8rem;vertical-align:middle;border-color:#edf1f6;color:#334155;font-size:.8rem}.attendance-monitor-table tbody tr:hover td{background:#f9fbff}.attendance-monitor-student{display:flex;align-items:center;gap:.6rem;min-width:230px}.attendance-monitor-student strong,.attendance-monitor-student small{display:block}.attendance-monitor-student strong{color:#17233b}.attendance-monitor-student small{color:#8994a5;font-size:.7rem}.attendance-monitor-avatar{display:inline-flex;flex:0 0 34px;width:34px;height:34px;align-items:center;justify-content:center;border-radius:10px;color:#3158b9;background:#e9efff;font-size:.72rem;font-weight:800}.attendance-monitor-avatar.is-female{color:#b33b71;background:#fdebf3}.attendance-monitor-status{display:inline-flex;align-items:center;gap:.3rem;min-width:105px;justify-content:center;padding:.3rem .5rem;border-radius:999px;border:1px solid transparent;font-size:.68rem;font-weight:800}.attendance-monitor-status.badge-light{border-color:#d8e0ea;color:#68758a;background:#f8fafc}.attendance-note-view{max-width:220px;padding:.35rem .5rem;border:1px solid #d7e2f1;border-radius:8px;color:#42618d;background:#f6f9fd;font-size:.72rem;text-align:left}.attendance-note-view:hover{border-color:#85a7d6;background:#edf5ff}.attendance-monitor-pagination{padding:.8rem 1rem;border-top:1px solid #edf1f6}.attendance-monitor-pagination nav{margin:0}.attendance-monitor-no-results{display:flex;align-items:center;justify-content:center;flex-direction:column;gap:.25rem;padding:2.7rem;color:#8793a4}.attendance-monitor-no-results i{font-size:1.5rem}.attendance-monitor-no-results strong{color:#475569}.attendance-monitor-empty{padding:4rem 2rem;text-align:center}.attendance-monitor-empty i{font-size:2rem;color:#5577e2}.attendance-monitor-empty h3{margin:1rem 0 .25rem;font-weight:800}.attendance-monitor-empty p{color:#748094}.attendance-note-modal{overflow:hidden;border:0;border-radius:16px}.attendance-note-modal__top{display:flex;align-items:center;gap:.7rem;padding:1rem 1.1rem;color:#fff;background:linear-gradient(135deg,#3e68d8,#3192b4)}.attendance-note-modal__top>span{display:inline-flex;width:38px;height:38px;align-items:center;justify-content:center;border-radius:11px;background:rgba(255,255,255,.17)}.attendance-note-modal__top div{flex:1}.attendance-note-modal__top small,.attendance-note-modal__top h4{display:block;margin:0}.attendance-note-modal__top small{font-size:.62rem;font-weight:800;letter-spacing:.08em;opacity:.85}.attendance-note-modal__top h4{font-size:1rem;font-weight:800}.attendance-note-modal__top .close{color:#fff;opacity:.8}.attendance-note-modal .modal-body p{margin:0;padding:.75rem;border-left:3px solid #4c77df;border-radius:5px;color:#37455d;background:#f5f8fd;white-space:pre-wrap}@media(max-width:1100px){.attendance-monitor-kpis{grid-template-columns:repeat(3,1fr)}.attendance-monitor-filter form{grid-template-columns:repeat(2,1fr)}.attendance-monitor-reset{width:100%}}@media(max-width:767px){.attendance-monitor-heading{align-items:flex-start;flex-direction:column}.attendance-monitor-heading__actions{width:100%;flex-wrap:wrap}.attendance-monitor-heading__actions>span{flex:1}.attendance-monitor-kpis{grid-template-columns:repeat(2,1fr)}.attendance-monitor-filter form{grid-template-columns:1fr}.attendance-monitor-filter>div{align-items:flex-start;flex-direction:column}.attendance-monitor-kpi:last-child{grid-column:1/-1}}
</style>
@stop

@section('js')
<script>
$(function () {
    $('#monitoringFilterForm select, #monitoringFilterForm input[name="tanggal"]').on('change', function () {
        this.form.submit();
    });

    $('.attendance-note-view').on('click', function () {
        $('#attendanceNoteViewStudent').text($(this).data('student'));
        $('#attendanceNoteViewText').text($(this).data('note'));
        $('#attendanceNoteViewModal').modal('show');
    });
});
</script>
@stop

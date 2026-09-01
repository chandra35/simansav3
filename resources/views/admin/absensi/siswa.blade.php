@extends('adminlte::page')

@section('title', 'Absensi Siswa')

@php
    $statusOptions = [
        'hadir' => ['label' => 'Hadir', 'class' => 'success', 'icon' => 'fa-check-circle'],
        'terlambat' => ['label' => 'Terlambat', 'class' => 'warning', 'icon' => 'fa-clock'],
        'izin' => ['label' => 'Izin', 'class' => 'info', 'icon' => 'fa-envelope-open-text'],
        'sakit' => ['label' => 'Sakit', 'class' => 'primary', 'icon' => 'fa-notes-medical'],
        'alpa' => ['label' => 'Alpa', 'class' => 'danger', 'icon' => 'fa-times-circle'],
        'dispen' => ['label' => 'Dispen', 'class' => 'secondary', 'icon' => 'fa-id-badge'],
        'keluar_awal' => ['label' => 'Keluar Awal', 'class' => 'dark', 'icon' => 'fa-sign-out-alt'],
    ];
    $presentCount = ($summary['hadir'] ?? 0) + ($summary['terlambat'] ?? 0) + ($summary['keluar_awal'] ?? 0);
    $exceptionCount = collect($summary)->except(['hadir', 'terlambat', 'keluar_awal'])->sum();
@endphp

@section('content_header')
    <div class="attendance-hero">
        <div>
            <span><i class="fas fa-user-check mr-2"></i>PRESENSI SISWA</span>
            <h1>Absensi Harian & Mapel</h1>
            <p>Kelola absensi harian dan kehadiran per mata pelajaran berdasarkan kelas serta jadwal aktif.</p>
        </div>
        <div class="attendance-hero__meta">
            <div><small>Tahun Pelajaran Aktif</small><strong>{{ $tahunPelajaran?->nama ?? 'Belum tersedia' }}</strong></div>
            @can('monitor-all-student-attendance')
                <a href="{{ route('admin.absensi-siswa.monitoring') }}" class="btn btn-light"><i class="fas fa-clipboard-list mr-1"></i>Pemantauan</a>
            @endcan
            @can('view-attendance-analytics')
                <a href="{{ route('admin.absensi-siswa.analytics') }}" class="btn btn-light"><i class="fas fa-chart-line mr-1"></i>Analitik</a>
            @endcan
        </div>
    </div>
@stop

@section('content')
    @if(!$tahunPelajaran)
        <div class="attendance-empty"><i class="fas fa-calendar-times"></i><h3>Tahun pelajaran aktif belum ditetapkan</h3><p>Absensi baru tidak dapat dibuat sebelum admin mengaktifkan tahun pelajaran.</p></div>
    @elseif(!$mode)
        <div class="attendance-empty"><i class="fas fa-lock"></i><h3>Tidak ada jadwal absensi yang dapat dikelola</h3><p>Akun ini dapat melihat modul, tetapi tidak mempunyai kelas wali atau jadwal mengajar pada tahun aktif.</p>@can('view-attendance-analytics')<a href="{{ route('admin.absensi-siswa.analytics') }}" class="btn btn-primary">Buka Analitik</a>@endcan</div>
    @else
        <section class="attendance-filter mb-4">
            <div class="attendance-section-head"><div><h2><i class="fas fa-filter mr-2"></i>Pilih Sesi</h2><p>{{ $isGlobalScope ? 'Anda dapat memilih seluruh kelas aktif; setiap perubahan tetap tercatat dalam audit.' : 'Kelas dan jadwal dibatasi otomatis sesuai hak akses akun.' }}</p></div><span class="badge badge-light border">Metode: Manual</span></div>
            <form method="GET" action="{{ route('admin.absensi-siswa.index') }}" id="attendanceFilterForm" class="attendance-filter__form row g-3 align-items-end">
                <div class="form-group col-12 col-md-6 col-lg-2"><label>Tanggal</label><input type="date" name="tanggal" value="{{ $tanggal }}" max="{{ now()->format('Y-m-d') }}" class="form-control"></div>
                <div class="form-group col-12 col-md-6 col-lg-2"><label>Mode</label><select name="mode" class="form-control">@if($canManageHarian)<option value="harian" @selected($mode==='harian')>{{ $isGlobalScope ? 'Harian Semua Kelas' : 'Harian Wali Kelas' }}</option>@endif @if($canManageMapel)<option value="mapel" @selected($mode==='mapel')>Per Mapel</option>@endif</select></div>
                <div class="form-group col-12 col-lg-3"><label>Kelas</label><select name="kelas_id" class="form-control"><option value="">Pilih kelas</option>@foreach($kelasOptions as $kelas)<option value="{{ $kelas->id }}" @selected($selectedKelas?->id===$kelas->id)>Tingkat {{ $kelas->tingkat }} · {{ $kelas->nama_kelas }}{{ $kelas->asrama_suffix }}</option>@endforeach</select></div>
                @if($mode === 'mapel')
                    <div class="form-group col-12 col-lg-10 attendance-filter__schedule"><label>{{ $isGlobalScope ? 'Jadwal Kelas Hari Ini' : 'Jadwal Saya Hari Ini' }}</label><select name="jadwal_pelajaran_id" class="form-control"><option value="">Pilih jadwal</option>@foreach($jadwalOptions as $jadwal)<option value="{{ $jadwal->id }}" @selected($selectedJadwalId===$jadwal->id)>Jam {{ $jadwal->jam_ke }} · {{ substr($jadwal->jam_mulai,0,5) }}–{{ substr($jadwal->jam_selesai,0,5) }} · {{ $jadwal->mapel_nama ?? 'Mapel' }}</option>@endforeach</select></div>
                @endif
                <div class="col-12 attendance-filter__action"><button class="btn btn-primary attendance-form__button"><i class="fas fa-sync-alt mr-1"></i>Muat Sesi</button></div>
            </form>
        </section>

        @if($mode === 'harian' && $kelasOptions->isNotEmpty())
            <section class="attendance-daily-summary mb-4">
                <div class="attendance-section-head"><div><h2><i class="fas fa-chart-pie mr-2"></i>Rekapan Hari Ini</h2><p>Ringkasan seluruh kelas yang dapat Anda akses pada {{ Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}.</p></div></div>
                <div class="row">
                    <div class="col-12 col-md-4 mb-3 mb-md-0"><div class="attendance-daily-summary__item is-blue"><span>Semua Siswa</span><strong>{{ $dailyAttendanceStats['total'] }}</strong></div></div>
                    <div class="col-12 col-md-4 mb-3 mb-md-0"><div class="attendance-daily-summary__item is-green"><span>Jumlah Hadir</span><strong>{{ $dailyAttendanceStats['present'] }}</strong></div></div>
                    <div class="col-12 col-md-4"><div class="attendance-daily-summary__item is-yellow"><span>Jumlah Tidak Hadir</span><strong>{{ $dailyAttendanceStats['absent'] }}</strong></div></div>
                </div>
            </section>

            <section class="attendance-class-list mb-4">
                <div class="attendance-section-head"><div><h2><i class="fas fa-school mr-2"></i>Daftar Kelas</h2><p>Pilih kelas untuk membuka detail siswa dan mengatur presensinya.</p></div></div>
                <div class="row">
                    @foreach($kelasOptions as $kelas)
                        @php($classSummary = $classAttendanceSummary->get($kelas->id))
                        <div class="col-12 col-sm-6 col-lg-3 mb-3">
                            <a href="{{ route('admin.absensi-siswa.index', ['tanggal' => $tanggal, 'mode' => 'harian', 'kelas_id' => $kelas->id]) }}" class="attendance-class-link {{ $selectedKelas?->id === $kelas->id ? 'is-active' : '' }} {{ ($classSummary->absent ?? 0) > 0 ? 'has-absence' : '' }}">
                                <span class="attendance-class-link__meta">Tingkat {{ $kelas->tingkat }}</span>
                                <strong>{{ $kelas->nama_kelas }}{{ $kelas->asrama_suffix }}</strong>
                                <span class="attendance-class-link__teacher"><i class="fas fa-chalkboard-teacher mr-1"></i>Wali kelas: {{ $kelas->waliKelas?->name ?? 'Belum ditetapkan' }}</span>
                                <span class="attendance-class-link__attendance"><span>Hadir <b>{{ $classSummary->present ?? 0 }}</b></span><span>Tidak Hadir <b>{{ $classSummary->absent ?? 0 }}</b></span></span>
                                <small><i class="fas {{ $selectedKelas?->id === $kelas->id ? 'fa-check-circle' : 'fa-arrow-right' }} mr-1"></i>{{ $selectedKelas?->id === $kelas->id ? 'Sedang dibuka' : 'Buka presensi' }}</small>
                            </a>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if($canBulkGenerate)
            <section class="attendance-bulk mb-4">
                <div class="attendance-bulk__header"><div class="attendance-bulk__title"><span class="attendance-bulk__icon"><i class="fas fa-layer-group"></i></span><div><h2>Buat Draft Massal</h2><p>Siapkan presensi harian lebih cepat; sesi yang sudah ada tetap aman dan tidak ditimpa.</p></div></div><span class="attendance-bulk__badge"><i class="fas fa-shield-alt mr-1"></i>Admin saja</span></div>
                <form method="POST" action="{{ route('admin.absensi-siswa.generate-draft') }}" id="bulkDraftForm" class="attendance-bulk__form row g-3 align-items-end">
                    @csrf
                    <div class="form-group col-12 col-md-6 col-lg-2"><label>Tanggal</label><input type="date" name="tanggal" value="{{ $tanggal }}" max="{{ now()->format('Y-m-d') }}" class="form-control" required></div>
                    <div class="form-group col-12 col-md-6 col-lg-3"><label>Cakupan</label><select name="scope" id="bulkDraftScope" class="form-control"><option value="all">Semua kelas aktif</option><option value="selected">Kelas tertentu</option></select></div>
                    <div class="form-group col-12 bulk-draft-classes d-none"><label>Pilih kelas</label><select name="kelas_ids[]" class="form-control" multiple size="4">@foreach($kelasOptions as $kelas)<option value="{{ $kelas->id }}">Tingkat {{ $kelas->tingkat }} · {{ $kelas->nama_kelas }}</option>@endforeach</select></div>
                    <div class="col-12 attendance-bulk__action"><button type="submit" class="btn btn-primary attendance-form__button"><i class="fas fa-file-medical mr-1"></i>Buat Draft</button></div>
                </form>
            </section>
        @endif

        @if($mode === 'harian' && $canBulkFinalize)
            <section class="attendance-bulk-finalize mb-4">
                <div class="attendance-bulk__header"><div class="attendance-bulk__title"><span class="attendance-bulk__icon"><i class="fas fa-lock"></i></span><div><h2>Finalisasi Harian Massal</h2><p>Tinjau status setiap kelas pada {{ Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}, lalu finalkan hanya draft yang dipilih.</p></div></div><span class="attendance-bulk__badge"><i class="fas fa-shield-alt mr-1"></i>Admin saja</span></div>
                <div class="row mb-3">
                    <div class="col-6 col-lg-3 mb-2 mb-lg-0"><div class="attendance-bulk-finalize__summary"><small>Semua Kelas</small><strong>{{ $bulkFinalizationSummary['total'] }}</strong></div></div>
                    <div class="col-6 col-lg-3 mb-2 mb-lg-0"><div class="attendance-bulk-finalize__summary is-warning"><small>Belum Ada Sesi</small><strong>{{ $bulkFinalizationSummary['missing'] }}</strong></div></div>
                    <div class="col-6 col-lg-3"><div class="attendance-bulk-finalize__summary is-primary"><small>Draft</small><strong>{{ $bulkFinalizationSummary['draft'] }}</strong></div></div>
                    <div class="col-6 col-lg-3"><div class="attendance-bulk-finalize__summary is-success"><small>Sudah Final</small><strong>{{ $bulkFinalizationSummary['final'] }}</strong></div></div>
                </div>
                @if($bulkFinalizationSummary['draft'] > 0)
                    <form method="POST" action="{{ route('admin.absensi-siswa.finalize-draft') }}" id="bulkFinalizeForm">
                        @csrf
                        <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                        <div class="attendance-bulk-finalize__list">
                            <div class="attendance-bulk-finalize__list-head"><label class="mb-0"><input type="checkbox" id="toggleAllBulkDrafts" checked> Pilih semua draft</label><small><span id="bulkFinalizeSelectedCount">{{ $bulkFinalizationSummary['draft'] }}</span> kelas siap difinalkan</small></div>
                            <div class="row">
                                @foreach($bulkFinalizationClasses as $entry)
                                    @php($kelas = $entry['kelas'])
                                    @php($dailySession = $entry['session'])
                                    @php($sessionState = $dailySession?->status ?? 'missing')
                                    <div class="col-12 col-md-6 col-xl-4 mb-2">
                                        <label class="attendance-bulk-finalize__class is-{{ $sessionState }} mb-0">
                                            @if($sessionState === 'draft')
                                                <input type="checkbox" name="session_ids[]" value="{{ $dailySession->id }}" class="bulk-finalize-session" checked>
                                            @else
                                                <span class="attendance-bulk-finalize__state"><i class="fas {{ $sessionState === 'final' ? 'fa-check-circle' : 'fa-circle-minus' }}"></i></span>
                                            @endif
                                            <span><strong>Tingkat {{ $kelas->tingkat }} · {{ $kelas->nama_kelas }}{{ $kelas->asrama_suffix }}</strong><small>@if($sessionState === 'draft') Draft · Hadir {{ $dailySession->present_records_count }} · Tidak Hadir {{ $dailySession->exception_records_count }}@elseif($sessionState === 'final') Sudah final @else Belum dibuatkan sesi @endif</small></span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="attendance-bulk-finalize__actions"><small><i class="fas fa-info-circle mr-1"></i>Kelas tanpa sesi tidak ikut difinalkan. Finalisasi dicatat per kelas dan dapat dikoreksi sesuai permission.</small><button type="submit" class="btn btn-success"><i class="fas fa-lock mr-1"></i>Finalkan Draft Terpilih</button></div>
                    </form>
                @else
                    <div class="attendance-bulk-finalize__empty"><i class="fas fa-check-double mr-1"></i>Tidak ada draft untuk difinalkan pada tanggal ini. Ubah tanggal di filter untuk melihat status hari lain.</div>
                @endif
            </section>
        @endif

        @if($selectedKelas && ($mode === 'harian' || $selectedJadwalId))
            <div class="attendance-kpis">
                <div class="attendance-kpi is-blue"><span>Total Siswa</span><strong>{{ $students->count() }}</strong><small>{{ $selectedKelas->nama_kelas }}{{ $selectedKelas->asrama_suffix }} · Tingkat {{ $selectedKelas->tingkat }}</small></div>
                <div class="attendance-kpi is-green"><span>Hadir Mengikuti</span><strong>{{ $presentCount }}</strong><small>Termasuk terlambat dan keluar awal</small></div>
                <div class="attendance-kpi is-red"><span>Perlu Keterangan</span><strong>{{ $exceptionCount }}</strong><small>Sakit, izin, alpa, atau dispensasi</small></div>
                <div class="attendance-kpi is-purple"><span>Status Sesi</span><strong class="text-capitalize">{{ $session?->status ?? 'Baru' }}</strong><small>{{ $session ? 'Versi '.$session->version.' · '.$session->updated_at?->format('d/m/Y H:i') : 'Belum pernah disimpan' }}</small></div>
            </div>

            <section class="attendance-panel">
                <div class="attendance-section-head attendance-panel__head">
                    <div><h2>{{ $mode === 'harian' ? ($isGlobalScope ? 'Absensi Harian Siswa' : 'Absensi Harian Wali Kelas') : ($jadwalOptions->firstWhere('id',$selectedJadwalId)?->mapel_nama ?? 'Absensi Mapel') }}</h2><p>{{ Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }} · {{ $selectedKelas->nama_kelas }}{{ $selectedKelas->asrama_suffix }} · Input manual oleh {{ $isGlobalScope ? 'petugas' : 'guru' }}</p></div>
                    @if($session?->status === 'final')<span class="session-state is-final"><i class="fas fa-lock mr-1"></i>Final · terkunci {{ $session->locked_at?->format('d/m H:i') }}</span>@else<span class="session-state"><i class="fas fa-pencil-alt mr-1"></i>{{ $session ? 'Draft' : 'Sesi baru' }}</span>@endif
                </div>

                @if($students->isEmpty())
                    <div class="attendance-empty is-inline"><i class="fas fa-users-slash"></i><h3>Tidak ada siswa pada tanggal ini</h3><p>Sistem membaca riwayat tanggal masuk dan keluar kelas, bukan hanya status kelas saat ini.</p></div>
                @else
                    <form method="POST" action="{{ route('admin.absensi-siswa.store') }}" id="attendanceForm">
                        @csrf
                        <input type="hidden" name="tanggal" value="{{ $tanggal }}"><input type="hidden" name="mode" value="{{ $mode }}"><input type="hidden" name="kelas_id" value="{{ $selectedKelas->id }}">@if($mode==='mapel')<input type="hidden" name="jadwal_pelajaran_id" value="{{ $selectedJadwalId }}">@endif
                        <div class="attendance-toolbar">
                            <div class="attendance-quick-actions"><button type="button" class="btn btn-sm btn-success quick-status" data-status="hadir"><i class="fas fa-check-double mr-1"></i>Semua Hadir</button><button type="button" class="btn btn-sm btn-outline-danger quick-status" data-status="alpa">Semua Alpa</button></div>
                            <div class="attendance-student-search"><i class="fas fa-search"></i><input type="search" id="attendanceStudentSearch" class="form-control form-control-sm" autocomplete="off" aria-controls="attendanceStudentSearchResults" placeholder="Cari siswa di semua tingkat dan kelas (nama atau NISN)"><div id="attendanceStudentSearchResults" class="attendance-student-search__results d-none" aria-live="polite"></div></div>
                            <small><i class="fas fa-history mr-1"></i>Setiap perubahan status dan pelakunya dicatat.</small>
                        </div>
                        <div class="table-responsive">
                            <table class="table attendance-table">
                                <thead><tr><th>#</th><th>Siswa</th><th>Status</th><th>Durasi</th><th>Catatan</th></tr></thead>
                                <tbody>
                                @foreach($students as $index => $siswa)
                                    @php($record=$existingRecords->get($siswa->id))
                                    @php($currentStatus=old("statuses.$siswa->id",$record?->status??'hadir'))
                                    <tr id="siswa-{{ $siswa->id }}" class="attendance-student-row {{ $currentStatus !== 'hadir' ? 'is-attendance-exception' : '' }}" data-search="{{ strtolower($siswa->nama_lengkap.' '.$siswa->nisn) }}">
                                        <td>{{ $siswa->pivot->nomor_urut_absen ?: $index+1 }}</td>
                                        <td><strong>{{ $siswa->nama_lengkap }}</strong><small>NISN {{ $siswa->nisn ?: '-' }}</small></td>
                                        <td><select name="statuses[{{ $siswa->id }}]" class="form-control form-control-sm student-status">@foreach($statusOptions as $value=>$meta)<option value="{{ $value }}" @selected($currentStatus===$value)>{{ $meta['label'] }}</option>@endforeach</select></td>
                                        <td class="duration-cell"><div class="duration-field late-field {{ $currentStatus==='terlambat'?'':'d-none' }}"><input type="number" min="1" max="600" name="late_minutes[{{ $siswa->id }}]" value="{{ old("late_minutes.$siswa->id",$record?->late_minutes) }}" class="form-control form-control-sm" placeholder="Menit"><small>menit terlambat</small></div><div class="duration-field early-field {{ $currentStatus==='keluar_awal'?'':'d-none' }}"><input type="number" min="1" max="600" name="left_early_minutes[{{ $siswa->id }}]" value="{{ old("left_early_minutes.$siswa->id",$record?->left_early_minutes) }}" class="form-control form-control-sm" placeholder="Menit"><small>menit lebih awal</small></div><span class="duration-empty {{ in_array($currentStatus,['terlambat','keluar_awal'])?'d-none':'' }}">—</span></td>
                                        <td>
                                            @php($noteValue=old("notes.$siswa->id",$record?->notes))
                                            <input type="hidden" name="notes[{{ $siswa->id }}]" value="{{ $noteValue }}"
                                                   id="studentNote{{ $siswa->id }}" class="student-note-input">
                                            <button type="button"
                                                    class="student-note-trigger {{ filled($noteValue) ? 'has-note' : '' }}"
                                                    data-note-target="studentNote{{ $siswa->id }}"
                                                    data-student-name="{{ $siswa->nama_lengkap }}">
                                                <span class="student-note-trigger__icon"><i class="fas {{ filled($noteValue) ? 'fa-comment-dots' : 'fa-plus' }}"></i></span>
                                                <span>
                                                    <strong>{{ filled($noteValue) ? 'Ada catatan' : 'Tambah catatan' }}</strong>
                                                    <small class="student-note-summary">{{ filled($noteValue) ? \Illuminate\Support\Str::limit($noteValue, 34) : 'Keterangan opsional' }}</small>
                                                </span>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="attendance-form-notes">
                            <div class="form-group mb-0"><label>Catatan sesi</label><textarea name="session_notes" rows="2" class="form-control" placeholder="Catatan umum kegiatan belajar">{{ old('session_notes',$session?->notes) }}</textarea></div>
                            @if($session?->status==='final')<div class="form-group mb-0"><label>Alasan perubahan <span class="text-danger">*</span></label><textarea name="revision_reason" rows="2" class="form-control" required placeholder="Jelaskan alasan koreksi data final">{{ old('revision_reason') }}</textarea></div>@endif
                        </div>
                        <div class="attendance-actions"><div><strong>Draft</strong><small>dapat dilanjutkan sebelum difinalkan.</small></div><button type="submit" name="submit_action" value="draft" class="btn btn-outline-primary"><i class="fas fa-save mr-1"></i>Simpan Draft</button><button type="submit" name="submit_action" value="final" class="btn btn-success btn-finalize"><i class="fas fa-lock mr-1"></i>Finalkan Absensi</button></div>
                    </form>
                @endif
            </section>
        @elseif($kelasOptions->isEmpty())
            <div class="attendance-empty"><i class="fas fa-calendar-day"></i><h3>Tidak ada kelas atau jadwal pada tanggal ini</h3><p>{{ $isGlobalScope ? 'Pastikan kelas dan jadwal pada tahun pelajaran aktif telah tersedia.' : 'Guru mapel hanya melihat kelas yang sesuai jadwal aktif pada hari terpilih.' }}</p></div>
        @endif
    @endif

    <div class="modal fade" id="studentNoteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content student-note-modal">
                <div class="student-note-modal__header">
                    <span class="student-note-modal__icon"><i class="fas fa-comment-medical"></i></span>
                    <div>
                        <small>CATATAN PER SISWA</small>
                        <h4 id="studentNoteName">Nama siswa</h4>
                    </div>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <label for="studentNoteEditor">Keterangan kehadiran</label>
                    <textarea id="studentNoteEditor" class="form-control" rows="5" maxlength="500"
                              placeholder="Contoh: izin mengikuti kegiatan madrasah, surat menyusul, atau informasi penting lainnya."></textarea>
                    <div class="student-note-modal__helper">
                        <span><i class="fas fa-info-circle mr-1"></i>Tersimpan bersama draft atau finalisasi absensi.</span>
                        <strong><span id="studentNoteCount">0</span>/500</strong>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger mr-auto" id="btnClearStudentNote">
                        <i class="fas fa-eraser mr-1"></i>Hapus Catatan
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnApplyStudentNote">
                        <i class="fas fa-check mr-1"></i>Terapkan Catatan
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<style>
.attendance-hero{display:flex;justify-content:space-between;align-items:center;gap:2rem;padding:1.6rem 1.8rem;border-radius:20px;background:linear-gradient(135deg,#4f7df3,#4779ed);color:#fff;box-shadow:0 16px 36px rgba(59,94,190,.18)}.attendance-hero span{font-size:.78rem;font-weight:800}.attendance-hero h1{margin:.35rem 0 .25rem;font-size:1.65rem;font-weight:800}.attendance-hero p{margin:0;opacity:.92}.attendance-hero__meta{display:flex;align-items:center;gap:.7rem}.attendance-hero__meta>div{min-width:180px;padding:.7rem 1rem;border:1px solid rgba(255,255,255,.3);border-radius:13px;background:rgba(255,255,255,.12)}.attendance-hero__meta small,.attendance-hero__meta strong{display:block}.attendance-filter,.attendance-panel,.attendance-empty{border:1px solid #dbe4f0;border-radius:18px;background:#fff;box-shadow:0 14px 35px rgba(15,23,42,.07)}.attendance-filter{padding:1.25rem}.attendance-section-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem}.attendance-section-head h2{margin:0;font-size:1.08rem;font-weight:800;color:#172033}.attendance-section-head p{margin:.25rem 0 0;color:#64748b}.attendance-filter form{display:grid;grid-template-columns:180px 210px minmax(220px,1fr) auto;gap:.9rem;align-items:end}.attendance-filter__schedule{grid-column:1/-2}.attendance-filter .form-group{margin:0}.attendance-filter label{font-size:.75rem;text-transform:uppercase;letter-spacing:.03em;color:#475569}.attendance-kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem}.attendance-kpi{position:relative;padding:1.1rem 1.2rem;border:1px solid #dbe4f0;border-top:4px solid;border-radius:16px;background:#fff}.attendance-kpi span,.attendance-kpi small{display:block;color:#64748b}.attendance-kpi span{font-size:.76rem;font-weight:800;text-transform:uppercase}.attendance-kpi strong{display:block;margin:.25rem 0;font-size:1.65rem;color:#111827}.attendance-kpi small{font-size:.76rem}.attendance-kpi.is-blue{border-top-color:#4f6ef7}.attendance-kpi.is-green{border-top-color:#22c55e}.attendance-kpi.is-red{border-top-color:#ef4444}.attendance-kpi.is-purple{border-top-color:#8b5cf6}.attendance-panel{overflow:hidden}.attendance-panel__head{padding:1.2rem 1.4rem;margin:0;border-bottom:1px solid #e5eaf2}.session-state{padding:.35rem .65rem;border-radius:999px;background:#eef2ff;color:#4f46e5;font-size:.75rem;font-weight:800}.session-state.is-final{background:#dcfce7;color:#15803d}.attendance-toolbar,.attendance-actions{display:flex;justify-content:space-between;align-items:center;gap:.8rem;padding:1rem 1.25rem}.attendance-toolbar{background:#f8fafc}.attendance-toolbar small{color:#64748b}.attendance-table{margin:0;min-width:960px}.attendance-table thead th{background:#f1f5f9;color:#526078;font-size:.72rem;text-transform:uppercase;border-bottom:1px solid #dbe4f0}.attendance-table td{vertical-align:middle;border-color:#edf0f5}.attendance-table td:nth-child(1){width:52px;text-align:center}.attendance-table td:nth-child(2){min-width:240px}.attendance-table td:nth-child(3){width:165px}.attendance-table td:nth-child(4){width:135px}.attendance-table td:nth-child(5){width:210px}.attendance-table td strong,.attendance-table td small{display:block}.attendance-table td small{color:#94a3b8}.duration-field small{margin-top:.15rem;font-size:.65rem}.attendance-form-notes{display:grid;grid-template-columns:1fr 1fr;gap:1rem;padding:1rem 1.25rem;border-top:1px solid #e5eaf2;background:#f8fafc}.attendance-actions{border-top:1px solid #e5eaf2}.attendance-actions>div{margin-right:auto}.attendance-actions strong,.attendance-actions small{display:block}.attendance-actions small{color:#64748b}.attendance-empty{padding:4rem 2rem;text-align:center}.attendance-empty i{display:inline-grid;place-items:center;width:72px;height:72px;border-radius:50%;background:#eef2ff;color:#4f6ef7;font-size:1.7rem}.attendance-empty h3{margin:1rem 0 .35rem;font-weight:800}.attendance-empty p{color:#64748b}.attendance-empty.is-inline{border:0;box-shadow:none}.attendance-alert{border:0;border-radius:12px}.student-note-trigger{display:flex;width:100%;align-items:center;gap:.5rem;padding:.42rem .5rem;border:1px dashed #b8c6da;border-radius:9px;color:#617087;background:#f8fafc;text-align:left;transition:.15s ease}.student-note-trigger:hover{border-color:#6688d8;color:#315dc3;background:#f1f5ff}.student-note-trigger.has-note{border-style:solid;border-color:#a9c2ee;color:#3159a7;background:#edf4ff}.student-note-trigger__icon{display:inline-flex;flex:0 0 28px;width:28px;height:28px;align-items:center;justify-content:center;border-radius:8px;color:#fff;background:#7890b5}.student-note-trigger.has-note .student-note-trigger__icon{background:#4f72d8}.student-note-trigger strong,.student-note-trigger small{display:block;max-width:145px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.student-note-trigger strong{font-size:.72rem}.student-note-trigger small{font-size:.65rem}.student-note-modal{overflow:hidden;border:0;border-radius:17px}.student-note-modal__header{display:flex;align-items:center;gap:.7rem;padding:1rem 1.1rem;color:#fff;background:linear-gradient(135deg,#426bd9,#2f9cb0)}.student-note-modal__icon{display:inline-flex;width:42px;height:42px;align-items:center;justify-content:center;border-radius:12px;background:rgba(255,255,255,.18);font-size:1.1rem}.student-note-modal__header>div{flex:1}.student-note-modal__header small,.student-note-modal__header h4{display:block;margin:0}.student-note-modal__header small{font-size:.62rem;font-weight:800;letter-spacing:.08em;opacity:.85}.student-note-modal__header h4{font-size:1.02rem;font-weight:800}.student-note-modal__header .close{color:#fff;opacity:.8}.student-note-modal .modal-body label{color:#3d4b61;font-size:.76rem;font-weight:800}.student-note-modal .modal-body textarea{border-color:#cfd9e7;border-radius:10px;resize:vertical}.student-note-modal__helper{display:flex;justify-content:space-between;gap:.6rem;margin-top:.45rem;color:#7b8797;font-size:.68rem}.student-note-modal__helper strong{color:#4265b2}@media(max-width:1100px){.attendance-kpis{grid-template-columns:repeat(2,1fr)}.attendance-filter form{grid-template-columns:repeat(2,1fr)}.attendance-filter__schedule{grid-column:auto}.attendance-filter button{height:38px}}@media(max-width:767px){.attendance-hero{align-items:flex-start;flex-direction:column}.attendance-hero__meta{width:100%;flex-wrap:wrap}.attendance-hero__meta>div{flex:1}.attendance-filter form,.attendance-kpis,.attendance-form-notes{grid-template-columns:1fr}.attendance-actions{flex-wrap:wrap}.attendance-actions>div{width:100%}.attendance-actions .btn{flex:1}.attendance-section-head{align-items:flex-start;gap:.7rem}.student-note-modal .modal-footer{flex-wrap:wrap}.student-note-modal .modal-footer .btn{flex:1}.student-note-modal .modal-footer .mr-auto{flex-basis:100%;margin-right:0!important}}
</style>
<style>
.attendance-bulk{overflow:hidden;padding:1.15rem 1.25rem 1.2rem;border:1px solid #c7d7fe;border-radius:16px;background:linear-gradient(135deg,#f8fbff 0%,#edf4ff 100%);box-shadow:0 10px 25px rgba(37,99,235,.08)}.attendance-bulk__header{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1rem}.attendance-bulk__title{display:flex;align-items:flex-start;gap:.75rem}.attendance-bulk__icon{display:grid;place-items:center;flex:0 0 38px;width:38px;height:38px;border-radius:11px;background:linear-gradient(135deg,#2563eb,#4f46e5);color:#fff;box-shadow:0 6px 13px rgba(37,99,235,.22)}.attendance-bulk h2{margin:0;color:#172033;font-size:1.08rem;font-weight:800}.attendance-bulk p{margin:.2rem 0 0;color:#64748b;font-size:.78rem}.attendance-bulk__badge{display:inline-flex;align-items:center;white-space:nowrap;padding:.35rem .6rem;border:1px solid #c7d7fe;border-radius:999px;background:#fff;color:#3159a7;font-size:.68rem;font-weight:800}.attendance-bulk form{display:grid;grid-template-columns:190px 240px minmax(230px,1fr);gap:.8rem;align-items:end;padding:.85rem;border:1px solid rgba(191,219,254,.8);border-radius:13px;background:rgba(255,255,255,.72)}.attendance-bulk .form-group{margin:0}.attendance-bulk label{display:block;margin-bottom:.35rem;color:#475569;font-size:.68rem;font-weight:800;letter-spacing:.045em;text-transform:uppercase}.attendance-bulk .form-control{height:38px;border-color:#d6e1f1;border-radius:8px;font-size:.8rem}.attendance-bulk .form-control:focus{border-color:#60a5fa;box-shadow:0 0 0 .14rem rgba(37,99,235,.12)}.attendance-bulk .bulk-draft-classes{grid-column:1/-1}.attendance-bulk .bulk-draft-classes select{height:auto;min-height:110px}.attendance-bulk__submit{grid-column:3;min-width:128px;height:38px;justify-self:end;padding:0 .95rem;border:0;border-radius:8px;font-size:.78rem;font-weight:800;box-shadow:0 7px 14px rgba(79,70,229,.2)}.attendance-toolbar{display:grid;grid-template-columns:auto minmax(240px,330px) 1fr;gap:1rem;align-items:center}.attendance-quick-actions{display:flex;align-items:center;gap:.35rem}.attendance-quick-actions .btn{font-weight:800}.attendance-student-search{position:relative;width:100%}.attendance-student-search i{position:absolute;z-index:1;top:.55rem;left:.62rem;color:#94a3b8;font-size:.74rem}.attendance-student-search input{height:34px;padding-left:1.9rem;border-color:#d9e2ee;border-radius:8px;font-size:.76rem}.attendance-toolbar>small{justify-self:end;color:#64748b;font-size:.7rem}.attendance-kpi{box-shadow:0 8px 19px rgba(15,23,42,.045)}@media(max-width:1100px){.attendance-bulk form{grid-template-columns:repeat(2,minmax(0,1fr))}.attendance-bulk__submit{grid-column:auto;justify-self:stretch}.attendance-toolbar{grid-template-columns:1fr 1fr}.attendance-toolbar>small{grid-column:1/-1;justify-self:start}}@media(max-width:767px){.attendance-bulk{padding:1rem}.attendance-bulk__header{flex-direction:column}.attendance-bulk form,.attendance-toolbar{grid-template-columns:1fr}.attendance-bulk__submit{width:100%}.attendance-toolbar>small{grid-column:auto}.attendance-quick-actions .btn{flex:1}.attendance-student-search{width:100%}}
</style>
<style>
.attendance-filter form.attendance-filter__form,.attendance-bulk form.attendance-bulk__form{display:flex;flex-wrap:wrap;align-items:flex-end;margin:0}.attendance-filter__form>.form-group,.attendance-bulk__form>.form-group,.attendance-filter__action,.attendance-bulk__action{margin-bottom:0;padding:.4rem}.attendance-filter__form>.form-group:nth-of-type(3){flex:0 0 50%;max-width:50%}.attendance-filter__schedule{flex:0 0 83.333333%;max-width:83.333333%}.attendance-bulk__form>.bulk-draft-classes{flex:0 0 100%;max-width:100%}.attendance-filter__action,.attendance-bulk__action{display:flex;align-items:flex-end;justify-content:flex-end}.attendance-filter__action .btn,.attendance-bulk__submit{width:100%;min-height:38px}.attendance-bulk__submit{max-width:160px}@media(max-width:767px){.attendance-filter__form>.form-group:nth-of-type(3),.attendance-filter__schedule,.attendance-filter__action,.attendance-bulk__action{flex:0 0 100%;max-width:100%}.attendance-bulk__submit{max-width:none}}
</style>
<style>
.attendance-filter .attendance-filter__form,.attendance-bulk .attendance-bulk__form{display:flex;flex-wrap:wrap;align-items:flex-end;gap:0;margin:-.5rem}.attendance-filter .attendance-filter__form>[class*="col-"],.attendance-bulk .attendance-bulk__form>[class*="col-"]{margin-bottom:0;padding:.5rem}.attendance-filter .attendance-filter__form>.form-group.col-lg-3{flex:0 0 25%;max-width:25%}.attendance-filter .attendance-filter__form>.attendance-filter__schedule{flex:0 0 83.333333%;max-width:83.333333%}.attendance-bulk .attendance-bulk__form>.bulk-draft-classes{flex:0 0 100%;max-width:100%}.attendance-filter__action,.attendance-bulk__action{display:flex;justify-content:flex-start}.attendance-filter__action .attendance-form__button,.attendance-bulk__action .attendance-form__button{width:170px;min-height:38px}.attendance-class-list{padding:1.1rem 1.25rem;border:1px solid #dbe4f0;border-radius:18px;background:#fff;box-shadow:0 10px 24px rgba(15,23,42,.05)}.attendance-class-list .attendance-section-head{margin-bottom:.75rem}.attendance-class-link{display:block;height:100%;padding:.9rem 1rem;border:1px solid #dbe4f0;border-radius:12px;background:#fff;color:#172033;box-shadow:0 4px 10px rgba(15,23,42,.03);transition:border-color .15s ease,box-shadow .15s ease,text-decoration .15s ease}.attendance-class-link:hover{border-color:#7da2f8;box-shadow:0 7px 15px rgba(37,99,235,.1);color:#243f93;text-decoration:none}.attendance-class-link.is-active{border-color:#4f6ef7;background:#f3f6ff}.attendance-class-link__meta,.attendance-class-link strong,.attendance-class-link small{display:block}.attendance-class-link__meta{margin-bottom:.2rem;color:#64748b;font-size:.68rem;font-weight:800;text-transform:uppercase}.attendance-class-link strong{font-size:.92rem}.attendance-class-link small{margin-top:.45rem;color:#4f6ef7;font-size:.72rem;font-weight:800}.attendance-student-search input{padding-left:2.25rem}.attendance-student-search__results{position:absolute;z-index:20;top:calc(100% + .35rem);right:0;left:0;overflow:hidden;border:1px solid #cad7eb;border-radius:10px;background:#fff;box-shadow:0 12px 25px rgba(15,23,42,.14)}.attendance-student-search__result,.attendance-student-search__empty{display:block;padding:.6rem .75rem;color:#26364e;font-size:.75rem}.attendance-student-search__result+.attendance-student-search__result{border-top:1px solid #edf1f7}.attendance-student-search__result:hover{background:#f4f7ff;color:#2f5ec4;text-decoration:none}.attendance-student-search__result strong,.attendance-student-search__result small{display:block}.attendance-student-search__result small{margin-top:.1rem;color:#738198;font-size:.68rem}.attendance-student-search__empty{color:#738198}tr:target td{background:#fff7cf}@media(max-width:991px){.attendance-filter .attendance-filter__form>.form-group.col-lg-3,.attendance-filter .attendance-filter__form>.attendance-filter__schedule{flex:0 0 100%;max-width:100%}}@media(max-width:767px){.attendance-filter__action,.attendance-bulk__action{justify-content:stretch}.attendance-filter__action .attendance-form__button,.attendance-bulk__action .attendance-form__button{width:100%}}
</style>
<style>
.attendance-daily-summary{padding:1.1rem 1.25rem;border:1px solid #dbe4f0;border-radius:18px;background:#fff;box-shadow:0 10px 24px rgba(15,23,42,.05)}.attendance-daily-summary .attendance-section-head{margin-bottom:.85rem}.attendance-daily-summary__item{height:100%;padding:.9rem 1rem;border:1px solid #dbe4f0;border-left:4px solid;border-radius:12px;background:#f8fafc}.attendance-daily-summary__item span,.attendance-daily-summary__item strong{display:block}.attendance-daily-summary__item span{color:#62718a;font-size:.7rem;font-weight:800;text-transform:uppercase}.attendance-daily-summary__item strong{margin-top:.25rem;color:#172033;font-size:1.5rem}.attendance-daily-summary__item.is-blue{border-left-color:#4f6ef7}.attendance-daily-summary__item.is-green{border-left-color:#22c55e}.attendance-daily-summary__item.is-yellow{border-left-color:#f0ab26;background:#fffaf0}.attendance-class-link__teacher{display:block;margin-top:.3rem;color:#6b788c;font-size:.7rem}.attendance-class-link__attendance{display:flex;gap:.45rem;margin-top:.55rem}.attendance-class-link__attendance span{padding:.2rem .4rem;border-radius:6px;background:#eef4ff;color:#3d5e9d;font-size:.64rem;font-weight:700}.attendance-class-link__attendance b{margin-left:.15rem;color:#182b50}.attendance-class-link.has-absence{border-color:#f3c46b;background:#fffaf0}.attendance-class-link.has-absence .attendance-class-link__attendance span:last-child{background:#fff0c7;color:#9a6200}.attendance-student-row.is-attendance-exception td{background:#fff8df}.attendance-student-row.is-attendance-exception td:first-child{box-shadow:inset 3px 0 0 #f0ab26}
</style>
<style>
.attendance-bulk-finalize{padding:1.15rem 1.25rem;border:1px solid #bde3ca;border-radius:16px;background:linear-gradient(135deg,#f7fffa 0%,#edf9f1 100%);box-shadow:0 10px 25px rgba(22,163,74,.07)}.attendance-bulk-finalize__summary{height:100%;padding:.65rem .8rem;border:1px solid #d9e6df;border-radius:10px;background:#fff}.attendance-bulk-finalize__summary small,.attendance-bulk-finalize__summary strong{display:block}.attendance-bulk-finalize__summary small{color:#64748b;font-size:.66rem;font-weight:800;text-transform:uppercase}.attendance-bulk-finalize__summary strong{margin-top:.18rem;color:#172033;font-size:1.2rem}.attendance-bulk-finalize__summary.is-warning{border-color:#f2d398;background:#fffaf0}.attendance-bulk-finalize__summary.is-primary{border-color:#c9dafd;background:#f5f8ff}.attendance-bulk-finalize__summary.is-success{border-color:#bde3ca;background:#f3fcf6}.attendance-bulk-finalize__list{padding:.8rem;border:1px solid #d7e6dd;border-radius:12px;background:rgba(255,255,255,.8)}.attendance-bulk-finalize__list-head{display:flex;align-items:center;justify-content:space-between;gap:.75rem;margin-bottom:.7rem;color:#42526b;font-size:.74rem}.attendance-bulk-finalize__list-head label{font-weight:800}.attendance-bulk-finalize__list-head input{vertical-align:-1px}.attendance-bulk-finalize__class{display:flex;align-items:flex-start;gap:.55rem;width:100%;min-height:62px;padding:.62rem .7rem;border:1px solid #dce6e1;border-radius:9px;background:#fff;cursor:default}.attendance-bulk-finalize__class.is-draft{border-color:#b7dcca;cursor:pointer}.attendance-bulk-finalize__class.is-final{background:#f4fcf7}.attendance-bulk-finalize__class.is-missing{background:#fffaf1}.attendance-bulk-finalize__class input{margin-top:.17rem}.attendance-bulk-finalize__class strong,.attendance-bulk-finalize__class small{display:block}.attendance-bulk-finalize__class strong{color:#26364e;font-size:.76rem}.attendance-bulk-finalize__class small{margin-top:.14rem;color:#718096;font-size:.67rem}.attendance-bulk-finalize__state{margin-top:.05rem;color:#96a7b9}.attendance-bulk-finalize__class.is-final .attendance-bulk-finalize__state{color:#20a35b}.attendance-bulk-finalize__class.is-missing .attendance-bulk-finalize__state{color:#d48a09}.attendance-bulk-finalize__actions{display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-top:.8rem}.attendance-bulk-finalize__actions small{color:#5f7185;font-size:.7rem}.attendance-bulk-finalize__actions .btn{min-width:200px;font-weight:800}.attendance-bulk-finalize__empty{padding:.8rem;border:1px dashed #b7dcca;border-radius:10px;background:rgba(255,255,255,.7);color:#54705f;font-size:.78rem}@media(max-width:767px){.attendance-bulk-finalize{padding:1rem}.attendance-bulk-finalize__list-head,.attendance-bulk-finalize__actions{align-items:stretch;flex-direction:column}.attendance-bulk-finalize__actions .btn{width:100%}}
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
$(function(){
    const notify=window.toastr||{success:$.noop,error:$.noop};
    if(window.toastr) toastr.options={closeButton:true,progressBar:true,positionClass:'toast-top-right',timeOut:4200,preventDuplicates:true};
    @if(session('toastr_success')) notify.success(@json(session('toastr_success')), 'Berhasil'); @endif
    @if($errors->any()) notify.error(@json($errors->first()), 'Data belum dapat disimpan'); @endif

    let activeNoteInput=null;
    let activeNoteButton=null;
    const refreshNoteCount=function(){const length=$('#studentNoteEditor').val().length;$('#studentNoteCount').text(length)};
    const refreshDuration=function(select){const row=$(select).closest('tr'),status=$(select).val();row.toggleClass('is-attendance-exception',status!=='hadir');row.find('.late-field').toggleClass('d-none',status!=='terlambat');row.find('.early-field').toggleClass('d-none',status!=='keluar_awal');row.find('.duration-empty').toggleClass('d-none',['terlambat','keluar_awal'].includes(status));};
    $('.student-status').each(function(){refreshDuration(this)}).on('change',function(){refreshDuration(this)});
    $('.quick-status').on('click',function(){$('.student-status').val($(this).data('status')).trigger('change')});
    const crossClassSearchEnabled=@json($canManageHarian),studentSearchUrl=@json(route('admin.absensi-siswa.search-students')),studentSearchDate=@json($tanggal);let studentSearchTimer=null,studentSearchRequest=0;
    const hideStudentSearchResults=function(){$('#attendanceStudentSearchResults').addClass('d-none').empty()};
    const renderStudentSearchResults=function(students){const results=$('#attendanceStudentSearchResults').empty().removeClass('d-none');if(!students.length){results.append($('<div>',{class:'attendance-student-search__empty',text:'Siswa tidak ditemukan pada kelas aktif yang dapat Anda akses.'}));return}students.forEach(function(student){const item=$('<a>',{class:'attendance-student-search__result',href:student.url});item.append($('<strong>').text(student.name),$('<small>').text((student.nisn?'NISN '+student.nisn+' · ':'')+student.class_label));results.append(item)})};
    $('#attendanceStudentSearch').on('input',function(){const keyword=$(this).val().trim(),normalizedKeyword=keyword.toLowerCase();$('.attendance-student-row').each(function(){$(this).toggle($(this).data('search').includes(normalizedKeyword))});clearTimeout(studentSearchTimer);if(!crossClassSearchEnabled||keyword.length<2){hideStudentSearchResults();return}const requestId=++studentSearchRequest;$('#attendanceStudentSearchResults').removeClass('d-none').html('<div class="attendance-student-search__empty"><i class="fas fa-spinner fa-spin mr-1"></i>Mencari di semua kelas...</div>');studentSearchTimer=setTimeout(function(){$.getJSON(studentSearchUrl,{q:keyword,tanggal:studentSearchDate}).done(function(response){if(requestId===studentSearchRequest)renderStudentSearchResults(response.students||[])}).fail(function(){if(requestId===studentSearchRequest)hideStudentSearchResults()})},220)}).on('keydown',function(event){if(event.key==='Escape')hideStudentSearchResults()});
    $('#bulkDraftScope').on('change',function(){$('.bulk-draft-classes').toggleClass('d-none',this.value!=='selected').find('select').prop('required',this.value==='selected')});
    $('#bulkDraftForm').on('submit',function(event){event.preventDefault();const form=this;Swal.fire({icon:'question',title:'Buat draft massal?',text:'Sesi yang belum ada dibuat sebagai draft dengan status awal Hadir. Sesi yang sudah ada tidak akan diubah.',showCancelButton:true,confirmButtonText:'Ya, buat draft',cancelButtonText:'Batal'}).then(result=>{if(result.isConfirmed)HTMLFormElement.prototype.submit.call(form)})});
    const refreshBulkFinalizeCount=function(){const count=$('.bulk-finalize-session:checked').length;$('#bulkFinalizeSelectedCount').text(count);$('#toggleAllBulkDrafts').prop('checked',count===$('.bulk-finalize-session').length)};
    $('#toggleAllBulkDrafts').on('change',function(){$('.bulk-finalize-session').prop('checked',this.checked);refreshBulkFinalizeCount()});
    $('.bulk-finalize-session').on('change',refreshBulkFinalizeCount);
    $('#bulkFinalizeForm').on('submit',function(event){event.preventDefault();const form=this,count=$('.bulk-finalize-session:checked').length;if(!count){notify.error('Pilih minimal satu kelas draft untuk difinalkan.','Belum ada kelas dipilih');return}Swal.fire({icon:'warning',title:'Finalkan '+count+' kelas?',html:'Draft terpilih akan menjadi <b>Final</b> dan masuk ke analitik. Kelas tanpa sesi atau yang sudah final tidak diubah.',showCancelButton:true,confirmButtonText:'Ya, finalkan',cancelButtonText:'Batal',confirmButtonColor:'#16a34a'}).then(result=>{if(result.isConfirmed)HTMLFormElement.prototype.submit.call(form)})});
    $('.student-note-trigger').on('click',function(){activeNoteButton=$(this);activeNoteInput=document.getElementById($(this).data('note-target'));$('#studentNoteName').text($(this).data('student-name'));$('#studentNoteEditor').val(activeNoteInput.value);refreshNoteCount();$('#btnClearStudentNote').toggleClass('d-none',!activeNoteInput.value);$('#studentNoteModal').modal('show')});
    $('#studentNoteEditor').on('input',refreshNoteCount);
    $('#btnApplyStudentNote').on('click',function(){if(!activeNoteInput||!activeNoteButton)return;const note=$('#studentNoteEditor').val().trim();activeNoteInput.value=note;activeNoteButton.toggleClass('has-note',!!note).find('.student-note-trigger__icon i').attr('class','fas '+(note?'fa-comment-dots':'fa-plus'));activeNoteButton.find('strong').text(note?'Ada catatan':'Tambah catatan');activeNoteButton.find('.student-note-summary').text(note?(note.length>34?note.slice(0,34)+'…':note):'Keterangan opsional');$('#studentNoteModal').modal('hide')});
    $('#btnClearStudentNote').on('click',function(){$('#studentNoteEditor').val('');refreshNoteCount();$(this).addClass('d-none')});
    $('#attendanceFilterForm select[name="mode"],#attendanceFilterForm select[name="kelas_id"],#attendanceFilterForm input[name="tanggal"]').on('change',function(){$('#attendanceFilterForm select[name="jadwal_pelajaran_id"]').val('');this.form.submit()});
    $('#attendanceFilterForm select[name="jadwal_pelajaran_id"]').on('change',function(){this.form.submit()});
    $('.btn-finalize').on('click',function(event){event.preventDefault();const button=this;Swal.fire({icon:'question',title:'Finalkan absensi?',text:'Data final masuk ke analitik. Perubahan berikutnya akan tercatat sebagai revisi.',showCancelButton:true,confirmButtonText:'Ya, finalkan',cancelButtonText:'Batal',confirmButtonColor:'#16a34a'}).then(result=>{if(result.isConfirmed){const hidden=$('<input>',{type:'hidden',name:'submit_action',value:'final'});$('#attendanceForm').append(hidden).trigger('submit')}})});
});
</script>
@stop

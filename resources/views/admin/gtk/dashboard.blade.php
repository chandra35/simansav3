@extends('adminlte::page')

@section('title', 'Dashboard GTK')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-home text-primary"></i> Dashboard GTK</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item active">Dashboard Saya</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="gtk-account-dashboard">
    <div class="card bg-gradient-primary text-white mb-4 gtk-account-dashboard__hero">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h3 class="mb-1"><i class="fas fa-user-tie mr-1"></i> Selamat Datang, {{ $gtk->nama_lengkap }}</h3>
                    <p class="mb-2 text-white-50">Ruang kerja pribadi untuk mengelola identitas dan keamanan akun GTK.</p>
                    <p class="mb-0">Pantau informasi perwalian dan akses layanan utama dari satu halaman.</p>
                </div>
                <div class="col-lg-4 mt-3 mt-lg-0 text-center">
                    <div class="text-white-50 small text-uppercase font-weight-bold">Status Profil</div>
                    <h3 class="mb-0 text-white">{{ $needsCompletion ? 'Perlu Dilengkapi' : 'Lengkap' }}</h3>
                </div>
            </div>
        </div>
    </div>

    @if($needsCompletion)
        <div class="alert alert-warning alert-dismissible fade show gtk-account-dashboard__alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
            <h5><i class="icon fas fa-exclamation-triangle"></i> Profil belum lengkap</h5>
            <p class="mb-2">Lengkapi data berikut agar seluruh fitur akun dapat digunakan:</p>
            <ul class="mb-3 pl-4">
                @if(!$stats['data_diri_completed'])
                    <li>Data diri, tempat/tanggal lahir, dan alamat lengkap</li>
                @endif
                @if(!$stats['data_kepeg_completed'])
                    <li>Status kepegawaian dan jabatan</li>
                @endif
            </ul>
            <a href="{{ route('admin.gtk.profile') }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit mr-1"></i> Lengkapi Profil
            </a>
        </div>
    @endif

    @if($teacherNotices->isNotEmpty())
        <div class="card card-outline card-warning gtk-account-dashboard__notices">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-bell text-warning mr-1"></i> Notice Pendampingan Siswa</h3><div class="card-tools"><span class="badge badge-warning">{{ $teacherNotices->count() }} notice</span></div></div>
            <div class="card-body"><div class="alert alert-light border py-2"><small><i class="fas fa-shield-alt text-primary mr-1"></i>Informasi internal untuk mendukung pembelajaran. Isi asesmen dan catatan rahasia BK tidak ditampilkan.</small></div><div class="row">@foreach($teacherNotices as $notice)<div class="col-lg-6 mb-3"><div class="gtk-account-dashboard__notice"><div class="d-flex"><img src="{{ $notice->siswa?->foto_profile_url }}" alt="Foto {{ $notice->siswa?->nama_lengkap }}"><div class="ml-2"><strong>{{ $notice->siswa?->nama_lengkap ?? '-' }}</strong><small>{{ $notice->siswa?->kelasTahunAktif->first()?->nama_kelas ?? '-' }} · {{ $notice->tanggal_konseling?->format('d/m/Y') }}</small></div></div><p>{{ $notice->teacher_notice }}</p></div></div>@endforeach</div></div>
        </div>
    @endif

    <div class="card card-outline card-primary gtk-account-dashboard__profile">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-id-card-alt mr-1"></i> Ringkasan Akun</h3>
        </div>
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-12 col-lg-auto text-center mb-3 mb-lg-0">
                    <img class="gtk-account-dashboard__avatar" src="{{ $gtk->foto_profile_url }}" alt="Foto profil {{ $gtk->nama_lengkap }}">
                </div>
                <div class="col-12 col-lg">
                    <h2 class="gtk-account-dashboard__name">{{ $gtk->nama_lengkap }}</h2>
                    <p class="text-muted mb-3">{{ $gtk->jabatan ?: 'Jabatan belum diisi' }}{{ $gtk->status_kepegawaian ? ' · '.$gtk->status_kepegawaian : '' }}</p>
                    <div class="gtk-account-dashboard__details">
                        <div class="gtk-account-dashboard__detail"><span>NIK</span><strong>{{ $gtk->nik ?: '-' }}</strong></div>
                        <div class="gtk-account-dashboard__detail"><span>NUPTK</span><strong>{{ $gtk->nuptk ?: '-' }}</strong></div>
                        <div class="gtk-account-dashboard__detail"><span>NIP</span><strong>{{ $gtk->nip ?: '-' }}</strong></div>
                        <div class="gtk-account-dashboard__detail"><span>Status</span><strong>{{ $gtk->status_kepegawaian ?: '-' }}</strong></div>
                        <div class="gtk-account-dashboard__detail"><span>Jabatan</span><strong>{{ $gtk->jabatan ?: '-' }}</strong></div>
                        <div class="gtk-account-dashboard__detail"><span>Jenis PTK</span><strong>{{ $gtk->jenis_ptk ?: ($gtk->kategori_ptk ?: '-') }}</strong></div>
                        @foreach($gtk->asramaAssignments as $assignment)
                            <div class="gtk-account-dashboard__detail gtk-account-dashboard__detail--asrama">
                                <span><i class="fas fa-bed mr-1"></i>Penugasan Asrama</span>
                                <strong>{{ $assignment->asrama?->nama ?? 'Asrama' }}</strong>
                                @if($assignment->jabatan)<small>{{ $assignment->jabatan }}</small>@endif
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                    <div class="gtk-account-dashboard__actions">
                        <a href="{{ route('admin.gtk.profile') }}" class="btn btn-primary">
                            <i class="fas fa-user-edit mr-1"></i> Edit Profil
                        </a>
                        <a href="{{ route('admin.gtk.profile.password') }}" class="btn btn-secondary">
                            <i class="fas fa-key mr-1"></i> Ganti Password
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($isWaliKelas)
        <div class="card card-outline card-primary">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                <h3 class="card-title"><i class="fas fa-chalkboard-teacher mr-1"></i> Rombel Perwalian Saya</h3>
                <span class="badge badge-light mt-2 mt-sm-0">{{ $tahunAktif?->nama ?? 'Tahun aktif belum tersedia' }}</span>
            </div>
            <div class="card-body">
                @forelse($waliKelasRombels as $rombel)
                    @php
                        $waliNama = $rombel->waliKelas?->gtk?->nama_lengkap ?? $rombel->waliKelas?->name;
                        $ketua = $rombel->ketuaKelasRecord?->siswa;
                    @endphp
                    <div class="gtk-account-dashboard__rombel {{ !$loop->last ? 'mb-3' : '' }}">
                        <div class="d-flex flex-wrap justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="font-weight-bold mb-1"><i class="fas fa-school text-primary mr-1"></i>{{ $rombel->nama_lengkap }}</h5>
                                <span class="text-muted small">{{ $rombel->siswa_aktif_count }} siswa aktif</span>
                            </div>
                            <span class="badge badge-success px-3 py-2 mt-2 mt-sm-0">Rombel Aktif</span>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-6 mb-2 mb-md-0">
                                <div class="gtk-account-dashboard__rombel-meta">
                                    <span>Wali Kelas</span>
                                    <strong><i class="fas fa-user-tie text-primary mr-1"></i>{{ $waliNama ?? 'Belum ditugaskan' }}</strong>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="gtk-account-dashboard__rombel-meta">
                                    <span>Ketua Kelas</span>
                                    <strong class="{{ $ketua ? '' : 'text-muted' }}"><i class="fas fa-crown text-warning mr-1"></i>{{ $ketua?->nama_lengkap ?? 'Belum ditetapkan' }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="alert alert-warning mb-0"><i class="fas fa-info-circle mr-1"></i> Akun Anda memiliki peran Wali Kelas, tetapi belum ditugaskan ke rombel aktif.</div>
                @endforelse
            </div>
        </div>
    @endif

    <div class="card card-outline card-primary mb-3 gtk-account-dashboard__schedule">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
            <h3 class="card-title"><i class="fas fa-calendar-day mr-1"></i> Jadwal Mengajar Hari Ini</h3>
            <div class="card-tools mt-2 mt-sm-0"><span class="badge badge-light mr-1">{{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM YYYY') }}</span><a href="{{ route('admin.gtk.my-schedule') }}" class="btn btn-primary btn-sm"><i class="fas fa-calendar-alt mr-1"></i> Jadwal Saya</a></div>
        </div>
        <div class="card-body">
            <div id="gtkScheduleLiveReminder" class="alert alert-warning gtk-account-dashboard__schedule-reminder d-none" role="status"></div>
            @if($scheduleReminder)
                <div class="alert alert-warning gtk-account-dashboard__schedule-reminder"><i class="fas fa-bell mr-1"></i>{{ $scheduleReminder['message'] }}</div>
            @endif
            @forelse($todaySchedules as $schedule)
                <div class="gtk-account-dashboard__schedule-item is-{{ $schedule->dashboard_status }}" data-schedule-start="{{ $schedule->jam_mulai ? substr($schedule->jam_mulai, 0, 5) : '' }}" data-schedule-end="{{ $schedule->jam_selesai ? substr($schedule->jam_selesai, 0, 5) : '' }}" data-schedule-subject="{{ $schedule->mataPelajaran?->nama_mapel ?? 'jadwal mengajar' }}" data-schedule-class="{{ $schedule->kelas?->nama_kelas ?? 'kelas Anda' }}">
                    <div class="gtk-account-dashboard__schedule-time"><strong>{{ $schedule->jam_mulai ? substr($schedule->jam_mulai, 0, 5) : '-' }}</strong><span>{{ $schedule->jam_selesai ? 's.d. '.substr($schedule->jam_selesai, 0, 5) : 'Waktu belum diisi' }}</span></div>
                    <div class="gtk-account-dashboard__schedule-main"><strong>{{ $schedule->mataPelajaran?->nama_mapel ?? 'Mata pelajaran' }}</strong><span><i class="fas fa-school mr-1"></i>{{ $schedule->location_label }}</span></div>
                    <div class="gtk-account-dashboard__schedule-meta"><span class="badge badge-light">Jam {{ $schedule->jam_ke ?: '-' }}</span><span class="gtk-account-dashboard__schedule-status">{{ ['ongoing' => 'Sedang berlangsung', 'completed' => 'Selesai', 'next' => 'Berikutnya', 'upcoming' => 'Terjadwal'][$schedule->dashboard_status] ?? 'Terjadwal' }}</span></div>
                </div>
            @empty
                <div class="text-center text-muted py-3"><i class="far fa-calendar-check fa-2x mb-2 d-block"></i>Tidak ada jadwal mengajar hari ini.</div>
            @endforelse
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .gtk-account-dashboard { color:#0f172a; }
    .gtk-account-dashboard__hero { overflow:hidden; border:0; border-radius:16px; box-shadow:0 12px 28px rgba(15,23,42,.1); }
    .gtk-account-dashboard__hero > .card-body { padding:1.2rem 1.25rem; }
    .gtk-account-dashboard__hero h3 { font-size:1.35rem; font-weight:700; }
    .gtk-account-dashboard .card-outline { border-radius:12px; box-shadow:0 8px 20px rgba(15,23,42,.06); }
    .gtk-account-dashboard__avatar { width:72px; height:72px; object-fit:cover; border-radius:50%; border:3px solid #e2e8f0; box-shadow:0 6px 16px rgba(15,23,42,.12); }
    .gtk-account-dashboard__name { margin:0 0 .2rem; color:#0f172a; font-size:1.25rem; font-weight:800; }
    .gtk-account-dashboard__details { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:.65rem; }
    .gtk-account-dashboard__detail, .gtk-account-dashboard__rombel-meta { min-width:0; padding:.7rem .8rem; border:1px solid #e2e8f0; border-radius:10px; background:#f8fafc; }
    .gtk-account-dashboard__detail span, .gtk-account-dashboard__rombel-meta span { display:block; margin-bottom:.18rem; color:#64748b; font-size:.7rem; font-weight:700; letter-spacing:.05em; text-transform:uppercase; }
    .gtk-account-dashboard__detail strong, .gtk-account-dashboard__rombel-meta strong { display:block; overflow-wrap:anywhere; color:#0f172a; font-size:.88rem; }
    .gtk-account-dashboard__detail small { display:block; margin-top:.16rem; color:#64748b; font-size:.75rem; }
    .gtk-account-dashboard__detail--asrama { border-color:#c7d2fe; background:#f5f7ff; }
    .gtk-account-dashboard__actions { display:flex; flex-direction:column; gap:.6rem; min-width:172px; }
    .gtk-account-dashboard__rombel { padding:1rem; border:1px solid #e2e8f0; border-radius:12px; }
    .gtk-account-dashboard__notice { height:100%; padding:.75rem; border:1px solid #fde68a; border-radius:10px; background:#fffbeb; }
    .gtk-account-dashboard__notice img { width:42px; height:52px; object-fit:cover; border-radius:6px; }
    .gtk-account-dashboard__notice strong, .gtk-account-dashboard__notice small { display:block; }
    .gtk-account-dashboard__notice small { color:#64748b; font-size:.72rem; }
    .gtk-account-dashboard__notice p { margin:.6rem 0 0; color:#334155; font-size:.82rem; white-space:pre-line; }
    .gtk-account-dashboard__alert { border-radius:12px; }
    .gtk-account-dashboard__schedule-reminder { margin-bottom:.8rem; border-radius:10px; font-size:.88rem; }
    .gtk-account-dashboard__schedule-item { position:relative; display:grid; grid-template-columns:96px minmax(0,1fr) auto; gap:.75rem; align-items:center; margin:0 -.15rem; padding:.78rem .65rem .78rem .85rem; overflow:hidden; border-bottom:1px solid #eef2f7; border-radius:10px; transition:background .25s ease,transform .25s ease,opacity .25s ease; }
    .gtk-account-dashboard__schedule-item:last-child { border-bottom:0; }
    .gtk-account-dashboard__schedule-item::before { position:absolute; top:.55rem; bottom:.55rem; left:0; width:3px; border-radius:3px; background:#cbd5e1; content:''; }
    .gtk-account-dashboard__schedule-time strong,.gtk-account-dashboard__schedule-time span,.gtk-account-dashboard__schedule-main strong,.gtk-account-dashboard__schedule-main span { display:block; }
    .gtk-account-dashboard__schedule-time strong { color:#2563eb; font-size:.93rem; }.gtk-account-dashboard__schedule-time span,.gtk-account-dashboard__schedule-main span { color:#64748b; font-size:.75rem; }.gtk-account-dashboard__schedule-main strong { color:#0f172a; font-size:.9rem; }
    .gtk-account-dashboard__schedule-meta { display:flex; flex-direction:column; align-items:flex-end; gap:.34rem; white-space:nowrap; }
    .gtk-account-dashboard__schedule-status { color:#64748b; font-size:.7rem; font-weight:700; }
    .gtk-account-dashboard__schedule-item.is-ongoing { background:linear-gradient(90deg,#ecfdf5 0%,#f8fffc 78%); box-shadow:0 4px 16px rgba(16,185,129,.12); transform:translateX(2px); }
    .gtk-account-dashboard__schedule-item.is-ongoing::before { background:#10b981; animation:gtkSchedulePulse 1.8s ease-in-out infinite; }.gtk-account-dashboard__schedule-item.is-ongoing .gtk-account-dashboard__schedule-status { color:#047857; }.gtk-account-dashboard__schedule-item.is-ongoing .gtk-account-dashboard__schedule-status::before { content:'● '; animation:gtkScheduleBlink 1.2s ease-in-out infinite; }
    .gtk-account-dashboard__schedule-item.is-next { background:#f5f7ff; }.gtk-account-dashboard__schedule-item.is-next::before { background:#6366f1; }.gtk-account-dashboard__schedule-item.is-next .gtk-account-dashboard__schedule-status { color:#4f46e5; }
    .gtk-account-dashboard__schedule-item.is-completed { opacity:.58; }.gtk-account-dashboard__schedule-item.is-completed::before { background:#94a3b8; }.gtk-account-dashboard__schedule-item.is-completed .gtk-account-dashboard__schedule-time strong { color:#64748b; text-decoration:line-through; }
    @keyframes gtkSchedulePulse { 50% { box-shadow:0 0 0 5px rgba(16,185,129,.12); } } @keyframes gtkScheduleBlink { 50% { opacity:.35; } }
    @media (max-width:991.98px) {
        .gtk-account-dashboard__details { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .gtk-account-dashboard__actions { flex-direction:row; min-width:0; }
    }
    @media (max-width:575.98px) {
        .gtk-account-dashboard__details { grid-template-columns:1fr; }
        .gtk-account-dashboard__actions { flex-direction:column; }
        .gtk-account-dashboard__actions .btn { width:100%; }
        .gtk-account-dashboard .card-outline > .card-header { align-items:flex-start !important; }
        .gtk-account-dashboard__hero > .card-body { padding:1rem; }
        .gtk-account-dashboard__hero h3 { font-size:1.15rem; }
        .gtk-account-dashboard__avatar { width:62px; height:62px; }
        .gtk-account-dashboard__schedule-item { grid-template-columns:76px minmax(0,1fr); gap:.55rem; padding:.75rem .45rem .75rem .7rem; }.gtk-account-dashboard__schedule-meta { grid-column:2; flex-direction:row; justify-content:flex-start; align-items:center; }.gtk-account-dashboard__schedule-status { font-size:.68rem; }
    }
    @media (prefers-reduced-motion:reduce) { .gtk-account-dashboard__schedule-item,.gtk-account-dashboard__schedule-item.is-ongoing::before,.gtk-account-dashboard__schedule-item.is-ongoing .gtk-account-dashboard__schedule-status::before { animation:none; transition:none; } }
</style>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const config = @json($scheduleReminderConfig);
    const serverStartedAt = Date.parse(config.server_now);
    const browserStartedAt = Date.now();
    const shown = new Set();
    const reminder = document.getElementById('gtkScheduleLiveReminder');

    function checkScheduleReminder() {
        const now = new Date(serverStartedAt + (Date.now() - browserStartedAt));
        let nextItem = null;
        document.querySelectorAll('[data-schedule-start]').forEach(function (item) {
            const start = item.dataset.scheduleStart;
            if (!start) return;

            const [hour, minute] = start.split(':').map(Number);
            const startsAt = new Date(now);
            startsAt.setHours(hour, minute, 0, 0);
            const [endHour, endMinute] = (item.dataset.scheduleEnd || '').split(':').map(Number);
            const endsAt = new Date(now);
            if (Number.isFinite(endHour) && Number.isFinite(endMinute)) endsAt.setHours(endHour, endMinute, 0, 0);
            const remaining = Math.ceil((startsAt - now) / 60000);
            const key = start + item.dataset.scheduleSubject + item.dataset.scheduleClass;

            item.classList.remove('is-ongoing', 'is-completed', 'is-next', 'is-upcoming');
            let state = 'upcoming';
            if (Number.isFinite(endHour) && now >= endsAt) state = 'completed';
            else if (now >= startsAt) state = 'ongoing';
            else if (!nextItem) { state = 'next'; nextItem = item; }
            item.classList.add('is-' + state);
            const stateLabel = item.querySelector('.gtk-account-dashboard__schedule-status');
            if (stateLabel) stateLabel.textContent = { ongoing: 'Sedang berlangsung', completed: 'Selesai', next: 'Berikutnya', upcoming: 'Terjadwal' }[state];

            if (!config.enabled || shown.has(key) || remaining < 0 || remaining > config.minutes) return;

            shown.add(key);
            const lead = remaining === 0
                ? ['Jadwal dimulai sekarang', 'Saatnya menuju kelas', 'Jangan lupa, jadwal sudah dimulai']
                : [`${remaining} menit lagi`, `Segera dimulai dalam ${remaining} menit`, `Jangan lupa, ${remaining} menit lagi`];
            const prefix = config.greeting ? config.greeting + ', ' : '';
            reminder.textContent = '🔔 ' + prefix + lead[Math.floor(Math.random() * lead.length)] + ': ' + item.dataset.scheduleSubject + ' di ' + item.dataset.scheduleClass + '.';
            reminder.classList.remove('d-none');
        });
    }

    checkScheduleReminder();
    window.setInterval(checkScheduleReminder, 30000);
});
</script>
@stop

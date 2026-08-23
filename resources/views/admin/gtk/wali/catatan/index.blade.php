@extends('adminlte::page')

@section('title', 'Catatan Siswa — Kelas Saya')
@section('plugins.Sweetalert2', true)

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6"><h1><i class="fas fa-sticky-note text-primary"></i> Catatan Siswa</h1></div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.gtk.dashboard') }}">Dashboard Saya</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.gtk.wali.siswa.index', ['kelas_id' => $kelas->id]) }}">Data Siswa</a></li>
                <li class="breadcrumb-item active">Catatan Siswa</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="gtk-wali-catatan-page">
    <div class="card bg-gradient-primary text-white mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h3 class="mb-1"><i class="fas fa-book-reader mr-1"></i> Pendampingan Siswa</h3>
                    <p class="mb-2 text-white-50">Catat perkembangan, prestasi, kehadiran, dan tindak lanjut siswa secara terarah.</p>
                    <p class="mb-0">Pilih foto siswa, tulis catatan, lalu pantau riwayat pendampingannya dalam satu halaman.</p>
                </div>
                <div class="col-lg-4 mt-3 mt-lg-0 text-lg-right">
                    <div class="text-white-50 small text-uppercase font-weight-bold">Rombel Aktif</div>
                    <h3 class="mb-0 text-white">{{ $kelas->nama_lengkap ?? $kelas->nama_kelas }}</h3>
                </div>
            </div>
        </div>
    </div>

    @includeWhen($kelasList->count() > 1, 'admin.gtk.wali.partials.kelas-switcher', ['route' => 'admin.gtk.wali.catatan.index'])

    <div class="row mb-4">
        @foreach([
            ['label' => 'Total Siswa', 'value' => $stats['total_siswa'], 'icon' => 'users', 'color' => 'primary', 'description' => 'Siswa aktif di rombel.'],
            ['label' => 'Sudah Dicatat', 'value' => $stats['sudah_dicatat'], 'icon' => 'user-check', 'color' => 'success', 'description' => 'Siswa memiliki catatan.'],
            ['label' => 'Total Catatan', 'value' => $stats['total_catatan'], 'icon' => 'clipboard-list', 'color' => 'info', 'description' => 'Riwayat yang tersimpan.'],
            ['label' => 'Catatan Penting', 'value' => $stats['penting'], 'icon' => 'star', 'color' => 'warning', 'description' => 'Perlu perhatian khusus.'],
        ] as $stat)
            <div class="col-6 col-xl-3 mb-3 mb-xl-0">
                <div class="card card-outline card-{{ $stat['color'] }} h-100 catatan-stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="catatan-stat-icon bg-{{ $stat['color'] }}"><i class="fas fa-{{ $stat['icon'] }}"></i></div>
                        <div class="min-w-0">
                            <div class="text-muted small text-uppercase font-weight-bold">{{ $stat['label'] }}</div>
                            <h3 class="text-{{ $stat['color'] }} mb-0">{{ number_format($stat['value']) }}</h3>
                            <div class="text-muted small stat-description">{{ $stat['description'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card card-outline card-primary student-picker-panel">
        <div class="card-header border-0 pb-0">
            <div class="d-flex flex-wrap align-items-center justify-content-between">
                <div>
                    <h3 class="card-title font-weight-bold"><i class="fas fa-user-graduate mr-1 text-primary"></i> Pilih Siswa</h3>
                    <div class="text-muted small mt-1 clear-both">Klik siswa untuk membuka ruang catatan dan riwayatnya.</div>
                </div>
                <div class="input-group input-group-sm student-search mt-2 mt-md-0">
                    <div class="input-group-prepend"><span class="input-group-text bg-white"><i class="fas fa-search"></i></span></div>
                    <input type="search" id="studentSearch" class="form-control" placeholder="Cari nama atau NISN..." aria-label="Cari siswa">
                </div>
            </div>
        </div>
        <div class="card-body pt-3">
            <div class="student-grid" id="studentGrid">
                @foreach($students as $index => $student)
                    @php
                        $studentUrl = route('admin.gtk.wali.catatan.index', array_filter([
                            'kelas_id' => $kelas->id,
                            'siswa_id' => $student->id,
                            'kategori' => $filterKategori,
                            'compose' => 1,
                        ]));
                        $absen = $student->pivot->nomor_urut_absen ?? ($index + 1);
                    @endphp
                    <a href="{{ $studentUrl }}" class="student-choice {{ $selectedStudent?->id === $student->id ? 'is-active' : '' }}"
                       data-search="{{ Illuminate\Support\Str::lower($student->nama_lengkap.' '.$student->nisn) }}">
                        <span class="student-avatar-wrap">
                            <img src="{{ $student->foto_profile_url }}" alt="Foto {{ $student->nama_lengkap }}" class="student-avatar">
                            <span class="student-absen">{{ $absen }}</span>
                        </span>
                        <span class="student-choice-text">
                            <strong>{{ $student->nama_lengkap }}</strong>
                            <small>NISN {{ $student->nisn ?: '—' }}</small>
                        </span>
                        <i class="fas fa-chevron-right student-choice-arrow"></i>
                    </a>
                @endforeach
            </div>
            <div id="studentEmptySearch" class="text-center text-muted py-4 d-none">
                <i class="fas fa-search fa-2x mb-2"></i><div>Siswa tidak ditemukan.</div>
            </div>
        </div>
    </div>

    @if($selectedStudent)
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card card-outline card-info h-100 history-card">
                    <div class="card-header">
                        <div class="d-flex flex-wrap align-items-center">
                            <img src="{{ $selectedStudent->foto_profile_url }}" alt="Foto {{ $selectedStudent->nama_lengkap }}" class="history-student-avatar mr-2">
                            <div class="min-w-0">
                                <h3 class="card-title float-none font-weight-bold"><i class="fas fa-history mr-1"></i> Riwayat {{ $selectedStudent->nama_lengkap }}</h3>
                                <small class="text-muted">NISN {{ $selectedStudent->nisn ?: '—' }} · {{ $kelas->nama_kelas }}</small>
                            </div>
                        </div>
                        <div class="card-tools">
                            <button type="button" class="btn btn-sm btn-primary mr-2" data-toggle="modal" data-target="#modalTambahCatatan"><i class="fas fa-pen-fancy mr-1"></i> Tulis Catatan</button>
                            <form method="GET" action="{{ route('admin.gtk.wali.catatan.index') }}">
                                <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
                                <input type="hidden" name="siswa_id" value="{{ $selectedStudent->id }}">
                                <select name="kategori" class="form-control form-control-sm" onchange="this.form.submit()" aria-label="Filter kategori">
                                    <option value="">Semua kategori</option>
                                    @foreach($kategoriList as $key => $label)
                                        <option value="{{ $key }}" {{ $filterKategori === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        @forelse($catatan as $item)
                            <article class="note-item {{ $item->is_penting ? 'is-important' : '' }}">
                                <div class="d-flex align-items-start">
                                    <img src="{{ optional($item->siswa)->foto_profile_url }}" alt="Foto {{ optional($item->siswa)->nama_lengkap }}" class="note-avatar">
                                    <div class="min-w-0 flex-grow-1">
                                        <div class="d-flex flex-wrap justify-content-between align-items-start">
                                            <div>
                                                <strong class="text-dark">{{ optional($item->siswa)->nama_lengkap }}</strong>
                                                <div class="text-muted small"><i class="far fa-calendar-alt mr-1"></i>{{ $item->tanggal->translatedFormat('d F Y') }}</div>
                                            </div>
                                            <div class="note-badges text-right">
                                                <span class="badge badge-info">{{ $item->kategori_label }}</span>
                                                @if($item->is_penting)<span class="badge badge-warning"><i class="fas fa-star"></i> Penting</span>@endif
                                                @if($item->dibaca_bk_at)<span class="badge badge-success"><i class="fas fa-check-double"></i> Dibaca BK</span>@endif
                                            </div>
                                        </div>
                                        <div class="note-content mt-3">{!! $item->catatan_html !!}</div>
                                        <div class="note-actions mt-3">
                                            <button type="button" class="btn btn-sm btn-outline-secondary btn-edit-catatan"
                                                data-id="{{ $item->id }}" data-tanggal="{{ $item->tanggal->format('Y-m-d') }}"
                                                data-kategori="{{ $item->kategori }}" data-penting="{{ $item->is_penting ? 1 : 0 }}"
                                                data-catatan="{{ $item->catatan }}"><i class="fas fa-edit mr-1"></i> Edit</button>
                                            <form method="POST" action="{{ route('admin.gtk.wali.catatan.destroy', $item->id) }}" class="d-inline form-delete-catatan">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash mr-1"></i> Hapus</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="empty-notes">
                                <div class="empty-notes-icon"><i class="fas fa-clipboard"></i></div>
                                <h5>Belum ada catatan {{ $filterKategori ? 'pada kategori ini' : '' }}</h5>
                                <p class="text-muted mb-0">Mulai dari perkembangan positif, kebutuhan pendampingan, atau tindak lanjut siswa.</p>
                            </div>
                        @endforelse
                        <div class="mt-3">{{ $catatan->links() }}</div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="card card-outline card-info mb-4">
            <div class="card-body select-student-empty">
                <div class="select-student-icon"><i class="fas fa-hand-pointer"></i></div>
                <h4>Pilih siswa untuk mulai menulis</h4>
                <p class="text-muted mb-0">Foto dan identitas siswa membantu memastikan catatan ditujukan kepada siswa yang tepat.</p>
            </div>
        </div>
    @endif

    @if($selectedStudent)
    <div class="modal fade" id="modalTambahCatatan" tabindex="-1" role="dialog" aria-labelledby="modalTambahCatatanLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <form method="POST" action="{{ route('admin.gtk.wali.catatan.store', ['siswa_id' => $selectedStudent->id, 'kelas_id' => $kelas->id]) }}" id="formTambahCatatan" class="modal-content">
                @csrf
                <input type="hidden" name="form_context" value="create">
                <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
                <input type="hidden" name="siswa_id" value="{{ $selectedStudent->id }}">
                <div class="modal-header bg-light">
                    <h5 class="modal-title text-dark" id="modalTambahCatatanLabel"><i class="fas fa-pen-fancy mr-1"></i> Tulis Catatan Siswa</h5>
                    <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors focus:outline-none" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="selected-student mb-3">
                        <img src="{{ $selectedStudent->foto_profile_url }}" alt="Foto {{ $selectedStudent->nama_lengkap }}">
                        <div class="min-w-0">
                            <div class="text-muted small text-uppercase font-weight-bold">Catatan untuk</div>
                            <div class="font-weight-bold text-dark selected-student-name">{{ $selectedStudent->nama_lengkap }}</div>
                            <small class="text-muted">NISN {{ $selectedStudent->nisn ?: '—' }} · {{ $kelas->nama_kelas }}</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="tanggal">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" class="form-control @error('tanggal') is-invalid @enderror" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="kategori">Kategori</label>
                            <select name="kategori" id="kategori" class="form-control">
                                <option value="">Umum</option>
                                @foreach($kategoriList as $key => $label)<option value="{{ $key }}" {{ old('kategori') === $key ? 'selected' : '' }}>{{ $label }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group mb-2">
                        <div class="d-flex justify-content-between align-items-center"><label for="catatan" class="mb-1">Isi Catatan <span class="text-danger">*</span></label><small class="text-muted"><span id="noteCounter">0</span>/5000</small></div>
                        <textarea name="catatan" id="catatan" class="form-control @error('catatan') is-invalid @enderror" required>{{ old('catatan') }}</textarea>
                    </div>
                    <div class="visual-tools mb-3" aria-label="Emoji dan simbol cepat">
                        <div class="small font-weight-bold text-muted mb-2"><i class="far fa-smile mr-1"></i> Emoji & simbol cepat</div>
                        <div class="symbol-list">@foreach(['🙂','😊','👏','⭐','✅','⚠️','📌','📚','🏆','💡','❤️','→','•','✓','★'] as $symbol)<button type="button" class="btn btn-light btn-sm btn-insert-symbol" data-target="#catatan" data-symbol="{{ $symbol }}" title="Sisipkan {{ $symbol }}">{{ $symbol }}</button>@endforeach</div>
                    </div>
                    <div class="quick-prompts mb-3">
                        <div class="small font-weight-bold text-muted mb-2"><i class="fas fa-magic mr-1"></i> Awalan cepat</div>
                        @foreach(['Menunjukkan perkembangan baik dalam ', 'Perlu pendampingan pada ', 'Tindak lanjut yang disepakati: '] as $prompt)<button type="button" class="btn btn-sm btn-outline-secondary btn-insert-prompt mb-1" data-target="#catatan" data-prompt="{{ $prompt }}">{{ $prompt }}</button>@endforeach
                    </div>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="is_penting" name="is_penting" value="1" {{ old('is_penting') ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_penting"><i class="fas fa-star text-warning mr-1"></i> Tandai penting untuk perhatian khusus</label>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan Catatan</button></div>
            </form>
        </div>
    </div>
    @endif

    <div class="modal fade" id="modalEditCatatan" tabindex="-1" role="dialog" aria-labelledby="modalEditCatatanLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <form method="POST" id="formEditCatatan" class="modal-content">
                @csrf @method('PUT')
                <input type="hidden" name="form_context" value="edit">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="modalEditCatatanLabel"><i class="fas fa-edit mr-1"></i> Edit Catatan</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 form-group"><label for="edit_tanggal">Tanggal</label><input type="date" name="tanggal" id="edit_tanggal" max="{{ date('Y-m-d') }}" class="form-control" required></div>
                        <div class="col-md-6 form-group"><label for="edit_kategori">Kategori</label><select name="kategori" id="edit_kategori" class="form-control"><option value="">Umum</option>@foreach($kategoriList as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></div>
                    </div>
                    <div class="form-group"><label for="edit_catatan">Isi Catatan</label><textarea name="catatan" id="edit_catatan" class="form-control" required></textarea></div>
                    <div class="symbol-list mb-3">
                        @foreach(['🙂','😊','👏','⭐','✅','⚠️','📌','📚','🏆','💡','❤️','→','•','✓','★'] as $symbol)
                            <button type="button" class="btn btn-light btn-sm btn-insert-symbol" data-target="#edit_catatan" data-symbol="{{ $symbol }}">{{ $symbol }}</button>
                        @endforeach
                    </div>
                    <div class="custom-control custom-switch"><input type="checkbox" class="custom-control-input" id="edit_penting" name="is_penting" value="1"><label class="custom-control-label" for="edit_penting">Tandai penting</label></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan Perubahan</button></div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
<style>
    .gtk-wali-catatan-page > .bg-gradient-primary { overflow:hidden; border:0; border-radius:16px; box-shadow:0 12px 28px rgba(15,23,42,.1); }
    .gtk-wali-catatan-page > .bg-gradient-primary .card-body { padding:1.2rem 1.25rem; }
    .gtk-wali-catatan-page > .bg-gradient-primary h3 { font-size:1.35rem; font-weight:700; overflow-wrap:anywhere; }
    .min-w-0 { min-width:0; } .clear-both { clear:both; }
    .catatan-stat-card { border-radius:12px; }
    .catatan-stat-icon { width:48px; height:48px; flex:0 0 48px; display:flex; align-items:center; justify-content:center; border-radius:14px; margin-right:.85rem; color:#fff; font-size:1.15rem; }
    .student-picker-panel, .history-card { border-radius:14px; overflow:hidden; }
    .student-search { width:280px; }
    .student-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:.75rem; max-height:360px; overflow-y:auto; padding:.1rem; scrollbar-width:thin; }
    .student-choice { position:relative; display:flex; align-items:center; min-width:0; padding:.75rem; border:1px solid #e2e8f0; border-radius:12px; background:#fff; color:#0f172a; transition:border-color .18s,box-shadow .18s,transform .18s; }
    .student-choice:hover { color:#0f172a; border-color:#60a5fa; box-shadow:0 8px 20px rgba(37,99,235,.1); transform:translateY(-1px); text-decoration:none; }
    .student-choice.is-active { border:2px solid #2563eb; background:#eff6ff; box-shadow:0 8px 20px rgba(37,99,235,.14); }
    .student-avatar-wrap { position:relative; flex:0 0 auto; }
    .student-avatar { width:52px; height:52px; border-radius:50%; object-fit:cover; border:2px solid #fff; box-shadow:0 2px 8px rgba(15,23,42,.16); }
    .student-absen { position:absolute; right:-3px; bottom:-3px; min-width:20px; height:20px; padding:0 4px; display:flex; align-items:center; justify-content:center; border-radius:10px; background:#334155; color:#fff; border:2px solid #fff; font-size:.65rem; font-weight:800; }
    .student-choice-text { display:block; min-width:0; padding:0 .65rem; }
    .student-choice-text strong, .student-choice-text small { display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .student-choice-text strong { font-size:.82rem; } .student-choice-text small { color:#64748b; font-size:.72rem; }
    .student-choice-arrow { margin-left:auto; color:#94a3b8; font-size:.7rem; }
    .selected-student { display:flex; align-items:center; gap:.75rem; padding:.85rem; border:1px solid #bfdbfe; border-radius:12px; background:#eff6ff; }
    .selected-student img { width:58px; height:58px; flex:0 0 58px; border-radius:50%; object-fit:cover; border:3px solid #fff; box-shadow:0 2px 8px rgba(15,23,42,.14); }
    .selected-student-name { overflow-wrap:anywhere; }
    .history-student-avatar { width:42px; height:42px; flex:0 0 42px; border-radius:50%; object-fit:cover; }
    .note-editor .note-editable { min-height:150px; color:#0f172a; background:#fff; }
    .note-editor.note-frame { border-color:#cbd5e1; border-radius:8px; overflow:hidden; }
    .visual-tools, .quick-prompts { padding:.75rem; border:1px solid #e2e8f0; border-radius:10px; background:#f8fafc; }
    .symbol-list { display:flex; flex-wrap:wrap; gap:.35rem; }
    .btn-insert-symbol { min-width:36px; border:1px solid #e2e8f0; font-size:1rem; }
    .quick-prompts .btn { max-width:100%; white-space:normal; text-align:left; }
    .note-item { padding:1rem; border:1px solid #e2e8f0; border-left:4px solid #3b82f6; border-radius:12px; background:#fff; }
    .note-item + .note-item { margin-top:.85rem; }
    .note-item.is-important { border-left-color:#f59e0b; background:#fffbeb; }
    .note-avatar { width:46px; height:46px; flex:0 0 46px; margin-right:.75rem; border-radius:50%; object-fit:cover; }
    .note-content { color:#334155; overflow-wrap:anywhere; } .note-content p:last-child { margin-bottom:0; }
    .note-content ul, .note-content ol { padding-left:1.25rem; }
    .note-badges .badge { margin:0 0 .2rem .2rem; }
    .empty-notes, .select-student-empty { padding:2rem 1rem; text-align:center; }
    .empty-notes-icon, .select-student-icon { width:64px; height:64px; margin:0 auto 1rem; display:flex; align-items:center; justify-content:center; border-radius:50%; background:#eff6ff; color:#2563eb; font-size:1.5rem; }
    #modalTambahCatatan .modal-content, #modalEditCatatan .modal-content { border:0; border-radius:16px; overflow:hidden; box-shadow:0 24px 64px rgba(15,23,42,.22); }
    #modalTambahCatatan .hover\\:text-gray-600:hover { color:#4b5563; }
    #modalTambahCatatan .text-gray-400 { padding:.15rem .45rem; border:0; background:transparent; color:#9ca3af; font-size:1.6rem; line-height:1; cursor:pointer; }
    #modalTambahCatatan .focus\\:outline-none:focus { outline:0; }
    @media (max-width:1199.98px) { .student-grid { grid-template-columns:repeat(3,minmax(0,1fr)); } }
    @media (max-width:767.98px) { .student-grid { grid-template-columns:repeat(2,minmax(0,1fr)); max-height:420px; } .student-search { width:100%; } .stat-description { display:none; } }
    @media (max-width:575.98px) {
        .gtk-wali-catatan-page > .bg-gradient-primary .card-body { padding:1rem; } .gtk-wali-catatan-page > .bg-gradient-primary h3 { font-size:1.1rem; }
        .catatan-stat-card .card-body { padding:.75rem; } .catatan-stat-icon { width:38px; height:38px; flex-basis:38px; margin-right:.55rem; border-radius:10px; }
        .catatan-stat-card h3 { font-size:1.25rem; } .student-grid { grid-template-columns:1fr; } .student-choice { padding:.65rem; }
        .note-item { padding:.8rem; } .note-item > .d-flex { align-items:flex-start!important; } .note-avatar { width:40px; height:40px; flex-basis:40px; }
        .history-card .card-header { padding-bottom:.85rem; }
        .history-card .card-tools { float:none; width:100%; margin:.75rem 0 0; display:flex; flex-wrap:wrap; gap:.5rem; }
        .history-card .card-tools .btn { margin-right:0!important; }
        .history-card .card-tools form { flex:1 1 160px; }
        #modalTambahCatatan .modal-dialog, #modalEditCatatan .modal-dialog { margin:.5rem; }
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
<script>
$(function () {
    var baseUrl = @json(url('admin/gtk/wali/catatan'));
    var successMessage = @json(session('success'));
    var validationErrors = @json($errors->all());
    var openComposer = @json((bool) $selectedStudent && ((!$errors->any() && !session('success') && request()->boolean('compose')) || ($errors->any() && old('form_context') === 'create')));
    var editorOptions = {
        height: 170,
        placeholder: 'Tuliskan pengamatan yang objektif, perkembangan siswa, dan tindak lanjutnya…',
        toolbar: [['style', ['bold', 'italic', 'underline', 'clear']], ['para', ['ul', 'ol', 'paragraph']], ['history', ['undo', 'redo']]],
        callbacks: { onChange: function (contents) { updateCounter(this, contents); } }
    };

    function updateCounter(editor, contents) {
        var target = editor.id === 'catatan' ? '#noteCounter' : null;
        if (target) $(target).text($('<div>').html(contents).text().length);
    }
    function insertText(target, value) {
        if ($(target).next('.note-editor').length) $(target).summernote('editor.insertText', value);
        else $(target).val($(target).val() + value).trigger('input');
    }

    if ($('#catatan').length) {
        $('#catatan').summernote(editorOptions);
        updateCounter(document.getElementById('catatan'), $('#catatan').summernote('code'));
    }
    $('#edit_catatan').summernote($.extend({}, editorOptions, { height: 190 }));

    if (openComposer) {
        $('#modalTambahCatatan').modal('show');
        var currentUrl = new URL(window.location.href);
        currentUrl.searchParams.delete('compose');
        window.history.replaceState({}, document.title, currentUrl.toString());
    }

    $('#studentSearch').on('input', function () {
        var query = $(this).val().toLocaleLowerCase('id-ID').trim();
        var visible = 0;
        $('.student-choice').each(function () {
            var show = !query || String($(this).data('search')).indexOf(query) !== -1;
            $(this).toggle(show); if (show) visible++;
        });
        $('#studentEmptySearch').toggleClass('d-none', visible > 0);
    });

    $('.btn-insert-symbol').on('click', function () { insertText($(this).data('target'), $(this).data('symbol')); });
    $('.btn-insert-prompt').on('click', function () { insertText($(this).data('target'), $(this).data('prompt')); });

    if (successMessage) Swal.fire({ icon:'success', title:'Berhasil', text:successMessage, timer:2200, showConfirmButton:false });
    if (validationErrors.length) Swal.fire({ icon:'error', title:'Data Belum Valid', html:validationErrors.map(function (error) { return $('<div>').text(error).html(); }).join('<br>'), confirmButtonText:'Periksa Kembali' });

    $('.btn-edit-catatan').on('click', function () {
        var button = $(this);
        $('#formEditCatatan').attr('action', baseUrl + '/' + button.attr('data-id'));
        $('#edit_tanggal').val(button.attr('data-tanggal'));
        $('#edit_kategori').val(button.attr('data-kategori') || '');
        $('#edit_catatan').summernote('code', button.attr('data-catatan') || '');
        $('#edit_penting').prop('checked', button.attr('data-penting') === '1');
        $('#modalEditCatatan').modal('show');
    });

    $('.form-delete-catatan').on('submit', function (event) {
        event.preventDefault(); var form = this;
        Swal.fire({ icon:'warning', title:'Hapus catatan?', text:'Catatan yang dihapus tidak dapat dipulihkan.', showCancelButton:true, confirmButtonText:'Ya, Hapus', cancelButtonText:'Batal', confirmButtonColor:'#dc2626' })
            .then(function (result) { if (result.isConfirmed) form.submit(); });
    });
});
</script>
@stop

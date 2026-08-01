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
            <p>Presensi wajah tetap khusus masuk–pulang. Guru mapel menandai kehadiran secara manual berdasarkan jadwal aktif.</p>
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
    @if(session('success'))
        <div class="alert alert-success attendance-alert"><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger attendance-alert"><strong>Data belum dapat disimpan.</strong><ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    @if(!$tahunPelajaran)
        <div class="attendance-empty"><i class="fas fa-calendar-times"></i><h3>Tahun pelajaran aktif belum ditetapkan</h3><p>Absensi baru tidak dapat dibuat sebelum admin mengaktifkan tahun pelajaran.</p></div>
    @elseif(!$mode)
        <div class="attendance-empty"><i class="fas fa-lock"></i><h3>Tidak ada jadwal absensi yang dapat dikelola</h3><p>Akun ini dapat melihat modul, tetapi tidak mempunyai kelas wali atau jadwal mengajar pada tahun aktif.</p>@can('view-attendance-analytics')<a href="{{ route('admin.absensi-siswa.analytics') }}" class="btn btn-primary">Buka Analitik</a>@endcan</div>
    @else
        <section class="attendance-filter mb-4">
            <div class="attendance-section-head"><div><h2><i class="fas fa-filter mr-2"></i>Pilih Sesi</h2><p>Kelas dan jadwal dibatasi otomatis sesuai hak akses akun.</p></div><span class="badge badge-light border">Metode: Manual</span></div>
            <form method="GET" action="{{ route('admin.absensi-siswa.index') }}" id="attendanceFilterForm">
                <div class="form-group"><label>Tanggal</label><input type="date" name="tanggal" value="{{ $tanggal }}" max="{{ now()->format('Y-m-d') }}" class="form-control"></div>
                <div class="form-group"><label>Mode</label><select name="mode" class="form-control">@if($canManageHarian)<option value="harian" @selected($mode==='harian')>Harian Wali Kelas</option>@endif @if($canManageMapel)<option value="mapel" @selected($mode==='mapel')>Per Mapel</option>@endif</select></div>
                <div class="form-group"><label>Kelas</label><select name="kelas_id" class="form-control"><option value="">Pilih kelas</option>@foreach($kelasOptions as $kelas)<option value="{{ $kelas->id }}" @selected($selectedKelas?->id===$kelas->id)>Tingkat {{ $kelas->tingkat }} · {{ $kelas->nama_kelas }}{{ $kelas->asrama_suffix }}</option>@endforeach</select></div>
                @if($mode === 'mapel')
                    <div class="form-group attendance-filter__schedule"><label>Jadwal Saya Hari Ini</label><select name="jadwal_pelajaran_id" class="form-control"><option value="">Pilih jadwal</option>@foreach($jadwalOptions as $jadwal)<option value="{{ $jadwal->id }}" @selected($selectedJadwalId===$jadwal->id)>Jam {{ $jadwal->jam_ke }} · {{ substr($jadwal->jam_mulai,0,5) }}–{{ substr($jadwal->jam_selesai,0,5) }} · {{ $jadwal->mapel_nama ?? 'Mapel' }}</option>@endforeach</select></div>
                @endif
                <button class="btn btn-primary"><i class="fas fa-sync-alt mr-1"></i>Muat Sesi</button>
            </form>
        </section>

        @if($selectedKelas && ($mode === 'harian' || $selectedJadwalId))
            <div class="attendance-kpis">
                <div class="attendance-kpi is-blue"><span>Total Siswa</span><strong>{{ $students->count() }}</strong><small>{{ $selectedKelas->nama_kelas }}{{ $selectedKelas->asrama_suffix }} · Tingkat {{ $selectedKelas->tingkat }}</small></div>
                <div class="attendance-kpi is-green"><span>Hadir Mengikuti</span><strong>{{ $presentCount }}</strong><small>Termasuk terlambat dan keluar awal</small></div>
                <div class="attendance-kpi is-red"><span>Perlu Keterangan</span><strong>{{ $exceptionCount }}</strong><small>Sakit, izin, alpa, atau dispensasi</small></div>
                <div class="attendance-kpi is-purple"><span>Status Sesi</span><strong class="text-capitalize">{{ $session?->status ?? 'Baru' }}</strong><small>{{ $session ? 'Versi '.$session->version.' · '.$session->updated_at?->format('d/m/Y H:i') : 'Belum pernah disimpan' }}</small></div>
            </div>

            <section class="attendance-panel">
                <div class="attendance-section-head attendance-panel__head">
                    <div><h2>{{ $mode === 'harian' ? 'Absensi Harian Wali Kelas' : ($jadwalOptions->firstWhere('id',$selectedJadwalId)?->mapel_nama ?? 'Absensi Mapel') }}</h2><p>{{ Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }} · {{ $selectedKelas->nama_kelas }}{{ $selectedKelas->asrama_suffix }} · Input manual oleh guru</p></div>
                    @if($session?->status === 'final')<span class="session-state is-final"><i class="fas fa-lock mr-1"></i>Final · terkunci {{ $session->locked_at?->format('d/m H:i') }}</span>@else<span class="session-state"><i class="fas fa-pencil-alt mr-1"></i>{{ $session ? 'Draft' : 'Sesi baru' }}</span>@endif
                </div>

                @if($students->isEmpty())
                    <div class="attendance-empty is-inline"><i class="fas fa-users-slash"></i><h3>Tidak ada siswa pada tanggal ini</h3><p>Sistem membaca riwayat tanggal masuk dan keluar kelas, bukan hanya status kelas saat ini.</p></div>
                @else
                    <form method="POST" action="{{ route('admin.absensi-siswa.store') }}" id="attendanceForm">
                        @csrf
                        <input type="hidden" name="tanggal" value="{{ $tanggal }}"><input type="hidden" name="mode" value="{{ $mode }}"><input type="hidden" name="kelas_id" value="{{ $selectedKelas->id }}">@if($mode==='mapel')<input type="hidden" name="jadwal_pelajaran_id" value="{{ $selectedJadwalId }}">@endif
                        <div class="attendance-toolbar">
                            <div><button type="button" class="btn btn-sm btn-success quick-status" data-status="hadir"><i class="fas fa-check-double mr-1"></i>Semua Hadir</button><button type="button" class="btn btn-sm btn-outline-danger quick-status" data-status="alpa">Semua Alpa</button></div>
                            <small><i class="fas fa-history mr-1"></i>Setiap perubahan status dan pelakunya dicatat.</small>
                        </div>
                        <div class="table-responsive">
                            <table class="table attendance-table">
                                <thead><tr><th>#</th><th>Siswa</th><th>Status</th><th>Durasi</th><th>Catatan</th></tr></thead>
                                <tbody>
                                @foreach($students as $index => $siswa)
                                    @php($record=$existingRecords->get($siswa->id))
                                    @php($currentStatus=old("statuses.$siswa->id",$record?->status??'hadir'))
                                    <tr>
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
            <div class="attendance-empty"><i class="fas fa-calendar-day"></i><h3>Tidak ada kelas atau jadwal pada tanggal ini</h3><p>Guru mapel hanya melihat kelas yang sesuai jadwal aktif pada hari terpilih.</p></div>
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
<style>
.attendance-hero{display:flex;justify-content:space-between;align-items:center;gap:2rem;padding:1.6rem 1.8rem;border-radius:20px;background:linear-gradient(135deg,#4f7df3,#4779ed);color:#fff;box-shadow:0 16px 36px rgba(59,94,190,.18)}.attendance-hero span{font-size:.78rem;font-weight:800}.attendance-hero h1{margin:.35rem 0 .25rem;font-size:1.65rem;font-weight:800}.attendance-hero p{margin:0;opacity:.92}.attendance-hero__meta{display:flex;align-items:center;gap:.7rem}.attendance-hero__meta>div{min-width:180px;padding:.7rem 1rem;border:1px solid rgba(255,255,255,.3);border-radius:13px;background:rgba(255,255,255,.12)}.attendance-hero__meta small,.attendance-hero__meta strong{display:block}.attendance-filter,.attendance-panel,.attendance-empty{border:1px solid #dbe4f0;border-radius:18px;background:#fff;box-shadow:0 14px 35px rgba(15,23,42,.07)}.attendance-filter{padding:1.25rem}.attendance-section-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem}.attendance-section-head h2{margin:0;font-size:1.08rem;font-weight:800;color:#172033}.attendance-section-head p{margin:.25rem 0 0;color:#64748b}.attendance-filter form{display:grid;grid-template-columns:180px 210px minmax(220px,1fr) auto;gap:.9rem;align-items:end}.attendance-filter__schedule{grid-column:1/-2}.attendance-filter .form-group{margin:0}.attendance-filter label{font-size:.75rem;text-transform:uppercase;letter-spacing:.03em;color:#475569}.attendance-kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem}.attendance-kpi{position:relative;padding:1.1rem 1.2rem;border:1px solid #dbe4f0;border-top:4px solid;border-radius:16px;background:#fff}.attendance-kpi span,.attendance-kpi small{display:block;color:#64748b}.attendance-kpi span{font-size:.76rem;font-weight:800;text-transform:uppercase}.attendance-kpi strong{display:block;margin:.25rem 0;font-size:1.65rem;color:#111827}.attendance-kpi small{font-size:.76rem}.attendance-kpi.is-blue{border-top-color:#4f6ef7}.attendance-kpi.is-green{border-top-color:#22c55e}.attendance-kpi.is-red{border-top-color:#ef4444}.attendance-kpi.is-purple{border-top-color:#8b5cf6}.attendance-panel{overflow:hidden}.attendance-panel__head{padding:1.2rem 1.4rem;margin:0;border-bottom:1px solid #e5eaf2}.session-state{padding:.35rem .65rem;border-radius:999px;background:#eef2ff;color:#4f46e5;font-size:.75rem;font-weight:800}.session-state.is-final{background:#dcfce7;color:#15803d}.attendance-toolbar,.attendance-actions{display:flex;justify-content:space-between;align-items:center;gap:.8rem;padding:1rem 1.25rem}.attendance-toolbar{background:#f8fafc}.attendance-toolbar small{color:#64748b}.attendance-table{margin:0;min-width:960px}.attendance-table thead th{background:#f1f5f9;color:#526078;font-size:.72rem;text-transform:uppercase;border-bottom:1px solid #dbe4f0}.attendance-table td{vertical-align:middle;border-color:#edf0f5}.attendance-table td:nth-child(1){width:52px;text-align:center}.attendance-table td:nth-child(2){min-width:240px}.attendance-table td:nth-child(3){width:165px}.attendance-table td:nth-child(4){width:135px}.attendance-table td:nth-child(5){width:210px}.attendance-table td strong,.attendance-table td small{display:block}.attendance-table td small{color:#94a3b8}.duration-field small{margin-top:.15rem;font-size:.65rem}.attendance-form-notes{display:grid;grid-template-columns:1fr 1fr;gap:1rem;padding:1rem 1.25rem;border-top:1px solid #e5eaf2;background:#f8fafc}.attendance-actions{border-top:1px solid #e5eaf2}.attendance-actions>div{margin-right:auto}.attendance-actions strong,.attendance-actions small{display:block}.attendance-actions small{color:#64748b}.attendance-empty{padding:4rem 2rem;text-align:center}.attendance-empty i{display:inline-grid;place-items:center;width:72px;height:72px;border-radius:50%;background:#eef2ff;color:#4f6ef7;font-size:1.7rem}.attendance-empty h3{margin:1rem 0 .35rem;font-weight:800}.attendance-empty p{color:#64748b}.attendance-empty.is-inline{border:0;box-shadow:none}.attendance-alert{border:0;border-radius:12px}.student-note-trigger{display:flex;width:100%;align-items:center;gap:.5rem;padding:.42rem .5rem;border:1px dashed #b8c6da;border-radius:9px;color:#617087;background:#f8fafc;text-align:left;transition:.15s ease}.student-note-trigger:hover{border-color:#6688d8;color:#315dc3;background:#f1f5ff}.student-note-trigger.has-note{border-style:solid;border-color:#a9c2ee;color:#3159a7;background:#edf4ff}.student-note-trigger__icon{display:inline-flex;flex:0 0 28px;width:28px;height:28px;align-items:center;justify-content:center;border-radius:8px;color:#fff;background:#7890b5}.student-note-trigger.has-note .student-note-trigger__icon{background:#4f72d8}.student-note-trigger strong,.student-note-trigger small{display:block;max-width:145px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.student-note-trigger strong{font-size:.72rem}.student-note-trigger small{font-size:.65rem}.student-note-modal{overflow:hidden;border:0;border-radius:17px}.student-note-modal__header{display:flex;align-items:center;gap:.7rem;padding:1rem 1.1rem;color:#fff;background:linear-gradient(135deg,#426bd9,#2f9cb0)}.student-note-modal__icon{display:inline-flex;width:42px;height:42px;align-items:center;justify-content:center;border-radius:12px;background:rgba(255,255,255,.18);font-size:1.1rem}.student-note-modal__header>div{flex:1}.student-note-modal__header small,.student-note-modal__header h4{display:block;margin:0}.student-note-modal__header small{font-size:.62rem;font-weight:800;letter-spacing:.08em;opacity:.85}.student-note-modal__header h4{font-size:1.02rem;font-weight:800}.student-note-modal__header .close{color:#fff;opacity:.8}.student-note-modal .modal-body label{color:#3d4b61;font-size:.76rem;font-weight:800}.student-note-modal .modal-body textarea{border-color:#cfd9e7;border-radius:10px;resize:vertical}.student-note-modal__helper{display:flex;justify-content:space-between;gap:.6rem;margin-top:.45rem;color:#7b8797;font-size:.68rem}.student-note-modal__helper strong{color:#4265b2}@media(max-width:1100px){.attendance-kpis{grid-template-columns:repeat(2,1fr)}.attendance-filter form{grid-template-columns:repeat(2,1fr)}.attendance-filter__schedule{grid-column:auto}.attendance-filter button{height:38px}}@media(max-width:767px){.attendance-hero{align-items:flex-start;flex-direction:column}.attendance-hero__meta{width:100%;flex-wrap:wrap}.attendance-hero__meta>div{flex:1}.attendance-filter form,.attendance-kpis,.attendance-form-notes{grid-template-columns:1fr}.attendance-actions{flex-wrap:wrap}.attendance-actions>div{width:100%}.attendance-actions .btn{flex:1}.attendance-section-head{align-items:flex-start;gap:.7rem}.student-note-modal .modal-footer{flex-wrap:wrap}.student-note-modal .modal-footer .btn{flex:1}.student-note-modal .modal-footer .mr-auto{flex-basis:100%;margin-right:0!important}}
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function(){
    let activeNoteInput=null;
    let activeNoteButton=null;
    const refreshNoteCount=function(){const length=$('#studentNoteEditor').val().length;$('#studentNoteCount').text(length)};
    const refreshDuration=function(select){const row=$(select).closest('tr'),status=$(select).val();row.find('.late-field').toggleClass('d-none',status!=='terlambat');row.find('.early-field').toggleClass('d-none',status!=='keluar_awal');row.find('.duration-empty').toggleClass('d-none',['terlambat','keluar_awal'].includes(status));};
    $('.student-status').each(function(){refreshDuration(this)}).on('change',function(){refreshDuration(this)});
    $('.quick-status').on('click',function(){$('.student-status').val($(this).data('status')).trigger('change')});
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

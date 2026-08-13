@extends('adminlte::page')

@section('title', 'Presensi GTK')

@section('content_header')
<div class="row align-items-center">
    <div class="col-sm-7"><h1 class="m-0"><i class="fas fa-user-clock text-primary mr-1"></i> Presensi GTK</h1></div>
    <div class="col-sm-5"><ol class="breadcrumb float-sm-right mb-0"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item">Presensi</li><li class="breadcrumb-item active">GTK</li></ol></div>
</div>
@stop

@section('content')
@php
    $selectedDate = \Carbon\Carbon::parse($tanggal);
    $isToday = $selectedDate->isToday();
    $hasFilter = request()->filled('q') || request()->filled('status') || request()->filled('metode');
    $statusMeta = [
        'hadir' => ['Hadir', 'success', 'check-circle'],
        'terlambat' => ['Terlambat', 'warning', 'clock'],
        'izin' => ['Izin', 'info', 'envelope-open-text'],
        'sakit' => ['Sakit', 'primary', 'briefcase-medical'],
        'alpa' => ['Alpa', 'danger', 'times-circle'],
        'dinas_luar' => ['Dinas luar', 'secondary', 'briefcase'],
        'cuti' => ['Cuti', 'dark', 'calendar-minus'],
    ];
@endphp
<div class="attendance-page pb-4">
    @if(session('success'))<div class="alert alert-success alert-dismissible"><button class="close" data-dismiss="alert">&times;</button><i class="fas fa-check-circle mr-1"></i>{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><strong>Data presensi belum dapat diproses.</strong><ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <div class="card bg-gradient-primary text-white attendance-hero mb-3">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <div class="attendance-hero__eyebrow"><i class="fas fa-satellite-dish mr-1"></i> Monitoring kehadiran GTK</div>
                    <h4 class="mb-1">{{ $isPersonalScope ? 'Presensi Saya' : 'Pusat Presensi GTK' }}</h4>
                    <p class="mb-0">{{ $isPersonalScope ? 'Lihat status masuk, pulang, dan riwayat presensi Anda secara ringkas.' : 'Pantau kehadiran, keterlambatan, kepulangan, serta kelengkapan data GTK dalam satu tampilan.' }}</p>
                </div>
                <div class="col-lg-5 mt-3 mt-lg-0">
                    <div class="attendance-hero__date">
                        <span>{{ $selectedDate->translatedFormat('l') }}</span>
                        <strong>{{ $selectedDate->translatedFormat('d F Y') }}</strong>
                        <small><i class="fas fa-circle mr-1 {{ $isHoliday ? 'text-warning' : 'text-success' }}"></i>{{ $isHoliday ? 'Hari libur / nonaktif' : ($isToday ? 'Hari kerja · hari ini' : 'Hari kerja terpilih') }}</small>
                    </div>
                    <div class="attendance-hero__actions mt-2">
                        <a href="{{ route('admin.absensi.rekap', ['bulan' => $selectedDate->month, 'tahun' => $selectedDate->year]) }}" class="btn btn-light btn-sm"><i class="fas fa-chart-bar mr-1"></i>Rekap Bulanan</a>
                        @can('face-registration-admin')<a href="{{ route('admin.absensi.kiosk') }}" target="_blank" rel="noopener" class="btn btn-dark btn-sm"><i class="fas fa-desktop mr-1"></i>Mode Kiosk</a>@endcan
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row attendance-metrics">
        @foreach([
            ['Populasi GTK', $stats['total_gtk'], 'GTK aktif', 'primary', 'users'],
            ['Sudah tercatat', $stats['tercatat'], $stats['persentase'].'% kelengkapan', 'success', 'clipboard-check'],
            ['Belum presensi', $stats['belum'], 'Belum memiliki rekaman', 'warning', 'user-clock'],
            ['Sudah pulang', $stats['sudah_pulang'], 'Check-out selesai', 'info', 'sign-out-alt'],
        ] as [$label, $value, $caption, $color, $icon])
        <div class="col-6 col-xl-3 mb-3"><div class="card attendance-metric h-100"><div class="card-body">
            <span class="attendance-metric__icon bg-{{ $color }}"><i class="fas fa-{{ $icon }}"></i></span>
            <div><small>{{ $label }}</small><strong>{{ number_format($value) }}</strong><span>{{ $caption }}</span></div>
        </div></div></div>
        @endforeach
    </div>

    <div class="card card-outline card-primary attendance-workspace">
        <div class="card-header attendance-card-header">
            <div><h3 class="card-title"><i class="fas fa-calendar-check mr-1"></i> Kehadiran Harian</h3><small>Gunakan filter untuk menemukan data tanpa mengubah rekap tanggal terpilih.</small></div>
            <div class="attendance-card-actions">
                @can('create-absensi')@if(!$isPersonalScope)<button class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalManual"><i class="fas fa-plus mr-1"></i>Input Manual</button>@endif@endcan
                <a href="{{ route('admin.absensi.export', ['bulan' => $selectedDate->month, 'tahun' => $selectedDate->year]) }}" class="btn btn-outline-success btn-sm"><i class="fas fa-file-excel mr-1"></i>Export Bulan Ini</a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.absensi.index') }}" class="attendance-filter mb-3">
                <div class="row align-items-end">
                    <div class="col-sm-6 col-lg-2"><div class="form-group"><label>Tanggal</label><input type="date" name="tanggal" class="form-control" value="{{ $tanggal }}"></div></div>
                    <div class="col-sm-6 col-lg-3"><div class="form-group"><label>Pencarian GTK</label><div class="input-group"><div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-search"></i></span></div><input name="q" class="form-control" value="{{ request('q') }}" placeholder="Nama atau NIP"></div></div></div>
                    <div class="col-sm-6 col-lg-2"><div class="form-group"><label>Status</label><select name="status" class="form-control"><option value="">Semua status</option>@foreach($statusMeta as $key => $meta)<option value="{{ $key }}" @selected(request('status') === $key)>{{ $meta[0] }}</option>@endforeach</select></div></div>
                    <div class="col-sm-6 col-lg-2"><div class="form-group"><label>Metode</label><select name="metode" class="form-control"><option value="">Semua metode</option><option value="face" @selected(request('metode') === 'face')>Pengenalan wajah</option><option value="manual" @selected(request('metode') === 'manual')>Input manual</option></select></div></div>
                    <div class="col-lg-3"><div class="form-group attendance-filter__actions"><button class="btn btn-primary"><i class="fas fa-filter mr-1"></i>Terapkan</button><a href="{{ route('admin.absensi.index', ['tanggal' => $tanggal]) }}" class="btn btn-outline-secondary"><i class="fas fa-redo mr-1"></i>Reset</a>@unless($isToday)<a href="{{ route('admin.absensi.index') }}" class="btn btn-outline-primary" title="Kembali ke hari ini"><i class="fas fa-calendar-day"></i></a>@endunless</div></div>
                </div>
            </form>

            <div class="attendance-status-strip mb-3">
                @foreach($statusMeta as $key => $meta)
                    <a href="{{ route('admin.absensi.index', array_filter(['tanggal' => $tanggal, 'status' => $key, 'q' => request('q'), 'metode' => request('metode')])) }}" class="attendance-status-pill {{ request('status') === $key ? 'is-active' : '' }}"><i class="fas fa-{{ $meta[2] }} text-{{ $meta[1] }}"></i><span>{{ $meta[0] }}</span><strong>{{ $stats[$key] }}</strong></a>
                @endforeach
            </div>

            <div class="attendance-progress mb-3">
                <div><span>Kelengkapan presensi</span><strong>{{ $stats['tercatat'] }} dari {{ $stats['total_gtk'] }} GTK</strong></div>
                <div class="progress"><div class="progress-bar bg-success" role="progressbar" style="width: {{ min($stats['persentase'], 100) }}%" aria-valuenow="{{ $stats['persentase'] }}" aria-valuemin="0" aria-valuemax="100"></div></div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
                <p class="text-muted small mb-1">Menampilkan <strong>{{ $absensis->count() }}</strong> rekaman{{ $hasFilter ? ' sesuai filter' : '' }}.</p>
                @if($isHoliday)<span class="badge badge-warning p-2"><i class="fas fa-calendar-times mr-1"></i>Tanggal terpilih adalah hari libur</span>@endif
            </div>
            <div class="table-responsive attendance-table-wrap">
                <table class="table table-hover attendance-table mb-0">
                    <thead><tr><th>GTK</th><th>Waktu Kerja</th><th>Status</th><th>Sumber Presensi</th><th>Catatan</th><th class="text-right">Aksi</th></tr></thead>
                    <tbody>
                    @forelse($absensis as $absensi)
                        @php $meta = $statusMeta[$absensi->status] ?? [ucfirst($absensi->status), 'secondary', 'info-circle']; @endphp
                        <tr>
                            <td><div class="attendance-person"><img src="{{ $absensi->user?->gtk?->foto_profile_url }}" alt=""><div><strong>{{ $absensi->user?->gtk?->nama_lengkap ?? $absensi->user?->name ?? 'GTK tidak ditemukan' }}</strong><span>NIP {{ $absensi->user?->gtk?->nip ?: 'belum tersedia' }}</span></div></div></td>
                            <td><div class="attendance-time"><span><i class="fas fa-sign-in-alt text-success"></i><strong>{{ $absensi->waktu_masuk_formatted }}</strong></span><span><i class="fas fa-sign-out-alt text-info"></i><strong>{{ $absensi->waktu_pulang_formatted }}</strong></span></div>@if($absensi->durasi_kerja)<small class="text-muted d-block mt-1">Durasi {{ $absensi->durasi_kerja }}</small>@endif</td>
                            <td><span class="badge badge-{{ $meta[1] }} attendance-badge"><i class="fas fa-{{ $meta[2] }} mr-1"></i>{{ $meta[0] }}</span>@if($absensi->status_pulang)<small class="d-block text-muted mt-1">Pulang: {{ ucfirst(str_replace('_', ' ', $absensi->status_pulang)) }}</small>@endif</td>
                            <td><div class="attendance-source"><strong><i class="fas fa-{{ $absensi->metode_masuk === 'face' ? 'user-shield' : 'keyboard' }} mr-1"></i>{{ $absensi->metode_masuk === 'face' ? 'Wajah' : 'Manual' }}</strong><span><i class="fas fa-map-marker-alt mr-1"></i>{{ $absensi->location?->nama ?: 'Lokasi tidak dicatat' }}</span>@if($absensi->face_confidence_masuk)<span>Confidence {{ number_format($absensi->face_confidence_masuk * 100, 1) }}%</span>@endif</div></td>
                            <td><span class="attendance-note">{{ $absensi->catatan ?: 'Tidak ada catatan' }}</span>@if($absensi->edited_at)<small class="d-block text-warning mt-1"><i class="fas fa-history mr-1"></i>Pernah dikoreksi</small>@endif</td>
                            <td class="text-right">@can('edit-absensi')<button type="button" class="btn btn-outline-primary btn-sm edit-attendance" data-id="{{ $absensi->id }}" title="Koreksi presensi"><i class="fas fa-edit"></i><span class="d-none d-xl-inline ml-1">Koreksi</span></button>@else<span class="text-muted">—</span>@endcan</td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="attendance-empty"><span><i class="fas fa-{{ $isHoliday ? 'calendar-times' : 'clipboard' }}"></i></span><strong>{{ $hasFilter ? 'Data tidak ditemukan' : ($isHoliday ? 'Tidak ada presensi pada hari libur' : 'Belum ada rekaman presensi') }}</strong><p>{{ $hasFilter ? 'Ubah atau reset filter untuk melihat data lainnya.' : 'Data akan tampil setelah GTK melakukan presensi atau operator membuat input manual.' }}</p></div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @can('create-absensi')@if(!$isPersonalScope)
    <div class="modal fade" id="modalManual" tabindex="-1"><div class="modal-dialog modal-lg"><form method="POST" action="{{ route('admin.absensi.manual') }}" enctype="multipart/form-data" class="modal-content">@csrf
        <div class="modal-header"><div><h5 class="modal-title"><i class="fas fa-keyboard text-success mr-1"></i>Input Presensi Manual</h5><small class="text-muted">Setiap input tersimpan dalam audit log.</small></div><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body"><input type="hidden" name="tanggal" value="{{ $tanggal }}"><div class="alert alert-warning py-2"><i class="fas fa-shield-alt mr-1"></i>Gunakan hanya untuk koreksi operasional, izin, sakit, atau ketika perangkat kiosk bermasalah.</div>
            <div class="form-group"><label>GTK <span class="text-danger">*</span></label><select name="user_id" id="manualGtk" class="form-control" required><option value="">Cari nama atau NIP GTK</option>@foreach($gtkOptions as $gtk)<option value="{{ $gtk->user_id }}" data-name="{{ $gtk->nama_lengkap }}" data-nip="{{ $gtk->nip ?: 'NIP belum tersedia' }}" data-photo="{{ $gtk->foto_profile_url }}" @selected(old('user_id') === $gtk->user_id)>{{ $gtk->nama_lengkap }} · {{ $gtk->nip ?: 'tanpa NIP' }}</option>@endforeach</select></div>
            <div class="row"><div class="col-md-4"><div class="form-group"><label>Status <span class="text-danger">*</span></label><select name="status" class="form-control" required>@foreach($statusMeta as $key => $meta)<option value="{{ $key }}" @selected(old('status', 'hadir') === $key)>{{ $meta[0] }}</option>@endforeach</select></div></div><div class="col-6 col-md-4"><div class="form-group"><label>Jam Masuk</label><input type="time" name="waktu_masuk" value="{{ old('waktu_masuk') }}" class="form-control"></div></div><div class="col-6 col-md-4"><div class="form-group"><label>Jam Pulang</label><input type="time" name="waktu_pulang" value="{{ old('waktu_pulang') }}" class="form-control"></div></div></div>
            <div class="form-group"><label>Catatan</label><textarea name="catatan" class="form-control" rows="3" maxlength="500" placeholder="Tuliskan keterangan yang relevan">{{ old('catatan') }}</textarea></div>
            <div class="form-group mb-0"><label>Bukti pendukung <small class="text-muted">(opsional, maks. 2 MB)</small></label><div class="custom-file"><input type="file" name="file_bukti" class="custom-file-input" id="manualEvidence" accept=".jpg,.jpeg,.png,.pdf"><label class="custom-file-label" for="manualEvidence">Pilih JPG, PNG, atau PDF</label></div></div>
        </div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button><button class="btn btn-success"><i class="fas fa-save mr-1"></i>Simpan Presensi</button></div>
    </form></div></div>
    @endif@endcan

    @can('edit-absensi')
    <div class="modal fade" id="modalEdit" tabindex="-1"><div class="modal-dialog"><form id="formEdit" method="POST" class="modal-content">@csrf @method('PUT')
        <div class="modal-header"><div><h5 class="modal-title"><i class="fas fa-edit text-primary mr-1"></i>Koreksi Presensi</h5><small class="text-muted" id="editGtkName"></small></div><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body"><div class="alert alert-info py-2"><i class="fas fa-history mr-1"></i>Nilai lama dan alasan perubahan akan disimpan dalam audit log.</div>
            <div class="form-group"><label>Status <span class="text-danger">*</span></label><select name="status" id="editStatus" class="form-control" required>@foreach($statusMeta as $key => $meta)<option value="{{ $key }}">{{ $meta[0] }}</option>@endforeach</select></div>
            <div class="form-group"><label>Catatan</label><textarea name="catatan" id="editCatatan" class="form-control" rows="3" maxlength="500"></textarea></div>
            <div class="form-group mb-0"><label>Alasan koreksi <span class="text-danger">*</span></label><input name="edit_reason" class="form-control" maxlength="255" required placeholder="Contoh: verifikasi surat izin dari GTK"></div>
        </div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button><button class="btn btn-primary"><i class="fas fa-save mr-1"></i>Simpan Koreksi</button></div>
    </form></div></div>
    @endcan
</div>
@stop

@section('css')
<style>
.attendance-page .attendance-hero{border:0;border-radius:.75rem;overflow:hidden;box-shadow:0 8px 24px rgba(37,99,235,.16)}.attendance-page .attendance-hero .card-body{padding:1.15rem 1.25rem}.attendance-hero__eyebrow{font-size:.7rem;font-weight:800;letter-spacing:.07em;text-transform:uppercase;opacity:.9}.attendance-hero p{font-size:.87rem;opacity:.92}.attendance-hero__date{display:flex;flex-direction:column;align-items:flex-end}.attendance-hero__date span{font-size:.72rem;text-transform:uppercase;opacity:.85}.attendance-hero__date strong{font-size:1.2rem}.attendance-hero__date small{font-size:.72rem}.attendance-hero__actions{display:flex;justify-content:flex-end;gap:.35rem;flex-wrap:wrap}.attendance-metric{border:1px solid #e2e8f0;box-shadow:0 4px 14px rgba(15,23,42,.05)}.attendance-metric .card-body{padding:.8rem;display:flex;align-items:center;gap:.7rem}.attendance-metric__icon{width:42px;height:42px;border-radius:.55rem;color:#fff;display:inline-flex;align-items:center;justify-content:center;flex:0 0 42px}.attendance-metric small,.attendance-metric strong,.attendance-metric span{display:block}.attendance-metric small{font-size:.68rem;font-weight:700;color:#64748b;text-transform:uppercase}.attendance-metric strong{font-size:1.35rem;line-height:1.15;color:#0f172a}.attendance-metric span{font-size:.7rem;color:#64748b}.attendance-workspace{border-radius:.65rem;box-shadow:0 5px 18px rgba(15,23,42,.06)}.attendance-card-header{display:flex;align-items:center;justify-content:space-between;gap:.75rem}.attendance-card-header h3{float:none;margin:0}.attendance-card-header small{display:block;color:#64748b;margin-top:.2rem}.attendance-card-actions{display:flex;gap:.35rem;flex-wrap:wrap}.attendance-filter{padding:.8rem;background:#f8fafc;border:1px solid #dbe4ef;border-radius:.55rem}.attendance-filter .form-group{margin-bottom:0}.attendance-filter label{font-size:.7rem;color:#475569;margin-bottom:.25rem;text-transform:uppercase}.attendance-filter__actions{display:flex;gap:.35rem}.attendance-status-strip{display:grid;grid-template-columns:repeat(7,minmax(105px,1fr));gap:.4rem;overflow-x:auto;padding-bottom:.2rem}.attendance-status-pill{display:grid;grid-template-columns:auto 1fr auto;align-items:center;gap:.35rem;padding:.55rem .6rem;border:1px solid #e2e8f0;border-radius:.5rem;color:#475569;background:#fff;white-space:nowrap}.attendance-status-pill:hover,.attendance-status-pill.is-active{border-color:#6366f1;background:#eef2ff;color:#312e81;text-decoration:none}.attendance-status-pill span{font-size:.72rem}.attendance-status-pill strong{font-size:.9rem}.attendance-progress{padding:.65rem .75rem;border:1px solid #dcfce7;background:#f0fdf4;border-radius:.5rem}.attendance-progress>div:first-child{display:flex;justify-content:space-between;font-size:.72rem;color:#166534;margin-bottom:.35rem}.attendance-progress .progress{height:7px;background:#dcfce7}.attendance-table-wrap{border:1px solid #e2e8f0;border-radius:.5rem}.attendance-table{min-width:920px;font-size:.78rem}.attendance-table thead th{border-top:0;background:#f8fafc;color:#475569;font-size:.68rem;text-transform:uppercase;letter-spacing:.03em}.attendance-table td{vertical-align:middle}.attendance-person{display:flex;align-items:center;gap:.55rem;min-width:210px}.attendance-person img{width:38px;height:48px;object-fit:cover;border-radius:.4rem;border:1px solid #dbeafe;background:#eff6ff}.attendance-person strong,.attendance-person span{display:block}.attendance-person strong{color:#0f172a}.attendance-person span,.attendance-source span{font-size:.68rem;color:#64748b}.attendance-time{display:flex;gap:.65rem}.attendance-time span{display:flex;align-items:center;gap:.25rem}.attendance-source strong,.attendance-source span{display:block}.attendance-badge{padding:.35rem .5rem}.attendance-note{display:block;max-width:190px;color:#475569;white-space:normal}.attendance-empty{text-align:center;padding:2.5rem 1rem;color:#64748b}.attendance-empty>span{width:52px;height:52px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background:#eef2ff;color:#4f46e5;font-size:1.2rem;margin-bottom:.65rem}.attendance-empty strong{display:block;color:#334155}.attendance-empty p{margin:.25rem 0 0}.manual-gtk-option{display:flex;align-items:center;gap:.6rem;min-height:48px}.manual-gtk-option img{width:34px;height:42px;object-fit:cover;border-radius:.35rem}.manual-gtk-option strong,.manual-gtk-option small{display:block}.manual-gtk-option small{color:#64748b}.select2-results__options{max-height:260px!important;overflow-y:auto!important}
@media(min-width:992px) and (max-width:1439.98px){.attendance-page .card-body{padding:.85rem}.attendance-status-strip{grid-template-columns:repeat(7,120px)}.attendance-filter>.row>[class*="col-"]{padding-left:.3rem;padding-right:.3rem}.attendance-filter__actions .btn{padding-left:.55rem;padding-right:.55rem}}
@media(max-width:991.98px){.attendance-hero__date{align-items:flex-start}.attendance-hero__actions{justify-content:flex-start}.attendance-filter .form-group{margin-bottom:.6rem}.attendance-filter__actions{margin-bottom:0}.attendance-card-header{align-items:flex-start;flex-wrap:wrap}}
@media(max-width:575.98px){.attendance-card-actions,.attendance-card-actions .btn{width:100%}.attendance-hero__actions{display:grid;grid-template-columns:1fr 1fr}.attendance-filter__actions{display:grid;grid-template-columns:1fr 1fr}.attendance-filter__actions .btn:last-child:nth-child(3){grid-column:1/-1}.attendance-progress>div:first-child{align-items:flex-start;flex-direction:column}.attendance-page .modal-dialog{margin:.5rem}}
</style>
@stop

@section('js')
<script>
$(function(){
    const records = {{ Illuminate\Support\Js::from($absensis->mapWithKeys(fn($item) => [$item->id => ['status' => $item->status, 'catatan' => $item->catatan, 'nama' => $item->user?->gtk?->nama_lengkap ?? $item->user?->name]])) }};
    $('.edit-attendance').on('click',function(){const record=records[$(this).data('id')];if(!record)return;$('#formEdit').attr('action',@json(url('admin/absensi'))+'/'+$(this).data('id'));$('#editStatus').val(record.status);$('#editCatatan').val(record.catatan||'');$('#editGtkName').text(record.nama||'GTK');$('#modalEdit').modal('show');});
    const optionTemplate=function(option){if(!option.id)return option.text;const source=option.element.dataset,wrap=$('<div class="manual-gtk-option">');return wrap.append($('<img>',{src:source.photo,alt:''}),$('<div>').append($('<strong>').text(source.name),$('<small>').text(source.nip)));};
    if($.fn.select2&&$('#manualGtk').length){$('#manualGtk').select2({theme:'bootstrap4',width:'100%',dropdownParent:$('#modalManual'),placeholder:'Cari nama atau NIP GTK',allowClear:true,templateResult:optionTemplate});}
    $('.custom-file-input').on('change',function(){const file=this.files&&this.files[0];$(this).next('.custom-file-label').text(file?file.name:'Pilih JPG, PNG, atau PDF');});
    @if($errors->any() && old('user_id')) $('#modalManual').modal('show'); @endif
});
</script>
@stop

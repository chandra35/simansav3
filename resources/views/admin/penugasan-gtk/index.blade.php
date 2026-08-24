@extends('adminlte::page')
@section('title', 'Penugasan GTK')
@section('plugins.Select2', true)

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6"><h1><i class="fas fa-user-tie text-primary"></i> Penugasan GTK</h1></div>
    <div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item">GTK</li><li class="breadcrumb-item active">Penugasan</li></ol></div>
</div>
@stop

@section('content')
<div class="gtk-assignment-page">
    @if(session('success'))
        <div class="alert assignment-feedback assignment-feedback--success rounded-2xl shadow-xl" role="status">
            <span class="assignment-feedback__icon"><i class="fas fa-check"></i></span>
            <div class="assignment-feedback__body"><strong>Penugasan berhasil disimpan</strong><p>{{ session('success') }}</p></div>
            <button type="button" class="assignment-feedback__close" data-dismiss="alert" aria-label="Tutup notifikasi"><i class="fas fa-times"></i></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert assignment-feedback assignment-feedback--error rounded-2xl shadow-xl" role="alert">
            <span class="assignment-feedback__icon"><i class="fas fa-exclamation"></i></span>
            <div class="assignment-feedback__body"><strong>Penugasan belum dapat diproses</strong><p>{{ session('error') }}</p></div>
            <button type="button" class="assignment-feedback__close" data-dismiss="alert" aria-label="Tutup notifikasi"><i class="fas fa-times"></i></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert assignment-feedback assignment-feedback--error rounded-2xl shadow-xl" role="alert">
            <span class="assignment-feedback__icon"><i class="fas fa-exclamation"></i></span>
            <div class="assignment-feedback__body"><strong>Data penugasan perlu diperiksa lagi</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            <button type="button" class="assignment-feedback__close" data-dismiss="alert" aria-label="Tutup notifikasi"><i class="fas fa-times"></i></button>
        </div>
    @endif

    <div class="card bg-gradient-primary text-white mb-3 assignment-hero"><div class="card-body py-3"><div class="row align-items-center">
        <div class="col-lg-7 assignment-hero-copy"><h4 class="mb-1"><i class="fas fa-briefcase mr-2"></i>Penugasan & Ekuivalensi Jam</h4><p class="mb-0">Kelola Kepala Madrasah, Waka, Kepala Laboratorium, dan tugas tambahan guru berdasarkan periode akademik.</p></div>
        <div class="col-lg-5 mt-3 mt-lg-0 assignment-hero-actions">@can('view-beban-kerja-gtk')<a href="{{ route('admin.penugasan-gtk.workload') }}" class="btn btn-light btn-sm"><i class="fas fa-balance-scale"></i> Beban Kerja</a>@endcan @can('manage-jenis-penugasan-gtk')<a href="{{ route('admin.penugasan-gtk.types') }}" class="btn btn-info btn-sm"><i class="fas fa-sliders-h"></i> Standar Penugasan</a>@endcan @can('create-penugasan-gtk')<button class="btn btn-success btn-sm" data-toggle="modal" data-target="#assignmentModal" id="addAssignment"><i class="fas fa-plus"></i> Penugasan Baru</button>@endcan</div>
    </div></div></div>

    <div class="row">
        @foreach([['Penugasan Aktif',$stats['aktif'],'primary','briefcase'],['GTK Ditugaskan',$stats['gtk'],'info','users'],['Tugas Utama',$stats['utama'],'success','user-shield'],['Total Ekuivalensi',$stats['jtm'],'warning','clock']] as [$label,$value,$color,$icon])
        <div class="col-6 col-xl-3"><div class="card assignment-stat"><div class="card-body"><div class="d-flex align-items-center"><span class="stat-icon bg-{{ $color }}"><i class="fas fa-{{ $icon }}"></i></span><div><small>{{ $label }}</small><h3>{{ number_format($value) }}@if($label === 'Total Ekuivalensi') <small>JTM</small>@endif</h3></div></div></div></div></div>
        @endforeach
    </div>

    @if($vacantLeadershipTypes->isNotEmpty())
    <div class="alert alert-warning assignment-vacancy d-flex align-items-start mb-3">
        <i class="fas fa-user-clock fa-lg mr-3 mt-1"></i>
        <div><strong>Jabatan aktif belum terisi</strong><div class="small mt-1">{{ $vacantLeadershipTypes->pluck('nama')->join(', ') }}. Histori pejabat sebelumnya tetap tersimpan pada filter <strong>Lepas / selesai</strong>.</div></div>
    </div>
    @endif

    <div class="card card-outline card-primary"><div class="card-header assignment-list-header"><h3 class="card-title"><i class="fas fa-list mr-1"></i> Daftar Penugasan</h3><div class="card-tools text-muted small">Ekuivalensi disimpan sebagai snapshot saat penugasan dibuat</div></div><div class="card-body">
        <form method="GET" class="assignment-filter mb-3"><div class="row">
            <div class="col-md-3"><label>Tahun Pelajaran</label><select name="tahun_pelajaran_id" class="form-control">@foreach($years as $item)<option value="{{ $item->id }}" @selected($year?->id===$item->id)>{{ $item->nama }}</option>@endforeach</select></div>
            <div class="col-md-3"><label>Jenis Penugasan</label><select name="jenis_penugasan_id" class="form-control"><option value="">Semua jenis</option>@foreach($types as $type)<option value="{{ $type->id }}" @selected(request('jenis_penugasan_id')===$type->id)>{{ $type->nama }}</option>@endforeach</select></div>
            <div class="col-md-2"><label>Status</label><select name="status" class="form-control"><option value="active" @selected($statusFilter==='active')>Aktif</option><option value="ended" @selected($statusFilter==='ended')>Lepas / selesai</option><option value="draft" @selected($statusFilter==='draft')>Draft</option><option value="cancelled" @selected($statusFilter==='cancelled')>Dibatalkan</option><option value="all" @selected($statusFilter==='all')>Semua histori</option></select></div>
            <div class="col-md-3"><label>Pencarian</label><input name="q" class="form-control" value="{{ request('q') }}" placeholder="Nama, NIP, atau unit"></div>
            <div class="col-md-1 d-flex align-items-end"><button class="btn btn-primary btn-block" title="Terapkan filter"><i class="fas fa-search"></i></button></div>
        </div></form>

        <div class="table-responsive"><table class="table table-hover table-bordered"><thead><tr><th>GTK</th><th>Penugasan</th><th>Periode Akademik</th><th class="text-center">JTM</th><th>Status</th><th class="text-right">Aksi</th></tr></thead><tbody>
        @forelse($assignments as $assignment)
            @php
                $badge = ['active'=>'success','ended'=>'secondary','draft'=>'warning','cancelled'=>'danger'][$assignment->status] ?? 'secondary';
                $statusLabel = ['active'=>'Aktif','ended'=>'Lepas / selesai','draft'=>'Draft','cancelled'=>'Dibatalkan'][$assignment->status] ?? ucfirst($assignment->status);
                $assignmentRecord = [
                    'gtk_id' => $assignment->gtk_id,
                    'jenis_penugasan_id' => $assignment->jenis_penugasan_id,
                    'tahun_pelajaran_id' => $assignment->tahun_pelajaran_id,
                    'semester' => $assignment->semester,
                    'unit_nama' => $assignment->unit_nama,
                    'keterangan' => $assignment->keterangan,
                ];
            @endphp
            <tr>
                <td><div class="d-flex align-items-center"><img src="{{ $assignment->gtk->foto_profile_url }}" class="assignment-photo mr-2" alt=""><div><strong>{{ $assignment->gtk->nama_lengkap }}</strong><small class="d-block text-muted">NIP {{ $assignment->gtk->nip ?: '-' }}</small></div></div></td>
                <td><strong>{{ $assignment->jenis->nama }}</strong>@if($assignment->unit_nama)<small class="d-block text-muted"><i class="fas fa-map-marker-alt mr-1"></i>{{ $assignment->unit_nama }}</small>@endif<small class="d-block text-muted">{{ $assignment->jenis->dasar_hukum ?: 'Standar internal' }}</small></td>
                <td><strong>{{ $assignment->tahunPelajaran?->nama ?: '-' }}</strong><small class="d-block text-muted">{{ $assignment->semester ? 'Semester '.$assignment->semester : 'Sepanjang tahun' }}</small></td>
                <td class="text-center"><span class="badge badge-primary p-2">{{ $assignment->ekuivalensi_jtm }} JTM</span></td>
                <td>
                    <span class="badge badge-{{ $badge }}">{{ $statusLabel }}</span>
                    @if($assignment->selesai_tugas && $assignment->status === 'ended')
                        <small class="d-block text-muted">Sejak {{ $assignment->selesai_tugas->translatedFormat('d M Y') }}</small>
                    @endif
                    @if($assignment->legacy_tugas_tambahan_id)
                        <small class="d-block text-muted">Migrasi data lama</small>
                    @endif
                </td>
                <td class="text-right text-nowrap">
                    @if($assignment->status==='active')
                        @can('edit-penugasan-gtk')<button class="btn btn-sm btn-primary edit-assignment" data-toggle="modal" data-target="#assignmentModal" data-url="{{ route('admin.penugasan-gtk.update',$assignment) }}" data-record='@json($assignmentRecord)' title="Edit"><i class="fas fa-edit"></i></button>@endcan
                        @can('end-penugasan-gtk')<button class="btn btn-sm btn-warning end-assignment" data-toggle="modal" data-target="#endAssignmentModal" data-url="{{ route('admin.penugasan-gtk.end',$assignment) }}" data-name="{{ $assignment->jenis->nama }} · {{ $assignment->gtk->nama_lengkap }}" title="Akhiri"><i class="fas fa-stop-circle"></i></button>@endcan
                    @elseif(!$assignment->legacy_tugas_tambahan_id)
                        @can('delete-penugasan-gtk')<form action="{{ route('admin.penugasan-gtk.destroy',$assignment) }}" method="POST" class="d-inline archive-form">@csrf @method('DELETE')<button class="btn btn-sm btn-danger" title="Arsipkan"><i class="fas fa-archive"></i></button></form>@endcan
                    @endif
                </td>
            </tr>
        @empty<tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2"></i>{{ $statusFilter === 'active' ? 'Belum ada penugasan aktif pada filter ini.' : 'Belum ada histori penugasan pada filter ini.' }}</td></tr>@endforelse
        </tbody></table></div>{{ $assignments->links() }}
    </div></div>
</div>

@canany(['create-penugasan-gtk','edit-penugasan-gtk'])
<div class="modal fade" id="assignmentModal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable"><form method="POST" action="{{ route('admin.penugasan-gtk.store') }}" class="modal-content rounded-2xl shadow-2xl confirm-assignment-form">@csrf <div id="assignmentMethod"></div>
    <div class="modal-header"><h5 class="modal-title"><i class="fas fa-user-tie mr-1"></i><span id="assignmentModalTitle">Penugasan GTK Baru</span></h5><button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button></div>
    <div class="modal-body"><div class="row">
        <div class="col-12"><div class="form-group"><label>Guru <span class="text-danger">*</span></label><select name="gtk_id" id="assignmentTeacher" class="form-control" required><option value="">Cari nama guru atau NIP</option>@foreach($gtks as $gtk)<option value="{{ $gtk->id }}" data-name="{{ $gtk->nama_lengkap }}" data-nip="{{ $gtk->nip ?: 'NIP belum tersedia' }}" data-role="{{ $gtk->jenis_ptk ?: $gtk->kategori_ptk ?: 'Guru' }}" data-photo="{{ $gtk->foto_profile_url }}">{{ $gtk->nama_lengkap }} · {{ $gtk->nip ?: 'tanpa NIP' }}</option>@endforeach</select><small class="text-muted"><i class="fas fa-search mr-1"></i>Ketik nama atau NIP untuk menemukan guru.</small></div></div>
        <div class="col-md-6"><div class="form-group"><label>Jenis Penugasan <span class="text-danger">*</span></label><select name="jenis_penugasan_id" id="assignmentType" class="form-control assignment-select" required><option value="">Pilih jenis</option>@foreach($types->where('is_active',true) as $type)<option value="{{ $type->id }}" data-jtm="{{ $type->ekuivalensi_jtm }}" data-unit="{{ $type->jenis_unit }}">{{ $type->nama }} · {{ $type->ekuivalensi_jtm }} JTM</option>@endforeach</select><small id="typeHelp" class="text-muted"></small></div></div>
        <div class="col-md-6"><div class="form-group"><label id="unitLabel">Unit/Bidang</label><input name="unit_nama" class="form-control" placeholder="Contoh: Laboratorium Komputer"></div></div>
        <div class="col-md-6"><div class="form-group"><label>Tahun Pelajaran <span class="text-danger">*</span></label><select name="tahun_pelajaran_id" class="form-control" required>@foreach($years as $item)<option value="{{ $item->id }}" @selected($year?->id===$item->id)>{{ $item->nama }}</option>@endforeach</select></div></div>
        <div class="col-md-6"><div class="form-group"><label>Semester</label><select name="semester" class="form-control"><option value="">Sepanjang tahun</option><option value="1">Ganjil</option><option value="2">Genap</option></select></div></div>
        <div class="col-12"><div class="form-group mb-0"><label>Keterangan</label><textarea name="keterangan" class="form-control" rows="2" placeholder="Keterangan penugasan bila diperlukan"></textarea></div></div>
    </div></div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary rounded-xl" data-dismiss="modal">Batal</button><button class="btn btn-primary rounded-xl"><i class="fas fa-save mr-1"></i>Simpan Penugasan</button></div>
</form></div></div>
@endcanany

@can('end-penugasan-gtk')
<div class="modal fade" id="endAssignmentModal" tabindex="-1"><div class="modal-dialog"><form method="POST" class="modal-content">@csrf
    <div class="modal-header"><h5 class="modal-title"><i class="fas fa-stop-circle text-warning mr-1"></i>Akhiri Penugasan</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
    <div class="modal-body"><p id="endAssignmentName" class="font-weight-bold"></p><div class="form-group"><label>Alasan <span class="text-danger">*</span></label><textarea name="alasan" class="form-control" rows="3" required placeholder="Contoh: pergantian pejabat atau perubahan penugasan"></textarea></div><div class="alert alert-info mb-0"><i class="fas fa-history mr-1"></i>Data tidak dihapus. Histori dan ekuivalensi periode lama tetap tersimpan.</div></div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button><button class="btn btn-warning"><i class="fas fa-check mr-1"></i>Akhiri Penugasan</button></div>
</form></div></div>
@endcan
@stop

@section('css')
<style>
.gtk-assignment-page .assignment-hero-actions{display:flex;align-items:center;justify-content:flex-end;flex-wrap:wrap;gap:.35rem}.gtk-assignment-page .assignment-hero-actions .btn{margin:0;white-space:nowrap}.gtk-assignment-page .assignment-stat{border:1px solid #e2e8f0;box-shadow:0 4px 14px rgba(15,23,42,.06)}.gtk-assignment-page .assignment-stat .card-body{padding:.85rem}.gtk-assignment-page .assignment-stat h3{font-size:1.35rem;margin:0;font-weight:700}.gtk-assignment-page .assignment-stat small{color:#64748b;font-weight:600}.gtk-assignment-page .stat-icon{width:40px;height:40px;border-radius:.5rem;display:inline-flex;align-items:center;justify-content:center;color:#fff;margin-right:.7rem}.gtk-assignment-page .assignment-filter{padding:.8rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:.4rem}.gtk-assignment-page .assignment-filter label{font-size:.75rem;margin-bottom:.25rem}.gtk-assignment-page .assignment-photo{width:42px;height:52px;object-fit:cover;border-radius:.35rem;border:1px solid #dbe3ef}.gtk-assignment-page table{min-width:820px;font-size:.82rem}.gtk-assignment-page textarea{resize:vertical}
.gtk-assignment-page .assignment-feedback{position:relative;display:flex;align-items:flex-start;gap:.8rem;margin-bottom:1rem;padding:1rem 3rem 1rem 1rem;border:1px solid;border-radius:1rem;background:#fff;box-shadow:0 12px 30px rgba(15,23,42,.09)}.gtk-assignment-page .assignment-feedback--success{color:#166534;border-color:#bbf7d0;background:linear-gradient(135deg,#f0fdf4,#fff)}.gtk-assignment-page .assignment-feedback--error{color:#991b1b;border-color:#fecaca;background:linear-gradient(135deg,#fef2f2,#fff)}.gtk-assignment-page .assignment-feedback__icon{display:inline-flex;align-items:center;justify-content:center;flex:0 0 2.25rem;width:2.25rem;height:2.25rem;border-radius:.75rem;color:#fff;background:currentColor}.gtk-assignment-page .assignment-feedback__icon i{filter:brightness(0) invert(1)}.gtk-assignment-page .assignment-feedback__body{min-width:0}.gtk-assignment-page .assignment-feedback__body strong{display:block;font-size:.9rem;font-weight:700}.gtk-assignment-page .assignment-feedback__body p,.gtk-assignment-page .assignment-feedback__body ul{margin:.16rem 0 0;font-size:.82rem;line-height:1.5}.gtk-assignment-page .assignment-feedback__body ul{padding-left:1.1rem}.gtk-assignment-page .assignment-feedback__close{position:absolute;top:.75rem;right:.75rem;width:2rem;height:2rem;padding:0;border:0;border-radius:.6rem;background:transparent;color:inherit;opacity:.7;transition:background .18s ease,opacity .18s ease}.gtk-assignment-page .assignment-feedback__close:hover{background:rgba(15,23,42,.08);opacity:1}
#assignmentModal .modal-dialog{max-width:840px;margin:1rem auto}#assignmentModal .modal-content{border:0;border-radius:1rem;overflow:hidden;box-shadow:0 25px 55px rgba(15,23,42,.24)}#assignmentModal .modal-header{align-items:center;padding:1rem 1.25rem;border-bottom:1px solid #e5e7eb;background:#f8fafc}#assignmentModal .modal-title{font-size:1rem;font-weight:700;color:#1e293b}#assignmentModal .modal-header .close{display:inline-flex;align-items:center;justify-content:center;width:2rem;height:2rem;margin:-.25rem 0 0;padding:0;border-radius:.65rem;color:#64748b;opacity:1;transition:background .18s ease,color .18s ease}#assignmentModal .modal-header .close:hover{color:#334155;background:#e2e8f0}#assignmentModal .modal-body{padding:1.25rem;background:#fff}#assignmentModal .form-group{margin-bottom:1.15rem}#assignmentModal .form-group label{margin-bottom:.42rem;font-size:.8rem;font-weight:700;color:#334155}#assignmentModal .form-control{min-height:44px;border-color:#dbe3ef;border-radius:.75rem;box-shadow:0 1px 2px rgba(15,23,42,.02);font-size:.86rem}#assignmentModal .form-control:focus{border-color:#818cf8;box-shadow:0 0 0 .18rem rgba(99,102,241,.14)}#assignmentModal textarea.form-control{min-height:96px;padding-top:.7rem}#assignmentModal .modal-footer{display:flex;gap:.55rem;padding:1rem 1.25rem 1.6rem;border-top:1px solid #e5e7eb;background:#f8fafc}#assignmentModal .modal-footer .btn{min-height:42px;margin:0;padding:.55rem 1rem;border-radius:.75rem;font-size:.84rem;font-weight:700}#assignmentModal .select2-container{width:100%!important}#assignmentModal .select2-container--bootstrap4 .select2-selection--single{height:44px;border:1px solid #dbe3ef;border-radius:.75rem;box-shadow:0 1px 2px rgba(15,23,42,.02)}#assignmentModal .select2-container--bootstrap4.select2-container--focus .select2-selection--single{border-color:#818cf8;box-shadow:0 0 0 .18rem rgba(99,102,241,.14)}#assignmentModal .select2-selection__rendered{display:flex!important;align-items:center;min-width:0;height:100%;padding:0 .8rem!important;line-height:normal!important;color:#334155}#assignmentModal .select2-selection__arrow{top:0!important;height:100%!important}#assignmentModal .select2-selection__clear{display:inline-flex;align-items:center;justify-content:center;float:none!important;order:2;margin:0 0 0 auto!important;padding:0 .25rem;color:#94a3b8;font-size:1.1rem}.assignment-teacher-option{display:flex;align-items:center;gap:.75rem;min-height:62px;padding:.45rem .55rem}.assignment-teacher-option img{width:40px;height:50px;flex:0 0 40px;object-fit:cover;border:1px solid #dbeafe;border-radius:.7rem;background:#eff6ff}.assignment-teacher-option__body{min-width:0}.assignment-teacher-option strong,.assignment-teacher-option small{display:block}.assignment-teacher-option strong{overflow:hidden;color:#0f172a;text-overflow:ellipsis;white-space:nowrap}.assignment-teacher-option small{color:#64748b;font-size:.72rem}.assignment-teacher-selection{display:flex;align-items:center;min-width:0;gap:.55rem}.assignment-teacher-selection img{width:30px;height:34px;flex:0 0 30px;object-fit:cover;border:1px solid #dbeafe;border-radius:.55rem;background:#eff6ff}.assignment-teacher-selection span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
#assignmentModal .select2-container--bootstrap4 .select2-selection--single{border-color:#e2e8f0;border-radius:.5rem;background:#f8fafc}#assignmentModal .select2-container--bootstrap4.select2-container--focus .select2-selection--single,#assignmentModal .select2-container--bootstrap4.select2-container--open .select2-selection--single{border-color:#3b82f6;box-shadow:0 0 0 .2rem rgba(59,130,246,.14)}#assignmentModal .assignment-teacher-dropdown.select2-dropdown{margin-top:.25rem;border:1px solid #e2e8f0;border-radius:.5rem;box-shadow:0 12px 26px rgba(15,23,42,.14);overflow:hidden;background:#fff}#assignmentModal .assignment-teacher-dropdown .select2-search--dropdown{padding:.55rem;background:#f8fafc;border-bottom:1px solid #f1f5f9}#assignmentModal .assignment-teacher-dropdown .select2-search__field{min-height:36px;padding:.4rem .6rem;border:1px solid #e2e8f0;border-radius:.45rem;background:#fff;color:#334155}#assignmentModal .assignment-teacher-dropdown .select2-search__field:focus{outline:0;border-color:#3b82f6;box-shadow:0 0 0 .16rem rgba(59,130,246,.12)}#assignmentModal .assignment-teacher-dropdown .select2-results__option{padding:.2rem .35rem}#assignmentModal .assignment-teacher-dropdown .select2-results__option--highlighted[aria-selected]{background:#eff6ff;color:#1e3a8a}
@media(min-width:992px) and (max-width:1439.98px){.gtk-assignment-page .assignment-stat .card-body{padding:.75rem}.gtk-assignment-page .stat-icon{width:38px;height:38px;margin-right:.55rem}.gtk-assignment-page .assignment-filter>.row>[class*="col-"]{padding-left:.35rem;padding-right:.35rem}}
@media(max-width:991.98px){.gtk-assignment-page .assignment-hero-actions{justify-content:flex-start}.gtk-assignment-page .assignment-list-header{display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:.35rem}.gtk-assignment-page .assignment-list-header::after{display:none}.gtk-assignment-page .assignment-list-header .card-tools{float:none;margin-left:auto}.gtk-assignment-page .assignment-filter .col-md-1{margin-top:.5rem}}
@media(max-width:767.98px){.gtk-assignment-page .assignment-hero-actions{display:grid;grid-template-columns:1fr}.gtk-assignment-page .assignment-hero-actions .btn{width:100%}.gtk-assignment-page .assignment-list-header .card-tools{width:100%;margin:0}.gtk-assignment-page .assignment-filter>.row>[class*="col-"]+ [class*="col-"]{margin-top:.5rem}}
@media(max-width:575.98px){.gtk-assignment-page .assignment-feedback{padding:.85rem 2.7rem .85rem .85rem}.gtk-assignment-page .assignment-feedback__icon{flex-basis:2rem;width:2rem;height:2rem}.gtk-assignment-page .assignment-feedback__body strong{font-size:.84rem}#assignmentModal .modal-dialog{margin:.45rem}#assignmentModal .modal-header{padding:.9rem 1rem}#assignmentModal .modal-body{padding:1rem}#assignmentModal .modal-footer{padding:.85rem 1rem 1.25rem}#assignmentModal .modal-footer .btn{flex:1;padding:.55rem .7rem}#assignmentModal .form-group{margin-bottom:1rem}}
</style>
@stop

@section('js')
<script>
$(function(){
    const modal=$('#assignmentModal'), form=modal.find('form'), createUrl=@json(route('admin.penugasan-gtk.store'));
    const teacherOption=function(option){
        if(!option.id)return option.text;
        const source=option.element.dataset, wrap=$('<div class="assignment-teacher-option">'), photo=$('<img>',{src:source.photo,alt:''}), body=$('<div class="assignment-teacher-option__body">');
        body.append($('<strong>').text(source.name)).append($('<small>').text(source.nip+' · '+source.role));
        return wrap.append(photo,body);
    };
    const teacherSelection=function(option){
        if(!option.id)return option.text;
        const source=option.element.dataset, wrap=$('<span class="assignment-teacher-selection">');
        return wrap.append($('<img>',{src:source.photo,alt:''}),$('<span>').text(source.name));
    };
    $('#assignmentTeacher').select2({theme:'bootstrap4',width:'100%',dropdownParent:modal,dropdownCssClass:'assignment-teacher-dropdown',placeholder:'Cari nama guru atau NIP',allowClear:true,templateResult:teacherOption,templateSelection:teacherSelection});
    $('.assignment-select').select2({theme:'bootstrap4',width:'100%',dropdownParent:modal});
    function syncType(){const option=$('#assignmentType option:selected'),unit=option.data('unit');$('[name="unit_nama"]').prop('required',!!unit);$('#unitLabel').text(unit?unit.charAt(0).toUpperCase()+unit.slice(1).replace('_',' ')+' *':'Unit/Bidang');$('#typeHelp').text(option.val()?'Ekuivalensi standar '+option.data('jtm')+' JTM akan dicatat pada periode ini.':'');}
    $('#assignmentType').on('change',syncType);
    $('#addAssignment').on('click',function(){form[0].reset();form.attr('action',createUrl).data('editing',false);$('#assignmentMethod').empty();$('#assignmentModalTitle').text('Penugasan GTK Baru');$('#assignmentTeacher,.assignment-select').val(null).trigger('change');$('[name="tahun_pelajaran_id"]').val(@json($year?->id));syncType();});
    $('.edit-assignment').on('click',function(){const record=$(this).data('record');form[0].reset();form.attr('action',$(this).data('url')).data('editing',true);$('#assignmentMethod').html('<input type="hidden" name="_method" value="PUT">');$('#assignmentModalTitle').text('Perbarui Penugasan GTK');Object.keys(record).forEach(key=>form.find('[name="'+key+'"]').val(record[key]??''));$('#assignmentTeacher,.assignment-select').trigger('change');syncType();});
    $('.end-assignment').on('click',function(){$('#endAssignmentModal form').attr('action',$(this).data('url'));$('#endAssignmentName').text($(this).data('name'));});
    $('.archive-form').on('submit',function(e){if(!confirm('Arsipkan histori penugasan ini?'))e.preventDefault();});
    $('.confirm-assignment-form').on('submit',function(e){if($(this).data('confirmed'))return;e.preventDefault();const target=this;if(window.Swal){Swal.fire({icon:'question',title:'Simpan penugasan?',text:'Sistem akan mencatat tugas dan ekuivalensi jam pada periode yang dipilih.',showCancelButton:true,confirmButtonText:'Ya, simpan',cancelButtonText:'Periksa lagi'}).then(r=>{if(r.isConfirmed){$(target).data('confirmed',true);target.submit();}});}else if(confirm('Simpan penugasan dan ekuivalensi jam ini?')){target.submit();}});
    @if($errors->any()) $('#addAssignment').trigger('click'); @endif
});
</script>
@stop

@extends('adminlte::page')
@section('title', 'Penugasan GTK')
@section('plugins.Select2', true)

@section('content_header')
<div class="row mb-2"><div class="col-sm-6"><h1><i class="fas fa-user-tie text-primary"></i> Penugasan GTK</h1></div><div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item">GTK</li><li class="breadcrumb-item active">Penugasan</li></ol></div></div>
@stop

@section('content')
<div class="gtk-assignment-page">
    @if(session('success'))<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button><i class="fas fa-check-circle mr-1"></i>{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button><i class="fas fa-exclamation-circle mr-1"></i>{{ session('error') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><strong>Penugasan belum dapat disimpan.</strong><ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <div class="card bg-gradient-primary text-white mb-3"><div class="card-body py-3"><div class="row align-items-center"><div class="col-lg-8"><h4 class="mb-1"><i class="fas fa-briefcase mr-2"></i>Penugasan Resmi & Ekuivalensi Jam</h4><p class="mb-0">Kelola Kepala Madrasah, Waka, Kepala Laboratorium, dan tugas tambahan guru berdasarkan periode serta SK.</p></div><div class="col-lg-4 mt-3 mt-lg-0 text-lg-right">@can('view-beban-kerja-gtk')<a href="{{ route('admin.penugasan-gtk.workload') }}" class="btn btn-light btn-sm"><i class="fas fa-balance-scale"></i> Beban Kerja</a>@endcan @can('manage-jenis-penugasan-gtk')<a href="{{ route('admin.penugasan-gtk.types') }}" class="btn btn-info btn-sm"><i class="fas fa-sliders-h"></i> Standar Penugasan</a>@endcan @can('create-penugasan-gtk')<button class="btn btn-success btn-sm" data-toggle="modal" data-target="#assignmentModal" id="addAssignment"><i class="fas fa-plus"></i> Penugasan Baru</button>@endcan</div></div></div></div>

    <div class="row">
        @foreach([['Penugasan Aktif',$stats['aktif'],'primary','briefcase'],['GTK Ditugaskan',$stats['gtk'],'info','users'],['Tugas Utama',$stats['utama'],'success','user-shield'],['SK Belum Lengkap',$stats['perlu_sk'],'warning','file-signature']] as [$label,$value,$color,$icon])
        <div class="col-6 col-xl-3"><div class="card assignment-stat"><div class="card-body"><div class="d-flex align-items-center"><span class="stat-icon bg-{{ $color }}"><i class="fas fa-{{ $icon }}"></i></span><div><small>{{ $label }}</small><h3>{{ number_format($value) }}</h3></div></div></div></div></div>
        @endforeach
    </div>

    <div class="card card-outline card-primary"><div class="card-header"><h3 class="card-title"><i class="fas fa-list mr-1"></i> Daftar Penugasan</h3><div class="card-tools text-muted small">Ekuivalensi tersimpan sebagai snapshot pada saat SK ditetapkan</div></div><div class="card-body">
        <form method="GET" class="assignment-filter mb-3"><div class="row"><div class="col-md-3"><label>Tahun Pelajaran</label><select name="tahun_pelajaran_id" class="form-control">@foreach($years as $item)<option value="{{ $item->id }}" @selected($year?->id===$item->id)>{{ $item->nama }}</option>@endforeach</select></div><div class="col-md-3"><label>Jenis Penugasan</label><select name="jenis_penugasan_id" class="form-control"><option value="">Semua jenis</option>@foreach($types as $type)<option value="{{ $type->id }}" @selected(request('jenis_penugasan_id')===$type->id)>{{ $type->nama }}</option>@endforeach</select></div><div class="col-md-2"><label>Status</label><select name="status" class="form-control"><option value="">Semua status</option>@foreach(['active'=>'Aktif','ended'=>'Selesai','draft'=>'Draft','cancelled'=>'Dibatalkan'] as $value=>$label)<option value="{{ $value }}" @selected(request('status')===$value)>{{ $label }}</option>@endforeach</select></div><div class="col-md-3"><label>Pencarian</label><input name="q" class="form-control" value="{{ request('q') }}" placeholder="Nama, NIP, unit, atau SK"></div><div class="col-md-1 d-flex align-items-end"><button class="btn btn-primary btn-block" title="Terapkan filter"><i class="fas fa-search"></i></button></div></div></form>

        <div class="table-responsive"><table class="table table-hover table-bordered"><thead><tr><th>GTK</th><th>Penugasan</th><th>Periode</th><th>SK</th><th class="text-center">JTM</th><th>Status</th><th class="text-right">Aksi</th></tr></thead><tbody>
        @forelse($assignments as $assignment)
            @php
                $badge = ['active'=>'success','ended'=>'secondary','draft'=>'warning','cancelled'=>'danger'][$assignment->status] ?? 'secondary';
                $assignmentRecord = [
                    'gtk_id' => $assignment->gtk_id,
                    'jenis_penugasan_id' => $assignment->jenis_penugasan_id,
                    'tahun_pelajaran_id' => $assignment->tahun_pelajaran_id,
                    'semester' => $assignment->semester,
                    'unit_nama' => $assignment->unit_nama,
                    'mulai_tugas' => $assignment->mulai_tugas->format('Y-m-d'),
                    'selesai_tugas' => $assignment->selesai_tugas?->format('Y-m-d'),
                    'nomor_sk' => $assignment->nomor_sk,
                    'tanggal_sk' => $assignment->tanggal_sk?->format('Y-m-d'),
                    'keterangan' => $assignment->keterangan,
                ];
            @endphp
            <tr><td><div class="d-flex align-items-center"><img src="{{ $assignment->gtk->foto_profile_url }}" class="assignment-photo mr-2" alt=""><div><strong>{{ $assignment->gtk->nama_lengkap }}</strong><small class="d-block text-muted">NIP {{ $assignment->gtk->nip ?: '-' }}</small></div></div></td><td><strong>{{ $assignment->jenis->nama }}</strong>@if($assignment->unit_nama)<small class="d-block text-muted"><i class="fas fa-map-marker-alt mr-1"></i>{{ $assignment->unit_nama }}</small>@endif<small class="d-block text-muted">{{ $assignment->jenis->dasar_hukum ?: 'Standar internal' }}</small></td><td>{{ $assignment->mulai_tugas->format('d/m/Y') }}<br><small class="text-muted">s.d. {{ $assignment->selesai_tugas?->format('d/m/Y') ?: 'berjalan' }} · {{ $assignment->semester ? 'Semester '.$assignment->semester : 'Sepanjang tahun' }}</small></td><td>{{ $assignment->nomor_sk ?: '-' }}<br><small class="text-muted">{{ $assignment->tanggal_sk?->format('d/m/Y') ?: 'Tanggal belum ada' }}</small>@if($assignment->file_sk)<a class="d-block small" href="{{ asset('storage/'.$assignment->file_sk) }}" target="_blank" rel="noopener"><i class="fas fa-paperclip"></i> Lihat dokumen</a>@endif</td><td class="text-center"><span class="badge badge-primary p-2">{{ $assignment->ekuivalensi_jtm }} JTM</span></td><td><span class="badge badge-{{ $badge }}">{{ ucfirst($assignment->status) }}</span>@if($assignment->legacy_tugas_tambahan_id)<small class="d-block text-muted">Migrasi data lama</small>@endif</td><td class="text-right text-nowrap">
                @if($assignment->status==='active')
                    @can('edit-penugasan-gtk')<button class="btn btn-sm btn-primary edit-assignment" data-toggle="modal" data-target="#assignmentModal" data-url="{{ route('admin.penugasan-gtk.update',$assignment) }}" data-record='@json($assignmentRecord)' title="Edit"><i class="fas fa-edit"></i></button>@endcan
                    @can('end-penugasan-gtk')<button class="btn btn-sm btn-warning end-assignment" data-toggle="modal" data-target="#endAssignmentModal" data-url="{{ route('admin.penugasan-gtk.end',$assignment) }}" data-name="{{ $assignment->jenis->nama }} · {{ $assignment->gtk->nama_lengkap }}" title="Akhiri"><i class="fas fa-stop-circle"></i></button>@endcan
                @elseif(!$assignment->legacy_tugas_tambahan_id)
                    @can('delete-penugasan-gtk')<form action="{{ route('admin.penugasan-gtk.destroy',$assignment) }}" method="POST" class="d-inline archive-form">@csrf @method('DELETE')<button class="btn btn-sm btn-danger" title="Arsipkan"><i class="fas fa-archive"></i></button></form>@endcan
                @endif
            </td></tr>
        @empty<tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2"></i>Belum ada penugasan pada filter ini.</td></tr>@endforelse
        </tbody></table></div>{{ $assignments->links() }}
    </div></div>
</div>

@canany(['create-penugasan-gtk','edit-penugasan-gtk'])
<div class="modal fade" id="assignmentModal" tabindex="-1"><div class="modal-dialog modal-lg"><form method="POST" action="{{ route('admin.penugasan-gtk.store') }}" enctype="multipart/form-data" class="modal-content confirm-assignment-form">@csrf <div id="assignmentMethod"></div><div class="modal-header"><h5 class="modal-title"><i class="fas fa-user-tie mr-1"></i><span id="assignmentModalTitle">Penugasan GTK Baru</span></h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body"><div class="row">
    <div class="col-md-6"><div class="form-group"><label>Guru/GTK <span class="text-danger">*</span></label><select name="gtk_id" class="form-control assignment-select" required><option value="">Pilih guru</option>@foreach($gtks as $gtk)<option value="{{ $gtk->id }}">{{ $gtk->nama_lengkap }} · NIP {{ $gtk->nip ?: '-' }}</option>@endforeach</select></div></div>
    <div class="col-md-6"><div class="form-group"><label>Jenis Penugasan <span class="text-danger">*</span></label><select name="jenis_penugasan_id" id="assignmentType" class="form-control assignment-select" required><option value="">Pilih jenis</option>@foreach($types->where('is_active',true) as $type)<option value="{{ $type->id }}" data-jtm="{{ $type->ekuivalensi_jtm }}" data-unit="{{ $type->jenis_unit }}" data-sk="{{ $type->wajib_sk ? 1 : 0 }}">{{ $type->nama }} · {{ $type->ekuivalensi_jtm }} JTM</option>@endforeach</select><small id="typeHelp" class="text-muted"></small></div></div>
    <div class="col-md-4"><div class="form-group"><label>Tahun Pelajaran <span class="text-danger">*</span></label><select name="tahun_pelajaran_id" class="form-control" required>@foreach($years as $item)<option value="{{ $item->id }}" @selected($year?->id===$item->id)>{{ $item->nama }}</option>@endforeach</select></div></div>
    <div class="col-md-4"><div class="form-group"><label>Semester</label><select name="semester" class="form-control"><option value="">Sepanjang tahun</option><option value="1">Ganjil</option><option value="2">Genap</option></select></div></div>
    <div class="col-md-4"><div class="form-group"><label id="unitLabel">Unit/Bidang</label><input name="unit_nama" class="form-control" placeholder="Contoh: Lab Komputer"></div></div>
    <div class="col-md-3"><div class="form-group"><label>Mulai Tugas <span class="text-danger">*</span></label><input type="date" name="mulai_tugas" class="form-control" value="{{ $year?->tanggal_mulai?->format('Y-m-d') ?: now()->format('Y-m-d') }}" required></div></div>
    <div class="col-md-3"><div class="form-group"><label>Selesai Tugas</label><input type="date" name="selesai_tugas" class="form-control" value="{{ $year?->tanggal_selesai?->format('Y-m-d') }}"></div></div>
    <div class="col-md-3"><div class="form-group"><label>Nomor SK <span class="sk-required text-danger">*</span></label><input name="nomor_sk" class="form-control"></div></div>
    <div class="col-md-3"><div class="form-group"><label>Tanggal SK <span class="sk-required text-danger">*</span></label><input type="date" name="tanggal_sk" class="form-control"></div></div>
    <div class="col-md-6"><div class="form-group"><label>Dokumen SK <span class="sk-required text-danger">*</span></label><div class="custom-file"><input type="file" name="file_sk" class="custom-file-input" accept=".pdf,.jpg,.jpeg,.png"><label class="custom-file-label">PDF/JPG/PNG maksimal 10 MB</label></div><small class="text-muted edit-file-help d-none">Kosongkan jika dokumen lama tetap digunakan.</small></div></div>
    <div class="col-md-6"><div class="form-group"><label>Keterangan</label><textarea name="keterangan" class="form-control" rows="2" placeholder="Keterangan penugasan bila diperlukan"></textarea></div></div>
</div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button><button class="btn btn-primary"><i class="fas fa-save mr-1"></i>Simpan Penugasan</button></div></form></div></div>
@endcanany

@can('end-penugasan-gtk')<div class="modal fade" id="endAssignmentModal" tabindex="-1"><div class="modal-dialog"><form method="POST" class="modal-content">@csrf<div class="modal-header"><h5 class="modal-title"><i class="fas fa-stop-circle text-warning mr-1"></i>Akhiri Penugasan</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body"><p id="endAssignmentName" class="font-weight-bold"></p><div class="form-group"><label>Tanggal Selesai <span class="text-danger">*</span></label><input type="date" name="selesai_tugas" class="form-control" value="{{ now()->format('Y-m-d') }}" required></div><div class="form-group"><label>Alasan <span class="text-danger">*</span></label><textarea name="alasan" class="form-control" rows="3" required placeholder="Contoh: berakhir masa SK atau pergantian pejabat"></textarea></div><div class="alert alert-info mb-0"><i class="fas fa-history mr-1"></i>Data tidak dihapus. Histori, SK, dan ekuivalensi periode lama tetap tersimpan.</div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button><button class="btn btn-warning"><i class="fas fa-check mr-1"></i>Akhiri Penugasan</button></div></form></div></div>@endcan
@stop

@section('css')
<style>.gtk-assignment-page .assignment-stat{border:1px solid #e2e8f0;box-shadow:0 4px 14px rgba(15,23,42,.06)}.gtk-assignment-page .assignment-stat .card-body{padding:.85rem}.gtk-assignment-page .assignment-stat h3{font-size:1.35rem;margin:0;font-weight:700}.gtk-assignment-page .assignment-stat small{color:#64748b;font-weight:600}.gtk-assignment-page .stat-icon{width:40px;height:40px;border-radius:.5rem;display:inline-flex;align-items:center;justify-content:center;color:#fff;margin-right:.7rem}.gtk-assignment-page .assignment-filter{padding:.8rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:.4rem}.gtk-assignment-page .assignment-filter label{font-size:.75rem;margin-bottom:.25rem}.gtk-assignment-page .assignment-photo{width:42px;height:52px;object-fit:cover;border-radius:.35rem;border:1px solid #dbe3ef}.gtk-assignment-page table{font-size:.82rem}.gtk-assignment-page textarea{resize:vertical}@media(max-width:767.98px){.gtk-assignment-page .card-tools{float:none!important;margin-top:.5rem}.gtk-assignment-page .assignment-filter .col-md-1{margin-top:.5rem}}</style>
@stop

@section('js')
<script>
$(function(){
    const modal=$('#assignmentModal'), form=modal.find('form'), createUrl=@json(route('admin.penugasan-gtk.store'));
    $('.assignment-select').select2({theme:'bootstrap4',width:'100%',dropdownParent:modal});
    function syncType(){const option=$('#assignmentType option:selected'), unit=option.data('unit'), required=String(option.data('sk'))==='1'; $('[name="unit_nama"]').prop('required',!!unit); $('#unitLabel').text(unit ? unit.charAt(0).toUpperCase()+unit.slice(1).replace('_',' ')+' *' : 'Unit/Bidang'); $('#typeHelp').text(option.val() ? 'Ekuivalensi standar '+option.data('jtm')+' JTM. Nilai disimpan sebagai histori saat penugasan dibuat.' : ''); $('.sk-required').toggleClass('d-none',!required); $('[name="nomor_sk"],[name="tanggal_sk"]').prop('required',required); if(!form.data('editing')) $('[name="file_sk"]').prop('required',required); }
    $('#assignmentType').on('change',syncType);
    $('#addAssignment').on('click',function(){form[0].reset();form.attr('action',createUrl).data('editing',false);$('#assignmentMethod').empty();$('#assignmentModalTitle').text('Penugasan GTK Baru');$('.assignment-select').val(null).trigger('change');$('[name="tahun_pelajaran_id"]').val(@json($year?->id));$('[name="mulai_tugas"]').val(@json($year?->tanggal_mulai?->format('Y-m-d') ?: now()->format('Y-m-d')));$('[name="selesai_tugas"]').val(@json($year?->tanggal_selesai?->format('Y-m-d')));$('.edit-file-help').addClass('d-none');syncType();});
    $('.edit-assignment').on('click',function(){const record=$(this).data('record');form[0].reset();form.attr('action',$(this).data('url')).data('editing',true);$('#assignmentMethod').html('<input type="hidden" name="_method" value="PUT">');$('#assignmentModalTitle').text('Perbarui Penugasan GTK');Object.keys(record).forEach(key=>{const input=form.find('[name="'+key+'"]');input.val(record[key]??'');});$('.assignment-select').trigger('change');$('[name="file_sk"]').prop('required',false);$('.edit-file-help').removeClass('d-none');syncType();});
    $('.end-assignment').on('click',function(){$('#endAssignmentModal form').attr('action',$(this).data('url'));$('#endAssignmentName').text($(this).data('name'));});
    $('.custom-file-input').on('change',function(){$(this).next('.custom-file-label').text(this.files[0]?.name||'Pilih dokumen');});
    $('.archive-form').on('submit',function(e){if(!confirm('Arsipkan histori penugasan ini?'))e.preventDefault();});
    $('.confirm-assignment-form').on('submit',function(e){if($(this).data('confirmed'))return;e.preventDefault();const target=this;if(window.Swal){Swal.fire({icon:'question',title:'Simpan penugasan?',text:'Sistem akan mencatat SK dan ekuivalensi jam untuk periode yang dipilih.',showCancelButton:true,confirmButtonText:'Ya, simpan',cancelButtonText:'Periksa lagi'}).then(r=>{if(r.isConfirmed){$(target).data('confirmed',true);target.submit();}});}else if(confirm('Simpan penugasan dan ekuivalensi jam ini?')){target.submit();}});
    @if($errors->any()) $('#addAssignment').trigger('click'); @endif
});
</script>
@stop

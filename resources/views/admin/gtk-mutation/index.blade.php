@extends('adminlte::page')
@section('title', 'Mutasi & Status GTK')
@section('plugins.Select2', true)

@section('content_header')
<div class="row mb-2"><div class="col-sm-6"><h1><i class="fas fa-user-clock text-primary"></i> Mutasi & Status GTK</h1></div><div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item">GTK</li><li class="breadcrumb-item active">Mutasi & Status</li></ol></div></div>
@stop

@section('content')
<div class="gtk-mutation-page">
@if(session('success'))<div class="alert alert-success alert-dismissible"><button class="close" data-dismiss="alert">&times;</button><i class="fas fa-check-circle mr-1"></i>{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><strong>Perubahan belum dapat disimpan.</strong><ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="card bg-gradient-primary text-white mb-3"><div class="card-body py-3"><div class="row align-items-center"><div class="col-lg-7"><h4 class="mb-1"><i class="fas fa-exchange-alt mr-2"></i>Riwayat Keaktifan GTK</h4><p class="mb-0">Bedakan GTK baru, mutasi masuk, aktif kembali, serta mutasi/nonaktif keluar tanpa menghapus histori.</p></div><div class="col-lg-5 mt-3 mt-lg-0 text-lg-right">@can('manage-status-gtk') @can('create-gtk')<a href="{{ route('admin.mutasi-gtk.incoming.create') }}" class="btn btn-light mr-1"><i class="fas fa-people-arrows mr-1"></i> Mutasi Masuk</a>@endcan <button class="btn btn-success" data-toggle="modal" data-target="#statusModal"><i class="fas fa-user-edit mr-1"></i> Ubah Status</button>@endcan</div></div></div></div>

<div class="row">
@foreach([['GTK Aktif',$stats['aktif'],'success','user-check'],['GTK Nonaktif',$stats['nonaktif'],'secondary','user-slash'],['GTK Baru',$stats['baru'],'primary','user-plus'],['Mutasi Masuk',$stats['masuk'],'info','people-arrows']] as [$label,$value,$color,$icon])
<div class="col-6 col-xl-3"><div class="card mutation-stat"><div class="card-body"><span class="mutation-icon bg-{{ $color }}"><i class="fas fa-{{ $icon }}"></i></span><div><small>{{ $label }}</small><h3>{{ number_format($value) }}</h3></div></div></div></div>
@endforeach
</div>

<div class="card card-outline card-primary"><div class="card-header"><h3 class="card-title"><i class="fas fa-history mr-1"></i> Riwayat Perubahan</h3><div class="card-tools text-muted small">Data tidak dapat diedit untuk menjaga jejak audit</div></div><div class="card-body">
<form method="GET" class="mutation-filter mb-3"><div class="row"><div class="col-md-4"><label>Pencarian GTK</label><input name="q" class="form-control" value="{{ request('q') }}" placeholder="Nama, NIP, atau NIK"></div><div class="col-md-3"><label>Status Baru</label><select name="status" class="form-control"><option value="">Semua status</option><option value="aktif" @selected(request('status')==='aktif')>Aktif</option><option value="nonaktif" @selected(request('status')==='nonaktif')>Nonaktif</option></select></div><div class="col-md-3"><label>Alasan</label><select name="alasan" class="form-control"><option value="">Semua alasan</option>@foreach($reasonLabels as $key=>$label)<option value="{{ $key }}" @selected(request('alasan')===$key)>{{ $label }}</option>@endforeach</select></div><div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary mr-1"><i class="fas fa-search"></i></button><a href="{{ route('admin.mutasi-gtk.index') }}" class="btn btn-secondary"><i class="fas fa-redo"></i></a></div></div></form>
<div class="table-responsive"><table class="table table-hover table-bordered"><thead><tr><th>Tanggal</th><th>GTK</th><th>Perubahan</th><th>Alasan/Instansi</th><th>Dampak Operasional</th><th>Dicatat Oleh</th></tr></thead><tbody>
@forelse($history as $item)<tr><td class="text-nowrap"><strong>{{ $item->tanggal_efektif->format('d/m/Y') }}</strong><small class="d-block text-muted">{{ $item->created_at->format('H:i') }} WIB</small></td><td><div class="d-flex align-items-center"><img src="{{ $item->gtk->foto_profile_url }}" class="mutation-photo mr-2" alt=""><div><strong>{{ $item->gtk->nama_lengkap }}</strong><small class="d-block text-muted">{{ $item->gtk->nip ? 'NIP '.$item->gtk->nip : ($item->gtk->nik ? 'NIK '.$item->gtk->nik : '-') }}</small></div></div></td><td><span class="badge badge-{{ $item->status_baru ? 'success' : 'secondary' }} p-2">{{ is_null($item->status_sebelumnya) ? 'Belum tercatat' : ($item->status_sebelumnya ? 'Aktif' : 'Nonaktif') }} <i class="fas fa-arrow-right mx-1"></i> {{ $item->status_baru ? 'Aktif' : 'Nonaktif' }}</span></td><td><strong>{{ $reasonLabels[$item->alasan] ?? ucfirst($item->alasan) }}</strong>@if($item->instansi_asal_tujuan)<small class="d-block text-primary"><i class="fas fa-school mr-1"></i>{{ $item->instansi_asal_tujuan }}</small>@endif @if($item->keterangan)<small class="d-block text-muted">{{ $item->keterangan }}</small>@endif</td><td>@php($impact=collect($item->dampak_operasional)->filter()) @forelse($impact as $key=>$count)<span class="badge badge-light border mr-1 mb-1">{{ $count }} {{ str_replace('_',' ',$key) }}</span>@empty<span class="text-muted">Tidak ada tugas aktif</span>@endforelse</td><td>{{ $item->creator?->name ?: 'Sistem' }}</td></tr>
@empty<tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-history fa-2x d-block mb-2"></i>Belum ada riwayat perubahan status.</td></tr>@endforelse
</tbody></table></div>{{ $history->links() }}
</div></div>
</div>

@can('manage-status-gtk')
<div class="modal fade" id="statusModal" tabindex="-1"><div class="modal-dialog modal-lg"><form method="POST" action="{{ route('admin.mutasi-gtk.store') }}" class="modal-content" id="gtkStatusForm">@csrf
<div class="modal-header"><h5 class="modal-title"><i class="fas fa-user-edit mr-1"></i> Catat Perubahan Status GTK</h5><button class="close" type="button" data-dismiss="modal">&times;</button></div>
<div class="modal-body"><div class="alert alert-info py-2"><i class="fas fa-shield-alt mr-1"></i>Menonaktifkan GTK akan memblokir akun, mengakhiri penugasan aktif, dan melepas wali kelas. Jadwal serta histori lama tetap tersimpan.</div><div class="row">
<div class="col-12"><div class="form-group"><label>GTK <span class="text-danger">*</span></label><select name="gtk_id" id="mutationGtk" class="form-control" required><option value="">Cari nama, NIP, atau NIK</option>@foreach($gtks as $gtk)<option value="{{ $gtk->id }}" data-active="{{ $gtk->status_aktif ? 1 : 0 }}" data-photo="{{ $gtk->foto_profile_url }}" data-meta="{{ $gtk->nip ?: $gtk->nik }} · {{ $gtk->jenis_ptk ?: 'GTK' }}" @selected(old('gtk_id',$selectedGtkId)===$gtk->id)>{{ $gtk->nama_lengkap }} · {{ $gtk->nip ?: $gtk->nik }}</option>@endforeach</select><small id="currentGtkStatus" class="text-muted"></small></div></div>
<div class="col-md-6"><div class="form-group"><label>Status Baru <span class="text-danger">*</span></label><select name="status_baru" id="newGtkStatus" class="form-control" required><option value="0">Nonaktif</option><option value="1">Aktif</option></select></div></div>
<div class="col-md-6"><div class="form-group"><label>Tanggal Efektif <span class="text-danger">*</span></label><input type="date" name="tanggal_efektif" class="form-control" max="{{ today()->toDateString() }}" value="{{ old('tanggal_efektif',today()->toDateString()) }}" required></div></div>
<div class="col-md-6"><div class="form-group"><label>Alasan <span class="text-danger">*</span></label><select name="alasan" id="mutationReason" class="form-control" required></select></div></div>
<div class="col-md-6"><div class="form-group"><label id="institutionLabel">Instansi Asal/Tujuan</label><input name="instansi_asal_tujuan" id="mutationInstitution" class="form-control" maxlength="255" placeholder="Nama instansi"></div></div>
<div class="col-12"><div class="form-group mb-0"><label>Keterangan</label><textarea name="keterangan" class="form-control" rows="3" maxlength="2000" placeholder="Keterangan pendukung bila diperlukan">{{ old('keterangan') }}</textarea></div></div>
</div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button><button class="btn btn-primary"><i class="fas fa-save mr-1"></i>Simpan Perubahan</button></div>
</form></div></div>
@endcan
@stop

@section('css')
<style>
.gtk-mutation-page .mutation-stat{border:1px solid #e2e8f0;box-shadow:0 4px 14px rgba(15,23,42,.06)}.gtk-mutation-page .mutation-stat .card-body{display:flex;align-items:center;padding:.85rem}.gtk-mutation-page .mutation-stat small{color:#64748b;font-weight:700}.gtk-mutation-page .mutation-stat h3{margin:0;font-size:1.4rem;font-weight:800}.gtk-mutation-page .mutation-icon{display:grid;place-items:center;width:44px;height:44px;margin-right:.75rem;border-radius:.6rem;color:#fff}.gtk-mutation-page .mutation-filter{padding:.85rem;border:1px solid #e2e8f0;border-radius:.45rem;background:#f8fafc}.gtk-mutation-page .mutation-filter label{font-size:.75rem}.gtk-mutation-page .mutation-photo{width:38px;height:48px;border-radius:.4rem;object-fit:cover}.gtk-mutation-page table{min-width:1000px;font-size:.82rem}.mutation-gtk-option{display:flex;align-items:center;gap:.65rem}.mutation-gtk-option img{width:34px;height:42px;border-radius:.35rem;object-fit:cover}.mutation-gtk-option strong,.mutation-gtk-option small{display:block}.mutation-gtk-option small{color:#64748b}
@media(max-width:767.98px){.gtk-mutation-page .mutation-filter [class*="col-"]+[class*="col-"]{margin-top:.5rem}}
</style>
@stop

@section('js')
<script>
$(function(){
const modal=$('#statusModal'), gtk=$('#mutationGtk'), status=$('#newGtkStatus'), reason=$('#mutationReason');
const labels=@json($reasonLabels), activeReasons=['mutasi_masuk','aktif_kembali','lainnya'], inactiveReasons=['pensiun','meninggal','mengundurkan_diri','mutasi_keluar','pemutusan_hubungan_kerja','kontrak_selesai','lainnya'];
function gtkTemplate(item){if(!item.id)return item.text;const data=item.element.dataset;return $('<div class="mutation-gtk-option">').append($('<img>',{src:data.photo,alt:''}),$('<div>').append($('<strong>').text(item.text.split(' · ')[0]),$('<small>').text(data.meta)));}
gtk.select2({theme:'bootstrap4',width:'100%',dropdownParent:modal,placeholder:'Cari nama, NIP, atau NIK',templateResult:gtkTemplate});
function syncGtk(){const option=gtk.find(':selected'),isActive=option.data('active')===1;status.val(isActive?'0':'1');$('#currentGtkStatus').html(option.val()?'<i class="fas fa-info-circle mr-1"></i>Status saat ini: <strong>'+(isActive?'Aktif':'Nonaktif')+'</strong>':'');syncReasons();}
function syncReasons(){const items=status.val()==='1'?activeReasons:inactiveReasons;reason.empty().append('<option value="">Pilih alasan</option>');items.forEach(key=>reason.append(new Option(labels[key],key)));syncInstitution();}
function syncInstitution(){const mutation=['mutasi_masuk','mutasi_keluar'].includes(reason.val());$('#mutationInstitution').prop('required',mutation);$('#institutionLabel').html((reason.val()==='mutasi_masuk'?'Instansi Asal':reason.val()==='mutasi_keluar'?'Instansi Tujuan':'Instansi Asal/Tujuan')+(mutation?' <span class="text-danger">*</span>':''));}
gtk.on('change',syncGtk);status.on('change',syncReasons);reason.on('change',syncInstitution);syncGtk();
$('#gtkStatusForm').on('submit',function(e){if(this.dataset.confirmed)return;e.preventDefault();const form=this,name=gtk.find(':selected').text().split(' · ')[0],target=status.val()==='1'?'mengaktifkan':'menonaktifkan';Swal.fire({icon:'warning',title:'Konfirmasi perubahan status',html:'Anda akan <strong>'+target+'</strong> '+$('<div>').text(name).html()+'. Perubahan operasional dilakukan langsung.',showCancelButton:true,confirmButtonText:'Ya, lanjutkan',cancelButtonText:'Periksa lagi'}).then(result=>{if(result.isConfirmed){form.dataset.confirmed='1';form.submit();}});});
@if($errors->any() || $selectedGtkId) modal.modal('show'); @endif
});
</script>
@stop

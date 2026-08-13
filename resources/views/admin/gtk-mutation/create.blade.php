@extends('adminlte::page')
@section('title', 'Mutasi Masuk GTK')

@section('content_header')
<div class="row mb-2"><div class="col-sm-6"><h1><i class="fas fa-user-plus text-primary"></i> Mutasi Masuk GTK</h1></div><div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('admin.mutasi-gtk.index') }}">Mutasi GTK</a></li><li class="breadcrumb-item active">Mutasi Masuk</li></ol></div></div>
@stop

@section('content')
<div class="gtk-incoming-page">
@if($errors->any())<div class="alert alert-danger"><strong>Data belum dapat disimpan.</strong><ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="card bg-gradient-primary text-white mb-3"><div class="card-body py-3"><h4 class="mb-1"><i class="fas fa-people-arrows mr-2"></i>Registrasi GTK dari Instansi Lain</h4><p class="mb-0">Sistem membuat profil dan akun GTK sekaligus serta mencatat instansi asal sebagai histori mutasi masuk.</p></div></div>
<form method="POST" action="{{ route('admin.mutasi-gtk.incoming.store') }}" id="incomingGtkForm">@csrf
<div class="card card-outline card-primary"><div class="card-header"><h3 class="card-title"><i class="fas fa-id-card mr-1"></i> Identitas & Asal GTK</h3></div><div class="card-body"><div class="row">
<div class="col-md-8"><div class="form-group"><label>Nama Lengkap <span class="text-danger">*</span></label><input name="nama_lengkap" class="form-control" value="{{ old('nama_lengkap') }}" required maxlength="255"></div></div>
<div class="col-md-4"><div class="form-group"><label>NIK <span class="text-danger">*</span></label><input name="nik" class="form-control numeric-id" value="{{ old('nik') }}" required maxlength="16" minlength="16"><small class="text-muted">Menjadi username dan password awal.</small></div></div>
<div class="col-md-4"><div class="form-group"><label>NIP</label><input name="nip" class="form-control numeric-id" value="{{ old('nip') }}" maxlength="20"></div></div>
<div class="col-md-4"><div class="form-group"><label>Jenis Kelamin <span class="text-danger">*</span></label><select name="jenis_kelamin" class="form-control" required><option value="">Pilih</option><option value="L" @selected(old('jenis_kelamin')==='L')>Laki-laki</option><option value="P" @selected(old('jenis_kelamin')==='P')>Perempuan</option></select></div></div>
<div class="col-md-4"><div class="form-group"><label>Tanggal Efektif/TMT <span class="text-danger">*</span></label><input type="date" name="tanggal_efektif" class="form-control" value="{{ old('tanggal_efektif',today()->toDateString()) }}" max="{{ today()->toDateString() }}" required></div></div>
<div class="col-md-6"><div class="form-group"><label>Kategori PTK <span class="text-danger">*</span></label><select name="kategori_ptk" id="incomingCategory" class="form-control" required><option value="">Pilih kategori</option><option value="Pendidik" @selected(old('kategori_ptk')==='Pendidik')>Pendidik</option><option value="Tenaga Kependidikan" @selected(old('kategori_ptk')==='Tenaga Kependidikan')>Tenaga Kependidikan</option></select></div></div>
<div class="col-md-6"><div class="form-group"><label>Jenis PTK <span class="text-danger">*</span></label><select name="jenis_ptk" id="incomingType" class="form-control" data-old="{{ old('jenis_ptk') }}" required></select></div></div>
<div class="col-md-8"><div class="form-group"><label>Instansi Asal <span class="text-danger">*</span></label><input name="instansi_asal" class="form-control" value="{{ old('instansi_asal') }}" required maxlength="255" placeholder="Nama sekolah/madrasah/instansi sebelumnya"></div></div>
<div class="col-md-4"><div class="form-group"><label>Status Kepegawaian</label><select name="status_kepegawaian" class="form-control"><option value="">Belum ditentukan</option>@foreach(['PNS','PPPK','GTY','PTY','Honorer'] as $status)<option value="{{ $status }}" @selected(old('status_kepegawaian')===$status)>{{ $status }}</option>@endforeach</select></div></div>
<div class="col-12"><div class="form-group mb-0"><label>Keterangan Mutasi</label><textarea name="keterangan" class="form-control" rows="3" maxlength="2000" placeholder="Nomor surat atau informasi pendukung bila diperlukan">{{ old('keterangan') }}</textarea></div></div>
</div></div><div class="card-footer text-right"><a href="{{ route('admin.mutasi-gtk.index') }}" class="btn btn-secondary mr-1">Batal</a><button class="btn btn-primary"><i class="fas fa-save mr-1"></i>Simpan Mutasi Masuk</button></div></div>
</form></div>
@stop

@section('css')<style>.gtk-incoming-page{max-width:1180px;margin:auto}.gtk-incoming-page textarea{resize:vertical}</style>@stop
@section('js')
<script>$(function(){const options={'Pendidik':['Guru Mapel','Guru BK'],'Tenaga Kependidikan':['Kepala TU','Staff TU','Bendahara','Laboran','Pustakawan','Cleaning Service','Satpam','Lainnya']};function sync(){const select=$('#incomingType'),old=select.data('old');select.empty().append('<option value="">Pilih jenis PTK</option>');(options[$('#incomingCategory').val()]||[]).forEach(value=>select.append(new Option(value,value,false,value===old)));}$('#incomingCategory').on('change',sync);sync();$('.numeric-id').on('input',function(){this.value=this.value.replace(/\D/g,'');});$('#incomingGtkForm').on('submit',function(e){if(this.dataset.confirmed)return;e.preventDefault();const form=this;Swal.fire({icon:'question',title:'Simpan mutasi masuk GTK?',text:'Profil, akun, dan histori mutasi masuk akan dibuat sekaligus.',showCancelButton:true,confirmButtonText:'Ya, simpan',cancelButtonText:'Periksa lagi'}).then(result=>{if(result.isConfirmed){form.dataset.confirmed='1';form.submit();}});});});</script>
@stop

@php($current=$unit)
<div class="row">
<div class="col-md-4"><div class="form-group"><label>Kode</label><input required class="form-control" name="kode" value="{{ old('kode',$current?->kode) }}"></div></div>
<div class="col-md-8"><div class="form-group"><label>Nama Unit</label><input required class="form-control" name="nama" value="{{ old('nama',$current?->nama) }}"></div></div>
<div class="col-md-4"><div class="form-group"><label>Jenis</label><select name="jenis" class="form-control">@foreach(['putra'=>'Putra','putri'=>'Putri','campuran'=>'Campuran'] as $v=>$l)<option value="{{ $v }}" @selected(old('jenis',$current?->jenis??'campuran')===$v)>{{ $l }}</option>@endforeach</select></div></div>
<div class="col-md-8"><div class="form-group"><label>Kepala Asrama (GTK)</label><select name="kepala_gtk_id" class="form-control"><option value="">Belum ditetapkan</option>@foreach($gtks as $gtk)<option value="{{ $gtk->id }}" @selected(old('kepala_gtk_id',$current?->kepala_gtk_id)===$gtk->id)>{{ $gtk->nama_lengkap }}{{ $gtk->nip?' · '.$gtk->nip:'' }}</option>@endforeach</select></div></div>
<div class="col-md-8"><div class="form-group"><label>Alamat</label><textarea name="alamat" class="form-control">{{ old('alamat',$current?->alamat) }}</textarea></div></div>
<div class="col-md-4"><div class="form-group"><label>Telepon</label><input name="telepon" class="form-control" value="{{ old('telepon',$current?->telepon) }}"></div></div>
<div class="col-12"><div class="form-group"><label>Deskripsi</label><textarea name="deskripsi" class="form-control">{{ old('deskripsi',$current?->deskripsi) }}</textarea></div></div>
<div class="col-12"><div class="custom-control custom-switch"><input type="checkbox" class="custom-control-input" id="unitActive{{ $current?->id??'new' }}" name="is_active" value="1" @checked(old('is_active',$current?->is_active??true))><label class="custom-control-label" for="unitActive{{ $current?->id??'new' }}">Unit aktif</label></div></div>
</div>

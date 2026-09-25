@extends('adminlte::page')

@section('title', 'Pengaturan Penomoran Surat')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-hashtag text-primary mr-2"></i>Penomoran Surat</h1>
            <p class="text-muted mb-0">Kelola format nomor surat PP dan KP secara terpusat.</p>
        </div>
        <a href="{{ route('admin.settings.edit') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i>Pengaturan Utama</a>
    </div>
@stop

@section('content')
    @if(session('success'))<div class="alert alert-success"><i class="fas fa-check-circle mr-1"></i>{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body d-flex flex-wrap align-items-center justify-content-between">
            <div><div class="text-uppercase text-muted small font-weight-bold">Tahun penomoran aktif</div><div class="h3 mb-0">{{ $tahun }}</div><small class="text-muted">Mengikuti tahun mulai dari Tahun Pelajaran aktif.</small></div>
            <div class="text-md-right mt-3 mt-md-0"><div class="text-muted small">Contoh nomor berikutnya</div><div id="numberPreview" class="h5 text-primary mb-0">{{ $preview }}</div><small class="text-muted">Nomor urut PP dan KP menggunakan satu urutan per tahun.</small></div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.settings.surat-nomor.update') }}">
        @csrf @method('PUT')
        <div class="card border-warning shadow-sm mb-3">
            <div class="card-body"><div class="form-row align-items-end"><div class="form-group col-md-4 mb-md-0"><label class="font-weight-bold">Nomor berikutnya tahun {{ $tahun }}</label><input type="number" min="{{ $nomorBerikutnya }}" max="999999" class="form-control" name="nomor_berikutnya" value="{{ $nomorBerikutnya }}" required></div><div class="col-md-8"><small class="text-muted"><i class="fas fa-shield-alt mr-1 text-warning"></i>Digunakan untuk menyesuaikan arsip lama. Nilai tidak boleh diturunkan setelah nomor berjalan.</small></div></div></div>
        </div>
        @foreach($configs as $config)
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white d-flex justify-content-between align-items-center"><strong><span class="badge badge-primary mr-2">{{ $config->kode }}</span>{{ $config->nama }}</strong><span class="text-muted small">Token: {prefix}, {nomor}, {kode_satuan_kerja}, {kode_klasifikasi}, {bulan}, {bulan_romawi}, {tahun}</span></div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-4"><label>Nama jenis surat</label><input class="form-control" name="configs[{{ $config->id }}][nama]" value="{{ $config->nama }}"></div>
                        <div class="form-group col-md-2"><label>Kode awal</label><input class="form-control" name="configs[{{ $config->id }}][prefix]" value="{{ $config->prefix }}"></div>
                        <div class="form-group col-md-3"><label>Kode satuan kerja</label><input class="form-control" name="configs[{{ $config->id }}][kode_satuan_kerja]" value="{{ $config->kode_satuan_kerja }}"></div>
                        <div class="form-group col-md-3"><label>Panjang nomor urut</label><input type="number" min="1" max="6" class="form-control" name="configs[{{ $config->id }}][panjang_nomor]" value="{{ $config->panjang_nomor }}"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4"><label>Kode klasifikasi</label><input class="form-control" name="configs[{{ $config->id }}][kode_klasifikasi]" value="{{ $config->kode_klasifikasi }}"></div>
                        <div class="form-group col-md-8"><label>Format nomor</label><input class="form-control" name="configs[{{ $config->id }}][format_nomor]" value="{{ $config->format_nomor }}"><small class="form-text text-muted">Gunakan token yang tampil pada header kartu.</small></div>
                    </div>
                    <div class="custom-control custom-switch"><input type="hidden" name="configs[{{ $config->id }}][is_active]" value="0"><input type="checkbox" class="custom-control-input" id="active-{{ $config->id }}" name="configs[{{ $config->id }}][is_active]" value="1" @checked($config->is_active)><label class="custom-control-label" for="active-{{ $config->id }}">Aktif digunakan untuk pembuatan nomor</label></div>
                </div>
            </div>
        @endforeach
        <div class="d-flex justify-content-end"><button class="btn btn-primary"><i class="fas fa-save mr-1"></i>Simpan Pengaturan</button></div>
    </form>
@stop

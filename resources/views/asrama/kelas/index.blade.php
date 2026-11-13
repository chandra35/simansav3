@extends('adminlte::page')
@section('title', 'Rombel Asrama')
@section('plugins.Select2', true)
@section('content')
@include('asrama._alerts')
@php
    $heroTitle='Rombel Asrama';
    $heroDescription='Kelola Rombel Asrama otomatis (dari SIMANSA Kampus 2) dan Rombel Mandiri/Custom (misal: santri SmartQ/campuran). Atur santri, pembina halaqoh/pengasuh, dan pengampu ujian.';
    $heroAction='<button type="button" class="btn btn-light font-weight-bold" data-toggle="modal" data-target="#modalCreateRombel"><i class="fas fa-plus-circle mr-1 text-primary"></i> Buat Rombel Baru</button>';
@endphp
@include('asrama._hero')

@if(isset($unassignedSantriCount) && $unassignedSantriCount > 0)
<div class="alert alert-warning mb-3 d-flex flex-wrap align-items-center justify-content-between shadow-sm">
    <div>
        <i class="fas fa-exclamation-triangle fa-lg mr-2 text-warning"></i>
        <span>Terdapat <strong>{{ $unassignedSantriCount }} santri asrama aktif</strong> yang belum dimasukkan ke rombel asrama manapun di tahun pelajaran ini.</span>
    </div>
    <button type="button" class="btn btn-sm btn-dark mt-2 mt-sm-0" data-toggle="modal" data-target="#modalCreateRombel">
        <i class="fas fa-plus mr-1"></i> Buat Rombel Mandiri
    </button>
</div>
@endif

<div class="asrama-panel"><div class="asrama-panel__body asrama-form"><form method="get" class="row align-items-end" data-asrama-loading data-loading-title="Memuat rombel Asrama">
<div class="col-md-5"><label>Tahun Pelajaran</label><select name="tahun_pelajaran_id" class="form-control asrama-select">@foreach($years as $year)<option value="{{ $year->id }}" @selected($selectedYear===$year->id)>{{ $year->nama }}{{ $year->is_active?' · Aktif':'' }}</option>@endforeach</select></div><div class="col-md-2 mt-3 mt-md-0"><button class="btn btn-info btn-block"><i class="fas fa-filter mr-1"></i> Tampilkan</button></div>
</form></div></div>
<div class="row">
@forelse($records as $item)
<div class="col-xl-4 col-md-6 mb-3"><div class="asrama-panel h-100 mb-0"><div class="asrama-panel__body">
<div class="d-flex justify-content-between align-items-start"><div><span class="asrama-pill mb-2"><i class="fas fa-link"></i> {{ $item->tahunPelajaran->nama }}</span> <span class="asrama-pill mb-2"><i class="fas fa-layer-group"></i> Tingkat {{ $item->tingkat }}</span> <span class="asrama-pill mb-2"><i class="fas fa-venus-mars"></i> {{ ucfirst($item->jenis) }}</span> @if($item->is_tahfidz)<span class="asrama-pill mb-2 text-success font-weight-bold"><i class="fas fa-quran"></i> Tahfizh</span>@endif @if($item->kelas_id)<span class="asrama-pill mb-2 bg-info text-white"><i class="fas fa-sync-alt"></i> Kampus 2</span>@else<span class="asrama-pill mb-2 text-white" style="background:#6f42c1;"><i class="fas fa-feather-alt"></i> Rombel Mandiri</span>@endif<h4 class="mb-0">{{ $item->kelasReguler?->nama_kelas ?? $item->nama_kelas }}</h4><div class="asrama-arab text-muted">{{ $item->nama_arab }}</div></div><span class="asrama-badge {{ $item->is_active?'asrama-badge--active':'asrama-badge--muted' }}">{{ $item->is_active?'Aktif':'Nonaktif' }}</span></div>
<hr><div class="row text-center"><div class="col-4"><small>Santri</small><strong class="d-block">{{ $item->anggota_aktif_count }}</strong></div><div class="col-4"><small>Pengasuh</small><strong class="d-block">{{ $item->pengasuhRombel->count() }}</strong></div><div class="col-4"><small>Pengampu</small><strong class="d-block">{{ $item->pengampu_count }}</strong></div></div>
<div class="mt-2 text-center"><small class="text-muted"><i class="fas fa-star text-warning"></i> Ketua: <strong>{{ $item->ketua?->santri?->siswa?->nama_lengkap ?? 'Belum ditetapkan' }}</strong></small></div>
<div class="mt-3">@forelse($item->pengasuhRombel as $caregiver)<span class="asrama-pill mb-1"><i class="fas fa-user-tie"></i> {{ $caregiver->pengasuh->gtk->nama_lengkap }}{{ $caregiver->is_primary?' · Utama':'' }}</span>@empty<small class="text-warning"><i class="fas fa-exclamation-circle"></i> Pengasuh belum ditetapkan</small>@endforelse</div>
@if($item->anggota_aktif_count===0)<small class="text-danger d-block mt-2"><i class="fas fa-exclamation-triangle"></i> Belum ada santri — buka rombel lalu klik "Sinkronkan Santri".</small>@endif
@if($item->pengampu_count===0)<small class="text-warning d-block mt-1"><i class="fas fa-book"></i> Pengampu mapel belum ditugaskan — nilai belum bisa diinput.</small>@endif
<a href="{{ route('asrama.kelas.show',$item) }}" class="btn btn-outline-info btn-block mt-3">Kelola Rombel <i class="fas fa-arrow-right ml-1"></i></a>
</div></div></div>
@empty<div class="col-12"><div class="asrama-panel"><div class="asrama-empty"><i class="fas fa-school"></i>Belum ada rombel asrama di tahun pelajaran ini. Anda dapat membuat Rombel Mandiri dengan tombol "Buat Rombel Baru" di atas, atau mencentang "Rombel Asrama (Kampus 2)" pada rombel di Manajemen Data → Kelas.</div></div></div>@endforelse
</div>

<div class="modal fade" id="modalCreateRombel" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <form method="POST" action="{{ route('asrama.kelas.store') }}" class="modal-content asrama-form" data-asrama-loading data-loading-title="Membuat Rombel Asrama">
            @csrf
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-plus-circle mr-2"></i>Buat Rombel Asrama Baru</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="callout callout-info py-2 mb-3">
                    <small><i class="fas fa-info-circle mr-1"></i> <strong>Rombel Mandiri / Custom</strong> digunakan untuk mengelompokkan santri yang berasal dari rombel reguler campuran (misal: santri SmartQ di kelas XII-A1, XI-A1, X.1) atau halaqoh khusus asrama. Data nilai ujian dan rapor asrama akan dihitung berdasarkan rombel asrama ini.</small>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold">Tahun Pelajaran <span class="text-danger">*</span></label>
                        <select name="tahun_pelajaran_id" class="form-control asrama-select" required>
                            @foreach($years as $year)
                                <option value="{{ $year->id }}" @selected($selectedYear === $year->id)>{{ $year->nama }}{{ $year->is_active ? ' (Aktif)' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold">Tingkat Kelas <span class="text-danger">*</span></label>
                        <select name="tingkat" class="form-control asrama-select" required>
                            <option value="10">Tingkat 10 (Fase E)</option>
                            <option value="11">Tingkat 11 (Fase F - XI)</option>
                            <option value="12">Tingkat 12 (Fase F - XII)</option>
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold">Nama Rombel Asrama <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kelas" class="form-control" placeholder="Contoh: X SmartQ Asrama / Halaqoh A" required maxlength="100">
                        <small class="text-muted">Nama rombel yang tampil di daftar rombel &amp; rapor santri.</small>
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold">Nama Arab (Opsional)</label>
                        <input type="text" name="nama_arab" dir="rtl" class="form-control asrama-arab" placeholder="Contoh: الفصل العاشر">
                        <small class="text-muted">Tampil pada rapor format bahasa Arab.</small>
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="font-weight-bold">Kelompok Gender <span class="text-danger">*</span></label>
                        <select name="jenis" class="form-control asrama-select" required>
                            <option value="putra">Putra</option>
                            <option value="putri">Putri</option>
                            <option value="campuran">Campuran</option>
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="font-weight-bold">Kapasitas Maksimal</label>
                        <input type="number" name="kapasitas" class="form-control" value="40" min="1" max="100">
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="font-weight-bold">Ruang / Lokasi Belajar</label>
                        <input type="text" name="ruang" class="form-control" placeholder="Contoh: Aula Asrama Lt. 2">
                    </div>
                    <div class="col-12 form-group mb-2">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="modal_is_tahfidz" name="is_tahfidz" value="1">
                            <label class="custom-control-label font-weight-bold" for="modal_is_tahfidz">Program Khusus Tahfizh</label>
                        </div>
                        <small class="text-muted">Aktifkan jika rombel ini menggunakan format rapor tahfizh.</small>
                    </div>
                    <div class="col-12 form-group">
                        <label class="font-weight-bold">Catatan / Deskripsi (Opsional)</label>
                        <textarea name="deskripsi" class="form-control" rows="2" placeholder="Catatan internal pengelola rombel asrama"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary font-weight-bold"><i class="fas fa-save mr-1"></i> Simpan Rombel Baru</button>
            </div>
        </form>
    </div>
</div>

@include('asrama._scripts')
@stop
@section('css') @include('asrama._styles') @stop

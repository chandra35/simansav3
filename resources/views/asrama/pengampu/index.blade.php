@extends('adminlte::page')
@section('title', 'Assign Guru Mapel Asrama')
@section('plugins.Select2', true)
@section('plugins.Datatables', true)

@section('content')
@include('asrama._alerts')
@php
    $heroTitle = 'Assign Guru Mapel Asrama';
    $heroDescription = 'Atur dan pantau penugasan pengampu mata pelajaran asrama per rombel dan semester untuk input nilai dan rapor.';
    $heroAction = '<button class="btn btn-light" data-toggle="modal" data-target="#addPengampuModal"><i class="fas fa-plus mr-1"></i> Tambah Penugasan</button>';
@endphp
@include('asrama._hero')

<div class="row">
    <div class="col-md-6 col-xl-3 mb-3">
        <div class="card card-outline card-primary h-100">
            <div class="card-body py-3">
                <div class="text-muted small text-uppercase font-weight-bold">Total Penugasan</div>
                <h3 class="text-primary mb-0">{{ number_format($stats['total_assignments']) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-3">
        <div class="card card-outline card-success h-100">
            <div class="card-body py-3">
                <div class="text-muted small text-uppercase font-weight-bold">Penugasan Aktif</div>
                <h3 class="text-success mb-0">{{ number_format($stats['total_active']) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-3">
        <div class="card card-outline card-info h-100">
            <div class="card-body py-3">
                <div class="text-muted small text-uppercase font-weight-bold">Guru Pengampu</div>
                <h3 class="text-info mb-0">{{ number_format($stats['total_teachers']) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-3">
        <div class="card card-outline card-warning h-100">
            <div class="card-body py-3">
                <div class="text-muted small text-uppercase font-weight-bold">Rombel Ter-assign</div>
                <h3 class="text-warning mb-0">{{ number_format($stats['total_classes']) }}</h3>
            </div>
        </div>
    </div>
</div>

{{-- Filter Card --}}
<div class="card card-outline card-secondary mb-3">
    <div class="card-header py-2">
        <h3 class="card-title text-sm font-weight-bold"><i class="fas fa-filter mr-1"></i> Filter Penugasan</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
        </div>
    </div>
    <div class="card-body py-2">
        <form method="get" action="{{ route('asrama.pengampu.index') }}" class="asrama-form">
            <div class="row">
                <div class="col-md-3 col-sm-6 mb-2">
                    <label class="small font-weight-bold text-muted mb-1">Tahun Pelajaran</label>
                    <select name="tahun_pelajaran_id" class="form-control form-control-sm asrama-select" onchange="this.form.submit()">
                        @foreach($years as $year)
                            <option value="{{ $year->id }}" @selected($selectedYear == $year->id)>
                                {{ $year->nama }} {{ $year->is_active ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-sm-6 mb-2">
                    <label class="small font-weight-bold text-muted mb-1">Rombel Asrama</label>
                    <select name="asrama_kelas_id" class="form-control form-control-sm asrama-select" onchange="this.form.submit()">
                        <option value="">-- Semua Rombel --</option>
                        @foreach($kelasList as $kelas)
                            <option value="{{ $kelas->id }}" @selected($selectedKelas == $kelas->id)>
                                {{ $kelas->nama_kelas }} ({{ ucfirst($kelas->jenis) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-sm-6 mb-2">
                    <label class="small font-weight-bold text-muted mb-1">Mata Pelajaran</label>
                    <select name="asrama_mapel_id" class="form-control form-control-sm asrama-select" onchange="this.form.submit()">
                        <option value="">-- Semua Mapel --</option>
                        @foreach($mapelList as $mapel)
                            <option value="{{ $mapel->id }}" @selected($selectedMapel == $mapel->id)>
                                {{ $mapel->nama_latin }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-sm-6 mb-2">
                    <label class="small font-weight-bold text-muted mb-1">Semester</label>
                    <select name="semester" class="form-control form-control-sm asrama-select" onchange="this.form.submit()">
                        <option value="">-- Semua --</option>
                        <option value="Ganjil" @selected($selectedSemester === 'Ganjil')>Ganjil</option>
                        <option value="Genap" @selected($selectedSemester === 'Genap')>Genap</option>
                    </select>
                </div>
                <div class="col-md-2 col-sm-6 mb-2 d-flex align-items-end">
                    <a href="{{ route('asrama.pengampu.index') }}" class="btn btn-sm btn-outline-secondary btn-block">
                        <i class="fas fa-undo mr-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Main Table Panel --}}
<div class="asrama-panel">
    <div class="asrama-panel__header">
        <div>
            <h3><i class="fas fa-chalkboard-teacher mr-2 text-info"></i>Daftar Guru Pengampu Mapel Asrama</h3>
            <p>Penugasan pengampu menentukan akses GTK pada menu Input Nilai Asrama.</p>
        </div>
        <div>
            <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#addPengampuModal">
                <i class="fas fa-plus mr-1"></i> Tambah Penugasan
            </button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table asrama-table asrama-datatable" data-order='[[1,"asc"],[2,"asc"]]'>
            <thead>
                <tr>
                    <th style="width:40px">No</th>
                    <th>Rombel</th>
                    <th>Mata Pelajaran</th>
                    <th>Semester</th>
                    <th>Guru Pengampu</th>
                    <th class="text-center" style="width:90px">Status</th>
                    <th class="text-center" style="width:90px">Nilai</th>
                    <th class="text-right no-sort" style="width:110px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="align-middle">
                        <strong>{{ $item->kelas?->nama_kelas ?? '-' }}</strong>
                        <br><small class="text-muted">{{ $item->kelas?->tahunPelajaran?->nama ?? '-' }} · Tingkat {{ $item->kelas?->tingkat ?? '-' }}</small>
                    </td>
                    <td class="align-middle">
                        <strong>{{ $item->mapel?->nama_latin ?? '-' }}</strong>
                        @if($item->mapel?->nama_arab)
                            <span class="asrama-arab ml-1 text-muted">({{ $item->mapel->nama_arab }})</span>
                        @endif
                        <br><small class="text-muted">Skala: {{ $item->mapel?->skala_maksimum ?? 100 }}</small>
                    </td>
                    <td class="align-middle">
                        <span class="badge badge-{{ $item->semester === 'Ganjil' ? 'info' : 'primary' }}">
                            {{ $item->semester }}
                        </span>
                    </td>
                    <td class="align-middle">
                        <div class="d-flex align-items-center">
                            @if($item->asatidz?->gtk?->foto_profile_url)
                                <img src="{{ $item->asatidz->gtk->foto_profile_url }}" alt="{{ $item->asatidz->gtk->nama_lengkap }}" class="img-circle mr-2" style="width:32px;height:32px;object-fit:cover">
                            @endif
                            <div>
                                <strong>{{ $item->asatidz?->gtk?->nama_lengkap ?? 'GTK Tidak Ditemukan' }}</strong>
                                <br><small class="text-muted">{{ $item->asatidz?->gtk?->nip ? 'NIP '.$item->asatidz->gtk->nip : ($item->asatidz?->gtk?->nuptk ? 'NUPTK '.$item->asatidz->gtk->nuptk : '-') }}</small>
                            </div>
                        </div>
                    </td>
                    <td class="align-middle text-center">
                        <span class="badge badge-{{ $item->is_active ? 'success' : 'secondary' }}">
                            {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="align-middle text-center">
                        @if($item->nilai_count > 0)
                            <span class="badge badge-light border text-success">
                                <i class="fas fa-check-circle mr-1"></i>{{ $item->nilai_count }} data
                            </span>
                        @else
                            <span class="text-muted small">Belum ada</span>
                        @endif
                    </td>
                    <td class="align-middle text-right text-nowrap">
                        <button type="button" class="btn btn-sm btn-outline-primary asrama-icon-button" title="Edit Penugasan" data-toggle="modal" data-target="#editPengampuModal{{ $item->id }}">
                            <i class="fas fa-pen"></i>
                        </button>
                        <form method="post" action="{{ route('asrama.pengampu.destroy', $item) }}" class="d-inline" data-asrama-loading data-confirm="Hapus penugasan mapel {{ $item->mapel?->nama_latin }} untuk rombel {{ $item->kelas?->nama_kelas }}?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger asrama-icon-button" title="Hapus Penugasan" @disabled($item->nilai_count > 0)>
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="asrama-empty">
                        <i class="fas fa-chalkboard-teacher"></i>
                        Belum ada penugasan guru mapel asrama yang sesuai dengan filter yang dipilih.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Tambah Penugasan --}}
<div class="modal fade" id="addPengampuModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <form method="post" action="{{ route('asrama.pengampu.store') }}" class="modal-content asrama-form" data-asrama-loading data-loading-title="Menyimpan penugasan">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-check mr-2 text-info"></i>Assign Guru Mapel Asrama</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="callout callout-info py-2 mb-3">
                    <small><i class="fas fa-info-circle mr-1"></i>GTK yang dapat ditugaskan harus berstatus aktif dan memiliki hak <strong>Dapat Mengampu Mapel</strong> pada master Pengasuh &amp; Pengajar Asrama.</small>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="small font-weight-bold text-muted">Rombel Asrama <span class="text-danger">*</span></label>
                        <select required name="asrama_kelas_id" class="form-control asrama-select" data-placeholder="Pilih Rombel">
                            <option value=""></option>
                            @foreach($kelasList as $kelas)
                                <option value="{{ $kelas->id }}" @selected($selectedKelas == $kelas->id)>
                                    {{ $kelas->nama_kelas }} ({{ ucfirst($kelas->jenis) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="small font-weight-bold text-muted">Mata Pelajaran <span class="text-danger">*</span></label>
                        <select required name="asrama_mapel_id" class="form-control asrama-select" data-placeholder="Pilih Mata Pelajaran">
                            <option value=""></option>
                            @foreach($mapelList as $mapel)
                                <option value="{{ $mapel->id }}" @selected($selectedMapel == $mapel->id)>
                                    {{ $mapel->nama_latin }} · {{ $mapel->nama_arab }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="small font-weight-bold text-muted">Guru Pengampu (GTK Asrama) <span class="text-danger">*</span></label>
                        <select required name="asrama_asatidz_id" class="form-control asrama-select" data-placeholder="Pilih Guru / Asatidz">
                            <option value=""></option>
                            @foreach($asatidzList as $asatidz)
                                <option value="{{ $asatidz->id }}" @selected($selectedAsatidz == $asatidz->id)>
                                    {{ $asatidz->gtk?->nama_lengkap }} ({{ $asatidz->jabatan }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="small font-weight-bold text-muted">Semester <span class="text-danger">*</span></label>
                        <select required name="semester" class="form-control asrama-select">
                            <option value="Ganjil" @selected($selectedSemester === 'Ganjil')>Ganjil</option>
                            <option value="Genap" @selected($selectedSemester === 'Genap')>Genap</option>
                        </select>
                    </div>
                    <div class="col-12 mt-2">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="newActiveSwitch" name="is_active" value="1" checked>
                            <label class="custom-control-label font-weight-normal" for="newActiveSwitch">Status Penugasan Aktif</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan Penugasan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modals Edit Penugasan --}}
@foreach($records as $item)
<div class="modal fade" id="editPengampuModal{{ $item->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <form method="post" action="{{ route('asrama.pengampu.update', $item) }}" class="modal-content asrama-form" data-asrama-loading data-loading-title="Memperbarui penugasan">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-pen mr-2 text-primary"></i>Edit Penugasan Guru Mapel</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="small font-weight-bold text-muted">Rombel Asrama <span class="text-danger">*</span></label>
                        <select required name="asrama_kelas_id" class="form-control asrama-select">
                            @foreach($kelasList as $kelas)
                                <option value="{{ $kelas->id }}" @selected($item->asrama_kelas_id === $kelas->id)>
                                    {{ $kelas->nama_kelas }} ({{ ucfirst($kelas->jenis) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="small font-weight-bold text-muted">Mata Pelajaran <span class="text-danger">*</span></label>
                        <select required name="asrama_mapel_id" class="form-control asrama-select">
                            @foreach($mapelList as $mapel)
                                <option value="{{ $mapel->id }}" @selected($item->asrama_mapel_id === $mapel->id)>
                                    {{ $mapel->nama_latin }} · {{ $mapel->nama_arab }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="small font-weight-bold text-muted">Guru Pengampu <span class="text-danger">*</span></label>
                        <select required name="asrama_asatidz_id" class="form-control asrama-select">
                            @foreach($asatidzList as $asatidz)
                                <option value="{{ $asatidz->id }}" @selected($item->asrama_asatidz_id === $asatidz->id)>
                                    {{ $asatidz->gtk?->nama_lengkap }} ({{ $asatidz->jabatan }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="small font-weight-bold text-muted">Semester <span class="text-danger">*</span></label>
                        <select required name="semester" class="form-control asrama-select">
                            <option value="Ganjil" @selected($item->semester === 'Ganjil')>Ganjil</option>
                            <option value="Genap" @selected($item->semester === 'Genap')>Genap</option>
                        </select>
                    </div>
                    <div class="col-12 mt-2">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="editActiveSwitch{{ $item->id }}" name="is_active" value="1" @checked($item->is_active)>
                            <label class="custom-control-label font-weight-normal" for="editActiveSwitch{{ $item->id }}">Status Penugasan Aktif</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Perbarui Penugasan</button>
            </div>
        </form>
    </div>
</div>
@endforeach

@include('asrama._scripts')
@stop

@section('css')
@include('asrama._styles')
@stop

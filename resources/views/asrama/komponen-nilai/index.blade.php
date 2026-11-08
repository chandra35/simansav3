@extends('adminlte::page')
@section('title', 'Komponen Nilai Asrama')
@section('plugins.Select2', true)
@section('plugins.Datatables', true)
@section('content')
@include('asrama._alerts')
@php
    $heroTitle = 'Komponen Penilaian Asrama';
    $heroDescription = 'Atur komponen nilai (Formatif, ATS, AAS, Tahfidz, Kitab, dll.) serta persentase bobot per tahun ajaran.';
    $heroAction = '<div class="btn-group">'
        . '<button type="button" class="btn btn-warning text-dark font-weight-bold mr-2" data-toggle="modal" data-target="#modal-copy"><i class="fas fa-copy mr-1"></i> Salin dari Tahun Lain</button>'
        . '<button type="button" class="btn btn-info font-weight-bold" data-toggle="modal" data-target="#modal-create"><i class="fas fa-plus mr-1"></i> Tambah Komponen</button>'
        . '</div>';
@endphp
@include('asrama._hero')

{{-- Filter Card Tahun Pelajaran --}}
<div class="asrama-panel">
    <div class="asrama-panel__header">
        <div>
            <h3><i class="fas fa-filter mr-1"></i> Tahun Pelajaran</h3>
            <p>Pilih tahun pelajaran untuk mengelola setelan komponen penilaian asrama.</p>
        </div>
    </div>
    <div class="asrama-panel__body asrama-form">
        <form method="get" class="row align-items-end">
            <div class="col-md-8 mb-2">
                <label>Tahun Pelajaran</label>
                <select name="tahun_pelajaran_id" class="form-control asrama-select" onchange="this.form.submit()">
                    @foreach($years as $yr)
                        <option value="{{ $yr->id }}" @selected($selectedYear?->id === $yr->id)>
                            {{ $yr->nama }}
                            @if($activeYear && $activeYear->id === $yr->id)
                                ★ [Aktif di SIMANSA]
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 mb-2">
                <a href="{{ route('asrama.nilai.index', ['tahun_pelajaran_id' => $selectedYear?->id]) }}" class="btn btn-outline-info btn-block">
                    <i class="fas fa-pen mr-1"></i> Buka Menu Input Nilai
                </a>
            </div>
        </form>
    </div>
</div>

{{-- KPI Stats --}}
<div class="row">
    <div class="col-md-3 col-6 mb-3">
        <div class="asrama-stat">
            <span>Tahun Pelajaran</span>
            <strong>{{ $selectedYear?->nama ?? '-' }}</strong>
            <small>
                @if($activeYear && $activeYear->id === $selectedYear?->id)
                    <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>Aktif di SIMANSA</span>
                @else
                    <span class="badge badge-secondary">Bukan Tahun Aktif</span>
                @endif
            </small>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="asrama-stat" style="border-left-color:var(--asrama-navy);">
            <span>Total Komponen</span>
            <strong>{{ $components->count() }}</strong>
            <small>{{ $activeCount }} komponen berstatus aktif</small>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="asrama-stat" style="border-left-color: {{ abs($totalBobot - 100) < 0.01 ? 'var(--asrama-teal)' : 'var(--asrama-gold)' }};">
            <span>Total Bobot</span>
            <strong style="color: {{ abs($totalBobot - 100) < 0.01 ? '#08756f' : '#b45309' }};">
                {{ number_format($totalBobot, 1) }}%
            </strong>
            <small>
                @if(abs($totalBobot - 100) < 0.01)
                    <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Ideal (100%)</span>
                @elseif($totalBobot == 0)
                    <span class="badge badge-secondary">Belum ada bobot</span>
                @else
                    <span class="badge badge-warning text-dark"><i class="fas fa-exclamation-triangle mr-1"></i>Disarankan 100%</span>
                @endif
            </small>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="asrama-stat" style="border-left-color:#3b82f6;">
            <span>Mode Input Nilai</span>
            <strong>{{ $components->isNotEmpty() ? 'Dinamis' : 'Standar' }}</strong>
            <small>{{ $components->isNotEmpty() ? 'Rincian per komponen' : 'Nilai tunggal flat' }}</small>
        </div>
    </div>
</div>

@if($components->isNotEmpty() && abs($totalBobot - 100) >= 0.01)
    <div class="alert alert-warning asrama-alert mb-3">
        <i class="fas fa-info-circle mr-1"></i>
        <strong>Perhatian Bobot:</strong> Total bobot komponen aktif saat ini adalah <strong>{{ number_format($totalBobot, 1) }}%</strong>.
        Sistem tetap menghitung rata-rata tertimbang proporsional sesuai rasio bobot masing-masing, namun sangat dianjurkan mengakumulasikan total bobot menjadi <strong>100%</strong> agar kalkulasi pembobotan mudah dipahami.
    </div>
@endif

{{-- Data Table --}}
<div class="asrama-panel">
    <div class="asrama-panel__header">
        <div>
            <h3><i class="fas fa-sliders-h mr-1"></i> Daftar Komponen Nilai · {{ $selectedYear?->nama }}</h3>
            <p>Komponen di bawah ini akan muncul sebagai kolom penilaian pada form input nilai guru asrama.</p>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table asrama-table asrama-datatable" data-order='[[0,"asc"]]'>
            <thead>
                <tr>
                    <th style="width:70px">Urutan</th>
                    <th style="width:130px">Kode</th>
                    <th>Nama Komponen</th>
                    <th style="width:120px" class="text-center">Bobot (%)</th>
                    <th style="width:100px" class="text-center">Status</th>
                    <th>Keterangan</th>
                    <th class="no-sort text-right" style="width:120px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($components as $item)
                    <tr>
                        <td class="font-weight-bold text-center">{{ $item->urutan }}</td>
                        <td><span class="badge badge-info px-2 py-1 font-weight-bold" style="font-size:.85rem;">{{ $item->kode }}</span></td>
                        <td><strong>{{ $item->nama }}</strong></td>
                        <td class="text-center">
                            <span class="badge badge-light border px-2 py-1 font-weight-bold" style="font-size:.85rem;">
                                {{ number_format($item->bobot, 1) }}%
                            </span>
                        </td>
                        <td class="text-center">
                            @if($item->is_active)
                                <span class="asrama-badge asrama-badge--active"><i class="fas fa-check mr-1"></i>Aktif</span>
                            @else
                                <span class="asrama-badge asrama-badge--muted">Nonaktif</span>
                            @endif
                        </td>
                        <td><span class="text-muted">{{ $item->keterangan ?: '-' }}</span></td>
                        <td class="text-right">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-info btn-edit"
                                    data-id="{{ $item->id }}"
                                    data-kode="{{ $item->kode }}"
                                    data-nama="{{ $item->nama }}"
                                    data-bobot="{{ $item->bobot }}"
                                    data-urutan="{{ $item->urutan }}"
                                    data-active="{{ $item->is_active ? '1' : '0' }}"
                                    data-keterangan="{{ $item->keterangan }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('asrama.komponen-nilai.destroy', $item) }}" method="post"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus komponen {{ $item->nama }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm" style="border-top-left-radius:0;border-bottom-left-radius:0;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="asrama-empty">
                            <i class="fas fa-sliders-h"></i>
                            Belum ada komponen penilaian untuk tahun pelajaran ini.<br>
                            <small class="text-muted">Jika belum ada komponen, guru asrama akan menginput nilai tunggal secara langsung (0–100).</small>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Tambah Komponen --}}
<div class="modal fade asrama-modal" id="modal-create" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="post" action="{{ route('asrama.komponen-nilai.store') }}" class="modal-content asrama-form">
            @csrf
            <input type="hidden" name="tahun_pelajaran_id" value="{{ $selectedYear?->id }}">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle mr-2"></i> Tambah Komponen Penilaian</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label>Tahun Pelajaran</label>
                    <input type="text" class="form-control" value="{{ $selectedYear?->nama }}" readonly>
                </div>
                <div class="row">
                    <div class="col-md-5 form-group mb-3">
                        <label>Kode Komponen <span class="text-danger">*</span></label>
                        <input type="text" name="kode" class="form-control text-uppercase" placeholder="Misal: HARIAN / ATS / AAS" required pattern="[A-Za-z0-9_]+" title="Hanya huruf, angka, dan underscore">
                        <small class="text-muted">Contoh: HARIAN, ATS, AAS, KITAB</small>
                    </div>
                    <div class="col-md-7 form-group mb-3">
                        <label>Nama Komponen <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" placeholder="Misal: Asesmen Tengah Semester" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label>Bobot Penilaian (%) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.1" min="0" max="100" name="bobot" class="form-control" placeholder="Misal: 30" required>
                            <div class="input-group-append"><span class="input-group-text">%</span></div>
                        </div>
                    </div>
                    <div class="col-md-6 form-group mb-3">
                        <label>Urutan Tampilan</label>
                        <input type="number" name="urutan" class="form-control" value="{{ ($components->max('urutan') ?? 0) + 1 }}" min="1">
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label>Keterangan (Opsional)</label>
                    <input type="text" name="keterangan" class="form-control" placeholder="Deskripsi singkat cakupan penilaian">
                </div>
                <div class="custom-control custom-switch mt-2">
                    <input type="checkbox" class="custom-control-input" id="is_active_create" name="is_active" value="1" checked>
                    <label class="custom-control-label" for="is_active_create">Status Aktif</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-info font-weight-bold"><i class="fas fa-save mr-1"></i> Simpan Komponen</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Komponen --}}
<div class="modal fade asrama-modal" id="modal-edit" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="post" id="form-edit" class="modal-content asrama-form">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit mr-2"></i> Edit Komponen Penilaian</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-5 form-group mb-3">
                        <label>Kode Komponen <span class="text-danger">*</span></label>
                        <input type="text" name="kode" id="edit_kode" class="form-control text-uppercase" required pattern="[A-Za-z0-9_]+">
                    </div>
                    <div class="col-md-7 form-group mb-3">
                        <label>Nama Komponen <span class="text-danger">*</span></label>
                        <input type="text" name="nama" id="edit_nama" class="form-control" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label>Bobot Penilaian (%) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.1" min="0" max="100" name="bobot" id="edit_bobot" class="form-control" required>
                            <div class="input-group-append"><span class="input-group-text">%</span></div>
                        </div>
                    </div>
                    <div class="col-md-6 form-group mb-3">
                        <label>Urutan Tampilan</label>
                        <input type="number" name="urutan" id="edit_urutan" class="form-control" min="1" required>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label>Keterangan (Opsional)</label>
                    <input type="text" name="keterangan" id="edit_keterangan" class="form-control">
                </div>
                <div class="custom-control custom-switch mt-2">
                    <input type="checkbox" class="custom-control-input" id="edit_is_active" name="is_active" value="1">
                    <label class="custom-control-label" for="edit_is_active">Status Aktif</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-info font-weight-bold"><i class="fas fa-save mr-1"></i> Perbarui Komponen</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Salin Komponen dari Tahun Lain --}}
<div class="modal fade asrama-modal" id="modal-copy" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="post" action="{{ route('asrama.komponen-nilai.copy') }}" class="modal-content asrama-form">
            @csrf
            <input type="hidden" name="to_tahun_pelajaran_id" value="{{ $selectedYear?->id }}">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-copy mr-2"></i> Salin Komponen dari Tahun Lain</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Salin susunan komponen penilaian dari tahun pelajaran lain ke tahun pelajaran <strong>{{ $selectedYear?->nama }}</strong>. Komponen yang kodenya sudah ada tidak akan diduplikasi.</p>
                <div class="form-group mb-3">
                    <label>Tahun Pelajaran Sumber</label>
                    <select name="from_tahun_pelajaran_id" class="form-control asrama-select" required>
                        <option value="">-- Pilih Tahun Pelajaran Sumber --</option>
                        @foreach($years->where('id', '!=', $selectedYear?->id) as $srcYear)
                            <option value="{{ $srcYear->id }}">{{ $srcYear->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mb-2">
                    <label>Tahun Pelajaran Tujuan</label>
                    <input type="text" class="form-control" value="{{ $selectedYear?->nama }}" readonly>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-warning font-weight-bold text-dark"><i class="fas fa-copy mr-1"></i> Salin Sekarang</button>
            </div>
        </form>
    </div>
</div>

@include('asrama._scripts')
@push('js')
<script>
$(function(){
    $('.btn-edit').on('click', function(){
        const data = $(this).data();
        const url = "{{ url('asrama/komponen-nilai') }}/" + data.id;
        $('#form-edit').attr('action', url);
        $('#edit_kode').val(data.kode);
        $('#edit_nama').val(data.nama);
        $('#edit_bobot').val(data.bobot);
        $('#edit_urutan').val(data.urutan);
        $('#edit_keterangan').val(data.keterangan);
        $('#edit_is_active').prop('checked', data.active == 1);
        $('#modal-edit').modal('show');
    });
});
</script>
@endpush
@stop
@section('css') @include('asrama._styles') @stop

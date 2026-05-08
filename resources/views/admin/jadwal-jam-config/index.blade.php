@extends('adminlte::page')

@section('title', 'Konfigurasi Jam Pelajaran')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-clock"></i> Konfigurasi Jam Pelajaran</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('admin.jadwal-pelajaran.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Jadwal
                </a>
            </div>
        </div>
    </div>
@endsection

@section('content')
<div class="row">
    {{-- Panel Kiri: Selector & Generate --}}
    <div class="col-lg-5">
        {{-- Pilih Tahun Pelajaran --}}
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-calendar-alt"></i> Pilih Tahun Pelajaran</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.jadwal-jam-config.index') }}" id="formTahun">
                    <div class="form-group">
                        <label>Tahun Pelajaran</label>
                        <select name="tahun_pelajaran_id" class="form-control select2" id="tahunSelect" onchange="this.form.submit()">
                            <option value="">-- Pilih --</option>
                            @foreach($tahunList as $t)
                                <option value="{{ $t->id }}" {{ ($tahunDipilih && $tahunDipilih->id === $t->id) ? 'selected' : '' }}>
                                    {{ $t->tahun_pelajaran }}
                                    @if($t->is_active) <small>(Aktif)</small> @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        @if($tahunDipilih)
        {{-- Form Generate --}}
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-magic"></i> Generate Otomatis</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form id="formGenerate">
                    <input type="hidden" name="tahun_pelajaran_id" value="{{ $tahunDipilih->id }}">
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label>Jam Mulai <span class="text-danger">*</span></label>
                            <input type="time" name="jam_mulai" class="form-control" value="07:00" required>
                        </div>
                        <div class="form-group col-6">
                            <label>Durasi (menit) <span class="text-danger">*</span></label>
                            <input type="number" name="durasi_menit" class="form-control" value="45" min="20" max="120" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Jumlah Jam Pelajaran <span class="text-danger">*</span></label>
                        <input type="number" name="jumlah_jam" class="form-control" value="8" min="1" max="15" required>
                    </div>

                    {{-- Istirahat rows --}}
                    <label>Istirahat <small class="text-muted">(opsional)</small></label>
                    <div id="istirahatRows"></div>
                    <button type="button" class="btn btn-sm btn-outline-secondary mb-3" id="btnAddIstirahat">
                        <i class="fas fa-plus"></i> Tambah Istirahat
                    </button>

                    <div>
                        <button type="submit" class="btn btn-success btn-block" id="btnGenerate">
                            <i class="fas fa-magic"></i> Generate
                        </button>
                        <small class="text-warning d-block mt-1">
                            <i class="fas fa-exclamation-triangle"></i>
                            Generate akan menghapus konfigurasi lama untuk tahun ini.
                        </small>
                    </div>
                </form>
            </div>
        </div>

        {{-- Form Tambah Manual --}}
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-plus"></i> Tambah Manual</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body collapsed-card" style="display:none">
                <form id="formTambahManual">
                    <input type="hidden" name="tahun_pelajaran_id" value="{{ $tahunDipilih->id }}">
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label>Mulai <span class="text-danger">*</span></label>
                            <input type="time" name="waktu_mulai" class="form-control" required>
                        </div>
                        <div class="form-group col-6">
                            <label>Selesai <span class="text-danger">*</span></label>
                            <input type="time" name="waktu_selesai" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Label <small class="text-muted">(contoh: Istirahat, Jum'at)</small></label>
                        <input type="text" name="label" class="form-control" placeholder="Kosong = jam pelajaran biasa" maxlength="50">
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="isIstirahat" name="is_istirahat" value="1">
                            <label class="custom-control-label" for="isIstirahat">Ini waktu istirahat</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-info btn-block">
                        <i class="fas fa-plus"></i> Tambah Baris
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>

    {{-- Panel Kanan: Tabel Jam --}}
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list"></i>
                    Daftar Jam
                    @if($tahunDipilih)
                        <span class="badge badge-primary ml-2">{{ $tahunDipilih->tahun_pelajaran }}</span>
                    @endif
                </h3>
            </div>
            <div class="card-body p-0">
                @if(!$tahunDipilih)
                    <div class="p-4 text-center text-muted">
                        <i class="fas fa-calendar-alt fa-2x mb-2 d-block"></i>
                        Pilih tahun pelajaran terlebih dahulu
                    </div>
                @elseif($jamList->isEmpty())
                    <div class="p-4 text-center text-muted">
                        <i class="fas fa-clock fa-2x mb-2 d-block"></i>
                        Belum ada konfigurasi jam. Gunakan Generate Otomatis di kiri.
                    </div>
                @else
                    <table class="table table-sm table-hover mb-0" id="jamTable">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-center" style="width:50px">#</th>
                                <th class="text-center" style="width:40px">Jam</th>
                                <th>Mulai</th>
                                <th>Selesai</th>
                                <th>Label / Jenis</th>
                                <th class="text-center" style="width:60px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="jamTableBody">
                            @foreach($jamList as $jam)
                                <tr class="{{ $jam->is_istirahat ? 'table-warning' : '' }}" data-id="{{ $jam->id }}">
                                    <td class="text-center">{{ $jam->urutan }}</td>
                                    <td class="text-center">
                                        @if($jam->jam_ke)
                                            <span class="badge badge-primary">{{ $jam->jam_ke }}</span>
                                        @else
                                            <span class="badge badge-warning"><i class="fas fa-coffee"></i></span>
                                        @endif
                                    </td>
                                    <td>{{ $jam->waktu_mulai }}</td>
                                    <td>{{ $jam->waktu_selesai }}</td>
                                    <td>
                                        @if($jam->is_istirahat)
                                            <span class="text-warning"><i class="fas fa-coffee"></i> {{ $jam->label ?? 'Istirahat' }}</span>
                                        @else
                                            <span class="text-muted small">{{ $jam->label ?? 'Jam Pelajaran' }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-xs btn-danger btn-hapus" data-id="{{ $jam->id }}" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(function () {
    // Select2
    $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

    // Istirahat row template
    let istirahatIdx = 0;
    $('#btnAddIstirahat').on('click', function () {
        const idx = istirahatIdx++;
        const row = `<div class="input-group input-group-sm mb-1 istirahat-row">
            <div class="input-group-prepend">
                <span class="input-group-text">Setelah jam ke-</span>
            </div>
            <input type="number" class="form-control" name="istirahat[${idx}][setelah_jam]" min="1" max="15" placeholder="3" style="max-width:60px">
            <div class="input-group-prepend">
                <span class="input-group-text">durasi</span>
            </div>
            <input type="number" class="form-control" name="istirahat[${idx}][durasi]" value="15" min="5" max="60" style="max-width:60px">
            <div class="input-group-prepend">
                <span class="input-group-text">menit, label</span>
            </div>
            <input type="text" class="form-control" name="istirahat[${idx}][label]" placeholder="Istirahat" maxlength="50">
            <div class="input-group-append">
                <button type="button" class="btn btn-outline-danger btn-remove-istirahat"><i class="fas fa-times"></i></button>
            </div>
        </div>`;
        $('#istirahatRows').append(row);
    });

    $(document).on('click', '.btn-remove-istirahat', function () {
        $(this).closest('.istirahat-row').remove();
    });

    // Form Generate
    $('#formGenerate').on('submit', function (e) {
        e.preventDefault();
        const data = $(this).serialize();
        Swal.fire({
            title: 'Generate ulang?',
            text: 'Konfigurasi jam lama untuk tahun ini akan dihapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, generate',
        }).then(result => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: '{{ route("admin.jadwal-jam-config.generate") }}',
                method: 'POST',
                data: data,
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: function (res) {
                    if (res.success) {
                        toastr.success(res.message);
                        setTimeout(() => location.reload(), 800);
                    } else {
                        toastr.error(res.message || 'Gagal generate.');
                    }
                },
                error: function (xhr) {
                    const msg = xhr.responseJSON?.message || 'Terjadi kesalahan.';
                    toastr.error(msg);
                }
            });
        });
    });

    // Form Tambah Manual
    $('#formTambahManual').on('submit', function (e) {
        e.preventDefault();
        const data = $(this).serialize();
        $.ajax({
            url: '{{ route("admin.jadwal-jam-config.store") }}',
            method: 'POST',
            data: data,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function (res) {
                if (res.success) {
                    toastr.success(res.message);
                    const row = res.data;
                    const isIstirahat = row.is_istirahat;
                    const jamBadge = row.jam_ke
                        ? `<span class="badge badge-primary">${row.jam_ke}</span>`
                        : `<span class="badge badge-warning"><i class="fas fa-coffee"></i></span>`;
                    const label = isIstirahat
                        ? `<span class="text-warning"><i class="fas fa-coffee"></i> ${row.label || 'Istirahat'}</span>`
                        : `<span class="text-muted small">${row.label || 'Jam Pelajaran'}</span>`;
                    const trClass = isIstirahat ? 'table-warning' : '';
                    $('#jamTableBody').append(`<tr class="${trClass}" data-id="${row.id}">
                        <td class="text-center">${row.urutan}</td>
                        <td class="text-center">${jamBadge}</td>
                        <td>${row.waktu_mulai}</td>
                        <td>${row.waktu_selesai}</td>
                        <td>${label}</td>
                        <td class="text-center">
                            <button class="btn btn-xs btn-danger btn-hapus" data-id="${row.id}" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>`);
                    $(this).find('input:not([name=tahun_pelajaran_id])').val('');
                }
            }.bind(this),
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message || 'Terjadi kesalahan.');
            }
        });
    });

    // Hapus baris
    $(document).on('click', '.btn-hapus', function () {
        const id = $(this).data('id');
        const $tr = $(this).closest('tr');
        Swal.fire({
            title: 'Hapus baris ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Hapus',
            confirmButtonColor: '#dc3545',
        }).then(result => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: `/admin/jadwal-jam-config/${id}`,
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: function (res) {
                    if (res.success) {
                        toastr.success(res.message);
                        $tr.remove();
                    }
                },
                error: function (xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Gagal menghapus.');
                }
            });
        });
    });
});
</script>
@endsection

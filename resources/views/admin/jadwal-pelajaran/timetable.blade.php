@extends('adminlte::page')

@section('title', 'Timetable Jadwal')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-8">
            <h1>
                <i class="fas fa-table"></i>
                Jadwal Pelajaran
                @if($kelasObj)
                    &mdash; <span class="text-primary">{{ $kelasObj->nama_kelas }}</span>
                    <small class="text-muted">Semester {{ $semester }}</small>
                @endif
            </h1>
        </div>
        <div class="col-sm-4">
            <div class="float-sm-right">
                @can('manage-jadwal-pelajaran')
                <a href="{{ route('admin.jadwal-jam-config.index', ['tahun_pelajaran_id' => $tahunId]) }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-clock"></i> Konfigurasi Jam
                </a>
                @endcan
                <a href="{{ route('admin.jadwal-pelajaran.index', ['tahun_pelajaran_id' => $tahunId]) }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>
@endsection

@section('css')
<style>
.timetable-wrap { overflow-x: auto; }
.timetable { min-width: 700px; border-collapse: collapse; width: 100%; }
.timetable th, .timetable td { border: 1px solid #dee2e6; padding: 0; vertical-align: middle; }
.timetable thead th { text-align: center; background: #f8f9fa; font-size: .8rem; padding: 6px 4px; }
.timetable .col-jam { width: 80px; text-align: center; background: #f8f9fa; font-size: .78rem; white-space: nowrap; padding: 4px; }
.timetable .col-jam.istirahat-row { background: #fff3cd; }
.timetable-cell { min-height: 56px; padding: 4px; cursor: pointer; position: relative; transition: background .15s; }
.timetable-cell:hover { background: rgba(0,123,255,.06); }
.timetable-cell.has-jadwal { cursor: pointer; }
.timetable-cell.istirahat-row { background: #fff3cd !important; cursor: default; font-size: .8rem; text-align: center; color: #856404; }
.slot-card { border-radius: 4px; padding: 3px 5px; font-size: .78rem; line-height: 1.3; }
.slot-mapel { font-weight: 600; font-size: .78rem; }
.slot-guru  { font-size: .72rem; color: rgba(0,0,0,.65); }
.slot-room  { font-size: .7rem; color: rgba(0,0,0,.5); }
.add-btn { width: 100%; height: 100%; min-height: 54px; display: flex; align-items: center; justify-content: center; color: #adb5bd; font-size: .9rem; }
.add-btn:hover { color: #007bff; }
/* mapel color coding (pastel palette cycling) */
.mc-1  { background: #dbeafe; border-left: 3px solid #3b82f6; }
.mc-2  { background: #dcfce7; border-left: 3px solid #22c55e; }
.mc-3  { background: #fef3c7; border-left: 3px solid #f59e0b; }
.mc-4  { background: #fce7f3; border-left: 3px solid #ec4899; }
.mc-5  { background: #ede9fe; border-left: 3px solid #8b5cf6; }
.mc-6  { background: #e0f2fe; border-left: 3px solid #0284c7; }
.mc-7  { background: #d1fae5; border-left: 3px solid #059669; }
.mc-8  { background: #ffedd5; border-left: 3px solid #ea580c; }
.mc-9  { background: #f0fdf4; border-left: 3px solid #16a34a; }
.mc-10 { background: #fdf4ff; border-left: 3px solid #a855f7; }
.mc-11 { background: #fff7ed; border-left: 3px solid #d97706; }
.mc-12 { background: #ecfeff; border-left: 3px solid #06b6d4; }
</style>
@endsection

@section('content')
{{-- Filter bar --}}
<div class="card">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('admin.jadwal-pelajaran.timetable') }}" class="form-inline">
            <div class="form-group mr-2">
                <label class="mr-1 small">Tahun:</label>
                <select name="tahun_pelajaran_id" class="form-control form-control-sm select2" style="min-width:160px" id="ftTahun">
                    @foreach($tahunList as $t)
                        <option value="{{ $t->id }}" {{ $tahunId == $t->id ? 'selected' : '' }}>{{ $t->tahun_pelajaran }}{{ $t->is_active ? ' ✓' : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mr-2">
                <label class="mr-1 small">Kelas:</label>
                <select name="kelas_id" class="form-control form-control-sm select2" style="min-width:180px" id="ftKelas">
                    <option value="">-- Pilih --</option>
                    @foreach($kelasList as $k)
                        <option value="{{ $k->id }}" {{ $kelasId == $k->id ? 'selected' : '' }}>
                            {{ $k->nama_kelas }}{{ $k->jurusan ? ' - '.$k->jurusan->nama_jurusan : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mr-2">
                <label class="mr-1 small">Sem:</label>
                <select name="semester" class="form-control form-control-sm">
                    <option value="1" {{ $semester == 1 ? 'selected' : '' }}>1 Ganjil</option>
                    <option value="2" {{ $semester == 2 ? 'selected' : '' }}>2 Genap</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-sync-alt"></i> Tampilkan
            </button>
        </form>
    </div>
</div>

@if(!$kelasObj)
<div class="callout callout-info">
    <i class="fas fa-info-circle"></i> Pilih kelas untuk melihat jadwal.
</div>
@elseif(!$hasJamConfig)
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle"></i>
    Konfigurasi jam untuk tahun ini belum ada.
    @can('manage-jadwal-pelajaran')
        <a href="{{ route('admin.jadwal-jam-config.index', ['tahun_pelajaran_id' => $tahunId]) }}" class="alert-link">
            Buat sekarang &rarr;
        </a>
    @endcan
</div>
@else

{{-- Timetable Grid --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">
            <i class="fas fa-table"></i>
            {{ $kelasObj->nama_kelas }}{{ $kelasObj->jurusan ? ' - '.$kelasObj->jurusan->nama_jurusan : '' }}
            &mdash; Semester {{ $semester }}
        </h3>
        @can('manage-jadwal-pelajaran')
        <div>
            <button class="btn btn-sm btn-outline-secondary" id="btnCopyJadwal">
                <i class="fas fa-copy"></i> Salin Jadwal
            </button>
        </div>
        @endcan
    </div>
    <div class="card-body p-2 timetable-wrap">
        <table class="timetable" id="timetableGrid">
            <thead>
                <tr>
                    <th style="width:80px">Jam</th>
                    @foreach($hariList as $hari)
                        <th>{{ ucfirst($hari) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($jamConfig as $jam)
                    @if($jam->is_istirahat)
                    <tr>
                        <td class="col-jam istirahat-row">
                            <div>{{ $jam->waktu_mulai }}</div>
                            <div style="font-size:.65rem">{{ $jam->waktu_selesai }}</div>
                        </td>
                        <td colspan="{{ count($hariList) }}" class="timetable-cell istirahat-row">
                            <i class="fas fa-coffee"></i> {{ $jam->label ?? 'Istirahat' }}
                        </td>
                    </tr>
                    @else
                    <tr data-jam-ke="{{ $jam->jam_ke }}" data-urutan="{{ $jam->urutan }}">
                        <td class="col-jam">
                            <div><strong>Jam {{ $jam->jam_ke }}</strong></div>
                            <div>{{ $jam->waktu_mulai }}</div>
                            <div style="font-size:.65rem">{{ $jam->waktu_selesai }}</div>
                        </td>
                        @foreach($hariList as $hari)
                            @php $slot = $jadwalMap[$hari][$jam->jam_ke] ?? null; @endphp
                            <td class="timetable-cell {{ $slot ? 'has-jadwal' : 'empty-cell' }}"
                                data-hari="{{ $hari }}"
                                data-jam-ke="{{ $jam->jam_ke }}"
                                data-jadwal-id="{{ $slot?->id ?? ' }}"
                                @can('manage-jadwal-pelajaran') onclick="handleCellClick(this)" @endcan>
                                @if($slot)
                                    @php
                                        // Stable color index from mapel_id last char
                                        $colorIdx = (crc32($slot->mapel_id) % 12) + 1;
                                        $colorIdx = $colorIdx < 1 ? 1 : $colorIdx;
                                    @endphp
                                    <div class="slot-card mc-{{ $colorIdx }}" data-id="{{ $slot->id }}">
                                        <div class="slot-mapel">{{ $slot->mataPelajaran?->kode_mapel ?? '?' }}</div>
                                        <div class="slot-guru">{{ $slot->gtk?->nama_lengkap ?? '-' }}</div>
                                        @if($slot->ruangan)
                                        <div class="slot-room"><i class="fas fa-door-open" style="font-size:.65rem"></i> {{ $slot->ruangan }}</div>
                                        @endif
                                    </div>
                                @else
                                    @can('manage-jadwal-pelajaran')
                                    <div class="add-btn"><i class="fas fa-plus"></i></div>
                                    @endcan
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Legenda Mapel --}}
<div class="card mt-2">
    <div class="card-header py-2">
        <h3 class="card-title small mb-0"><i class="fas fa-palette"></i> Legenda Mata Pelajaran</h3>
    </div>
    <div class="card-body py-2" id="legendaMapel">
        @php
            $mapelSet = [];
            foreach($jadwalMap as $hariData) {
                foreach($hariData as $slot) {
                    if ($slot->mapel_id && !isset($mapelSet[$slot->mapel_id])) {
                        $mapelSet[$slot->mapel_id] = $slot;
                    }
                }
            }
        @endphp
        @foreach($mapelSet as $mid => $slot)
            @php $ci = (crc32($mid) % 12) + 1; $ci = $ci < 1 ? 1 : $ci; @endphp
            <span class="badge mr-1 mb-1 p-2 mc-{{ $ci }}" style="font-size:.8rem; border-radius:4px">
                {{ $slot->mataPelajaran?->kode_mapel }}
                <span class="font-weight-normal ml-1">{{ $slot->mataPelajaran?->nama_mapel }}</span>
            </span>
        @endforeach
        @if(empty($mapelSet))
            <span class="text-muted small">Belum ada jadwal</span>
        @endif
    </div>
</div>
@endif

{{-- Modal Add/Edit Slot --}}
@can('manage-jadwal-pelajaran')
<div class="modal fade" id="modalSlot" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalSlotTitle">Tambah Jadwal</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formSlot">
                    <input type="hidden" id="slotId" name="slot_id">
                    <input type="hidden" id="slotHari" name="hari">
                    <input type="hidden" id="slotJamKe" name="jam_ke">
                    <input type="hidden" id="slotKelasId" name="kelas_id" value="{{ $kelasId }}">
                    <input type="hidden" id="slotTahunId" name="tahun_pelajaran_id" value="{{ $tahunId }}">
                    <input type="hidden" id="slotSemester" name="semester" value="{{ $semester }}">

                    <div class="form-group">
                        <label><strong id="labelHariJam">Senin, Jam 1</strong></label>
                    </div>

                    <div class="form-group">
                        <label>Mata Pelajaran <span class="text-danger">*</span></label>
                        <select class="form-control select2" id="selMapel" name="mapel_id" required>
                            <option value="">-- Memuat... --</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Guru / GTK <span class="text-danger">*</span></label>
                        <select class="form-control select2" id="selGuru" name="gtk_id" required>
                            <option value="">-- Memuat... --</option>
                        </select>
                        <small id="konflikGuruNote" class="text-danger d-none">
                            <i class="fas fa-exclamation-triangle"></i> <span></span>
                        </small>
                    </div>

                    <div class="form-group">
                        <label>Ruangan</label>
                        <input type="text" class="form-control" name="ruangan" id="slotRuangan" maxlength="50" placeholder="cth: XI IPA 1">
                    </div>

                    <div class="form-group">
                        <label>Catatan</label>
                        <input type="text" class="form-control" name="catatan" id="slotCatatan" maxlength="255">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger mr-auto d-none" id="btnHapusSlot">
                    <i class="fas fa-trash"></i> Hapus
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSimpanSlot">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>
@endcan
@endsection

@section('js')
<script>
const CSRF = '{{ csrf_token() }}';
const TAHUN_ID = '{{ $tahunId }}';
const KELAS_ID = '{{ $kelasId }}';
const SEMESTER = {{ $semester }};

$(function () {
    $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

    // Filter tahun change: reload page
    $('#ftTahun').on('change', function () {
        $('#ftKelas').val(null).trigger('change');
    });
});

// ===== Cell click handler =====
function handleCellClick(cell) {
    const $cell = $(cell);
    const hari = $cell.data('hari');
    const jamKe = $cell.data('jam-ke');
    const jadwalId = $cell.data('jadwal-id') || null;

    $('#slotHari').val(hari);
    $('#slotJamKe').val(jamKe);
    $('#slotId').val(jadwalId || '');
    $('#labelHariJam').text(capitalize(hari) + ', Jam ke-' + jamKe);
    $('#modalSlotTitle').text(jadwalId ? 'Edit Jadwal' : 'Tambah Jadwal');
    $('#btnHapusSlot').toggleClass('d-none', !jadwalId);
    $('#konflikGuruNote').addClass('d-none');
    $('#slotRuangan').val('');
    $('#slotCatatan').val('');

    // Load mapel & guru options in parallel
    Promise.all([loadMapelOptions(), loadGuruOptions(hari, jamKe, jadwalId)])
        .then(() => {
            if (jadwalId) {
                // Load existing data
                $.get('{{ route("admin.jadwal-pelajaran.show", ":id") }}'.replace(':id', jadwalId))
                    .done(function (res) {
                        if (res.success) {
                            const d = res.data;
                            setSelect2Val('#selMapel', d.mapel_id, d.mapel_nama);
                            setSelect2Val('#selGuru', d.gtk_id, d.gtk_nama);
                            $('#slotRuangan').val(d.ruangan || '');
                            $('#slotCatatan').val(d.catatan || '');
                        }
                    });
            } else {
                setSelect2Val('#selMapel', null, '');
                setSelect2Val('#selGuru', null, '');
            }
        });

    $('#modalSlot').modal('show');
}

function loadMapelOptions() {
    return $.get('{{ route("admin.jadwal-pelajaran.mapel-options") }}', {
        tahun_pelajaran_id: TAHUN_ID,
        kelas_id: KELAS_ID,
    }).done(function (res) {
        const sel = $('#selMapel');
        sel.empty().append('<option value="">-- Pilih Mapel --</option>');
        if (res.success) {
            res.data.forEach(m => {
                const label = m.kode ? `[${m.kode}] ${m.nama}` : m.nama;
                sel.append(`<option value="${m.id}" data-kode="${m.kode}" data-kelompok="${m.kelompok}">${label}</option>`);
            });
        }
        sel.trigger('change');
    });
}

function loadGuruOptions(hari, jamKe, excludeId) {
    return $.get('{{ route("admin.jadwal-pelajaran.guru-options") }}', {
        tahun_pelajaran_id: TAHUN_ID,
        hari: hari,
        jam_ke: jamKe,
        semester: SEMESTER,
        exclude_id: excludeId || '',
    }).done(function (res) {
        const sel = $('#selGuru');
        sel.empty().append('<option value="">-- Pilih Guru --</option>');
        if (res.success) {
            res.data.forEach(g => {
                const label = g.kode ? `[${g.kode}] ${g.nama}` : g.nama;
                const konflikAttr = g.konflik ? ' data-konflik="1"' : '';
                const suffix = g.konflik ? ' ⚠️ (bentrok)' : '';
                sel.append(`<option value="${g.id}"${konflikAttr}>${label}${suffix}</option>`);
            });
        }
        sel.trigger('change');
    });
}

function setSelect2Val(selector, val, text) {
    const $sel = $(selector);
    if (!val) { $sel.val(null).trigger('change'); return; }
    if ($sel.find(`option[value="${val}"]`).length === 0) {
        $sel.append(new Option(text, val, true, true));
    }
    $sel.val(val).trigger('change');
}

// Warn on bentrok guru
$('#selGuru').on('change', function () {
    const opt = $(this).find('option:selected');
    const isBentrok = opt.data('konflik') == 1;
    const note = $('#konflikGuruNote');
    if (isBentrok) {
        note.find('span').text('Guru ini sudah mengajar di kelas lain pada jam ini.');
        note.removeClass('d-none');
    } else {
        note.addClass('d-none');
    }
});

// Simpan slot
$('#btnSimpanSlot').on('click', function () {
    const jadwalId = $('#slotId').val();
    const isEdit = !!jadwalId;
    const url = isEdit
        ? `/admin/jadwal-pelajaran/${jadwalId}`
        : '{{ route("admin.jadwal-pelajaran.store") }}';
    const method = isEdit ? 'PUT' : 'POST';

    const data = $('#formSlot').serialize();
    $('#btnSimpanSlot').prop('disabled', true);

    $.ajax({
        url, method, data,
        headers: { 'X-CSRF-TOKEN': CSRF },
        success: function (res) {
            if (res.success) {
                toastr.success(res.message);
                $('#modalSlot').modal('hide');
                refreshCell(res.data, $('#slotHari').val(), $('#slotJamKe').val());
            } else {
                toastr.error(res.message || 'Gagal menyimpan.');
            }
        },
        error: function (xhr) {
            toastr.error(xhr.responseJSON?.message || 'Terjadi kesalahan.');
        },
        complete: function () {
            $('#btnSimpanSlot').prop('disabled', false);
        }
    });
});

// Hapus slot
$('#btnHapusSlot').on('click', function () {
    const jadwalId = $('#slotId').val();
    if (!jadwalId) return;
    Swal.fire({
        title: 'Hapus slot ini?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Hapus',
    }).then(result => {
        if (!result.isConfirmed) return;
        $.ajax({
            url: `/admin/jadwal-pelajaran/${jadwalId}`,
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF },
            success: function (res) {
                if (res.success) {
                    toastr.success(res.message);
                    $('#modalSlot').modal('hide');
                    clearCell($('#slotHari').val(), $('#slotJamKe').val());
                }
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message || 'Gagal menghapus.');
            }
        });
    });
});

function refreshCell(data, hari, jamKe) {
    const $cell = $(`[data-hari="${hari}"][data-jam-ke="${jamKe}"]`);
    $cell.attr('data-jadwal-id', data.id).addClass('has-jadwal').removeClass('empty-cell');
    const colorIdx = Math.abs(hashStr(data.id)) % 12 + 1;
    $cell.html(`<div class="slot-card mc-${colorIdx}" data-id="${data.id}">
        <div class="slot-mapel">${data.mapel_kode || data.mapel_nama}</div>
        <div class="slot-guru">${data.gtk_nama}</div>
        ${data.ruangan ? `<div class="slot-room"><i class="fas fa-door-open" style="font-size:.65rem"></i> ${data.ruangan}</div>` : ''}
    </div>`);
}

function clearCell(hari, jamKe) {
    const $cell = $(`[data-hari="${hari}"][data-jam-ke="${jamKe}"]`);
    $cell.attr('data-jadwal-id', '').removeClass('has-jadwal').addClass('empty-cell');
    $cell.html('<div class="add-btn"><i class="fas fa-plus"></i></div>');
}

function hashStr(str) {
    let h = 0;
    for (let i = 0; i < str.length; i++) {
        h = Math.imul(31, h) + str.charCodeAt(i) | 0;
    }
    return Math.abs(h) % 12 + 1;
}

function capitalize(s) { return s.charAt(0).toUpperCase() + s.slice(1); }
</script>
@endsection
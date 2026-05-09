@extends('adminlte::page')

@section('title', 'Konfigurasi Jam Pelajaran')

@section('plugins.Sweetalert2', true)

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-clock"></i> Konfigurasi Jam Pelajaran</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('admin.jadwal-pelajaran.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Kembali ke Jadwal
                </a>
            </div>
        </div>
    </div>
@endsection

@section('content')
<div class="row">

    {{-- ===== KIRI: Form ===== --}}
    <div class="col-lg-5">

        {{-- Pilih Tahun --}}
        <div class="simansa-jjc-panel mb-3">
            <div class="simansa-jjc-panel__header">
                <i class="fas fa-calendar-alt"></i> Tahun Pelajaran
            </div>
            <div class="simansa-jjc-panel__body">
                <form method="GET" action="{{ route('admin.jadwal-jam-config.index') }}">
                    <select name="tahun_pelajaran_id" class="form-control select2" onchange="this.form.submit()">
                        <option value="">-- Pilih --</option>
                        @foreach($tahunList as $t)
                            <option value="{{ $t->id }}" {{ ($tahunDipilih && $tahunDipilih->id === $t->id) ? 'selected' : '' }}>
                                {{ $t->nama }}{{ $t->is_active ? ' (Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        @if($tahunDipilih)
        {{-- Generate Otomatis --}}
        <div class="simansa-jjc-panel mb-3">
            <div class="simansa-jjc-panel__header">
                <i class="fas fa-magic"></i> Generate Otomatis
                <small class="ml-1 text-muted font-weight-normal">— jadwal dihitung dari parameter di bawah</small>
            </div>
            <div class="simansa-jjc-panel__body">
                <form id="formGenerate">
                    <input type="hidden" name="tahun_pelajaran_id" value="{{ $tahunDipilih->id }}">

                    {{-- Baris 1: Jam Masuk + Durasi --}}
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label class="simansa-jjc-label">
                                <i class="fas fa-sign-in-alt text-primary"></i> Jam Masuk
                            </label>
                            <input type="time" name="jam_mulai" id="inpJamMasuk" class="form-control" value="07:00" required>
                        </div>
                        <div class="form-group col-6">
                            <label class="simansa-jjc-label">
                                <i class="fas fa-sign-out-alt text-danger"></i> Jam Pulang
                            </label>
                            <input type="time" name="jam_pulang" id="inpJamPulang" class="form-control" value="14:30" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="simansa-jjc-label">
                            <i class="fas fa-stopwatch text-info"></i> Durasi per Jam Pelajaran
                        </label>
                        <div class="input-group">
                            <input type="number" name="durasi_menit" id="inpDurasi" class="form-control" value="45" min="20" max="120" required>
                            <div class="input-group-append"><span class="input-group-text">menit</span></div>
                        </div>
                        <div class="simansa-jjc-presets mt-1">
                            <span>Preset:</span>
                            <button type="button" class="simansa-jjc-preset-btn" data-val="30">30'</button>
                            <button type="button" class="simansa-jjc-preset-btn" data-val="40">40'</button>
                            <button type="button" class="simansa-jjc-preset-btn active" data-val="45">45'</button>
                            <button type="button" class="simansa-jjc-preset-btn" data-val="50">50'</button>
                        </div>
                    </div>

                    <hr class="my-2">

                    {{-- Istirahat 1 --}}
                    <div class="simansa-jjc-istirahat-block" id="blkIst1">
                        <div class="simansa-jjc-istirahat-title">
                            <i class="fas fa-coffee text-warning"></i>
                            Istirahat 1
                            <small class="text-muted">— pagi menjelang siang</small>
                            <div class="custom-control custom-switch ml-auto">
                                <input type="checkbox" class="custom-control-input" id="ist1Active" checked>
                                <label class="custom-control-label" for="ist1Active"></label>
                            </div>
                        </div>
                        <div class="simansa-jjc-istirahat-body" id="ist1Body">
                            <div class="form-row">
                                <div class="form-group col-4">
                                    <label class="simansa-jjc-label">Setelah jam ke-</label>
                                    <input type="number" name="istirahat[0][setelah_jam]" id="ist1Setelah"
                                           class="form-control" value="3" min="1" max="15">
                                </div>
                                <div class="form-group col-4">
                                    <label class="simansa-jjc-label">Durasi</label>
                                    <div class="input-group">
                                        <input type="number" name="istirahat[0][durasi]" id="ist1Durasi"
                                               class="form-control" value="15" min="5" max="60">
                                        <div class="input-group-append"><span class="input-group-text">mnt</span></div>
                                    </div>
                                </div>
                                <div class="form-group col-4">
                                    <label class="simansa-jjc-label">Label</label>
                                    <input type="text" name="istirahat[0][label]" class="form-control"
                                           value="Istirahat" maxlength="30">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Istirahat 2 --}}
                    <div class="simansa-jjc-istirahat-block" id="blkIst2">
                        <div class="simansa-jjc-istirahat-title">
                            <i class="fas fa-mosque text-success"></i>
                            Istirahat 2
                            <small class="text-muted">— sholat &amp; makan</small>
                            <div class="custom-control custom-switch ml-auto">
                                <input type="checkbox" class="custom-control-input" id="ist2Active" checked>
                                <label class="custom-control-label" for="ist2Active"></label>
                            </div>
                        </div>
                        <div class="simansa-jjc-istirahat-body" id="ist2Body">
                            <div class="form-row">
                                <div class="form-group col-4">
                                    <label class="simansa-jjc-label">Setelah jam ke-</label>
                                    <input type="number" name="istirahat[1][setelah_jam]" id="ist2Setelah"
                                           class="form-control" value="6" min="1" max="15">
                                </div>
                                <div class="form-group col-4">
                                    <label class="simansa-jjc-label">Durasi</label>
                                    <div class="input-group">
                                        <input type="number" name="istirahat[1][durasi]" id="ist2Durasi"
                                               class="form-control" value="30" min="5" max="90">
                                        <div class="input-group-append"><span class="input-group-text">mnt</span></div>
                                    </div>
                                </div>
                                <div class="form-group col-4">
                                    <label class="simansa-jjc-label">Label</label>
                                    <input type="text" name="istirahat[1][label]" class="form-control"
                                           value="Istirahat Sholat" maxlength="30">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Preview --}}
                    <div class="simansa-jjc-preview" id="previewBox">
                        <div class="simansa-jjc-preview__label"><i class="fas fa-eye"></i> Preview</div>
                        <div id="previewContent" class="text-muted small">— ubah parameter untuk melihat preview —</div>
                    </div>

                    <button type="submit" class="btn btn-success btn-block mt-3" id="btnGenerate">
                        <i class="fas fa-magic"></i> Generate Jadwal
                    </button>
                    <small class="text-warning d-block mt-1">
                        <i class="fas fa-exclamation-triangle"></i>
                        Generate akan menghapus konfigurasi lama untuk tahun ini.
                    </small>
                </form>
            </div>
        </div>

        {{-- Tambah Manual (collapsed) --}}
        <div class="simansa-jjc-panel mb-3">
            <div class="simansa-jjc-panel__header simansa-jjc-panel__header--toggle" id="toggleManual" role="button">
                <span><i class="fas fa-plus-circle text-info"></i> Tambah Jam Manual</span>
                <i class="fas fa-chevron-down ml-auto toggle-icon"></i>
            </div>
            <div class="simansa-jjc-panel__body" id="manualBody" style="display:none">
                <form id="formTambahManual">
                    <input type="hidden" name="tahun_pelajaran_id" value="{{ $tahunDipilih->id }}">
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label class="simansa-jjc-label">Mulai</label>
                            <input type="time" name="waktu_mulai" class="form-control" required>
                        </div>
                        <div class="form-group col-6">
                            <label class="simansa-jjc-label">Selesai</label>
                            <input type="time" name="waktu_selesai" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="simansa-jjc-label">Label <small class="text-muted">(kosong = jam pelajaran biasa)</small></label>
                        <input type="text" name="label" class="form-control" placeholder="contoh: Istirahat, Jum'at" maxlength="50">
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

    {{-- ===== KANAN: Daftar Jam ===== --}}
    <div class="col-lg-7">
        <div class="simansa-jjc-panel">
            <div class="simansa-jjc-panel__header">
                <i class="fas fa-list-ol"></i> Daftar Jam
                @if($tahunDipilih)
                    <span class="badge badge-primary ml-2">{{ $tahunDipilih->nama }}</span>
                    @if($jamList->isNotEmpty())
                        <span class="badge badge-secondary ml-1">{{ $jamList->where('is_istirahat', false)->count() }} jam pelajaran</span>
                    @endif
                @endif
            </div>
            <div class="simansa-jjc-panel__body p-0">
                @if(!$tahunDipilih)
                    <div class="simansa-jjc-empty">
                        <i class="fas fa-calendar-alt"></i>
                        <p>Pilih tahun pelajaran terlebih dahulu</p>
                    </div>
                @elseif($jamList->isEmpty())
                    <div class="simansa-jjc-empty">
                        <i class="fas fa-clock"></i>
                        <p>Belum ada konfigurasi jam. Gunakan Generate Otomatis di kiri.</p>
                    </div>
                @else
                    <div class="simansa-jjc-jam-list" id="jamTableBody">
                        @foreach($jamList as $jam)
                            @if($jam->is_istirahat)
                            <div class="simansa-jjc-jam-row simansa-jjc-jam-row--break" data-id="{{ $jam->id }}">
                                <div class="simansa-jjc-jam-badge simansa-jjc-jam-badge--break">
                                    <i class="fas fa-coffee"></i>
                                </div>
                                <div class="simansa-jjc-jam-info">
                                    <span class="simansa-jjc-jam-label">{{ $jam->label ?? 'Istirahat' }}</span>
                                    <span class="simansa-jjc-jam-time">{{ substr($jam->waktu_mulai,0,5) }} – {{ substr($jam->waktu_selesai,0,5) }}</span>
                                </div>
                                <button class="simansa-jjc-del btn-hapus" data-id="{{ $jam->id }}" title="Hapus">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                            @else
                            <div class="simansa-jjc-jam-row" data-id="{{ $jam->id }}">
                                <div class="simansa-jjc-jam-badge">{{ $jam->jam_ke }}</div>
                                <div class="simansa-jjc-jam-info">
                                    <span class="simansa-jjc-jam-time">{{ substr($jam->waktu_mulai,0,5) }} – {{ substr($jam->waktu_selesai,0,5) }}</span>
                                    @if($jam->label)
                                        <span class="simansa-jjc-jam-label text-muted">{{ $jam->label }}</span>
                                    @endif
                                </div>
                                <button class="simansa-jjc-del btn-hapus" data-id="{{ $jam->id }}" title="Hapus">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
.simansa-jjc-panel{background:#fff;border-radius:18px;box-shadow:0 8px 24px rgba(15,23,42,.08);overflow:hidden}
.simansa-jjc-panel__header{display:flex;align-items:center;gap:.5rem;padding:.85rem 1.25rem;background:#f8fafc;border-bottom:1px solid #e2e8f0;font-size:.88rem;font-weight:700;color:#1f2a44}
.simansa-jjc-panel__header--toggle{cursor:pointer;user-select:none}
.simansa-jjc-panel__header--toggle:hover{background:#f1f5f9}
.simansa-jjc-panel__body{padding:1.1rem 1.25rem}
.simansa-jjc-panel__body.p-0{padding:0}
.simansa-jjc-label{font-size:.8rem;font-weight:600;color:#475569;margin-bottom:.3rem;display:block}
.simansa-jjc-presets{display:flex;gap:.35rem;align-items:center;font-size:.75rem;color:#64748b}
.simansa-jjc-preset-btn{background:#f1f5f9;border:1px solid #e2e8f0;border-radius:6px;padding:.15rem .55rem;font-size:.75rem;color:#475569;cursor:pointer;transition:all .12s}
.simansa-jjc-preset-btn:hover,.simansa-jjc-preset-btn.active{background:#3b82f6;border-color:#3b82f6;color:#fff}

.simansa-jjc-istirahat-block{border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;margin-bottom:.85rem}
.simansa-jjc-istirahat-title{display:flex;align-items:center;gap:.5rem;padding:.65rem 1rem;background:#f8fafc;font-size:.85rem;font-weight:700;color:#374151}
.simansa-jjc-istirahat-body{padding:.85rem 1rem}

.simansa-jjc-preview{background:#f0fdf4;border:1px solid #6ee7b7;border-radius:12px;padding:.75rem 1rem;margin-top:.75rem}
.simansa-jjc-preview__label{font-size:.75rem;font-weight:700;color:#059669;margin-bottom:.4rem;text-transform:uppercase;letter-spacing:.05em}
.simansa-jjc-preview-row{display:flex;gap:.5rem;align-items:center;font-size:.8rem;padding:.15rem 0}
.simansa-jjc-preview-badge{min-width:28px;height:22px;border-radius:5px;background:#3b82f6;color:#fff;font-size:.72rem;font-weight:700;display:flex;align-items:center;justify-content:center}
.simansa-jjc-preview-badge--break{background:#f59e0b}
.simansa-jjc-preview-time{color:#374151;font-size:.8rem}

.simansa-jjc-empty{text-align:center;padding:2.5rem 1rem;color:#94a3b8}
.simansa-jjc-empty i{font-size:2rem;margin-bottom:.5rem;display:block;color:#cbd5e1}
.simansa-jjc-empty p{margin:0;font-size:.9rem}

.simansa-jjc-jam-list{padding:.5rem .75rem}
.simansa-jjc-jam-row{display:flex;align-items:center;gap:.75rem;padding:.55rem .5rem;border-radius:10px;transition:background .1s}
.simansa-jjc-jam-row:hover{background:#f8fafc}
.simansa-jjc-jam-row--break{background:#fffbeb}
.simansa-jjc-jam-row--break:hover{background:#fef3c7}
.simansa-jjc-jam-badge{min-width:32px;height:32px;border-radius:8px;background:#3b82f6;color:#fff;font-size:.85rem;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.simansa-jjc-jam-badge--break{background:#f59e0b}
.simansa-jjc-jam-info{flex:1;display:flex;gap:.75rem;align-items:center;flex-wrap:wrap}
.simansa-jjc-jam-time{font-size:.85rem;color:#1e293b;font-weight:600}
.simansa-jjc-jam-label{font-size:.78rem;color:#64748b}
.simansa-jjc-del{background:none;border:none;color:#cbd5e1;padding:.2rem .4rem;border-radius:6px;cursor:pointer;transition:all .12s;flex-shrink:0}
.simansa-jjc-del:hover{background:#fee2e2;color:#dc2626}
</style>
@endsection

@section('js')
<!-- SweetAlert2 Fallback (jika plugin AdminLTE belum load) -->
<script>
if (typeof Swal === 'undefined') {
    document.write('<script src="https:\/\/cdn.jsdelivr.net\/npm\/sweetalert2@11\/dist\/sweetalert2.all.min.js"><\/script>');
}
</script>

<script>
$(function () {
    $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

    // Preset buttons
    $('.simansa-jjc-preset-btn').on('click', function () {
        $('.simansa-jjc-preset-btn').removeClass('active');
        $(this).addClass('active');
        $('#inpDurasi').val($(this).data('val')).trigger('change');
    });

    // Toggle istirahat blocks
    $('#ist1Active').on('change', function () {
        $('#ist1Body').toggle(this.checked);
        updatePreview();
    });
    $('#ist2Active').on('change', function () {
        $('#ist2Body').toggle(this.checked);
        updatePreview();
    });

    // Toggle manual panel
    $('#toggleManual').on('click', function () {
        $('#manualBody').slideToggle(150);
        $(this).find('.toggle-icon').toggleClass('fa-chevron-down fa-chevron-up');
    });

    // Preview on any input change
    $('#inpJamMasuk, #inpJamPulang, #inpDurasi, #ist1Setelah, #ist1Durasi, #ist2Setelah, #ist2Durasi')
        .on('change input', updatePreview);

    // Initial preview
    updatePreview();

    function timeToMin(t) {
        const parts = t.split(':');
        return parseInt(parts[0]) * 60 + parseInt(parts[1]);
    }
    function minToTime(m) {
        return String(Math.floor(m/60)).padStart(2,'0') + ':' + String(m%60).padStart(2,'0');
    }

    function updatePreview() {
        const masuk  = $('#inpJamMasuk').val();
        const pulang = $('#inpJamPulang').val();
        const durasi = parseInt($('#inpDurasi').val()) || 45;
        if (!masuk || !pulang) return;

        let cur  = timeToMin(masuk);
        const end = timeToMin(pulang);
        if (cur >= end) { $('#previewContent').html('<span class="text-danger">Jam masuk harus sebelum jam pulang.</span>'); return; }

        const breaks = {};
        if ($('#ist1Active').is(':checked')) {
            breaks[parseInt($('#ist1Setelah').val())] = { durasi: parseInt($('#ist1Durasi').val()), label: 'Istirahat 1' };
        }
        if ($('#ist2Active').is(':checked')) {
            breaks[parseInt($('#ist2Setelah').val())] = { durasi: parseInt($('#ist2Durasi').val()), label: 'Istirahat 2' };
        }

        let html = '';
        let jam  = 1;
        let i    = 1;
        while (i <= 20) {
            if (cur + durasi > end) break;
            const s = minToTime(cur);
            cur += durasi;
            const e = minToTime(cur);
            html += `<div class="simansa-jjc-preview-row">
                <span class="simansa-jjc-preview-badge">${jam}</span>
                <span class="simansa-jjc-preview-time">${s} – ${e}</span>
            </div>`;
            jam++;
            if (breaks[i]) {
                const bDur = breaks[i].durasi;
                const bs = minToTime(cur);
                cur += bDur;
                const be = minToTime(cur);
                html += `<div class="simansa-jjc-preview-row">
                    <span class="simansa-jjc-preview-badge simansa-jjc-preview-badge--break"><i class="fas fa-coffee" style="font-size:.6rem"></i></span>
                    <span class="simansa-jjc-preview-time">${bs} – ${be} &mdash; ${breaks[i].label} (${bDur} mnt)</span>
                </div>`;
            }
            i++;
        }
        html += `<div class="mt-2 text-muted" style="font-size:.78rem"><strong>${jam-1} jam pelajaran</strong> · Selesai ${minToTime(cur)}</div>`;
        $('#previewContent').html(html);
    }

    // Generate form submit
    $('#formGenerate').on('submit', function (e) {
        e.preventDefault();
        // Build data object explicitly — avoid removeAttr() which breaks jQuery selectors
        // containing brackets, and prevents permanent attribute loss on cancel
        const data = {
            _token: '{{ csrf_token() }}',
            tahun_pelajaran_id: $('#formGenerate input[name="tahun_pelajaran_id"]').val(),
            jam_mulai:    $('#inpJamMasuk').val(),
            jam_pulang:   $('#inpJamPulang').val(),
            durasi_menit: $('#inpDurasi').val(),
        };
        if ($('#ist1Active').is(':checked')) {
            data['istirahat[0][setelah_jam]'] = $('#ist1Setelah').val();
            data['istirahat[0][durasi]']      = $('#ist1Durasi').val();
            data['istirahat[0][label]']       = $('#ist1Body input[type=text]').val();
        }
        if ($('#ist2Active').is(':checked')) {
            data['istirahat[1][setelah_jam]'] = $('#ist2Setelah').val();
            data['istirahat[1][durasi]']      = $('#ist2Durasi').val();
            data['istirahat[1][label]']       = $('#ist2Body input[type=text]').val();
        }
        Swal.fire({
            title: 'Generate ulang?',
            text: 'Konfigurasi jam lama untuk tahun ini akan dihapus.',
            icon: 'warning', showCancelButton: true,
            confirmButtonText: 'Ya, generate', cancelButtonText: 'Batal'
        }).then(result => {
            if (!result.isConfirmed) return;
            $('#btnGenerate').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Generating…');
            $.ajax({
                url: '{{ route("admin.jadwal-jam-config.generate") }}',
                method: 'POST',
                data: data,
                success: function (res) {
                    if (res.success) {
                        toastr.success(res.message);
                        setTimeout(() => location.reload(), 700);
                    } else {
                        toastr.error(res.message || 'Gagal generate.');
                    }
                },
                error: function (xhr) {
                    const errs = xhr.responseJSON?.errors;
                    const msg  = errs ? Object.values(errs).flat().join(' | ') : (xhr.responseJSON?.message || 'Terjadi kesalahan.');
                    toastr.error(msg);
                    $('#btnGenerate').prop('disabled', false).html('<i class="fas fa-magic"></i> Generate Jadwal');
                }
            });
        });
    });

    // Tambah manual form
    $('#formTambahManual').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: '{{ route("admin.jadwal-jam-config.store") }}',
            method: 'POST',
            data: $(this).serialize(),
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function (res) {
                if (res.success) {
                    toastr.success(res.message);
                    setTimeout(() => location.reload(), 500);
                }
            },
            error: function (xhr) { toastr.error(xhr.responseJSON?.message || 'Gagal.'); }
        });
    });

    // Hapus baris
    $(document).on('click', '.btn-hapus', function () {
        const id = $(this).data('id');
        const $row = $(this).closest('[data-id]');
        Swal.fire({
            title: 'Hapus baris ini?', icon: 'question',
            showCancelButton: true, confirmButtonColor: '#dc3545',
            confirmButtonText: 'Hapus', cancelButtonText: 'Batal'
        }).then(result => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: `/admin/jadwal-jam-config/${id}`,
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: function (res) {
                    if (res.success) { $row.remove(); toastr.success('Baris dihapus.'); }
                },
                error: function () { toastr.error('Gagal menghapus.'); }
            });
        });
    });
});
</script>
@endsection
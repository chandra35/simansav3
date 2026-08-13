@extends('adminlte::page')

@section('title', 'Pengaturan Absensi')

@section('plugins.Sweetalert2', true)

@section('content_header')
    <h1><i class="fas fa-cog"></i> Pengaturan Absensi</h1>
@stop

@section('content')
@if(session('success'))<div class="alert alert-success alert-dismissible"><button class="close" data-dismiss="alert">&times;</button><i class="fas fa-check-circle mr-1"></i>{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><strong>Pengaturan belum dapat disimpan.</strong><ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="card bg-gradient-primary text-white border-0 shadow-sm mb-3 settings-hero">
    <div class="card-body">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between">
            <div class="pr-lg-4 mb-3 mb-lg-0">
                <div class="text-uppercase small font-weight-bold mb-1">Pusat Modul Presensi</div>
                <h2 class="h4 mb-2">Pengaturan Operasional Registrasi dan Kiosk</h2>
                <p class="text-muted mb-0">
                    Atur jadwal mingguan GTK dan siswa, ketelitian pengenalan wajah, lokasi, hari libur, dan kesiapan kiosk dari satu halaman.
                </p>
            </div>
            <div class="settings-hero-note">
                <i class="fas fa-layer-group mr-1"></i>
                Jadwal diproses berdasarkan waktu server Asia/Jakarta.
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('admin.absensi.settings.operational-schedules.update') }}" class="card card-primary card-outline operational-schedule-card mb-3">
    @csrf @method('PUT')
    <div class="card-header schedule-header">
        <div><h3 class="card-title"><i class="fas fa-calendar-alt mr-1"></i> Jadwal Operasional Kiosk</h3><small>Mode masuk dan pulang dipilih otomatis. Di luar jendela ini backend menolak pencatatan.</small></div>
        <div class="schedule-legend"><span><i class="fas fa-circle text-success"></i> Tepat waktu</span><span><i class="fas fa-circle text-warning"></i> Terlambat</span><span><i class="fas fa-circle text-info"></i> Pulang</span></div>
    </div>
    <div class="card-body">
        <div class="alert alert-info py-2"><i class="fas fa-shield-alt mr-1"></i>Hari libur menutup kiosk secara otomatis. Absensi siswa dari kiosk merupakan presensi kedatangan harian dan tidak menggantikan absensi mapel.</div>
        <ul class="nav nav-pills schedule-tabs mb-3" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-toggle="pill" href="#scheduleGtk"><i class="fas fa-user-tie mr-1"></i>GTK</a></li>
            <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#scheduleSiswa"><i class="fas fa-user-graduate mr-1"></i>Siswa</a></li>
        </ul>
        <div class="tab-content">
            @foreach(['gtk' => 'GTK', 'siswa' => 'Siswa'] as $type => $typeLabel)
            <div class="tab-pane fade {{ $type === 'gtk' ? 'show active' : '' }}" id="schedule{{ ucfirst($type) }}">
                <div class="table-responsive schedule-table-wrap"><table class="table table-sm schedule-table mb-0">
                    <thead><tr><th>Hari</th><th class="text-center">Aktif</th><th>Masuk dibuka</th><th>Tepat waktu s.d.</th><th>Masuk ditutup</th><th>Pulang dibuka</th><th>Pulang ditutup</th></tr></thead>
                    <tbody>
                    @foreach(range(1, 7) as $day)
                        @php $schedule = $operationalSchedules->get($type)?->get($day); @endphp
                        <tr class="{{ $schedule?->is_active ? '' : 'is-inactive' }}">
                            <td><strong>{{ [1=>'Senin',2=>'Selasa',3=>'Rabu',4=>'Kamis',5=>'Jumat',6=>'Sabtu',7=>'Minggu'][$day] }}</strong></td>
                            <td class="text-center"><input type="hidden" name="schedules[{{ $type }}][{{ $day }}][active]" value="0"><div class="custom-control custom-switch"><input type="checkbox" class="custom-control-input schedule-active" id="active-{{ $type }}-{{ $day }}" name="schedules[{{ $type }}][{{ $day }}][active]" value="1" @checked($schedule?->is_active)><label class="custom-control-label" for="active-{{ $type }}-{{ $day }}"></label></div></td>
                            @foreach(['check_in_open'=>'06:00','on_time_until'=>'07:00','check_in_close'=>'08:00','check_out_open'=>'15:00','check_out_close'=>'23:59'] as $field => $fallback)
                            <td><input type="time" class="form-control form-control-sm" name="schedules[{{ $type }}][{{ $day }}][{{ $field }}]" value="{{ old("schedules.{$type}.{$day}.{$field}", $schedule?->shortTime($field) ?? $fallback) }}" required></td>
                            @endforeach
                        </tr>
                    @endforeach
                    </tbody>
                </table></div>
            </div>
            @endforeach
        </div>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center flex-wrap"><small class="text-muted"><i class="fas fa-info-circle mr-1"></i>Urutan wajib: buka masuk ≤ tepat waktu ≤ tutup masuk &lt; buka pulang ≤ tutup pulang.</small><button class="btn btn-primary"><i class="fas fa-save mr-1"></i>Simpan Jadwal Operasional</button></div>
</form>

<div class="row">
    {{-- SETTINGS --}}
    <div class="col-lg-6">
        <form method="POST" action="{{ route('admin.absensi.settings.update') }}">
            @csrf
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-clock"></i> Pengaturan Waktu & Sistem</h3>
                </div>
                <div class="card-body">
                    @foreach($settings as $group => $items)
                        <h6 class="text-uppercase text-muted border-bottom pb-2 mb-3">
                            {{ ucfirst($group) }}
                        </h6>
                        @if($group === 'waktu')
                            <div class="alert alert-light border mb-3">
                                <strong>Tips operasional:</strong>
                                Samakan jam GTK dengan aturan kerja madrasah agar status hadir dan terlambat dari kiosk lebih akurat.
                            </div>
                        @elseif($group === 'kiosk')
                            <div class="alert alert-light border mb-3">
                                <strong>Mode gerbang:</strong>
                                gunakan detection interval yang stabil dan cooldown yang cukup agar scan tidak ganda saat antrean ramai.
                            </div>
                        @endif
                        @foreach($items as $setting)
                            <div class="form-group row">
                                <label class="col-sm-5 col-form-label">
                                    {{ $setting->label }}
                                    @if($setting->description)
                                        <br><small class="text-muted">{{ $setting->description }}</small>
                                    @endif
                                </label>
                                <div class="col-sm-7">
                                    @if($setting->type === 'boolean')
                                        <select name="settings[{{ $setting->key }}]" class="form-control form-control-sm">
                                            <option value="1" {{ $setting->value == '1' ? 'selected' : '' }}>Ya</option>
                                            <option value="0" {{ $setting->value == '0' ? 'selected' : '' }}>Tidak</option>
                                        </select>
                                    @elseif($setting->type === 'time')
                                        <input type="time" name="settings[{{ $setting->key }}]" class="form-control form-control-sm" value="{{ $setting->value }}">
                                    @elseif($setting->type === 'integer')
                                        <input type="number" name="settings[{{ $setting->key }}]" class="form-control form-control-sm" value="{{ $setting->value }}">
                                    @else
                                        <input type="text" name="settings[{{ $setting->key }}]" class="form-control form-control-sm" value="{{ $setting->value }}">
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endforeach
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Pengaturan</button>
                </div>
            </div>
        </form>
    </div>

    {{-- LOKASI --}}
    <div class="col-lg-6">
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-map-marker-alt"></i> Lokasi Absensi</h3>
                <div class="card-tools">
                    <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#modalAddLocation">
                        <i class="fas fa-plus"></i> Tambah
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Kode</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($locations as $loc)
                            <tr>
                                <td>{{ $loc->nama }}</td>
                                <td><code>{{ $loc->kode }}</code></td>
                                <td>
                                    <span class="badge badge-{{ $loc->is_active ? 'success' : 'secondary' }}">
                                        {{ $loc->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td>
                                    <form method="POST"
                                          action="{{ route('admin.absensi.location.destroy', $loc) }}"
                                          class="d-inline js-confirm-form"
                                          data-confirm-title="Hapus lokasi absensi?"
                                          data-confirm-text="Lokasi {{ $loc->nama }} akan dihapus dari daftar lokasi absensi."
                                          data-confirm-button="Ya, hapus lokasi">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-danger" title="Hapus lokasi">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">Belum ada lokasi</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- HARI LIBUR --}}
        <div class="card card-danger card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-calendar-times"></i> Hari Libur / Tanggal Merah</h3>
                <div class="card-tools">
                    <form method="POST"
                          action="{{ route('admin.absensi.hari-libur.seed') }}"
                          class="d-inline js-confirm-form"
                          data-confirm-title="Tambahkan hari libur nasional 2026?"
                          data-confirm-text="Sistem akan menambahkan daftar hari libur nasional 2026 yang belum tersedia."
                          data-confirm-button="Ya, tambahkan">
                        @csrf
                        <button type="submit" class="btn btn-xs btn-outline-danger">
                            <i class="fas fa-magic"></i> Seed 2026
                        </button>
                    </form>
                    <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#modalAddHoliday">
                        <i class="fas fa-plus"></i> Tambah
                    </button>
                </div>
            </div>
            <div class="card-body p-0" style="max-height:400px; overflow-y:auto;">
                <table class="table table-sm table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Nama</th>
                            <th>Jenis</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hariLibur as $hl)
                            <tr>
                                <td>{{ $hl->tanggal->format('d/m/Y') }}</td>
                                <td>{{ $hl->nama }}</td>
                                <td><span class="badge badge-danger">{{ ucfirst(str_replace('_', ' ', $hl->jenis)) }}</span></td>
                                <td>
                                    <form method="POST"
                                          action="{{ route('admin.absensi.hari-libur.destroy', $hl) }}"
                                          class="d-inline js-confirm-form"
                                          data-confirm-title="Hapus hari libur?"
                                          data-confirm-text="{{ $hl->nama }} ({{ $hl->tanggal->format('d/m/Y') }}) akan dihapus dari kalender absensi."
                                          data-confirm-button="Ya, hapus">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-danger" title="Hapus hari libur">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">Belum ada data. Klik "Seed 2026" untuk menambah otomatis.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Add Location Modal --}}
<div class="modal fade" id="modalAddLocation" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.absensi.location.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-info"><h5 class="modal-title">Tambah Lokasi</h5></div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Lokasi *</label>
                        <input type="text" name="nama" class="form-control" required placeholder="Pos Satpam Utama">
                    </div>
                    <div class="form-group">
                        <label>Kode *</label>
                        <input type="text" name="kode" class="form-control" required placeholder="POS-1">
                    </div>
                    <div class="form-group">
                        <label>Alamat</label>
                        <textarea name="alamat" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-4"><div class="form-group"><label>Latitude</label><input type="number" step="any" name="latitude" class="form-control"></div></div>
                        <div class="col-4"><div class="form-group"><label>Longitude</label><input type="number" step="any" name="longitude" class="form-control"></div></div>
                        <div class="col-4"><div class="form-group"><label>Radius (m)</label><input type="number" name="radius_meter" class="form-control" value="100"></div></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Add Holiday Modal --}}
<div class="modal fade" id="modalAddHoliday" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.absensi.hari-libur.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-danger"><h5 class="modal-title text-white">Tambah Hari Libur</h5></div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Tanggal *</label>
                        <input type="date" name="tanggal" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Nama *</label>
                        <input type="text" name="nama" class="form-control" required placeholder="Hari Raya Idul Fitri">
                    </div>
                    <div class="form-group">
                        <label>Jenis *</label>
                        <select name="jenis" class="form-control" required>
                            <option value="nasional">Nasional</option>
                            <option value="keagamaan">Keagamaan</option>
                            <option value="sekolah">Sekolah</option>
                            <option value="cuti_bersama">Cuti Bersama</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="isRecurring" name="is_recurring" value="1">
                        <label class="custom-control-label" for="isRecurring">Berulang setiap tahun</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>
@stop

@section('css')
<style>
.settings-hero{border-radius:18px}.settings-hero .text-muted{color:rgba(255,255,255,.9)!important}.settings-hero-note{max-width:340px;padding:12px 15px;border:1px solid rgba(255,255,255,.32);border-radius:12px;background:rgba(255,255,255,.13)}
.operational-schedule-card{border-radius:.65rem;box-shadow:0 5px 18px rgba(15,23,42,.06)}.schedule-header{display:flex;justify-content:space-between;align-items:center;gap:.75rem}.schedule-header h3{float:none;margin:0}.schedule-header small{display:block;color:#64748b;margin-top:.2rem}.schedule-legend{display:flex;gap:.75rem;flex-wrap:wrap;font-size:.72rem;color:#64748b}.schedule-tabs{gap:.35rem}.schedule-tabs .nav-link{padding:.45rem 1rem;border:1px solid #dbe4ef}.schedule-table-wrap{border:1px solid #e2e8f0;border-radius:.5rem}.schedule-table{min-width:900px}.schedule-table th{background:#f8fafc;border-top:0;color:#475569;font-size:.68rem;text-transform:uppercase;vertical-align:middle}.schedule-table td{vertical-align:middle}.schedule-table tr.is-inactive{background:#f8fafc}.schedule-table tr.is-inactive td:not(:first-child):not(:nth-child(2)){opacity:.55}.schedule-table .form-control{min-width:105px}.schedule-table .custom-switch{padding-left:2.7rem}.operational-schedule-card .card-footer{gap:.75rem}
@media(max-width:991px){.settings-hero-note{max-width:none;width:100%}.schedule-header{align-items:flex-start;flex-direction:column}}
@media(max-width:575px){.operational-schedule-card .card-footer .btn{width:100%}}
</style>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.schedule-active').forEach(function (toggle) {
        const sync = function () { toggle.closest('tr').classList.toggle('is-inactive', !toggle.checked); };
        toggle.addEventListener('change', sync);
        sync();
    });
    document.querySelectorAll('.js-confirm-form').forEach(function (form) {
        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            const result = await Swal.fire({
                icon: 'warning',
                title: form.dataset.confirmTitle || 'Lanjutkan tindakan?',
                text: form.dataset.confirmText || 'Pastikan data yang dipilih sudah benar.',
                showCancelButton: true,
                reverseButtons: true,
                focusCancel: true,
                confirmButtonText: form.dataset.confirmButton || 'Ya, lanjutkan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                allowOutsideClick: false,
            });

            if (result.isConfirmed) {
                HTMLFormElement.prototype.submit.call(form);
            }
        });
    });
});
</script>
@stop

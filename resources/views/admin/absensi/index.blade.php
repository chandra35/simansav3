@extends('adminlte::page')

@section('title', 'Absensi GTK')

@section('css')
<style>
    .attendance-hero {
        display: grid;
        grid-template-columns: minmax(0, 1.35fr) minmax(280px, .85fr);
        gap: 1.1rem;
        align-items: stretch;
        margin-bottom: 1.1rem;
    }

    .attendance-hero__main {
        background: linear-gradient(135deg, rgba(37, 99, 235, .16), rgba(13, 148, 136, .10));
        border: 1px solid rgba(148, 163, 184, .16);
        border-radius: 26px;
        padding: 1.35rem 1.45rem;
        box-shadow: 0 20px 45px rgba(15, 23, 42, .06);
    }

    .attendance-hero__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        color: #334155;
        font-size: .82rem;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        margin-bottom: .65rem;
    }

    .attendance-hero__title {
        font-size: 2rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
        margin: 0 0 .55rem 0;
    }

    .attendance-hero__subtitle {
        color: #475569;
        font-size: 1rem;
        line-height: 1.7;
        margin: 0;
        max-width: 760px;
    }

    .attendance-hero__side {
        display: grid;
        gap: .9rem;
    }

    .attendance-hero-chip {
        background: rgba(255, 255, 255, .92);
        border: 1px solid rgba(148, 163, 184, .18);
        border-radius: 20px;
        padding: 1rem 1.1rem;
        box-shadow: 0 16px 35px rgba(15, 23, 42, .06);
    }

    .attendance-hero-chip__label {
        display: block;
        color: #64748b;
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        margin-bottom: .35rem;
    }

    .attendance-hero-chip__value {
        display: block;
        color: #0f172a;
        font-size: 1.45rem;
        font-weight: 800;
        line-height: 1.2;
    }

    .attendance-kiosk-btn {
        align-self: start;
        justify-self: end;
    }

    .attendance-filter-panel {
        background: linear-gradient(180deg, rgba(248, 250, 252, .96), rgba(255, 255, 255, .98));
        border: 1px solid rgba(148, 163, 184, .18);
        border-radius: 20px;
        padding: 1rem 1rem .85rem;
        margin-bottom: 1rem;
    }

    .attendance-filter-label {
        display: block;
        font-size: .82rem;
        font-weight: 700;
        color: #475569;
        margin-bottom: .4rem;
    }

    .attendance-filter-meta {
        display: flex;
        justify-content: flex-end;
        gap: .6rem;
        flex-wrap: wrap;
    }

    .attendance-stat-card {
        position: relative;
        overflow: hidden;
        min-height: 166px;
        border: 0;
        border-radius: 22px;
        padding: 1.2rem 1.2rem 1rem;
        color: #fff;
        box-shadow: 0 24px 50px rgba(15, 23, 42, .10);
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }

    .attendance-stat-card::after {
        content: "";
        position: absolute;
        right: -32px;
        bottom: -40px;
        width: 132px;
        height: 132px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .12);
    }

    .attendance-stat-card--success { background: linear-gradient(135deg, #10b981, #34d399); }
    .attendance-stat-card--warning { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
    .attendance-stat-card--info { background: linear-gradient(135deg, #3b82f6, #60a5fa); }
    .attendance-stat-card--primary { background: linear-gradient(135deg, #4f46e5, #6366f1); }
    .attendance-stat-card--danger { background: linear-gradient(135deg, #ef4444, #f87171); }
    .attendance-stat-card--secondary { background: linear-gradient(135deg, #475569, #64748b); }

    .attendance-stat-card__icon {
        width: 56px;
        height: 56px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, .16);
        font-size: 1.25rem;
        position: relative;
        z-index: 1;
        flex: 0 0 56px;
    }

    .attendance-stat-card__body {
        position: relative;
        z-index: 1;
        flex: 1 1 auto;
        min-width: 0;
    }

    .attendance-stat-card__label {
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        opacity: .9;
        margin-bottom: .55rem;
    }

    .attendance-stat-card__value {
        font-size: 2rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: .7rem;
    }

    .attendance-stat-card__desc {
        opacity: .92;
        line-height: 1.5;
        font-size: .92rem;
    }

    .attendance-management-card {
        border: 0;
        border-radius: 24px;
        box-shadow: 0 22px 48px rgba(15, 23, 42, .08);
        overflow: hidden;
    }

    .attendance-management-card .card-header {
        background: linear-gradient(135deg, rgba(37, 99, 235, .98), rgba(13, 148, 136, .9));
        color: #fff;
        border-bottom: 0;
        padding: 1rem 1.25rem;
    }

    .attendance-table-note {
        color: #64748b;
        font-size: .92rem;
        line-height: 1.5;
        margin-bottom: 1rem;
    }

    .attendance-empty-state {
        color: #64748b;
        padding: 2rem 1rem !important;
    }

    @media (max-width: 991.98px) {
        .attendance-hero {
            grid-template-columns: 1fr;
        }

        .attendance-hero__side {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .attendance-filter-meta {
            justify-content: flex-start;
        }
    }

    @media (max-width: 575.98px) {
        .attendance-hero__title {
            font-size: 1.7rem;
        }

        .attendance-hero__side {
            grid-template-columns: 1fr;
        }

        .attendance-stat-card {
            flex-direction: column;
            gap: .9rem;
        }

        .attendance-kiosk-btn {
            justify-self: stretch;
        }
    }
</style>
@stop

@section('content_header')
    <div class="attendance-hero">
        <div class="attendance-hero__main">
            <div class="attendance-hero__eyebrow">
                <i class="fas fa-clipboard-check"></i>
                Presensi GTK
            </div>
            <h1 class="attendance-hero__title">Absensi GTK</h1>
            <p class="attendance-hero__subtitle">
                Pantau kehadiran harian GTK, cek status masuk dan pulang, lalu kelola input manual dari satu halaman operasional yang lebih rapi.
            </p>
        </div>
        <div class="attendance-hero__side">
            <div class="attendance-hero-chip">
                <span class="attendance-hero-chip__label">Tanggal Presensi</span>
                <span class="attendance-hero-chip__value">{{ \Carbon\Carbon::parse($tanggal)->isoFormat('D MMMM YYYY') }}</span>
            </div>
            <div class="attendance-hero-chip">
                <span class="attendance-hero-chip__label">Kondisi Hari Ini</span>
                <span class="attendance-hero-chip__value">{{ $isHoliday ? 'Libur / Weekend' : 'Hari Kerja Aktif' }}</span>
            </div>
            <a href="{{ route('admin.absensi.kiosk') }}" class="btn btn-dark attendance-kiosk-btn" target="_blank">
                <i class="fas fa-desktop"></i> Buka Mode Kiosk
            </a>
        </div>
    </div>
@stop

@section('content')
    {{-- Filter Tanggal --}}
    <div class="attendance-filter-panel">
        <div class="row align-items-end">
            <div class="col-md-4 mb-3">
                <label class="attendance-filter-label">
                    <i class="fas fa-calendar-alt mr-1"></i> Pilih Tanggal
                </label>
                <form method="GET" action="{{ route('admin.absensi.index') }}" class="input-group">
                    <input type="date" name="tanggal" class="form-control" value="{{ $tanggal }}">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Tampilkan</button>
                    </div>
                </form>
            </div>
            <div class="col-md-8 mb-3">
                <div class="attendance-filter-meta">
                    @if($isHoliday)
                        <span class="badge badge-danger p-2"><i class="fas fa-calendar-times"></i> Hari Libur / Weekend</span>
                    @endif
                    <span class="badge badge-light p-2"><i class="fas fa-calendar"></i> {{ \Carbon\Carbon::parse($tanggal)->isoFormat('dddd, D MMMM YYYY') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row mb-4">
        <div class="col-md-6 col-xl-2 mb-4">
            <div class="attendance-stat-card attendance-stat-card--success">
                <div class="attendance-stat-card__icon"><i class="fas fa-check"></i></div>
                <div class="attendance-stat-card__body">
                    <div class="attendance-stat-card__label">Hadir</div>
                    <div class="attendance-stat-card__value">{{ $stats['hadir'] }}</div>
                    <div class="attendance-stat-card__desc">GTK yang hadir normal pada tanggal terpilih.</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-2 mb-4">
            <div class="attendance-stat-card attendance-stat-card--warning">
                <div class="attendance-stat-card__icon"><i class="fas fa-clock"></i></div>
                <div class="attendance-stat-card__body">
                    <div class="attendance-stat-card__label">Terlambat</div>
                    <div class="attendance-stat-card__value">{{ $stats['terlambat'] }}</div>
                    <div class="attendance-stat-card__desc">GTK yang check-in melewati batas waktu hadir.</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-2 mb-4">
            <div class="attendance-stat-card attendance-stat-card--info">
                <div class="attendance-stat-card__icon"><i class="fas fa-envelope"></i></div>
                <div class="attendance-stat-card__body">
                    <div class="attendance-stat-card__label">Izin</div>
                    <div class="attendance-stat-card__value">{{ $stats['izin'] }}</div>
                    <div class="attendance-stat-card__desc">GTK yang tercatat izin pada hari ini.</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-2 mb-4">
            <div class="attendance-stat-card attendance-stat-card--primary">
                <div class="attendance-stat-card__icon"><i class="fas fa-medkit"></i></div>
                <div class="attendance-stat-card__body">
                    <div class="attendance-stat-card__label">Sakit</div>
                    <div class="attendance-stat-card__value">{{ $stats['sakit'] }}</div>
                    <div class="attendance-stat-card__desc">GTK yang tercatat sakit pada hari ini.</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-2 mb-4">
            <div class="attendance-stat-card attendance-stat-card--danger">
                <div class="attendance-stat-card__icon"><i class="fas fa-times"></i></div>
                <div class="attendance-stat-card__body">
                    <div class="attendance-stat-card__label">Alpa</div>
                    <div class="attendance-stat-card__value">{{ $stats['alpa'] }}</div>
                    <div class="attendance-stat-card__desc">GTK yang belum memiliki presensi tanpa keterangan.</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-2 mb-4">
            <div class="attendance-stat-card attendance-stat-card--secondary">
                <div class="attendance-stat-card__icon"><i class="fas fa-briefcase"></i></div>
                <div class="attendance-stat-card__body">
                    <div class="attendance-stat-card__label">Dinas / Cuti</div>
                    <div class="attendance-stat-card__value">{{ $stats['dinas_luar'] + $stats['cuti'] }}</div>
                    <div class="attendance-stat-card__desc">Akumulasi dinas luar dan cuti pada tanggal ini.</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="card attendance-management-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title"><i class="fas fa-table"></i> Data Absensi Harian</h3>
            <div>
                <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#modalManual">
                    <i class="fas fa-plus"></i> Input Manual
                </button>
                <a href="{{ route('admin.absensi.rekap', ['bulan' => \Carbon\Carbon::parse($tanggal)->month, 'tahun' => \Carbon\Carbon::parse($tanggal)->year]) }}" class="btn btn-sm btn-info">
                    <i class="fas fa-chart-bar"></i> Rekap Bulan Ini
                </a>
            </div>
        </div>
        <div class="card-body">
            <p class="attendance-table-note">
                Tabel ini menampilkan status presensi masuk dan pulang GTK pada tanggal yang dipilih, termasuk metode presensi dan confidence pengenalan wajah bila tersedia.
            </p>
        <div class="table-responsive p-0">
            <table class="table table-hover table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th width="40">No</th>
                        <th>Nama</th>
                        <th>NIP</th>
                        <th>Masuk</th>
                        <th>Pulang</th>
                        <th>Status</th>
                        <th>Metode</th>
                        <th>Lokasi</th>
                        <th>Confidence</th>
                        <th width="80">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($absensis as $i => $absensi)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>
                                @if($absensi->foto_masuk)
                                    <img src="{{ asset('storage/' . $absensi->foto_masuk) }}" class="img-circle mr-1" width="25" height="25" style="object-fit:cover;">
                                @endif
                                {{ $absensi->user->gtk->nama_lengkap ?? $absensi->user->name }}
                            </td>
                            <td><small>{{ $absensi->user->gtk->nip ?? '-' }}</small></td>
                            <td>
                                <span class="text-success font-weight-bold">{{ $absensi->waktu_masuk_formatted }}</span>
                            </td>
                            <td>
                                <span class="text-info">{{ $absensi->waktu_pulang_formatted }}</span>
                                @if($absensi->durasi_kerja)
                                    <br><small class="text-muted">{{ $absensi->durasi_kerja }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $absensi->status_badge }}">{{ ucfirst(str_replace('_', ' ', $absensi->status)) }}</span>
                                @if($absensi->status_pulang)
                                    <br><small class="badge badge-light">{{ ucfirst(str_replace('_', ' ', $absensi->status_pulang)) }}</small>
                                @endif
                            </td>
                            <td>
                                <small>
                                    <i class="fas fa-{{ $absensi->metode_masuk === 'face' ? 'user-shield' : 'keyboard' }}"></i>
                                    {{ ucfirst($absensi->metode_masuk) }}
                                </small>
                            </td>
                            <td><small>{{ $absensi->location->nama ?? '-' }}</small></td>
                            <td>
                                @if($absensi->face_confidence_masuk)
                                    <small>{{ number_format($absensi->face_confidence_masuk * 100, 1) }}%</small>
                                @else
                                    <small class="text-muted">-</small>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-xs btn-warning" onclick="editAbsensi('{{ $absensi->id }}')" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center attendance-empty-state">
                                @if($isHoliday)
                                    <i class="fas fa-calendar-times fa-2x mb-2"></i><br>
                                    Hari libur - tidak ada data absensi
                                @else
                                    <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                    Belum ada data absensi untuk tanggal ini
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        </div>
    </div>

    {{-- Manual Input Modal --}}
    <div class="modal fade" id="modalManual" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.absensi.manual') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header bg-success">
                        <h5 class="modal-title"><i class="fas fa-keyboard"></i> Input Absensi Manual</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="callout callout-warning">
                            <small><i class="fas fa-exclamation-triangle"></i> Input manual dicatat dalam audit log. Gunakan jika kamera/kiosk bermasalah.</small>
                        </div>
                        <div class="form-group">
                            <label>GTK <span class="text-danger">*</span></label>
                            <select name="user_id" class="form-control select2" required>
                                <option value="">-- Pilih GTK --</option>
                            </select>
                        </div>
                        <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Jam Masuk</label>
                                    <input type="time" name="waktu_masuk" class="form-control">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Jam Pulang</label>
                                    <input type="time" name="waktu_pulang" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-control" required>
                                <option value="hadir">Hadir</option>
                                <option value="terlambat">Terlambat</option>
                                <option value="izin">Izin</option>
                                <option value="sakit">Sakit</option>
                                <option value="dinas_luar">Dinas Luar</option>
                                <option value="cuti">Cuti</option>
                                <option value="alpa">Alpa</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Catatan</label>
                            <textarea name="catatan" class="form-control" rows="2" maxlength="500"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Bukti (opsional)</label>
                            <input type="file" name="file_bukti" class="form-control-file" accept=".jpg,.jpeg,.png,.pdf">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div class="modal fade" id="modalEdit" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formEdit" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Absensi</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Status <span class="text-danger">*</span></label>
                            <select name="status" id="editStatus" class="form-control" required>
                                <option value="hadir">Hadir</option>
                                <option value="terlambat">Terlambat</option>
                                <option value="izin">Izin</option>
                                <option value="sakit">Sakit</option>
                                <option value="dinas_luar">Dinas Luar</option>
                                <option value="cuti">Cuti</option>
                                <option value="alpa">Alpa</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Catatan</label>
                            <textarea name="catatan" id="editCatatan" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Alasan Edit <span class="text-danger">*</span></label>
                            <input type="text" name="edit_reason" class="form-control" required placeholder="Wajib isi alasan perubahan">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    // Load GTK list for manual input
    async function loadGtkList() {
        try {
            const response = await fetch('{{ route("admin.gtk.data") }}', {
                headers: { 'Accept': 'application/json' }
            });
            const result = await response.json();
            const select = document.querySelector('select[name="user_id"]');
            if (result.data) {
                result.data.forEach(gtk => {
                    const opt = document.createElement('option');
                    opt.value = gtk.user_id;
                    opt.textContent = `${gtk.nama_lengkap} ${gtk.nip ? '(' + gtk.nip + ')' : ''}`;
                    select.appendChild(opt);
                });
            }
        } catch(e) {
            console.error('Error loading GTK:', e);
        }
    }

    function editAbsensi(id) {
        document.getElementById('formEdit').action = '{{ url("admin/absensi") }}/' + id;
        $('#modalEdit').modal('show');
    }

    document.addEventListener('DOMContentLoaded', loadGtkList);
</script>
@stop

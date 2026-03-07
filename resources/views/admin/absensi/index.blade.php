@extends('adminlte::page')

@section('title', 'Absensi GTK')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-clipboard-check"></i> Absensi GTK</h1>
        <div>
            <a href="{{ route('admin.absensi.kiosk') }}" class="btn btn-dark" target="_blank">
                <i class="fas fa-desktop"></i> Buka Mode Kiosk
            </a>
        </div>
    </div>
@stop

@section('content')
    {{-- Filter Tanggal --}}
    <div class="row mb-3">
        <div class="col-md-4">
            <form method="GET" action="{{ route('admin.absensi.index') }}" class="input-group">
                <input type="date" name="tanggal" class="form-control" value="{{ $tanggal }}">
                <div class="input-group-append">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Tampilkan</button>
                </div>
            </form>
        </div>
        <div class="col-md-8 text-right">
            @if($isHoliday)
                <span class="badge badge-danger p-2"><i class="fas fa-calendar-times"></i> Hari Libur / Weekend</span>
            @endif
            <span class="badge badge-light p-2"><i class="fas fa-calendar"></i> {{ \Carbon\Carbon::parse($tanggal)->isoFormat('dddd, D MMMM YYYY') }}</span>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row">
        <div class="col-lg-2 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['hadir'] }}</h3>
                    <p>Hadir</p>
                </div>
                <div class="icon"><i class="fas fa-check"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['terlambat'] }}</h3>
                    <p>Terlambat</p>
                </div>
                <div class="icon"><i class="fas fa-clock"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['izin'] }}</h3>
                    <p>Izin</p>
                </div>
                <div class="icon"><i class="fas fa-envelope"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $stats['sakit'] }}</h3>
                    <p>Sakit</p>
                </div>
                <div class="icon"><i class="fas fa-medkit"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['alpa'] }}</h3>
                    <p>Alpa</p>
                </div>
                <div class="icon"><i class="fas fa-times"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $stats['dinas_luar'] + $stats['cuti'] }}</h3>
                    <p>Dinas/Cuti</p>
                </div>
                <div class="icon"><i class="fas fa-briefcase"></i></div>
            </div>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title"><i class="fas fa-table"></i> Data Absensi</h3>
            <div>
                <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#modalManual">
                    <i class="fas fa-plus"></i> Input Manual
                </button>
                <a href="{{ route('admin.absensi.rekap', ['bulan' => \Carbon\Carbon::parse($tanggal)->month, 'tahun' => \Carbon\Carbon::parse($tanggal)->year]) }}" class="btn btn-sm btn-info">
                    <i class="fas fa-chart-bar"></i> Rekap Bulan Ini
                </a>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
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
                            <td colspan="10" class="text-center text-muted py-4">
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

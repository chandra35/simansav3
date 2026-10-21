@extends('adminlte::page')

@section('title', 'Pengaturan Presensi Sholat')

@section('content_header')
<div class="row align-items-center">
    <div class="col-sm-7">
        <h1 class="m-0"><i class="fas fa-mosque text-primary mr-2"></i>Pengaturan Presensi Sholat</h1>
    </div>
    <div class="col-sm-5">
        <ol class="breadcrumb float-sm-right mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.sholat.input') }}">Presensi Sholat</a></li>
            <li class="breadcrumb-item active">Pengaturan</li>
        </ol>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid pb-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <!-- Kolom Pengaturan Sholat Aktif -->
        <div class="col-lg-7">
            <div class="card card-primary card-outline shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-sliders-h mr-1 text-primary"></i> Daftar Sholat & Status Pemantauan
                    </h3>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover table-striped mb-0 align-middle">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Nama Sholat</th>
                                <th>Waktu & Toleransi</th>
                                <th>Target Gender</th>
                                <th class="text-center" style="width: 120px;">Status</th>
                                <th class="text-center" style="width: 130px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($settings as $idx => $s)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td>
                                    <strong class="text-dark">{{ $s->nama_sholat }}</strong>
                                    <div class="small text-muted">Kode: <code>{{ $s->kode }}</code></div>
                                </td>
                                <td>
                                    <div><i class="far fa-clock text-info mr-1"></i>{{ substr($s->jam_mulai, 0, 5) }} - {{ substr($s->jam_selesai, 0, 5) }} WIB</div>
                                    <div class="small text-muted">Batas masbuq: {{ substr($s->batas_masbuq, 0, 5) }} WIB</div>
                                </td>
                                <td>
                                    @if($s->gender_target === 'L')
                                        <span class="badge badge-info"><i class="fas fa-mars mr-1"></i>Khusus Putra</span>
                                    @elseif($s->gender_target === 'P')
                                        <span class="badge badge-danger"><i class="fas fa-venus mr-1"></i>Khusus Putri</span>
                                    @else
                                        <span class="badge badge-secondary"><i class="fas fa-venus-mars mr-1"></i>Semua</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('admin.sholat.settings.toggle', $s->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $s->is_active ? 'btn-success' : 'btn-outline-secondary' }}" title="Klik untuk mengubah status">
                                            <i class="fas {{ $s->is_active ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                                            {{ $s->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#modalEditSetting{{ $s->id }}">
                                        <i class="fas fa-edit mr-1"></i> Ubah
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal Edit Sholat Setting -->
                            <div class="modal fade" id="modalEditSetting{{ $s->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.sholat.settings.update', $s->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header bg-primary text-white">
                                                <h5 class="modal-title font-weight-bold">
                                                    <i class="fas fa-edit mr-1"></i> Edit Pengaturan: {{ $s->nama_sholat }}
                                                </h5>
                                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label>Nama Sholat / Agenda</label>
                                                    <input type="text" name="nama_sholat" class="form-control" value="{{ $s->nama_sholat }}" required>
                                                </div>
                                                <div class="form-row">
                                                    <div class="form-group col-md-6">
                                                        <label>Jam Mulai</label>
                                                        <input type="time" name="jam_mulai" class="form-control" value="{{ substr($s->jam_mulai, 0, 5) }}" required>
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        <label>Jam Selesai</label>
                                                        <input type="time" name="jam_selesai" class="form-control" value="{{ substr($s->jam_selesai, 0, 5) }}" required>
                                                    </div>
                                                </div>
                                                <div class="form-row">
                                                    <div class="form-group col-md-6">
                                                        <label>Batas Masbuq</label>
                                                        <input type="time" name="batas_masbuq" class="form-control" value="{{ substr($s->batas_masbuq, 0, 5) }}" required>
                                                        <small class="text-muted">Waktu maksimal dianggap masbuq sebelum dihitung munfarid.</small>
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        <label>Sasaran Gender</label>
                                                        <select name="gender_target" class="form-control">
                                                            <option value="ALL" {{ $s->gender_target === 'ALL' ? 'selected' : '' }}>Semua Siswa/Siswi</option>
                                                            <option value="L" {{ $s->gender_target === 'L' ? 'selected' : '' }}>Khusus Putra (Laki-laki)</option>
                                                            <option value="P" {{ $s->gender_target === 'P' ? 'selected' : '' }}>Khusus Putri (Perempuan)</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label>Deskripsi / Petunjuk</label>
                                                    <textarea name="deskripsi" class="form-control" rows="2">{{ $s->deskripsi }}</textarea>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="checkActive{{ $s->id }}" {{ $s->is_active ? 'checked' : '' }}>
                                                    <label class="form-check-label font-weight-bold" for="checkActive{{ $s->id }}">Aktifkan Pemantauan Sholat Ini</label>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Kolom Petugas Seksi Kerohanian Kelas -->
        <div class="col-lg-5">
            <div class="card card-success card-outline shadow-sm mb-4">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-user-shield mr-1 text-success"></i> Tetapkan Seksi Kerohanian Kelas
                    </h3>
                </div>
                <div class="card-body">
                    <p class="text-muted small">
                        Siswa yang ditugaskan sebagai <strong>Seksi Kerohanian</strong> akan memiliki hak akses menginput kehadiran sholat khusus untuk rekan-rekan sekelasnya.
                    </p>
                    <form action="{{ route('admin.sholat.settings.petugas.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>Pilih Kelas / Rombel</label>
                            <select name="kelas_id" id="selectKelas" class="form-control select2" required>
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($kelasList as $k)
                                    <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Pilih Siswa (Petugas)</label>
                            <select name="siswa_id" id="selectSiswa" class="form-control select2" required disabled>
                                <option value="">-- Pilih Kelas Terlebih Dahulu --</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Keterangan (Opsional)</label>
                            <input type="text" name="keterangan" class="form-control" placeholder="Contoh: Petugas Utama / Seksi Ibadah">
                        </div>
                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fas fa-user-plus mr-1"></i> Simpan Penugasan Petugas
                        </button>
                    </form>
                </div>
            </div>

            <!-- Daftar Petugas Kelas Aktif -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-users-cog mr-1"></i> Petugas Kerohanian Aktif ({{ $petugasList->count() }})
                    </h3>
                </div>
                <div class="card-body p-0 table-responsive" style="max-height: 400px;">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Kelas</th>
                                <th>Nama Petugas</th>
                                <th class="text-center" style="width: 70px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($petugasList as $p)
                            <tr>
                                <td><span class="badge badge-light border">{{ $p->kelas->nama_kelas ?? '-' }}</span></td>
                                <td>
                                    <div class="font-weight-bold text-dark">{{ $p->siswa->nama_lengkap ?? '-' }}</div>
                                    <div class="small text-muted">{{ $p->siswa->nisn ?? $p->siswa->nis_lokal ?? '' }}</div>
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('admin.sholat.settings.petugas.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Hapus status petugas siswa ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-outline-danger" title="Hapus Penugasan">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">Belum ada petugas kerohanian kelas yang ditetapkan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    if ($.fn.select2) {
        $('.select2').select2({ theme: 'bootstrap4' });
    }

    $('#selectKelas').on('change', function() {
        const kelasId = $(this).val();
        const $siswaSelect = $('#selectSiswa');

        $siswaSelect.empty().append('<option value="">Memuat siswa...</option>').prop('disabled', true);

        if (!kelasId) {
            $siswaSelect.empty().append('<option value="">-- Pilih Kelas Terlebih Dahulu --</option>');
            return;
        }

        $.ajax({
            url: '{{ route("admin.sholat.settings.siswa-by-kelas") }}',
            type: 'GET',
            data: { kelas_id: kelasId },
            success: function(data) {
                $siswaSelect.empty().append('<option value="">-- Pilih Siswa --</option>');
                if (data.length > 0) {
                    data.forEach(function(s) {
                        const genderBadge = s.jenis_kelamin === 'L' ? ' [L]' : ' [P]';
                        $siswaSelect.append('<option value="' + s.id + '">' + s.nama_lengkap + genderBadge + '</option>');
                    });
                    $siswaSelect.prop('disabled', false);
                } else {
                    $siswaSelect.append('<option value="">Tidak ada siswa di kelas ini</option>');
                }
            },
            error: function() {
                $siswaSelect.empty().append('<option value="">Gagal memuat data siswa</option>');
            }
        });
    });
});
</script>
@stop

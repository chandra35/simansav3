@extends('adminlte::page')

@section('title', 'Detail Kelas')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-chalkboard-teacher"></i> Detail Kelas</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.kelas.index') }}">Kelas</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    {{-- Kelas Info Header --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card card-widget widget-user-2">
                <div class="widget-user-header bg-gradient-primary">
                    <div class="widget-user-image">
                        <span class="elevation-2 bg-white rounded-circle p-3">
                            <i class="fas fa-chalkboard-teacher fa-2x text-primary"></i>
                        </span>
                    </div>
                    <h3 class="widget-user-username">{{ $kelas->nama_lengkap ?? $kelas->nama_kelas }}</h3>
                    <h5 class="widget-user-desc">
                        {{ $kelas->kode_kelas }} | 
                        Tingkat {{ $kelas->getTingkatRomawi() }} | 
                        {{ $kelas->tahunPelajaran->nama ?? '-' }}
                    </h5>
                </div>
                <div class="card-footer p-0">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <span class="nav-link">
                                <strong>Kurikulum:</strong>
                                <span class="float-right">{{ $kelas->kurikulum->formatted_name ?? '-' }}</span>
                            </span>
                        </li>
                        @if($kelas->jurusan)
                        <li class="nav-item">
                            <span class="nav-link">
                                <strong>Jurusan:</strong>
                                <span class="float-right"><span class="badge badge-info">{{ $kelas->jurusan->nama_jurusan }}</span></span>
                            </span>
                        </li>
                        @endif
                        <li class="nav-item">
                            <span class="nav-link">
                                <strong>Wali Kelas:</strong>
                                <span class="float-right">
                                    {{ $kelas->waliKelas ? $kelas->waliKelas->name : 'Belum ditugaskan' }}
                                    @can('assign-wali-kelas')
                                        <button type="button" class="btn btn-xs btn-primary ml-2" data-toggle="modal" data-target="#modalWaliKelas">
                                            <i class="fas fa-edit"></i> Ubah
                                        </button>
                                    @endcan
                                </span>
                            </span>
                        </li>
                        <li class="nav-item">
                            <span class="nav-link">
                                <strong>Ruang Kelas:</strong>
                                <span class="float-right">{{ $kelas->ruang_kelas ?? '-' }}</span>
                            </span>
                        </li>
                        <li class="nav-item">
                            <span class="nav-link">
                                <strong>Status:</strong>
                                <span class="float-right">
                                    {!! $kelas->is_active ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-secondary">Non-Aktif</span>' !!}
                                </span>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistics --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total_siswa'] }}/{{ $kelas->kapasitas }}</h3>
                    <p>Total Siswa</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-{{ $kelas->capacity_badge_color }}">
                <div class="inner">
                    <h3>{{ $stats['sisa_tempat'] }}</h3>
                    <p>Sisa Tempat</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chair"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $stats['laki_laki'] }}</h3>
                    <p>Laki-Laki</p>
                </div>
                <div class="icon">
                    <i class="fas fa-male"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['perempuan'] }}</h3>
                    <p>Perempuan</p>
                </div>
                <div class="icon">
                    <i class="fas fa-female"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Siswa List --}}
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-users"></i> Daftar Siswa</h3>
            @can('assign-siswa-kelas')
                @if($kelas && $kelas->id && !$kelas->isFull())
                <div class="card-tools">
                    <a href="{{ route('admin.kelas.assign-siswa', $kelas->id) }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Tambah Siswa
                    </a>
                </div>
                @elseif($kelas && $kelas->id && $kelas->isFull())
                <div class="card-tools">
                    <span class="badge badge-danger">Kelas Penuh</span>
                </div>
                @endif
            @endcan
        </div>
        <div class="card-body">
            @if($kelas->siswaAktif->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="8%">Absen</th>
                                <th width="12%">NISN</th>
                                <th>Nama Lengkap</th>
                                <th width="8%">JK</th>
                                <th width="12%">Tanggal Masuk</th>
                                @can('remove-siswa-kelas')
                                <th width="10%">Aksi</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kelas->siswaAktif->sortBy('pivot.nomor_urut_absen') as $index => $siswa)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-primary">{{ $siswa->pivot->nomor_urut_absen }}</span>
                                    </td>
                                    <td>{{ $siswa->nisn }}</td>
                                    <td>
                                        <a href="{{ route('admin.siswa.show', $siswa->uuid) }}" target="_blank">
                                            {{ $siswa->nama_lengkap }}
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        @if($siswa->jenis_kelamin == 'L')
                                            <span class="badge badge-primary"><i class="fas fa-male"></i> L</span>
                                        @else
                                            <span class="badge badge-danger"><i class="fas fa-female"></i> P</span>
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($siswa->pivot->tanggal_masuk)->format('d/m/Y') }}</td>
                                    @can('remove-siswa-kelas')
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-danger btn-remove-siswa" 
                                            data-siswa-id="{{ $siswa->uuid }}"
                                            data-siswa-nama="{{ $siswa->nama_lengkap }}">
                                            <i class="fas fa-user-minus"></i>
                                        </button>
                                    </td>
                                    @endcan
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Belum ada siswa di kelas ini.
                    @can('assign-siswa-kelas')
                        @if($kelas && $kelas->id)
                            <a href="{{ route('admin.kelas.assign-siswa', $kelas->id) }}" class="alert-link">Klik di sini untuk menambahkan siswa.</a>
                        @endif
                    @endcan
                </div>
            @endif
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="card">
        <div class="card-footer">
            <div class="row">
                <div class="col-md-6">
                    <a href="{{ route('admin.kelas.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
                <div class="col-md-6 text-right">
                    @can('edit-kelas')
                        <a href="{{ route('admin.kelas.edit', $kelas->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit Kelas
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Assign Wali Kelas --}}
    @can('assign-wali-kelas')
    <div class="modal fade" id="modalWaliKelas" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formWaliKelas">
                    @csrf
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title"><i class="fas fa-user-tie"></i> Tugaskan Wali Kelas</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="wali_kelas_id">Pilih Wali Kelas</label>
                            <select class="form-control" id="wali_kelas_id" name="wali_kelas_id" required>
                                <option value="">Pilih Guru/Wali Kelas</option>
                                @foreach(\App\Models\User::role(['Wali Kelas', 'Guru'])->orderBy('name')->get() as $guru)
                                    <option value="{{ $guru->uuid }}" {{ $kelas->wali_kelas_id == $guru->uuid ? 'selected' : '' }}>
                                        {{ $guru->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Remove Siswa --}}
    <div class="modal fade" id="modalRemoveSiswa" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formRemoveSiswa">
                    @csrf
                    <input type="hidden" id="remove_siswa_id" name="siswa_id">
                    <div class="modal-header bg-danger">
                        <h5 class="modal-title"><i class="fas fa-user-minus"></i> Keluarkan Siswa dari Kelas</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> 
                            Anda akan mengeluarkan <strong id="siswa-nama-display"></strong> dari kelas ini.
                        </div>
                        
                        <div class="form-group">
                            <label for="tanggal_keluar">Tanggal Keluar <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal_keluar" name="tanggal_keluar" required>
                        </div>

                        <div class="form-group">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="">Pilih Status</option>
                                <option value="naik_kelas">Naik Kelas</option>
                                <option value="tinggal_kelas">Tinggal Kelas</option>
                                <option value="lulus">Lulus</option>
                                <option value="keluar">Keluar</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="catatan">Catatan</label>
                            <textarea class="form-control" id="catatan" name="catatan" rows="3" 
                                placeholder="Alasan atau catatan perpindahan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Keluarkan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // Assign Wali Kelas
            $('#formWaliKelas').on('submit', function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: "{{ route('admin.kelas.wali-kelas', $kelas->id) }}",
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#modalWaliKelas').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                        }).then(() => location.reload());
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                        });
                    }
                });
            });

            // Remove Siswa - Show Modal
            $('.btn-remove-siswa').on('click', function() {
                let siswaId = $(this).data('siswa-id');
                let siswaNama = $(this).data('siswa-nama');
                
                $('#remove_siswa_id').val(siswaId);
                $('#siswa-nama-display').text(siswaNama);
                $('#tanggal_keluar').val('{{ date("Y-m-d") }}');
                $('#modalRemoveSiswa').modal('show');
            });

            // Remove Siswa - Submit
            $('#formRemoveSiswa').on('submit', function(e) {
                e.preventDefault();
                let siswaId = $('#remove_siswa_id').val();
                
                $.ajax({
                    url: "{{ route('admin.kelas.siswa.remove', ['kelas' => $kelas->id, 'siswa' => ':siswa']) }}".replace(':siswa', siswaId),
                    type: 'DELETE',
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#modalRemoveSiswa').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                        }).then(() => location.reload());
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                        });
                    }
                });
            });
        });
    </script>
@stop

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
            <div class="card card-primary card-outline">
                <div class="card-body">
                    <div class="row align-items-center">
                        {{-- Icon --}}
                        <div class="col-auto">
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" 
                                 style="width: 70px; height: 70px;">
                                <i class="fas fa-chalkboard-teacher fa-2x text-white"></i>
                            </div>
                        </div>
                        
                        {{-- Info Kelas --}}
                        <div class="col">
                            <h3 class="mb-1">
                                <strong>{{ $kelas->nama_lengkap ?? $kelas->nama_kelas }}</strong>
                                {!! $kelas->is_active ? '<span class="badge badge-success ml-2">Aktif</span>' : '<span class="badge badge-secondary ml-2">Non-Aktif</span>' !!}
                            </h3>
                            <p class="text-muted mb-2">
                                <i class="fas fa-barcode"></i> {{ $kelas->kode_kelas }} | 
                                <i class="fas fa-layer-group"></i> Tingkat {{ $kelas->getTingkatRomawi() }} | 
                                <i class="fas fa-calendar-alt"></i> {{ $kelas->tahunPelajaran->nama ?? '-' }}
                            </p>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        <i class="fas fa-book"></i> <strong>Kurikulum:</strong> {{ $kelas->kurikulum->formatted_name ?? '-' }}
                                    </small>
                                </div>
                                @if($kelas->jurusan)
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        <i class="fas fa-graduation-cap"></i> <strong>Jurusan:</strong> 
                                        <span class="badge badge-info">{{ $kelas->jurusan->nama_jurusan }}</span>
                                    </small>
                                </div>
                                @endif
                            </div>
                            <div class="row mt-1">
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        <i class="fas fa-user-tie"></i> <strong>Wali Kelas:</strong> 
                                        {{ $kelas->waliKelas ? $kelas->waliKelas->name : 'Belum ditugaskan' }}
                                        @can('assign-wali-kelas')
                                            <button type="button" class="btn btn-xs btn-outline-primary ml-1" data-toggle="modal" data-target="#modalWaliKelas">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        @endcan
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        <i class="fas fa-door-open"></i> <strong>Ruang Kelas:</strong> {{ $kelas->ruang_kelas ?? '-' }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
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
            <div class="card-tools">
                @can('assign-siswa-kelas')
                    @if($kelas && $kelas->id && !$kelas->isFull())
                        <button type="button" class="btn btn-sm btn-success mr-1" data-toggle="modal" data-target="#modalTambahSiswa">
                            <i class="fas fa-user-plus"></i> Tambah Siswa
                        </button>
                    @elseif($kelas && $kelas->id && $kelas->isFull())
                        <span class="badge badge-danger mr-1">Kelas Penuh</span>
                    @endif
                @endcan
                @can('remove-siswa-kelas')
                    @if($kelas->siswaAktif->count() > 0)
                        <button type="button" class="btn btn-sm btn-danger" id="btnKosongkanKelas">
                            <i class="fas fa-user-times"></i> Kosongkan Kelas
                        </button>
                    @endif
                @endcan
            </div>
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
                                        <a href="{{ route('admin.siswa.show', $siswa->id) }}" target="_blank">
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
                                            data-siswa-id="{{ $siswa->id }}"
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
                    <i class="fas fa-info-circle"></i> Belum ada siswa di kelas ini. Gunakan tombol <strong>"Tambah Siswa"</strong> di atas untuk menambahkan siswa.
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

    {{-- Modal Tambah Siswa --}}
    @can('assign-siswa-kelas')
    <div class="modal fade" id="modalTambahSiswa" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-gradient-success text-white">
                    <h4 class="modal-title font-weight-bold">
                        <i class="fas fa-user-plus mr-2"></i>Tambah Siswa ke Kelas
                    </h4>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <!-- Info Banner -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card bg-light border-0 shadow-sm">
                                <div class="card-body py-3">
                                    <div class="row text-center">
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-center justify-content-center">
                                                <div class="icon-circle bg-primary text-white mr-3">
                                                    <i class="fas fa-users"></i>
                                                </div>
                                                <div class="text-left">
                                                    <small class="text-muted d-block">Total Siswa</small>
                                                    <h5 class="mb-0 font-weight-bold">{{ $stats['total_siswa'] }}</h5>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-center justify-content-center">
                                                <div class="icon-circle bg-success text-white mr-3">
                                                    <i class="fas fa-chair"></i>
                                                </div>
                                                <div class="text-left">
                                                    <small class="text-muted d-block">Kapasitas</small>
                                                    <h5 class="mb-0 font-weight-bold">{{ $kelas->kapasitas }}</h5>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-center justify-content-center">
                                                <div class="icon-circle {{ $stats['sisa_tempat'] > 0 ? 'bg-warning' : 'bg-danger' }} text-white mr-3">
                                                    <i class="fas fa-plus-circle"></i>
                                                </div>
                                                <div class="text-left">
                                                    <small class="text-muted d-block">Sisa Tempat</small>
                                                    <h5 class="mb-0 font-weight-bold {{ $stats['sisa_tempat'] > 0 ? 'text-success' : 'text-danger' }}">
                                                        {{ $stats['sisa_tempat'] }}
                                                    </h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Nav tabs dengan style modern -->
                    <ul class="nav nav-pills nav-fill mb-4" id="addSiswaTab" role="tablist">
                        <li class="nav-item mr-2">
                            <a class="nav-link active rounded-lg py-3 shadow-sm" id="select-tab" data-toggle="tab" href="#tabSelect" role="tab">
                                <i class="fas fa-mouse-pointer fa-lg"></i>
                                <div class="mt-2">
                                    <strong>Pilih Siswa</strong>
                                    <small class="d-block text-muted">Cari & pilih manual</small>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item ml-2">
                            <a class="nav-link rounded-lg py-3 shadow-sm" id="nisn-tab" data-toggle="tab" href="#tabNISN" role="tab">
                                <i class="fas fa-list-ol fa-lg"></i>
                                <div class="mt-2">
                                    <strong>Input NISN (Bulk)</strong>
                                    <small class="d-block text-muted">Import banyak sekaligus</small>
                                </div>
                            </a>
                        </li>
                    </ul>

                    <!-- Tab panes -->
                    <div class="tab-content">
                        <!-- Tab Dual Listbox -->
                        <div class="tab-pane fade show active" id="tabSelect" role="tabpanel">
                            <form id="formTambahSiswaSelect">
                                @csrf
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body">
                                        <!-- Search Box -->
                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text bg-primary text-white">
                                                            <i class="fas fa-search"></i>
                                                        </span>
                                                    </div>
                                                    <input type="text" class="form-control" id="searchSiswa" 
                                                           placeholder="Cari nama atau NISN siswa...">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Dual Listbox -->
                                        <div class="row">
                                            <!-- Siswa Tersedia (Kiri) -->
                                            <div class="col-md-5">
                                                <div class="card bg-light">
                                                    <div class="card-header bg-info text-white py-2">
                                                        <h6 class="mb-0">
                                                            <i class="fas fa-users"></i> Siswa Tersedia
                                                            <span class="badge badge-light text-info float-right" id="availableCount">0</span>
                                                        </h6>
                                                    </div>
                                                    <div class="card-body p-0" style="height: 400px; overflow-y: auto;">
                                                        <div class="list-group list-group-flush" id="availableSiswaList">
                                                            <div class="text-center py-5 text-muted">
                                                                <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                                                                <p>Memuat data siswa...</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Tombol Pindah (Tengah) -->
                                            <div class="col-md-2 d-flex flex-column justify-content-center align-items-center">
                                                <button type="button" class="btn btn-primary btn-block mb-2" id="btnAddSelected" title="Tambahkan yang dipilih">
                                                    <i class="fas fa-chevron-right"></i>
                                                </button>
                                                <button type="button" class="btn btn-success btn-block mb-2" id="btnAddAll" title="Tambahkan semua">
                                                    <i class="fas fa-angle-double-right"></i>
                                                </button>
                                                <button type="button" class="btn btn-warning btn-block mb-2" id="btnRemoveSelected" title="Hapus yang dipilih">
                                                    <i class="fas fa-chevron-left"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-block" id="btnRemoveAll" title="Hapus semua">
                                                    <i class="fas fa-angle-double-left"></i>
                                                </button>
                                            </div>

                                            <!-- Siswa Dipilih (Kanan) -->
                                            <div class="col-md-5">
                                                <div class="card bg-light">
                                                    <div class="card-header bg-success text-white py-2">
                                                        <h6 class="mb-0">
                                                            <i class="fas fa-check-circle"></i> Siswa Dipilih
                                                            <span class="badge badge-light text-success float-right" id="selectedCount">0</span>
                                                        </h6>
                                                    </div>
                                                    <div class="card-body p-0" style="height: 400px; overflow-y: auto;">
                                                        <div class="list-group list-group-flush" id="selectedSiswaList">
                                                            <div class="text-center py-5 text-muted">
                                                                <i class="fas fa-hand-pointer fa-2x mb-3"></i>
                                                                <p>Belum ada siswa dipilih</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-right mt-4">
                                            <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">
                                                <i class="fas fa-times"></i> Batal
                                            </button>
                                            <button type="submit" class="btn btn-success px-4 shadow">
                                                <i class="fas fa-check"></i> Tambahkan <span id="submitCount">0</span> Siswa
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Tab NISN Bulk -->
                        <div class="tab-pane fade" id="tabNISN" role="tabpanel">
                            <form id="formTambahSiswaNISN">
                                @csrf
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body">
                                        <div class="alert alert-info border-0 shadow-sm">
                                            <h6 class="alert-heading font-weight-bold">
                                                <i class="fas fa-lightbulb"></i> Petunjuk Penggunaan:
                                            </h6>
                                            <ul class="mb-0 pl-3">
                                                <li>Copy daftar NISN dari Excel atau file lain</li>
                                                <li>Paste di kotak teks di bawah ini</li>
                                                <li><strong>Satu NISN per baris</strong> (tekan Enter untuk baris baru)</li>
                                                <li>NISN harus berupa <strong>10 digit angka</strong></li>
                                                <li>Karakter non-angka akan otomatis dihapus</li>
                                            </ul>
                                        </div>

                                        <div class="form-group">
                                            <label for="nisn_list" class="font-weight-bold">
                                                <i class="fas fa-list-ol text-primary"></i> Daftar NISN <span class="text-danger">*</span>
                                            </label>
                                            <textarea class="form-control font-monospace" id="nisn_list" name="nisn_list" rows="12" 
                                                      style="resize: vertical; font-size: 14px;"
                                                      placeholder="Contoh:&#10;0123456789&#10;0123456790&#10;0123456791&#10;0123456792&#10;..." required></textarea>
                                            <small class="form-text text-muted">
                                                <i class="fas fa-keyboard"></i> Anda bisa paste langsung dari Excel. Setiap baris = 1 NISN.
                                            </small>
                                        </div>

                                        <div class="text-right mt-4">
                                            <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">
                                                <i class="fas fa-times"></i> Batal
                                            </button>
                                            <button type="submit" class="btn btn-success px-4 shadow">
                                                <i class="fas fa-upload"></i> Proses Bulk Import
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endcan

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

@section('css')
    <style>
        /* Dual Listbox Styling */
        #modalTambahSiswa .list-group-item {
            cursor: pointer;
            transition: all 0.2s;
            border-left: 3px solid transparent;
            padding: 10px 15px;
        }
        #modalTambahSiswa .list-group-item:hover {
            background-color: #e9ecef;
            border-left-color: #007bff;
        }
        #modalTambahSiswa .list-group-item.active {
            background-color: #cfe2ff;
            border-left-color: #0d6efd;
            color: #000;
            font-weight: 500;
        }
        #modalTambahSiswa .siswa-name {
            font-weight: 500;
            color: #212529;
        }
        #modalTambahSiswa .siswa-nisn {
            font-size: 12px;
            color: #6c757d;
        }
        #modalTambahSiswa .siswa-gender {
            font-size: 11px;
        }
        #modalTambahSiswa .card-body::-webkit-scrollbar {
            width: 8px;
        }
        #modalTambahSiswa .card-body::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        #modalTambahSiswa .card-body::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        #modalTambahSiswa .card-body::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Icon Circle - Hanya untuk modal */
        #modalTambahSiswa .icon-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        /* Modal Styling - Hanya untuk modal tambah siswa */
        #modalTambahSiswa .modal-dialog.modal-xl {
            max-width: 900px;
        }
        #modalTambahSiswa .modal-content {
            border-radius: 15px;
            overflow: hidden;
        }
        #modalTambahSiswa .modal-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            padding: 20px 30px;
        }
        #modalTambahSiswa .modal-body {
            background-color: #f8f9fa;
        }

        /* Nav Pills Modern - Hanya untuk modal */
        #modalTambahSiswa .nav-pills .nav-link {
            border: 2px solid #e0e0e0;
            background-color: #fff;
            color: #6c757d;
            transition: all 0.3s;
        }
        #modalTambahSiswa .nav-pills .nav-link:hover {
            border-color: #28a745;
            color: #28a745;
            transform: translateY(-2px);
        }
        #modalTambahSiswa .nav-pills .nav-link.active {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border-color: transparent;
            color: #fff;
            transform: translateY(0);
        }

        /* Card Shadow - Hanya untuk modal */
        #modalTambahSiswa .card.shadow-sm {
            box-shadow: 0 0.125rem 0.5rem rgba(0,0,0,0.075);
        }

        /* Textarea monospace - Hanya untuk modal */
        #modalTambahSiswa .font-monospace {
            font-family: 'Courier New', Courier, monospace;
            line-height: 1.8;
        }

        /* Button Styling - Hanya untuk modal */
        #modalTambahSiswa .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            transition: all 0.3s;
        }
        #modalTambahSiswa .btn-success:hover {
            background: linear-gradient(135deg, #218838 0%, #1ea87a 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
        }

        /* Alert modern - Hanya untuk modal */
        #modalTambahSiswa .alert {
            border-radius: 10px;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            let availableSiswa = [];
            let selectedSiswa = [];

            // Load siswa saat modal dibuka
            $('#modalTambahSiswa').on('shown.bs.modal', function() {
                loadAvailableSiswa();
            });

            // Load Available Siswa
            function loadAvailableSiswa() {
                $.ajax({
                    url: '{{ route("admin.kelas.siswa.available", $kelas->id) }}?per_page=1000',
                    type: 'GET',
                    beforeSend: function() {
                        $('#availableSiswaList').html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');
                    },
                    success: function(response) {
                        availableSiswa = response.items || [];
                        selectedSiswa = [];
                        renderAvailableList();
                        renderSelectedList();
                    },
                    error: function() {
                        $('#availableSiswaList').html('<div class="text-center py-5 text-danger"><i class="fas fa-exclamation-triangle fa-2x mb-3"></i><p>Gagal memuat data siswa</p></div>');
                    }
                });
            }

            // Render Available List
            function renderAvailableList(searchTerm = '') {
                let filtered = availableSiswa;
                
                if (searchTerm) {
                    filtered = availableSiswa.filter(s => 
                        s.text.toLowerCase().includes(searchTerm.toLowerCase()) ||
                        s.nisn.includes(searchTerm)
                    );
                }

                if (filtered.length === 0) {
                    $('#availableSiswaList').html('<div class="text-center py-5 text-muted"><i class="fas fa-inbox fa-2x mb-3"></i><p>Tidak ada siswa tersedia</p></div>');
                    $('#availableCount').text('0');
                    return;
                }

                let html = '';
                filtered.forEach(siswa => {
                    let genderIcon = siswa.jenis_kelamin === 'L' 
                        ? '<i class="fas fa-male text-primary"></i>' 
                        : '<i class="fas fa-female text-danger"></i>';
                    
                    html += `
                        <a href="#" class="list-group-item list-group-item-action siswa-item" data-id="${siswa.id}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="siswa-name">${siswa.text}</div>
                                    <div class="siswa-nisn">NISN: ${siswa.nisn} ${genderIcon}</div>
                                </div>
                            </div>
                        </a>
                    `;
                });

                $('#availableSiswaList').html(html);
                $('#availableCount').text(filtered.length);
            }

            // Render Selected List
            function renderSelectedList() {
                if (selectedSiswa.length === 0) {
                    $('#selectedSiswaList').html('<div class="text-center py-5 text-muted"><i class="fas fa-hand-pointer fa-2x mb-3"></i><p>Belum ada siswa dipilih</p></div>');
                    $('#selectedCount').text('0');
                    $('#submitCount').text('0');
                    return;
                }

                let html = '';
                selectedSiswa.forEach(siswa => {
                    let genderIcon = siswa.jenis_kelamin === 'L' 
                        ? '<i class="fas fa-male text-primary"></i>' 
                        : '<i class="fas fa-female text-danger"></i>';
                    
                    html += `
                        <a href="#" class="list-group-item list-group-item-action siswa-item selected" data-id="${siswa.id}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="siswa-name">${siswa.text}</div>
                                    <div class="siswa-nisn">NISN: ${siswa.nisn} ${genderIcon}</div>
                                </div>
                            </div>
                        </a>
                    `;
                });

                $('#selectedSiswaList').html(html);
                $('#selectedCount').text(selectedSiswa.length);
                $('#submitCount').text(selectedSiswa.length);
            }

            // Search functionality
            $('#searchSiswa').on('keyup', function() {
                renderAvailableList($(this).val());
            });

            // Toggle selection on available list
            $(document).on('click', '#availableSiswaList .siswa-item', function(e) {
                e.preventDefault();
                $(this).toggleClass('active');
            });

            // Toggle selection on selected list
            $(document).on('click', '#selectedSiswaList .siswa-item', function(e) {
                e.preventDefault();
                $(this).toggleClass('active');
            });

            // Add Selected
            $('#btnAddSelected').on('click', function() {
                $('#availableSiswaList .siswa-item.active').each(function() {
                    let id = $(this).data('id');
                    let siswa = availableSiswa.find(s => s.id === id);
                    if (siswa && !selectedSiswa.find(s => s.id === id)) {
                        selectedSiswa.push(siswa);
                    }
                });
                
                // Remove from available
                selectedSiswa.forEach(s => {
                    availableSiswa = availableSiswa.filter(a => a.id !== s.id);
                });
                
                renderAvailableList($('#searchSiswa').val());
                renderSelectedList();
            });

            // Add All
            $('#btnAddAll').on('click', function() {
                let searchTerm = $('#searchSiswa').val();
                let filtered = availableSiswa;
                
                if (searchTerm) {
                    filtered = availableSiswa.filter(s => 
                        s.text.toLowerCase().includes(searchTerm.toLowerCase()) ||
                        s.nisn.includes(searchTerm)
                    );
                }
                
                filtered.forEach(siswa => {
                    if (!selectedSiswa.find(s => s.id === siswa.id)) {
                        selectedSiswa.push(siswa);
                    }
                });
                
                // Remove from available
                selectedSiswa.forEach(s => {
                    availableSiswa = availableSiswa.filter(a => a.id !== s.id);
                });
                
                renderAvailableList();
                renderSelectedList();
            });

            // Remove Selected
            $('#btnRemoveSelected').on('click', function() {
                $('#selectedSiswaList .siswa-item.active').each(function() {
                    let id = $(this).data('id');
                    let siswa = selectedSiswa.find(s => s.id === id);
                    if (siswa) {
                        availableSiswa.push(siswa);
                        selectedSiswa = selectedSiswa.filter(s => s.id !== id);
                    }
                });
                
                renderAvailableList($('#searchSiswa').val());
                renderSelectedList();
            });

            // Remove All
            $('#btnRemoveAll').on('click', function() {
                selectedSiswa.forEach(siswa => {
                    availableSiswa.push(siswa);
                });
                selectedSiswa = [];
                
                renderAvailableList($('#searchSiswa').val());
                renderSelectedList();
            });

            // Form Submit
            $('#formTambahSiswaSelect').on('submit', function(e) {
                e.preventDefault();
                
                if (selectedSiswa.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Perhatian!',
                        text: 'Silakan pilih minimal 1 siswa'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Konfirmasi',
                    html: `Tambahkan <strong>${selectedSiswa.length} siswa</strong> ke kelas ini?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Tambahkan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let siswaIds = selectedSiswa.map(s => s.id);
                        $.ajax({
                            url: '{{ route("admin.kelas.siswa.store", $kelas->id) }}',
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                siswa_ids: siswaIds
                            },
                            beforeSend: function() {
                                Swal.fire({
                                    title: 'Memproses...',
                                    html: 'Menambahkan siswa ke kelas...',
                                    allowOutsideClick: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    }
                                });
                            },
                            success: function(response) {
                                $('#modalTambahSiswa').modal('hide');
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    html: response.message,
                                    timer: 2000
                                }).then(() => location.reload());
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    html: xhr.responseJSON?.message || 'Terjadi kesalahan'
                                });
                            }
                        });
                    }
                });
            });

            // Form Submit: Tambah Siswa via NISN Bulk
            $('#formTambahSiswaNISN').on('submit', function(e) {
                e.preventDefault();
                
                let nisnList = $('#nisn_list').val().trim();
                if (!nisnList) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Perhatian!',
                        text: 'Silakan masukkan minimal 1 NISN'
                    });
                    return;
                }

                // Parse NISN list
                let nisnArray = nisnList.split('\n')
                    .map(n => n.trim())
                    .filter(n => n.length > 0);

                if (nisnArray.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Perhatian!',
                        text: 'Tidak ada NISN yang valid'
                    });
                    return;
                }

                let formData = $(this).serialize();
                
                Swal.fire({
                    title: 'Konfirmasi Bulk Import',
                    html: `Proses <strong>${nisnArray.length} NISN</strong>?<br><small class="text-muted">Sistem akan mencocokkan NISN dengan data siswa</small>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Proses!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        submitTambahSiswaNISN(formData, nisnArray.length);
                    }
                });
            });

            // Submit Add Siswa (Select2)
            // Submit Add Siswa (NISN Bulk)
            function submitTambahSiswaNISN(formData, count) {
                $.ajax({
                    url: '{{ route("admin.kelas.siswa.store-nisn", $kelas->id) }}',
                    type: 'POST',
                    data: formData,
                    beforeSend: function() {
                        Swal.fire({
                            title: 'Memproses...',
                            html: `Memproses ${count} NISN...<br><small>Mohon tunggu</small>`,
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function(response) {
                        $('#modalTambahSiswa').modal('hide');
                        
                        let html = '<div class="text-left">';
                        html += `<p><strong>✅ Berhasil:</strong> ${response.success_count} siswa</p>`;
                        
                        if (response.failed_count > 0) {
                            html += `<p><strong>❌ Gagal:</strong> ${response.failed_count} NISN</p>`;
                            html += '<hr><p><strong>Detail Error:</strong></p><ul>';
                            response.errors.forEach(error => {
                                html += `<li><code>${error.nisn}</code>: ${error.error}</li>`;
                            });
                            html += '</ul>';
                        }
                        html += '</div>';

                        Swal.fire({
                            icon: response.failed_count > 0 ? 'warning' : 'success',
                            title: 'Proses Selesai!',
                            html: html,
                            width: 600
                        }).then(() => location.reload());
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            html: xhr.responseJSON?.message || 'Terjadi kesalahan'
                        });
                    }
                });
            }

            // Reset form when modal closed
            $('#modalTambahSiswa').on('hidden.bs.modal', function() {
                $('#formTambahSiswaSelect')[0].reset();
                $('#formTambahSiswaNISN')[0].reset();
                $('.select2-siswa').val(null).trigger('change');
            });

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

            // Kosongkan Kelas
            $('#btnKosongkanKelas').on('click', function() {
                const jumlahSiswa = {{ $kelas->siswaAktif->count() }};
                const namaKelas = '{{ $kelas->nama_lengkap }}';
                
                Swal.fire({
                    title: '⚠️ PERINGATAN!',
                    html: `
                        <div class="text-left">
                            <p class="mb-3"><strong>Anda akan mengeluarkan SEMUA siswa dari kelas ini!</strong></p>
                            <div class="alert alert-danger">
                                <h5 class="mb-2"><i class="fas fa-exclamation-triangle"></i> Detail Tindakan:</h5>
                                <ul class="mb-0">
                                    <li>Kelas: <strong>${namaKelas}</strong></li>
                                    <li>Jumlah siswa: <strong>${jumlahSiswa} siswa</strong></li>
                                    <li>Semua siswa akan dikeluarkan dari kelas</li>
                                    <li>Status siswa berubah menjadi "keluar"</li>
                                    <li>Tanggal keluar: <strong>Hari ini</strong></li>
                                </ul>
                            </div>
                            <p class="text-danger"><strong>⚠️ TINDAKAN INI TIDAK DAPAT DIBATALKAN!</strong></p>
                            <hr>
                            <p class="mb-2">Untuk melanjutkan, ketik: <code>KOSONGKAN</code></p>
                            <input type="text" id="confirmText" class="form-control" placeholder="Ketik: KOSONGKAN">
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: '<i class="fas fa-check"></i> Ya, Kosongkan!',
                    cancelButtonText: '<i class="fas fa-times"></i> Batal',
                    reverseButtons: true,
                    width: '600px',
                    preConfirm: () => {
                        const confirmText = document.getElementById('confirmText').value;
                        if (confirmText !== 'KOSONGKAN') {
                            Swal.showValidationMessage('Ketik "KOSONGKAN" dengan benar untuk melanjutkan');
                            return false;
                        }
                        return true;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Second confirmation with password
                        Swal.fire({
                            title: '🔐 Konfirmasi Terakhir',
                            html: `
                                <div class="text-left">
                                    <p class="mb-3">Tindakan ini akan mengeluarkan <strong>${jumlahSiswa} siswa</strong> dari kelas <strong>${namaKelas}</strong></p>
                                    <p class="text-danger mb-3"><i class="fas fa-lock"></i> Masukkan alasan pengosongan kelas:</p>
                                    <textarea id="alasanKosongkan" class="form-control" rows="3" placeholder="Contoh: Pembubaran kelas, Reorganisasi, dll." required></textarea>
                                </div>
                            `,
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: '<i class="fas fa-user-times"></i> Kosongkan Sekarang!',
                            cancelButtonText: '<i class="fas fa-times"></i> Batal',
                            reverseButtons: true,
                            preConfirm: () => {
                                const alasan = document.getElementById('alasanKosongkan').value.trim();
                                if (!alasan) {
                                    Swal.showValidationMessage('Alasan harus diisi');
                                    return false;
                                }
                                if (alasan.length < 10) {
                                    Swal.showValidationMessage('Alasan minimal 10 karakter');
                                    return false;
                                }
                                return alasan;
                            }
                        }).then((finalResult) => {
                            if (finalResult.isConfirmed) {
                                // Show loading
                                Swal.fire({
                                    title: 'Memproses...',
                                    html: 'Sedang mengeluarkan siswa dari kelas...',
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    }
                                });
                                
                                // Execute empty class
                                $.ajax({
                                    url: '{{ route("admin.kelas.kosongkan", $kelas->id) }}',
                                    type: 'POST',
                                    data: {
                                        _token: '{{ csrf_token() }}',
                                        alasan: finalResult.value
                                    },
                                    success: function(response) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Berhasil!',
                                            html: `
                                                <p>${response.message}</p>
                                                <div class="alert alert-info mt-3">
                                                    <strong>${response.jumlah_siswa} siswa</strong> telah dikeluarkan dari kelas
                                                </div>
                                            `,
                                            confirmButtonText: 'OK'
                                        }).then(() => {
                                            location.reload();
                                        });
                                    },
                                    error: function(xhr) {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Gagal!',
                                            text: xhr.responseJSON?.message || 'Terjadi kesalahan saat mengosongkan kelas'
                                        });
                                    }
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@stop

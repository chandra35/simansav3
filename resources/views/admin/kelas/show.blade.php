@extends('adminlte::page')

@section('title', 'Detail Kelas')

@section('plugins.Datatables', true)
@section('plugins.Select2', true)

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
                            <div class="row mt-1">
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        <i class="fas fa-crown text-warning"></i> <strong>Ketua Kelas:</strong>
                                        {{ $kelas->ketuaKelasRecord?->siswa?->nama_lengkap ?? 'Belum ditetapkan' }}
                                        @can('edit-kelas')
                                            <button type="button" class="btn btn-xs btn-outline-warning ml-1" data-toggle="modal" data-target="#modalKetuaKelas">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        @endcan
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
                @can('edit-kelas')
                    @php
                        $jumlahBelumDicek = $students
                            ->filter(fn ($siswa) => $siswa->pivot->keberadaan_diverifikasi_at === null)
                            ->count();
                    @endphp
                    @if($students->isNotEmpty())
                        <button type="button"
                                class="btn btn-sm {{ $jumlahBelumDicek > 0 ? 'btn-warning' : 'btn-success' }} mr-1"
                                id="btnVerifyAllPresence"
                                data-url="{{ route('admin.kelas.siswa.verifikasi-keberadaan-semua', $kelas) }}"
                                data-pending="{{ $jumlahBelumDicek }}"
                                @disabled($jumlahBelumDicek === 0)>
                            <i class="fas {{ $jumlahBelumDicek > 0 ? 'fa-user-check' : 'fa-check-circle' }} mr-1"></i>
                            {{ $jumlahBelumDicek > 0 ? 'Cek Keberadaan Semua' : 'Semua Sudah Dicek' }}
                        </button>
                    @endif
                @endcan
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
                <div class="class-table-guide">
                    <i class="fas fa-sort-amount-down-alt"></i>
                    <span>Klik judul kolom untuk mengurutkan daftar siswa.</span>
                </div>
                <div class="table-responsive">
                    <table id="classStudentsTable" class="table table-bordered table-hover class-students-table">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>Foto</th>
                                <th>Absen</th>
                                <th>NISN</th>
                                <th>Nama Lengkap</th>
                                <th>Asal Sekolah</th>
                                <th>JK</th>
                                @can('edit-kelas')
                                <th class="text-center">Keberadaan</th>
                                @endcan
                                @if(auth()->user()->hasRole('Super Admin'))
                                <th class="text-center">EMIS</th>
                                @endif
                                <th>Tanggal Masuk</th>
                                @canany(['transfer-siswa-kelas', 'remove-siswa-kelas'])
                                <th>Aksi</th>
                                @endcanany
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $index => $siswa)
                                @php
                                    $tanggalMasuk = $siswa->pivot->tanggal_masuk
                                        ? \Carbon\Carbon::parse($siswa->pivot->tanggal_masuk)
                                        : null;
                                    $asalSekolah = $siswa->sekolahAsal;
                                    $schoolIdentity = \Illuminate\Support\Str::lower(collect([
                                        $asalSekolah?->nama,
                                        $asalSekolah?->bentuk_pendidikan,
                                    ])->filter()->implode(' '));
                                    $isMts = \Illuminate\Support\Str::contains($schoolIdentity, ['mts', 'madrasah tsanawiyah']);
                                    $needsNsm = $asalSekolah && $isMts && blank($asalSekolah->nsm);
                                @endphp
                                <tr>
                                    <td class="text-center row-number">{{ $index + 1 }}</td>
                                    <td class="text-center">
                                        <img src="{{ $siswa->foto_profile_url }}" 
                                             alt="{{ $siswa->nama_lengkap }}" 
                                             class="img-circle elevation-2" 
                                             style="width: 40px; height: 40px; object-fit: cover;">
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-primary">{{ $siswa->pivot->nomor_urut_absen }}</span>
                                    </td>
                                    <td>{{ $siswa->nisn }}</td>
                                    <td>
                                        <a href="javascript:void(0)" class="btn-show-siswa text-primary" 
                                           data-siswa-id="{{ $siswa->id }}"
                                           style="text-decoration: none; cursor: pointer;">
                                            <strong>{{ $siswa->nama_lengkap }}</strong>
                                        </a>
                                        @if($siswa->pivot->is_ketua_kelas && $siswa->pivot->ketua_kelas_selesai_at === null)
                                            <span class="badge badge-warning ml-1">
                                                <i class="fas fa-crown mr-1"></i>Ketua Kelas
                                            </span>
                                        @endif
                                        <small class="d-block text-info mt-1">
                                            <i class="fas fa-id-badge mr-1"></i>NIS Lokal:
                                            <strong>{{ $siswa->nis_lokal ?: 'Belum diterbitkan' }}</strong>
                                        </small>
                                        @if((int) $kelas->tingkat > 10)
                                            <small class="d-block text-muted mt-1">
                                                <i class="fas fa-history mr-1"></i>
                                                Asal kelas:
                                                <strong>{{ $kelasAsalBySiswa->get($siswa->id) ?: 'Belum tercatat' }}</strong>
                                            </small>
                                        @endif
                                    </td>
                                    <td data-order="{{ $asalSekolah?->nama ?: 'ZZZZ' }}">
                                        <div class="origin-school" data-npsn="{{ $siswa->npsn_asal_sekolah }}">
                                            @if($asalSekolah)
                                                @can('view-siswa')
                                                    <a href="{{ route('admin.sekolah-asal.show', $asalSekolah->npsn) }}"
                                                       class="origin-school__name"
                                                       title="Buka detail sekolah">
                                                        <strong>{{ $asalSekolah->nama }}</strong>
                                                    </a>
                                                @else
                                                    <strong>{{ $asalSekolah->nama }}</strong>
                                                @endcan
                                            @else
                                                <strong>Sekolah belum terdata</strong>
                                            @endif
                                            <small>
                                                NPSN: {{ $siswa->npsn_asal_sekolah ?: '-' }}
                                                <span>|</span>
                                                NSM:
                                                <b class="origin-school-nsm {{ $needsNsm ? 'is-missing' : '' }}">
                                                    {{ $asalSekolah?->nsm ?: ($needsNsm ? 'Belum terisi' : '-') }}
                                                </b>
                                            </small>
                                            @can('edit-siswa')
                                                @if($needsNsm)
                                                    <div class="school-nsm-action">
                                                        <button type="button"
                                                                class="btn btn-xs btn-outline-warning btn-complete-school-nsm"
                                                                data-url="{{ route('admin.sekolah-asal.enrich', $asalSekolah->npsn) }}"
                                                                data-npsn="{{ $asalSekolah->npsn }}"
                                                                data-school="{{ $asalSekolah->nama }}"
                                                                data-toggle="tooltip"
                                                                title="Ambil dan simpan NSM dari referensi institusi EMIS Kemenag">
                                                            <i class="fas fa-magic mr-1"></i>Lengkapi NSM
                                                        </button>
                                                    </div>
                                                @endif
                                            @endcan
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if($siswa->jenis_kelamin == 'L')
                                            <span class="badge badge-primary"><i class="fas fa-male"></i> L</span>
                                        @else
                                            <span class="badge badge-danger"><i class="fas fa-female"></i> P</span>
                                        @endif
                                    </td>
                                    @can('edit-kelas')
                                    @php
                                        $keberadaanTerverifikasi = $siswa->pivot->keberadaan_diverifikasi_at !== null;
                                    @endphp
                                    <td class="text-center class-presence-cell">
                                        <button type="button"
                                                class="btn btn-xs class-presence-toggle {{ $keberadaanTerverifikasi ? 'is-verified' : 'is-pending' }}"
                                                data-url="{{ route('admin.kelas.siswa.toggle-keberadaan', ['kelas' => $kelas, 'siswa' => $siswa]) }}"
                                                data-student="{{ $siswa->nama_lengkap }}"
                                                data-verified="{{ $keberadaanTerverifikasi ? 1 : 0 }}"
                                                data-toggle="tooltip"
                                                title="{{ $keberadaanTerverifikasi ? 'Batalkan verifikasi keberadaan' : 'Tandai siswa ada di rombel ini' }}">
                                            <i class="fas {{ $keberadaanTerverifikasi ? 'fa-user-check' : 'fa-user-clock' }} mr-1"></i>
                                            <span>{{ $keberadaanTerverifikasi ? 'Ada' : 'Belum dicek' }}</span>
                                        </button>
                                    </td>
                                    @endcan
                                    @if(auth()->user()->hasRole('Super Admin'))
                                    <td class="text-center class-emis-cell">
                                        <button type="button"
                                                class="btn btn-xs class-emis-toggle {{ $siswa->emis_registered ? 'is-registered' : 'is-pending' }}"
                                                data-url="{{ route('admin.siswa.toggle-emis-registered', $siswa) }}"
                                                data-student="{{ $siswa->nama_lengkap }}"
                                                data-registered="{{ $siswa->emis_registered ? 1 : 0 }}"
                                                data-toggle="tooltip"
                                                title="{{ $siswa->emis_registered ? 'Tandai belum masuk EMIS' : 'Tandai sudah masuk EMIS' }}">
                                            <i class="fas {{ $siswa->emis_registered ? 'fa-check-circle' : 'fa-circle' }} mr-1"></i>
                                            <span>{{ $siswa->emis_registered ? 'Sudah' : 'Belum' }}</span>
                                        </button>
                                    </td>
                                    @endif
                                    <td data-order="{{ $tanggalMasuk?->format('Y-m-d') }}">
                                        {{ $tanggalMasuk?->format('d/m/Y') ?: '-' }}
                                    </td>
                                    @canany(['transfer-siswa-kelas', 'remove-siswa-kelas'])
                                    <td class="text-center">
                                        <div class="class-row-actions">
                                        @can('transfer-siswa-kelas')
                                            @if($transferClasses->isNotEmpty())
                                            <button type="button" class="btn btn-sm btn-primary btn-transfer-siswa"
                                                data-siswa-id="{{ $siswa->id }}"
                                                data-siswa-nama="{{ $siswa->nama_lengkap }}"
                                                data-siswa-nisn="{{ $siswa->nisn ?: '-' }}"
                                                data-siswa-foto="{{ $siswa->foto_profile_url }}"
                                                data-toggle="tooltip"
                                                title="Pindah ke rombel lain">
                                                <i class="fas fa-exchange-alt"></i>
                                            </button>
                                            @endif
                                        @endcan
                                        @can('remove-siswa-kelas')
                                        <button type="button" class="btn btn-sm btn-danger btn-remove-siswa" 
                                            data-siswa-id="{{ $siswa->id }}"
                                            data-siswa-nama="{{ $siswa->nama_lengkap }}">
                                            <i class="fas fa-user-minus"></i>
                                        </button>
                                        @endcan
                                        </div>
                                    </td>
                                    @endcanany
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

    {{-- Modal Pindah Rombel --}}
    @can('transfer-siswa-kelas')
    <div class="modal fade" id="modalTransferSiswa" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content transfer-modal">
                <form id="formTransferSiswa">
                    @csrf
                    <div class="modal-header transfer-modal__header">
                        <div><small>ADMINISTRASI ROMBEL</small><h5 class="modal-title"><i class="fas fa-exchange-alt mr-2"></i>Pindah Rombel Siswa</h5></div>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="transfer-student">
                            <img id="transferStudentPhoto" src="" alt="Foto siswa">
                            <div><small>SISWA YANG DIPINDAHKAN</small><strong id="transferStudentName">-</strong><span id="transferStudentMeta">-</span></div>
                            <span class="transfer-origin"><small>DARI</small><b>{{ $kelas->nama_lengkap }}</b></span>
                        </div>
                        <div class="transfer-rule"><i class="fas fa-shield-alt"></i><span>Tujuan dibatasi ke tingkat {{ $kelas->getTingkatRomawi() }} dan tahun pelajaran {{ $kelas->tahunPelajaran->nama ?? '-' }}. Riwayat rombel asal tetap disimpan.</span></div>
                        <div class="form-group mt-3">
                            <label for="targetKelasId">Rombel Tujuan</label>
                            <select class="form-control" id="targetKelasId" name="target_kelas_id" required>
                                <option value="">Pilih rombel tujuan</option>
                                @foreach($transferClasses as $targetClass)
                                    @php
                                        $targetFull = $targetClass->siswa_aktif_count >= $targetClass->kapasitas;
                                    @endphp
                                    <option value="{{ $targetClass->id }}" @disabled($targetFull)>
                                        {{ $targetClass->nama_lengkap }} · {{ $targetClass->siswa_aktif_count }}/{{ $targetClass->kapasitas }} siswa{{ $targetFull ? ' · PENUH' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-0"><label for="transferReason">Catatan <span class="text-muted font-weight-normal">(opsional)</span></label><textarea class="form-control" id="transferReason" name="reason" rows="3" maxlength="500" placeholder="Contoh: Penyesuaian pembagian rombel"></textarea></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary" id="btnSubmitTransfer"><i class="fas fa-exchange-alt mr-1"></i> Pindahkan Sekarang</button></div>
                </form>
            </div>
        </div>
    </div>
    @endcan

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
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form id="formWaliKelas">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-user-tie mr-2"></i>Tugaskan Wali Kelas
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-light border d-flex align-items-center mb-3">
                            <i class="fas fa-school text-primary mr-3"></i>
                            <div>
                                <small class="text-muted d-block">Rombel yang akan ditugaskan</small>
                                <strong class="d-block">{{ $kelas->nama_lengkap }}</strong>
                                <span class="text-muted small">{{ $kelas->tahunPelajaran->nama ?? 'Tahun pelajaran tidak tersedia' }}</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="wali_kelas_id" class="font-weight-bold">
                                Pilih Guru <span class="text-danger">*</span>
                            </label>
                            <select class="form-control" id="wali_kelas_id" name="wali_kelas_id" style="width: 100%;" required>
                                <option value="">Cari nama, jenis guru, atau rombel...</option>
                                @if($availableGtk->isEmpty())
                                    <option value="" disabled>Tidak ada guru aktif tersedia</option>
                                @else
                                    @foreach($availableGtk as $gtk)
                                        @php
                                            $displayName = $gtk->gtk?->nama_lengkap ?: $gtk->name;
                                            $teacherType = $gtk->gtk?->jenis_ptk ?: 'Guru';
                                            $assignedRombels = $waliKelasRombelByUser->get($gtk->id, collect());
                                            $assignmentText = $assignedRombels->isNotEmpty()
                                                ? 'Wali: '.$assignedRombels->implode(', ')
                                                : 'Belum menjadi wali kelas';
                                        @endphp
                                        <option value="{{ $gtk->id }}"
                                                {{ $kelas->wali_kelas_id == $gtk->id ? 'selected' : '' }}>
                                            {{ $displayName }} | {{ $teacherType }} | {{ $assignmentText }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <small class="form-text text-muted d-flex justify-content-between flex-wrap">
                                <span><i class="fas fa-search mr-1"></i>Ketik nama atau rombel untuk mencari.</span>
                                <span><i class="fas fa-chalkboard-teacher mr-1"></i>{{ $availableGtk->count() }} guru aktif tersedia.</span>
                            </small>
                        </div>
                        <div class="alert alert-info py-2 mb-0 small">
                            <i class="fas fa-info-circle mr-1"></i>
                            Guru yang sudah menjadi wali kelas tetap dapat dipilih. Rombel aktifnya ditampilkan sebagai metadata untuk mencegah salah penugasan.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check mr-1"></i>Simpan Penugasan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan

    {{-- Modal Assign Ketua Kelas --}}
    @can('edit-kelas')
    <div class="modal fade" id="modalKetuaKelas" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form id="formKetuaKelas">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-crown text-warning mr-2"></i>Tetapkan Ketua Kelas
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-light border mb-3">
                            <small class="text-muted d-block">Rombel dan tahun pelajaran</small>
                            <strong>{{ $kelas->nama_lengkap }}</strong>
                            <span class="text-muted">— {{ $kelas->tahunPelajaran->nama ?? '-' }}</span>
                        </div>
                        <div class="form-group mb-2">
                            <label for="ketua_kelas_id" class="font-weight-bold">Pilih Siswa Aktif</label>
                            <select class="form-control" id="ketua_kelas_id" name="ketua_kelas_id" style="width:100%;">
                                <option value="">Belum ada / kosongkan penugasan</option>
                                @foreach($students as $studentOption)
                                    <option value="{{ $studentOption->id }}"
                                        @selected($studentOption->pivot->is_ketua_kelas && $studentOption->pivot->ketua_kelas_selesai_at === null)>
                                        {{ $studentOption->nama_lengkap }} | NISN {{ $studentOption->nisn ?: '-' }} | Absen {{ $studentOption->pivot->nomor_urut_absen ?: '-' }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">
                                Hanya siswa aktif pada rombel ini yang dapat dipilih. Pergantian akan tersimpan pada rekam didik kedua siswa.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save mr-1"></i>Simpan Ketua Kelas
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan

    {{-- Modal Remove Siswa --}}
    @can('remove-siswa-kelas')
    <div class="modal fade" id="modalRemoveSiswa" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white"><i class="fas fa-user-minus"></i> Keluarkan Siswa dari Kelas</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Anda akan mengeluarkan <strong id="siswa-nama-display"></strong> dari kelas ini.
                    </div>
                    <p class="text-muted">Siswa dapat langsung di-assign ke kelas lain setelah dikeluarkan.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" id="btnConfirmRemoveSiswa" class="btn btn-danger">
                        <i class="fas fa-user-minus"></i> Keluarkan
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endcan

    {{-- Modal Detail Siswa --}}
    <div class="modal fade" id="modalDetailSiswa" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white"><i class="fas fa-user-graduate"></i> Detail Siswa</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body" id="detailSiswaContent">
                    <div class="text-center py-5">
                        <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
                        <p class="mt-3">Memuat data siswa...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" id="btnViewFullSiswa" class="btn btn-info" target="_blank">
                        <i class="fas fa-external-link-alt"></i> Lihat Detail Lengkap
                    </a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .class-table-guide {
            display: flex;
            align-items: center;
            gap: .55rem;
            margin-bottom: .9rem;
            padding: .65rem .8rem;
            border: 1px solid #dbeafe;
            border-radius: 9px;
            background: #eff6ff;
            color: #1e40af;
            font-size: .82rem;
            font-weight: 600;
        }
        .class-students-table {
            min-width: 1180px;
        }
        .class-students-table thead th {
            vertical-align: middle;
            white-space: nowrap;
        }
        .class-students-table tbody td {
            vertical-align: middle;
        }
        .class-students-table .origin-school {
            min-width: 240px;
            line-height: 1.25;
        }
        .class-students-table .origin-school strong {
            display: block;
            color: #172554;
            font-size: .82rem;
        }
        .class-students-table .origin-school__name:hover strong {
            color: #2563eb;
            text-decoration: underline;
        }
        .class-students-table .origin-school small {
            display: block;
            margin-top: .3rem;
            color: #64748b;
            font-size: .72rem;
            white-space: nowrap;
        }
        .class-students-table .origin-school small span {
            margin: 0 .25rem;
            color: #cbd5e1;
        }
        .class-students-table .origin-school-nsm {
            font-weight: 700;
            color: #475569;
        }
        .class-students-table .origin-school-nsm.is-missing {
            color: #b45309;
        }
        .class-students-table .school-nsm-action {
            margin-top: .45rem;
        }
        .class-students-table .btn-complete-school-nsm {
            border-radius: 7px;
            font-size: .7rem;
            font-weight: 700;
        }
        .class-students-table .class-emis-cell {
            width: 76px;
        }
        .class-students-table .class-emis-toggle {
            min-width: 62px;
            padding: .2rem .42rem;
            border: 1px solid;
            border-radius: 999px;
            font-size: .68rem;
            font-weight: 700;
            line-height: 1.25;
            white-space: nowrap;
            box-shadow: none;
        }
        .class-students-table .class-emis-toggle.is-registered {
            color: #15803d;
            border-color: #86efac;
            background: #dcfce7;
        }
        .class-students-table .class-emis-toggle.is-pending {
            color: #64748b;
            border-color: #cbd5e1;
            background: #f8fafc;
        }
        .class-students-table .class-emis-toggle:hover,
        .class-students-table .class-emis-toggle:focus {
            filter: brightness(.97);
            transform: translateY(-1px);
        }
        .class-students-table .class-presence-cell {
            width: 108px;
        }
        .class-students-table .class-presence-toggle {
            min-width: 94px;
            padding: .2rem .5rem;
            border: 1px solid;
            border-radius: 999px;
            font-size: .68rem;
            font-weight: 700;
            line-height: 1.25;
            white-space: nowrap;
            box-shadow: none;
        }
        .class-students-table .class-presence-toggle.is-verified {
            color: #15803d;
            border-color: #86efac;
            background: #dcfce7;
        }
        .class-students-table .class-presence-toggle.is-pending {
            color: #92400e;
            border-color: #fcd34d;
            background: #fffbeb;
        }
        .class-students-table .class-presence-toggle:hover,
        .class-students-table .class-presence-toggle:focus {
            filter: brightness(.97);
            transform: translateY(-1px);
        }

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
        .class-row-actions {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            white-space: nowrap;
        }
        .class-row-actions .btn {
            display: inline-grid;
            place-items: center;
            width: 34px;
            height: 34px;
            padding: 0;
            border-radius: 9px;
        }
        .transfer-modal {
            overflow: hidden;
            border: 0;
            border-radius: 16px;
            box-shadow: 0 22px 60px rgba(15, 23, 42, .24);
        }
        .transfer-modal__header {
            align-items: center;
            border: 0;
            background: linear-gradient(135deg, #2563eb, #0f766e);
            color: #fff;
        }
        .transfer-modal__header small {
            display: block;
            font-size: .64rem;
            font-weight: 800;
            letter-spacing: .1em;
            opacity: .78;
        }
        .transfer-modal__header h5 {
            margin: .15rem 0 0;
            color: #fff;
            font-weight: 800;
        }
        .transfer-student {
            display: grid;
            grid-template-columns: 58px minmax(0, 1fr) auto;
            align-items: center;
            gap: .8rem;
            padding: .85rem;
            border: 1px solid #dbeafe;
            border-radius: 13px;
            background: #f8fbff;
        }
        .transfer-student img {
            width: 58px;
            height: 68px;
            border-radius: 11px;
            object-fit: cover;
        }
        .transfer-student small,
        .transfer-student strong,
        .transfer-student span {
            display: block;
        }
        .transfer-student>div small,
        .transfer-origin small {
            color: #64748b;
            font-size: .62rem;
            font-weight: 800;
            letter-spacing: .06em;
        }
        .transfer-student>div strong {
            margin: .15rem 0;
            color: #0f172a;
            font-size: .9rem;
        }
        .transfer-student>div span {
            color: #64748b;
            font-size: .72rem;
        }
        .transfer-origin {
            max-width: 120px;
            padding: .5rem .65rem;
            border-radius: 9px;
            background: #e0e7ff;
            color: #3730a3;
            text-align: right;
        }
        .transfer-origin b { font-size: .76rem; }
        .transfer-rule {
            display: flex;
            align-items: flex-start;
            gap: .65rem;
            margin-top: .8rem;
            padding: .7rem .8rem;
            border-radius: 10px;
            background: #ecfdf5;
            color: #166534;
            font-size: .75rem;
        }
        @media(max-width:575.98px) {
            .transfer-student { grid-template-columns: 52px 1fr; }
            .transfer-student img { width: 52px; height: 62px; }
            .transfer-origin { grid-column: 1 / -1; max-width: none; text-align: left; }
        }
    </style>
@stop

@section('js')
    @php
        $hasClassPresenceColumn = auth()->user()->can('edit-kelas');
        $hasClassEmisColumn = auth()->user()->hasRole('Super Admin');
        $nonSortableStudentColumns = [0, 1];
        if ($hasClassPresenceColumn) {
            $nonSortableStudentColumns[] = 7;
        }
        if ($hasClassEmisColumn) {
            $nonSortableStudentColumns[] = $hasClassPresenceColumn ? 8 : 7;
        }
        if (auth()->user()->canAny(['transfer-siswa-kelas', 'remove-siswa-kelas'])) {
            $nonSortableStudentColumns[] = 8 + (int) $hasClassPresenceColumn + (int) $hasClassEmisColumn;
        }
    @endphp
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            const classStudentsTableElement = $('#classStudentsTable');
            if (classStudentsTableElement.length) {
                const classStudentsTable = classStudentsTableElement.DataTable({
                    paging: false,
                    searching: false,
                    info: false,
                    autoWidth: false,
                    order: [[4, 'asc']],
                    columnDefs: [
                        {
                            targets: @json($nonSortableStudentColumns),
                            orderable: false
                        }
                    ],
                    language: {
                        emptyTable: 'Belum ada siswa di kelas ini',
                        zeroRecords: 'Tidak ada data yang sesuai'
                    }
                });

                const refreshRowNumbers = function() {
                    classStudentsTable.column(0, { order: 'applied' }).nodes().each(function(cell, index) {
                        cell.textContent = index + 1;
                    });
                };
                classStudentsTable.on('order.dt draw.dt', refreshRowNumbers);
                refreshRowNumbers();
            }

            $('[data-toggle="tooltip"]').tooltip();

            @can('edit-kelas')
            $('#btnVerifyAllPresence').on('click', function() {
                const button = $(this);
                const pendingCount = Number(button.data('pending')) || 0;
                if (button.prop('disabled') || pendingCount === 0) return;

                Swal.fire({
                    icon: 'question',
                    title: 'Cek keberadaan semua siswa?',
                    html: `<strong>${pendingCount} siswa</strong> yang masih belum dicek akan ditandai <strong>Ada</strong> di rombel ini.`,
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-user-check mr-1"></i>Ya, Tandai Semua Ada',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#f59e0b',
                    showLoaderOnConfirm: true,
                    allowOutsideClick: () => !Swal.isLoading(),
                    preConfirm: () => $.post(button.data('url'), {_token: '{{ csrf_token() }}'})
                        .catch(xhr => {
                            Swal.showValidationMessage(xhr.responseJSON?.message || 'Gagal memverifikasi keberadaan siswa.');
                        })
                }).then(result => {
                    if (!result.isConfirmed) return;

                    Swal.fire({
                        icon: 'success',
                        title: 'Keberadaan diperbarui',
                        text: result.value.message,
                        confirmButtonText: 'Selesai'
                    }).then(() => window.location.reload());
                });
            });

            $(document).on('click', '.class-presence-toggle', function() {
                const button = $(this);
                if (button.prop('disabled')) return;

                const originalHtml = button.html();
                button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                $.post(button.data('url'), {_token: '{{ csrf_token() }}'})
                .done(function(response) {
                    if (!response.success) {
                        button.html(originalHtml);
                        toastr.error('Status keberadaan belum berhasil diperbarui.');
                        return;
                    }

                    const verified = Boolean(response.keberadaan_terverifikasi);
                    const tooltipText = verified
                        ? 'Batalkan verifikasi keberadaan'
                        : 'Tandai siswa ada di rombel ini';
                    button
                        .data('verified', verified ? 1 : 0)
                        .toggleClass('is-verified', verified)
                        .toggleClass('is-pending', !verified)
                        .html(verified
                            ? '<i class="fas fa-user-check mr-1"></i><span>Ada</span>'
                            : '<i class="fas fa-user-clock mr-1"></i><span>Belum dicek</span>');
                    button.tooltip('dispose').attr('title', tooltipText).tooltip();
                    toastr.success(response.message);
                })
                .fail(function(xhr) {
                    button.html(originalHtml);
                    toastr.error(xhr.responseJSON?.message || 'Gagal mengubah status keberadaan');
                })
                .always(function() {
                    button.prop('disabled', false);
                });
            });
            @endcan

            @if(auth()->user()->hasRole('Super Admin'))
            $(document).on('click', '.class-emis-toggle', function() {
                const button = $(this);
                if (button.prop('disabled')) return;

                const originalHtml = button.html();
                button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                $.post(button.data('url'), {_token: '{{ csrf_token() }}'})
                .done(function(response) {
                    if (!response.success) {
                        button.html(originalHtml);
                        toastr.error('Status EMIS belum berhasil diperbarui.');
                        return;
                    }

                    const registered = Boolean(response.emis_registered);
                    const tooltipText = registered ? 'Tandai belum masuk EMIS' : 'Tandai sudah masuk EMIS';
                    button
                        .data('registered', registered ? 1 : 0)
                        .toggleClass('is-registered', registered)
                        .toggleClass('is-pending', !registered)
                        .html(registered
                            ? '<i class="fas fa-check-circle mr-1"></i><span>Sudah</span>'
                            : '<i class="fas fa-circle mr-1"></i><span>Belum</span>');
                    button.tooltip('dispose').attr('title', tooltipText).tooltip();
                    toastr.success(registered ? 'Ditandai sudah masuk EMIS' : 'Tanda masuk EMIS dibatalkan');
                })
                .fail(function(xhr) {
                    button.html(originalHtml);
                    toastr.error(xhr.responseJSON?.message || 'Gagal mengubah status EMIS');
                })
                .always(function() {
                    button.prop('disabled', false);
                });
            });
            @endif

            $(document).on('click', '.btn-complete-school-nsm', function() {
                const button = $(this);
                const url = button.data('url');
                const npsn = String(button.data('npsn') || '');
                const schoolName = button.data('school') || 'Madrasah';

                Swal.fire({
                    icon: 'question',
                    title: 'Lengkapi NSM madrasah?',
                    text: `${schoolName} (${npsn}) akan dicek ke referensi institusi EMIS Kemenag.`,
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-sync-alt mr-1"></i> Cek & Simpan',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#f59e0b',
                    showLoaderOnConfirm: true,
                    allowOutsideClick: () => !Swal.isLoading(),
                    preConfirm: function() {
                        return $.ajax({
                            url: url,
                            method: 'POST',
                            data: {_token: '{{ csrf_token() }}'}
                        }).then(function(response) {
                            return response;
                        }, function(xhr) {
                            Swal.showValidationMessage(xhr.responseJSON?.message || 'Data sekolah belum berhasil dilengkapi.');
                        });
                    }
                }).then(function(result) {
                    if (!result.isConfirmed || !result.value) return;

                    const response = result.value;
                    const savedNsm = response.data?.nsm;
                    const warningText = (response.warnings || []).filter(Boolean).join(' ');

                    if (!response.complete || !savedNsm) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'NSM belum terisi',
                            text: `${response.message || 'Data baru terisi sebagian.'}${warningText ? ` ${warningText}` : ''}`,
                            confirmButtonText: 'OK'
                        });
                        return;
                    }

                    const matchingSchools = $('.origin-school').filter(function() {
                        return String($(this).data('npsn') || '') === npsn;
                    });
                    matchingSchools.find('.origin-school-nsm')
                        .text(savedNsm)
                        .removeClass('is-missing');
                    matchingSchools.find('.school-nsm-action').fadeOut(180, function() {
                        $(this).remove();
                    });

                    Swal.fire({
                        icon: 'success',
                        title: 'NSM berhasil disimpan',
                        text: `${schoolName}: ${savedNsm}`,
                        timer: 2200,
                        showConfirmButton: false
                    });
                });
            });

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
                            title: 'Memproses Bulk NISN',
                            html: `
                                <div class="text-left">
                                    <p class="mb-2">Memvalidasi <strong>${count} NISN</strong> dan menempatkan siswa ke rombel.</p>
                                    <small class="text-muted d-block">Sistem mencocokkan data siswa, mengecek histori kelas aktif, menghitung nomor absen, lalu menyimpan hasilnya.</small>
                                </div>
                            `,
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

            const waliKelasSelect = $('#wali_kelas_id');
            if ($.fn.select2) {
                waliKelasSelect.select2({
                    dropdownParent: $('#modalWaliKelas'),
                    width: '100%',
                    placeholder: 'Cari nama, jenis guru, atau rombel...',
                    allowClear: true,
                    minimumResultsForSearch: 0
                });

                $('#ketua_kelas_id').select2({
                    dropdownParent: $('#modalKetuaKelas'),
                    width: '100%',
                    placeholder: 'Cari nama, NISN, atau nomor absen...',
                    allowClear: true,
                    minimumResultsForSearch: 0
                });
            }

            // Assign Wali Kelas
            $('#formWaliKelas').on('submit', function(e) {
                e.preventDefault();
                
                const waliKelasId = $('#wali_kelas_id').val();
                
                // Validasi client-side
                if (!waliKelasId || waliKelasId === '') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Perhatian!',
                        text: 'Silakan pilih GTK untuk ditugaskan sebagai Wali Kelas'
                    });
                    return false;
                }
                
                const formData = $(this).serialize();
                
                $.ajax({
                    url: "{{ route('admin.kelas.wali-kelas', $kelas->id) }}",
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        console.log('Success:', response);
                        $('#modalWaliKelas').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                        }).then(() => location.reload());
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr);
                        console.error('Response:', xhr.responseJSON);
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan saat assign wali kelas'
                        });
                    }
                });
            });

            // Assign Ketua Kelas
            $('#formKetuaKelas').on('submit', function(e) {
                e.preventDefault();

                const submitButton = $(this).find('button[type="submit"]');
                submitButton.prop('disabled', true);

                $.ajax({
                    url: "{{ route('admin.kelas.ketua-kelas', $kelas) }}",
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#modalKetuaKelas').modal('hide');
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
                            text: xhr.responseJSON?.message || 'Ketua Kelas gagal ditetapkan.'
                        });
                    },
                    complete: function() {
                        submitButton.prop('disabled', false);
                    }
                });
            });

            // Show Detail Siswa - Click Handler
            $('.btn-show-siswa').on('click', function() {
                let siswaId = $(this).data('siswa-id');
                
                // Show loading in modal
                $('#detailSiswaContent').html(`
                    <div class="text-center py-5">
                        <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
                        <p class="mt-3">Memuat data siswa...</p>
                    </div>
                `);
                
                // Update link to full detail
                $('#btnViewFullSiswa').attr('href', '{{ url("admin/siswa") }}/' + siswaId);
                
                // Show modal
                $('#modalDetailSiswa').modal('show');
                
                // Load siswa detail via AJAX
                $.ajax({
                    url: '{{ url("admin/siswa") }}/' + siswaId + '/quick-detail',
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            let siswa = response.siswa;
                            let fotoUrl = siswa.foto_profile_url || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(siswa.nama_lengkap) + '&size=200&background=3498db&color=FFFFFF';
                            let jkBadge = siswa.jenis_kelamin === 'L' 
                                ? '<span class="badge badge-primary"><i class="fas fa-male"></i> Laki-laki</span>' 
                                : '<span class="badge badge-danger"><i class="fas fa-female"></i> Perempuan</span>';
                            let jabatanBadge = siswa.is_ketua_kelas
                                ? '<span class="badge badge-warning ml-1"><i class="fas fa-crown mr-1"></i>Ketua Kelas</span>'
                                : '';
                            
                            let html = `
                                <div class="row">
                                    <div class="col-md-4 text-center">
                                        <img src="${fotoUrl}" alt="${siswa.nama_lengkap}" 
                                             class="img-thumbnail mb-3" style="width: 180px; height: 180px; object-fit: cover;">
                                        <h5 class="font-weight-bold">${siswa.nama_lengkap}</h5>
                                        <p class="text-muted">${siswa.nisn || '-'}</p>
                                        ${jkBadge} ${jabatanBadge}
                                    </div>
                                    <div class="col-md-8">
                                        <table class="table table-sm table-borderless">
                                            <tr>
                                                <th width="35%"><i class="fas fa-id-card text-primary"></i> NISN</th>
                                                <td>${siswa.nisn || '-'}</td>
                                            </tr>
                                            <tr>
                                                <th><i class="fas fa-id-badge text-info"></i> NIS</th>
                                                <td>${siswa.nis || '-'}</td>
                                            </tr>
                                            <tr>
                                                <th><i class="fas fa-birthday-cake text-warning"></i> Tanggal Lahir</th>
                                                <td>${siswa.tanggal_lahir_formatted || '-'}</td>
                                            </tr>
                                            <tr>
                                                <th><i class="fas fa-map-marker-alt text-danger"></i> Tempat Lahir</th>
                                                <td>${siswa.tempat_lahir || '-'}</td>
                                            </tr>
                                            <tr>
                                                <th><i class="fas fa-phone text-success"></i> No. HP</th>
                                                <td>${siswa.nomor_hp || '-'}</td>
                                            </tr>
                                            <tr>
                                                <th><i class="fas fa-envelope text-secondary"></i> Email</th>
                                                <td>${siswa.email || '-'}</td>
                                            </tr>
                                            <tr>
                                                <th><i class="fas fa-home text-info"></i> Alamat</th>
                                                <td>${siswa.alamat_siswa || '-'}</td>
                                            </tr>
                                            <tr>
                                                <th><i class="fas fa-school text-primary"></i> Asal Sekolah</th>
                                                <td>${siswa.nama_sekolah_asal || '-'}</td>
                                            </tr>
                                            <tr>
                                                <th><i class="fas fa-crown text-warning"></i> Jabatan Rombel</th>
                                                <td>${siswa.jabatan_rombel || 'Siswa'}${siswa.kelas_aktif ? ' — ' + siswa.kelas_aktif : ''}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            `;
                            
                            $('#detailSiswaContent').html(html);
                        } else {
                            $('#detailSiswaContent').html(`
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle"></i> ${response.message || 'Gagal memuat data siswa'}
                                </div>
                            `);
                        }
                    },
                    error: function(xhr) {
                        $('#detailSiswaContent').html(`
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> Gagal memuat data siswa. Silakan coba lagi.
                            </div>
                        `);
                    }
                });
            });

            // Remove Siswa - Show Modal
            @can('transfer-siswa-kelas')
            let transferSiswaId = null;
            $('.btn-transfer-siswa').on('click', function() {
                transferSiswaId = $(this).data('siswa-id');
                $('#formTransferSiswa')[0].reset();
                $('#transferStudentPhoto').attr('src', $(this).data('siswa-foto'));
                $('#transferStudentName').text($(this).data('siswa-nama'));
                $('#transferStudentMeta').text('NISN ' + $(this).data('siswa-nisn'));
                $('#modalTransferSiswa').modal('show');
            });

            $('#formTransferSiswa').on('submit', function(event) {
                event.preventDefault();
                if (!transferSiswaId || !$('#targetKelasId').val()) {
                    Swal.fire({icon:'warning', title:'Pilih rombel tujuan', text:'Tentukan rombel tujuan sebelum melanjutkan.'});
                    return;
                }

                const submitButton = $('#btnSubmitTransfer').prop('disabled', true);
                const transferUrl = @json(route('admin.kelas.siswa.transfer', ['kelas' => $kelas->id, 'siswa' => ':siswa']));
                Swal.fire({
                    title: 'Memindahkan siswa',
                    html: '<p class="mb-1">Memperbarui rombel aktif dan menyimpan riwayat perpindahan.</p><small class="text-muted">Mohon tunggu, jangan tutup halaman.</small>',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: transferUrl.replace(':siswa', transferSiswaId),
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#modalTransferSiswa').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Rombel berhasil dipindahkan',
                            text: response.message,
                            confirmButtonText: 'Selesai',
                            allowOutsideClick: false,
                            allowEscapeKey: false
                        }).then(() => window.location.reload());
                    },
                    error: function(xhr) {
                        Swal.fire({icon:'error', title:'Perpindahan gagal', text:xhr.responseJSON?.message || 'Terjadi kesalahan saat memindahkan siswa.'});
                    },
                    complete: function() {
                        submitButton.prop('disabled', false);
                    }
                });
            });

            $('#modalTransferSiswa').on('hidden.bs.modal', function() { transferSiswaId = null; });
            @endcan

            let _removeSiswaId = null;
            $('.btn-remove-siswa').on('click', function() {
                _removeSiswaId = $(this).data('siswa-id');
                let siswaNama = $(this).data('siswa-nama');
                $('#siswa-nama-display').text(siswaNama);
                $('#modalRemoveSiswa').modal('show');
            });

            // Remove Siswa - Confirm
            $('#btnConfirmRemoveSiswa').on('click', function() {
                if (!_removeSiswaId) return;
                let $btn = $(this).prop('disabled', true).text('Memproses...');

                $.ajax({
                    url: "{{ route('admin.kelas.siswa.remove', ['kelas' => $kelas->id, 'siswa' => ':siswa']) }}".replace(':siswa', _removeSiswaId),
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        $('#modalRemoveSiswa').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                        }).then(() => location.reload());
                    },
                    error: function(xhr) {
                        $btn.prop('disabled', false).html('<i class="fas fa-user-minus"></i> Keluarkan');
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

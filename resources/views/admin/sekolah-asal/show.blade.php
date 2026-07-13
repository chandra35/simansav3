@extends('adminlte::page')

@section('title', 'Detail Sekolah - ' . $sekolah->nama)

@section('content_header')
    <div class="simansa-page-hero">
        <div>
            <div class="simansa-page-hero__eyebrow">
                <i class="fas fa-school mr-1"></i> Detail Sekolah Asal
            </div>
            <h1>{{ $sekolah->nama }}</h1>
            <p>{{ $sekolah->npsn }} | NSM: {{ $sekolah->nsm ?: '-' }} | {{ $sekolah->alamat_lengkap ?: 'Wilayah belum lengkap' }}</p>
        </div>
        <div class="simansa-page-hero__actions">
            <button type="button"
                class="btn btn-light"
                id="btnEnrichSchool"
                data-url="{{ route('admin.sekolah-asal.enrich', $sekolah->npsn) }}">
                <i class="fas fa-sync-alt mr-1"></i>Lengkapi Data
            </button>
            <a href="{{ route('admin.sekolah-asal.index') }}" class="btn btn-outline-light">
                <i class="fas fa-arrow-left mr-1"></i>Kembali
            </a>
        </div>
    </div>
@stop

@section('content')
    {{-- Informasi Sekolah --}}
    <div class="card simansa-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i>Informasi Sekolah</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">NPSN</th>
                            <td>{{ $sekolah->npsn }}</td>
                        </tr>
                        <tr>
                            <th>NSM</th>
                            <td>{{ $sekolah->nsm ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Nama Sekolah</th>
                            <td><strong>{{ $sekolah->nama }}</strong></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @if($sekolah->status)
                                    <span class="badge badge-{{ $sekolah->status === 'NEGERI' ? 'primary' : 'success' }}">
                                        {{ $sekolah->status }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Bentuk Pendidikan</th>
                            <td>{{ $sekolah->bentuk_pendidikan ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Jenjang</th>
                            <td>{{ $sekolah->jenjang_pendidikan ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Kementerian Pembina</th>
                            <td>{{ $sekolah->kementerian_pembina ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Sumber Data</th>
                            <td>{{ $sekolah->sumber_data_sekolah ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Terakhir Update</th>
                            <td>{{ $sekolah->last_fetched_at?->format('d/m/Y H:i') ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Alamat</th>
                            <td>{{ $sekolah->alamat_lengkap }}</td>
                        </tr>
                        <tr>
                            <th>Kecamatan</th>
                            <td>{{ $sekolah->kecamatan ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Kabupaten/Kota</th>
                            <td>{{ $sekolah->kabupaten_kota ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Provinsi</th>
                            <td>{{ $sekolah->provinsi ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Kode Pos</th>
                            <td>{{ $sekolah->kode_pos ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>RT/RW</th>
                            <td>{{ $sekolah->rt ?? '-' }} / {{ $sekolah->rw ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Koordinat</th>
                            <td>{{ $sekolah->lintang ?? '-' }}, {{ $sekolah->bujur ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card simansa-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-file-signature mr-1"></i>Dokumen, Perizinan, dan Kontak</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr><th width="40%">NPYP</th><td>{{ $sekolah->npyp ?? '-' }}</td></tr>
                        <tr><th>No. SK Pendirian</th><td>{{ $sekolah->no_sk_pendirian ?? '-' }}</td></tr>
                        <tr><th>Tgl. SK Pendirian</th><td>{{ $sekolah->tanggal_sk_pendirian ?? '-' }}</td></tr>
                        <tr><th>No. SK Operasional</th><td>{{ $sekolah->no_sk_operasional ?? '-' }}</td></tr>
                        <tr><th>Tgl. SK Operasional</th><td>{{ $sekolah->tanggal_sk_operasional ?? '-' }}</td></tr>
                        <tr><th>Akreditasi</th><td>{{ $sekolah->akreditasi ?? '-' }}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr><th width="40%">Luas Tanah</th><td>{{ $sekolah->luas_tanah ?? '-' }}</td></tr>
                        <tr><th>Akses Internet</th><td>{{ $sekolah->akses_internet ?? '-' }}</td></tr>
                        <tr><th>Sumber Listrik</th><td>{{ $sekolah->sumber_listrik ?? '-' }}</td></tr>
                        <tr><th>Telepon</th><td>{{ $sekolah->telepon ?? '-' }}</td></tr>
                        <tr><th>Email</th><td>{{ $sekolah->email ?? '-' }}</td></tr>
                        <tr><th>Website</th><td>{{ $sekolah->website ?? '-' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistik Siswa --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total'] }}</h3>
                    <p>Total Siswa</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['aktif'] }}</h3>
                    <p>Siswa Aktif</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-check"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $stats['laki'] }}</h3>
                    <p>Laki-laki</p>
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

    {{-- Daftar Siswa --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user-graduate"></i> Daftar Siswa dari {{ $sekolah->nama }}</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tableSiswa" class="table table-bordered table-striped table-hover table-sm">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="12%">NISN</th>
                            <th width="25%">Nama Lengkap</th>
                            <th width="10%">JK</th>
                            <th width="20%">Kelas Saat Ini</th>
                            <th width="10%">Status</th>
                            <th width="8%">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    {{-- Grafik Sebaran Per Kelas (Optional) --}}
    @if($siswaPerKelas->count() > 0)
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-chart-pie"></i> Sebaran Siswa Per Kelas</h3>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($siswaPerKelas as $namaKelas => $siswaGroup)
                <div class="col-md-3 col-sm-6">
                    <div class="info-box bg-gradient-info">
                        <span class="info-box-icon"><i class="fas fa-door-open"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">{{ $namaKelas }}</span>
                            <span class="info-box-number">{{ $siswaGroup->total_siswa }} siswa</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Modal View Siswa -->
    <div class="modal fade" id="viewSiswaModal" tabindex="-1" role="dialog" aria-labelledby="viewSiswaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="viewSiswaModalLabel">
                        <i class="fas fa-user-graduate"></i> Detail Siswa
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs" id="siswaDetailTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="data-siswa-tab" data-toggle="tab" href="#data-siswa" role="tab">
                            <i class="fas fa-user"></i> Data Siswa
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="data-diri-tab" data-toggle="tab" href="#data-diri" role="tab">
                            <i class="fas fa-id-card"></i> Data Diri
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="data-ortu-tab" data-toggle="tab" href="#data-ortu" role="tab">
                            <i class="fas fa-users"></i> Data Orang Tua
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="dokumen-tab" data-toggle="tab" href="#dokumen" role="tab">
                            <i class="fas fa-file-alt"></i> Dokumen
                        </a>
                    </li>
                </ul>

                <!-- Tab panes -->
                <div class="tab-content mt-3" id="siswaDetailTabContent">
                    <div class="tab-pane fade show active" id="data-siswa" role="tabpanel">
                        <!-- Content will be loaded here -->
                    </div>
                    <div class="tab-pane fade" id="data-diri" role="tabpanel">
                        <!-- Content will be loaded here -->
                    </div>
                    <div class="tab-pane fade" id="data-ortu" role="tabpanel">
                        <!-- Content will be loaded here -->
                    </div>
                    <div class="tab-pane fade" id="dokumen" role="tabpanel">
                        <!-- Content will be loaded here -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
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
        .content-wrapper {
            background: #f3f7fc;
        }
        .simansa-page-hero {
            align-items: center;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 56%, #168891 100%);
            border-radius: 16px;
            box-shadow: 0 16px 34px rgba(37, 99, 235, 0.2);
            color: #fff;
            display: flex;
            justify-content: space-between;
            min-height: 118px;
            padding: 22px 24px;
        }
        .simansa-page-hero__eyebrow {
            font-size: 0.82rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .simansa-page-hero h1 {
            color: #fff;
            font-size: 1.45rem;
            font-weight: 850;
            margin: 10px 0 6px;
        }
        .simansa-page-hero p {
            color: rgba(255, 255, 255, 0.9);
            margin: 0;
        }
        .simansa-page-hero__actions {
            display: flex;
            flex: 0 0 auto;
            gap: 10px;
        }
        .simansa-card {
            border: 1px solid #dbe7f5;
            border-radius: 16px;
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.06);
            overflow: hidden;
        }
        .simansa-card .card-header {
            background: #fff;
            border-bottom: 1px solid #e5edf7;
            color: #0f172a;
            font-weight: 800;
        }
        .simansa-card th {
            color: #64748b;
            font-size: 0.86rem;
            font-weight: 800;
        }
        .small-box .inner h3 {
            font-size: 2.5rem;
        }
        .info-box {
            min-height: 80px;
        }
        .table-detail td {
            padding: 0.4rem;
        }
        @media (max-width: 768px) {
            .simansa-page-hero {
                align-items: stretch;
                flex-direction: column;
                gap: 14px;
            }
        }
    </style>
@stop

@section('js')
<script>
$(document).ready(function() {
    $('#btnEnrichSchool').on('click', function() {
        const button = $(this);
        const original = button.html();

        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Mengambil data...');

        $.ajax({
            url: button.data('url'),
            method: 'POST',
            data: {_token: '{{ csrf_token() }}'}
        }).done(function(response) {
            Swal.fire({
                icon: 'success',
                title: 'Data sekolah diperbarui',
                text: response.message || 'Data sekolah berhasil dilengkapi.',
                confirmButtonText: 'Muat Ulang'
            }).then(() => window.location.reload());
        }).fail(function(xhr) {
            button.prop('disabled', false).html(original);
            Swal.fire({
                icon: 'warning',
                title: 'Belum berhasil',
                text: xhr.responseJSON?.message || 'Data sekolah belum berhasil dilengkapi.',
                confirmButtonText: 'OK'
            });
        });
    });

    $('#tableSiswa').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('admin.sekolah-asal.siswa-data', $sekolah->npsn) }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'nisn', name: 'nisn' },
            { data: 'nama_lengkap', name: 'nama_lengkap' },
            { data: 'jenis_kelamin_badge', name: 'jenis_kelamin', orderable: false },
            { data: 'kelas_saat_ini', name: 'kelas_saat_ini', orderable: false },
            { data: 'status_badge', name: 'status_siswa', orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[2, 'asc']], // Sort by nama
        language: {
            processing: "Sedang memproses...",
            lengthMenu: "Tampilkan _MENU_ data",
            zeroRecords: "Tidak ditemukan data yang sesuai",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            infoFiltered: "(disaring dari _MAX_ total data)",
            search: "Cari:",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            }
        },
        responsive: true,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
        pageLength: 25
    });
});

// Fungsi untuk menampilkan detail siswa di modal
window.showSiswa = function(id) {
    $.get(`{{ url('admin/siswa') }}/${id}`)
        .done(function(response) {
            if (response.success) {
                const siswa = response.data;
                loadSiswaDataTab(siswa);
                loadDataDiriTab(siswa);
                loadDataOrtuTab(siswa);
                loadDokumenTab(siswa.id);
                $('#viewSiswaModal').modal('show');
            }
        })
        .fail(function() {
            Swal.fire('Error!', 'Gagal memuat data siswa', 'error');
        });
}

function loadSiswaDataTab(siswa) {
    const createdAt = new Date(siswa.created_at).toLocaleString('id-ID');
    const updatedAt = new Date(siswa.updated_at).toLocaleString('id-ID');
    
    const html = `
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-primary"><i class="fas fa-user"></i> Informasi Akun</h6>
                <table class="table table-detail table-sm table-bordered">
                    <tr><td width="40%" class="bg-light"><strong>NISN</strong></td><td>${siswa.nisn || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Nama Lengkap</strong></td><td>${siswa.nama_lengkap || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Jenis Kelamin</strong></td><td>${siswa.jenis_kelamin == 'L' ? '<span class="badge badge-primary">Laki-laki</span>' : '<span class="badge badge-danger">Perempuan</span>'}</td></tr>
                    <tr><td class="bg-light"><strong>Username</strong></td><td>${siswa.user.username || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Email</strong></td><td>${siswa.user.email || '-'}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-primary"><i class="fas fa-check-circle"></i> Status Kelengkapan</h6>
                <table class="table table-detail table-sm table-bordered">
                    <tr><td width="40%" class="bg-light"><strong>Data Ortu</strong></td><td>${siswa.data_ortu_completed ? '<span class="badge badge-success"><i class="fas fa-check"></i> Lengkap</span>' : '<span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> Belum Lengkap</span>'}</td></tr>
                    <tr><td class="bg-light"><strong>Data Diri</strong></td><td>${siswa.data_diri_completed ? '<span class="badge badge-success"><i class="fas fa-check"></i> Lengkap</span>' : '<span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> Belum Lengkap</span>'}</td></tr>
                    <tr><td class="bg-light"><strong>Status Login</strong></td><td>${siswa.user.is_first_login ? '<span class="badge badge-warning"><i class="fas fa-clock"></i> Belum Pernah Login</span>' : '<span class="badge badge-success"><i class="fas fa-check"></i> Sudah Login</span>'}</td></tr>
                </table>
                
                <h6 class="text-primary mt-3"><i class="fas fa-history"></i> History</h6>
                <table class="table table-detail table-sm table-bordered">
                    <tr><td width="40%" class="bg-light"><strong>Dibuat Oleh</strong></td><td>${siswa.created_by_name || 'System'}</td></tr>
                    <tr><td class="bg-light"><strong>Tanggal Dibuat</strong></td><td>${createdAt}</td></tr>
                    <tr><td class="bg-light"><strong>Diupdate Oleh</strong></td><td>${siswa.updated_by_name || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Tanggal Update</strong></td><td>${updatedAt}</td></tr>
                </table>
            </div>
        </div>
    `;
    $('#data-siswa').html(html);
}

function loadDataDiriTab(siswa) {
    const tglLahir = siswa.tanggal_lahir ? new Date(siswa.tanggal_lahir).toLocaleDateString('id-ID') : '-';
    
    const html = `
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-primary"><i class="fas fa-id-card"></i> Data Pribadi</h6>
                <table class="table table-detail table-sm table-bordered">
                    <tr><td width="40%" class="bg-light"><strong>NIK</strong></td><td>${siswa.nik || '<span class="text-muted">Belum diisi</span>'}</td></tr>
                    <tr><td class="bg-light"><strong>Tempat Lahir</strong></td><td>${siswa.tempat_lahir || '<span class="text-muted">Belum diisi</span>'}</td></tr>
                    <tr><td class="bg-light"><strong>Tanggal Lahir</strong></td><td>${tglLahir}</td></tr>
                    <tr><td class="bg-light"><strong>Jumlah Saudara</strong></td><td>${siswa.jumlah_saudara || '<span class="text-muted">Belum diisi</span>'}</td></tr>
                    <tr><td class="bg-light"><strong>Anak Ke</strong></td><td>${siswa.anak_ke || '<span class="text-muted">Belum diisi</span>'}</td></tr>
                    <tr><td class="bg-light"><strong>Hobi</strong></td><td>${siswa.hobi || '<span class="text-muted">Belum diisi</span>'}</td></tr>
                    <tr><td class="bg-light"><strong>Cita-cita</strong></td><td>${siswa.cita_cita || '<span class="text-muted">Belum diisi</span>'}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-primary"><i class="fas fa-map-marker-alt"></i> Alamat Siswa</h6>
                ${siswa.alamat_siswa ? `
                <table class="table table-detail table-sm table-bordered">
                    <tr><td width="40%" class="bg-light"><strong>Alamat</strong></td><td>${siswa.alamat_siswa}</td></tr>
                    <tr><td class="bg-light"><strong>RT / RW</strong></td><td>${siswa.rt_siswa || '-'} / ${siswa.rw_siswa || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Kodepos</strong></td><td>${siswa.kodepos_siswa || '-'}</td></tr>
                </table>
                ` : '<div class="alert alert-info"><i class="fas fa-info-circle"></i> Data alamat belum dilengkapi</div>'}
            </div>
        </div>
    `;
    $('#data-diri').html(html);
}

function loadDataOrtuTab(siswa) {
    const ortu = siswa.ortu;
    
    if (!ortu || !siswa.data_ortu_completed) {
        $('#data-ortu').html(`
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> Data orang tua belum dilengkapi
            </div>
        `);
        return;
    }
    
    const html = `
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-primary"><i class="fas fa-male"></i> Data Ayah</h6>
                <table class="table table-detail table-sm table-bordered">
                    <tr><td width="40%" class="bg-light"><strong>Status</strong></td><td>${ortu.status_ayah == 'masih_hidup' ? '<span class="badge badge-success">Masih Hidup</span>' : ortu.status_ayah == 'meninggal' ? '<span class="badge badge-secondary">Meninggal</span>' : '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Nama</strong></td><td>${ortu.nama_ayah || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>NIK</strong></td><td>${ortu.nik_ayah || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>HP</strong></td><td>${ortu.hp_ayah || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Pekerjaan</strong></td><td>${ortu.pekerjaan_ayah || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Penghasilan</strong></td><td>${ortu.penghasilan_ayah || '-'}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-primary"><i class="fas fa-female"></i> Data Ibu</h6>
                <table class="table table-detail table-sm table-bordered">
                    <tr><td width="40%" class="bg-light"><strong>Status</strong></td><td>${ortu.status_ibu == 'masih_hidup' ? '<span class="badge badge-success">Masih Hidup</span>' : ortu.status_ibu == 'meninggal' ? '<span class="badge badge-secondary">Meninggal</span>' : '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Nama</strong></td><td>${ortu.nama_ibu || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>NIK</strong></td><td>${ortu.nik_ibu || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>HP</strong></td><td>${ortu.hp_ibu || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Pekerjaan</strong></td><td>${ortu.pekerjaan_ibu || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Penghasilan</strong></td><td>${ortu.penghasilan_ibu || '-'}</td></tr>
                </table>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-12">
                <h6 class="text-primary"><i class="fas fa-home"></i> Alamat Orang Tua</h6>
                <table class="table table-detail table-sm table-bordered">
                    <tr><td width="20%" class="bg-light"><strong>No. KK</strong></td><td>${ortu.no_kk || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Alamat</strong></td><td>${ortu.alamat_ortu || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>RT / RW</strong></td><td>${ortu.rt_ortu || '-'} / ${ortu.rw_ortu || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Kodepos</strong></td><td>${ortu.kodepos || '-'}</td></tr>
                </table>
            </div>
        </div>
    `;
    $('#data-ortu').html(html);
}

function loadDokumenTab(siswaId) {
    $('#dokumen').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat dokumen...</div>');
    
    $.get(`{{ url('admin/siswa') }}/${siswaId}/dokumen`)
        .done(function(response) {
            if (response.success) {
                const dokumen = response.data;
                let html = '';
                
                if (dokumen.length === 0) {
                    html = `<div class="alert alert-info"><i class="fas fa-info-circle"></i> Belum ada dokumen yang diupload</div>`;
                } else {
                    html = '<div class="table-responsive"><table class="table table-sm table-bordered table-striped">';
                    html += '<thead><tr><th width="5%">No</th><th>Jenis Dokumen</th><th width="15%">Aksi</th></tr></thead><tbody>';
                    dokumen.forEach((doc, index) => {
                        const fileExt = doc.file_path ? doc.file_path.split('.').pop().toLowerCase() : '';
                        const isImage = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(fileExt);
                        const isPdf = fileExt === 'pdf';
                        
                        html += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${doc.jenis_dokumen}</td>
                                <td>
                                    <button onclick="previewDocument('${doc.file_url}', '${isImage ? 'image' : (isPdf ? 'pdf' : 'other')}', '${doc.jenis_dokumen}')" 
                                        class="btn btn-xs btn-info" title="Lihat Preview">
                                        <i class="fas fa-eye"></i> Lihat
                                    </button>
                                    <a href="${doc.file_url}" download class="btn btn-xs btn-success ml-1" title="Download">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </td>
                            </tr>
                        `;
                    });
                    html += '</tbody></table></div>';
                }
                
                $('#dokumen').html(html);
            }
        })
        .fail(function() {
            $('#dokumen').html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Gagal memuat dokumen</div>');
        });
}

// Function to preview document in popup window
window.previewDocument = function(url, type, title) {
    if (type === 'image') {
        // Open image in new window with zoom functionality
        const win = window.open('', 'ImagePreview', 'width=1000,height=800,scrollbars=yes,resizable=yes');
        win.document.write('<!DOCTYPE html><html><head><title>' + title + '</title>');
        win.document.write('<style>');
        win.document.write('* { margin: 0; padding: 0; box-sizing: border-box; }');
        win.document.write('body { background: #1a1a1a; font-family: Arial, sans-serif; overflow: hidden; }');
        win.document.write('.header { background: #2d2d2d; padding: 15px 20px; color: #fff; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.3); }');
        win.document.write('.header h3 { margin: 0; font-size: 18px; font-weight: 500; }');
        win.document.write('.controls { display: flex; gap: 10px; }');
        win.document.write('.btn { background: #007bff; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 14px; transition: background 0.3s; }');
        win.document.write('.btn:hover { background: #0056b3; }');
        win.document.write('.btn-success { background: #28a745; }');
        win.document.write('.btn-success:hover { background: #1e7e34; }');
        win.document.write('.btn-danger { background: #dc3545; }');
        win.document.write('.btn-danger:hover { background: #c82333; }');
        win.document.write('.image-container { width: 100%; height: calc(100vh - 70px); display: flex; align-items: center; justify-content: center; overflow: auto; cursor: grab; }');
        win.document.write('.image-container img { max-width: 100%; max-height: 100%; object-fit: contain; transition: transform 0.3s; user-select: none; }');
        win.document.write('.zoom-info { position: absolute; bottom: 20px; right: 20px; background: rgba(0,0,0,0.8); color: white; padding: 8px 15px; border-radius: 20px; font-size: 14px; }');
        win.document.write('</style></head><body>');
        win.document.write('<div class="header"><h3>' + title + '</h3>');
        win.document.write('<div class="controls">');
        win.document.write('<button class="btn" onclick="zoomOut()">🔍 Zoom Out</button>');
        win.document.write('<button class="btn" onclick="resetZoom()">↺ Reset</button>');
        win.document.write('<button class="btn" onclick="zoomIn()">🔍 Zoom In</button>');
        win.document.write('<a href="' + url + '" download class="btn btn-success" style="text-decoration:none;">⬇ Download</a>');
        win.document.write('<button class="btn btn-danger" onclick="window.close()">✕ Close</button>');
        win.document.write('</div></div>');
        win.document.write('<div class="image-container" id="imageContainer">');
        win.document.write('<img src="' + url + '" id="previewImage" alt="' + title + '">');
        win.document.write('<div class="zoom-info" id="zoomInfo">100%</div></div>');
        win.document.write('<scr' + 'ipt>');
        win.document.write('let scale = 1;');
        win.document.write('const img = document.getElementById("previewImage");');
        win.document.write('const zoomInfo = document.getElementById("zoomInfo");');
        win.document.write('function updateZoom() { img.style.transform = "scale(" + scale + ")"; zoomInfo.textContent = Math.round(scale * 100) + "%"; }');
        win.document.write('function zoomIn() { scale = Math.min(scale + 0.2, 5); updateZoom(); }');
        win.document.write('function zoomOut() { scale = Math.max(scale - 0.2, 0.5); updateZoom(); }');
        win.document.write('function resetZoom() { scale = 1; updateZoom(); }');
        win.document.write('img.addEventListener("wheel", function(e) { e.preventDefault(); if(e.deltaY < 0) zoomIn(); else zoomOut(); });');
        win.document.write('</scr' + 'ipt>');
        win.document.write('</body></html>');
        win.document.close();
    } else if (type === 'pdf') {
        // Open PDF in new window
        const win = window.open('', 'PDFPreview', 'width=1000,height=800,scrollbars=yes,resizable=yes');
        win.document.write('<!DOCTYPE html><html><head><title>' + title + '</title>');
        win.document.write('<style>body{margin:0;padding:0;}iframe{width:100%;height:100vh;border:none;}</style>');
        win.document.write('</head><body>');
        win.document.write('<iframe src="' + url + '"></iframe>');
        win.document.write('</body></html>');
        win.document.close();
    } else {
        // For other files, force download
        window.location.href = url;
    }
}
</script>
@stop

@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)

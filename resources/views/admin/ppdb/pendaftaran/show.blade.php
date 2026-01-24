@extends('adminlte::page')

@section('title', 'Detail Pendaftaran - ' . $pendaftaran->nomor_pendaftaran)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-user-graduate mr-2"></i>Detail Pendaftaran</h1>
        <a href="{{ route('operator.pendaftar.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
@stop

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        {{ session('error') }}
    </div>
@endif

<div class="row">
    <!-- Left Column - Data -->
    <div class="col-lg-8">
        <!-- Status Card -->
        <div class="card card-{{ $pendaftaran->status_badge }}">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i> Status: {{ $pendaftaran->status_label }}
                </h3>
                <div class="card-tools">
                    <span class="badge badge-{{ $pendaftaran->status_badge }} badge-lg">{{ $pendaftaran->nomor_pendaftaran }}</span>
                </div>
            </div>
            @if($pendaftaran->catatan_verifikasi)
                <div class="card-body">
                    <strong>Catatan:</strong> {{ $pendaftaran->catatan_verifikasi }}
                </div>
            @endif
        </div>

        <!-- Data Calon Siswa -->
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title"><i class="fas fa-user"></i> Data Calon Siswa</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr><th width="40%">NISN</th><td>{{ $pendaftaran->nisn }}</td></tr>
                            <tr><th>NIK</th><td>{{ $pendaftaran->nik }}</td></tr>
                            <tr><th>Nama Lengkap</th><td><strong>{{ $pendaftaran->nama_lengkap }}</strong></td></tr>
                            <tr><th>Jenis Kelamin</th><td>{{ $pendaftaran->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td></tr>
                            <tr><th>TTL</th><td>{{ $pendaftaran->tempat_lahir }}, {{ $pendaftaran->tanggal_lahir?->format('d M Y') }}</td></tr>
                            <tr><th>Agama</th><td>{{ ucfirst($pendaftaran->agama) }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr><th width="40%">No. HP</th><td>{{ $pendaftaran->no_hp }}</td></tr>
                            <tr><th>Email</th><td>{{ $pendaftaran->email ?? '-' }}</td></tr>
                            <tr><th>Alamat</th><td>{{ $pendaftaran->alamat }}</td></tr>
                            <tr><th>RT/RW</th><td>{{ $pendaftaran->rt }}/{{ $pendaftaran->rw }}</td></tr>
                            <tr><th>Kelurahan</th><td>{{ $pendaftaran->kelurahan }}</td></tr>
                            <tr><th>Kecamatan</th><td>{{ $pendaftaran->kecamatan }}</td></tr>
                            <tr><th>Kabupaten</th><td>{{ $pendaftaran->kabupaten }}</td></tr>
                            <tr><th>Provinsi</th><td>{{ $pendaftaran->provinsi }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Asal Sekolah -->
        <div class="card">
            <div class="card-header bg-success">
                <h3 class="card-title"><i class="fas fa-school"></i> Asal Sekolah</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr><th width="40%">Nama Sekolah</th><td>{{ $pendaftaran->asal_sekolah }}</td></tr>
                            <tr><th>NPSN</th><td>{{ $pendaftaran->npsn_asal_sekolah ?? '-' }}</td></tr>
                            <tr><th>Tahun Lulus</th><td>{{ $pendaftaran->tahun_lulus }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr><th width="40%">No. Ijazah</th><td>{{ $pendaftaran->no_ijazah ?? '-' }}</td></tr>
                            <tr><th>No. SKHUN</th><td>{{ $pendaftaran->no_skhun ?? '-' }}</td></tr>
                            <tr><th>Nilai Rata-rata</th><td>{{ $pendaftaran->nilai_rata_rata ?? '-' }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Orang Tua -->
        <div class="card">
            <div class="card-header bg-warning">
                <h3 class="card-title"><i class="fas fa-users"></i> Data Orang Tua/Wali</h3>
            </div>
            <div class="card-body">
                @php
                    $pekerjaanOptions = \App\Models\PendaftaranPpdb::getPekerjaanOptions();
                    $penghasilanOptions = \App\Models\PendaftaranPpdb::getPenghasilanOptions();
                @endphp
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fas fa-male text-primary"></i> Data Ayah</h6>
                        <table class="table table-sm table-borderless">
                            <tr><th width="40%">Nama</th><td>{{ $pendaftaran->nama_ayah }}</td></tr>
                            <tr><th>NIK</th><td>{{ $pendaftaran->nik_ayah ?? '-' }}</td></tr>
                            <tr><th>Pekerjaan</th><td>{{ $pekerjaanOptions[$pendaftaran->pekerjaan_ayah] ?? '-' }}</td></tr>
                            <tr><th>Penghasilan</th><td>{{ $penghasilanOptions[$pendaftaran->penghasilan_ayah] ?? '-' }}</td></tr>
                            <tr><th>No. HP</th><td>{{ $pendaftaran->no_hp_ayah ?? '-' }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-female text-danger"></i> Data Ibu</h6>
                        <table class="table table-sm table-borderless">
                            <tr><th width="40%">Nama</th><td>{{ $pendaftaran->nama_ibu }}</td></tr>
                            <tr><th>NIK</th><td>{{ $pendaftaran->nik_ibu ?? '-' }}</td></tr>
                            <tr><th>Pekerjaan</th><td>{{ $pekerjaanOptions[$pendaftaran->pekerjaan_ibu] ?? '-' }}</td></tr>
                            <tr><th>Penghasilan</th><td>{{ $penghasilanOptions[$pendaftaran->penghasilan_ibu] ?? '-' }}</td></tr>
                            <tr><th>No. HP</th><td>{{ $pendaftaran->no_hp_ibu ?? '-' }}</td></tr>
                        </table>
                    </div>
                </div>
                @if($pendaftaran->nama_wali)
                    <hr>
                    <h6><i class="fas fa-user-friends text-success"></i> Data Wali</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr><th width="40%">Nama</th><td>{{ $pendaftaran->nama_wali }}</td></tr>
                                <tr><th>Hubungan</th><td>{{ ucfirst($pendaftaran->hubungan_wali) }}</td></tr>
                                <tr><th>NIK</th><td>{{ $pendaftaran->nik_wali ?? '-' }}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr><th width="40%">Pekerjaan</th><td>{{ $pekerjaanOptions[$pendaftaran->pekerjaan_wali] ?? '-' }}</td></tr>
                                <tr><th>Penghasilan</th><td>{{ $penghasilanOptions[$pendaftaran->penghasilan_wali] ?? '-' }}</td></tr>
                                <tr><th>No. HP</th><td>{{ $pendaftaran->no_hp_wali ?? '-' }}</td></tr>
                            </table>
                        </div>
                    </div>
                @endif
                <hr>
                <strong>Alamat Orang Tua:</strong> {{ $pendaftaran->alamat_orangtua }}
            </div>
        </div>

        <!-- Dokumen -->
        <div class="card">
            <div class="card-header bg-info">
                <h3 class="card-title"><i class="fas fa-file-alt"></i> Dokumen Terupload</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    @forelse($pendaftaran->dokumen as $doc)
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center p-2">
                                    @if($doc->isImage())
                                        <a href="{{ $doc->file_url }}" target="_blank">
                                            <img src="{{ $doc->file_url }}" class="img-fluid mb-2" style="max-height: 120px;">
                                        </a>
                                    @else
                                        <a href="{{ $doc->file_url }}" target="_blank">
                                            <i class="fas fa-file-pdf text-danger fa-4x mb-2"></i>
                                        </a>
                                    @endif
                                    <p class="mb-1 small"><strong>{{ $jenisDokumen[$doc->jenis_dokumen]['nama'] ?? $doc->jenis_dokumen }}</strong></p>
                                    <span class="badge badge-{{ $doc->status_badge }}">{{ $doc->status_verifikasi_label }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <p class="text-muted text-center">Belum ada dokumen terupload</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column - Actions -->
    <div class="col-lg-4">
        <!-- Pilihan Jurusan -->
        <div class="card">
            <div class="card-header bg-purple">
                <h3 class="card-title"><i class="fas fa-graduation-cap"></i> Pilihan Jurusan</h3>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <th>Jalur</th>
                        <td><span class="badge badge-primary">{{ ucfirst($pendaftaran->jalur_pendaftaran) }}</span></td>
                    </tr>
                    <tr>
                        <th>Pilihan 1</th>
                        <td>{{ $jurusanPilihan1?->nama ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Pilihan 2</th>
                        <td>{{ $jurusanPilihan2?->nama ?? '-' }}</td>
                    </tr>
                    @if($pendaftaran->diterima_di_jurusan)
                        <tr class="bg-success">
                            <th>Diterima di</th>
                            <td><strong>{{ $pendaftaran->diterima_di_jurusan }}</strong></td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>

        <!-- Action Buttons -->
        @if(in_array($pendaftaran->status, ['submitted', 'verified']))
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-cogs"></i> Aksi</h3>
                </div>
                <div class="card-body">
                    @if($pendaftaran->status == 'submitted')
                        <form action="{{ route('operator.pendaftar.verify', $pendaftaran) }}" method="POST" class="mb-3">
                            @csrf
                            <div class="form-group">
                                <label>Catatan Verifikasi</label>
                                <textarea name="catatan" class="form-control" rows="2" placeholder="Opsional"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-check-circle"></i> Verifikasi Data
                            </button>
                        </form>
                    @endif

                    @if($pendaftaran->status == 'verified')
                        <form action="{{ route('operator.pendaftar.accept', $pendaftaran) }}" method="POST" class="mb-3">
                            @csrf
                            <div class="form-group">
                                <label>Terima di Jurusan <span class="text-danger">*</span></label>
                                <select name="jurusan" class="form-control" required>
                                    <option value="">Pilih Jurusan</option>
                                    @foreach($jurusanList as $j)
                                        <option value="{{ $j->id }}" {{ $pendaftaran->jurusan_pilihan_1 == $j->id ? 'selected' : '' }}>
                                            {{ $j->nama }} (Sisa: {{ $j->sisa_kuota }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Catatan</label>
                                <textarea name="catatan" class="form-control" rows="2" placeholder="Opsional"></textarea>
                            </div>
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fas fa-check-double"></i> Terima Pendaftaran
                            </button>
                        </form>
                    @endif

                    <hr>

                    <button type="button" class="btn btn-danger btn-block" data-toggle="modal" data-target="#rejectModal">
                        <i class="fas fa-times-circle"></i> Tolak Pendaftaran
                    </button>
                </div>
            </div>
        @endif

        <!-- Info Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-clock"></i> Informasi</h3>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr><th>Tanggal Daftar</th><td>{{ $pendaftaran->created_at->format('d M Y, H:i') }}</td></tr>
                    <tr><th>Step Terakhir</th><td>{{ $pendaftaran->step_terakhir }}/5</td></tr>
                    @if($pendaftaran->tanggal_verifikasi)
                        <tr><th>Tgl Verifikasi</th><td>{{ $pendaftaran->tanggal_verifikasi->format('d M Y, H:i') }}</td></tr>
                    @endif
                    @if($pendaftaran->verifier)
                        <tr><th>Diverifikasi oleh</th><td>{{ $pendaftaran->verifier->name }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        @if(in_array($pendaftaran->status, ['draft', 'rejected']))
            <form action="{{ route('operator.pendaftar.destroy', $pendaftaran) }}" method="POST" 
                  onsubmit="return confirm('Yakin hapus pendaftaran ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-block">
                    <i class="fas fa-trash"></i> Hapus Pendaftaran
                </button>
            </form>
        @endif
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('operator.pendaftar.reject', $pendaftaran) }}" method="POST">
                @csrf
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white"><i class="fas fa-times-circle"></i> Tolak Pendaftaran</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="catatan" class="form-control" rows="4" required 
                                  placeholder="Jelaskan alasan penolakan agar calon siswa dapat memperbaiki..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Tolak Pendaftaran</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

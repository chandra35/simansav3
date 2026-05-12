@extends('adminlte::page')

@section('title', 'Detail Mutasi Siswa')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-exchange-alt"></i> Detail Mutasi Siswa</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                @can('edit-mutasi')
                @if($mutasiSiswa->isPending())
                <a href="{{ route('admin.mutasi-siswa.edit', $mutasiSiswa) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
                @endif
                @endcan
                <a href="{{ route('admin.mutasi-siswa.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>
@endsection

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    {{ session('success') }}
</div>
@endif

<div class="row">
    {{-- Detail mutasi --}}
    <div class="col-md-8">

        {{-- Badge status --}}
        <div class="callout callout-{{ $mutasiSiswa->statusBadgeColor }}">
            <h5>
                @if($mutasiSiswa->isMutasiMasuk())
                    <span class="badge badge-info"><i class="fas fa-sign-in-alt"></i> Mutasi Masuk</span>
                @else
                    <span class="badge badge-danger"><i class="fas fa-sign-out-alt"></i> Mutasi Keluar</span>
                @endif
                &nbsp;
                <span class="badge badge-{{ $mutasiSiswa->statusBadgeColor }}">{{ $mutasiSiswa->statusText }}</span>
            </h5>
            @if($mutasiSiswa->isApproved() || $mutasiSiswa->isRejected())
                <small>
                    Diverifikasi oleh: <strong>{{ $mutasiSiswa->verifikator?->name ?? '-' }}</strong>
                    pada {{ $mutasiSiswa->tanggal_verifikasi?->format('d/m/Y H:i') ?? '-' }}
                    @if($mutasiSiswa->catatan_verifikasi)
                        <br>Catatan: <em>{{ $mutasiSiswa->catatan_verifikasi }}</em>
                    @endif
                </small>
            @endif
        </div>

        {{-- Data siswa --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-graduate"></i> Data Siswa</h3>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Nama Lengkap</dt>
                    <dd class="col-sm-8">{{ $mutasiSiswa->siswa?->nama_lengkap ?? '-' }}</dd>
                    <dt class="col-sm-4">NISN</dt>
                    <dd class="col-sm-8">{{ $mutasiSiswa->siswa?->nisn ?? '-' }}</dd>
                    <dt class="col-sm-4">Status Siswa</dt>
                    <dd class="col-sm-8">{{ $mutasiSiswa->siswa?->status_siswa ?? '-' }}</dd>
                    <dt class="col-sm-4">Tanggal Mutasi</dt>
                    <dd class="col-sm-8">{{ $mutasiSiswa->tanggal_mutasi?->format('d F Y') ?? '-' }}</dd>
                    <dt class="col-sm-4">Tahun Pelajaran</dt>
                    <dd class="col-sm-8">{{ $mutasiSiswa->tahunPelajaran?->nama_tahun_pelajaran ?? $mutasiSiswa->tahunPelajaran?->nama ?? '-' }}</dd>
                </dl>
            </div>
        </div>

        {{-- Data sekolah asal/tujuan --}}
        @if($mutasiSiswa->isMutasiMasuk())
        <div class="card">
            <div class="card-header bg-info">
                <h3 class="card-title text-white"><i class="fas fa-school"></i> Sekolah Asal</h3>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Nama Sekolah</dt>
                    <dd class="col-sm-8">{{ $mutasiSiswa->sekolah_asal ?? '-' }}</dd>
                    <dt class="col-sm-4">NPSN</dt>
                    <dd class="col-sm-8">{{ $mutasiSiswa->npsn_sekolah_asal ?? '-' }}</dd>
                    <dt class="col-sm-4">Kelas Asal</dt>
                    <dd class="col-sm-8">{{ $mutasiSiswa->kelas_asal ?? '-' }}</dd>
                    <dt class="col-sm-4">Alamat</dt>
                    <dd class="col-sm-8">{{ $mutasiSiswa->alamat_sekolah_asal ?? '-' }}</dd>
                    <dt class="col-sm-4">Alasan Masuk</dt>
                    <dd class="col-sm-8">{{ $mutasiSiswa->alasan_mutasi_masuk ?? '-' }}</dd>
                </dl>
            </div>
        </div>
        @else
        <div class="card">
            <div class="card-header bg-danger">
                <h3 class="card-title text-white"><i class="fas fa-school"></i> Sekolah Tujuan</h3>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Nama Sekolah</dt>
                    <dd class="col-sm-8">{{ $mutasiSiswa->sekolah_tujuan ?? '-' }}</dd>
                    <dt class="col-sm-4">NPSN</dt>
                    <dd class="col-sm-8">{{ $mutasiSiswa->npsn_sekolah_tujuan ?? '-' }}</dd>
                    <dt class="col-sm-4">Alamat</dt>
                    <dd class="col-sm-8">{{ $mutasiSiswa->alamat_sekolah_tujuan ?? '-' }}</dd>
                    <dt class="col-sm-4">Alasan Keluar</dt>
                    <dd class="col-sm-8">{{ $mutasiSiswa->alasan_mutasi_keluar ?? '-' }}</dd>
                </dl>
            </div>
        </div>
        @endif

    </div>

    {{-- Kolom kanan: dokumen + verifikasi --}}
    <div class="col-md-4">

        {{-- Dokumen --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-pdf"></i> Dokumen Surat</h3>
            </div>
            <div class="card-body">
                @if($mutasiSiswa->nomor_surat_mutasi)
                    <p><strong>No. Surat:</strong> {{ $mutasiSiswa->nomor_surat_mutasi }}</p>
                @endif

                @if($mutasiSiswa->file_surat_mutasi)
                    <a href="{{ $mutasiSiswa->fileSuratUrl }}" target="_blank" class="btn btn-outline-danger btn-block">
                        <i class="fas fa-file-pdf"></i> Lihat Surat Mutasi
                    </a>
                @else
                    <p class="text-muted text-center"><i class="fas fa-file-times"></i> Belum ada dokumen</p>
                @endif

                @can('upload-dokumen-mutasi')
                <hr>
                <form id="formUpload" enctype="multipart/form-data">
                    @csrf
                    <div class="custom-file mb-2">
                        <input type="file" class="custom-file-input" id="file_upload" 
                            name="file_surat_mutasi" accept=".pdf">
                        <label class="custom-file-label" for="file_upload">Upload PDF...</label>
                    </div>
                    <button type="submit" class="btn btn-secondary btn-block btn-sm">
                        <i class="fas fa-upload"></i> Upload Surat
                    </button>
                </form>
                @endcan
            </div>
        </div>

        @if($mutasiSiswa->catatan)
        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-sticky-note"></i> Catatan</h3></div>
            <div class="card-body">{{ $mutasiSiswa->catatan }}</div>
        </div>
        @endif

        {{-- Verifikasi (hanya pending) --}}
        @if($mutasiSiswa->isPending())
        @canany(['approve-mutasi', 'reject-mutasi'])
        <div class="card card-outline card-warning">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-check-double"></i> Verifikasi</h3>
            </div>
            <div class="card-body">
                @can('approve-mutasi')
                <div class="form-group">
                    <label>Catatan Verifikasi (opsional)</label>
                    <textarea id="catatan_verifikasi" class="form-control" rows="3"
                        placeholder="Catatan verifikasi..."></textarea>
                </div>
                <button id="btnApprove" class="btn btn-success btn-block">
                    <i class="fas fa-check"></i> Setujui Mutasi
                </button>
                @endcan

                @canany(['approve-mutasi', 'reject-mutasi'])
                <hr>
                @endcanany

                @can('reject-mutasi')
                <div class="form-group">
                    <label>Alasan Penolakan <span class="text-danger">*</span></label>
                    <textarea id="alasan_reject" class="form-control" rows="3"
                        placeholder="Alasan penolakan (min 10 karakter)..."></textarea>
                </div>
                <button id="btnReject" class="btn btn-danger btn-block">
                    <i class="fas fa-times"></i> Tolak Mutasi
                </button>
                @endcan
            </div>
        </div>
        @endcanany
        @endif

    </div>
</div>

@endsection

@section('js')
<script>
$(function () {
    // Approve
    $('#btnApprove').on('click', function () {
        const catatan = $('#catatan_verifikasi').val();
        Swal.fire({
            title: 'Setujui mutasi ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Setujui',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#28a745',
        }).then(res => {
            if (res.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.mutasi-siswa.approve", $mutasiSiswa) }}',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}', catatan },
                    success: r => Swal.fire('Disetujui!', r.message, 'success').then(() => location.reload()),
                    error: xhr => Swal.fire('Gagal!', xhr.responseJSON?.message || 'Error', 'error'),
                });
            }
        });
    });

    // Reject
    $('#btnReject').on('click', function () {
        const alasan = $('#alasan_reject').val();
        if (!alasan || alasan.length < 10) {
            Swal.fire('Perhatian!', 'Alasan penolakan minimal 10 karakter.', 'warning');
            return;
        }
        Swal.fire({
            title: 'Tolak mutasi ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Tolak',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc3545',
        }).then(res => {
            if (res.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.mutasi-siswa.reject", $mutasiSiswa) }}',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}', alasan },
                    success: r => Swal.fire('Ditolak!', r.message, 'success').then(() => location.reload()),
                    error: xhr => Swal.fire('Gagal!', xhr.responseJSON?.message || 'Error', 'error'),
                });
            }
        });
    });

    // Upload dokumen
    $('#formUpload').on('submit', function (e) {
        e.preventDefault();
        const fd = new FormData(this);
        $.ajax({
            url: '{{ route("admin.mutasi-siswa.upload-dokumen", $mutasiSiswa) }}',
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: r => Swal.fire('Berhasil!', r.message, 'success').then(() => location.reload()),
            error: xhr => Swal.fire('Gagal!', xhr.responseJSON?.message || 'Error', 'error'),
        });
    });

    // Custom file label
    $('#file_upload').on('change', function () {
        const name = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').text(name || 'Upload PDF...');
    });
});
</script>
@endsection

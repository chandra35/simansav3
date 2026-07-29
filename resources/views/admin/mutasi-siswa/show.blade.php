@extends('adminlte::page')

@section('title', 'Detail Mutasi Siswa')
@section('plugins.Sweetalert2', true)

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
                    <i class="fas fa-edit mr-1"></i>Edit
                </a>
                @endif
                @endcan
                <a href="{{ route('admin.mutasi-siswa.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i>Kembali
                </a>
            </div>
        </div>
    </div>
@endsection

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
</div>
@endif

{{-- Status Banner --}}
@php
    $bannerColor = match($mutasiSiswa->status_verifikasi) {
        'pending'  => '#ffc107',
        'approved' => '#28a745',
        'rejected' => '#dc3545',
        default    => '#6c757d',
    };
    $bannerIcon = match($mutasiSiswa->status_verifikasi) {
        'pending'  => 'fa-clock',
        'approved' => 'fa-check-circle',
        'rejected' => 'fa-times-circle',
        default    => 'fa-question-circle',
    };
    $bannerText = match($mutasiSiswa->status_verifikasi) {
        'pending'  => 'Menunggu Verifikasi',
        'approved' => 'Mutasi Disetujui',
        'rejected' => 'Mutasi Ditolak',
        default    => $mutasiSiswa->statusText,
    };
@endphp
<div class="alert mb-3" style="background:{{ $bannerColor }}; color: {{ $mutasiSiswa->status_verifikasi === 'pending' ? '#212529' : '#fff' }}; border:none;">
    <div class="d-flex align-items-center justify-content-between flex-wrap">
        <div>
            <i class="fas {{ $bannerIcon }} fa-lg mr-2"></i>
            <strong>{{ $bannerText }}</strong>
            &nbsp;&bull;&nbsp;
            @if($mutasiSiswa->isMutasiMasuk())
                <span class="badge" style="background:rgba(255,255,255,.25); color:inherit;">
                    <i class="fas fa-sign-in-alt mr-1"></i>Mutasi Masuk
                </span>
            @else
                <span class="badge" style="background:rgba(255,255,255,.25); color:inherit;">
                    <i class="fas fa-sign-out-alt mr-1"></i>Mutasi Keluar
                </span>
            @endif
        </div>
        @if($mutasiSiswa->isApproved() || $mutasiSiswa->isRejected())
        <div class="text-right" style="{{ $mutasiSiswa->status_verifikasi === 'pending' ? 'color:#212529' : 'color:rgba(255,255,255,.85)' }}">
            <small>
                Diverifikasi: <strong>{{ $mutasiSiswa->verifikator?->name ?? '-' }}</strong>
                &bull; {{ $mutasiSiswa->tanggal_verifikasi?->format('d/m/Y H:i') }}
                @if($mutasiSiswa->catatan_verifikasi)
                    <br><em>"{{ $mutasiSiswa->catatan_verifikasi }}"</em>
                @endif
            </small>
        </div>
        @endif
    </div>
</div>

<div class="row">
    {{-- Kiri: Detail --}}
    <div class="col-md-8">

        {{-- Data Siswa --}}
        <div class="card">
            <div class="card-header" style="border-top: 3px solid #007bff;">
                <h3 class="card-title"><i class="fas fa-user-graduate mr-1"></i> Data Siswa</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tr>
                        <td class="text-muted pl-3" width="35%">Nama Lengkap</td>
                        <td><strong>{{ $mutasiSiswa->siswa?->nama_lengkap ?? '-' }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted pl-3">NISN</td>
                        <td>{{ $mutasiSiswa->siswa?->nisn ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted pl-3">Status Siswa</td>
                        <td>
                            <span class="badge badge-secondary">{{ $mutasiSiswa->siswa?->status_siswa ?? '-' }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted pl-3">Tanggal Mutasi</td>
                        <td>{{ $mutasiSiswa->tanggal_mutasi?->format('d F Y') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted pl-3">Tahun Pelajaran</td>
                        <td>{{ $mutasiSiswa->tahunPelajaran?->nama_tahun_pelajaran ?? $mutasiSiswa->tahunPelajaran?->nama ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Data Sekolah --}}
        @if($mutasiSiswa->isMutasiMasuk())
        <div class="card">
            <div class="card-header bg-info" style="border-top: 3px solid #17a2b8;">
                <h3 class="card-title text-white">
                    <i class="fas fa-school mr-1"></i> Sekolah Asal
                </h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tr>
                        <td class="text-muted pl-3" width="35%">Nama Sekolah</td>
                        <td><strong>{{ $mutasiSiswa->sekolah_asal ?? '-' }}</strong></td>
                    </tr>
                    @if($mutasiSiswa->npsn_sekolah_asal)
                    <tr>
                        <td class="text-muted pl-3">NPSN</td>
                        <td>{{ $mutasiSiswa->npsn_sekolah_asal }}</td>
                    </tr>
                    @endif
                    @if($mutasiSiswa->kelas_asal)
                    <tr>
                        <td class="text-muted pl-3">Kelas Asal</td>
                        <td>{{ $mutasiSiswa->kelas_asal }}</td>
                    </tr>
                    @endif
                    @if($mutasiSiswa->alamat_sekolah_asal)
                    <tr>
                        <td class="text-muted pl-3">Alamat</td>
                        <td>{{ $mutasiSiswa->alamat_sekolah_asal }}</td>
                    </tr>
                    @endif
                    @if($mutasiSiswa->alasan_mutasi_masuk)
                    <tr>
                        <td class="text-muted pl-3">Alasan Masuk</td>
                        <td>{{ $mutasiSiswa->alasan_mutasi_masuk }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
        @else
        <div class="card">
            <div class="card-header bg-danger" style="border-top: 3px solid #dc3545;">
                <h3 class="card-title text-white">
                    <i class="fas fa-school mr-1"></i> Sekolah Tujuan
                </h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tr>
                        <td class="text-muted pl-3" width="35%">Nama Sekolah</td>
                        <td>
                            @if($mutasiSiswa->sekolah_tujuan)
                                <strong>{{ $mutasiSiswa->sekolah_tujuan }}</strong>
                            @else
                                <span class="text-muted"><i class="fas fa-clock mr-1"></i>Belum ditentukan</span>
                            @endif
                        </td>
                    </tr>
                    @if($mutasiSiswa->npsn_sekolah_tujuan)
                    <tr>
                        <td class="text-muted pl-3">NPSN</td>
                        <td>{{ $mutasiSiswa->npsn_sekolah_tujuan }}</td>
                    </tr>
                    @endif
                    @if($mutasiSiswa->alamat_sekolah_tujuan)
                    <tr>
                        <td class="text-muted pl-3">Alamat</td>
                        <td>{{ $mutasiSiswa->alamat_sekolah_tujuan }}</td>
                    </tr>
                    @endif
                    @if($mutasiSiswa->alasan_mutasi_keluar)
                    <tr>
                        <td class="text-muted pl-3">Alasan Keluar</td>
                        <td>{{ $mutasiSiswa->alasan_mutasi_keluar }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
        @endif

    </div>

    {{-- Kanan: Dokumen + Verifikasi --}}
    <div class="col-md-4">

        {{-- Dokumen --}}
        <div class="card">
            <div class="card-header" style="border-top: 3px solid #6c757d;">
                <h3 class="card-title"><i class="fas fa-file-pdf mr-1"></i> Dokumen</h3>
            </div>
            <div class="card-body">
                @if($mutasiSiswa->nomor_surat_mutasi)
                <div class="mb-3">
                    <small class="text-muted d-block">Nomor Surat:</small>
                    <strong>{{ $mutasiSiswa->nomor_surat_mutasi }}</strong>
                </div>
                @endif

                @if($mutasiSiswa->file_surat_mutasi)
                    <a href="{{ $mutasiSiswa->fileSuratUrl }}" target="_blank" class="btn btn-outline-danger btn-block">
                        <i class="fas fa-file-pdf mr-1"></i> Lihat Surat Mutasi
                    </a>
                @else
                    <div class="text-center py-3 text-muted">
                        <i class="fas fa-file-times fa-2x mb-2 d-block" style="opacity:.4"></i>
                        <small>Belum ada dokumen</small>
                    </div>
                @endif

                @can('upload-dokumen-mutasi')
                <hr>
                <form id="formUpload" enctype="multipart/form-data">
                    @csrf
                    <div class="custom-file mb-2">
                        <input type="file" class="custom-file-input" id="file_upload"
                            name="file_surat_mutasi" accept=".pdf">
                        <label class="custom-file-label" for="file_upload">
                            {{ $mutasiSiswa->file_surat_mutasi ? 'Ganti PDF...' : 'Upload PDF...' }}
                        </label>
                    </div>
                    <button type="submit" class="btn btn-secondary btn-block btn-sm" id="btnUpload">
                        <i class="fas fa-upload mr-1"></i>Upload Surat
                    </button>
                </form>
                @endcan
            </div>
        </div>

        @if($mutasiSiswa->catatan)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-sticky-note mr-1"></i> Catatan</h3>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $mutasiSiswa->catatan }}</p>
            </div>
        </div>
        @endif

        {{-- Verifikasi Panel --}}
        @if($mutasiSiswa->isPending())
        @canany(['approve-mutasi', 'reject-mutasi'])
        <div class="card card-outline card-warning">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-check-double mr-1"></i> Verifikasi</h3>
            </div>
            <div class="card-body">
                @can('approve-mutasi')
                <form method="POST"
                      action="{{ route('admin.mutasi-siswa.approve', $mutasiSiswa) }}"
                      id="formApproveMutation">
                    @csrf
                    <div class="form-group">
                        <label class="small text-muted" for="catatan_verifikasi">Catatan verifikasi <em>(opsional)</em></label>
                        <textarea id="catatan_verifikasi" name="catatan" class="form-control form-control-sm" rows="2"
                            maxlength="2000" placeholder="Catatan persetujuan..."></textarea>
                    </div>
                    <button type="submit" id="btnApprove" class="btn btn-success btn-block">
                        <i class="fas fa-check mr-1"></i> Setujui Mutasi
                    </button>
                </form>
                @endcan

                @canany(['approve-mutasi','reject-mutasi'])
                <div class="my-3 border-top"></div>
                @endcanany

                @can('reject-mutasi')
                <div class="form-group">
                    <label class="small text-muted">Alasan penolakan <span class="text-danger">*</span></label>
                    <textarea id="alasan_reject" class="form-control form-control-sm" rows="3"
                        placeholder="Tuliskan alasan penolakan (min. 10 karakter)..."></textarea>
                </div>
                <button id="btnReject" class="btn btn-danger btn-block">
                    <i class="fas fa-times mr-1"></i> Tolak Mutasi
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

    // Persetujuan memakai form POST biasa sebagai fallback, lalu dikonfirmasi melalui modal.
    $('#formApproveMutation').on('submit', function (event) {
        if ($(this).data('confirmed')) return;

        if (typeof window.Swal === 'undefined') {
            const approved = window.confirm(
                'Setujui mutasi ini? Riwayat kelas aktif akan ditutup, status siswa menjadi mutasi keluar, dan akun siswa dinonaktifkan.'
            );

            if (!approved) {
                event.preventDefault();
                return;
            }

            $('#btnApprove')
                .prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin mr-1"></i>Memproses mutasi...');
            $(this).data('confirmed', true);
            return;
        }

        event.preventDefault();
        const form = this;
        Swal.fire({
            title: 'Setujui mutasi ini?',
            html: 'Riwayat kelas aktif akan ditutup, status siswa menjadi <strong>mutasi keluar</strong>, dan akun siswa dinonaktifkan.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-check mr-1"></i>Ya, Setujui',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#28a745',
        }).then(res => {
            if (!res.isConfirmed) return;
            const button = $('#btnApprove')
                .prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin mr-1"></i>Memproses mutasi...');
            $(form).data('confirmed', true);

            fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then(async response => {
                    const payload = await response.json().catch(() => ({}));
                    if (!response.ok) throw new Error(payload.message || 'Mutasi belum dapat disetujui.');
                    return payload;
                })
                .then(payload => Swal.fire({
                    title: 'Mutasi Disetujui',
                    text: payload.message,
                    icon: 'success',
                    confirmButtonText: 'Lihat Hasil',
                    confirmButtonColor: '#28a745',
                }))
                .then(() => window.location.reload())
                .catch(error => {
                    $(form).data('confirmed', false);
                    button
                        .prop('disabled', false)
                        .html('<i class="fas fa-check mr-1"></i>Setujui Mutasi');
                    Swal.fire('Gagal menyetujui mutasi', error.message, 'error');
                });
        });
    });

    // Reject
    $('#btnReject').on('click', function () {
        const alasan = $('#alasan_reject').val().trim();
        if (alasan.length < 10) {
            $('#alasan_reject').addClass('is-invalid').focus();
            Swal.fire('Perhatian!', 'Alasan penolakan minimal 10 karakter.', 'warning');
            return;
        }
        $('#alasan_reject').removeClass('is-invalid');
        Swal.fire({
            title: 'Tolak mutasi ini?',
            html: `Alasan: <em>${alasan}</em>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-times mr-1"></i>Ya, Tolak',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc3545',
        }).then(res => {
            if (!res.isConfirmed) return;
            const btn = $('#btnReject').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Memproses...');
            $.ajax({
                url: '{{ route("admin.mutasi-siswa.reject", $mutasiSiswa) }}',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', alasan },
                success: r => Swal.fire({ title: 'Ditolak!', text: r.message, icon: 'success', timer: 1800, showConfirmButton: false }).then(() => location.reload()),
                error: xhr => {
                    btn.prop('disabled', false).html('<i class="fas fa-times mr-1"></i>Tolak Mutasi');
                    Swal.fire('Gagal!', xhr.responseJSON?.message || 'Error', 'error');
                },
            });
        });
    });

    // Upload dokumen
    $('#formUpload').on('submit', function (e) {
        e.preventDefault();
        const fd = new FormData(this);
        const btn = $('#btnUpload').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Mengunggah...');
        $.ajax({
            url: '{{ route("admin.mutasi-siswa.upload-dokumen", $mutasiSiswa) }}',
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: r => {
                Swal.fire({ title: 'Berhasil!', text: r.message, icon: 'success', timer: 1500, showConfirmButton: false })
                    .then(() => location.reload());
            },
            error: xhr => {
                btn.prop('disabled', false).html('<i class="fas fa-upload mr-1"></i>Upload Surat');
                Swal.fire('Gagal!', xhr.responseJSON?.message || 'Error', 'error');
            },
        });
    });

    // Custom file label
    $('#file_upload').on('change', function () {
        const name = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').text(name || 'Upload PDF...');
    });

    // Alasan reject live validation
    $('#alasan_reject').on('input', function () {
        if ($(this).val().trim().length >= 10) $(this).removeClass('is-invalid');
    });
});
</script>
@endsection

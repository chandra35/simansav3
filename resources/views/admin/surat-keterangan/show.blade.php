@extends('adminlte::page')

@section('title', 'Detail Surat Keterangan Siswa')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0 text-dark"><i class="fas fa-file-alt text-primary mr-2"></i>Detail Surat Keterangan Siswa</h1>
            <small class="text-muted">Nomor Surat: <strong>{{ $suratKeterangan->nomor_surat }}</strong></small>
        </div>
        <div>
            <a href="{{ route('admin.surat-keterangan.print', $suratKeterangan->id) }}" target="_blank" class="btn btn-primary btn-sm mr-1">
                <i class="fas fa-print mr-1"></i> Cetak Dokumen PDF
            </a>
            @can('manage-layanan-surat')
                <a href="{{ route('admin.surat-keterangan.edit', $suratKeterangan->id) }}" class="btn btn-warning btn-sm mr-1">
                    <i class="fas fa-edit mr-1"></i> Edit Data
                </a>
            @endcan
            <a href="{{ route('admin.surat-keterangan.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar
            </a>
        </div>
    </div>
@stop

@section('content')
    @php
        $data = $suratKeterangan->data_tambahan ?? [];
        $siswa = $suratKeterangan->siswa;
        $pejabat = $suratKeterangan->penandatangan;
    @endphp

    <div class="row">
        <!-- Kolom Kiri: Rincian Surat -->
        <div class="col-lg-8">
            <div class="card card-outline card-primary shadow-sm mb-3">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-file-invoice mr-1"></i> Informasi Surat Keterangan</h3>
                    <div class="card-tools">
                        <span class="badge badge-{{ $suratKeterangan->status_badge }} px-2 py-1">
                            {{ $suratKeterangan->status_label }}
                        </span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-hover mb-0">
                        <tr>
                            <th style="width: 30%;">Nomor Surat</th>
                            <td><strong class="text-primary font-size-lg">{{ $suratKeterangan->nomor_surat }}</strong></td>
                        </tr>
                        <tr>
                            <th>Jenis Template</th>
                            <td>{{ $suratKeterangan->template?->nama ?? 'Surat Keterangan Aktif Siswa' }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Surat</th>
                            <td>{{ $suratKeterangan->tanggal_surat?->translatedFormat('d F Y') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Peruntukan / Keperluan</th>
                            <td><strong>{{ $suratKeterangan->keperluan }}</strong></td>
                        </tr>
                        <tr>
                            <th>Pejabat Penandatangan</th>
                            <td>
                                <strong>{{ $data['nama_pejabat'] ?? ($pejabat?->nama_lengkap ?? '-') }}</strong><br>
                                <small class="text-muted">
                                    NIP: {{ $data['nip_pejabat'] ?? ($pejabat?->nip ?? '-') }} |
                                    Jabatan: {{ $data['jabatan_pejabat'] ?? ($suratKeterangan->jabatan_penandatangan ?? 'Kepala Madrasah') }} |
                                    Gol: {{ $data['pangkat_golongan_pejabat'] ?? 'Pembina Tk I / IV(b)' }}
                                </small>
                            </td>
                        </tr>
                        <tr>
                            <th>Riwayat Cetak</th>
                            <td>
                                <span class="badge badge-info">{{ $suratKeterangan->jumlah_cetak }} kali dicetak</span>
                            </td>
                        </tr>
                        <tr>
                            <th>Dibuat Oleh</th>
                            <td>{{ $suratKeterangan->createdBy?->name ?? 'Administrator' }} pada {{ $suratKeterangan->created_at?->translatedFormat('d F Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
                <div class="card-footer bg-light d-flex justify-content-between align-items-center">
                    <div>
                        <a href="{{ route('admin.surat-keterangan.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                    </div>
                    <div>
                        @if($suratKeterangan->status !== 'selesai')
                            <button type="button" class="btn btn-success btn-approve mr-1" data-id="{{ $suratKeterangan->id }}">
                                <i class="fas fa-check mr-1"></i> Tandai Selesai
                            </button>
                        @endif
                        <a href="{{ route('admin.surat-keterangan.print', $suratKeterangan->id) }}" target="_blank" class="btn btn-primary">
                            <i class="fas fa-print mr-1"></i> Cetak Dokumen PDF
                        </a>
                    </div>
                </div>
            </div>

            <!-- Preview Card -->
            <div class="card card-outline card-secondary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-eye mr-1"></i> Preview Isi Dokumen</h3>
                </div>
                <div class="card-body bg-light">
                    <div class="border bg-white p-4 mx-auto shadow-sm" style="max-width: 680px; font-family: 'Times New Roman', serif; line-height: 1.4;">
                        <div class="text-center font-weight-bold" style="font-size: 13pt; text-decoration: underline;">
                            SURAT KETERANGAN
                        </div>
                        <div class="text-center mb-3" style="font-size: 10.5pt;">
                            Nomor : {{ $suratKeterangan->nomor_surat }}
                        </div>

                        <p class="mb-2">Yang bertanda tangan di bawah ini :</p>
                        <table style="width: 100%; margin-left: 15px; margin-bottom: 10px; font-size: 10.5pt;">
                            <tr><td style="width: 180px;">Nama</td><td style="width: 15px;">:</td><td><strong>{{ $data['nama_pejabat'] ?? ($pejabat?->nama_lengkap ?? '-') }}</strong></td></tr>
                            <tr><td>NIP</td><td>:</td><td>{{ $data['nip_pejabat'] ?? ($pejabat?->nip ?? '-') }}</td></tr>
                            <tr><td>Pangkat / Golongan</td><td>:</td><td>{{ $data['pangkat_golongan_pejabat'] ?? 'Pembina Tk I / IV(b)' }}</td></tr>
                            <tr><td>Jabatan</td><td>:</td><td>{{ $data['jabatan_pejabat'] ?? 'Kepala Madrasah' }}</td></tr>
                            <tr><td>Instansi</td><td>:</td><td>{{ $data['instansi_pejabat'] ?? ($setting->nama_sekolah ?: 'MAN 1 Metro') }}</td></tr>
                        </table>

                        <p class="mb-2">Dengan ini menerangkan bahwa :</p>
                        <table style="width: 100%; margin-left: 15px; margin-bottom: 10px; font-size: 10.5pt;">
                            <tr><td style="width: 180px;">Nama Siswa</td><td style="width: 15px;">:</td><td><strong>{{ strtoupper($data['nama_siswa'] ?? ($siswa?->nama_lengkap ?? '-')) }}</strong></td></tr>
                            <tr><td>NIS / NISN</td><td>:</td><td>{{ $data['nis'] ?? ($siswa?->nis ?? '-') }} / {{ $data['nisn'] ?? ($siswa?->nisn ?? '-') }}</td></tr>
                            <tr><td>Tempat / Tanggal Lahir</td><td>:</td><td>{{ $data['tempat_lahir'] ?? ($siswa?->tempat_lahir ?? '') }}, {{ $data['tanggal_lahir'] ?? ($siswa?->tanggal_lahir ? \Carbon\Carbon::parse($siswa->tanggal_lahir)->translatedFormat('d F Y') : '') }}</td></tr>
                            <tr>
                                <td style="vertical-align: top;">Nama Orang Tua / Wali</td>
                                <td style="vertical-align: top;">:</td>
                                <td>
                                    Ayah : {{ strtoupper($data['nama_ayah'] ?? ($siswa?->ortu?->nama_ayah ?? '-')) }}<br>
                                    Ibu : {{ strtoupper($data['nama_ibu'] ?? ($siswa?->ortu?->nama_ibu ?? '-')) }}
                                </td>
                            </tr>
                            <tr><td>Asal Sekolah</td><td>:</td><td>{{ $data['instansi_pejabat'] ?? 'MAN 1 Metro' }}</td></tr>
                        </table>

                        <p style="text-indent: 25px; text-align: justify; margin-bottom: 8px;">
                            Adalah benar yang bersangkutan tercatat aktif sebagai Siswa di {{ $data['instansi_pejabat'] ?? 'MAN 1 Metro' }} Kelas <strong>{{ $data['kelas'] ?? '-' }}</strong> Tahun Pelajaran <strong>{{ $data['tahun_pelajaran'] ?? '2026/2027' }}</strong>.
                        </p>

                        <p style="margin-bottom: 8px;">
                            Surat keterangan ini dibuat untuk dipergunakan sebagai : <strong>{{ $suratKeterangan->keperluan }}</strong>
                        </p>

                        <p style="text-indent: 25px; margin-bottom: 25px;">
                            Demikian surat keterangan ini dibuat untuk dapat dipergunakan sebagaimana mestinya.
                        </p>

                        <div class="text-right" style="margin-right: 20px;">
                            <div>Metro, {{ $suratKeterangan->tanggal_surat ? $suratKeterangan->tanggal_surat->translatedFormat('d F Y') : date('d F Y') }}</div>
                            <div class="mb-4">{{ $data['jabatan_pejabat'] ?? 'Kepala Madrasah' }},</div>
                            <div class="mt-4 font-weight-bold" style="text-decoration: underline;">{{ $data['nama_pejabat'] ?? ($pejabat?->nama_lengkap ?? '-') }}</div>
                            <div>NIP. {{ $data['nip_pejabat'] ?? ($pejabat?->nip ?? '-') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Identitas Siswa -->
        <div class="col-lg-4">
            <div class="card card-outline card-success shadow-sm mb-3">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold text-success"><i class="fas fa-user-graduate mr-1"></i> Data Siswa Terkait</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0">
                        <tr>
                            <th style="width: 40%;" class="pl-3">Nama Lengkap</th>
                            <td class="pr-3"><strong>{{ $data['nama_siswa'] ?? ($siswa?->nama_lengkap ?? '-') }}</strong></td>
                        </tr>
                        <tr>
                            <th class="pl-3">NISN</th>
                            <td class="pr-3">{{ $data['nisn'] ?? ($siswa?->nisn ?? '-') }}</td>
                        </tr>
                        <tr>
                            <th class="pl-3">NIS</th>
                            <td class="pr-3">{{ $data['nis'] ?? ($siswa?->nis ?? '-') }}</td>
                        </tr>
                        <tr>
                            <th class="pl-3">Kelas Aktif</th>
                            <td class="pr-3"><span class="badge badge-primary">{{ $data['kelas'] ?? ($siswa?->kelasTahunAktif->first()?->nama ?? '-') }}</span></td>
                        </tr>
                        <tr>
                            <th class="pl-3">Tahun Pelajaran</th>
                            <td class="pr-3">{{ $data['tahun_pelajaran'] ?? '2026/2027' }}</td>
                        </tr>
                        <tr>
                            <th class="pl-3">TTL</th>
                            <td class="pr-3">{{ $data['tempat_lahir'] ?? ($siswa?->tempat_lahir ?? '-') }}, {{ $data['tanggal_lahir'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="pl-3">Nama Ayah</th>
                            <td class="pr-3">{{ $data['nama_ayah'] ?? ($siswa?->ortu?->nama_ayah ?? '-') }}</td>
                        </tr>
                        <tr>
                            <th class="pl-3">Nama Ibu</th>
                            <td class="pr-3">{{ $data['nama_ibu'] ?? ($siswa?->ortu?->nama_ibu ?? '-') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card card-outline card-info shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-print mr-1"></i> Aksi Cepat</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.surat-keterangan.print', $suratKeterangan->id) }}" target="_blank" class="btn btn-primary btn-block mb-2">
                        <i class="fas fa-file-pdf mr-1"></i> Buka / Cetak PDF
                    </a>
                    @can('manage-layanan-surat')
                        <a href="{{ route('admin.surat-keterangan.edit', $suratKeterangan->id) }}" class="btn btn-warning btn-block text-dark">
                            <i class="fas fa-edit mr-1"></i> Edit Data Surat
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
$(function() {
    $('.btn-approve').on('click', function() {
        var id = $(this).data('id');
        if (confirm('Tandai surat keterangan ini sebagai Selesai / Siap Cetak?')) {
            $.ajax({
                url: "{{ url('admin/tata-usaha/surat-keterangan') }}/" + id + "/approve",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(res) {
                    location.reload();
                },
                error: function() {
                    alert('Gagal menyetujui surat keterangan');
                }
            });
        }
    });
});
</script>
@stop
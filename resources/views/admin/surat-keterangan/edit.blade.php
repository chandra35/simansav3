@extends('adminlte::page')

@section('title', 'Edit Surat Keterangan Siswa')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0 text-dark"><i class="fas fa-edit text-warning mr-2"></i>Edit Surat Keterangan Siswa</h1>
            <small class="text-muted">Nomor Surat: <strong>{{ $suratKeterangan->nomor_surat }}</strong></small>
        </div>
        <div>
            <a href="{{ route('admin.surat-keterangan.show', $suratKeterangan->id) }}" class="btn btn-info btn-sm mr-1">
                <i class="fas fa-eye mr-1"></i> Lihat
            </a>
            <a href="{{ route('admin.surat-keterangan.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar
            </a>
        </div>
    </div>
@stop

@section('plugins.Select2', true)

@section('content')
    @php
        $data = $suratKeterangan->data_tambahan ?? [];
        $siswa = $suratKeterangan->siswa;
    @endphp

    <form action="{{ route('admin.surat-keterangan.update', $suratKeterangan->id) }}" method="POST" id="formSurat">
        @csrf
        @method('PUT')
        <div class="row">
            <!-- Kolom Kiri: Form Data Pokok & Penandatangan -->
            <div class="col-lg-7">
                <div class="card card-outline card-warning shadow-sm mb-3">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold"><i class="fas fa-info-circle mr-1"></i> Data Surat & Penandatangan</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="template_surat_id">Template Surat <span class="text-danger">*</span></label>
                                    <select name="template_surat_id" id="template_surat_id" class="form-control select2 @error('template_surat_id') is-invalid @enderror" required>
                                        @foreach($templates as $tmpl)
                                            <option value="{{ $tmpl->id }}" {{ (old('template_surat_id', $suratKeterangan->template_surat_id) == $tmpl->id) ? 'selected' : '' }}>
                                                {{ $tmpl->nama }} ({{ $tmpl->kode }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('template_surat_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nomor Surat</label>
                                    <input type="text" class="form-control bg-light font-weight-bold text-primary" value="{{ $suratKeterangan->nomor_surat }}" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tanggal_surat">Tanggal Surat <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_surat" id="tanggal_surat" 
                                        class="form-control @error('tanggal_surat') is-invalid @enderror" 
                                        value="{{ old('tanggal_surat', $suratKeterangan->tanggal_surat?->format('Y-m-d')) }}" required>
                                    @error('tanggal_surat')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Status Surat <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                        <option value="selesai" {{ old('status', $suratKeterangan->status) == 'selesai' ? 'selected' : '' }}>Selesai (Siap Cetak)</option>
                                        <option value="menunggu_ttd" {{ old('status', $suratKeterangan->status) == 'menunggu_ttd' ? 'selected' : '' }}>Menunggu TTD</option>
                                        <option value="draft" {{ old('status', $suratKeterangan->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="dibatalkan" {{ old('status', $suratKeterangan->status) == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                                    </select>
                                    @error('status')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="penandatangan_id">Pejabat Penandatangan <span class="text-danger">*</span></label>
                            <select name="penandatangan_id" id="penandatangan_id" class="form-control select2 @error('penandatangan_id') is-invalid @enderror" required>
                                @foreach($gtks as $gtk)
                                    <option value="{{ $gtk->id }}" {{ (old('penandatangan_id', $suratKeterangan->penandatangan_id) == $gtk->id) ? 'selected' : '' }}>
                                        {{ $gtk->nama_lengkap }} (NIP: {{ $gtk->nip ?: '-' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('penandatangan_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="jabatan_pejabat">Jabatan</label>
                                    <input type="text" name="data_tambahan[jabatan_pejabat]" id="jabatan_pejabat" 
                                        class="form-control" value="{{ old('data_tambahan.jabatan_pejabat', $data['jabatan_pejabat'] ?? $suratKeterangan->jabatan_penandatangan ?? 'Kepala Madrasah') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pangkat_golongan_pejabat">Pangkat / Golongan</label>
                                    <input type="text" name="data_tambahan[pangkat_golongan_pejabat]" id="pangkat_golongan_pejabat" 
                                        class="form-control" value="{{ old('data_tambahan.pangkat_golongan_pejabat', $data['pangkat_golongan_pejabat'] ?? 'Pembina Tk I / IV(b)') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Keperluan Surat -->
                <div class="card card-outline card-info shadow-sm mb-3">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold"><i class="fas fa-bullseye mr-1"></i> Keperluan Surat</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-0">
                            <label for="keperluan">Peruntukan / Keperluan Surat <span class="text-danger">*</span></label>
                            <input type="text" name="keperluan" id="keperluan" 
                                list="keperluanList"
                                class="form-control form-control-lg @error('keperluan') is-invalid @enderror" 
                                value="{{ old('keperluan', $suratKeterangan->keperluan) }}" required 
                                placeholder="Ketik atau pilih keperluan...">
                            <datalist id="keperluanList">
                                <option value="Persyaratan Tunjangan Gaji Orang Tua">
                                <option value="Persyaratan Beasiswa">
                                <option value="Persyaratan Tunjangan Anak (KP4)">
                                <option value="Pengajuan BPJS Kesehatan / Ketenagakerjaan">
                                <option value="Pembuatan Paspor">
                                <option value="Kelengkapan Berkas">
                                <option value="Persyaratan Lomba / Kegiatan">
                            </datalist>
                            <small class="text-muted">Akan dicetak pada kalimat: "Surat keterangan ini dibuat untuk dipergunakan sebagai : ..."</small>
                            @error('keperluan')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Detail Siswa -->
            <div class="col-lg-5">
                <div class="card card-outline card-success shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h3 class="card-title font-weight-bold text-success">
                            <i class="fas fa-user-graduate mr-1"></i> Data Siswa
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="siswa_id">Cari Ganti Siswa (Jika Perlu)</label>
                            <select name="siswa_id" id="siswa_id" class="form-control @error('siswa_id') is-invalid @enderror" required style="width: 100%;">
                                @if($siswa)
                                    <option value="{{ $siswa->id }}" selected>
                                        {{ $siswa->nama_lengkap }} (NISN: {{ $siswa->nisn }}) - Kelas {{ $data['kelas'] ?? ($siswa->kelasTahunAktif->first()?->nama ?? '-') }}
                                    </option>
                                @endif
                            </select>
                            @error('siswa_id')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div id="loadingSiswa" class="text-center py-3 d-none">
                            <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                            <p class="text-muted mt-2 mb-0">Mengambil data siswa...</p>
                        </div>

                        <!-- Panel Detail Siswa -->
                        <div id="siswaDetailBox" class="border rounded p-3 bg-light mt-2">
                            <h6 class="font-weight-bold text-primary mb-2 border-bottom pb-1">
                                <i class="fas fa-id-card mr-1"></i> Data Siswa
                            </h6>
                            
                            <div class="form-group mb-2">
                                <label class="small mb-0 font-weight-bold">Nama Siswa</label>
                                <input type="text" id="disp_nama" class="form-control form-control-sm bg-white" 
                                    value="{{ $data['nama_siswa'] ?? ($siswa?->nama_lengkap ?? '') }}" readonly>
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group mb-2">
                                        <label class="small mb-0 font-weight-bold">NIS</label>
                                        <input type="text" name="data_tambahan[nis]" id="disp_nis" class="form-control form-control-sm"
                                            value="{{ $data['nis'] ?? ($siswa?->nis ?? '') }}">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group mb-2">
                                        <label class="small mb-0 font-weight-bold">NISN</label>
                                        <input type="text" name="data_tambahan[nisn]" id="disp_nisn" class="form-control form-control-sm"
                                            value="{{ $data['nisn'] ?? ($siswa?->nisn ?? '') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group mb-2">
                                        <label class="small mb-0 font-weight-bold">Tempat Lahir</label>
                                        <input type="text" name="data_tambahan[tempat_lahir]" id="disp_tempat_lahir" class="form-control form-control-sm"
                                            value="{{ $data['tempat_lahir'] ?? ($siswa?->tempat_lahir ?? '') }}">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group mb-2">
                                        <label class="small mb-0 font-weight-bold">Tanggal Lahir</label>
                                        <input type="text" name="data_tambahan[tanggal_lahir]" id="disp_tanggal_lahir" class="form-control form-control-sm"
                                            value="{{ $data['tanggal_lahir'] ?? ($siswa?->tanggal_lahir ? \Carbon\Carbon::parse($siswa->tanggal_lahir)->translatedFormat('d F Y') : '') }}">
                                    </div>
                                </div>
                            </div>

                            <h6 class="font-weight-bold text-primary mt-3 mb-2 border-bottom pb-1">
                                <i class="fas fa-users mr-1"></i> Data Orang Tua / Wali
                            </h6>

                            <div class="form-group mb-2">
                                <label class="small mb-0 font-weight-bold">Nama Ayah</label>
                                <input type="text" name="data_tambahan[nama_ayah]" id="disp_nama_ayah" class="form-control form-control-sm" 
                                    value="{{ $data['nama_ayah'] ?? ($siswa?->ortu?->nama_ayah ?? '') }}" placeholder="Nama Ayah">
                            </div>

                            <div class="form-group mb-2">
                                <label class="small mb-0 font-weight-bold">Nama Ibu</label>
                                <input type="text" name="data_tambahan[nama_ibu]" id="disp_nama_ibu" class="form-control form-control-sm" 
                                    value="{{ $data['nama_ibu'] ?? ($siswa?->ortu?->nama_ibu ?? '') }}" placeholder="Nama Ibu">
                            </div>

                            <h6 class="font-weight-bold text-primary mt-3 mb-2 border-bottom pb-1">
                                <i class="fas fa-school mr-1"></i> Tingkat Kelas & Tapel
                            </h6>

                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group mb-0">
                                        <label class="small mb-0 font-weight-bold">Kelas Aktif</label>
                                        <input type="text" name="data_tambahan[kelas]" id="disp_kelas" class="form-control form-control-sm" 
                                            value="{{ $data['kelas'] ?? ($siswa?->kelasTahunAktif->first()?->nama ?? '') }}" placeholder="Contoh: XII A 4">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group mb-0">
                                        <label class="small mb-0 font-weight-bold">Tahun Pelajaran</label>
                                        <input type="text" name="data_tambahan[tahun_pelajaran]" id="disp_tapel" class="form-control form-control-sm" 
                                            value="{{ $data['tahun_pelajaran'] ?? '2026/2027' }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <button type="submit" class="btn btn-warning btn-block btn-lg font-weight-bold text-dark">
                            <i class="fas fa-save mr-2"></i> Perbarui Surat Keterangan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@stop

@section('js')
<script>
$(function() {
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });

    // Select2 AJAX untuk pencarian siswa
    $('#siswa_id').select2({
        theme: 'bootstrap4',
        placeholder: 'Ketik minimal 2 karakter untuk ganti siswa...',
        allowClear: false,
        ajax: {
            url: "{{ route('admin.surat-keterangan.search-siswa') }}",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: data
                };
            },
            cache: true
        },
        minimumInputLength: 2
    });

    // Ketika siswa diubah
    $('#siswa_id').on('select2:select', function (e) {
        var siswaId = e.params.data.id;
        if (!siswaId) return;

        $('#loadingSiswa').removeClass('d-none');
        var url = "{{ route('admin.surat-keterangan.siswa-data', ':id') }}".replace(':id', siswaId);

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                $('#loadingSiswa').addClass('d-none');
                $('#disp_nama').val(res.nama_lengkap);
                $('#disp_nis').val(res.nis || '-');
                $('#disp_nisn').val(res.nisn || '-');
                $('#disp_tempat_lahir').val(res.tempat_lahir || '');
                $('#disp_tanggal_lahir').val(res.tanggal_lahir || '');
                $('#disp_nama_ayah').val(res.nama_ayah || '-');
                $('#disp_nama_ibu').val(res.nama_ibu || '-');
                $('#disp_kelas').val(res.kelas || '-');
                if (res.tahun_pelajaran) {
                    $('#disp_tapel').val(res.tahun_pelajaran);
                }
            },
            error: function(xhr) {
                $('#loadingSiswa').addClass('d-none');
                alert('Gagal mengambil data siswa: ' + (xhr.responseJSON?.message || 'Error server'));
            }
        });
    });
});
</script>
@stop
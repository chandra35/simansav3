@extends('adminlte::page')

@section('title', 'Buat Surat Keterangan Siswa')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0 text-dark"><i class="fas fa-file-signature text-primary mr-2"></i>Buat Surat Keterangan Siswa</h1>
            <small class="text-muted">Layanan Surat Tata Usaha - Surat Keterangan Aktif Siswa</small>
        </div>
        <div>
            <a href="{{ route('admin.surat-keterangan.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar
            </a>
        </div>
    </div>
@stop

@section('plugins.Select2', true)

@section('content')
    <form action="{{ route('admin.surat-keterangan.store') }}" method="POST" id="formSurat">
        @csrf
        <div class="row">
            <!-- Kolom Kiri: Form Data Pokok & Siswa -->
            <div class="col-lg-7">
                <!-- Card 1: Pengaturan Surat -->
                <div class="card card-outline card-primary shadow-sm mb-3">
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
                                            <option value="{{ $tmpl->id }}" {{ (old('template_surat_id', $defaultTemplate?->id) == $tmpl->id) ? 'selected' : '' }}>
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
                                    <label>Preview Nomor Surat (Otomatis)</label>
                                    <input type="text" class="form-control bg-light font-weight-bold text-primary" value="{{ $nomorPreview }}" readonly>
                                    <small class="form-text text-muted">Nomor urut otomatis di-generate saat disimpan.</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tanggal_surat">Tanggal Surat <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_surat" id="tanggal_surat" 
                                        class="form-control @error('tanggal_surat') is-invalid @enderror" 
                                        value="{{ old('tanggal_surat', date('Y-m-d')) }}" required>
                                    @error('tanggal_surat')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Status Surat <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                        <option value="selesai" {{ old('status', 'selesai') == 'selesai' ? 'selected' : '' }}>Selesai (Siap Cetak)</option>
                                        <option value="menunggu_ttd" {{ old('status') == 'menunggu_ttd' ? 'selected' : '' }}>Menunggu TTD</option>
                                        <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
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
                                    <option value="{{ $gtk->id }}" {{ (old('penandatangan_id', $kamadGtk?->id) == $gtk->id) ? 'selected' : '' }}>
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
                                        class="form-control" value="{{ old('data_tambahan.jabatan_pejabat', 'Kepala Madrasah') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pangkat_golongan_pejabat">Pangkat / Golongan</label>
                                    <input type="text" name="data_tambahan[pangkat_golongan_pejabat]" id="pangkat_golongan_pejabat" 
                                        class="form-control" value="{{ old('data_tambahan.pangkat_golongan_pejabat', 'Pembina Tk I / IV(b)') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Keperluan Surat -->
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
                                value="{{ old('keperluan', 'Persyaratan Tunjangan Gaji Orang Tua') }}" required 
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

            <!-- Kolom Kanan: Pencarian Siswa & Detail Data Terisi Otomatis -->
            <div class="col-lg-5">
                <div class="card card-outline card-success shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h3 class="card-title font-weight-bold text-success">
                            <i class="fas fa-user-graduate mr-1"></i> Pilih Siswa
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="siswa_id">Cari Siswa (Ketik Nama / NISN) <span class="text-danger">*</span></label>
                            <select name="siswa_id" id="siswa_id" class="form-control @error('siswa_id') is-invalid @enderror" required style="width: 100%;">
                                <option value="">-- Ketik nama atau NISN siswa --</option>
                            </select>
                            @error('siswa_id')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div id="loadingSiswa" class="text-center py-3 d-none">
                            <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                            <p class="text-muted mt-2 mb-0">Mengambil data siswa...</p>
                        </div>

                        <!-- Panel Detail Siswa Otomatis Terisi -->
                        <div id="siswaDetailBox" class="border rounded p-3 bg-light mt-2 d-none">
                            <h6 class="font-weight-bold text-primary mb-2 border-bottom pb-1">
                                <i class="fas fa-id-card mr-1"></i> Data Siswa Terpilih
                            </h6>
                            
                            <div class="form-group mb-2">
                                <label class="small mb-0 font-weight-bold">Nama Siswa</label>
                                <input type="text" id="disp_nama" class="form-control form-control-sm bg-white" readonly>
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group mb-2">
                                        <label class="small mb-0 font-weight-bold">NIS</label>
                                        <input type="text" name="data_tambahan[nis]" id="disp_nis" class="form-control form-control-sm">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group mb-2">
                                        <label class="small mb-0 font-weight-bold">NISN</label>
                                        <input type="text" name="data_tambahan[nisn]" id="disp_nisn" class="form-control form-control-sm">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group mb-2">
                                        <label class="small mb-0 font-weight-bold">Tempat Lahir</label>
                                        <input type="text" name="data_tambahan[tempat_lahir]" id="disp_tempat_lahir" class="form-control form-control-sm">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group mb-2">
                                        <label class="small mb-0 font-weight-bold">Tanggal Lahir</label>
                                        <input type="text" name="data_tambahan[tanggal_lahir]" id="disp_tanggal_lahir" class="form-control form-control-sm">
                                    </div>
                                </div>
                            </div>

                            <h6 class="font-weight-bold text-primary mt-3 mb-2 border-bottom pb-1">
                                <i class="fas fa-users mr-1"></i> Data Orang Tua / Wali
                            </h6>

                            <div class="form-group mb-2">
                                <label class="small mb-0 font-weight-bold">Nama Ayah</label>
                                <input type="text" name="data_tambahan[nama_ayah]" id="disp_nama_ayah" class="form-control form-control-sm" placeholder="Nama Ayah">
                            </div>

                            <div class="form-group mb-2">
                                <label class="small mb-0 font-weight-bold">Nama Ibu</label>
                                <input type="text" name="data_tambahan[nama_ibu]" id="disp_nama_ibu" class="form-control form-control-sm" placeholder="Nama Ibu">
                            </div>

                            <h6 class="font-weight-bold text-primary mt-3 mb-2 border-bottom pb-1">
                                <i class="fas fa-school mr-1"></i> Tingkat Kelas & Tapel
                            </h6>

                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group mb-0">
                                        <label class="small mb-0 font-weight-bold">Kelas Aktif</label>
                                        <input type="text" name="data_tambahan[kelas]" id="disp_kelas" class="form-control form-control-sm" placeholder="Contoh: XII A 4">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group mb-0">
                                        <label class="small mb-0 font-weight-bold">Tahun Pelajaran</label>
                                        <input type="text" name="data_tambahan[tahun_pelajaran]" id="disp_tapel" class="form-control form-control-sm" value="{{ $tapel }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="noSiswaAlert" class="alert alert-info mt-3 mb-0">
                            <i class="fas fa-info-circle mr-1"></i> Silakan ketik nama atau NISN siswa di kolom pencarian di atas untuk memuat data secara otomatis.
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary btn-block btn-lg font-weight-bold">
                            <i class="fas fa-save mr-2"></i> Simpan & Buat Surat
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
        placeholder: 'Ketik minimal 2 karakter (Nama / NISN / NIS)...',
        allowClear: true,
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

    // Ketika siswa dipilih, ambil data lengkapnya
    $('#siswa_id').on('select2:select', function (e) {
        var siswaId = e.params.data.id;
        if (!siswaId) return;

        $('#loadingSiswa').removeClass('d-none');
        $('#noSiswaAlert').addClass('d-none');
        $('#siswaDetailBox').addClass('d-none');

        var url = "{{ route('admin.surat-keterangan.siswa-data', ':id') }}".replace(':id', siswaId);

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                $('#loadingSiswa').addClass('d-none');
                $('#siswaDetailBox').removeClass('d-none');

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

    $('#siswa_id').on('select2:clear', function () {
        $('#siswaDetailBox').addClass('d-none');
        $('#noSiswaAlert').removeClass('d-none');
    });
});
</script>
@stop
@extends('adminlte::page')

@section('title', 'Tambah Mutasi Siswa')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-exchange-alt"></i> Tambah Mutasi Siswa</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('admin.mutasi-siswa.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>
@endsection

@section('content')

@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach($errors->all() as $e)
            <li>{{ $e }}</li>
        @endforeach
    </ul>
</div>
@endif

<form action="{{ route('admin.mutasi-siswa.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="row">
        {{-- Kolom kiri --}}
        <div class="col-md-8">

            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title text-white"><i class="fas fa-user-graduate"></i> Data Siswa & Jenis Mutasi</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Siswa <span class="text-danger">*</span></label>
                        <select name="siswa_id" id="siswa_id" class="form-control select2" required>
                            <option value="">-- Pilih Siswa --</option>
                            @foreach($siswaList as $s)
                                <option value="{{ $s->id }}" 
                                    {{ (old('siswa_id', $selectedSiswa?->id) === $s->id) ? 'selected' : '' }}>
                                    {{ $s->nama_lengkap }} ({{ $s->nisn }}) — {{ $s->status_siswa }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Jenis Mutasi <span class="text-danger">*</span></label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="jenis_mutasi" 
                                    id="jenis_masuk" value="masuk" 
                                    {{ old('jenis_mutasi', 'masuk') === 'masuk' ? 'checked' : '' }}>
                                <label class="form-check-label" for="jenis_masuk">
                                    <span class="badge badge-info px-3 py-2">
                                        <i class="fas fa-sign-in-alt"></i> Mutasi Masuk
                                    </span>
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="jenis_mutasi" 
                                    id="jenis_keluar" value="keluar" 
                                    {{ old('jenis_mutasi') === 'keluar' ? 'checked' : '' }}>
                                <label class="form-check-label" for="jenis_keluar">
                                    <span class="badge badge-danger px-3 py-2">
                                        <i class="fas fa-sign-out-alt"></i> Mutasi Keluar
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Tahun Pelajaran <span class="text-danger">*</span></label>
                        <select name="tahun_pelajaran_id" class="form-control" required>
                            <option value="">-- Pilih Tahun Pelajaran --</option>
                            @foreach($tahunPelajarans as $tp)
                                <option value="{{ $tp->id }}"
                                    {{ old('tahun_pelajaran_id', $tahunAktif?->id) === $tp->id ? 'selected' : '' }}>
                                    {{ $tp->nama_tahun_pelajaran ?? $tp->nama ?? $tp->id }}
                                    {{ $tp->is_active ? '(Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Tanggal Mutasi <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_mutasi" class="form-control" 
                            value="{{ old('tanggal_mutasi', date('Y-m-d')) }}" required>
                    </div>
                </div>
            </div>

            {{-- Field Mutasi Masuk --}}
            <div class="card" id="card-masuk">
                <div class="card-header bg-info">
                    <h3 class="card-title text-white"><i class="fas fa-school"></i> Data Sekolah Asal</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Nama Sekolah Asal <span class="text-danger">*</span></label>
                        <input type="text" name="sekolah_asal" class="form-control" 
                            value="{{ old('sekolah_asal') }}" placeholder="Contoh: SMP Negeri 1 Metro">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>NPSN Sekolah Asal</label>
                                <input type="text" name="npsn_sekolah_asal" class="form-control"
                                    value="{{ old('npsn_sekolah_asal') }}" placeholder="8 digit NPSN">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kelas Asal</label>
                                <input type="text" name="kelas_asal" class="form-control"
                                    value="{{ old('kelas_asal') }}" placeholder="Contoh: VII-A">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Alamat Sekolah Asal</label>
                        <textarea name="alamat_sekolah_asal" class="form-control" rows="2"
                            placeholder="Alamat lengkap sekolah asal">{{ old('alamat_sekolah_asal') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Alasan Mutasi Masuk</label>
                        <textarea name="alasan_mutasi_masuk" class="form-control" rows="3"
                            placeholder="Alasan pindah ke sekolah ini...">{{ old('alasan_mutasi_masuk') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Field Mutasi Keluar --}}
            <div class="card d-none" id="card-keluar">
                <div class="card-header bg-danger">
                    <h3 class="card-title text-white"><i class="fas fa-school"></i> Data Sekolah Tujuan</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Nama Sekolah Tujuan <span class="text-danger">*</span></label>
                        <input type="text" name="sekolah_tujuan" class="form-control"
                            value="{{ old('sekolah_tujuan') }}" placeholder="Contoh: SMA Negeri 1 Bandar Lampung">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>NPSN Sekolah Tujuan</label>
                                <input type="text" name="npsn_sekolah_tujuan" class="form-control"
                                    value="{{ old('npsn_sekolah_tujuan') }}" placeholder="8 digit NPSN">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Alamat Sekolah Tujuan</label>
                        <textarea name="alamat_sekolah_tujuan" class="form-control" rows="2"
                            placeholder="Alamat lengkap sekolah tujuan">{{ old('alamat_sekolah_tujuan') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Alasan Mutasi Keluar</label>
                        <textarea name="alasan_mutasi_keluar" class="form-control" rows="3"
                            placeholder="Alasan keluar dari sekolah ini...">{{ old('alasan_mutasi_keluar') }}</textarea>
                    </div>
                </div>
            </div>

        </div>

        {{-- Kolom kanan --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-secondary">
                    <h3 class="card-title text-white"><i class="fas fa-file-alt"></i> Dokumen & Catatan</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Nomor Surat Mutasi</label>
                        <input type="text" name="nomor_surat_mutasi" class="form-control"
                            value="{{ old('nomor_surat_mutasi') }}" placeholder="No. surat...">
                    </div>
                    <div class="form-group">
                        <label>File Surat Mutasi <small class="text-muted">(PDF, max 5MB)</small></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="file_surat_mutasi"
                                name="file_surat_mutasi" accept=".pdf">
                            <label class="custom-file-label" for="file_surat_mutasi">Pilih file PDF...</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Catatan</label>
                        <textarea name="catatan" class="form-control" rows="4"
                            placeholder="Catatan tambahan...">{{ old('catatan') }}</textarea>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-save"></i> Simpan Mutasi
                    </button>
                </div>
            </div>
        </div>
    </div>

</form>

@endsection

@section('js')
<script>
$(function () {
    // Toggle card masuk/keluar
    function toggleJenisCard() {
        const jenis = $('input[name="jenis_mutasi"]:checked').val();
        if (jenis === 'masuk') {
            $('#card-masuk').removeClass('d-none');
            $('#card-keluar').addClass('d-none');
        } else {
            $('#card-masuk').addClass('d-none');
            $('#card-keluar').removeClass('d-none');
        }
    }
    $('input[name="jenis_mutasi"]').on('change', toggleJenisCard);
    toggleJenisCard();

    // Select2
    $('#siswa_id').select2({ placeholder: '-- Pilih Siswa --', width: '100%' });

    // Custom file label
    $('#file_surat_mutasi').on('change', function () {
        const name = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').text(name || 'Pilih file PDF...');
    });
});
</script>
@endsection

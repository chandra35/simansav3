@extends('adminlte::page')

@section('title', 'Edit Mutasi Siswa')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-edit"></i> Edit Mutasi Siswa</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('admin.mutasi-siswa.show', $mutasiSiswa) }}" class="btn btn-secondary">
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

<form action="{{ route('admin.mutasi-siswa.update', $mutasiSiswa) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row">
        {{-- Kolom kiri --}}
        <div class="col-md-8">

            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title text-white"><i class="fas fa-user-graduate"></i> Data Siswa</h3>
                </div>
                <div class="card-body">
                    {{-- Siswa tidak bisa diubah --}}
                    <div class="form-group">
                        <label>Siswa</label>
                        <input type="text" class="form-control" 
                            value="{{ $mutasiSiswa->siswa?->nama_lengkap }} ({{ $mutasiSiswa->siswa?->nisn }})" 
                            readonly>
                    </div>
                    <div class="form-group">
                        <label>Jenis Mutasi</label>
                        <input type="text" class="form-control" 
                            value="{{ $mutasiSiswa->jenisMutasiText }}" readonly>
                    </div>
                    <div class="form-group">
                        <label>Tahun Pelajaran <span class="text-danger">*</span></label>
                        <select name="tahun_pelajaran_id" class="form-control" required>
                            @foreach($tahunPelajarans as $tp)
                                <option value="{{ $tp->id }}"
                                    {{ old('tahun_pelajaran_id', $mutasiSiswa->tahun_pelajaran_id) === $tp->id ? 'selected' : '' }}>
                                    {{ $tp->nama_tahun_pelajaran ?? $tp->nama ?? $tp->id }}
                                    {{ $tp->is_active ? '(Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Mutasi <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_mutasi" class="form-control" 
                            value="{{ old('tanggal_mutasi', $mutasiSiswa->tanggal_mutasi?->format('Y-m-d')) }}" required>
                    </div>
                </div>
            </div>

            @if($mutasiSiswa->isMutasiMasuk())
            <div class="card">
                <div class="card-header bg-info">
                    <h3 class="card-title text-white"><i class="fas fa-school"></i> Data Sekolah Asal</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Nama Sekolah Asal <span class="text-danger">*</span></label>
                        <input type="text" name="sekolah_asal" class="form-control" 
                            value="{{ old('sekolah_asal', $mutasiSiswa->sekolah_asal) }}" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>NPSN</label>
                                <input type="text" name="npsn_sekolah_asal" class="form-control"
                                    value="{{ old('npsn_sekolah_asal', $mutasiSiswa->npsn_sekolah_asal) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kelas Asal</label>
                                <input type="text" name="kelas_asal" class="form-control"
                                    value="{{ old('kelas_asal', $mutasiSiswa->kelas_asal) }}">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Alamat</label>
                        <textarea name="alamat_sekolah_asal" class="form-control" rows="2">{{ old('alamat_sekolah_asal', $mutasiSiswa->alamat_sekolah_asal) }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Alasan Mutasi Masuk</label>
                        <textarea name="alasan_mutasi_masuk" class="form-control" rows="3">{{ old('alasan_mutasi_masuk', $mutasiSiswa->alasan_mutasi_masuk) }}</textarea>
                    </div>
                </div>
            </div>
            @else
            <div class="card">
                <div class="card-header bg-danger">
                    <h3 class="card-title text-white"><i class="fas fa-school"></i> Data Sekolah Tujuan</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Nama Sekolah Tujuan <span class="text-danger">*</span></label>
                        <input type="text" name="sekolah_tujuan" class="form-control"
                            value="{{ old('sekolah_tujuan', $mutasiSiswa->sekolah_tujuan) }}" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>NPSN</label>
                                <input type="text" name="npsn_sekolah_tujuan" class="form-control"
                                    value="{{ old('npsn_sekolah_tujuan', $mutasiSiswa->npsn_sekolah_tujuan) }}">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Alamat</label>
                        <textarea name="alamat_sekolah_tujuan" class="form-control" rows="2">{{ old('alamat_sekolah_tujuan', $mutasiSiswa->alamat_sekolah_tujuan) }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Alasan Mutasi Keluar</label>
                        <textarea name="alasan_mutasi_keluar" class="form-control" rows="3">{{ old('alasan_mutasi_keluar', $mutasiSiswa->alasan_mutasi_keluar) }}</textarea>
                    </div>
                </div>
            </div>
            @endif

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
                            value="{{ old('nomor_surat_mutasi', $mutasiSiswa->nomor_surat_mutasi) }}">
                    </div>
                    <div class="form-group">
                        <label>Ganti File Surat <small class="text-muted">(PDF, max 5MB)</small></label>
                        @if($mutasiSiswa->file_surat_mutasi)
                            <div class="mb-2">
                                <a href="{{ $mutasiSiswa->fileSuratUrl }}" target="_blank" class="btn btn-outline-danger btn-sm">
                                    <i class="fas fa-file-pdf"></i> File Saat Ini
                                </a>
                            </div>
                        @endif
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="file_surat_mutasi"
                                name="file_surat_mutasi" accept=".pdf">
                            <label class="custom-file-label" for="file_surat_mutasi">Pilih file baru (opsional)...</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Catatan</label>
                        <textarea name="catatan" class="form-control" rows="4">{{ old('catatan', $mutasiSiswa->catatan) }}</textarea>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-save"></i> Perbarui Mutasi
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
    $('#file_surat_mutasi').on('change', function () {
        const name = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').text(name || 'Pilih file baru (opsional)...');
    });
});
</script>
@endsection

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
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="fas fa-exclamation-triangle mr-1"></i>
    <strong>Terdapat kesalahan:</strong>
    <ul class="mb-0 mt-1">
        @foreach($errors->all() as $e)
            <li>{{ $e }}</li>
        @endforeach
    </ul>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    {{ session('error') }}
</div>
@endif

<form action="{{ route('admin.mutasi-siswa.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="row">
        {{-- Kolom kiri --}}
        <div class="col-md-8">

            {{-- Step 1: Data Siswa & Jenis --}}
            <div class="card">
                <div class="card-header" style="border-top: 3px solid #007bff;">
                    <h3 class="card-title">
                        <span class="badge badge-primary mr-2">1</span>
                        Data Siswa &amp; Jenis Mutasi
                    </h3>
                </div>
                <div class="card-body">

                    {{-- Siswa Search --}}
                    <div class="form-group">
                        <label for="siswa_id">Siswa <span class="text-danger">*</span></label>
                        <select name="siswa_id" id="siswa_id" class="form-control" required>
                            @if($selectedSiswa)
                            <option value="{{ $selectedSiswa->id }}" selected>
                                {{ $selectedSiswa->nama_lengkap }}
                            </option>
                            @endif
                        </select>
                        <small class="text-muted">
                            <i class="fas fa-search mr-1"></i>Ketik minimal 2 huruf nama atau NISN untuk mencari
                        </small>
                    </div>

                    {{-- Siswa info card (muncul setelah pilih) --}}
                    <div id="siswa-info" class="mb-3" style="display:none;">
                        <div class="alert alert-light border mb-0 py-2">
                            <div class="d-flex align-items-center">
                                <div class="mr-3">
                                    <i class="fas fa-user-graduate fa-2x text-primary"></i>
                                </div>
                                <div>
                                    <strong id="info-nama" class="d-block"></strong>
                                    <small class="text-muted">
                                        NISN: <span id="info-nisn"></span>
                                        &bull; Status: <span id="info-status" class="badge badge-secondary badge-sm"></span>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Jenis Mutasi --}}
                    <div class="form-group">
                        <label>Jenis Mutasi <span class="text-danger">*</span></label>
                        <div class="row">
                            <div class="col-6">
                                <label class="jenis-card w-100 mb-0" for="jenis_masuk" style="cursor:pointer;">
                                    <input type="radio" name="jenis_mutasi" id="jenis_masuk"
                                        value="masuk" class="d-none jenis-radio"
                                        {{ old('jenis_mutasi', 'masuk') === 'masuk' ? 'checked' : '' }}>
                                    <div class="border rounded p-3 text-center jenis-card-inner" id="card-jenis-masuk">
                                        <i class="fas fa-sign-in-alt fa-2x text-info mb-1 d-block"></i>
                                        <strong>Mutasi Masuk</strong>
                                        <small class="d-block text-muted mt-1">Siswa pindah dari sekolah lain ke sini</small>
                                    </div>
                                </label>
                            </div>
                            <div class="col-6">
                                <label class="jenis-card w-100 mb-0" for="jenis_keluar" style="cursor:pointer;">
                                    <input type="radio" name="jenis_mutasi" id="jenis_keluar"
                                        value="keluar" class="d-none jenis-radio"
                                        {{ old('jenis_mutasi') === 'keluar' ? 'checked' : '' }}>
                                    <div class="border rounded p-3 text-center jenis-card-inner" id="card-jenis-keluar">
                                        <i class="fas fa-sign-out-alt fa-2x text-danger mb-1 d-block"></i>
                                        <strong>Mutasi Keluar</strong>
                                        <small class="d-block text-muted mt-1">Siswa pindah ke sekolah lain</small>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
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
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal Mutasi <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_mutasi" class="form-control"
                                    value="{{ old('tanggal_mutasi', date('Y-m-d')) }}" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step 2: Data Sekolah --}}
            <div id="card-masuk">
                <div class="card">
                    <div class="card-header bg-info" style="border-top: 3px solid #17a2b8;">
                        <h3 class="card-title text-white">
                            <span class="badge badge-light text-info mr-2">2</span>
                            <i class="fas fa-school mr-1"></i> Data Sekolah Asal
                        </h3>
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
                                        value="{{ old('npsn_sekolah_asal') }}" placeholder="8 digit NPSN" maxlength="8">
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
                        <div class="form-group mb-0">
                            <label>Alasan Mutasi Masuk</label>
                            <textarea name="alasan_mutasi_masuk" class="form-control" rows="3"
                                placeholder="Alasan pindah ke sekolah ini...">{{ old('alasan_mutasi_masuk') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div id="card-keluar" class="d-none">
                <div class="card">
                    <div class="card-header bg-danger" style="border-top: 3px solid #dc3545;">
                        <h3 class="card-title text-white">
                            <span class="badge badge-light text-danger mr-2">2</span>
                            <i class="fas fa-school mr-1"></i> Data Sekolah Tujuan
                        </h3>
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
                                        value="{{ old('npsn_sekolah_tujuan') }}" placeholder="8 digit NPSN" maxlength="8">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Alamat Sekolah Tujuan</label>
                            <textarea name="alamat_sekolah_tujuan" class="form-control" rows="2"
                                placeholder="Alamat lengkap sekolah tujuan">{{ old('alamat_sekolah_tujuan') }}</textarea>
                        </div>
                        <div class="form-group mb-0">
                            <label>Alasan Mutasi Keluar</label>
                            <textarea name="alasan_mutasi_keluar" class="form-control" rows="3"
                                placeholder="Alasan keluar dari sekolah ini...">{{ old('alasan_mutasi_keluar') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Kolom kanan --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header" style="border-top: 3px solid #6c757d;">
                    <h3 class="card-title">
                        <span class="badge badge-secondary mr-2">3</span>
                        <i class="fas fa-file-alt mr-1"></i> Dokumen &amp; Catatan
                    </h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Nomor Surat Mutasi</label>
                        <input type="text" name="nomor_surat_mutasi" class="form-control"
                            value="{{ old('nomor_surat_mutasi') }}" placeholder="No. surat...">
                    </div>
                    <div class="form-group">
                        <label>
                            File Surat Mutasi
                            <small class="text-muted">(PDF, maks 5MB)</small>
                        </label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="file_surat_mutasi"
                                name="file_surat_mutasi" accept=".pdf">
                            <label class="custom-file-label" for="file_surat_mutasi">
                                Pilih file PDF...
                            </label>
                        </div>
                        <div id="file-preview" class="mt-2" style="display:none;">
                            <div class="alert alert-success py-2 mb-0">
                                <i class="fas fa-file-pdf mr-1 text-danger"></i>
                                <span id="file-name" class="small"></span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label>Catatan</label>
                        <textarea name="catatan" class="form-control" rows="4"
                            placeholder="Catatan tambahan...">{{ old('catatan') }}</textarea>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-save mr-1"></i> Simpan Mutasi
                    </button>
                    <a href="{{ route('admin.mutasi-siswa.index') }}" class="btn btn-secondary btn-block btn-sm mt-2">
                        Batal
                    </a>
                </div>
            </div>
        </div>
    </div>

</form>

@endsection

@section('css')
<style>
.jenis-card-inner {
    transition: all .2s;
}
.jenis-card-inner.active-masuk {
    border-color: #17a2b8 !important;
    background-color: #e8f7fb;
}
.jenis-card-inner.active-keluar {
    border-color: #dc3545 !important;
    background-color: #fdf0f1;
}
.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #007bff;
}
</style>
@endsection

@section('js')
<script>
$(function () {

    // ── Select2 AJAX siswa ──────────────────────────────────────────────────
    $('#siswa_id').select2({
        placeholder: 'Ketik min. 2 huruf nama atau NISN...',
        minimumInputLength: 2,
        width: '100%',
        language: {
            inputTooShort:  () => 'Ketik minimal 2 karakter untuk mencari...',
            searching:      () => '<i class="fas fa-spinner fa-spin mr-1"></i>Mencari...',
            noResults:      () => 'Siswa tidak ditemukan',
            loadingMore:    () => 'Memuat lebih banyak...',
        },
        ajax: {
            url: '{{ route("admin.mutasi-siswa.search-siswa") }}',
            dataType: 'json',
            delay: 350,
            data: params => ({ q: params.term }),
            processResults: data => ({
                results: data.map(s => ({
                    id:    s.id,
                    text:  s.nama_lengkap,
                    nisn:  s.nisn,
                    status: s.status_siswa,
                }))
            }),
            cache: true,
        },
        templateResult: function (s) {
            if (s.loading) return s.text;
            return $(`<div class="py-1">
                <strong>${s.text}</strong>
                <br><small class="text-muted">NISN: ${s.nisn || '-'} &bull; ${s.status || ''}</small>
            </div>`);
        },
        templateSelection: s => s.text || s.id,
    });

    // Tampilkan info setelah pilih siswa
    $('#siswa_id').on('select2:select', function (e) {
        const d = e.params.data;
        if (d.id) {
            $('#info-nama').text(d.text);
            $('#info-nisn').text(d.nisn || '-');
            $('#info-status').text(d.status || '-');
            $('#siswa-info').slideDown(200);
        }
    }).on('select2:unselect', function () {
        $('#siswa-info').slideUp(200);
    });

    @if($selectedSiswa)
    // Pre-select siswa dari query string
    var preOpt = new Option('{{ $selectedSiswa->nama_lengkap }}', '{{ $selectedSiswa->id }}', true, true);
    $('#siswa_id').append(preOpt).trigger('change');
    $('#info-nama').text('{{ $selectedSiswa->nama_lengkap }}');
    $('#info-nisn').text('{{ $selectedSiswa->nisn ?? "-" }}');
    $('#info-status').text('{{ $selectedSiswa->status_siswa ?? "-" }}');
    $('#siswa-info').show();
    @endif

    // ── Jenis mutasi card selector ─────────────────────────────────────────
    function updateJenisCard() {
        const jenis = $('input[name="jenis_mutasi"]:checked').val();
        $('#card-jenis-masuk').removeClass('active-masuk active-keluar');
        $('#card-jenis-keluar').removeClass('active-masuk active-keluar');
        if (jenis === 'masuk') {
            $('#card-jenis-masuk').addClass('active-masuk');
            $('#card-masuk').show();
            $('#card-keluar').addClass('d-none');
        } else {
            $('#card-jenis-keluar').addClass('active-keluar');
            $('#card-masuk').hide();
            $('#card-keluar').removeClass('d-none');
        }
    }
    $('input[name="jenis_mutasi"]').on('change', updateJenisCard);
    updateJenisCard();

    // ── File preview ───────────────────────────────────────────────────────
    $('#file_surat_mutasi').on('change', function () {
        const file = this.files[0];
        if (file) {
            const name = file.name;
            $(this).next('.custom-file-label').text(name);
            $('#file-name').text(name + ' (' + (file.size / 1024).toFixed(1) + ' KB)');
            $('#file-preview').show();
        } else {
            $(this).next('.custom-file-label').text('Pilih file PDF...');
            $('#file-preview').hide();
        }
    });
});
</script>
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

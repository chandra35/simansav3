@extends('adminlte::page')

@section('title', 'Buat Surat Keluar - Langkah 2: Data Surat')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h3 mb-0">
                <i class="fas fa-magic"></i> Wizard Surat Keluar
                <small class="text-muted">Langkah 2: Data Surat</small>
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <form method="POST" action="{{ route('admin.tata-usaha.surat-keluar.store') }}">
                @csrf
                <input type="hidden" name="template_surat_id" value="{{ $selectedTemplate->id }}">
                <input type="hidden" name="step" value="2">

                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Informasi Surat</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Template</label>
                                    <input type="text" class="form-control" value="{{ $selectedTemplate->nama }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Klasifikasi</label>
                                    <input type="text" class="form-control" value="{{ $selectedTemplate->kode_klasifikasi }}" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tanggal_surat">Tanggal Surat <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_surat" id="tanggal_surat" class="form-control @error('tanggal_surat') is-invalid @enderror" value="{{ old('tanggal_surat', date('Y-m-d')) }}" required>
                                    @error('tanggal_surat')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="penandatangan_id">Penandatangan <span class="text-danger">*</span></label>
                                    <select name="penandatangan_id" id="penandatangan_id" class="form-control @error('penandatangan_id') is-invalid @enderror" required>
                                        <option value="">-- Pilih Penandatangan --</option>
                                        @foreach($gtks as $gtk)
                                            <option value="{{ $gtk->id }}" data-nip="{{ $gtk->nip }}" data-jabatan="{{ $gtk->jabatan ?? 'Kepala Madrasah' }}">
                                                {{ $gtk->nama_lengkap }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('penandatangan_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="perihal">Perihal <span class="text-danger">*</span></label>
                            <input type="text" name="perihal" id="perihal" class="form-control @error('perihal') is-invalid @enderror" placeholder="Contoh: Undangan Rapat Kepala Sekolah" value="{{ old('perihal') }}" required>
                            @error('perihal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="tujuan">Tujuan (Opsional)</label>
                            <input type="text" name="tujuan" id="tujuan" class="form-control @error('tujuan') is-invalid @enderror" placeholder="Contoh: Dinas Pendidikan Provinsi" value="{{ old('tujuan') }}">
                            @error('tujuan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="jabatan_penandatangan">Jabatan Penandatangan (Opsional)</label>
                            <input type="text" name="jabatan_penandatangan" id="jabatan_penandatangan" class="form-control" placeholder="Contoh: Kepala Madrasah" value="{{ old('jabatan_penandatangan', 'Kepala Madrasah') }}">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <a href="{{ route('admin.tata-usaha.surat-keluar.create', ['step' => 1]) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <button type="button" class="btn btn-primary" onclick="nextStep()">
                        Lanjutkan <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </form>
        </div>

        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-header">
                    <h5 class="mb-0">Preview Nomor Surat</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0"><strong>Format:</strong> <code id="nomorPreview">----</code></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function nextStep() {
        const form = document.querySelector('form');
        if (!form.reportValidity()) {
            return;
        }

        const params = new URLSearchParams(new FormData(form));
        params.set('step', '3');
        window.location.href = `{{ route('admin.tata-usaha.surat-keluar.create') }}?${params.toString()}`;
    }
</script>
@endsection

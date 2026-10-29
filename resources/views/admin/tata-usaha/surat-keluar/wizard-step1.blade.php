@extends('adminlte::page')

@section('title', 'Buat Surat Keluar - Langkah 1: Pilih Template')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h3 mb-0">
                <i class="fas fa-magic"></i> Wizard Surat Keluar
                <small class="text-muted">Langkah 1: Pilih Template</small>
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Pilih Template Surat</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.tata-usaha.surat-keluar.create', ['step' => 2]) }}">
                        <div class="row">
                            @forelse($templates as $template)
                                <div class="col-md-6 mb-3">
                                    <label class="card template-card" style="cursor: pointer; border: 2px solid #e9ecef; padding: 1rem;">
                                        <input type="radio" name="template_id" value="{{ $template->id }}" required>
                                        <div class="ml-2">
                                            <strong>{{ $template->nama }}</strong>
                                            <div class="small text-muted">
                                                Jenis: {{ $template->jenis_label }}
                                                <br>Kode Klasifikasi: {{ $template->kode_klasifikasi }}
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle"></i> Tidak ada template surat yang aktif.
                                        Silakan hubungi administrator.
                                    </div>
                                </div>
                            @endforelse
                        </div>

                        <div class="form-group mt-4">
                            <a href="{{ route('admin.tata-usaha.surat-keluar.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Lanjutkan <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-header">
                    <h5 class="mb-0">Informasi</h5>
                </div>
                <div class="card-body small">
                    <p><strong>Wizard Surat Keluar</strong> memandu Anda dalam membuat surat keluar dengan:</p>
                    <ul>
                        <li>Memilih template surat</li>
                        <li>Mengisi data surat (perihal, tujuan, dll)</li>
                        <li>Mengisi konten surat</li>
                        <li>Menyimpan sebagai draft atau menunggu TTD</li>
                    </ul>
                    <p class="mt-3 text-muted">Nomor surat akan di-generate otomatis berdasarkan klasifikasi template yang dipilih.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .template-card input[type="radio"] {
        cursor: pointer;
    }
    .template-card:hover {
        border-color: #007bff !important;
        background-color: #f8f9fa;
    }
</style>
@endsection

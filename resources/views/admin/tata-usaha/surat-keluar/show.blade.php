@extends('adminlte::page')

@section('title', 'Lihat Surat Keluar')

@section('content_header')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between surat-page-header">
    <div class="mb-3 mb-md-0">
        <h1 class="m-0 text-dark text-break"><i class="fas fa-envelope-open-text text-primary mr-2"></i>{{ $suratKeluar->perihal }}</h1>
        <small class="text-muted">{{ $suratKeluar->nomor_surat ?: 'Draft belum memiliki nomor surat' }}</small>
    </div>
    <div class="d-flex flex-wrap" style="gap: .5rem;">
        <a href="{{ route('admin.tata-usaha.surat-keluar.print', $suratKeluar->id) }}" class="btn btn-primary" target="_blank"><i class="fas fa-print mr-1"></i> Cetak PDF</a>
        @if ($suratKeluar->status !== 'selesai')
            <a href="{{ route('admin.tata-usaha.surat-keluar.edit', $suratKeluar->id) }}" class="btn btn-warning"><i class="fas fa-edit mr-1"></i> Edit</a>
        @endif
        <a href="{{ route('admin.tata-usaha.surat-keluar.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
    </div>
</div>
@stop

@section('content')
<div class="surat-keluar-detail">

    <div class="row">
        <div class="col-md-9">
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Informasi Surat</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td style="width: 200px;"><strong>Nomor Surat</strong></td>
                            <td><code>{{ $suratKeluar->nomor_surat }}</code></td>
                        </tr>
                        <tr>
                            <td><strong>Tanggal Surat</strong></td>
                            <td>{{ $suratKeluar->tanggal_surat?->translatedFormat('l, d F Y') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Template</strong></td>
                            <td>{{ $suratKeluar->template?->nama }}</td>
                        </tr>
                        <tr>
                            <td><strong>Perihal</strong></td>
                            <td>{{ $suratKeluar->perihal }}</td>
                        </tr>
                        <tr>
                            <td><strong>Tujuan</strong></td>
                            <td>{{ $suratKeluar->tujuan ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Penandatangan</strong></td>
                            <td>{{ $suratKeluar->nama_penandatangan ?? ($suratKeluar->penandatangan?->nama_lengkap ?? '-') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Status</strong></td>
                            <td><span class="badge badge-{{ $suratKeluar->status_badge }}">{{ $suratKeluar->status_label }}</span></td>
                        </tr>
                        <tr>
                            <td><strong>Dibuat Oleh</strong></td>
                            <td>{{ $suratKeluar->createdBy?->name ?? '-' }} pada {{ $suratKeluar->created_at?->translatedFormat('d F Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Konten Surat</h5>
                </div>
                <div class="card-body">
                    <div class="surat-content border p-4 bg-light">
                        {!! nl2br(htmlspecialchars($suratKeluar->konten_generate)) !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-header">
                    <h5 class="mb-0">Aksi</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('admin.tata-usaha.surat-keluar.print', $suratKeluar->id) }}" class="list-group-item list-group-item-action" target="_blank">
                        <i class="fas fa-print"></i> Cetak PDF
                    </a>
                    @if ($suratKeluar->status !== 'selesai')
                        <a href="{{ route('admin.tata-usaha.surat-keluar.edit', $suratKeluar->id) }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-edit"></i> Edit Surat
                        </a>
                    @endif
                    @if (auth()->user()->can('manage-layanan-surat') || auth()->id() === $suratKeluar->created_by)
                        <a href="#" class="list-group-item list-group-item-action text-danger" onclick="deleteSurat(event)">
                            <i class="fas fa-trash"></i> Hapus Surat
                        </a>
                    @endif
                </div>
            </div>

            <div class="card mt-3 bg-info text-white">
                <div class="card-body small">
                    <p class="mb-0"><strong>Status {{ $suratKeluar->status_label }}</strong> - Surat dapat dicetak atau diarsipkan.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function deleteSurat(e) {
    e.preventDefault();
    if (confirm('Yakin ingin menghapus surat ini?')) {
        fetch(`{{ route('admin.tata-usaha.surat-keluar.destroy', $suratKeluar->id) }}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.href = '{{ route('admin.tata-usaha.surat-keluar.index') }}';
            }
        });
    }
}
</script>
@stop

@section('css')
<style>
    .surat-page-header { gap: 1rem; }
    .surat-keluar-detail .card { box-shadow: 0 2px 8px rgba(31, 41, 55, .07); }
    .surat-keluar-detail .table td { vertical-align: middle; overflow-wrap: anywhere; }
    .surat-keluar-detail .surat-content { min-height: 300px; overflow-wrap: anywhere; }
    @media (max-width: 575.98px) {
        .surat-keluar-detail .table td:first-child { width: 38% !important; }
        .surat-keluar-detail .list-group-item { padding: .8rem 1rem; }
    }
</style>
@stop

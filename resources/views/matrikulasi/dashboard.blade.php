@extends('adminlte::page')

@section('title', 'Dashboard Matrikulasi')

@section('content_header')
    <div class="mat-dash-head">
        <div>
            <h1>Dashboard Matrikulasi</h1>
            <p>Selamat datang, {{ $peserta->nama_lengkap }}.</p>
        </div>
        <span>{{ $peserta->periode?->tahunPelajaran?->nama ?? '-' }}</span>
    </div>
@stop

@section('content')
    <div class="mat-dash-grid">
        <div class="card mat-dash-card">
            <div class="card-body">
                <span class="mat-card-label">Kelompok Sementara</span>
                <strong>{{ $peserta->kelompok?->nama ?? 'Belum diassign' }}</strong>
                <p>{{ $peserta->kelompok?->label_kelas ?? 'Admin akan mengatur kelompok matrikulasi Anda.' }}</p>
            </div>
        </div>
        <div class="card mat-dash-card">
            <div class="card-body">
                <span class="mat-card-label">No.Tes</span>
                <strong>{{ $peserta->nomor_tes ?: '-' }}</strong>
                <p>NISN {{ $peserta->nisn ?: '-' }}</p>
            </div>
        </div>
        <div class="card mat-dash-card">
            <div class="card-body">
                <span class="mat-card-label">Dokumen PPDB</span>
                <strong>{{ $peserta->dokumens->count() }}</strong>
                <p>Dokumen tersimpan di staging matrikulasi.</p>
            </div>
        </div>
    </div>

    <div class="card mat-info-card">
        <div class="card-body">
            <h3>Data Matrikulasi</h3>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <tr><th>Nama</th><td>{{ $peserta->nama_lengkap }}</td></tr>
                    <tr><th>NIK</th><td>{{ $peserta->nik ?: '-' }}</td></tr>
                    <tr><th>Jurusan</th><td>{{ $peserta->jurusan_final ?: $peserta->jurusan_awal ?: '-' }}</td></tr>
                    <tr><th>Status</th><td>{{ ucfirst($peserta->status) }}</td></tr>
                </table>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .mat-dash-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        .mat-dash-head h1 {
            color: #111827;
            font-size: 1.45rem;
            font-weight: 800;
            margin: 0;
        }
        .mat-dash-head p {
            color: #64748b;
            margin: .2rem 0 0;
        }
        .mat-dash-head span {
            border: 1px solid #dbeafe;
            border-radius: 8px;
            background: #eff6ff;
            color: #1d4ed8;
            font-weight: 800;
            padding: .45rem .7rem;
        }
        .mat-dash-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .85rem;
        }
        .mat-dash-card,
        .mat-info-card {
            border: 1px solid #e6e8ef;
            border-radius: 8px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, .05);
        }
        .mat-card-label {
            display: block;
            color: #64748b;
            font-size: .75rem;
            font-weight: 800;
            text-transform: uppercase;
        }
        .mat-dash-card strong {
            display: block;
            color: #111827;
            font-size: 1.3rem;
            margin-top: .25rem;
        }
        .mat-dash-card p {
            color: #64748b;
            margin: .25rem 0 0;
        }
        .mat-info-card h3 {
            color: #111827;
            font-size: 1rem;
            font-weight: 800;
            margin-bottom: .8rem;
        }
        .mat-info-card th {
            width: 170px;
            color: #64748b;
        }
        @media (max-width: 767.98px) {
            .mat-dash-head,
            .mat-dash-grid {
                display: block;
            }
            .mat-dash-card {
                margin-bottom: .85rem;
            }
            .mat-dash-head span {
                display: inline-flex;
                margin-top: .75rem;
            }
        }
    </style>
@stop

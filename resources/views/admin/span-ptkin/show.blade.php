@extends('adminlte::page')

@section('title', $spanPtkinMenu->nama_menu)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-mosque"></i> {{ $spanPtkinMenu->nama_menu }}</h1>
        <a href="{{ route('admin.span-ptkin-menu.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid">
    @foreach(['success' => 'success', 'warning' => 'warning', 'error' => 'danger'] as $sessionKey => $alertType)
        @if(session($sessionKey))
            <div class="alert alert-{{ $alertType }}">{{ session($sessionKey) }}</div>
        @endif
    @endforeach

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Tahun Pelajaran</strong><br>
                            {{ $spanPtkinMenu->tahunPelajaran->nama ?? '-' }}
                        </div>
                        <div class="col-md-4">
                            <strong>Status</strong><br>
                            <span class="badge badge-{{ $spanPtkinMenu->is_active ? 'success' : 'secondary' }}">
                                {{ $spanPtkinMenu->is_active ? 'Aktif' : 'Non-Aktif' }}
                            </span>
                        </div>
                        <div class="col-md-4">
                            <strong>Periode</strong><br>
                            <small>{{ $spanPtkinMenu->tanggal_mulai?->format('d-m-Y H:i') ?? 'Tanpa batas' }}</small>
                        </div>
                    </div>
                    @if($spanPtkinMenu->konten_informasi)
                        <hr>
                        <div>{!! $spanPtkinMenu->konten_informasi !!}</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <form action="{{ route('admin.span-ptkin-menu.import-pdf', $spanPtkinMenu) }}" method="POST" enctype="multipart/form-data" class="card card-success card-outline">
                @csrf
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-file-pdf"></i> Import PDF Resmi</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Upload daftar siswa SPAN-PTKIN hasil unduhan resmi sekolah. Sistem akan mencocokkan berdasarkan NISN, lalu fallback ke nama siswa.</p>
                    <div class="form-group mb-0">
                        <input type="file" name="pdf_file" accept="application/pdf" class="form-control-file @error('pdf_file') is-invalid @enderror">
                        @error('pdf_file')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success btn-block">
                        <i class="fas fa-upload"></i> Import Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner"><h3>{{ $summary['kelas_12_total'] }}</h3><p>Total siswa kelas 12</p></div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner"><h3>{{ $summary['sudah_terimport'] }}</h3><p>Nomor sudah terimport</p></div>
                <div class="icon"><i class="fas fa-id-card"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-secondary">
                <div class="inner"><h3>{{ $summary['belum_terimport'] }}</h3><p>Belum ada nomor</p></div>
                <div class="icon"><i class="fas fa-clock"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-primary">
                <div class="inner"><h3>{{ $summary['terhubung_lulusan'] }}</h3><p>Terhubung ke lulusan</p></div>
                <div class="icon"><i class="fas fa-link"></i></div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-success">
        <div class="card-header">
            <h3 class="card-title">Monitoring Siswa Kelas 12</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="spanPtkinTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>NISN</th>
                            <th>Nama</th>
                            <th>Kelas</th>
                            <th>Tanggal Lahir</th>
                            <th>No. Pendaftaran</th>
                            <th>Import Terakhir</th>
                            <th>Status Lulusan</th>
                            <th>Jalur</th>
                            <th>PTKIN / Universitas</th>
                            <th>Program Studi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($monitoring as $index => $siswa)
                            @php
                                $registration = $siswa->spanPtkinRegistration;
                                $lulusan = optional($registration)->lulusan;
                                $universitas = optional($lulusan)->nama_universitas
                                    ?? optional($lulusan)->nama_universitas_manual
                                    ?? optional(optional($lulusan)->referensiPerguruanTinggi)->nama
                                    ?? '-';
                                $programStudi = optional($lulusan)->program_studi
                                    ?? optional($lulusan)->program_studi_manual
                                    ?? optional(optional($lulusan)->referensiProgramStudi)->nama_program_studi
                                    ?? '-';
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><code>{{ $siswa->nisn }}</code></td>
                                <td>{{ $siswa->nama_lengkap }}</td>
                                <td>{{ $siswa->kelasSaatIni->nama_kelas ?? '-' }}</td>
                                <td data-order="{{ $siswa->tanggal_lahir?->format('Y-m-d') ?? '' }}">{{ $siswa->tanggal_lahir?->format('d-m-Y') ?? '-' }}</td>
                                <td>{{ $registration?->nomor_pendaftaran ? $registration->nomor_pendaftaran : 'Belum terimport' }}</td>
                                <td data-order="{{ $registration?->imported_at?->timestamp ?? 0 }}">{{ $registration?->imported_at?->format('d-m-Y H:i') ?? '-' }}</td>
                                <td>
                                    @if($lulusan)
                                        <span class="badge badge-success">Terhubung</span>
                                    @else
                                        <span class="badge badge-secondary">Belum</span>
                                    @endif
                                </td>
                                <td>{{ $lulusan?->jalur_masuk ?? '-' }}</td>
                                <td>{{ $universitas }}</td>
                                <td>{{ $programStudi }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
@stop

@section('js')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script>
    $(function () {
        $('#spanPtkinTable').DataTable({
            pageLength: 25,
            order: [[5, 'desc'], [2, 'asc']],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
            }
        });
    });
</script>
@stop

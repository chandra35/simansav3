@extends('adminlte::page')

@section('title', 'Preview Export Nilai ' . $semesterLabel)

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-eye"></i> Preview Export Nilai {{ $semesterLabel }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.nilai.index') }}">Nilai Siswa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.nilai.semester', $semester) }}">{{ $semesterLabel }}</a></li>
                <li class="breadcrumb-item active">Preview Export</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    {{-- Info Summary --}}
    <div class="row">
        <div class="col-md-3">
            <div class="info-box bg-info">
                <span class="info-box-icon"><i class="fas fa-calendar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Tahun Pelajaran</span>
                    <span class="info-box-number">{{ $tahunPelajaran->nama }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-success">
                <span class="info-box-icon"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Jumlah Siswa</span>
                    <span class="info-box-number">{{ count($exportData) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-primary">
                <span class="info-box-icon"><i class="fas fa-book"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Jumlah Mapel</span>
                    <span class="info-box-number">{{ count($mapelCodes) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            @if(count($notFoundNisn) > 0)
            <div class="info-box bg-warning">
                <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">NISN Tidak Ditemukan</span>
                    <span class="info-box-number">{{ count($notFoundNisn) }}</span>
                </div>
            </div>
            @else
            <div class="info-box bg-success">
                <span class="info-box-icon"><i class="fas fa-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Status</span>
                    <span class="info-box-number">Semua NISN Valid</span>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Warning Messages --}}
    @if(count($notFoundNisn) > 0)
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i> 
        <strong>{{ count($notFoundNisn) }} NISN tidak ditemukan:</strong>
        {{ implode(', ', array_slice($notFoundNisn, 0, 10)) }}
        @if(count($notFoundNisn) > 10)
            ... dan {{ count($notFoundNisn) - 10 }} lainnya
        @endif
    </div>
    @endif

    {{-- Actions --}}
    <div class="card">
        <div class="card-body">
            <a href="{{ route('admin.nilai.export-semester-download', $semester) }}" class="btn btn-success btn-lg">
                <i class="fas fa-download"></i> Download Excel
            </a>
            <a href="{{ route('admin.nilai.semester', ['semester' => $semester, 'tahun_pelajaran_id' => $tahunPelajaran->id]) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <button type="button" class="btn btn-info float-right" id="btn-copy-nilai">
                <i class="fas fa-copy"></i> Copy Nilai (tanpa header)
            </button>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-table"></i> Data Nilai untuk Export</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm mb-0" id="export-table">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th>NISN</th>
                            <th>Nama</th>
                            @foreach($mapelCodes as $kode)
                                <th class="text-center">{{ $kode }}</th>
                            @endforeach
                            <th class="text-center bg-info">Jml Nilai</th>
                            <th class="text-center bg-info">Jml Mapel</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($exportData as $index => $row)
                        <tr class="{{ !($row['found'] ?? true) ? 'table-danger' : '' }}">
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $row['nisn'] }}</td>
                            <td>{{ $row['nama'] }}</td>
                            @foreach($mapelCodes as $kode)
                                <td class="text-center nilai-cell">{{ $row[$kode] ?? '' }}</td>
                            @endforeach
                            <td class="text-center bg-light"><strong>{{ $row['total_nilai_semester'] > 0 ? (int)$row['total_nilai_semester'] : '-' }}</strong></td>
                            <td class="text-center bg-light"><strong>{{ $row['total_mapel_semester'] ?? 0 }}</strong></td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-warning">
                        <tr>
                            <th colspan="3" class="text-right">Jumlah Nilai:</th>
                            @foreach($mapelCodes as $kode)
                                <th class="text-center">{{ $mapelStats[$kode] ?? 0 }}</th>
                            @endforeach
                            <th class="text-center">-</th>
                            <th class="text-center">-</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Hidden textarea for copy --}}
    <textarea id="copy-data" style="position: absolute; left: -9999px;"></textarea>
@stop

@section('css')
    <style>
        #export-table th, #export-table td {
            vertical-align: middle;
            font-size: 12px;
            padding: 4px 8px;
        }
        #export-table th {
            white-space: nowrap;
        }
        .nilai-cell {
            min-width: 45px;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // Copy nilai only (tanpa header, NISN, Nama) tapi termasuk Jml Nilai & Jml Mapel
            $('#btn-copy-nilai').click(function() {
                var data = [];
                $('#export-table tbody tr').each(function() {
                    var row = [];
                    // Ambil nilai dari kolom nilai (skip No, NISN, Nama - 3 kolom pertama)
                    $(this).find('td').each(function(index) {
                        if (index >= 3) { // Skip kolom No, NISN, Nama
                            row.push($(this).text().trim());
                        }
                    });
                    data.push(row.join('\t'));
                });
                
                var text = data.join('\n');
                
                // Copy to clipboard
                navigator.clipboard.writeText(text).then(function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Data nilai berhasil di-copy ke clipboard (termasuk Jml Nilai & Jml Mapel)',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }).catch(function() {
                    // Fallback
                    $('#copy-data').val(text).select();
                    document.execCommand('copy');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Data nilai berhasil di-copy ke clipboard (termasuk Jml Nilai & Jml Mapel)',
                        timer: 2000,
                        showConfirmButton: false
                    });
                });
            });
        });
    </script>
@stop

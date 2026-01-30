@extends('adminlte::page')

@section('title', 'Export Custom')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-file-export"></i> Export Custom Kelas {{ $tingkat }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.nilai.index') }}">Nilai Legger</a></li>
                <li class="breadcrumb-item active">Export</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    {{-- Alert Messages --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <form action="{{ route('admin.nilai.export-legger') }}" method="GET" id="exportForm">
                <input type="hidden" name="tingkat" value="{{ $tingkat }}">
                
                {{-- Info Siswa --}}
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-users"></i> Data Siswa</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="info-box bg-info">
                                    <span class="info-box-icon"><i class="fas fa-users"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Siswa Kelas {{ $tingkat }}</span>
                                        <span class="info-box-number">{{ $totalSiswa }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box bg-success">
                                    <span class="info-box-icon"><i class="fas fa-school"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Jumlah Kelas</span>
                                        <span class="info-box-number">{{ $kelasList->count() }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box bg-primary">
                                    <span class="info-box-icon"><i class="fas fa-calendar"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Tahun Pelajaran</span>
                                        <span class="info-box-number">{{ $tahunAktif->nama }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Pilih Kelas --}}
                        <div class="form-group">
                            <label><i class="fas fa-school"></i> Filter Kelas (Opsional)</label>
                            <div class="row">
                                @foreach($kelasList as $kelas)
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input kelas-checkbox" type="checkbox" 
                                               name="kelas[]" value="{{ $kelas->id }}" id="kelas_{{ $kelas->id }}">
                                        <label class="form-check-label" for="kelas_{{ $kelas->id }}">
                                            {{ $kelas->nama_kelas }}
                                            <small class="text-muted">({{ $kelas->siswas_count }} siswa)</small>
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <small class="text-muted">Kosongkan untuk export semua kelas {{ $tingkat }}</small>
                        </div>
                    </div>
                </div>

                {{-- Pilih Mapel --}}
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-book"></i> Pilih Mata Pelajaran</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" id="selectAllMapel">
                                <i class="fas fa-check-square"></i> Pilih Semua
                            </button>
                            <button type="button" class="btn btn-tool" id="deselectAllMapel">
                                <i class="fas fa-square"></i> Hapus Semua
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($mapelList as $index => $mapel)
                            <div class="col-md-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input mapel-checkbox" type="checkbox" 
                                           name="mapel[]" value="{{ $mapel->kode_mapel }}" 
                                           id="mapel_{{ $mapel->id }}" checked>
                                    <label class="form-check-label" for="mapel_{{ $mapel->id }}">
                                        <strong>{{ $mapel->kode_mapel }}</strong>
                                        <br><small class="text-muted">{{ $mapel->nama_mapel }}</small>
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Semester Info --}}
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-calendar-alt"></i> Semester yang Akan Di-export</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-bordered mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th width="80">Semester</th>
                                    <th>Keterangan</th>
                                    <th>Tahun Pelajaran</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($semesterConfig as $sem => $config)
                                @php
                                    $tahunPelajaran = null;
                                    $offset = $config['offset'];
                                    $targetTahun = $tahunAktif->tahun_mulai + $offset;
                                    $tahunPelajaran = \App\Models\TahunPelajaran::where('tahun_mulai', $targetTahun)->first();
                                @endphp
                                <tr>
                                    <td class="text-center"><span class="badge badge-primary">S{{ $sem }}</span></td>
                                    <td>{{ $config['label'] }}</td>
                                    <td>
                                        @if($tahunPelajaran)
                                            <span class="text-success"><i class="fas fa-check-circle"></i> {{ $tahunPelajaran->nama }}</span>
                                        @else
                                            <span class="text-danger"><i class="fas fa-times-circle"></i> Tidak ada</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-file-excel"></i> Export ke Excel
                        </button>
                        <a href="{{ route('admin.nilai.index', ['tingkat' => $tingkat]) }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-md-4">
            {{-- Format Preview --}}
            <div class="card card-warning card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-eye"></i> Format Output Excel</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">Format header Excel yang akan dihasilkan:</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm" style="font-size: 10px;">
                            <thead>
                                <tr class="bg-light">
                                    <th rowspan="2">No</th>
                                    <th rowspan="2">NISN</th>
                                    <th rowspan="2">NIS</th>
                                    <th rowspan="2">Nama</th>
                                    <th rowspan="2">L/P</th>
                                    <th rowspan="2">Kelas</th>
                                    <th colspan="6" class="text-center bg-success text-white">MTK</th>
                                    <th colspan="6" class="text-center bg-info text-white">BING</th>
                                    <th>...</th>
                                </tr>
                                <tr class="bg-light">
                                    <th>S1</th>
                                    <th>S2</th>
                                    <th>S3</th>
                                    <th>S4</th>
                                    <th>S5</th>
                                    <th>Avg</th>
                                    <th>S1</th>
                                    <th>S2</th>
                                    <th>S3</th>
                                    <th>S4</th>
                                    <th>S5</th>
                                    <th>Avg</th>
                                    <th>...</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>001...</td>
                                    <td>123</td>
                                    <td>Ahmad</td>
                                    <td>L</td>
                                    <td>XII-A</td>
                                    <td>85</td>
                                    <td>87</td>
                                    <td>86</td>
                                    <td>88</td>
                                    <td>90</td>
                                    <td>87.2</td>
                                    <td>80</td>
                                    <td>82</td>
                                    <td>81</td>
                                    <td>83</td>
                                    <td>85</td>
                                    <td>82.2</td>
                                    <td>...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <hr>
                    
                    <h6><i class="fas fa-info-circle"></i> Keterangan:</h6>
                    <ul class="small mb-0">
                        <li><strong>S1-S5:</strong> Nilai per semester</li>
                        <li><strong>Avg:</strong> Rata-rata nilai per mapel</li>
                        <li><strong>RATA2:</strong> Rata-rata total semua mapel</li>
                        <li>Kolom NISN, NIS, Nama, L/P, Kelas di-freeze</li>
                        <li>Ada sheet Info Legger dengan mapping semester</li>
                    </ul>
                </div>
            </div>

            {{-- Tips --}}
            <div class="card card-secondary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-lightbulb"></i> Tips</h3>
                </div>
                <div class="card-body">
                    <ul class="small mb-0">
                        <li>Pastikan semua semester sudah terisi nilai sebelum export</li>
                        <li>Pilih mapel yang relevan untuk keperluan SPAN-PTKIN/SNBP</li>
                        <li>File Excel dapat langsung digunakan untuk upload ke sistem PDSS</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Select/Deselect all mapel
    $('#selectAllMapel').click(function() {
        $('.mapel-checkbox').prop('checked', true);
    });
    
    $('#deselectAllMapel').click(function() {
        $('.mapel-checkbox').prop('checked', false);
    });
    
    // Validate at least one mapel selected
    $('#exportForm').submit(function(e) {
        if ($('.mapel-checkbox:checked').length === 0) {
            e.preventDefault();
            alert('Pilih minimal satu mata pelajaran!');
            return false;
        }
        return true;
    });
});
</script>
@stop

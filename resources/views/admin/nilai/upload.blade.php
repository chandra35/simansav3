@extends('adminlte::page')

@section('title', 'Upload Nilai Excel')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-file-excel"></i> Upload Nilai dari Excel</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.nilai.index') }}">Nilai Siswa</a></li>
                <li class="breadcrumb-item active">Upload Excel</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            {{-- Upload Form --}}
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-upload"></i> Upload File</h3>
                </div>
                <form action="{{ route('admin.nilai.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="tahun_pelajaran_id">Tahun Pelajaran <span class="text-danger">*</span></label>
                            <select name="tahun_pelajaran_id" id="tahun_pelajaran_id" class="form-control @error('tahun_pelajaran_id') is-invalid @enderror" required>
                                @foreach($tahunPelajarans as $tp)
                                    <option value="{{ $tp->id }}" {{ ($tahunAktif && $tahunAktif->id == $tp->id) ? 'selected' : '' }}>
                                        {{ $tp->nama }} {{ $tp->is_active ? '(Aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tahun_pelajaran_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="semester">Semester <span class="text-danger">*</span></label>
                            <select name="semester" id="semester" class="form-control @error('semester') is-invalid @enderror" required>
                                <option value="">-- Pilih Semester --</option>
                                <option value="1" {{ request('semester') == 1 ? 'selected' : '' }}>Semester 1 (Kelas X - Sem 1)</option>
                                <option value="2" {{ request('semester') == 2 ? 'selected' : '' }}>Semester 2 (Kelas X - Sem 2)</option>
                                <option value="3" {{ request('semester') == 3 ? 'selected' : '' }}>Semester 3 (Kelas XI - Sem 1)</option>
                                <option value="4" {{ request('semester') == 4 ? 'selected' : '' }}>Semester 4 (Kelas XI - Sem 2)</option>
                                <option value="5" {{ request('semester') == 5 ? 'selected' : '' }}>Semester 5 (Kelas XII - Sem 1)</option>
                            </select>
                            @error('semester')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="file">File Excel <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input @error('file') is-invalid @enderror" 
                                           id="file" name="file" accept=".xlsx,.xls" required>
                                    <label class="custom-file-label" for="file">Pilih file...</label>
                                </div>
                            </div>
                            <small class="text-muted">Format: .xlsx atau .xls, Maks: 10MB</small>
                            @error('file')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> <strong>Perhatian:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Jika NISN sudah memiliki nilai untuk mapel dan semester yang sama, nilai akan <strong>di-update</strong>.</li>
                                <li>Pastikan NISN siswa sudah terdaftar di sistem.</li>
                                <li>Kode mapel di header Excel harus sesuai dengan kode mapel di sistem.</li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Upload
                        </button>
                        <a href="{{ route('admin.nilai.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-4">
            {{-- Kode Mapel Reference --}}
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list"></i> Kode Mapel di Sistem</h3>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama Mapel</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mapelList as $mapel)
                            <tr>
                                <td><code>{{ $mapel->kode_mapel }}</code></td>
                                <td>{{ $mapel->nama_mapel }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i> 
                        Kode mapel di header Excel harus sama persis (case-insensitive).
                    </small>
                </div>
            </div>

            {{-- Format Example --}}
            <div class="card card-secondary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-file-alt"></i> Contoh Format Excel</h3>
                </div>
                <div class="card-body">
                    <p>Format Excel dari RDM/Legger:</p>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered" style="font-size: 10px;">
                            <thead class="bg-light">
                                <tr>
                                    <th>No</th>
                                    <th>NIS</th>
                                    <th>Nisn</th>
                                    <th>Nama</th>
                                    <th>JK</th>
                                    <th>QH</th>
                                    <th>AA</th>
                                    <th>...</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>1234</td>
                                    <td>0012345678</td>
                                    <td>Ahmad</td>
                                    <td>L</td>
                                    <td>85</td>
                                    <td>87</td>
                                    <td>...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="text-muted small mb-0">
                        <i class="fas fa-check text-success"></i> Acuan matching: kolom <strong>Nisn</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>
    <script>
        $(document).ready(function() {
            bsCustomFileInput.init();
        });
    </script>
@stop

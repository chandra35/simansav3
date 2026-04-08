@extends('adminlte::page')

@section('title', 'Upload Nilai Legger')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-file-excel"></i> Upload Nilai Legger</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.nilai.index') }}">Nilai Siswa</a></li>
                <li class="breadcrumb-item active">Upload Legger</li>
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

    <section class="simansa-upload-hero">
        <div class="simansa-upload-hero__eyebrow">
            <i class="fas fa-file-upload"></i> Impor Nilai Legger
        </div>
        <h2>Upload Nilai</h2>
        <p>Pilih tingkat, tentukan semester, biarkan tahun pelajaran terhitung otomatis, lalu unggah file Excel untuk dipreview sebelum disimpan ke database.</p>
    </section>

    <div class="row">
        <div class="col-md-8">
            {{-- Upload Form --}}
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-upload"></i> Upload Nilai Legger</h3>
                </div>
                <form action="{{ route('admin.nilai.upload') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                    @csrf
                    <input type="hidden" name="tahun_pelajaran_id" id="tahun_pelajaran_id">
                    
                    <div class="card-body">
                        {{-- Step 1: Pilih Tingkat Kelas --}}
                        <div class="form-group">
                            <label for="tingkat_kelas">1. Pilih Tingkat Kelas <span class="text-danger">*</span></label>
                            <select name="tingkat_kelas" id="tingkat_kelas" class="form-control form-control-lg" required>
                                <option value="">-- Pilih Tingkat Kelas --</option>
                                <option value="12">Kelas 12 (Legger untuk SPAN-PTKIN/SNBP/UTBK)</option>
                                <option value="11">Kelas 11</option>
                                <option value="10">Kelas 10</option>
                            </select>
                            <small class="text-muted">Pilih tingkat kelas siswa saat ini</small>
                        </div>

                        {{-- Step 2: Pilih Semester --}}
                        <div class="form-group" id="semesterGroup" style="display: none;">
                            <label for="semester">2. Pilih Semester Legger <span class="text-danger">*</span></label>
                            <select name="semester" id="semester" class="form-control form-control-lg" required disabled>
                                <option value="">-- Pilih Semester --</option>
                            </select>
                            <small class="text-muted">Semester berdasarkan tingkat kelas yang dipilih</small>
                        </div>

                        {{-- Step 3: Info Tahun Pelajaran (Auto) --}}
                        <div class="form-group" id="tahunInfo" style="display: none;">
                            <label>3. Tahun Pelajaran (Otomatis)</label>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-calendar"></i> 
                                <strong id="tahunPelajaranLabel">-</strong>
                                <span id="tahunNotFound" class="text-danger" style="display: none;">
                                    <br><i class="fas fa-exclamation-triangle"></i> Tahun pelajaran tidak ditemukan! Silakan buat di menu <a href="{{ route('admin.tahun-pelajaran.index') }}">Tahun Pelajaran</a>
                                </span>
                            </div>
                        </div>

                        {{-- Step 4: Upload File --}}
                        <div class="form-group" id="fileGroup" style="display: none;">
                            <label for="file">4. File Excel <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input @error('file') is-invalid @enderror" 
                                           id="file" name="file" accept=".xlsx,.xls" required disabled>
                                    <label class="custom-file-label" for="file">Pilih file...</label>
                                </div>
                            </div>
                            <small class="text-muted">Format: .xlsx atau .xls, Maks: 10MB</small>
                            @error('file')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-info-circle"></i> <strong>Petunjuk:</strong>
                            <ol class="mb-0 mt-2">
                                <li>Pilih <strong>Tingkat Kelas</strong> siswa saat ini (misal: Kelas 12)</li>
                                <li>Pilih <strong>Semester Legger</strong> yang akan diupload (1-5)</li>
                                <li>Tahun Pelajaran akan <strong>otomatis dihitung</strong></li>
                                <li>Download template dan isi data nilai</li>
                                <li>Upload file yang sudah diisi</li>
                            </ol>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-lg" id="btnSubmit" disabled>
                            <i class="fas fa-upload"></i> Upload & Preview
                        </button>
                        <div class="btn-group">
                            <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-download"></i> Download Template
                            </button>
                            <div class="dropdown-menu">
                                <h6 class="dropdown-header">Semester 1-2 (18 Mapel)</h6>
                                <a class="dropdown-item" href="{{ route('admin.nilai.template', ['semester' => 1]) }}">
                                    <i class="fas fa-file-excel text-success"></i> Template Semester 1-2
                                </a>
                                <div class="dropdown-divider"></div>
                                <h6 class="dropdown-header">Semester 3 (18 Mapel)</h6>
                                <a class="dropdown-item" href="{{ route('admin.nilai.template', ['semester' => 3]) }}">
                                    <i class="fas fa-file-excel text-primary"></i> Template Semester 3
                                </a>
                                <div class="dropdown-divider"></div>
                                <h6 class="dropdown-header">Semester 4 (20 Mapel)</h6>
                                <a class="dropdown-item" href="{{ route('admin.nilai.template', ['semester' => 4]) }}">
                                    <i class="fas fa-file-excel text-info"></i> Template Semester 4
                                </a>
                                <div class="dropdown-divider"></div>
                                <h6 class="dropdown-header">Semester 5 (20 Mapel)</h6>
                                <a class="dropdown-item" href="{{ route('admin.nilai.template', ['semester' => 5]) }}">
                                    <i class="fas fa-file-excel text-warning"></i> Template Semester 5
                                </a>
                            </div>
                        </div>
                        <a href="{{ route('admin.nilai.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>

            {{-- Panduan Semester --}}
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-question-circle"></i> Panduan Semester Legger</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Semester Legger</th>
                                <th>Tingkat</th>
                                <th>Contoh untuk Kelas 12 (TA {{ $tahunAktif->nama ?? '2025/2026' }})</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge badge-primary">Semester 1</span></td>
                                <td>Kelas X - Semester 1</td>
                                <td>Tahun Pelajaran 2023/2024</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-primary">Semester 2</span></td>
                                <td>Kelas X - Semester 2</td>
                                <td>Tahun Pelajaran 2023/2024</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-success">Semester 3</span></td>
                                <td>Kelas XI - Semester 1</td>
                                <td>Tahun Pelajaran 2024/2025</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-success">Semester 4</span></td>
                                <td>Kelas XI - Semester 2</td>
                                <td>Tahun Pelajaran 2024/2025</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-warning">Semester 5</span></td>
                                <td>Kelas XII - Semester 1</td>
                                <td>Tahun Pelajaran 2025/2026 (Aktif)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            {{-- Urutan Mapel --}}
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-sort-numeric-down"></i> Urutan Kolom Mapel</h3>
                </div>
                <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="bg-light sticky-top">
                            <tr>
                                <th width="40">Kol</th>
                                <th>Kode</th>
                                <th>Nama Mapel</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="text-muted">
                                <td>A</td>
                                <td>No</td>
                                <td><small>Nomor urut</small></td>
                            </tr>
                            <tr class="text-muted">
                                <td>B</td>
                                <td>NIS</td>
                                <td><small>Nomor Induk Siswa</small></td>
                            </tr>
                            <tr class="table-primary">
                                <td><strong>C</strong></td>
                                <td><strong>NISN</strong></td>
                                <td><small><strong>Kunci matching</strong></small></td>
                            </tr>
                            <tr class="text-muted">
                                <td>D</td>
                                <td>Nama</td>
                                <td><small>Nama siswa</small></td>
                            </tr>
                            <tr class="text-muted">
                                <td>E</td>
                                <td>JK</td>
                                <td><small>Jenis Kelamin</small></td>
                            </tr>
                            @php $col = 'F'; @endphp
                            @foreach($mapelList as $mapel)
                            <tr>
                                <td><code>{{ $col }}</code></td>
                                <td><code>{{ $mapel->kode_mapel }}</code></td>
                                <td><small>{{ $mapel->nama_mapel }}</small></td>
                            </tr>
                            @php $col++; @endphp
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer text-center">
                    <div class="btn-group-vertical w-100">
                        <a href="{{ route('admin.nilai.template', ['semester' => 1]) }}" class="btn btn-success">
                            <i class="fas fa-download"></i> Template Semester 1-2 (18 Mapel)
                        </a>
                        <a href="{{ route('admin.nilai.template', ['semester' => 3]) }}" class="btn btn-primary">
                            <i class="fas fa-download"></i> Template Semester 3 (18 Mapel)
                        </a>
                        <a href="{{ route('admin.nilai.template', ['semester' => 4]) }}" class="btn btn-info">
                            <i class="fas fa-download"></i> Template Semester 4 (20 Mapel)
                        </a>
                        <a href="{{ route('admin.nilai.template', ['semester' => 5]) }}" class="btn btn-warning">
                            <i class="fas fa-download"></i> Template Semester 5 (20 Mapel)
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .simansa-upload-hero{margin-bottom:1.5rem;padding:1.35rem 1.5rem;border-radius:22px;background:linear-gradient(135deg,#2147cf 0%,#2f8d9c 100%);color:#fff;box-shadow:0 18px 40px rgba(33,71,207,.16)}
        .simansa-upload-hero__eyebrow{display:inline-flex;align-items:center;gap:.45rem;font-size:.78rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.82);margin-bottom:.75rem}
        .simansa-upload-hero h2{margin:0 0 .35rem;font-size:1.75rem;font-weight:700}
        .simansa-upload-hero p{margin:0;max-width:840px;color:rgba(255,255,255,.92)}
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>
    <script>
        $(document).ready(function() {
            bsCustomFileInput.init();
            
            // Data tahun pelajaran dari server
            const tahunPelajarans = @json($tahunPelajarans);
            const tahunAktif = @json($tahunAktif);
            
            // Semester mapping untuk setiap tingkat kelas
            const semesterOptions = {
                12: [
                    { value: 1, label: 'Semester 1 - Kelas X Sem 1', offset: -2 },
                    { value: 2, label: 'Semester 2 - Kelas X Sem 2', offset: -2 },
                    { value: 3, label: 'Semester 3 - Kelas XI Sem 1', offset: -1 },
                    { value: 4, label: 'Semester 4 - Kelas XI Sem 2', offset: -1 },
                    { value: 5, label: 'Semester 5 - Kelas XII Sem 1', offset: 0 }
                ],
                11: [
                    { value: 1, label: 'Semester 1 - Kelas X Sem 1', offset: -1 },
                    { value: 2, label: 'Semester 2 - Kelas X Sem 2', offset: -1 },
                    { value: 3, label: 'Semester 3 - Kelas XI Sem 1', offset: 0 },
                    { value: 4, label: 'Semester 4 - Kelas XI Sem 2', offset: 0 }
                ],
                10: [
                    { value: 1, label: 'Semester 1 - Kelas X Sem 1', offset: 0 },
                    { value: 2, label: 'Semester 2 - Kelas X Sem 2', offset: 0 }
                ]
            };
            
            // Event: Tingkat kelas berubah
            $('#tingkat_kelas').change(function() {
                const tingkat = $(this).val();
                const $semester = $('#semester');
                const $semesterGroup = $('#semesterGroup');
                
                if (!tingkat) {
                    $semesterGroup.hide();
                    $('#tahunInfo').hide();
                    $('#fileGroup').hide();
                    $semester.prop('disabled', true);
                    return;
                }
                
                // Populate semester options
                $semester.empty().append('<option value="">-- Pilih Semester --</option>');
                semesterOptions[tingkat].forEach(function(opt) {
                    $semester.append(`<option value="${opt.value}" data-offset="${opt.offset}">${opt.label}</option>`);
                });
                
                $semester.prop('disabled', false);
                $semesterGroup.show();
                $('#tahunInfo').hide();
                $('#fileGroup').hide();
                updateSubmitButton();
            });
            
            // Event: Semester berubah
            $('#semester').change(function() {
                const semester = $(this).val();
                const tingkat = $('#tingkat_kelas').val();
                const $tahunInfo = $('#tahunInfo');
                const $fileGroup = $('#fileGroup');
                
                if (!semester) {
                    $tahunInfo.hide();
                    $fileGroup.hide();
                    updateSubmitButton();
                    return;
                }
                
                // Hitung tahun pelajaran berdasarkan offset
                const offset = $(this).find(':selected').data('offset');
                const tahunAktifMulai = tahunAktif ? tahunAktif.tahun_mulai : new Date().getFullYear();
                const tahunTarget = tahunAktifMulai + offset;
                
                // Cari tahun pelajaran yang sesuai
                let tahunFound = null;
                tahunPelajarans.forEach(function(tp) {
                    if (tp.tahun_mulai == tahunTarget) {
                        tahunFound = tp;
                    }
                });
                
                if (tahunFound) {
                    $('#tahunPelajaranLabel').html(
                        `<strong>${tahunFound.nama}</strong>` + 
                        (tahunFound.is_active ? ' <span class="badge badge-success">Aktif</span>' : '')
                    );
                    $('#tahun_pelajaran_id').val(tahunFound.id);
                    $('#tahunNotFound').hide();
                    $('#file').prop('disabled', false);
                } else {
                    const tahunNama = `${tahunTarget}/${tahunTarget + 1}`;
                    $('#tahunPelajaranLabel').html(`<span class="text-danger">${tahunNama}</span>`);
                    $('#tahun_pelajaran_id').val('');
                    $('#tahunNotFound').show();
                    $('#file').prop('disabled', true);
                }
                
                $tahunInfo.show();
                $fileGroup.show();
                updateSubmitButton();
            });
            
            // Update submit button state
            function updateSubmitButton() {
                const tingkat = $('#tingkat_kelas').val();
                const semester = $('#semester').val();
                const tahunId = $('#tahun_pelajaran_id').val();
                
                const canSubmit = tingkat && semester && tahunId;
                $('#btnSubmit').prop('disabled', !canSubmit);
            }
            
            // File change event
            $('#file').change(function() {
                updateSubmitButton();
            });
        });
    </script>
@stop

@extends('adminlte::page')

@section('title', 'Dokumen Siswa - SIMANSA')

@section('content_header')
    <h1><i class="fas fa-folder-open"></i> Dokumen Siswa</h1>
@stop

@section('content')
<!-- Info Progress -->
<div class="row">
    <div class="col-12">
        <div class="callout callout-warning student-doc-hero">
            <h5><i class="fas fa-folder-open"></i> Dokumen Siswa</h5>
            <p class="mb-0">
                Upload dokumen yang diperlukan untuk kelengkapan administrasi. Dokumen <strong>Kartu Keluarga</strong> dan <strong>Ijazah SMP</strong> adalah wajib, 
                sedangkan <strong>KIP</strong> dan <strong>SKTM</strong> bersifat opsional (jika memiliki). 
                Pastikan dokumen yang diupload <strong>jelas dan dapat dibaca</strong>.
            </p>
        </div>
    </div>
</div>

<!-- Info Box Summary -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $dokumen->where('jenis_dokumen', 'kk')->count() }}</h3>
                <p>Kartu Keluarga</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $dokumen->where('jenis_dokumen', 'ijazah_smp')->count() }}</h3>
                <p>Ijazah SMP</p>
            </div>
            <div class="icon">
                <i class="fas fa-graduation-cap"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $dokumen->where('jenis_dokumen', 'kip')->count() }}</h3>
                <p>KIP</p>
            </div>
            <div class="icon">
                <i class="fas fa-id-card"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $dokumen->where('jenis_dokumen', 'sktm')->count() }}</h3>
                <p>SKTM</p>
            </div>
            <div class="icon">
                <i class="fas fa-file-alt"></i>
            </div>
        </div>
    </div>
</div>

<!-- Petunjuk -->
<div class="row">
    <div class="col-12">
        <div class="callout callout-info">
            <h5><i class="fas fa-info-circle"></i> Petunjuk Upload Dokumen</h5>
            <ul class="mb-0">
                <li>Format file yang diperbolehkan: <strong>PDF, JPG, JPEG, PNG</strong></li>
                <li>Ukuran maksimal file: <strong>5 MB</strong> (gambar akan di-compress otomatis)</li>
                <li>Pastikan dokumen <strong>jelas dan dapat dibaca</strong></li>
                <li>Upload ulang akan <strong>mengganti dokumen lama</strong></li>
                <li>Dokumen <strong>KK dan Ijazah SMP</strong> adalah wajib, sedangkan <strong>KIP dan SKTM</strong> opsional</li>
                <li><span class="badge badge-success"><i class="fas fa-compress"></i> Auto-Compress</span> File gambar besar akan dikompres otomatis tanpa mengurangi kualitas visual</li>
            </ul>
        </div>
    </div>
</div>

<!-- Dokumen Cards -->
<div class="row">
    <!-- Kartu Keluarga -->
    <div class="col-lg-6">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-users"></i> Kartu Keluarga (KK)
                    <span class="badge badge-danger ml-2">Wajib</span>
                </h3>
            </div>
            <div class="card-body">
                @php
                    $kk = $dokumen->where('jenis_dokumen', 'kk')->first();
                @endphp
                
                @if($kk)
                    <div class="d-flex align-items-start mb-3">
                        <div class="mr-3">
                            <i class="fas fa-file-pdf fa-3x text-danger"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-1">{{ $kk->nama_file }}</h5>
                            <p class="mb-1 text-muted">
                                <small>
                                    <i class="far fa-file"></i> {{ $kk->getFileSizeFormatted() }} |
                                    <i class="far fa-clock"></i> {{ $kk->created_at->format('d/m/Y H:i') }}
                                </small>
                            </p>
                            @if($kk->keterangan)
                            <p class="mb-1"><small><strong>Ket:</strong> {{ $kk->keterangan }}</small></p>
                            @endif
                        </div>
                        <div>
                            <span class="badge badge-success"><i class="fas fa-check"></i> Uploaded</span>
                        </div>
                    </div>
                    <div class="btn-group btn-block">
                        <button type="button" class="btn btn-info btn-sm" onclick="previewDokumen('{{ $kk->getFileUrl() }}', 'Kartu Keluarga (KK)', '{{ $kk->getFileExtension() }}')">
                            <i class="fas fa-eye"></i> Lihat
                        </button>
                        <button type="button" class="btn btn-warning btn-sm" onclick="showUploadModal('kk', 'Kartu Keluarga (KK)')">
                            <i class="fas fa-sync"></i> Ganti
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteDokumen('{{ $kk->id }}', 'KK')">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-3">Belum ada dokumen yang diupload</p>
                        <button type="button" class="btn btn-primary" onclick="showUploadModal('kk', 'Kartu Keluarga (KK)')">
                            <i class="fas fa-upload"></i> Upload Dokumen
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Ijazah SMP -->
    <div class="col-lg-6">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-graduation-cap"></i> Ijazah SMP
                    <span class="badge badge-danger ml-2">Wajib</span>
                </h3>
            </div>
            <div class="card-body">
                @php
                    $ijazah = $dokumen->where('jenis_dokumen', 'ijazah_smp')->first();
                @endphp
                
                @if($ijazah)
                    <div class="d-flex align-items-start mb-3">
                        <div class="mr-3">
                            <i class="fas fa-file-pdf fa-3x text-danger"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-1">{{ $ijazah->nama_file }}</h5>
                            <p class="mb-1 text-muted">
                                <small>
                                    <i class="far fa-file"></i> {{ $ijazah->getFileSizeFormatted() }} |
                                    <i class="far fa-clock"></i> {{ $ijazah->created_at->format('d/m/Y H:i') }}
                                </small>
                            </p>
                            @if($ijazah->keterangan)
                            <p class="mb-1"><small><strong>Ket:</strong> {{ $ijazah->keterangan }}</small></p>
                            @endif
                        </div>
                        <div>
                            <span class="badge badge-success"><i class="fas fa-check"></i> Uploaded</span>
                        </div>
                    </div>
                    <div class="btn-group btn-block">
                        <button type="button" class="btn btn-info btn-sm" onclick="previewDokumen('{{ $ijazah->getFileUrl() }}', 'Ijazah SMP', '{{ $ijazah->getFileExtension() }}')">
                            <i class="fas fa-eye"></i> Lihat
                        </button>
                        <button type="button" class="btn btn-warning btn-sm" onclick="showUploadModal('ijazah_smp', 'Ijazah SMP')">
                            <i class="fas fa-sync"></i> Ganti
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteDokumen('{{ $ijazah->id }}', 'Ijazah SMP')">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-3">Belum ada dokumen yang diupload</p>
                        <button type="button" class="btn btn-success" onclick="showUploadModal('ijazah_smp', 'Ijazah SMP')">
                            <i class="fas fa-upload"></i> Upload Dokumen
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- KIP -->
    <div class="col-lg-6">
        <div class="card card-outline card-warning">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-id-card"></i> Kartu Indonesia Pintar (KIP)
                    <span class="badge badge-secondary ml-2">Opsional</span>
                </h3>
            </div>
            <div class="card-body">
                @php
                    $kip = $dokumen->where('jenis_dokumen', 'kip')->first();
                @endphp
                
                @if($kip)
                    <div class="d-flex align-items-start mb-3">
                        <div class="mr-3">
                            <i class="fas fa-file-pdf fa-3x text-danger"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-1">{{ $kip->nama_file }}</h5>
                            <p class="mb-1 text-muted">
                                <small>
                                    <i class="far fa-file"></i> {{ $kip->getFileSizeFormatted() }} |
                                    <i class="far fa-clock"></i> {{ $kip->created_at->format('d/m/Y H:i') }}
                                </small>
                            </p>
                            @if($kip->keterangan)
                            <p class="mb-1"><small><strong>Ket:</strong> {{ $kip->keterangan }}</small></p>
                            @endif
                        </div>
                        <div>
                            <span class="badge badge-success"><i class="fas fa-check"></i> Uploaded</span>
                        </div>
                    </div>
                    <div class="btn-group btn-block">
                        <button type="button" class="btn btn-info btn-sm" onclick="previewDokumen('{{ $kip->getFileUrl() }}', 'Kartu Indonesia Pintar (KIP)', '{{ $kip->getFileExtension() }}')">
                            <i class="fas fa-eye"></i> Lihat
                        </button>
                        <button type="button" class="btn btn-warning btn-sm" onclick="showUploadModal('kip', 'Kartu Indonesia Pintar (KIP)')">
                            <i class="fas fa-sync"></i> Ganti
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteDokumen('{{ $kip->id }}', 'KIP')">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-3">Dokumen opsional (jika memiliki)</p>
                        <button type="button" class="btn btn-warning" onclick="showUploadModal('kip', 'Kartu Indonesia Pintar (KIP)')">
                            <i class="fas fa-upload"></i> Upload Dokumen
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- SKTM -->
    <div class="col-lg-6">
        <div class="card card-outline card-danger">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-file-alt"></i> Surat Keterangan Tidak Mampu (SKTM)
                    <span class="badge badge-secondary ml-2">Opsional</span>
                </h3>
            </div>
            <div class="card-body">
                @php
                    $sktm = $dokumen->where('jenis_dokumen', 'sktm')->first();
                @endphp
                
                @if($sktm)
                    <div class="d-flex align-items-start mb-3">
                        <div class="mr-3">
                            <i class="fas fa-file-pdf fa-3x text-danger"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-1">{{ $sktm->nama_file }}</h5>
                            <p class="mb-1 text-muted">
                                <small>
                                    <i class="far fa-file"></i> {{ $sktm->getFileSizeFormatted() }} |
                                    <i class="far fa-clock"></i> {{ $sktm->created_at->format('d/m/Y H:i') }}
                                </small>
                            </p>
                            @if($sktm->keterangan)
                            <p class="mb-1"><small><strong>Ket:</strong> {{ $sktm->keterangan }}</small></p>
                            @endif
                        </div>
                        <div>
                            <span class="badge badge-success"><i class="fas fa-check"></i> Uploaded</span>
                        </div>
                    </div>
                    <div class="btn-group btn-block">
                        <button type="button" class="btn btn-info btn-sm" onclick="previewDokumen('{{ $sktm->getFileUrl() }}', 'Surat Keterangan Tidak Mampu (SKTM)', '{{ $sktm->getFileExtension() }}')">
                            <i class="fas fa-eye"></i> Lihat
                        </button>
                        <button type="button" class="btn btn-warning btn-sm" onclick="showUploadModal('sktm', 'Surat Keterangan Tidak Mampu (SKTM)')">
                            <i class="fas fa-sync"></i> Ganti
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteDokumen('{{ $sktm->id }}', 'SKTM')">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-3">Dokumen opsional (jika memiliki)</p>
                        <button type="button" class="btn btn-outline-danger" onclick="showUploadModal('sktm', 'Surat Keterangan Tidak Mampu (SKTM)')">
                            <i class="fas fa-upload"></i> Upload Dokumen
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- File Lainnya Section -->
<div class="row">
    <div class="col-12">
        <div class="card card-outline card-secondary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-paperclip"></i> File Lainnya
                    <span class="badge badge-secondary ml-2">Opsional</span>
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-success" onclick="showUploadLainnyaModal()">
                        <i class="fas fa-plus"></i> Tambah File Lainnya
                    </button>
                </div>
            </div>
            <div class="card-body">
                @php
                    $fileLainnya = $dokumen->where('jenis_dokumen', 'lainnya');
                @endphp
                
                @if($fileLainnya->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="40%">Nama File</th>
                                    <th width="20%">Ukuran</th>
                                    <th width="20%">Tanggal Upload</th>
                                    <th width="15%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fileLainnya as $index => $file)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <i class="fas fa-file-pdf text-danger mr-2"></i>
                                        {{ $file->nama_file }}
                                        @if($file->keterangan)
                                        <br><small class="text-muted">{{ $file->keterangan }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $file->getFileSizeFormatted() }}</td>
                                    <td>{{ $file->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-info" onclick="previewDokumen('{{ $file->getFileUrl() }}', '{{ $file->nama_file }}', '{{ $file->getFileExtension() }}')" title="Lihat">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger" onclick="deleteDokumen('{{ $file->id }}', 'File Lainnya')" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-paperclip fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-3">Belum ada file lainnya yang diupload</p>
                        <button type="button" class="btn btn-secondary" onclick="showUploadLainnyaModal()">
                            <i class="fas fa-plus"></i> Tambah File Lainnya
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Upload -->
<div class="modal fade" id="uploadModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="uploadForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="uploadModalLabel">Upload Dokumen</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="jenis_dokumen" id="jenis_dokumen">
                    
                    <div class="form-group">
                        <label>File Dokumen <span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="file" name="file" accept=".pdf,.jpg,.jpeg,.png" required>
                            <label class="custom-file-label" for="file">Pilih file...</label>
                        </div>
                        <small class="form-text text-muted">
                            Format: PDF, JPG, JPEG, PNG (Max: 5MB - gambar akan di-compress otomatis)
                        </small>
                    </div>

                    <div class="form-group">
                        <label>Keterangan (Opsional)</label>
                        <textarea class="form-control" name="keterangan" rows="3" placeholder="Tambahkan keterangan jika diperlukan"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Upload File Lainnya -->
<div class="modal fade" id="uploadLainnyaModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="uploadLainnyaForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-plus"></i> Tambah File Lainnya
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="jenis_dokumen" value="lainnya">
                    
                    <div class="form-group">
                        <label>Nama Dokumen <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_dokumen" id="nama_dokumen" 
                               placeholder="Misal: Akta Kelahiran, Sertifikat, dll" required>
                        <small class="form-text text-muted">
                            Masukkan nama/jenis dokumen yang akan diupload
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label>File Dokumen <span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="file_lainnya" name="file" 
                                   accept=".pdf,.jpg,.jpeg,.png" required>
                            <label class="custom-file-label" for="file_lainnya">Pilih file...</label>
                        </div>
                        <small class="form-text text-muted">
                            Format: PDF, JPG, JPEG, PNG (Max: 5MB - gambar akan di-compress otomatis)
                        </small>
                    </div>

                    <div class="form-group">
                        <label>Keterangan (Opsional)</label>
                        <textarea class="form-control" name="keterangan" rows="3" 
                                  placeholder="Tambahkan keterangan jika diperlukan"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-upload"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Preview Dokumen -->
<div class="modal fade" id="previewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title text-white" id="previewModalTitle">
                    <i class="fas fa-eye"></i> Preview Dokumen
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body p-0" style="min-height: 500px;">
                <!-- Preview container for images -->
                <div id="previewImage" class="text-center p-3" style="display: none;">
                    <img src="" alt="Preview" class="img-fluid" style="max-height: 70vh; cursor: zoom-in;" onclick="openFullscreen(this)">
                </div>
                <!-- Preview container for PDF -->
                <div id="previewPdf" style="display: none;">
                    <iframe src="" frameborder="0" style="width: 100%; height: 70vh;"></iframe>
                </div>
                <!-- Loading indicator -->
                <div id="previewLoading" class="text-center py-5" style="display: none;">
                    <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
                    <p class="mt-3">Memuat dokumen...</p>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" id="btnDownloadDoc" class="btn btn-success" download>
                    <i class="fas fa-download"></i> Download
                </a>
                <a href="#" id="btnOpenNewTab" class="btn btn-primary" target="_blank">
                    <i class="fas fa-external-link-alt"></i> Buka di Tab Baru
                </a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Fullscreen Image Modal -->
<div class="modal fade" id="fullscreenModal" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.95);">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document" style="max-width: 95vw;">
        <div class="modal-content bg-transparent border-0">
            <button type="button" class="close text-white position-absolute" style="top: 10px; right: 20px; z-index: 1060; font-size: 2rem;" data-dismiss="modal">
                <span>&times;</span>
            </button>
            <div class="modal-body text-center p-0">
                <img src="" alt="Fullscreen" id="fullscreenImage" style="max-width: 100%; max-height: 95vh; cursor: zoom-out;">
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css">
    <style>
        .student-doc-hero {
            border-left: 0 !important;
            border-radius: 16px;
            background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.07);
            padding: 1.15rem 1.25rem;
        }

        .student-doc-hero h5 {
            color: #0f172a;
            font-weight: 800;
            margin-bottom: 0.55rem;
        }

        .student-doc-hero p,
        .callout ul,
        .callout li {
            color: #64748b;
        }

        .small-box {
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
            border: 1px solid rgba(255,255,255,0.12);
        }

        .small-box .inner {
            padding: 1rem 1rem 1.05rem;
        }

        .card {
            border-radius: 14px;
            border: 1px solid #dbe7f4;
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.07);
            overflow: hidden;
            background: #ffffff;
        }

        .card-header {
            background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
            border-bottom: 1px solid #dbe7f4;
            color: #0f172a;
            padding: 0.95rem 1.1rem;
        }

        .card-outline {
            border-top-width: 0;
        }

        .btn-group.btn-block {
            display: flex;
            gap: 0.45rem;
        }

        .btn-group.btn-block > .btn,
        .btn {
            border-radius: 10px;
            font-weight: 600;
        }

        .btn-group.btn-block > .btn {
            flex: 1 1 0;
        }

        .table thead th {
            border-top: 0;
            color: #475569;
            font-size: .84rem;
            text-transform: uppercase;
            letter-spacing: .02em;
        }

        .modal-content {
            border-radius: 14px;
            overflow: hidden;
        }

        #previewModal .modal-body { background: #f4f6f9; }
        #previewImage img { 
            border-radius: 8px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: transform 0.3s ease;
        }
        #previewImage img:hover { transform: scale(1.02); }
        #fullscreenModal { z-index: 1060; }
        #fullscreenModal .modal-content { box-shadow: none; }
    </style>
@stop

@section('js')
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>

<script>
// Toastr configuration
toastr.options = {
    "closeButton": true,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "timeOut": "3000"
};

// Preview dokumen in modal
function previewDokumen(url, title, extension) {
    // Reset preview
    $('#previewImage').hide();
    $('#previewPdf').hide();
    $('#previewLoading').show();
    
    // Set title and URLs
    $('#previewModalTitle').html('<i class="fas fa-eye"></i> ' + title);
    $('#btnDownloadDoc').attr('href', url);
    $('#btnOpenNewTab').attr('href', url);
    
    // Show modal
    $('#previewModal').modal('show');
    
    // Determine file type and show appropriate preview
    var ext = extension.toLowerCase();
    
    if (ext === 'pdf') {
        // Show PDF in iframe
        $('#previewPdf iframe').attr('src', url);
        setTimeout(function() {
            $('#previewLoading').hide();
            $('#previewPdf').show();
        }, 500);
    } else if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
        // Show image
        var img = new Image();
        img.onload = function() {
            $('#previewImage img').attr('src', url);
            $('#previewLoading').hide();
            $('#previewImage').show();
        };
        img.onerror = function() {
            $('#previewLoading').hide();
            toastr.error('Gagal memuat gambar');
        };
        img.src = url;
    } else {
        // Unknown format - open in new tab
        $('#previewModal').modal('hide');
        window.open(url, '_blank');
    }
}

// Open image in fullscreen
function openFullscreen(imgElement) {
    $('#fullscreenImage').attr('src', imgElement.src);
    $('#previewModal').modal('hide');
    $('#fullscreenModal').modal('show');
}

// Reset preview when modal is closed
$('#previewModal').on('hidden.bs.modal', function() {
    $('#previewImage img').attr('src', '');
    $('#previewPdf iframe').attr('src', '');
    $('#previewImage').hide();
    $('#previewPdf').hide();
});

// Show upload modal
function showUploadModal(jenisDokumen, label) {
    $('#jenis_dokumen').val(jenisDokumen);
    $('#uploadModalLabel').text('Upload ' + label);
    $('#uploadModal').modal('show');
}

// Show upload lainnya modal
function showUploadLainnyaModal() {
    $('#uploadLainnyaModal').modal('show');
}

// Handle file input change
$('#file').on('change', function() {
    var fileName = $(this).val().split('\\').pop();
    $(this).siblings('.custom-file-label').addClass('selected').html(fileName);
});

$('#file_lainnya').on('change', function() {
    var fileName = $(this).val().split('\\').pop();
    $(this).siblings('.custom-file-label').addClass('selected').html(fileName);
});

// Handle form submit
$('#uploadForm').on('submit', function(e) {
    e.preventDefault();
    
    var formData = new FormData(this);
    
    // Show loading
    Swal.fire({
        title: 'Mengupload...',
        html: 'Mohon tunggu sebentar',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: '{{ route('siswa.dokumen.upload') }}',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            Swal.close();
            if (response.success) {
                $('#uploadModal').modal('hide');
                toastr.success(response.message);
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                Swal.fire('Gagal!', response.message, 'error');
            }
        },
        error: function(xhr) {
            Swal.close();
            if (xhr.status === 422) {
                var errors = xhr.responseJSON.errors;
                var errorMessage = '';
                $.each(errors, function(key, value) {
                    errorMessage += value[0] + '<br>';
                });
                Swal.fire('Validasi Gagal!', errorMessage, 'error');
            } else {
                Swal.fire('Error!', 'Terjadi kesalahan saat mengupload dokumen', 'error');
            }
        }
    });
});

// Delete dokumen
function deleteDokumen(id, jenis) {
    Swal.fire({
        title: 'Hapus Dokumen?',
        text: 'Dokumen ' + jenis + ' akan dihapus permanen!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus!',
        cancelButtonText: '<i class="fas fa-times"></i> Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ url('siswa/dokumen') }}/' + id,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        Swal.fire('Gagal!', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Terjadi kesalahan saat menghapus dokumen', 'error');
                }
            });
        }
    });
}

// Handle upload lainnya form submit
$('#uploadLainnyaForm').on('submit', function(e) {
    e.preventDefault();
    
    var formData = new FormData(this);
    
    // Show loading
    Swal.fire({
        title: 'Mengupload...',
        html: 'Mohon tunggu sebentar',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: '{{ route('siswa.dokumen.upload') }}',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            Swal.close();
            if (response.success) {
                $('#uploadLainnyaModal').modal('hide');
                toastr.success(response.message);
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                Swal.fire('Gagal!', response.message, 'error');
            }
        },
        error: function(xhr) {
            Swal.close();
            if (xhr.status === 422) {
                var errors = xhr.responseJSON.errors;
                var errorMessage = '';
                $.each(errors, function(key, value) {
                    errorMessage += value[0] + '<br>';
                });
                Swal.fire('Validasi Gagal!', errorMessage, 'error');
            } else {
                Swal.fire('Error!', 'Terjadi kesalahan saat mengupload dokumen', 'error');
            }
        }
    });
});

// Reset form when modal is closed
$('#uploadModal').on('hidden.bs.modal', function() {
    $('#uploadForm')[0].reset();
    $('.custom-file-label').removeClass('selected').html('Pilih file...');
});

$('#uploadLainnyaModal').on('hidden.bs.modal', function() {
    $('#uploadLainnyaForm')[0].reset();
    $('.custom-file-label').removeClass('selected').html('Pilih file...');
});
</script>
@stop

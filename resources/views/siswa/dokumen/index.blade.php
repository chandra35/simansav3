@extends('adminlte::page')

@section('title', 'Dokumen Siswa - SIMANSA')

@section('content_header')
    <h1><i class="fas fa-folder-open"></i> Dokumen Siswa</h1>
@stop

@section('content')
<!-- Info Progress -->
<div class="row">
    <div class="col-12">
        <div class="callout callout-warning">
            <h5><i class="fas fa-upload"></i> Langkah 3: Upload Dokumen</h5>
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
                <li>Ukuran maksimal file: <strong>2 MB</strong></li>
                <li>Pastikan dokumen <strong>jelas dan dapat dibaca</strong></li>
                <li>Upload ulang akan <strong>mengganti dokumen lama</strong></li>
                <li>Dokumen <strong>KK dan Ijazah SMP</strong> adalah wajib, sedangkan <strong>KIP dan SKTM</strong> opsional</li>
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
                        <a href="{{ $kk->getFileUrl() }}" target="_blank" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i> Lihat
                        </a>
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
                        <a href="{{ $ijazah->getFileUrl() }}" target="_blank" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i> Lihat
                        </a>
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
                        <a href="{{ $kip->getFileUrl() }}" target="_blank" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i> Lihat
                        </a>
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
                        <a href="{{ $sktm->getFileUrl() }}" target="_blank" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i> Lihat
                        </a>
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
                            Format: PDF, JPG, JPEG, PNG (Max: 2MB)
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
@stop

@section('css')
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css">
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

// Show upload modal
function showUploadModal(jenisDokumen, label) {
    $('#jenis_dokumen').val(jenisDokumen);
    $('#uploadModalLabel').text('Upload ' + label);
    $('#uploadModal').modal('show');
}

// Handle file input change
$('#file').on('change', function() {
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

// Reset form when modal is closed
$('#uploadModal').on('hidden.bs.modal', function() {
    $('#uploadForm')[0].reset();
    $('.custom-file-label').removeClass('selected').html('Pilih file...');
});
</script>
@stop

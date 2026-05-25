@extends('adminlte::page')

@section('title', 'Download Center')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-download"></i> Download Center</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('admin.download-settings.edit') }}" class="btn btn-secondary">
                    <i class="fas fa-cog"></i> Pengaturan Storage
                </a>
                <a href="{{ route('admin.download-categories.index') }}" class="btn btn-info">
                    <i class="fas fa-folder"></i> Kategori
                </a>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#uploadDownloadModal">
                    <i class="fas fa-plus"></i> Tambah File
                </button>
            </div>
        </div>
    </div>
@endsection

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card simansa-management-card mb-3">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filter</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.downloads.index') }}">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Cari judul/file...">
                    </div>
                    <div class="col-md-3 mb-2">
                        <select name="category" class="form-control">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category') === $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <select name="source" class="form-control">
                            <option value="">Semua Storage</option>
                            <option value="local" {{ request('source') === 'local' ? 'selected' : '' }}>Local</option>
                            <option value="gdrive" {{ request('source') === 'gdrive' ? 'selected' : '' }}>GDrive</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <select name="status" class="form-control">
                            <option value="">Semua Status</option>
                            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <button class="btn btn-primary btn-block" type="submit"><i class="fas fa-search"></i> Terapkan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card simansa-management-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-list"></i> Daftar File</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap mb-0">
                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Kategori</th>
                        <th>Ext</th>
                        <th>Ukuran</th>
                        <th>Storage</th>
                        <th>Status</th>
                        <th>Download</th>
                        <th style="width: 180px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($downloads as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->title }}</strong><br>
                                <small class="text-muted">{{ $item->file_name_original }}</small>
                            </td>
                            <td>{{ $item->category->name ?? '-' }}</td>
                            <td><span class="badge badge-secondary">{{ strtoupper($item->file_extension ?: 'FILE') }}</span></td>
                            <td>{{ $item->formatted_size }}</td>
                            <td>
                                @if($item->source === 'gdrive')
                                    <span class="badge badge-info">Google Drive</span>
                                @else
                                    <span class="badge badge-primary">Local</span>
                                @endif
                            </td>
                            <td>
                                @if($item->is_published)
                                    <span class="badge badge-success">Published</span>
                                @else
                                    <span class="badge badge-warning">Draft</span>
                                @endif
                            </td>
                            <td>{{ number_format($item->download_count, 0, ',', '.') }}</td>
                            <td>
                                <a href="{{ route('downloads.download', ['download' => $item, 'filename' => $item->download_route_filename]) }}" class="btn btn-xs btn-info" target="_blank">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.downloads.edit', $item) }}" class="btn btn-xs btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form
                                    action="{{ route('admin.downloads.destroy', $item) }}"
                                    method="POST"
                                    class="d-inline js-confirm-delete-download"
                                    data-no-overlay
                                    data-title="Hapus File?"
                                    data-text="File {{ addslashes($item->title) }} akan dihapus permanen."
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-xs btn-danger" type="submit"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Belum ada file download.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $downloads->links() }}
        </div>
    </div>

    <div class="modal fade" id="uploadDownloadModal" tabindex="-1" role="dialog" aria-labelledby="uploadDownloadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="uploadDownloadForm" action="{{ route('admin.downloads.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header bg-gradient-primary">
                        <h5 class="modal-title" id="uploadDownloadModalLabel">
                            <i class="fas fa-cloud-upload-alt"></i> Upload File Download
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="uploadValidationAlert" class="alert alert-danger d-none mb-3"></div>

                        <div class="form-group">
                            <label for="upload_title">Judul</label>
                            <input type="text" id="upload_title" name="title" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="upload_category_id">Kategori</label>
                            <select id="upload_category_id" name="category_id" class="form-control">
                                <option value="">Tanpa Kategori</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="upload_description">Deskripsi</label>
                            <textarea id="upload_description" name="description" rows="3" class="form-control"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="upload_source">Upload Ke</label>
                            <select id="upload_source" name="source" class="form-control" required>
                                <option value="local" {{ $settings->default_storage === 'local' ? 'selected' : '' }}>Local Simansa</option>
                                <option value="gdrive" {{ $settings->default_storage === 'gdrive' ? 'selected' : '' }}>Google Drive</option>
                            </select>
                            <small class="text-muted">Gunakan Google Drive untuk meringankan penyimpanan dan bandwidth VM.</small>
                        </div>

                        <div class="form-group">
                            <label for="upload_file">File</label>
                            <input type="file" id="upload_file" name="file" class="form-control-file" required>
                            <small class="text-muted">Maksimal 150MB per file.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="custom-control custom-switch mb-2 mb-md-0">
                                    <input type="checkbox" id="upload_is_published" name="is_published" value="1" class="custom-control-input" checked>
                                    <label class="custom-control-label" for="upload_is_published">Published</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="custom-control custom-switch mb-2 mb-md-0">
                                    <input type="checkbox" id="upload_is_featured" name="is_featured" value="1" class="custom-control-input">
                                    <label class="custom-control-label" for="upload_is_featured">Featured</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <input type="datetime-local" name="published_at" class="form-control" value="{{ now()->format('Y-m-d\\TH:i') }}">
                            </div>
                        </div>

                        <div class="upload-progress-wrap d-none mt-3" id="uploadProgressWrap">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="text-muted" id="uploadProgressText">Mempersiapkan upload...</small>
                                <small class="font-weight-bold" id="uploadProgressPercent">0%</small>
                            </div>
                            <div class="progress progress-sm">
                                <div id="uploadProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSubmitUpload">
                            <i class="fas fa-upload"></i> Upload Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('css')
<style>
    .upload-progress-wrap {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        background: #f8fafc;
        padding: .75rem;
    }
</style>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function () {
    const $uploadModal = $('#uploadDownloadModal');
    const $uploadForm = $('#uploadDownloadForm');
    const $uploadAlert = $('#uploadValidationAlert');
    const $progressWrap = $('#uploadProgressWrap');
    const $progressBar = $('#uploadProgressBar');
    const $progressText = $('#uploadProgressText');
    const $progressPercent = $('#uploadProgressPercent');
    const $submitBtn = $('#btnSubmitUpload');

    function resetUploadState() {
        $uploadAlert.addClass('d-none').empty();
        $progressWrap.addClass('d-none');
        $progressBar.css('width', '0%');
        $progressText.text('Mempersiapkan upload...');
        $progressPercent.text('0%');
        $submitBtn.prop('disabled', false).html('<i class="fas fa-upload"></i> Upload Sekarang');
    }

    function renderValidationErrors(errors) {
        const messages = [];
        Object.keys(errors || {}).forEach(function (key) {
            (errors[key] || []).forEach(function (message) {
                messages.push('<li>' + message + '</li>');
            });
        });

        if (!messages.length) {
            messages.push('<li>Data tidak valid. Silakan cek form upload.</li>');
        }

        $uploadAlert.removeClass('d-none').html('<strong>Periksa input berikut:</strong><ul class="mb-0 mt-2">' + messages.join('') + '</ul>');
    }

    $uploadModal.on('hidden.bs.modal', function () {
        $uploadForm[0].reset();
        $('#upload_source').val('{{ $settings->default_storage }}');
        $('#upload_is_published').prop('checked', true);
        $('#upload_is_featured').prop('checked', false);
        $('input[name="published_at"]').val('{{ now()->format('Y-m-d\\TH:i') }}');
        resetUploadState();
    });

    $uploadForm.on('submit', function (event) {
        event.preventDefault();

        Swal.fire({
            title: 'Konfirmasi Upload?',
            text: 'File akan diproses dan disimpan sesuai storage yang dipilih.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Upload',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (!result.isConfirmed) {
                return;
            }

            const formData = new FormData($uploadForm[0]);
            resetUploadState();
            $progressWrap.removeClass('d-none');
            $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mengupload...');

            $.ajax({
                url: $uploadForm.attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'Accept': 'application/json'
                },
                xhr: function () {
                    const xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener('progress', function (e) {
                        if (!e.lengthComputable) {
                            return;
                        }
                        const percent = Math.round((e.loaded / e.total) * 100);
                        $progressBar.css('width', percent + '%');
                        $progressPercent.text(percent + '%');
                        $progressText.text('Mengupload file...');
                    });
                    return xhr;
                },
                success: function (response) {
                    $progressBar.css('width', '100%');
                    $progressPercent.text('100%');
                    $progressText.text('Upload selesai, menyimpan data...');

                    Swal.fire({
                        icon: 'success',
                        title: 'Upload Berhasil',
                        text: response.message || 'File download berhasil ditambahkan.'
                    }).then(function () {
                        window.location.reload();
                    });
                },
                error: function (xhr) {
                    if (xhr.status === 422) {
                        renderValidationErrors(xhr.responseJSON && xhr.responseJSON.errors ? xhr.responseJSON.errors : {});
                        return;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Upload Gagal',
                        text: (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Terjadi kesalahan saat upload file.'
                    });
                },
                complete: function () {
                    $submitBtn.prop('disabled', false).html('<i class="fas fa-upload"></i> Upload Sekarang');
                }
            });
        });
    });

    $(document).on('submit', '.js-confirm-delete-download', function (event) {
        const form = this;
        event.preventDefault();
        Swal.fire({
            title: $(form).data('title') || 'Hapus Data?',
            text: $(form).data('text') || 'Data akan dihapus permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc3545'
        }).then(function (result) {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Menghapus file...',
                    text: 'Mohon tunggu, file sedang dihapus.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: function () {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: $(form).attr('action'),
                    method: 'POST',
                    data: $(form).serialize(),
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function (response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil Dihapus',
                            text: response.message || 'File download berhasil dihapus.'
                        }).then(function () {
                            window.location.reload();
                        });
                    },
                    error: function (xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Menghapus',
                            text: (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Terjadi kesalahan saat menghapus file.'
                        });
                    }
                });
            }
        });
    });
});
</script>
@endsection

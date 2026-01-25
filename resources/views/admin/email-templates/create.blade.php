@extends('adminlte::page')

@section('title', 'Buat Template Email')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-plus-circle text-primary"></i> Buat Template Email</h1>
        <a href="{{ route('admin.email-templates.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
@stop

@section('content')
    <form action="{{ route('admin.email-templates.store') }}" method="POST" id="templateForm">
        @csrf
        
        <div class="row">
            {{-- Left Column - Form --}}
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Informasi Template</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="code">Kode Template <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                           id="code" name="code" value="{{ old('code') }}"
                                           pattern="[a-z0-9_]+" placeholder="contoh: welcome_email"
                                           required>
                                    <small class="text-muted">Huruf kecil, angka, dan underscore saja</small>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Nama Template <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}"
                                           placeholder="contoh: Email Selamat Datang" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="subject">Subject Email <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('subject') is-invalid @enderror" 
                                   id="subject" name="subject" value="{{ old('subject') }}"
                                   placeholder="contoh: Selamat Datang di [nama_sekolah]" required>
                            <small class="text-muted">Dapat menggunakan placeholder seperti [nama_sekolah]</small>
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="body">Isi Email (HTML) <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('body') is-invalid @enderror" 
                                      id="body" name="body" rows="15" required>{{ old('body') }}</textarea>
                            @error('body')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description">Deskripsi</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="2"
                                      placeholder="Penjelasan singkat tentang kapan template ini digunakan">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_active" 
                                       name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">Template Aktif</label>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Template
                        </button>
                        <button type="button" class="btn btn-info" id="btnPreview">
                            <i class="fas fa-eye"></i> Preview
                        </button>
                    </div>
                </div>
            </div>

            {{-- Right Column - Placeholders --}}
            <div class="col-lg-4">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-code"></i> Placeholder Tersedia</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0" style="max-height: 600px; overflow-y: auto;">
                        @foreach($placeholders as $group => $items)
                            <div class="p-2 border-bottom">
                                <strong class="text-primary text-uppercase" style="font-size: 0.8rem;">
                                    <i class="fas fa-folder mr-1"></i>{{ ucfirst($group) }}
                                </strong>
                            </div>
                            <div class="list-group list-group-flush">
                                @foreach($items as $placeholder => $desc)
                                    <a href="javascript:void(0)" class="list-group-item list-group-item-action py-2 placeholder-item" 
                                       data-placeholder="{{ $placeholder }}">
                                        <code class="text-primary">{{ $placeholder }}</code>
                                        <br>
                                        <small class="text-muted">{{ $desc }}</small>
                                    </a>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                    <div class="card-footer text-muted small">
                        <i class="fas fa-info-circle"></i> Klik placeholder untuk menyalin ke clipboard
                    </div>
                </div>

                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-lightbulb"></i> Tips</h3>
                    </div>
                    <div class="card-body">
                        <ul class="pl-3 mb-0 small">
                            <li>Gunakan HTML valid untuk tampilan terbaik</li>
                            <li>Placeholder otomatis diganti saat pengiriman</li>
                            <li>Gunakan inline CSS untuk styling</li>
                            <li>Test dengan Preview sebelum menyimpan</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- Preview Modal --}}
    <div class="modal fade" id="previewModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white"><i class="fas fa-eye"></i> Preview Template</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="font-weight-bold">Subject:</label>
                        <div id="previewSubject" class="border rounded p-2 bg-light"></div>
                    </div>
                    <div>
                        <label class="font-weight-bold">Isi Email:</label>
                        <div class="border rounded">
                            <iframe id="previewFrame" style="width: 100%; height: 400px; border: none;"></iframe>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
<style>
    .placeholder-item:hover {
        background-color: #e8f4fc;
    }
    .note-editor {
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
<script>
$(function() {
    // Initialize Summernote
    $('#body').summernote({
        height: 350,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture']],
            ['view', ['codeview', 'fullscreen', 'help']]
        ],
        callbacks: {
            onInit: function() {
                // Allow raw HTML
            }
        }
    });

    // Copy placeholder to clipboard and insert
    $('.placeholder-item').on('click', function() {
        var placeholder = $(this).data('placeholder');
        navigator.clipboard.writeText(placeholder).then(function() {
            toastr.info('Placeholder "' + placeholder + '" disalin ke clipboard');
        });
        
        // Insert to summernote at cursor position
        $('#body').summernote('editor.insertText', placeholder);
    });

    // Preview
    $('#btnPreview').on('click', function() {
        var subject = $('#subject').val();
        var body = $('#body').summernote('code');
        
        if (!subject || !body) {
            toastr.warning('Mohon isi subject dan body terlebih dahulu');
            return;
        }

        $.post('{{ route("admin.email-templates.preview-form") }}', {
            _token: '{{ csrf_token() }}',
            subject: subject,
            body: body
        }, function(response) {
            if (response.success) {
                $('#previewSubject').text(response.subject);
                var iframe = document.getElementById('previewFrame');
                iframe.srcdoc = response.body;
                $('#previewModal').modal('show');
            }
        });
    });

    // Auto-generate code from name
    $('#name').on('input', function() {
        if (!$('#code').data('manual')) {
            var name = $(this).val();
            var code = name.toLowerCase()
                .replace(/[^a-z0-9\s]/g, '')
                .replace(/\s+/g, '_')
                .substring(0, 50);
            $('#code').val(code);
        }
    });

    $('#code').on('input', function() {
        $(this).data('manual', true);
    });
});
</script>
@stop

@extends('adminlte::page')

@section('title', 'Tambah Custom Menu')

{{-- Enable Select2 plugin --}}
@section('plugins.Select2', true)

{{-- Enable Sweetalert2 plugin --}}
@section('plugins.Sweetalert2', true)

@section('content_header')
    <h1><i class="fas fa-plus-circle"></i> Tambah Custom Menu Siswa</h1>
@stop

@section('content')
<form id="form-create-menu">
    @csrf
    
    <div class="row">
        <div class="col-md-8">
            <!-- Basic Info Card -->
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Informasi Menu</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="judul">Judul Menu <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="judul" name="judul" required
                               placeholder="Contoh: SNBP 2025, WiFi Sekolah, Akun CBT">
                        <small class="text-muted">Judul akan muncul di sidebar siswa</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="icon">Icon <span class="text-muted">(Font Awesome)</span></label>
                                <select class="form-control select2" id="icon" name="icon">
                                    <option value="">Pilih Icon</option>
                                    <option value="fas fa-graduation-cap">🎓 Graduation Cap</option>
                                    <option value="fas fa-book">📚 Book</option>
                                    <option value="fas fa-clipboard-list">📋 Clipboard</option>
                                    <option value="fas fa-file-alt">📄 File</option>
                                    <option value="fas fa-wifi">📡 WiFi</option>
                                    <option value="fas fa-network-wired">🌐 Network</option>
                                    <option value="fas fa-user-shield">🛡️ User Shield</option>
                                    <option value="fas fa-key">🔑 Key</option>
                                    <option value="fas fa-laptop">💻 Laptop</option>
                                    <option value="fas fa-money-bill">💰 Money</option>
                                    <option value="fas fa-certificate">🏆 Certificate</option>
                                    <option value="fas fa-bullhorn">📢 Announcement</option>
                                    <option value="fas fa-info-circle">ℹ️ Info</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="menu_group">Grup Menu <span class="text-danger">*</span></label>
                                <select class="form-control" id="menu_group" name="menu_group" required>
                                    <option value="">Pilih Grup</option>
                                    <option value="akademik">📚 Akademik</option>
                                    <option value="administrasi">📋 Administrasi</option>
                                    <option value="hotspot">📡 Hotspot & Akun</option>
                                    <option value="lainnya">📌 Lainnya</option>
                                </select>
                                <small class="text-muted">Menu akan dikelompokkan berdasarkan grup</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="content_type">Tipe Konten <span class="text-danger">*</span></label>
                        <select class="form-control" id="content_type" name="content_type" required>
                            <option value="">Pilih Tipe</option>
                            <option value="general">📢 Informasi Umum (konten sama untuk semua)</option>
                            <option value="personal">👤 Informasi Personal (data berbeda per siswa)</option>
                        </select>
                        <small class="text-muted">
                            <strong>Umum:</strong> Pengumuman, jadwal, tata tertib<br>
                            <strong>Personal:</strong> Akun hotspot, hasil SNBP, data individual
                        </small>
                    </div>
                </div>
            </div>

            <!-- Content Card -->
            <div class="card">
                <div class="card-header bg-info">
                    <h3 class="card-title"><i class="fas fa-file-alt"></i> Konten Menu</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="konten">Konten <span id="content-type-label"></span></label>
                        <textarea class="form-control" id="konten" name="konten" rows="10"></textarea>
                        <small class="text-muted" id="content-help"></small>
                    </div>
                </div>
            </div>

            <!-- Custom Fields Card (only for personal type) -->
            <div class="card" id="custom-fields-card" style="display:none;">
                <div class="card-header bg-warning">
                    <h3 class="card-title"><i class="fas fa-cogs"></i> Custom Fields (Data Personal)</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">Definisikan field data yang berbeda untuk setiap siswa (username, password, dll)</p>
                    
                    <div id="custom-fields-container">
                        <!-- Dynamic fields will be added here -->
                    </div>

                    <button type="button" class="btn btn-sm btn-success" id="add-custom-field">
                        <i class="fas fa-plus"></i> Tambah Field
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Settings Card -->
            <div class="card">
                <div class="card-header bg-secondary">
                    <h3 class="card-title"><i class="fas fa-cog"></i> Pengaturan</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="urutan">Urutan Tampil</label>
                        <input type="number" class="form-control" id="urutan" name="urutan" value="1" min="1">
                        <small class="text-muted">Urutan menu di sidebar (1 = paling atas)</small>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" checked>
                            <label class="custom-control-label" for="is_active">
                                <strong>Aktifkan Menu</strong>
                            </label>
                        </div>
                        <small class="text-muted">Menu hanya muncul jika aktif</small>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-save"></i> Simpan & Assign Siswa
                    </button>
                    <a href="{{ route('admin.custom-menu.index') }}" class="btn btn-secondary btn-block">
                        <i class="fas fa-arrow-left"></i> Batal
                    </a>
                </div>
            </div>

            <!-- Info Card -->
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-lightbulb"></i> Tips</h3>
                </div>
                <div class="card-body">
                    <ul class="pl-3" style="font-size: 0.9em;">
                        <li>Gunakan <strong>Tipe Umum</strong> untuk pengumuman yang sama untuk semua siswa</li>
                        <li>Gunakan <strong>Tipe Personal</strong> untuk data yang berbeda per siswa (akun, hasil)</li>
                        <li>Setelah save, Anda bisa assign siswa via Excel atau manual</li>
                        <li>Menu bisa di-enable/disable kapan saja</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</form>
@stop

@section('css')
<style>
    .custom-field-item {
        border: 1px solid #ddd;
        padding: 15px;
        margin-bottom: 10px;
        border-radius: 5px;
        background: #f9f9f9;
    }
    .custom-field-item .form-row {
        margin-bottom: 10px;
    }
    
    /* Make ALL textareas resizable both vertically and horizontally */
    textarea.form-control {
        resize: both !important;
        min-height: 100px;
    }
    
    /* Specific styling for konten textarea before TinyMCE initializes */
    #konten {
        min-height: 300px;
    }
</style>
@stop

@section('js')
<!-- TinyMCE CDN (Self-hosted version) -->
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>
<!-- SweetAlert2 Fallback (jika plugin AdminLTE belum load) -->
<script>
if (typeof Swal === 'undefined') {
    document.write('<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"><\/script>');
}
</script>

<script>
let fieldCounter = 0;

$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2();

    // Initialize TinyMCE
    tinymce.init({
        selector: '#konten',
        height: 400,
        menubar: true,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount'
        ],
        toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image | code',
        content_style: 'body { font-family:Arial,sans-serif; font-size:14px }',
        promotion: false // Hide "Upgrade" button
    });

    // Content Type Change Handler
    $('#content_type').change(function() {
        const type = $(this).val();
        
        if (type === 'general') {
            $('#content-type-label').text('(Konten Umum)');
            $('#content-help').text('Konten ini akan sama untuk semua siswa yang di-assign');
            $('#custom-fields-card').slideUp();
        } else if (type === 'personal') {
            $('#content-type-label').text('(Header/Pengantar)');
            $('#content-help').text('Konten ini adalah header. Data personal ditambahkan melalui Custom Fields di bawah');
            $('#custom-fields-card').slideDown();
        }
    });

    // Add Custom Field
    $('#add-custom-field').click(function() {
        fieldCounter++;
        const fieldHtml = `
            <div class="custom-field-item" data-field-id="${fieldCounter}">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Field ${fieldCounter}</h6>
                    <button type="button" class="btn btn-sm btn-danger remove-field">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="form-row">
                    <div class="col-md-6">
                        <input type="text" class="form-control form-control-sm" 
                               name="custom_fields[field_${fieldCounter}][label]" 
                               placeholder="Label (contoh: Username)" required>
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control form-control-sm" 
                               name="custom_fields[field_${fieldCounter}][key]" 
                               placeholder="Key (contoh: username)" required>
                    </div>
                </div>
                <div class="form-row mt-2">
                    <div class="col-md-6">
                        <select class="form-control form-control-sm" 
                                name="custom_fields[field_${fieldCounter}][type]">
                            <option value="text">Text</option>
                            <option value="password">Password</option>
                            <option value="number">Number</option>
                            <option value="email">Email</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" 
                                   id="show_${fieldCounter}" 
                                   name="custom_fields[field_${fieldCounter}][show_in_table]" 
                                   value="1" checked>
                            <label class="custom-control-label" for="show_${fieldCounter}">
                                Tampil di tabel
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('#custom-fields-container').append(fieldHtml);
    });

    // Remove Custom Field
    $(document).on('click', '.remove-field', function() {
        $(this).closest('.custom-field-item').remove();
    });

    // Form Submit
    $('#form-create-menu').submit(function(e) {
        e.preventDefault();
        
        // Get TinyMCE content
        const content = tinymce.get('konten').getContent();
        $('#konten').val(content);

        // Prepare custom fields data
        let customFieldsData = {};
        if ($('#content_type').val() === 'personal') {
            $('.custom-field-item').each(function() {
                const fieldId = $(this).data('field-id');
                const key = $(this).find(`input[name="custom_fields[field_${fieldId}][key]"]`).val();
                
                if (key) {
                    customFieldsData[key] = {
                        label: $(this).find(`input[name="custom_fields[field_${fieldId}][label]"]`).val(),
                        type: $(this).find(`select[name="custom_fields[field_${fieldId}][type]"]`).val(),
                        show_in_table: $(this).find(`input[name="custom_fields[field_${fieldId}][show_in_table]"]`).is(':checked')
                    };
                }
            });
        }

        const formData = {
            _token: '{{ csrf_token() }}',
            judul: $('#judul').val(),
            icon: $('#icon').val(),
            menu_group: $('#menu_group').val(),
            content_type: $('#content_type').val(),
            konten: content,
            custom_fields: JSON.stringify(customFieldsData),
            urutan: $('#urutan').val(),
            is_active: $('#is_active').is(':checked') ? 1 : 0
        };

        $.ajax({
            url: '{{ route('admin.custom-menu.store') }}',
            type: 'POST',
            data: formData,
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.message,
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.href = response.redirect;
                });
            },
            error: function(xhr) {
                let errorMsg = 'Terjadi kesalahan';
                
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    html: errorMsg
                });
            }
        });
    });
});
</script>
@stop

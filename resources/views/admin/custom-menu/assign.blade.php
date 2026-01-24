@extends('adminlte::page')

@section('title', 'Assign Siswa - ' . $customMenu->judul)

{{-- Enable Select2 plugin --}}
@section('plugins.Select2', true)

{{-- Enable Sweetalert2 plugin --}}
@section('plugins.Sweetalert2', true)

{{-- Enable DataTables plugin --}}
@section('plugins.Datatables', true)

@section('content_header')
    <h1><i class="fas fa-users"></i> Assign Siswa: <strong>{{ $customMenu->judul }}</strong></h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary card-tabs">
            <div class="card-header p-0 pt-1">
                <ul class="nav nav-tabs" id="custom-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tab-nisn" data-toggle="pill" href="#nisn" role="tab">
                            <i class="fas fa-list-ol"></i> Input NISN
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-excel" data-toggle="pill" href="#excel" role="tab">
                            <i class="fas fa-file-excel"></i> Upload Excel
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-manual" data-toggle="pill" href="#manual" role="tab">
                            <i class="fas fa-hand-pointer"></i> Pilih Manual
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-kelas" data-toggle="pill" href="#kelas" role="tab">
                            <i class="fas fa-chalkboard"></i> By Kelas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-assigned" data-toggle="pill" href="#assigned" role="tab">
                            <i class="fas fa-check-circle"></i> Siswa Ter-assign ({{ $customMenu->siswa_assigned_count ?? 0 }})
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="custom-tabContent">
                    <!-- Tab: Input NISN -->
                    <div class="tab-pane fade show active" id="nisn" role="tabpanel">
                        <div class="row">
                            <div class="col-md-7">
                                <h5><i class="fas fa-list-ol"></i> Assign Berdasarkan NISN</h5>
                                <p class="text-muted">
                                    Masukkan NISN siswa yang ingin di-assign. Cocok untuk menu informatif seperti pengumuman SNBP, daftar siswa eligible, dll.
                                </p>
                                
                                <form id="form-assign-nisn">
                                    @csrf
                                    <div class="form-group">
                                        <label>Daftar NISN <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="nisn_list" name="nisn_list" rows="10" 
                                                  placeholder="Masukkan NISN (satu per baris)&#10;Contoh:&#10;0123456789&#10;0123456790&#10;0123456791"></textarea>
                                        <small class="text-muted">
                                            Satu NISN per baris. Data siswa akan diambil otomatis dari database.
                                        </small>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-user-plus"></i> Assign Siswa
                                        </button>
                                        <span class="text-muted" id="nisn-count">0 NISN</span>
                                    </div>
                                </form>

                                <div id="nisn-result" class="mt-3" style="display:none;">
                                    <div class="alert alert-info">
                                        <h6><i class="fas fa-info-circle"></i> Hasil Assign:</h6>
                                        <ul id="nisn-summary"></ul>
                                    </div>
                                    <div id="nisn-errors" style="display:none;">
                                        <h6 class="text-danger"><i class="fas fa-exclamation-triangle"></i> NISN Tidak Ditemukan:</h6>
                                        <ul id="nisn-error-list" class="text-danger"></ul>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-5">
                                <div class="card card-success">
                                    <div class="card-header">
                                        <h3 class="card-title"><i class="fas fa-lightbulb"></i> Kapan Gunakan Fitur Ini?</h3>
                                    </div>
                                    <div class="card-body">
                                        <ul class="mb-0 pl-3">
                                            <li class="mb-2">
                                                <strong>Pengumuman SNBP:</strong><br>
                                                <small class="text-muted">Assign daftar siswa eligible/tidak eligible</small>
                                            </li>
                                            <li class="mb-2">
                                                <strong>Informasi Beasiswa:</strong><br>
                                                <small class="text-muted">Assign siswa penerima beasiswa</small>
                                            </li>
                                            <li class="mb-2">
                                                <strong>Pengumuman Khusus:</strong><br>
                                                <small class="text-muted">Kirim info hanya ke siswa tertentu</small>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                @if($customMenu->content_type === 'personal' && $customMenu->has_personal_data)
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <strong>Catatan:</strong><br>
                                    Menu ini memiliki Custom Fields. Jika ingin mengisi data personal per siswa, gunakan tab <strong>Upload Excel</strong>.
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Tab: Upload Excel -->
                    <div class="tab-pane fade" id="excel" role="tabpanel">
                        <div class="row">
                            <div class="col-md-8">
                                <h5><i class="fas fa-upload"></i> Upload File Excel</h5>
                                <p class="text-muted">Format file: NISN | Nama | {{ $customMenu->content_type === 'personal' ? 'Custom Fields | ' : '' }}Keterangan</p>
                                
                                <form id="form-upload-excel" enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-group">
                                        <label>File Excel <span class="text-danger">*</span></label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="excel_file" name="excel_file" 
                                                   accept=".xlsx,.xls,.csv" required>
                                            <label class="custom-file-label" for="excel_file">Pilih file...</label>
                                        </div>
                                        <small class="text-muted">Max 5MB. Format: .xlsx, .xls, .csv</small>
                                    </div>

                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-upload"></i> Upload & Import
                                    </button>
                                </form>

                                <div id="import-result" class="mt-3" style="display:none;">
                                    <div class="alert alert-info">
                                        <h6><i class="fas fa-info-circle"></i> Hasil Import:</h6>
                                        <ul id="import-summary"></ul>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card card-info">
                                    <div class="card-header">
                                        <h3 class="card-title"><i class="fas fa-download"></i> Download Template</h3>
                                    </div>
                                    <div class="card-body">
                                        <p>Download template Excel sesuai tipe menu ini:</p>
                                        <a href="{{ route('admin.custom-menu.template', $customMenu->id) }}" 
                                           class="btn btn-success btn-block">
                                            <i class="fas fa-file-excel"></i> Download Template
                                        </a>
                                        @if($customMenu->content_type === 'personal')
                                        <small class="text-muted mt-2 d-block">
                                            <strong>Custom Fields:</strong><br>
                                            @php
                                                $fields = $customMenu->getCustomFieldsArray();
                                            @endphp
                                            @foreach($fields as $key => $field)
                                                - {{ $field['label'] ?? $key }}<br>
                                            @endforeach
                                        </small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab: Manual Selection -->
                    <div class="tab-pane fade" id="manual" role="tabpanel">
                        <h5><i class="fas fa-user-plus"></i> Pilih Siswa Manual</h5>
                        <p class="text-muted">Centang siswa yang ingin di-assign ke menu ini</p>

                        <form id="form-assign-manual">
                            @csrf
                            <div class="form-group">
                                <label>Cari Siswa (NISN/Nama):</label>
                                <input type="text" class="form-control" id="search-siswa" placeholder="Ketik untuk mencari...">
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm" id="table-siswa-manual">
                                    <thead>
                                        <tr>
                                            <th width="5%">
                                                <input type="checkbox" id="check-all">
                                            </th>
                                            <th>NISN</th>
                                            <th>Nama Lengkap</th>
                                            <th>Kelas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Will be loaded via AJAX -->
                                    </tbody>
                                </table>
                            </div>

                            <button type="submit" class="btn btn-primary mt-3">
                                <i class="fas fa-save"></i> Assign Siswa Terpilih
                            </button>
                        </form>
                    </div>

                    <!-- Tab: By Kelas -->
                    <div class="tab-pane fade" id="kelas" role="tabpanel">
                        <h5><i class="fas fa-chalkboard"></i> Assign Berdasarkan Kelas</h5>
                        <p class="text-muted">Assign semua siswa dalam kelas tertentu sekaligus</p>

                        <form id="form-assign-kelas">
                            @csrf
                            <div class="form-group">
                                <label>Pilih Kelas:</label>
                                <select class="form-control select2" id="select-kelas" style="width: 100%;">
                                    <option value="">-- Pilih Kelas --</option>
                                    @foreach($kelas as $k)
                                        <option value="{{ $k->id }}">{{ $k->nama_lengkap }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div id="siswa-in-kelas" style="display:none;">
                                <h6>Siswa dalam kelas: <span id="kelas-name"></span></h6>
                                <p class="text-muted">Total: <span id="siswa-count">0</span> siswa</p>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-user-plus"></i> Assign Semua Siswa di Kelas Ini
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Tab: Assigned Siswa -->
                    <div class="tab-pane fade" id="assigned" role="tabpanel">
                        <h5><i class="fas fa-list"></i> Siswa yang Sudah Di-assign</h5>
                        <p class="text-muted">Daftar siswa yang dapat melihat menu ini</p>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="table-assigned">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>NISN</th>
                                        <th>Nama Lengkap</th>
                                        <th>Kelas</th>
                                        <th width="10%">Status Baca</th>
                                        <th width="10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($customMenu->menuSiswa as $index => $menuSiswa)
                                        @if($menuSiswa->siswa)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $menuSiswa->siswa->nisn }}</td>
                                                <td>{{ $menuSiswa->siswa->nama_lengkap }}</td>
                                                <td>
                                                    @php
                                                        $kelasAktif = $menuSiswa->siswa->kelasAktif->first();
                                                    @endphp
                                                    {{ $kelasAktif ? $kelasAktif->nama_lengkap : '-' }}
                                                </td>
                                                <td>
                                                    @if($menuSiswa->is_read)
                                                        <span class="badge badge-success">
                                                            <i class="fas fa-check"></i> Sudah Dibaca
                                                        </span>
                                                    @else
                                                        <span class="badge badge-secondary">
                                                            <i class="fas fa-clock"></i> Belum Dibaca
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-danger btn-remove-siswa" 
                                                            data-siswa-id="{{ $menuSiswa->siswa->id }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endif
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">
                                                Belum ada siswa yang di-assign
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('admin.custom-menu.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Daftar Menu
                </a>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<!-- SweetAlert2 Fallback (jika plugin AdminLTE belum load) -->
<script>
if (typeof Swal === 'undefined') {
    document.write('<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"><\/script>');
}
</script>

<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2();

    // ===== NISN Count =====
    $('#nisn_list').on('input', function() {
        var lines = $(this).val().split('\n').filter(line => line.trim() !== '');
        $('#nisn-count').text(lines.length + ' NISN');
    });

    // ===== Form Assign by NISN =====
    $('#form-assign-nisn').on('submit', function(e) {
        e.preventDefault();
        
        var nisnList = $('#nisn_list').val().trim();
        if (!nisnList) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Masukkan minimal 1 NISN'
            });
            return;
        }

        // Parse NISN list
        var nisns = nisnList.split('\n').map(n => n.trim()).filter(n => n !== '');
        
        if (nisns.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Masukkan minimal 1 NISN yang valid'
            });
            return;
        }

        Swal.fire({
            title: 'Proses Assign',
            html: 'Mengassign ' + nisns.length + ' NISN...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: '{{ route("admin.custom-menu.assign-by-nisn", $customMenu->id) }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                nisn_list: nisns
            },
            success: function(response) {
                Swal.close();
                
                if (response.success) {
                    $('#nisn-result').show();
                    $('#nisn-summary').html(
                        '<li>Total NISN: <strong>' + response.data.total + '</strong></li>' +
                        '<li>Berhasil: <strong class="text-success">' + response.data.success + '</strong></li>' +
                        '<li>Sudah ter-assign: <strong class="text-info">' + response.data.duplicate + '</strong></li>' +
                        '<li>Tidak ditemukan: <strong class="text-danger">' + response.data.not_found + '</strong></li>'
                    );

                    if (response.data.not_found_nisns && response.data.not_found_nisns.length > 0) {
                        $('#nisn-errors').show();
                        var errorHtml = response.data.not_found_nisns.map(n => '<li>' + n + '</li>').join('');
                        $('#nisn-error-list').html(errorHtml);
                    } else {
                        $('#nisn-errors').hide();
                    }

                    if (response.data.success > 0) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.data.success + ' siswa berhasil di-assign',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    }
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                });
            }
        });
    });

    // Initialize DataTables for assigned siswa table
    var assignedTable = $('#table-assigned').DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "pageLength": 25,
        "order": [[1, 'asc']], // Sort by NISN
        "language": {
            "search": "Cari:",
            "lengthMenu": "Tampilkan _MENU_ data per halaman",
            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ siswa",
            "infoEmpty": "Menampilkan 0 sampai 0 dari 0 siswa",
            "infoFiltered": "(difilter dari _MAX_ total siswa)",
            "paginate": {
                "first": "Pertama",
                "last": "Terakhir",
                "next": "Selanjutnya",
                "previous": "Sebelumnya"
            },
            "zeroRecords": "Tidak ada data yang ditemukan",
            "emptyTable": "Belum ada siswa yang di-assign"
        },
        "columnDefs": [
            { "orderable": false, "targets": [0, 5] } // Disable sorting on no & action column
        ]
    });

    // Auto-number rows based on current page
    assignedTable.on('draw', function() {
        var info = assignedTable.page.info();
        assignedTable.column(0, {search:'applied', order:'applied', page:'current'}).nodes().each(function(cell, i) {
            cell.innerHTML = info.start + i + 1;
        });
    });

    // ===== Load Siswa untuk Tab Manual =====
    var searchTimeout;
    function loadSiswaManual(search = '') {
        var tbody = $('#table-siswa-manual tbody');
        tbody.html('<tr><td colspan="4" class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat data...</td></tr>');
        
        $.ajax({
            url: '{{ route("admin.custom-menu.get-siswa", $customMenu->id) }}',
            type: 'GET',
            data: { search: search },
            success: function(response) {
                tbody.empty();
                if (response.data && response.data.length > 0) {
                    response.data.forEach(function(siswa) {
                        var checked = siswa.is_assigned ? 'checked disabled' : '';
                        var rowClass = siswa.is_assigned ? 'table-success' : '';
                        tbody.append(`
                            <tr class="${rowClass}">
                                <td class="text-center">
                                    <input type="checkbox" name="siswa_ids[]" value="${siswa.id}" ${checked}>
                                </td>
                                <td>${siswa.nisn}</td>
                                <td>${siswa.nama_lengkap} ${siswa.is_assigned ? '<span class="badge badge-success">Assigned</span>' : ''}</td>
                                <td>${siswa.kelas}</td>
                            </tr>
                        `);
                    });
                } else {
                    tbody.html('<tr><td colspan="4" class="text-center text-muted">Tidak ada siswa ditemukan</td></tr>');
                }
            },
            error: function() {
                tbody.html('<tr><td colspan="4" class="text-center text-danger">Gagal memuat data</td></tr>');
            }
        });
    }

    // Load siswa when tab Manual is clicked
    $('a[href="#manual"]').on('shown.bs.tab', function() {
        loadSiswaManual();
    });

    // Search siswa
    $('#search-siswa').on('keyup', function() {
        clearTimeout(searchTimeout);
        var search = $(this).val();
        searchTimeout = setTimeout(function() {
            loadSiswaManual(search);
        }, 300);
    });

    // Check all checkbox
    $('#check-all').on('change', function() {
        var isChecked = $(this).is(':checked');
        $('#table-siswa-manual tbody input[type="checkbox"]:not(:disabled)').prop('checked', isChecked);
    });

    // Form assign manual
    $('#form-assign-manual').on('submit', function(e) {
        e.preventDefault();
        
        var selectedIds = [];
        $('#table-siswa-manual tbody input[type="checkbox"]:checked:not(:disabled)').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Pilih minimal 1 siswa untuk di-assign'
            });
            return;
        }

        $.ajax({
            url: '{{ route("admin.custom-menu.assign-siswa", $customMenu->id) }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                siswa_ids: selectedIds
            },
            beforeSend: function() {
                Swal.fire({
                    title: 'Menyimpan...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
            },
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                });
            }
        });
    });

    // ===== Load Siswa by Kelas =====
    $('#select-kelas').on('change', function() {
        var kelasId = $(this).val();
        if (!kelasId) {
            $('#siswa-in-kelas').hide();
            return;
        }

        $.ajax({
            url: '{{ route("admin.custom-menu.get-siswa-by-kelas", $customMenu->id) }}',
            type: 'GET',
            data: { kelas_id: kelasId },
            success: function(response) {
                $('#kelas-name').text($('#select-kelas option:selected').text());
                $('#siswa-count').text(response.count);
                $('#siswa-in-kelas').show();
            }
        });
    });

    // Form assign by kelas
    $('#form-assign-kelas').on('submit', function(e) {
        e.preventDefault();
        
        var kelasId = $('#select-kelas').val();
        if (!kelasId) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Pilih kelas terlebih dahulu'
            });
            return;
        }

        // Get all siswa IDs from that class
        $.ajax({
            url: '{{ route("admin.custom-menu.get-siswa-by-kelas", $customMenu->id) }}',
            type: 'GET',
            data: { kelas_id: kelasId },
            success: function(response) {
                var siswaIds = response.data.filter(s => !s.is_assigned).map(s => s.id);
                
                if (siswaIds.length === 0) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Info',
                        text: 'Semua siswa di kelas ini sudah di-assign'
                    });
                    return;
                }

                $.ajax({
                    url: '{{ route("admin.custom-menu.assign-siswa", $customMenu->id) }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        siswa_ids: siswaIds
                    },
                    beforeSend: function() {
                        Swal.fire({
                            title: 'Menyimpan...',
                            allowOutsideClick: false,
                            didOpen: () => { Swal.showLoading(); }
                        });
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                        });
                    }
                });
            }
        });
    });

    // Custom file input label
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).siblings('.custom-file-label').addClass('selected').html(fileName);
    });

    // Upload Excel
    $('#form-upload-excel').submit(function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        $.ajax({
            url: '{{ route('admin.custom-menu.upload-excel', $customMenu->id) }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                Swal.fire({
                    title: 'Mengupload...',
                    text: 'Mohon tunggu',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function(response) {
                Swal.close();
                
                const result = response.data;
                let summary = `
                    <li>Total Data: ${result.total}</li>
                    <li class="text-success">Berhasil: ${result.success}</li>
                    <li class="text-warning">Duplikat: ${result.duplicate}</li>
                    <li class="text-danger">Gagal: ${result.failed}</li>
                `;
                
                if (result.errors && result.errors.length > 0) {
                    summary += '<li><strong>Error:</strong><ul>';
                    result.errors.slice(0, 5).forEach(err => {
                        summary += `<li>${err}</li>`;
                    });
                    if (result.errors.length > 5) {
                        summary += `<li>... dan ${result.errors.length - 5} error lainnya</li>`;
                    }
                    summary += '</ul></li>';
                }
                
                $('#import-summary').html(summary);
                $('#import-result').slideDown();
                
                // Reload assigned tab
                setTimeout(() => {
                    location.reload();
                }, 2000);
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Upload!',
                    text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                });
            }
        });
    });

    // Remove Siswa
    $(document).on('click', '.btn-remove-siswa', function() {
        const siswaId = $(this).data('siswa-id');
        
        Swal.fire({
            title: 'Konfirmasi',
            text: 'Hapus siswa dari menu ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route('admin.custom-menu.remove-siswa', $customMenu->id) }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        siswa_ids: [siswaId]
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                        });
                    }
                });
            }
        });
    });
});
</script>
@stop

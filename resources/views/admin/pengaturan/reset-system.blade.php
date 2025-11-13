@extends('adminlte::page')

@section('title', 'Reset Data Sistem')

@section('plugins.Sweetalert2', true)

@section('content_header')
    <h1><i class="fas fa-exclamation-triangle text-danger"></i> Reset Data Sistem</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="alert alert-danger">
            <h5><i class="icon fas fa-exclamation-triangle"></i> PERINGATAN!</h5>
            Halaman ini hanya untuk <strong>Super Admin</strong>. Semua operasi bersifat <strong>PERMANEN</strong> atau <strong>ARCHIVE</strong>.<br>
            Backup otomatis akan dibuat sebelum penghapusan. <strong>Masukkan password Anda untuk konfirmasi.</strong>
        </div>
    </div>
</div>

<div class="row">
    <!-- Reset ALL -->
    <div class="col-md-6">
        <div class="card card-danger">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-bomb"></i> Hapus SEMUA Data</h3>
            </div>
            <div class="card-body">
                <p class="text-muted">Hapus seluruh data Siswa, GTK, Kelas, Tahun Pelajaran, Kurikulum, Jurusan, Activity Logs</p>
                
                <div class="row mb-3">
                    <div class="col-6"><strong>Siswa:</strong></div>
                    <div class="col-6 text-right"><span class="badge badge-info">{{ number_format($counts['siswa']) }}</span></div>
                </div>
                <div class="row mb-3">
                    <div class="col-6"><strong>GTK:</strong></div>
                    <div class="col-6 text-right"><span class="badge badge-info">{{ number_format($counts['gtk']) }}</span></div>
                </div>
                <div class="row mb-3">
                    <div class="col-6"><strong>Kelas:</strong></div>
                    <div class="col-6 text-right"><span class="badge badge-info">{{ number_format($counts['kelas']) }}</span></div>
                </div>
                <div class="row mb-3">
                    <div class="col-6"><strong>Tahun Pelajaran:</strong></div>
                    <div class="col-6 text-right"><span class="badge badge-info">{{ number_format($counts['tahun_pelajaran']) }}</span></div>
                </div>
                <div class="row mb-3">
                    <div class="col-6"><strong>Kurikulum:</strong></div>
                    <div class="col-6 text-right"><span class="badge badge-info">{{ number_format($counts['kurikulum']) }}</span></div>
                </div>
                <div class="row mb-3">
                    <div class="col-6"><strong>Activity Logs:</strong></div>
                    <div class="col-6 text-right"><span class="badge badge-secondary">{{ number_format($counts['activity_logs']) }}</span></div>
                </div>

                <button class="btn btn-danger btn-block" onclick="showDeleteAllModal()">
                    <i class="fas fa-trash-alt"></i> Hapus SEMUA Data
                </button>
            </div>
        </div>
    </div>

    <!-- Per-Feature Delete -->
    <div class="col-md-6">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list"></i> Hapus Per Fitur</h3>
            </div>
            <div class="card-body">
                <p class="text-muted">Hapus data berdasarkan kategori tertentu saja</p>

                <div class="mb-3">
                    <button class="btn btn-outline-danger btn-block" onclick="showDeleteModal('siswa', '{{ $counts['siswa'] }}')">
                        <i class="fas fa-user-graduate"></i> Hapus Data Siswa ({{ number_format($counts['siswa']) }})
                    </button>
                </div>

                <div class="mb-3">
                    <button class="btn btn-outline-danger btn-block" onclick="showDeleteModal('gtk', '{{ $counts['gtk'] }}')">
                        <i class="fas fa-chalkboard-teacher"></i> Hapus Data GTK ({{ number_format($counts['gtk']) }})
                    </button>
                </div>

                <div class="mb-3">
                    <button class="btn btn-outline-danger btn-block" onclick="showDeleteModal('kelas', '{{ $counts['kelas'] }}')">
                        <i class="fas fa-door-open"></i> Hapus Data Kelas ({{ number_format($counts['kelas']) }})
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Backup Section -->
<div class="row mt-3">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-database"></i> Backup & Restore</h3>
                <div class="card-tools">
                    <button class="btn btn-sm btn-success" onclick="createBackup()">
                        <i class="fas fa-plus"></i> Buat Backup
                    </button>
                </div>
            </div>
            <div class="card-body">
                <p class="mb-3">
                    <strong>Total Backups:</strong> {{ count($backups) }} file | 
                    <strong>Total Size:</strong> {{ $backupStats['formatted'] }}
                </p>

                @if(count($backups) > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>File</th>
                                <th>Ukuran</th>
                                <th>Dibuat</th>
                                <th width="200">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($backups as $backup)
                            <tr>
                                <td><code>{{ $backup['filename'] }}</code></td>
                                <td>{{ $backup['size_formatted'] }}</td>
                                <td>{{ $backup['created_at'] }} <small class="text-muted">({{ $backup['age'] }})</small></td>
                                <td>
                                    <a href="{{ route('admin.reset-system.download-backup', $backup['filename']) }}" class="btn btn-xs btn-info">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                    <button class="btn btn-xs btn-primary" onclick="showRestoreModal('{{ $backup['filename'] }}')">
                                        <i class="fas fa-undo"></i> Restore
                                    </button>
                                    <button class="btn btn-xs btn-danger" onclick="deleteBackupFile('{{ $backup['filename'] }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-center text-muted">Belum ada backup</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Delete ALL -->
<div class="modal fade" id="deleteAllModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title">Konfirmasi Hapus SEMUA Data</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <strong>PERHATIAN!</strong> Anda akan menghapus seluruh data sistem.
                </div>

                <div class="form-group">
                    <label>Mode Penghapusan</label>
                    <select class="form-control" id="deleteAllMode">
                        <option value="archive">Archive (Soft Delete - dapat dipulihkan)</option>
                        <option value="permanent">Permanent (Hard Delete - TIDAK dapat dipulihkan)</option>
                    </select>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="deleteAllAutoBackup" checked>
                        <label class="custom-control-label" for="deleteAllAutoBackup">Buat backup otomatis sebelum hapus</label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Ketik: <code>HAPUS SEMUA DATA</code></label>
                    <input type="text" class="form-control" id="deleteAllConfirmation" placeholder="HAPUS SEMUA DATA">
                </div>

                <div class="form-group">
                    <label>Password Anda</label>
                    <input type="password" class="form-control" id="deleteAllPassword" placeholder="Masukkan password">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" onclick="executeDeleteAll()">
                    <i class="fas fa-trash-alt"></i> Hapus SEMUA
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Delete Per Feature -->
<div class="modal fade" id="deleteFeatureModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="deleteFeatureTitle">Konfirmasi Hapus Data</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p id="deleteFeatureMessage"></p>

                <div class="form-group">
                    <label>Mode Penghapusan</label>
                    <select class="form-control" id="deleteFeatureMode">
                        <option value="archive">Archive (Soft Delete)</option>
                        <option value="permanent">Permanent (Hard Delete)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Ketik: <code id="deleteFeatureConfirmText"></code></label>
                    <input type="text" class="form-control" id="deleteFeatureConfirmation">
                </div>

                <div class="form-group">
                    <label>Password Anda</label>
                    <input type="password" class="form-control" id="deleteFeaturePassword">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-warning" onclick="executeDeleteFeature()">
                    <i class="fas fa-trash"></i> Hapus
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Restore -->
<div class="modal fade" id="restoreModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title">Restore Database</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    Restore akan mengganti database saat ini dengan backup yang dipilih. Backup keamanan akan dibuat sebelum restore.
                </div>

                <p>File: <code id="restoreFilename"></code></p>

                <div class="form-group">
                    <label>Ketik: <code>RESTORE DATABASE</code></label>
                    <input type="text" class="form-control" id="restoreConfirmation">
                </div>

                <div class="form-group">
                    <label>Password Anda</label>
                    <input type="password" class="form-control" id="restorePassword">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="executeRestore()">
                    <i class="fas fa-undo"></i> Restore
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
let currentFeature = '';

function showDeleteAllModal() {
    $('#deleteAllModal').modal('show');
}

function showDeleteModal(feature, count) {
    currentFeature = feature;
    const titles = {
        siswa: 'Siswa',
        gtk: 'GTK',
        kelas: 'Kelas'
    };
    const confirmTexts = {
        siswa: 'HAPUS DATA SISWA',
        gtk: 'HAPUS DATA GTK',
        kelas: 'HAPUS DATA KELAS'
    };

    $('#deleteFeatureTitle').text('Konfirmasi Hapus Data ' + titles[feature]);
    $('#deleteFeatureMessage').text(`Anda akan menghapus ${count} data ${titles[feature]}.`);
    $('#deleteFeatureConfirmText').text(confirmTexts[feature]);
    $('#deleteFeatureConfirmation').val('');
    $('#deleteFeaturePassword').val('');
    $('#deleteFeatureModal').modal('show');
}

function executeDeleteAll() {
    const mode = $('#deleteAllMode').val();
    const confirmation = $('#deleteAllConfirmation').val();
    const password = $('#deleteAllPassword').val();
    const autoBackup = $('#deleteAllAutoBackup').is(':checked');

    if (confirmation !== 'HAPUS SEMUA DATA') {
        Swal.fire('Error', 'Konfirmasi tidak sesuai', 'error');
        return;
    }

    if (!password) {
        Swal.fire('Error', 'Masukkan password', 'error');
        return;
    }

    Swal.fire({
        title: 'Menghapus...',
        text: 'Mohon tunggu...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    $.ajax({
        url: '{{ route("admin.reset-system.delete-all") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            mode: mode,
            confirmation: confirmation,
            password: password,
            auto_backup: autoBackup
        },
        success: function(res) {
            Swal.close();
            if (res.success) {
                Swal.fire('Berhasil!', res.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Gagal', res.message, 'error');
            }
        },
        error: function(xhr) {
            Swal.close();
            Swal.fire('Error', xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
        }
    });

    $('#deleteAllModal').modal('hide');
}

function executeDeleteFeature() {
    const mode = $('#deleteFeatureMode').val();
    const confirmation = $('#deleteFeatureConfirmation').val();
    const password = $('#deleteFeaturePassword').val();

    const confirmTexts = {
        siswa: 'HAPUS DATA SISWA',
        gtk: 'HAPUS DATA GTK',
        kelas: 'HAPUS DATA KELAS'
    };

    if (confirmation !== confirmTexts[currentFeature]) {
        Swal.fire('Error', 'Konfirmasi tidak sesuai', 'error');
        return;
    }

    if (!password) {
        Swal.fire('Error', 'Masukkan password', 'error');
        return;
    }

    Swal.fire({
        title: 'Menghapus...',
        text: 'Mohon tunggu...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    const routes = {
        siswa: '{{ route("admin.reset-system.delete-siswa") }}',
        gtk: '{{ route("admin.reset-system.delete-gtk") }}',
        kelas: '{{ route("admin.reset-system.delete-kelas") }}'
    };

    $.ajax({
        url: routes[currentFeature],
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            mode: mode,
            confirmation: confirmation,
            password: password
        },
        success: function(res) {
            Swal.close();
            if (res.success) {
                Swal.fire('Berhasil!', res.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Gagal', res.message, 'error');
            }
        },
        error: function(xhr) {
            Swal.close();
            Swal.fire('Error', xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
        }
    });

    $('#deleteFeatureModal').modal('hide');
}

function createBackup() {
    Swal.fire({
        title: 'Membuat Backup...',
        text: 'Mohon tunggu...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    $.ajax({
        url: '{{ route("admin.reset-system.create-backup") }}',
        type: 'POST',
        data: { _token: '{{ csrf_token() }}' },
        success: function(res) {
            Swal.close();
            if (res.success) {
                Swal.fire('Berhasil!', 'Backup berhasil dibuat: ' + res.filename, 'success').then(() => location.reload());
            } else {
                Swal.fire('Gagal', res.message, 'error');
            }
        },
        error: function(xhr) {
            Swal.close();
            Swal.fire('Error', 'Terjadi kesalahan', 'error');
        }
    });
}

function showRestoreModal(filename) {
    $('#restoreFilename').text(filename);
    $('#restoreConfirmation').val('');
    $('#restorePassword').val('');
    $('#restoreModal').modal('show');
}

function executeRestore() {
    const filename = $('#restoreFilename').text();
    const confirmation = $('#restoreConfirmation').val();
    const password = $('#restorePassword').val();

    if (confirmation !== 'RESTORE DATABASE') {
        Swal.fire('Error', 'Konfirmasi tidak sesuai', 'error');
        return;
    }

    if (!password) {
        Swal.fire('Error', 'Masukkan password', 'error');
        return;
    }

    Swal.fire({
        title: 'Restoring...',
        text: 'Mohon tunggu...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    $.ajax({
        url: '{{ route("admin.reset-system.restore-backup") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            filename: filename,
            confirmation: confirmation,
            password: password
        },
        success: function(res) {
            Swal.close();
            if (res.success) {
                Swal.fire('Berhasil!', res.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Gagal', res.message, 'error');
            }
        },
        error: function(xhr) {
            Swal.close();
            Swal.fire('Error', xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
        }
    });

    $('#restoreModal').modal('hide');
}

function deleteBackupFile(filename) {
    Swal.fire({
        title: 'Hapus Backup?',
        text: 'File: ' + filename,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("admin.reset-system.delete-backup", ":filename") }}'.replace(':filename', filename),
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    if (res.success) {
                        Swal.fire('Berhasil!', 'Backup dihapus', 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Terjadi kesalahan', 'error');
                }
            });
        }
    });
}
</script>
@stop

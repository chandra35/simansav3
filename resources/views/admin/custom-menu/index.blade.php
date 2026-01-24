@extends('adminlte::page')

@section('title', 'Custom Menu Siswa')

{{-- Enable Datatables plugin --}}
@section('plugins.Datatables', true)

{{-- Enable Sweetalert2 plugin --}}
@section('plugins.Sweetalert2', true)

@section('content_header')
    <h1><i class="fas fa-th-list"></i> Custom Menu Siswa</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title"><i class="fas fa-list"></i> Daftar Custom Menu</h3>
            <a href="{{ route('admin.custom-menu.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Menu Baru
            </a>
        </div>
    </div>
    <div class="card-body">
        <!-- Filters -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label>Filter Grup:</label>
                <select id="filter-group" class="form-control">
                    <option value="">Semua Grup</option>
                    <option value="akademik">Akademik</option>
                    <option value="administrasi">Administrasi</option>
                    <option value="hotspot">Hotspot & Akun</option>
                    <option value="lainnya">Lainnya</option>
                </select>
            </div>
            <div class="col-md-4">
                <label>Filter Status:</label>
                <select id="filter-status" class="form-control">
                    <option value="">Semua Status</option>
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>
        </div>

        <!-- DataTable -->
        <div class="table-responsive">
            <table id="custom-menu-table" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th>Judul Menu</th>
                        <th width="10%">Grup</th>
                        <th width="10%">Tipe</th>
                        <th width="10%">Status</th>
                        <th width="10%">Jumlah Siswa</th>
                        <th width="8%">Urutan</th>
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .btn-group {
        display: flex;
        gap: 2px;
    }
</style>
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
    console.log('Document ready, initializing...'); // Debug
    
    // Initialize DataTable
    const table = $('#custom-menu-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('admin.custom-menu.index') }}',
            data: function(d) {
                d.group = $('#filter-group').val();
                d.status = $('#filter-status').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'judul', name: 'judul' },
            { data: 'group_badge', name: 'menu_group', orderable: false },
            { data: 'type_label', name: 'content_type' },
            { data: 'status_badge', name: 'is_active', orderable: false },
            { data: 'siswa_count', name: 'siswa_count', orderable: false, searchable: false },
            { data: 'urutan', name: 'urutan' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[6, 'asc']], // Sort by urutan
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        }
    });
    
    console.log('DataTable initialized'); // Debug

    // Filter handlers
    $('#filter-group, #filter-status').change(function() {
        table.ajax.reload();
    });

    // Toggle Status
    $(document).on('click', '.toggle-status', function() {
        const id = $(this).data('id');
        const currentStatus = $(this).data('status');
        const statusText = currentStatus ? 'nonaktifkan' : 'aktifkan';
        
        console.log('Toggle clicked:', { id, currentStatus, statusText }); // Debug
        
        Swal.fire({
            title: 'Konfirmasi',
            text: `Apakah Anda yakin ingin ${statusText} menu ini?`,
            type: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Lanjutkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            console.log('Swal result:', result); // Debug
            if (result.value) { // Changed from isConfirmed to value for v8
                console.log('Sending AJAX request...'); // Debug
                $.ajax({
                    url: '{{ route('admin.custom-menu.toggle-status', ':id') }}'.replace(':id', id),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        console.log('Success response:', response); // Debug
                        Swal.fire({
                            type: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        console.error('Error response:', xhr); // Debug
                        Swal.fire({
                            type: 'error',
                            title: 'Gagal!',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                        });
                    }
                });
            }
        });
    });

    // Delete Menu
    $(document).on('click', '.delete-menu', function() {
        const id = $(this).data('id');
        
        console.log('Delete menu clicked, ID:', id); // Debug
        
        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: 'Menu dan semua assignment siswa akan dihapus. Yakin?',
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            console.log('Swal result:', result); // Debug
            
            if (result.value) { // Changed from isConfirmed to value for v8
                const deleteUrl = `/admin/custom-menu/${id}`;
                console.log('Deleting URL:', deleteUrl); // Debug
                
                $.ajax({
                    url: deleteUrl,
                    type: 'POST', // Changed from DELETE to POST
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE' // Laravel method spoofing
                    },
                    beforeSend: function() {
                        console.log('Sending delete request...'); // Debug
                    },
                    success: function(response) {
                        console.log('Delete success:', response); // Debug
                        Swal.fire({
                            type: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        table.ajax.reload();
                    },
                    error: function(xhr) {
                        console.error('Delete error:', xhr); // Debug
                        Swal.fire({
                            type: 'error',
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

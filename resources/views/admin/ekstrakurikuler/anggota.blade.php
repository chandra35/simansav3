@extends('adminlte::page')

@section('title', 'Anggota Ekstrakurikuler')

@section('content_header')
    <h1><i class="fas fa-users mr-2"></i>Anggota {{ $ekstrakurikuler->nama }}</h1>
@stop

@section('plugins.Datatables', true)
@section('plugins.Select2', true)

@section('content')
    <div class="row">
        <div class="{{ auth()->user()->can('manage-anggota-ekstrakurikuler') ? 'col-md-8' : 'col-12' }}">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daftar Anggota</h3>
                    <div class="card-tools">
                        <span class="badge badge-info">
                            Kuota: {{ $ekstrakurikuler->jumlah_anggota }} / {{ $ekstrakurikuler->kuota_max ?? '∞' }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <table id="anggota-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>NIS</th>
                                <th>Nama Siswa</th>
                                <th>Jabatan</th>
                                <th>Status</th>
                                <th>Nilai</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.ekstrakurikuler.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
        
        @can('manage-anggota-ekstrakurikuler')
        <div class="col-md-4">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-plus mr-1"></i> Tambah Anggota</h3>
                </div>
                <form id="form-tambah-anggota">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="siswa_id">Siswa <span class="text-danger">*</span></label>
                            <select name="siswa_id" id="siswa_id" class="form-control select2" required>
                                <option value="">-- Pilih Siswa --</option>
                                @foreach($siswa as $s)
                                    <option value="{{ $s->id }}">{{ $s->nis }} - {{ $s->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <input type="hidden" name="tahun_pelajaran_id" value="{{ $tahunPelajaranAktif?->id }}">
                        
                        <div class="form-group">
                            <label for="tanggal_bergabung">Tanggal Bergabung <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_bergabung" id="tanggal_bergabung" 
                                class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="jabatan">Jabatan</label>
                            <input type="text" name="jabatan" id="jabatan" class="form-control" 
                                placeholder="Contoh: Ketua, Sekretaris">
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-plus mr-1"></i> Tambah Anggota
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-edit mr-1"></i> Edit Anggota</h3>
                </div>
                <form id="form-edit-anggota" style="display: none;">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="anggota_id" id="edit_anggota_id">
                    <div class="card-body">
                        <div class="form-group">
                            <label>Siswa</label>
                            <input type="text" id="edit_siswa_nama" class="form-control" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_status">Status</label>
                            <select name="status" id="edit_status" class="form-control">
                                <option value="aktif">Aktif</option>
                                <option value="tidak_aktif">Tidak Aktif</option>
                                <option value="keluar">Keluar</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_jabatan">Jabatan</label>
                            <input type="text" name="jabatan" id="edit_jabatan" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_nilai_ekskul">Nilai (0-100)</label>
                            <input type="number" name="nilai_ekskul" id="edit_nilai_ekskul" 
                                class="form-control" min="0" max="100">
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_catatan">Catatan</label>
                            <textarea name="catatan" id="edit_catatan" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="button" class="btn btn-secondary btn-cancel-edit">
                            <i class="fas fa-times mr-1"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-warning float-right">
                            <i class="fas fa-save mr-1"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endcan
    </div>
@stop

@section('js')
<script>
$(function() {
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });
    
    var table = $('#anggota-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.ekstrakurikuler.anggota', $ekstrakurikuler->id) }}",
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'siswa_nis', name: 'siswa.nis'},
            {data: 'siswa_nama', name: 'siswa.nama'},
            {data: 'jabatan', name: 'jabatan'},
            {data: 'status_badge', name: 'status'},
            {data: 'nilai_predikat', name: 'nilai_ekskul'},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ]
    });
    
    // Tambah Anggota
    $('#form-tambah-anggota').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: "{{ route('admin.ekstrakurikuler.anggota.store', $ekstrakurikuler->id) }}",
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    table.ajax.reload();
                    $('#form-tambah-anggota')[0].reset();
                    $('#siswa_id').val('').trigger('change');
                    // Remove added siswa from dropdown
                    location.reload();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                var errors = xhr.responseJSON?.errors;
                if (errors) {
                    $.each(errors, function(key, value) {
                        toastr.error(value[0]);
                    });
                } else {
                    toastr.error('Terjadi kesalahan');
                }
            }
        });
    });
    
    // Edit Anggota
    $(document).on('click', '.btn-edit-anggota', function() {
        var id = $(this).data('id');
        
        $.get("{{ url('admin/ekstrakurikuler-anggota') }}/" + id, function(data) {
            $('#edit_anggota_id').val(data.id);
            $('#edit_siswa_nama').val(data.siswa?.nama || '-');
            $('#edit_status').val(data.status);
            $('#edit_jabatan').val(data.jabatan);
            $('#edit_nilai_ekskul').val(data.nilai_ekskul);
            $('#edit_catatan').val(data.catatan);
            $('#form-edit-anggota').show();
        });
    });
    
    $('.btn-cancel-edit').on('click', function() {
        $('#form-edit-anggota').hide();
        $('#form-edit-anggota')[0].reset();
    });
    
    $('#form-edit-anggota').on('submit', function(e) {
        e.preventDefault();
        var id = $('#edit_anggota_id').val();
        
        $.ajax({
            url: "{{ url('admin/ekstrakurikuler-anggota') }}/" + id,
            method: 'PUT',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    table.ajax.reload();
                    $('#form-edit-anggota').hide();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                toastr.error('Terjadi kesalahan');
            }
        });
    });
    
    // Delete Anggota
    $(document).on('click', '.btn-delete-anggota', function() {
        var id = $(this).data('id');
        
        Swal.fire({
            title: 'Hapus Anggota?',
            text: "Data akan dihapus dari ekstrakurikuler ini",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('admin/ekstrakurikuler-anggota') }}/" + id,
                    method: 'DELETE',
                    data: {_token: '{{ csrf_token() }}'},
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            table.ajax.reload();
                            location.reload();
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function() {
                        toastr.error('Terjadi kesalahan');
                    }
                });
            }
        });
    });
});
</script>
@stop

@extends('adminlte::page')

@section('title', 'Jenis Pembayaran')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-list mr-2"></i>Jenis Pembayaran</h1>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-jenis">
            <i class="fas fa-plus mr-1"></i> Tambah Jenis
        </button>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <table id="jenis-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="30">No</th>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th>Nominal</th>
                        <th width="150">Status</th>
                        <th width="100">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    
    <!-- Modal -->
    <div class="modal fade" id="modal-jenis" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="form-jenis">
                    @csrf
                    <input type="hidden" name="id" id="jenis-id">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Jenis Pembayaran</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Tahun Pelajaran <span class="text-danger">*</span></label>
                            <select name="tahun_pelajaran_id" id="jenis-tahun" class="form-control" required>
                                @foreach($tahunPelajaran as $tp)
                                    <option value="{{ $tp->id }}" {{ $tp->is_aktif ? 'selected' : '' }}>{{ $tp->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Kode <span class="text-danger">*</span></label>
                                    <input type="text" name="kode" id="jenis-kode" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Nama <span class="text-danger">*</span></label>
                                    <input type="text" name="nama" id="jenis-nama" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kategori <span class="text-danger">*</span></label>
                                    <select name="kategori" id="jenis-kategori" class="form-control" required>
                                        @foreach($kategori as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nominal <span class="text-danger">*</span></label>
                                    <input type="number" name="nominal" id="jenis-nominal" class="form-control" min="0" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Keterangan</label>
                            <textarea name="keterangan" id="jenis-keterangan" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="is_wajib" id="jenis-wajib" class="custom-control-input" value="1">
                                    <label class="custom-control-label" for="jenis-wajib">Wajib</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="is_bulanan" id="jenis-bulanan" class="custom-control-input" value="1">
                                    <label class="custom-control-label" for="jenis-bulanan">Bulanan</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="is_aktif" id="jenis-aktif" class="custom-control-input" value="1" checked>
                                    <label class="custom-control-label" for="jenis-aktif">Aktif</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/datatables/css/dataTables.bootstrap4.min.css') }}">
@stop

@section('js')
    <script src="{{ asset('vendor/datatables/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/js/dataTables.bootstrap4.min.js') }}"></script>
    <script>
        $(function() {
            var table = $('#jenis-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.pembayaran.jenis') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'kode', name: 'kode' },
                    { data: 'nama', name: 'nama' },
                    { data: 'kategori_label', name: 'kategori' },
                    { data: 'nominal_format', name: 'nominal' },
                    { data: 'status', name: 'is_aktif' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
                }
            });

            function resetForm() {
                $('#form-jenis')[0].reset();
                $('#jenis-id').val('');
                $('#jenis-aktif').prop('checked', true);
                $('.modal-title').text('Tambah Jenis Pembayaran');
            }

            $('#modal-jenis').on('hidden.bs.modal', function() {
                resetForm();
            });

            // Edit handler
            $(document).on('click', '.btn-edit', function() {
                var id = $(this).data('id');
                $.get('{{ route("admin.pembayaran.jenis") }}/' + id, function(response) {
                    if (response.success) {
                        var data = response.data;
                        $('#jenis-id').val(data.id);
                        $('#jenis-tahun').val(data.tahun_pelajaran_id);
                        $('#jenis-kode').val(data.kode);
                        $('#jenis-nama').val(data.nama);
                        $('#jenis-kategori').val(data.kategori);
                        $('#jenis-nominal').val(data.nominal);
                        $('#jenis-keterangan').val(data.keterangan);
                        $('#jenis-wajib').prop('checked', data.is_wajib);
                        $('#jenis-bulanan').prop('checked', data.is_bulanan);
                        $('#jenis-aktif').prop('checked', data.is_aktif);
                        $('.modal-title').text('Edit Jenis Pembayaran');
                        $('#modal-jenis').modal('show');
                    }
                });
            });

            // Submit handler
            $('#form-jenis').submit(function(e) {
                e.preventDefault();
                var id = $('#jenis-id').val();
                var url = id 
                    ? '{{ route("admin.pembayaran.jenis") }}/' + id 
                    : '{{ route("admin.pembayaran.jenis.store") }}';
                var method = id ? 'PUT' : 'POST';
                
                $.ajax({
                    url: url,
                    type: method,
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#modal-jenis').modal('hide');
                            table.ajax.reload();
                            toastr.success(response.message);
                        }
                    },
                    error: function(xhr) {
                        var errors = xhr.responseJSON?.errors;
                        if (errors) {
                            Object.values(errors).forEach(function(err) {
                                toastr.error(err[0]);
                            });
                        } else {
                            toastr.error('Terjadi kesalahan');
                        }
                    }
                });
            });

            // Delete handler
            $(document).on('click', '.btn-delete', function() {
                var id = $(this).data('id');
                if (confirm('Apakah Anda yakin ingin menghapus jenis pembayaran ini?')) {
                    $.ajax({
                        url: '{{ route("admin.pembayaran.jenis") }}/' + id,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            if (response.success) {
                                table.ajax.reload();
                                toastr.success(response.message);
                            }
                        }
                    });
                }
            });
        });
    </script>
@stop

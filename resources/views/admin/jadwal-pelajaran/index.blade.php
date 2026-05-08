@extends('adminlte::page')

@section('title', 'Jadwal Pelajaran')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-clock mr-2"></i>Jadwal Pelajaran</h1>
        <div>
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal-copy-jadwal">
                <i class="fas fa-copy mr-1"></i> Copy Jadwal
            </button>
            <a href="{{ route('admin.jadwal-pelajaran.timetable') }}" class="btn btn-info">
                <i class="fas fa-calendar-alt mr-1"></i> View Timetable
            </a>
            <a href="{{ route('admin.jadwal-pelajaran.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i> Tambah Jadwal
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-md-3">
                    <select id="filter-tahun" class="form-control">
                        <option value="">-- Semua Tahun Pelajaran --</option>
                        @foreach($tahunPelajaran as $tp)
                            <option value="{{ $tp->id }}" {{ $tp->is_aktif ? 'selected' : '' }}>
                                {{ $tp->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="filter-kelas" class="form-control">
                        <option value="">-- Semua Kelas --</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}">{{ $k->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="filter-hari" class="form-control">
                        <option value="">-- Semua Hari --</option>
                        @foreach($hari as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button id="btn-filter" class="btn btn-info btn-block">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table id="jadwal-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="30">No</th>
                        <th>Kelas</th>
                        <th>Hari</th>
                        <th>Jam Ke</th>
                        <th>Waktu</th>
                        <th>Mata Pelajaran</th>
                        <th>Guru</th>
                        <th>Ruangan</th>
                        <th width="80">Status</th>
                        <th width="100">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    
    <!-- Modal Edit -->
    <div class="modal fade" id="modal-edit" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="form-edit">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" id="edit-id">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title"><i class="fas fa-edit mr-1"></i>Edit Jadwal Pelajaran</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tahun Pelajaran <span class="text-danger">*</span></label>
                                    <select name="tahun_pelajaran_id" id="edit-tahun-pelajaran" class="form-control" required>
                                        @foreach($tahunPelajaran as $tp)
                                            <option value="{{ $tp->id }}">{{ $tp->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kelas <span class="text-danger">*</span></label>
                                    <select name="kelas_id" id="edit-kelas" class="form-control" required>
                                        @foreach($kelas as $k)
                                            <option value="{{ $k->id }}">{{ $k->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Mata Pelajaran <span class="text-danger">*</span></label>
                                    <select name="mapel_id" id="edit-mapel" class="form-control" required>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Guru Pengajar <span class="text-danger">*</span></label>
                                    <select name="gtk_id" id="edit-guru" class="form-control" required>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Hari <span class="text-danger">*</span></label>
                                    <select name="hari" id="edit-hari" class="form-control" required>
                                        @foreach($hari as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Jam Ke <span class="text-danger">*</span></label>
                                    <input type="number" name="jam_ke" id="edit-jam-ke" class="form-control" min="1" max="12" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Semester <span class="text-danger">*</span></label>
                                    <select name="semester" id="edit-semester" class="form-control" required>
                                        <option value="1">Semester 1 (Ganjil)</option>
                                        <option value="2">Semester 2 (Genap)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Waktu Mulai <span class="text-danger">*</span></label>
                                    <input type="time" name="waktu_mulai" id="edit-waktu-mulai" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Waktu Selesai <span class="text-danger">*</span></label>
                                    <input type="time" name="waktu_selesai" id="edit-waktu-selesai" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Ruangan</label>
                                    <input type="text" name="ruangan" id="edit-ruangan" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="is_aktif" id="edit-aktif" class="custom-control-input" value="1">
                                <label class="custom-control-label" for="edit-aktif">Aktif</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning"><i class="fas fa-save mr-1"></i>Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Copy Jadwal --}}
    <div class="modal fade" id="modal-copy-jadwal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-copy mr-1"></i> Copy Jadwal dari Tahun Lain</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Menyalin semua jadwal dari tahun asal ke tahun tujuan. Kelas dicocokkan berdasarkan <strong>nama kelas yang sama</strong>. Jadwal yang sudah ada di tahun tujuan akan dilewati.</p>
                    <div class="form-group">
                        <label>Tahun Pelajaran Asal <span class="text-danger">*</span></label>
                        <select id="copy-tahun-asal" class="form-control">
                            <option value="">-- Pilih Tahun Asal --</option>
                            @foreach($tahunPelajaran as $tp)
                                <option value="{{ $tp->id }}">{{ $tp->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tahun Pelajaran Tujuan <span class="text-danger">*</span></label>
                        <select id="copy-tahun-tujuan" class="form-control">
                            <option value="">-- Pilih Tahun Tujuan --</option>
                            @foreach($tahunPelajaran as $tp)
                                <option value="{{ $tp->id }}">{{ $tp->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="copy-result"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" id="btn-copy-jadwal" class="btn btn-success">
                        <i class="fas fa-copy mr-1"></i> Salin Jadwal
                    </button>
                </div>
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
        // Data for edit form
        var mapelData = @json(\App\Models\Mapel::orderBy('nama')->get());
        var guruData = @json(\App\Models\Gtk::aktif()->orderBy('nama')->get());
        
        $(function() {
            // Populate mapel and guru selects
            mapelData.forEach(function(m) {
                $('#edit-mapel').append('<option value="' + m.id + '">' + m.nama + '</option>');
            });
            guruData.forEach(function(g) {
                $('#edit-guru').append('<option value="' + g.id + '">' + g.nama + '</option>');
            });
            
            var table = $('#jadwal-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.jadwal-pelajaran.index') }}",
                    data: function(d) {
                        d.tahun_pelajaran_id = $('#filter-tahun').val();
                        d.kelas_id = $('#filter-kelas').val();
                        d.hari = $('#filter-hari').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'kelas_nama', name: 'kelas.nama' },
                    { data: 'hari_label', name: 'hari' },
                    { data: 'jam_ke', name: 'jam_ke' },
                    { data: 'waktu', name: 'waktu_mulai' },
                    { data: 'mapel_nama', name: 'mapel.nama' },
                    { data: 'guru_nama', name: 'gtk.nama' },
                    { data: 'ruangan', name: 'ruangan' },
                    { data: 'status', name: 'is_aktif' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
                }
            });

            $('#btn-filter').click(function() {
                table.ajax.reload();
            });
            
            // Edit handler
            $(document).on('click', '.btn-edit', function() {
                var id = $(this).data('id');
                $.ajax({
                    url: '{{ route("admin.jadwal-pelajaran.index") }}/' + id,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            var data = response.data;
                            $('#edit-id').val(data.id);
                            $('#edit-tahun-pelajaran').val(data.tahun_pelajaran_id);
                            $('#edit-kelas').val(data.kelas_id);
                            $('#edit-mapel').val(data.mapel_id);
                            $('#edit-guru').val(data.gtk_id);
                            $('#edit-hari').val(data.hari);
                            $('#edit-jam-ke').val(data.jam_ke);
                            $('#edit-semester').val(data.semester);
                            $('#edit-waktu-mulai').val(data.waktu_mulai);
                            $('#edit-waktu-selesai').val(data.waktu_selesai);
                            $('#edit-ruangan').val(data.ruangan);
                            $('#edit-aktif').prop('checked', data.is_aktif);
                            $('#modal-edit').modal('show');
                        }
                    }
                });
            });
            
            // Edit form submit
            $('#form-edit').submit(function(e) {
                e.preventDefault();
                var id = $('#edit-id').val();
                $.ajax({
                    url: '{{ route("admin.jadwal-pelajaran.index") }}/' + id,
                    type: 'PUT',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#modal-edit').modal('hide');
                            table.ajax.reload();
                            toastr.success(response.message);
                        }
                    },
                    error: function(xhr) {
                        var errors = xhr.responseJSON?.errors;
                        if (errors) {
                            var msg = Object.values(errors).flat().join('<br>');
                            toastr.error(msg);
                        } else {
                            toastr.error(xhr.responseJSON?.message || 'Terjadi kesalahan');
                        }
                    }
                });
            });

            // Delete handler
            $(document).on('click', '.btn-delete', function() {
                var id = $(this).data('id');
                if (confirm('Apakah Anda yakin ingin menghapus jadwal ini?')) {
                    $.ajax({
                        url: '{{ route("admin.jadwal-pelajaran.index") }}/' + id,
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

            // Copy jadwal handler
            $('#btn-copy-jadwal').click(function() {
                var asalId   = $('#copy-tahun-asal').val();
                var tujuanId = $('#copy-tahun-tujuan').val();
                if (!asalId || !tujuanId) { toastr.warning('Pilih tahun asal dan tujuan.'); return; }
                if (asalId === tujuanId) { toastr.warning('Tahun asal dan tujuan tidak boleh sama.'); return; }
                if (!confirm('Salin jadwal dari tahun asal ke tahun tujuan? Jadwal yang sudah ada di tahun tujuan akan dilewati.')) return;

                var btn = $(this);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyalin...');
                $('#copy-result').html('');

                $.ajax({
                    url: '{{ route("admin.jadwal-pelajaran.copy") }}',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}', tahun_asal_id: asalId, tahun_tujuan_id: tujuanId },
                    success: function(response) {
                        var type = response.success ? 'success' : 'warning';
                        $('#copy-result').html('<div class="alert alert-' + type + '">' + response.message + '</div>');
                        table.ajax.reload();
                        toastr.success(response.message);
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON?.message || 'Terjadi kesalahan.';
                        $('#copy-result').html('<div class="alert alert-danger">' + msg + '</div>');
                        toastr.error(msg);
                    },
                    complete: function() {
                        btn.prop('disabled', false).html('<i class="fas fa-copy mr-1"></i> Salin Jadwal');
                    }
                });
            });
        });
    </script>
@stop

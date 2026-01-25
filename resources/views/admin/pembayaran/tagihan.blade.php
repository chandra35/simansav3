@extends('adminlte::page')

@section('title', 'Tagihan')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-file-invoice mr-2"></i>Tagihan</h1>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-generate">
            <i class="fas fa-magic mr-1"></i> Generate Tagihan
        </button>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-md-4">
                    <select id="filter-jenis" class="form-control">
                        <option value="">-- Semua Jenis --</option>
                        @foreach($jenisPembayaran as $jp)
                            <option value="{{ $jp->id }}">{{ $jp->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <select id="filter-status" class="form-control">
                        <option value="">-- Semua Status --</option>
                        @foreach($status as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <button id="btn-filter" class="btn btn-info btn-block">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table id="tagihan-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="30">No</th>
                        <th>NIS</th>
                        <th>Siswa</th>
                        <th>Jenis</th>
                        <th>Periode</th>
                        <th>Tagihan</th>
                        <th>Terbayar</th>
                        <th>Sisa</th>
                        <th width="100">Status</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    
    <!-- Modal Generate -->
    <div class="modal fade" id="modal-generate" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="form-generate">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Generate Tagihan</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Generate tagihan akan membuat tagihan untuk semua siswa aktif berdasarkan jenis pembayaran yang dipilih.
                        </div>
                        <div class="form-group">
                            <label>Jenis Pembayaran <span class="text-danger">*</span></label>
                            <select name="jenis_pembayaran_id" id="gen-jenis" class="form-control" required>
                                <option value="">-- Pilih Jenis --</option>
                                @foreach($jenisPembayaran as $jp)
                                    <option value="{{ $jp->id }}">{{ $jp->nama }} ({{ $jp->nominal_format }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tahun Pelajaran <span class="text-danger">*</span></label>
                            <select name="tahun_pelajaran_id" id="gen-tahun" class="form-control" required>
                                @foreach(\App\Models\TahunPelajaran::orderBy('tahun_mulai', 'desc')->get() as $tp)
                                    <option value="{{ $tp->id }}" {{ $tp->is_aktif ? 'selected' : '' }}>{{ $tp->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Kelas (Opsional)</label>
                            <select name="kelas_id" id="gen-kelas" class="form-control">
                                <option value="">-- Semua Kelas --</option>
                                @foreach(\App\Models\Kelas::orderBy('nama')->get() as $k)
                                    <option value="{{ $k->id }}">{{ $k->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Bulan <span class="text-danger">*</span></label>
                                    <select name="bulan" id="gen-bulan" class="form-control" required>
                                        @for($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i }}" {{ $i == date('m') ? 'selected' : '' }}>
                                                {{ \App\Models\Tagihan::BULAN[$i] }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tahun <span class="text-danger">*</span></label>
                                    <input type="number" name="tahun" id="gen-tahun-angka" class="form-control" 
                                        value="{{ date('Y') }}" min="2020" max="2100" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Jatuh Tempo <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_jatuh_tempo" id="gen-jatuh-tempo" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Generate</button>
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
            var table = $('#tagihan-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.pembayaran.tagihan') }}",
                    data: function(d) {
                        d.jenis_pembayaran_id = $('#filter-jenis').val();
                        d.status = $('#filter-status').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'siswa_nis', name: 'siswa.nis' },
                    { data: 'siswa_nama', name: 'siswa.nama' },
                    { data: 'jenis', name: 'jenisPembayaran.nama' },
                    { data: 'periode', name: 'bulan' },
                    { data: 'nominal', name: 'nominal_tagihan' },
                    { data: 'terbayar', name: 'nominal_terbayar' },
                    { data: 'sisa', name: 'sisa', orderable: false },
                    { data: 'status_badge', name: 'status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
                }
            });

            $('#btn-filter').click(function() {
                table.ajax.reload();
            });

            // Generate submit
            $('#form-generate').submit(function(e) {
                e.preventDefault();
                var btn = $(this).find('button[type="submit"]');
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Generating...');
                
                $.ajax({
                    url: '{{ route("admin.pembayaran.tagihan.generate") }}',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#modal-generate').modal('hide');
                            table.ajax.reload();
                            toastr.success(response.message);
                        }
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Terjadi kesalahan');
                    },
                    complete: function() {
                        btn.prop('disabled', false).html('Generate');
                    }
                });
            });

            // Delete handler
            $(document).on('click', '.btn-delete', function() {
                var id = $(this).data('id');
                if (confirm('Apakah Anda yakin ingin menghapus tagihan ini?')) {
                    $.ajax({
                        url: '{{ route("admin.pembayaran.tagihan") }}/' + id,
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

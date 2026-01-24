@extends('adminlte::page')

@section('title', 'Detail Kurikulum')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-info-circle"></i> Detail Kurikulum</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.kurikulum.index') }}">Kurikulum</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    {{-- Header Info --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card card-widget widget-user-2">
                <div class="widget-user-header bg-gradient-info">
                    <div class="widget-user-image">
                        <span class="elevation-2 bg-white rounded-circle p-3">
                            <i class="fas fa-book-open fa-2x text-info"></i>
                        </span>
                    </div>
                    <h3 class="widget-user-username">{{ $kurikulum->formatted_name }}</h3>
                    <h5 class="widget-user-desc">Kode: {{ $kurikulum->kode }} | Tahun Berlaku: {{ $kurikulum->tahun_berlaku }}</h5>
                </div>
                <div class="card-footer p-0">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <span class="nav-link">
                                <strong>Status:</strong> 
                                <span class="float-right">{!! $kurikulum->status_badge !!}</span>
                            </span>
                        </li>
                        <li class="nav-item">
                            <span class="nav-link">
                                <strong>Peminatan/Jurusan:</strong>
                                <span class="float-right">
                                    @if($kurikulum->has_jurusan)
                                        <span class="badge badge-success"><i class="fas fa-check"></i> Ya ({{ $stats['total_jurusan'] }} jurusan)</span>
                                    @else
                                        <span class="badge badge-secondary"><i class="fas fa-times"></i> Tidak</span>
                                    @endif
                                </span>
                            </span>
                        </li>
                        @if($kurikulum->deskripsi)
                        <li class="nav-item">
                            <span class="nav-link">
                                <strong>Deskripsi:</strong>
                                <p class="text-muted mt-2">{{ $kurikulum->deskripsi }}</p>
                            </span>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistics --}}
    <div class="row">
        <div class="col-lg-4 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total_jurusan'] }}</h3>
                    <p>Total Jurusan</p>
                </div>
                <div class="icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['total_tahun_pelajaran'] }}</h3>
                    <p>Tahun Pelajaran</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['total_kelas'] }}</h3>
                    <p>Total Kelas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Jurusan List --}}
    @if($kurikulum->has_jurusan)
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list"></i> Daftar Peminatan/Jurusan</h3>
                @can('manage-jurusan')
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modalAddJurusan">
                            <i class="fas fa-plus"></i> Tambah Jurusan
                        </button>
                    </div>
                @endcan
            </div>
            <div class="card-body">
                @if($kurikulum->jurusans->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Kode</th>
                                    <th>Nama Jurusan</th>
                                    <th>Singkatan</th>
                                    <th>Deskripsi</th>
                                    <th width="10%">Status</th>
                                    @can('manage-jurusan')
                                    <th width="15%">Aksi</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($kurikulum->jurusans as $index => $jurusan)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td><span class="badge badge-info">{{ $jurusan->kode_jurusan }}</span></td>
                                        <td>{{ $jurusan->nama_jurusan }}</td>
                                        <td><span class="badge badge-secondary">{{ $jurusan->singkatan }}</span></td>
                                        <td>{{ $jurusan->deskripsi ?? '-' }}</td>
                                        <td>
                                            @if($jurusan->is_active)
                                                <span class="badge badge-success"><i class="fas fa-check"></i> Aktif</span>
                                            @else
                                                <span class="badge badge-secondary">Non-Aktif</span>
                                            @endif
                                        </td>
                                        @can('manage-jurusan')
                                        <td>
                                            <button type="button" class="btn btn-sm btn-warning btn-edit-jurusan" 
                                                data-id="{{ $jurusan->id }}"
                                                data-kode="{{ $jurusan->kode_jurusan }}"
                                                data-nama="{{ $jurusan->nama_jurusan }}"
                                                data-singkatan="{{ $jurusan->singkatan }}"
                                                data-deskripsi="{{ $jurusan->deskripsi }}"
                                                data-urutan="{{ $jurusan->urutan }}"
                                                data-active="{{ $jurusan->is_active }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger btn-delete-jurusan" data-id="{{ $jurusan->id }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                        @endcan
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Belum ada jurusan yang ditambahkan.
                        @can('manage-jurusan')
                            Klik tombol <strong>Tambah Jurusan</strong> untuk menambahkan.
                        @endcan
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Tahun Pelajaran List --}}
    @if($kurikulum->tahunPelajarans->count() > 0)
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-calendar"></i> Tahun Pelajaran Menggunakan Kurikulum Ini</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th width="5%">No</th>
                                <th>Tahun Pelajaran</th>
                                <th>Semester</th>
                                <th>Status</th>
                                <th>Kuota PPDB</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kurikulum->tahunPelajarans->sortByDesc('tahun_mulai') as $index => $tp)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $tp->nama }}</strong>
                                        @if($tp->is_active)
                                            <span class="badge badge-success ml-2"><i class="fas fa-check-circle"></i> Aktif</span>
                                        @endif
                                    </td>
                                    <td>{!! $tp->semester_badge !!}</td>
                                    <td>{!! $tp->status_badge !!}</td>
                                    <td>{{ $tp->kuota_ppdb }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Action Buttons --}}
    <div class="card">
        <div class="card-footer">
            <div class="row">
                <div class="col-md-6">
                    <a href="{{ route('admin.kurikulum.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
                <div class="col-md-6 text-right">
                    @can('edit-kurikulum')
                        <a href="{{ route('admin.kurikulum.edit', $kurikulum->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    @endcan
                    
                    @can('activate-kurikulum')
                        @if($kurikulum->is_active)
                            <button type="button" class="btn btn-warning" onclick="deactivate()">
                                <i class="fas fa-toggle-off"></i> Nonaktifkan
                            </button>
                        @else
                            <button type="button" class="btn btn-success" onclick="activate()">
                                <i class="fas fa-toggle-on"></i> Aktifkan
                            </button>
                        @endif
                    @endcan
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Add Jurusan --}}
    @can('manage-jurusan')
    <div class="modal fade" id="modalAddJurusan" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="formAddJurusan">
                    @csrf
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title"><i class="fas fa-plus"></i> Tambah Jurusan</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="kode_jurusan">Kode Jurusan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="kode_jurusan" name="kode_jurusan" 
                                placeholder="IPA, IPS, AGAMA" required maxlength="20">
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Otomatis akan diubah ke huruf besar
                            </small>
                        </div>
                        <div class="form-group">
                            <label for="nama_jurusan">Nama Jurusan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_jurusan" name="nama_jurusan" 
                                placeholder="Ilmu Pengetahuan Alam" required maxlength="100">
                        </div>
                        <div class="form-group">
                            <label for="singkatan">Singkatan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="singkatan" name="singkatan" 
                                placeholder="IPA" required maxlength="10">
                        </div>
                        <div class="form-group">
                            <label for="urutan">Urutan Tampilan <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="urutan" name="urutan" 
                                value="1" required min="1">
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Urutan jurusan saat ditampilkan
                            </small>
                        </div>
                        <div class="form-group">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" 
                                rows="3" placeholder="Deskripsi singkat jurusan..."></textarea>
                        </div>
                        <div class="form-group">
                            <label for="is_active">Status <span class="text-danger">*</span></label>
                            <select class="form-control" id="is_active" name="is_active" required>
                                <option value="1">Aktif</option>
                                <option value="0">Non-Aktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Edit Jurusan --}}
    <div class="modal fade" id="modalEditJurusan" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="formEditJurusan">
                    @csrf
                    <input type="hidden" id="edit_jurusan_id" name="jurusan_id">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Jurusan</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit_kode_jurusan">Kode Jurusan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_kode_jurusan" name="kode_jurusan" 
                                placeholder="IPA, IPS, AGAMA" required maxlength="20">
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Otomatis akan diubah ke huruf besar
                            </small>
                        </div>
                        <div class="form-group">
                            <label for="edit_nama_jurusan">Nama Jurusan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_nama_jurusan" name="nama_jurusan" 
                                placeholder="Ilmu Pengetahuan Alam" required maxlength="100">
                        </div>
                        <div class="form-group">
                            <label for="edit_singkatan">Singkatan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_singkatan" name="singkatan" 
                                placeholder="IPA" required maxlength="10">
                        </div>
                        <div class="form-group">
                            <label for="edit_urutan">Urutan Tampilan <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="edit_urutan" name="urutan" 
                                required min="1">
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Urutan jurusan saat ditampilkan
                            </small>
                        </div>
                        <div class="form-group">
                            <label for="edit_deskripsi">Deskripsi</label>
                            <textarea class="form-control" id="edit_deskripsi" name="deskripsi" 
                                rows="3" placeholder="Deskripsi singkat jurusan..."></textarea>
                        </div>
                        <div class="form-group">
                            <label for="edit_is_active">Status <span class="text-danger">*</span></label>
                            <select class="form-control" id="edit_is_active" name="is_active" required>
                                <option value="1">Aktif</option>
                                <option value="0">Non-Aktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function activate() {
            Swal.fire({
                title: 'Konfirmasi',
                text: "Aktifkan kurikulum ini?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                confirmButtonText: 'Ya!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('admin.kurikulum.activate', $kurikulum->id) }}",
                        type: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message,
                            }).then(() => location.reload());
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
        }

        function deactivate() {
            Swal.fire({
                title: 'Konfirmasi',
                text: "Nonaktifkan kurikulum ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                confirmButtonText: 'Ya!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('admin.kurikulum.deactivate', $kurikulum->id) }}",
                        type: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message,
                            }).then(() => location.reload());
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
        }

        // Jurusan Management
        $(document).ready(function() {
            // Add Jurusan
            $('#formAddJurusan').on('submit', function(e) {
                e.preventDefault();
                let formData = $(this).serialize();
                
                $.ajax({
                    url: "{{ route('admin.kurikulum.jurusan.store', $kurikulum->id) }}",
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        $('#modalAddJurusan').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                        }).then(() => location.reload());
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON?.errors;
                        if (errors) {
                            let errorMsg = '';
                            Object.keys(errors).forEach(key => {
                                errorMsg += errors[key][0] + '<br>';
                            });
                            Swal.fire({
                                icon: 'error',
                                title: 'Validasi Gagal!',
                                html: errorMsg
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                            });
                        }
                    }
                });
            });

            // Edit Jurusan - Show Modal
            $('.btn-edit-jurusan').on('click', function() {
                let id = $(this).data('id');
                let kode = $(this).data('kode');
                let nama = $(this).data('nama');
                let singkatan = $(this).data('singkatan');
                let deskripsi = $(this).data('deskripsi');
                let urutan = $(this).data('urutan');
                let active = $(this).data('active');

                $('#edit_jurusan_id').val(id);
                $('#edit_kode_jurusan').val(kode);
                $('#edit_nama_jurusan').val(nama);
                $('#edit_singkatan').val(singkatan);
                $('#edit_deskripsi').val(deskripsi);
                $('#edit_urutan').val(urutan);
                $('#edit_is_active').val(active ? 1 : 0);

                $('#modalEditJurusan').modal('show');
            });

            // Update Jurusan
            $('#formEditJurusan').on('submit', function(e) {
                e.preventDefault();
                let jurusanId = $('#edit_jurusan_id').val();
                let formData = $(this).serialize();
                
                $.ajax({
                    url: "{{ route('admin.kurikulum.jurusan.update', ['kurikulum' => $kurikulum->id, 'jurusan' => ':id']) }}".replace(':id', jurusanId),
                    type: 'PUT',
                    data: formData,
                    success: function(response) {
                        $('#modalEditJurusan').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                        }).then(() => location.reload());
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON?.errors;
                        if (errors) {
                            let errorMsg = '';
                            Object.keys(errors).forEach(key => {
                                errorMsg += errors[key][0] + '<br>';
                            });
                            Swal.fire({
                                icon: 'error',
                                title: 'Validasi Gagal!',
                                html: errorMsg
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                            });
                        }
                    }
                });
            });

            // Delete Jurusan
            $('.btn-delete-jurusan').on('click', function() {
                let jurusanId = $(this).data('id');
                
                Swal.fire({
                    title: 'Konfirmasi',
                    text: "Hapus jurusan ini? Data yang terkait akan terpengaruh!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('admin.kurikulum.jurusan.delete', ['kurikulum' => $kurikulum->id, 'jurusan' => ':id']) }}".replace(':id', jurusanId),
                            type: 'DELETE',
                            data: { _token: '{{ csrf_token() }}' },
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: response.message,
                                }).then(() => location.reload());
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

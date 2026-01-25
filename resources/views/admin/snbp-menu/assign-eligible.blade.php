@extends('adminlte::page')

@section('title', 'Assign Siswa Eligible')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-user-check"></i> Assign Siswa Eligible</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.snbp-menu.index') }}">Menu SNBP</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.snbp-menu.show', $snbpMenu) }}">{{ $snbpMenu->nama_menu }}</a></li>
                <li class="breadcrumb-item active">Assign Eligible</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-check"></i> Assign Siswa Eligible - {{ $snbpMenu->nama_menu }}
                    </h3>
                </div>
                <form action="{{ route('admin.snbp-menu.store-eligible', $snbpMenu) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            Masukkan NISN siswa kelas 12 yang <strong>eligible</strong>, satu NISN per baris.
                            <br>Sistem akan mencocokkan dengan data siswa kelas 12 yang ada.
                        </div>

                        <div class="form-group">
                            <label for="nisn_list">Daftar NISN <span class="text-danger">*</span></label>
                            <textarea name="nisn_list" id="nisn_list" class="form-control @error('nisn_list') is-invalid @enderror" 
                                      rows="12" placeholder="Masukkan NISN, satu per baris&#10;Contoh:&#10;1234567890&#10;0987654321&#10;1122334455">{{ old('nisn_list') }}</textarea>
                            @error('nisn_list')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                <i class="fas fa-lightbulb"></i> Anda dapat copy-paste langsung dari Excel atau file text.
                            </small>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="clear_existing" name="clear_existing" value="1">
                                <label class="custom-control-label" for="clear_existing">
                                    Hapus semua siswa eligible yang sudah ada terlebih dahulu
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                Jika dicentang, semua siswa eligible sebelumnya akan dihapus dan diganti dengan daftar baru.
                            </small>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check"></i> Simpan Eligible
                        </button>
                        <a href="{{ route('admin.snbp-menu.show', $snbpMenu) }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Current Eligible List -->
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-users"></i> Siswa Eligible Saat Ini ({{ $snbpMenu->eligibleSiswa->count() }})
                    </h3>
                </div>
                <div class="card-body p-0">
                    @if($snbpMenu->eligibleSiswa->count() > 0)
                    <div style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm table-striped mb-0">
                            <thead class="bg-light" style="position: sticky; top: 0;">
                                <tr>
                                    <th>NISN</th>
                                    <th>Nama</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($snbpMenu->eligibleSiswa as $siswa)
                                <tr>
                                    <td><code>{{ $siswa->nisn }}</code></td>
                                    <td>{{ Str::limit($siswa->nama_lengkap, 20) }}</td>
                                    <td>
                                        <button type="button" class="btn btn-xs btn-danger btn-remove" 
                                                data-id="{{ $siswa->pivot->id }}" data-nama="{{ $siswa->nama_lengkap }}"
                                                title="Hapus">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p>Belum ada siswa eligible</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Available Kelas 12 Stats -->
            <div class="card">
                <div class="card-header bg-info">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie"></i> Statistik Kelas 12
                    </h3>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li><strong>Total Siswa Kelas 12:</strong> {{ $totalKelas12 }}</li>
                        <li><strong>Sudah Eligible:</strong> <span class="text-success">{{ $snbpMenu->eligibleSiswa->count() }}</span></li>
                        <li><strong>Tidak Eligible:</strong> <span class="text-danger">{{ $snbpMenu->notEligibleSiswa->count() }}</span></li>
                        <li><strong>Belum Diassign:</strong> <span class="text-warning">{{ $totalKelas12 - $snbpMenu->eligibleSiswa->count() - $snbpMenu->notEligibleSiswa->count() }}</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Remove individual siswa
    $('.btn-remove').on('click', function() {
        var id = $(this).data('id');
        var nama = $(this).data('nama');
        var row = $(this).closest('tr');

        Swal.fire({
            title: 'Hapus dari Eligible?',
            text: 'Siswa "' + nama + '" akan dihapus dari daftar eligible.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ url("admin/snbp-menu") }}/' + id + '/remove-assignment',
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if(response.success) {
                            row.fadeOut(300, function() {
                                $(this).remove();
                            });
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: 'Terjadi kesalahan saat menghapus data.'
                        });
                    }
                });
            }
        });
    });
});
</script>
@stop

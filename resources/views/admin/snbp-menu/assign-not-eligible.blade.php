@extends('adminlte::page')

@section('title', 'Assign Siswa Tidak Eligible')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-user-times"></i> Assign Siswa Tidak Eligible</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.snbp-menu.index') }}">Menu SNBP</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.snbp-menu.show', $snbpMenu) }}">{{ $snbpMenu->nama_menu }}</a></li>
                <li class="breadcrumb-item active">Assign Tidak Eligible</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="card card-danger">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-user-times"></i> Assign Siswa Tidak Eligible - {{ $snbpMenu->nama_menu }}
            </h3>
        </div>
        <form action="{{ route('admin.snbp-menu.store-not-eligible', $snbpMenu) }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    Pilih siswa kelas 12 yang <strong>tidak eligible</strong> untuk {{ $snbpMenu->nama_menu }}.
                    <br>Siswa yang sudah ditandai eligible tidak akan muncul di daftar ini.
                </div>

                @if($availableSiswa->count() > 0)
                <div class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" id="searchSiswa" class="form-control" placeholder="Cari nama atau NISN...">
                    </div>
                    <div class="col-md-4">
                        <select id="filterKelas" class="form-control">
                            <option value="">-- Semua Kelas --</option>
                            @foreach($kelasList as $kelas)
                                <option value="{{ $kelas->nama_kelas }}">{{ $kelas->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 text-right">
                        <button type="button" id="selectAll" class="btn btn-outline-danger">
                            <i class="fas fa-check-double"></i> Pilih Semua
                        </button>
                        <button type="button" id="deselectAll" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Batal Pilih
                        </button>
                    </div>
                </div>

                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-bordered table-striped table-hover" id="tableSiswa">
                        <thead class="bg-light" style="position: sticky; top: 0; z-index: 1;">
                            <tr>
                                <th style="width: 50px;">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="checkAll">
                                        <label class="custom-control-label" for="checkAll"></label>
                                    </div>
                                </th>
                                <th>No</th>
                                <th>NISN</th>
                                <th>Nama Lengkap</th>
                                <th>Kelas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($availableSiswa as $index => $siswa)
                            <tr data-kelas="{{ $siswa->kelasSaatIni->nama_kelas ?? '' }}">
                                <td>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input siswa-checkbox" 
                                               id="siswa_{{ $siswa->id }}" name="siswa_ids[]" value="{{ $siswa->id }}">
                                        <label class="custom-control-label" for="siswa_{{ $siswa->id }}"></label>
                                    </div>
                                </td>
                                <td>{{ $index + 1 }}</td>
                                <td><code>{{ $siswa->nisn }}</code></td>
                                <td>{{ $siswa->nama_lengkap }}</td>
                                <td>{{ $siswa->kelasSaatIni->nama_kelas ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <strong>Total Siswa Dipilih: <span id="selectedCount" class="badge badge-danger">0</span></strong>
                </div>
                @else
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                    <h5>Semua siswa kelas 12 sudah diassign!</h5>
                    <p>Tidak ada siswa yang tersedia untuk ditandai tidak eligible.</p>
                </div>
                @endif
            </div>
            <div class="card-footer">
                @if($availableSiswa->count() > 0)
                <button type="submit" class="btn btn-danger" id="btnSubmit" disabled>
                    <i class="fas fa-save"></i> Simpan Tidak Eligible
                </button>
                @endif
                <a href="{{ route('admin.snbp-menu.show', $snbpMenu) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </form>
    </div>

    <!-- Current Not Eligible List -->
    @if($snbpMenu->notEligibleSiswa->count() > 0)
    <div class="card card-outline card-danger">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-users"></i> Siswa Tidak Eligible Saat Ini ({{ $snbpMenu->notEligibleSiswa->count() }})
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                <table class="table table-sm table-striped mb-0">
                    <thead class="bg-light" style="position: sticky; top: 0;">
                        <tr>
                            <th>#</th>
                            <th>NISN</th>
                            <th>Nama</th>
                            <th>Kelas</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($snbpMenu->notEligibleSiswa as $index => $siswa)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><code>{{ $siswa->nisn }}</code></td>
                            <td>{{ $siswa->nama_lengkap }}</td>
                            <td>{{ $siswa->kelasSaatIni->nama_kelas ?? '-' }}</td>
                            <td>
                                <button type="button" class="btn btn-xs btn-warning btn-remove" 
                                        data-id="{{ $siswa->pivot->id }}" data-nama="{{ $siswa->nama_lengkap }}"
                                        title="Hapus dari Tidak Eligible">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Update selected count
    function updateSelectedCount() {
        var count = $('.siswa-checkbox:checked').length;
        $('#selectedCount').text(count);
        $('#btnSubmit').prop('disabled', count === 0);
    }

    // Checkbox change
    $('.siswa-checkbox').on('change', function() {
        updateSelectedCount();
    });

    // Check all in current view
    $('#checkAll').on('change', function() {
        var isChecked = $(this).is(':checked');
        $('#tableSiswa tbody tr:visible .siswa-checkbox').prop('checked', isChecked);
        updateSelectedCount();
    });

    // Select All button (all visible)
    $('#selectAll').on('click', function() {
        $('#tableSiswa tbody tr:visible .siswa-checkbox').prop('checked', true);
        updateSelectedCount();
    });

    // Deselect All button
    $('#deselectAll').on('click', function() {
        $('.siswa-checkbox').prop('checked', false);
        $('#checkAll').prop('checked', false);
        updateSelectedCount();
    });

    // Search filter
    $('#searchSiswa').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        filterTable();
    });

    // Kelas filter
    $('#filterKelas').on('change', function() {
        filterTable();
    });

    function filterTable() {
        var searchValue = $('#searchSiswa').val().toLowerCase();
        var kelasValue = $('#filterKelas').val();

        $('#tableSiswa tbody tr').each(function() {
            var text = $(this).text().toLowerCase();
            var kelas = $(this).data('kelas');
            
            var matchSearch = searchValue === '' || text.indexOf(searchValue) > -1;
            var matchKelas = kelasValue === '' || kelas === kelasValue;

            $(this).toggle(matchSearch && matchKelas);
        });
    }

    // Remove from not eligible
    $('.btn-remove').on('click', function() {
        var id = $(this).data('id');
        var nama = $(this).data('nama');
        var row = $(this).closest('tr');

        Swal.fire({
            title: 'Hapus dari Tidak Eligible?',
            text: 'Siswa "' + nama + '" akan dihapus dari daftar tidak eligible.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
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
                                // Reload page to refresh available list
                                location.reload();
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

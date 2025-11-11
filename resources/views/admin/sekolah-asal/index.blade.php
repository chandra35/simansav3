@extends('adminlte::page')

@section('title', 'Data Sekolah Asal')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-school"></i> Data Sekolah Asal</h1>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-list"></i> Daftar Sekolah Asal Siswa</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tableSekolah" class="table table-bordered table-striped table-hover table-sm">
                    <thead class="bg-light">
                        <tr>
                            <th width="50" class="text-center">No</th>
                            <th width="90">NPSN</th>
                            <th>Nama Sekolah</th>
                            <th width="90" class="text-center">Status</th>
                            <th width="130">Bentuk Pendidikan</th>
                            <th width="150">Kab/Kota</th>
                            <th width="80" class="text-center">Siswa</th>
                            <th width="80" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .badge-pill {
            font-size: 0.85rem;
            padding: 0.35rem 0.75rem;
        }
        
        #tableSekolah {
            font-size: 0.875rem;
        }
        
        #tableSekolah th {
            vertical-align: middle;
            font-weight: 600;
        }
        
        #tableSekolah td {
            vertical-align: middle;
        }
        
        #tableSekolah .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }
    </style>
@stop

@section('js')
<script>
$(document).ready(function() {
    $('#tableSekolah').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('admin.sekolah-asal.index') }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'npsn', name: 'npsn' },
            { data: 'nama', name: 'nama' },
            { data: 'status_badge', name: 'status', orderable: false },
            { data: 'bentuk_pendidikan', name: 'bentuk_pendidikan', defaultContent: '-' },
            { data: 'kabupaten_kota', name: 'kabupaten_kota', defaultContent: '-' },
            { data: 'siswa_count_badge', name: 'siswa_count', searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[6, 'desc']], // Sort by jumlah siswa descending
        language: {
            processing: "Sedang memproses...",
            lengthMenu: "Tampilkan _MENU_ data",
            zeroRecords: "Tidak ditemukan data yang sesuai",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            infoFiltered: "(disaring dari _MAX_ total data)",
            search: "Cari:",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            }
        },
        responsive: true,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
        pageLength: 25
    });
});
</script>
@stop

@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)

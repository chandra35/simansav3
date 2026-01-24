@extends('adminlte::page')

@section('title', 'Kelola Pendaftaran PPDB')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-user-graduate mr-2"></i>Kelola Pendaftaran PPDB</h1>
    </div>
@stop

@section('content')
<div class="row">
    <!-- Status Cards -->
    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $statusCounts['all'] }}</h3>
                <p>Total</p>
            </div>
            <div class="icon"><i class="fas fa-users"></i></div>
            <a href="#" class="small-box-footer filter-link" data-status="all">Lihat semua <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>{{ $statusCounts['draft'] }}</h3>
                <p>Draft</p>
            </div>
            <div class="icon"><i class="fas fa-edit"></i></div>
            <a href="#" class="small-box-footer filter-link" data-status="draft">Filter <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $statusCounts['submitted'] }}</h3>
                <p>Menunggu</p>
            </div>
            <div class="icon"><i class="fas fa-clock"></i></div>
            <a href="#" class="small-box-footer filter-link" data-status="submitted">Filter <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $statusCounts['verified'] }}</h3>
                <p>Terverifikasi</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <a href="#" class="small-box-footer filter-link" data-status="verified">Filter <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $statusCounts['accepted'] }}</h3>
                <p>Diterima</p>
            </div>
            <div class="icon"><i class="fas fa-check-double"></i></div>
            <a href="#" class="small-box-footer filter-link" data-status="accepted">Filter <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $statusCounts['rejected'] }}</h3>
                <p>Ditolak</p>
            </div>
            <div class="icon"><i class="fas fa-times-circle"></i></div>
            <a href="#" class="small-box-footer filter-link" data-status="rejected">Filter <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Pendaftaran</h3>
        <div class="card-tools">
            <div class="btn-group">
                <select class="form-control form-control-sm" id="filterJalur" style="width: 150px;">
                    <option value="">Semua Jalur</option>
                    <option value="reguler">Reguler</option>
                    <option value="prestasi">Prestasi</option>
                    <option value="afirmasi">Afirmasi</option>
                    <option value="zonasi">Zonasi</option>
                </select>
            </div>
            <div class="btn-group ml-2">
                <select class="form-control form-control-sm" id="filterJurusan" style="width: 200px;">
                    <option value="">Semua Jurusan</option>
                    @foreach($jurusan as $j)
                        <option value="{{ $j->id }}">{{ $j->nama }}</option>
                    @endforeach
                </select>
            </div>
            <a href="{{ route('operator.pendaftar.export') }}" class="btn btn-sm btn-success ml-2">
                <i class="fas fa-file-excel"></i> Export
            </a>
        </div>
    </div>
    <div class="card-body">
        <table id="pendaftaranTable" class="table table-bordered table-striped table-sm">
            <thead>
                <tr>
                    <th>No. Pendaftaran</th>
                    <th>NISN</th>
                    <th>Nama</th>
                    <th>Asal Sekolah</th>
                    <th>Jalur</th>
                    <th>Jurusan</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                    <th width="100">Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@stop

@section('css')
<style>
    .small-box .inner h3 {
        font-size: 2rem;
    }
    .small-box .inner p {
        font-size: 0.9rem;
    }
</style>
@stop

@section('js')
<script>
$(function() {
    var currentStatus = 'all';
    
    var table = $('#pendaftaranTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("operator.pendaftar.data") }}',
            data: function(d) {
                d.status = currentStatus;
                d.jalur = $('#filterJalur').val();
                d.jurusan = $('#filterJurusan').val();
            }
        },
        columns: [
            { data: 'nomor_pendaftaran' },
            { data: 'nisn' },
            { data: 'nama_lengkap' },
            { data: 'asal_sekolah' },
            { data: 'jalur' },
            { data: 'jurusan' },
            { 
                data: 'status',
                render: function(data, type, row) {
                    return '<span class="badge badge-' + row.status_badge + '">' + row.status_label + '</span>';
                }
            },
            { data: 'tanggal' },
            {
                data: 'id',
                orderable: false,
                render: function(data) {
                    return '<a href="/admin/ppdb/pendaftaran/' + data + '" class="btn btn-xs btn-info" title="Detail"><i class="fas fa-eye"></i></a>';
                }
            }
        ],
        order: [[7, 'desc']],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
        }
    });

    // Filter by status
    $('.filter-link').on('click', function(e) {
        e.preventDefault();
        currentStatus = $(this).data('status');
        $('.filter-link').closest('.small-box').removeClass('elevation-3');
        $(this).closest('.small-box').addClass('elevation-3');
        table.ajax.reload();
    });

    // Filter by jalur & jurusan
    $('#filterJalur, #filterJurusan').on('change', function() {
        table.ajax.reload();
    });
});
</script>
@stop

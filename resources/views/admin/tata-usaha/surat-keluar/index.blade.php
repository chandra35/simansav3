@extends('adminlte::page')

@section('title', 'Surat Keluar')

@section('content_header')
<div class="surat-page-header d-flex flex-column flex-md-row align-items-md-center justify-content-between">
    <div class="mb-3 mb-md-0">
        <h1 class="m-0 text-dark"><i class="fas fa-paper-plane text-primary mr-2"></i>Surat Keluar</h1>
        <small class="text-muted">Kelola draft, surat menunggu tanda tangan, dan surat yang telah diterbitkan.</small>
    </div>
    <a href="{{ route('admin.tata-usaha.surat-keluar.create') }}" class="btn btn-primary align-self-start align-self-md-center">
        <i class="fas fa-plus mr-1"></i> Buat Surat Keluar
    </a>
</div>
@stop

@section('plugins.Datatables', true)

@section('content')
<div class="card card-outline card-primary shadow-sm surat-keluar-index">
    <div class="card-header bg-white border-0 py-3">
        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between">
            <div>
                <h2 class="h6 font-weight-bold mb-1">Daftar Surat Keluar</h2>
                <p class="small text-muted mb-0">Gunakan pencarian atau filter tabel untuk menemukan surat.</p>
            </div>
            <span class="badge badge-light border mt-2 mt-sm-0"><i class="fas fa-shield-alt text-primary mr-1"></i>Nomor surat tercatat di Buku Nomor Surat</span>
        </div>
    </div>
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped w-100 mb-0" id="suratKeluarTable">
                <thead class="thead-light">
                    <tr>
                        <th class="text-center" width="45">No.</th>
                        <th width="170">Nomor Surat</th>
                        <th>Perihal</th>
                        <th>Tujuan</th>
                        <th width="105">Tanggal</th>
                        <th>Template</th>
                        <th>Penandatangan</th>
                        <th class="text-center" width="110">Status</th>
                        <th class="text-center" width="125">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .surat-page-header { gap: 1rem; }
    .surat-keluar-index .dataTables_wrapper { padding-top: .5rem; }
    .surat-keluar-index .dataTables_filter input { max-width: 220px; }
    .surat-keluar-index td { vertical-align: middle; }
    @media (max-width: 575.98px) { .surat-keluar-index .dataTables_filter, .surat-keluar-index .dataTables_length { text-align: left; margin-top: .75rem; } }
</style>
@stop

@section('js')
<script>
    $(function() {
        $('#suratKeluarTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.tata-usaha.surat-keluar.index') }}",
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'nomor', name: 'nomor_surat'},
                {data: 'perihal', name: 'perihal'},
                {data: 'tujuan', name: 'tujuan'},
                {data: 'tanggal', name: 'tanggal_surat'},
                {data: 'template', name: 'template'},
                {data: 'penandatangan', name: 'nama_penandatangan'},
                {data: 'status_badge', name: 'status'},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ],
            language: {
                url: '/vendor/datatables/locales/id.json'
            },
            pageLength: 25
        });

        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            if (confirm('Yakin ingin menghapus surat ini?')) {
                $.ajax({
                    url: `/admin/tata-usaha/surat-keluar/${id}`,
                    type: 'DELETE',
                    data: {_token: '{{ csrf_token() }}'},
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            $('#suratKeluarTable').DataTable().ajax.reload();
                        }
                    }
                });
            }
        });
    });
</script>
@stop

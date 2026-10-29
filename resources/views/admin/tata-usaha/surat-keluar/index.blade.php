@extends('adminlte::page')

@section('title', 'Surat Keluar')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0"><i class="fas fa-arrow-right"></i> Surat Keluar</h1>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('admin.tata-usaha.surat-keluar.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Buat Surat Keluar
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-striped table-hover" id="suratKeluarTable">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nomor Surat</th>
                        <th>Perihal</th>
                        <th>Tujuan</th>
                        <th>Tanggal</th>
                        <th>Template</th>
                        <th>Penandatangan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

@push('scripts')
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
@endpush
@endsection

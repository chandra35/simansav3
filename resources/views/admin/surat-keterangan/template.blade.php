@extends('adminlte::page')

@section('title', 'Template Surat')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-file-alt mr-2"></i>Template Surat</h1>
        @can('manage-layanan-surat')<a href="{{ route('admin.surat-keterangan.template.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Tambah Template
        </a>@endcan
    </div>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-body p-0">
            @if($templates->isEmpty())
                <div class="text-center py-5">
                    <i class="far fa-folder-open fa-2x text-muted mb-3"></i>
                    <p class="mb-3">Belum ada template surat.</p>
                    @can('manage-layanan-surat')
                        <a href="{{ route('admin.surat-keterangan.template.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Tambah Template</a>
                    @endcan
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th width="60">No</th>
                                <th>Kode</th>
                                <th>Nama Template</th>
                                <th>Jenis</th>
                                <th>Status</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($templates as $template)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td><code>{{ $template->kode }}</code></td>
                                    <td class="font-weight-bold">{{ $template->nama }}</td>
                                    <td>{{ $template->jenis_label }}</td>
                                    <td>
                                        <span class="badge badge-{{ $template->is_active ? 'success' : 'secondary' }}">
                                            {{ $template->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        @can('manage-layanan-surat')
                                            <a href="{{ route('admin.surat-keterangan.template.edit', $template) }}" class="btn btn-sm btn-outline-warning" title="Edit template"><i class="fas fa-edit"></i></a>
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-url="{{ route('admin.surat-keterangan.template.destroy', $template) }}" title="Hapus template"><i class="fas fa-trash"></i></button>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@stop

@section('js')
<script>
    $(document).on('click', '.btn-delete', function () {
        if (!confirm('Apakah Anda yakin ingin menghapus template ini?')) {
            return;
        }

        $.ajax({
            url: $(this).data('url'),
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function (response) {
                if (response.success) {
                    window.location.reload();
                }
            }
        });
    });
</script>
@stop

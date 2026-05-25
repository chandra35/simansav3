@extends('adminlte::page')

@section('title', 'Tambah File Download')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-plus"></i> Tambah File Download</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('admin.downloads.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="card simansa-management-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-upload"></i> Form Upload</h3>
        </div>
        <div class="card-body">
            <form id="createDownloadForm" action="{{ route('admin.downloads.store') }}" method="POST" enctype="multipart/form-data">
                @include('admin.downloads.form')
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                    <a href="{{ route('admin.downloads.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function () {
    $('#createDownloadForm').on('submit', function (event) {
        if ($(this).data('confirmed')) {
            return;
        }

        event.preventDefault();

        Swal.fire({
            title: 'Simpan File Download?',
            text: 'Data file akan ditambahkan ke Download Center.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Simpan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $(this).data('confirmed', true);
                this.submit();
            }
        });
    });
});
</script>
@endsection

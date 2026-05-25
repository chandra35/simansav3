@extends('adminlte::page')

@section('title', 'Edit File Download')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-edit"></i> Edit File Download</h1>
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
            <h3 class="card-title"><i class="fas fa-file"></i> {{ $download->title }}</h3>
        </div>
        <div class="card-body">
            <div class="alert alert-light border">
                <strong>File Saat Ini:</strong> {{ $download->file_name_original }}
                <span class="badge badge-secondary">{{ strtoupper($download->file_extension ?: 'FILE') }}</span>
                <span class="badge badge-info">{{ $download->formatted_size }}</span>
            </div>

            <form action="{{ route('admin.downloads.update', $download) }}" method="POST" enctype="multipart/form-data">
                @method('PUT')
                @include('admin.downloads.form')
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
                    <a href="{{ route('admin.downloads.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@extends('adminlte::page')

@section('title', 'Buat Menu SPAN-PTKIN')

@section('content_header')
    <h1><i class="fas fa-plus-circle"></i> Buat Menu SPAN-PTKIN</h1>
@stop

@section('content')
<div class="container-fluid">
    @if($existingMenu)
        <div class="alert alert-warning">
            Tahun pelajaran aktif sudah memiliki menu SPAN-PTKIN.
            <a href="{{ route('admin.span-ptkin-menu.edit', $existingMenu) }}">Edit menu tersebut</a>.
        </div>
    @endif

    <form action="{{ route('admin.span-ptkin-menu.store') }}" method="POST">
        @include('admin.span-ptkin._form')
    </form>
</div>
@stop

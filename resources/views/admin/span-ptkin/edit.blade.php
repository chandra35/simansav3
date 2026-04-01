@extends('adminlte::page')

@section('title', 'Edit Menu SPAN-PTKIN')

@section('content_header')
    <h1><i class="fas fa-edit"></i> Edit Menu SPAN-PTKIN</h1>
@stop

@section('content')
<div class="container-fluid">
    <form action="{{ route('admin.span-ptkin-menu.update', $spanPtkinMenu) }}" method="POST">
        @php($method = 'PUT')
        @include('admin.span-ptkin._form')
    </form>
</div>
@stop

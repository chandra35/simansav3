@extends('adminlte::page')

@section('title', 'Edit Role - ' . $role->name)

@section('css')
<style>
    .permission-group {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        margin-bottom: 1rem;
    }
    .permission-group-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 10px 15px;
        border-radius: 8px 8px 0 0;
        font-weight: 600;
        text-transform: capitalize;
    }
    .permission-group-body {
        padding: 15px;
    }
    .permission-item {
        padding: 8px 12px;
        border-radius: 6px;
        margin-bottom: 5px;
        transition: background 0.2s;
    }
    .permission-item:hover {
        background-color: #f8f9fa;
    }
    .permission-item .custom-control-label {
        cursor: pointer;
    }
    .check-all-btn {
        font-size: 0.75rem;
    }
    .current-permissions {
        background-color: #d4edda;
    }
</style>
@stop

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-edit text-warning"></i> Edit Role: {{ $role->name }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Roles</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('admin.roles.update', $role) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-info-circle"></i> Informasi Role</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Nama Role <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $role->name) }}" placeholder="Contoh: Kepala TU, Staff Keuangan" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="text-muted">Nama role harus unik dan menggambarkan fungsi role</small>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-key"></i> Permissions</h3>
                        <div class="card-tools">
                            <span class="badge badge-success mr-2">
                                <i class="fas fa-check"></i> {{ count($rolePermissions) }} permission aktif
                            </span>
                            <button type="button" class="btn btn-success btn-sm" onclick="checkAll()">
                                <i class="fas fa-check-double"></i> Pilih Semua
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="uncheckAll()">
                                <i class="fas fa-times"></i> Hapus Semua
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($permissions as $group => $groupPermissions)
                            <div class="col-md-6 col-lg-4">
                                <div class="permission-group">
                                    <div class="permission-group-header d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-folder"></i> {{ ucfirst($group) }}</span>
                                        <button type="button" class="btn btn-sm btn-light check-all-btn" onclick="checkGroup('{{ $group }}')">
                                            <i class="fas fa-check"></i> All
                                        </button>
                                    </div>
                                    <div class="permission-group-body">
                                        @foreach($groupPermissions as $permission)
                                        @php $isChecked = in_array($permission->name, old('permissions', $rolePermissions)); @endphp
                                        <div class="permission-item {{ $isChecked ? 'current-permissions' : '' }}">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input permission-checkbox permission-{{ $group }}" 
                                                       id="perm_{{ $permission->id }}" name="permissions[]" value="{{ $permission->name }}"
                                                       {{ $isChecked ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="perm_{{ $permission->id }}">
                                                    {{ $permission->name }}
                                                </label>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save"></i> Update Role
                        </button>
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
<script>
    function checkAll() {
        $('.permission-checkbox').prop('checked', true);
        updateHighlight();
    }
    
    function uncheckAll() {
        $('.permission-checkbox').prop('checked', false);
        updateHighlight();
    }
    
    function checkGroup(group) {
        var checkboxes = $('.permission-' + group);
        var allChecked = checkboxes.length === checkboxes.filter(':checked').length;
        checkboxes.prop('checked', !allChecked);
        updateHighlight();
    }
    
    function updateHighlight() {
        $('.permission-item').each(function() {
            var checkbox = $(this).find('.permission-checkbox');
            if (checkbox.is(':checked')) {
                $(this).addClass('current-permissions');
            } else {
                $(this).removeClass('current-permissions');
            }
        });
    }
    
    $(function() {
        $('.permission-checkbox').on('change', updateHighlight);
    });
</script>
@stop

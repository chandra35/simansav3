@extends('adminlte::page')

@section('title', 'Detail Role - ' . $role->name)

@section('css')
@include('admin.partials.action-buttons-style')
<style>
    .role-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 30px;
        margin-bottom: 20px;
    }
    .role-header h2 {
        margin-bottom: 10px;
    }
    .stat-card {
        text-align: center;
        padding: 15px;
        border-radius: 8px;
        background: rgba(255,255,255,0.2);
    }
    .stat-card h3 {
        margin-bottom: 5px;
        font-size: 2rem;
    }
    .permission-badge {
        margin: 3px;
        font-size: 0.85rem;
    }
    .permission-group-card {
        border-left: 4px solid #667eea;
    }
    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }
    .user-avatar-placeholder {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
    }
</style>
@stop

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-user-tag text-primary"></i> Detail Role</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Roles</a></li>
                <li class="breadcrumb-item active">{{ $role->name }}</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    <!-- Role Header -->
    <div class="role-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2><i class="fas fa-user-tag"></i> {{ $role->name }}</h2>
                @php
                    $systemRoles = ['Super Admin', 'Siswa', 'GTK', 'Admin', 'Kepala Madrasah', 'WAKA', 'Operator', 'BK', 'Wali Kelas', 'Bendahara'];
                    $isSystem = in_array($role->name, $systemRoles);
                @endphp
                @if($isSystem)
                    <span class="badge badge-light"><i class="fas fa-lock"></i> System Role</span>
                @else
                    <span class="badge badge-warning"><i class="fas fa-user-edit"></i> Custom Role</span>
                @endif
                <p class="mb-0 mt-2 text-white-50">Guard: {{ $role->guard_name }}</p>
            </div>
            <div class="col-md-4">
                <div class="row">
                    <div class="col-6">
                        <div class="stat-card">
                            <h3>{{ $role->users->count() }}</h3>
                            <span>Users</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card">
                            <h3>{{ $role->permissions->count() }}</h3>
                            <span>Permissions</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Permissions Section -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-key"></i> Permissions ({{ $role->permissions->count() }})</h3>
                    @if(!$isSystem || $role->name != 'Super Admin')
                        <div class="card-tools">
                            <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit Permissions
                            </a>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    @if($groupedPermissions->count() > 0)
                        @foreach($groupedPermissions as $group => $perms)
                        <div class="card permission-group-card mb-3">
                            <div class="card-header py-2">
                                <strong><i class="fas fa-folder text-primary"></i> {{ ucfirst($group) }}</strong>
                                <span class="badge badge-primary float-right">{{ $perms->count() }}</span>
                            </div>
                            <div class="card-body py-2">
                                @foreach($perms as $permission)
                                    <span class="badge badge-light permission-badge">
                                        <i class="fas fa-check text-success"></i> {{ $permission->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-key fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Role ini belum memiliki permission</p>
                            @if(!$isSystem || $role->name != 'Super Admin')
                                <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Tambah Permission
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Users Section -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-users"></i> Users dengan Role Ini ({{ $role->users->count() }})</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addUserModal">
                            <i class="fas fa-plus"></i> Tambah User
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($role->users->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Email</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($role->users as $user)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($user->avatar)
                                                    <img src="{{ asset('storage/' . $user->avatar) }}" class="user-avatar mr-2" alt="{{ $user->name }}">
                                                @else
                                                    <div class="user-avatar-placeholder mr-2">
                                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                                    </div>
                                                @endif
                                                <div>
                                                    <strong>{{ $user->name }}</strong>
                                                    <br><small class="text-muted">{{ $user->username }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            <form action="{{ route('admin.roles.remove-user', $role) }}" method="POST" 
                                                  onsubmit="return confirm('Hapus user ini dari role {{ $role->name }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" data-toggle="tooltip" title="Hapus dari role">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Belum ada user dengan role ini</p>
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addUserModal">
                                <i class="fas fa-plus"></i> Tambah User Pertama
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar Role
        </a>
        @if(!$isSystem || $role->name != 'Super Admin')
            <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit Role
            </a>
        @endif
    </div>

    <!-- Modal Add User -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.roles.assign-user', $role) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-user-plus"></i> Tambah User ke Role {{ $role->name }}</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="user_id">Pilih User</label>
                            <select name="user_id" id="user_id" class="form-control select2" required>
                                <option value="">-- Pilih User --</option>
                                @php
                                    $existingUserIds = $role->users->pluck('id')->toArray();
                                    $availableUsers = \App\Models\User::whereNotIn('id', $existingUserIds)
                                        ->where('is_active', true)
                                        ->orderBy('name')
                                        ->get();
                                @endphp
                                @foreach($availableUsers as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->username }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Tambahkan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
        
        if ($.fn.select2) {
            $('.select2').select2({
                theme: 'bootstrap4',
                dropdownParent: $('#addUserModal')
            });
        }
    });
</script>
@stop

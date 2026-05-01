@extends('adminlte::page')

@section('title', 'Detail Role - ' . $role->name)

@php
    $systemRoles = ['Super Admin', 'Siswa', 'GTK', 'Admin', 'Kepala Madrasah', 'WAKA', 'Operator', 'BK', 'Wali Kelas', 'Bendahara'];
    $isSystem = in_array($role->name, $systemRoles);
@endphp

@section('content_header')
    <div class="simansa-hero">
        <div class="simansa-hero__main">
            <div class="simansa-hero__eyebrow"><i class="fas fa-user-tag"></i> Users & Role</div>
            <h1 class="simansa-hero__title">Detail Role</h1>
            <p class="simansa-hero__subtitle">Lihat paket permission dan user yang memakai role ini dalam satu tampilan yang lebih ringkas dan mudah dipindai.</p>
        </div>
        <div class="simansa-hero__side">
            <div class="simansa-hero-chip">
                <span class="simansa-hero-chip__label">Role</span>
                <span class="simansa-hero-chip__value">{{ $role->name }}</span>
            </div>
            <div class="simansa-hero-chip">
                <span class="simansa-hero-chip__label">Tipe</span>
                <span class="simansa-hero-chip__value">{{ $isSystem ? 'System' : 'Custom' }}</span>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="simansa-mini-stat">
                <span class="simansa-mini-stat__label">Total Users</span>
                <span class="simansa-mini-stat__value">{{ $role->users->count() }}</span>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="simansa-mini-stat">
                <span class="simansa-mini-stat__label">Total Permissions</span>
                <span class="simansa-mini-stat__value">{{ $role->permissions->count() }}</span>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="simansa-mini-stat">
                <span class="simansa-mini-stat__label">Guard</span>
                <span class="simansa-mini-stat__value">{{ $role->guard_name }}</span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6 mb-4">
            <div class="card simansa-management-card h-100">
                <div class="card-header">
                    <div class="simansa-toolbar">
                        <h3 class="card-title mb-0"><i class="fas fa-key mr-2"></i> Permissions</h3>
                        @if(!$isSystem || $role->name != 'Super Admin')
                            <a href="{{ route('admin.roles.edit', $role) }}" class="btn simansa-btn-contrast">
                                <i class="fas fa-edit mr-1"></i> Edit Role
                            </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if(count($permissionCatalog) > 0)
                        <div class="simansa-check-grid">
                            @foreach($permissionCatalog as $module)
                                <div class="simansa-check-card">
                                    <div class="font-weight-bold text-dark mb-2">
                                        <i class="fas fa-{{ $module['icon'] }} mr-1 text-{{ $module['color'] }}"></i>{{ $module['label'] }}
                                    </div>
                                    @if(!empty($module['description']))
                                        <div class="simansa-filter-hint mb-2">{{ $module['description'] }}</div>
                                    @endif
                                    @foreach($module['items'] as $permission)
                                        <div class="small text-muted mb-1"><i class="fas fa-check text-success mr-1"></i>{{ $permission['label'] }}</div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="simansa-empty-state">
                            <i class="fas fa-key"></i>
                            <div class="font-weight-bold text-dark mb-1">Role ini belum punya permission</div>
                            <div>Tambah permission dulu agar role bisa dipakai dengan efektif.</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-6 mb-4">
            <div class="card simansa-management-card h-100">
                <div class="card-header">
                    <div class="simansa-toolbar">
                        <h3 class="card-title mb-0"><i class="fas fa-users mr-2"></i> User dengan Role Ini</h3>
                        <button type="button" class="btn simansa-btn-contrast" data-toggle="modal" data-target="#addUserModal">
                            <i class="fas fa-plus mr-1"></i> Tambah User
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if($role->users->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>User</th>
                                        <th>Email</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($role->users as $user)
                                        <tr>
                                            <td>
                                                <strong>{{ $user->name }}</strong>
                                                <div class="text-muted small">{{ $user->username }}</div>
                                            </td>
                                            <td>{{ $user->email }}</td>
                                            <td class="text-center">
                                                <form action="{{ route('admin.roles.remove-user', $role) }}" method="POST" onsubmit="return confirm('Hapus user ini dari role {{ $role->name }}?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
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
                        <div class="simansa-empty-state">
                            <i class="fas fa-users"></i>
                            <div class="font-weight-bold text-dark mb-1">Belum ada user di role ini</div>
                            <div>Tambahkan user jika role ini sudah siap dipakai operasional.</div>
                        </div>
                    @endif
                </div>
                <div class="card-footer">
                    <div class="simansa-toolbar">
                        <a href="{{ route('admin.roles.index') }}" class="btn simansa-btn-muted">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar Role
                        </a>
                        @if(!$isSystem || $role->name != 'Super Admin')
                            <a href="{{ route('admin.roles.edit', $role) }}" class="btn simansa-btn-strong">
                                <i class="fas fa-edit mr-1"></i> Edit Role
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.roles.assign-user', $role) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-user-plus mr-2"></i> Tambah User ke Role {{ $role->name }}</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-0">
                            <label for="user_id" class="simansa-filter-label"><i class="fas fa-users"></i> Pilih User</label>
                            <select name="user_id" id="user_id" class="form-control" required>
                                <option value="">-- Pilih User --</option>
                                @foreach($availableUsers as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} - {{ $user->email }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn simansa-btn-muted" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn simansa-btn-strong">Tambah User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

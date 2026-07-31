@extends('adminlte::page')

@section('title', 'Permission Management')

@section('css')
<style>
    .permission-group-card {
        border-left: 4px solid;
        transition: all 0.3s ease;
    }
    .permission-group-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .color-1 { border-left-color: #007bff; }
    .color-2 { border-left-color: #28a745; }
    .color-3 { border-left-color: #dc3545; }
    .color-4 { border-left-color: #ffc107; }
    .color-5 { border-left-color: #17a2b8; }
    .color-6 { border-left-color: #6f42c1; }
    .color-7 { border-left-color: #fd7e14; }
    .color-8 { border-left-color: #20c997; }
    .permission-badge {
        font-size: 0.85rem;
        margin: 3px;
        padding: 6px 12px;
    }
</style>
@stop

@section('content_header')
    <div class="simansa-hero">
        <div class="simansa-hero__main">
            <p class="simansa-hero__eyebrow"><i class="fas fa-shield-alt"></i> Manajemen Sistem</p>
            <h1 class="simansa-hero__title">Permission Management</h1>
            <p class="simansa-hero__subtitle">Kelola daftar permission dan kategori hak akses sistem.</p>
        </div>
        <div class="simansa-hero__side">
            <div class="simansa-hero-chip">
                <span class="simansa-hero-chip__label">Total Permission</span>
                <span class="simansa-hero-chip__value">{{ $permissions->count() }}</span>
            </div>
            <div class="simansa-hero-chip">
                <span class="simansa-hero-chip__label">Kategori</span>
                <span class="simansa-hero-chip__value">{{ $groupedPermissions->count() }}</span>
            </div>
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

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="simansa-stat-card simansa-stat-card--teal">
                <div class="simansa-stat-card__icon"><i class="fas fa-key"></i></div>
                <div class="simansa-stat-card__body">
                    <div class="simansa-stat-card__value">{{ $permissions->count() }}</div>
                    <div class="simansa-stat-card__label">Total Permission</div>
                    <div class="simansa-stat-card__desc">Permission terdaftar</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="simansa-stat-card simansa-stat-card--cyan">
                <div class="simansa-stat-card__icon"><i class="fas fa-folder"></i></div>
                <div class="simansa-stat-card__body">
                    <div class="simansa-stat-card__value">{{ $groupedPermissions->count() }}</div>
                    <div class="simansa-stat-card__label">Kategori</div>
                    <div class="simansa-stat-card__desc">Grup permission</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="simansa-stat-card simansa-stat-card--blue">
                <div class="simansa-stat-card__icon"><i class="fas fa-user-tag"></i></div>
                <div class="simansa-stat-card__body">
                    <div class="simansa-stat-card__value">{{ \Spatie\Permission\Models\Role::count() }}</div>
                    <div class="simansa-stat-card__label">Total Role</div>
                    <div class="simansa-stat-card__desc">Role aktif di sistem</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Permission List -->
    <div class="card simansa-management-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-list"></i> Daftar Permission</h3>
            <div class="card-tools">
                <a href="{{ route('admin.roles.index') }}" class="btn btn-info btn-sm mr-2">
                    <i class="fas fa-user-tag"></i> Kelola Roles
                </a>
                <button type="button" class="btn btn-success btn-sm mr-2" data-toggle="modal" data-target="#bulkCreateModal">
                    <i class="fas fa-layer-group"></i> Bulk Create
                </button>
                <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Tambah Permission
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                @php $colorIndex = 0; @endphp
                @foreach($groupedPermissions as $group => $perms)
                @php $colorIndex++; if($colorIndex > 8) $colorIndex = 1; @endphp
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card permission-group-card color-{{ $colorIndex }} h-100">
                        <div class="card-header py-2 d-flex justify-content-between align-items-center">
                            <strong><i class="fas fa-folder"></i> {{ ucfirst($group) }}</strong>
                            <span class="badge badge-secondary">{{ $perms->count() }}</span>
                        </div>
                        <div class="card-body py-2">
                            @foreach($perms as $permission)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="permission-badge badge badge-secondary text-white">
                                    <i class="fas fa-key"></i> {{ $permission->name }}
                                </span>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.permissions.show', $permission) }}" class="btn btn-outline-info btn-sm" 
                                       data-toggle="tooltip" title="Lihat roles dengan permission ini">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form action="{{ route('admin.permissions.destroy', $permission) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Hapus permission {{ $permission->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" 
                                                data-toggle="tooltip" title="Hapus permission">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
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

    <!-- Bulk Create Modal -->
    <div class="modal fade" id="bulkCreateModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('admin.permissions.bulk-create') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="fas fa-layer-group"></i> Bulk Create Permissions</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Masukkan satu permission per baris. Permission yang sudah ada akan dilewati.
                        </div>
                        <div class="form-group">
                            <label for="permissions">Permissions (satu per baris)</label>
                            <textarea name="permissions" id="permissions" class="form-control" rows="10" 
                                      placeholder="view-reports&#10;create-reports&#10;export-reports&#10;manage-notifications"></textarea>
                        </div>
                        <div class="alert alert-secondary">
                            <strong>Format yang disarankan:</strong>
                            <ul class="mb-0 mt-2">
                                <li><code>kategori-aksi</code> (contoh: view-siswa, create-kelas)</li>
                                <li>Gunakan huruf kecil dan tanda hubung (-)</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Buat Permissions</button>
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
    });
</script>
@stop

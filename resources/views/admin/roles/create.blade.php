@extends('adminlte::page')

@section('title', 'Tambah Role')

@section('content_header')
    <div class="simansa-hero">
        <div class="simansa-hero__main">
            <div class="simansa-hero__eyebrow"><i class="fas fa-user-tag"></i> Users & Role</div>
            <h1 class="simansa-hero__title">Tambah Role</h1>
            <p class="simansa-hero__subtitle">Buat role baru, lalu pilih permission yang benar-benar dibutuhkan agar pengelolaan akses tetap rapi dan mudah diaudit.</p>
        </div>
        <div class="simansa-hero__side">
            <div class="simansa-hero-chip">
                <span class="simansa-hero-chip__label">Grup Permission</span>
                <span class="simansa-hero-chip__value">{{ count($permissionCatalog) }}</span>
            </div>
        </div>
    </div>
@stop

@section('content')
    <form action="{{ route('admin.roles.store') }}" method="POST" class="simansa-form-shell">
        @csrf

        <div class="card simansa-management-card simansa-form-card">
            <div class="card-header">
                <div class="simansa-toolbar">
                    <h3 class="card-title mb-0"><i class="fas fa-info-circle mr-2"></i> Informasi Role</h3>
                    <div class="simansa-toolbar__group">
                        <a href="{{ route('admin.roles.index') }}" class="btn simansa-btn-muted">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="form-group mb-0">
                            <label for="name" class="simansa-filter-label"><i class="fas fa-fingerprint"></i> Nama Role <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="Contoh: Koordinator BK, Staff Keuangan" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <div class="simansa-filter-hint">Gunakan nama yang jelas dan mudah dikenali operator saat assign role ke user.</div>
                        </div>
                    </div>
                    <div class="col-lg-4 mt-3 mt-lg-0">
                        <div class="simansa-mini-stat h-100">
                            <span class="simansa-mini-stat__label">Prinsip</span>
                            <span class="simansa-mini-stat__value">Least Privilege</span>
                            <div class="simansa-filter-hint">Mulai dari akses minimum, lalu tambah bila benar-benar diperlukan.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card simansa-management-card simansa-form-card">
            <div class="card-header">
                <div class="simansa-toolbar">
                    <div>
                        <h3 class="card-title mb-0"><i class="fas fa-key mr-2"></i> Permission Role</h3>
                    </div>
                    <div class="simansa-toolbar__group">
                        <button type="button" class="btn simansa-btn-contrast" onclick="checkAll()">
                            <i class="fas fa-check-double mr-1"></i> Pilih Semua
                        </button>
                        <button type="button" class="btn simansa-btn-muted" onclick="uncheckAll()">
                            <i class="fas fa-eraser mr-1"></i> Kosongkan
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="simansa-section-note mb-4">
                    <i class="fas fa-lightbulb mr-1"></i> Permission sekarang dikelompokkan per fitur sekolah. Jadi Anda bisa langsung menyusun akses untuk modul seperti Data Siswa, GTK, Kelas, atau Tahun Pelajaran.
                </div>
                <div class="simansa-check-grid">
                    @foreach($permissionCatalog as $module)
                        <div class="simansa-check-card">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <div class="font-weight-bold text-dark">
                                        <i class="fas fa-{{ $module['icon'] }} mr-1 text-{{ $module['color'] }}"></i>{{ $module['label'] }}
                                    </div>
                                    <small class="text-muted">{{ count($module['items']) }} permission</small>
                                </div>
                                <button type="button" class="btn btn-xs simansa-btn-contrast" onclick="checkGroup('{{ $module['key'] }}')">
                                    <i class="fas fa-check mr-1"></i> Toggle
                                </button>
                            </div>
                            @if(!empty($module['description']))
                                <div class="simansa-filter-hint mb-2">{{ $module['description'] }}</div>
                            @endif
                            @foreach($module['items'] as $permission)
                                <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" class="custom-control-input permission-checkbox permission-{{ $module['key'] }}"
                                           id="perm_{{ md5($permission['name']) }}" name="permissions[]" value="{{ $permission['name'] }}"
                                           {{ in_array($permission['name'], old('permissions', [])) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="perm_{{ md5($permission['name']) }}">
                                        {{ $permission['label'] }}
                                    </label>
                                    <div class="simansa-check-card__meta">{{ $permission['name'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="card-footer">
                <div class="simansa-toolbar">
                    <div class="text-muted small">Pastikan role baru ini hanya membawa akses yang memang diperlukan.</div>
                    <div class="simansa-toolbar__group">
                        <a href="{{ route('admin.roles.index') }}" class="btn simansa-btn-muted">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                        <button type="submit" class="btn simansa-btn-strong">
                            <i class="fas fa-save mr-1"></i> Simpan Role
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@stop

@section('js')
<script>
    function checkAll() {
        $('.permission-checkbox').prop('checked', true);
    }

    function uncheckAll() {
        $('.permission-checkbox').prop('checked', false);
    }

    function checkGroup(group) {
        const checkboxes = $('.permission-' + group);
        const allChecked = checkboxes.length === checkboxes.filter(':checked').length;
        checkboxes.prop('checked', !allChecked);
    }
</script>
@stop

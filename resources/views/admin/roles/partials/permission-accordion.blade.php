<div class="accordion simansa-role-permission-accordion" id="{{ $accordionId }}">
    @foreach($permissionCatalog as $module)
        @php
            $collapseId = $accordionId.'-'.str($module['key'])->slug();
            $headingId = $collapseId.'-heading';
            $selectedCount = collect($module['items'])->whereIn('name', $selectedPermissions)->count();
        @endphp
        <div class="card simansa-role-permission-module">
            <div class="card-header p-0" id="{{ $headingId }}">
                <div class="simansa-role-permission-module__header">
                    <button class="btn btn-link simansa-role-permission-module__trigger" type="button"
                            data-toggle="collapse" data-target="#{{ $collapseId }}"
                            aria-expanded="false" aria-controls="{{ $collapseId }}">
                        <span class="simansa-role-permission-module__icon text-{{ $module['color'] }}">
                            <i class="fas fa-{{ $module['icon'] }}" aria-hidden="true"></i>
                        </span>
                        <span class="simansa-role-permission-module__title">
                            <strong>{{ $module['label'] }}</strong>
                            <small>{{ $module['description'] ?: count($module['items']).' permission tersedia' }}</small>
                        </span>
                        <span class="simansa-role-permission-module__count" data-permission-group-count="{{ $module['key'] }}">
                            {{ $selectedCount }}/{{ count($module['items']) }} aktif
                        </span>
                        <i class="fas fa-chevron-down simansa-role-permission-module__chevron" aria-hidden="true"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary simansa-role-permission-module__toggle"
                            data-permission-group="{{ $module['key'] }}" title="Pilih atau kosongkan semua permission {{ $module['label'] }}">
                        <i class="fas fa-check mr-1" aria-hidden="true"></i>Toggle
                    </button>
                </div>
            </div>
            <div id="{{ $collapseId }}" class="collapse" aria-labelledby="{{ $headingId }}" data-parent="#{{ $accordionId }}">
                <div class="card-body simansa-role-permission-module__body">
                    @foreach($module['items'] as $permission)
                        @php $isChecked = in_array($permission['name'], $selectedPermissions); @endphp
                        <div class="custom-control custom-checkbox simansa-role-permission-row {{ $isChecked ? 'is-checked' : '' }}">
                            <input type="checkbox" class="custom-control-input permission-checkbox permission-{{ $module['key'] }}"
                                   id="perm_{{ md5($permission['name']) }}" name="permissions[]" value="{{ $permission['name'] }}"
                                   {{ $isChecked ? 'checked' : '' }}>
                            <label class="custom-control-label" for="perm_{{ md5($permission['name']) }}">
                                <span>{{ $permission['label'] }}</span>
                                <small>{{ $permission['name'] }}</small>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>

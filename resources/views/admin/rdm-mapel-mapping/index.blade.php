@extends('adminlte::page')

@section('title', 'Mapping Mapel RDM')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-exchange-alt"></i> Mapping Mapel RDM</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.rdm-sync.index') }}">Integrasi RDM</a></li>
                <li class="breadcrumb-item active">Mapping Mapel</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total_rdm'] }}</h3>
                    <p>Mapel RDM</p>
                </div>
                <div class="icon"><i class="fas fa-database"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['mapped'] }}</h3>
                    <p>Sudah Dipetakan</p>
                </div>
                <div class="icon"><i class="fas fa-link"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['unmapped'] }}</h3>
                    <p>Belum Dipetakan</p>
                </div>
                <div class="icon"><i class="fas fa-unlink"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $stats['simansa_total'] }}</h3>
                    <p>Mapel SIMANSA</p>
                </div>
                <div class="icon"><i class="fas fa-book"></i></div>
            </div>
        </div>
    </div>

    {{-- Progress Bar --}}
    @php
        $pct = $stats['total_rdm'] > 0 ? round(($stats['mapped'] / $stats['total_rdm']) * 100) : 0;
    @endphp
    <div class="card card-outline card-primary mb-4">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="font-weight-bold"><i class="fas fa-tasks"></i> Progress Mapping</span>
                <span class="badge badge-{{ $pct === 100 ? 'success' : ($pct >= 50 ? 'info' : 'warning') }}">{{ $pct }}%</span>
            </div>
            <div class="progress" style="height: 8px;">
                <div class="progress-bar bg-{{ $pct === 100 ? 'success' : ($pct >= 50 ? 'info' : 'warning') }}" style="width: {{ $pct }}%"></div>
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="row mb-3">
        <div class="col-12 d-flex flex-wrap gap-2">
            @if($stats['unmapped'] > 0 && count($suggestions) > 0)
                <form method="POST" action="{{ route('admin.rdm-mapel-mapping.auto-map') }}" class="mr-2" id="autoMapForm">
                    @csrf
                    <button type="button" class="btn btn-info" id="btnAutoMap">
                        <i class="fas fa-magic"></i> Auto-Map ({{ count($suggestions) }} cocok)
                    </button>
                </form>
            @endif
            <button type="button" class="btn btn-primary mr-2" data-toggle="modal" data-target="#bulkMapModal" {{ $stats['unmapped'] == 0 ? 'disabled' : '' }}>
                <i class="fas fa-layer-group"></i> Bulk Mapping
            </button>
            <div class="ml-auto">
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-secondary btn-sm active" id="btnAll">Semua</button>
                    <button type="button" class="btn btn-outline-success btn-sm" id="btnMapped">Sudah Dipetakan</button>
                    <button type="button" class="btn btn-outline-warning btn-sm" id="btnUnmapped">Belum Dipetakan</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Table --}}
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-table"></i> Daftar Mapel RDM &harr; SIMANSA</h3>
            <div class="card-tools">
                <div class="input-group input-group-sm" style="width: 250px;">
                    <input type="text" id="searchInput" class="form-control" placeholder="Cari mapel...">
                    <div class="input-group-append">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0" id="mappingTable">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 50px;" class="text-center">#</th>
                            <th style="width: 80px;">ID RDM</th>
                            <th>Mapel RDM</th>
                            <th style="width: 90px;">Kurikulum</th>
                            <th style="width: 50px;" class="text-center"><i class="fas fa-arrows-alt-h"></i></th>
                            <th>Mapel SIMANSA</th>
                            <th style="width: 100px;">Kelompok</th>
                            <th style="width: 130px;">Dipetakan Oleh</th>
                            <th style="width: 130px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rdmMapels as $idx => $rdm)
                            @php
                                $mapping = $mappings->get($rdm->mapel_id);
                                $suggestion = $suggestions[$rdm->mapel_id] ?? null;
                                $isMapped = $mapping !== null;
                            @endphp
                            <tr class="mapping-row {{ $isMapped ? 'row-mapped' : 'row-unmapped' }}" data-search="{{ strtolower($rdm->mapel_nama . ' ' . ($mapping?->mataPelajaran?->nama_mapel ?? '') . ' ' . ($rdm->kurikulum_nama ?? '')) }}">
                                <td class="text-center text-muted">{{ $idx + 1 }}</td>
                                <td><code>{{ $rdm->mapel_id }}</code></td>
                                <td>
                                    <strong>{{ $rdm->mapel_nama }}</strong>
                                    @if($suggestion && !$isMapped)
                                        <br><small class="text-success"><i class="fas fa-lightbulb"></i> Saran: {{ $suggestion['simansa_nama'] }}
                                            @if(($suggestion['confidence'] ?? '') === 'exact')
                                                <span class="badge badge-success badge-sm">kurikulum match</span>
                                            @elseif(($suggestion['confidence'] ?? '') === 'name_only')
                                                <span class="badge badge-warning badge-sm">nama saja</span>
                                            @endif
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    @if(($rdm->kurikulum_id ?? 0) == 2)
                                        <span class="badge badge-info">Merdeka</span>
                                    @elseif(($rdm->kurikulum_id ?? 0) == 1)
                                        <span class="badge badge-secondary">K13</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($isMapped)
                                        <span class="text-success"><i class="fas fa-link fa-lg"></i></span>
                                    @else
                                        <span class="text-muted"><i class="fas fa-unlink"></i></span>
                                    @endif
                                </td>
                                <td>
                                    @if($isMapped)
                                        <span class="badge badge-success">{{ $mapping->mataPelajaran?->nama_mapel ?? '?' }}</span>
                                    @else
                                        <span class="text-muted font-italic">Belum dipetakan</span>
                                    @endif
                                </td>
                                <td>
                                    @if($isMapped)
                                        <span class="badge badge-light">{{ $mapping->mataPelajaran?->kelompok ?? '-' }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($isMapped)
                                        <small class="text-muted">{{ $mapping->mappedByUser?->name ?? '-' }}</small>
                                        <br><small class="text-muted">{{ $mapping->updated_at?->format('d/m H:i') }}</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($isMapped)
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary btn-edit"
                                                data-rdm-id="{{ $rdm->mapel_id }}"
                                                data-rdm-nama="{{ $rdm->mapel_nama }}"
                                                data-rdm-kurikulum="{{ $rdm->kurikulum_id ?? '' }}"
                                                data-simansa-id="{{ $mapping->mata_pelajaran_id }}"
                                                title="Ubah mapping">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" action="{{ route('admin.rdm-mapel-mapping.destroy', $mapping) }}" class="d-inline form-delete">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-outline-danger btn-delete" title="Hapus mapping" data-nama="{{ $rdm->mapel_nama }}">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <button type="button" class="btn btn-sm btn-success btn-map"
                                            data-rdm-id="{{ $rdm->mapel_id }}"
                                            data-rdm-nama="{{ $rdm->mapel_nama }}"
                                            data-rdm-kurikulum="{{ $rdm->kurikulum_id ?? '' }}"
                                            data-suggestion-id="{{ $suggestion['simansa_id'] ?? '' }}">
                                            <i class="fas fa-plus"></i> Map
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer text-muted small">
            <i class="fas fa-info-circle"></i> Mapel RDM bertipe "Kelompok Mata Pelajaran" disembunyikan karena bukan mapel yang dinilai.
        </div>
    </div>

    {{-- Single Map Modal --}}
    <div class="modal fade" id="mapModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('admin.rdm-mapel-mapping.store') }}">
                @csrf
                <input type="hidden" name="rdm_mapel_id" id="modalRdmId">
                <input type="hidden" name="rdm_mapel_nama" id="modalRdmNama">
                <input type="hidden" name="rdm_kurikulum_id" id="modalRdmKurikulum">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="fas fa-exchange-alt"></i> Petakan Mapel</h5>
                        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-light border mb-3">
                            <strong>Mapel RDM:</strong>
                            <span id="modalRdmLabel" class="font-weight-bold text-primary"></span>
                        </div>
                        <div class="form-group">
                            <label>Pilih Mapel SIMANSA <span class="text-danger">*</span></label>
                            <select name="mata_pelajaran_id" id="modalSimansaSelect" class="form-control select2-modal" required style="width: 100%;">
                                <option value="">-- Pilih --</option>
                                @foreach($simansaMapels as $mp)
                                    <option value="{{ $mp->id }}" data-kelompok="{{ $mp->kelompok }}">
                                        {{ $mp->nama_mapel }} {{ $mp->kelompok ? '('.$mp->kelompok.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Bulk Map Modal --}}
    <div class="modal fade" id="bulkMapModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="{{ route('admin.rdm-mapel-mapping.bulk-store') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="fas fa-layer-group"></i> Bulk Mapping Mapel</h5>
                        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        <div class="alert alert-info py-2">
                            <i class="fas fa-info-circle"></i> Pilih mapel SIMANSA untuk setiap mapel RDM yang ingin dipetakan. Kosongkan jika tidak ingin memetakan.
                        </div>
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 40px;"><input type="checkbox" id="bulkCheckAll"></th>
                                    <th>Mapel RDM</th>
                                    <th style="width: 90px;">Kurikulum</th>
                                    <th>Mapel SIMANSA</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rdmMapels as $rdm)
                                    @if(!$mappings->has($rdm->mapel_id))
                                        @php $suggestion = $suggestions[$rdm->mapel_id] ?? null; @endphp
                                        <tr class="bulk-row">
                                            <td class="text-center">
                                                <input type="checkbox" class="bulk-check" data-idx="{{ $rdm->mapel_id }}">
                                            </td>
                                            <td>
                                                <strong>{{ $rdm->mapel_nama }}</strong>
                                                <input type="hidden" class="bulk-rdm-id" value="{{ $rdm->mapel_id }}" disabled>
                                                <input type="hidden" class="bulk-rdm-nama" value="{{ $rdm->mapel_nama }}" disabled>
                                                <input type="hidden" class="bulk-rdm-kurikulum" value="{{ $rdm->kurikulum_id ?? '' }}" disabled>
                                            </td>
                                            <td>
                                                @if(($rdm->kurikulum_id ?? 0) == 2)
                                                    <span class="badge badge-info">Merdeka</span>
                                                @elseif(($rdm->kurikulum_id ?? 0) == 1)
                                                    <span class="badge badge-secondary">K13</span>
                                                @endif
                                            </td>
                                            <td>
                                                <select class="form-control form-control-sm bulk-simansa-select" disabled>
                                                    <option value="">-- Lewati --</option>
                                                    @foreach($simansaMapels as $mp)
                                                        <option value="{{ $mp->id }}" {{ ($suggestion && $suggestion['simansa_id'] === $mp->id) ? 'selected' : '' }}>
                                                            {{ $mp->nama_mapel }} {{ $mp->kelompok ? '('.$mp->kelompok.')' : '' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @if($suggestion)
                                                    <small class="text-success"><i class="fas fa-lightbulb"></i> Saran otomatis</small>
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <span class="mr-auto text-muted" id="bulkSelectedCount">0 dipilih</span>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="bulkSubmitBtn" disabled>
                            <i class="fas fa-save"></i> Simpan Mapping Terpilih
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .row-mapped { background-color: rgba(40, 167, 69, 0.04) !important; }
    .row-unmapped { background-color: rgba(255, 193, 7, 0.04) !important; }
    .table td, .table th { vertical-align: middle !important; }
    .small-box .inner h3 { font-size: 2rem; }
    .small-box .inner p { font-size: 0.9rem; }
    .btn-group-sm > .btn { padding: .2rem .45rem; }
    .gap-2 { gap: 0.5rem; }
    .select2-container--default .select2-selection--single { height: 38px !important; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 38px !important; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 38px !important; }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function() {
    // Search filter
    $('#searchInput').on('keyup', function() {
        var val = $(this).val().toLowerCase();
        $('#mappingTable tbody .mapping-row').each(function() {
            var text = $(this).data('search');
            $(this).toggle(text.indexOf(val) > -1);
        });
    });

    // Filter buttons
    $('#btnAll').on('click', function() {
        $('.mapping-row').show();
        $('.btn-group .btn').removeClass('active');
        $(this).addClass('active');
    });
    $('#btnMapped').on('click', function() {
        $('.row-mapped').show();
        $('.row-unmapped').hide();
        $('.btn-group .btn').removeClass('active');
        $(this).addClass('active');
    });
    $('#btnUnmapped').on('click', function() {
        $('.row-unmapped').show();
        $('.row-mapped').hide();
        $('.btn-group .btn').removeClass('active');
        $(this).addClass('active');
    });

    // Single map button
    $(document).on('click', '.btn-map, .btn-edit', function() {
        var rdmId = $(this).data('rdm-id');
        var rdmNama = $(this).data('rdm-nama');
        var rdmKurikulum = $(this).data('rdm-kurikulum') || '';
        var simansaId = $(this).data('simansa-id') || $(this).data('suggestion-id') || '';

        $('#modalRdmId').val(rdmId);
        $('#modalRdmNama').val(rdmNama);
        $('#modalRdmKurikulum').val(rdmKurikulum);
        $('#modalRdmLabel').text(rdmNama + (rdmKurikulum == 2 ? ' (Merdeka)' : rdmKurikulum == 1 ? ' (K13)' : ''));

        if ($('.select2-modal').data('select2')) {
            $('.select2-modal').val(simansaId).trigger('change');
        } else {
            $('#modalSimansaSelect').val(simansaId);
        }

        $('#mapModal').modal('show');
    });

    // Initialize select2 on modal shown
    $('#mapModal').on('shown.bs.modal', function() {
        if ($.fn.select2) {
            $('.select2-modal').select2({
                dropdownParent: $('#mapModal'),
                placeholder: '-- Pilih Mapel SIMANSA --',
                allowClear: true,
                width: '100%'
            });
        }
    });

    // Bulk checkbox logic
    $('#bulkCheckAll').on('change', function() {
        var checked = $(this).prop('checked');
        $('.bulk-check').prop('checked', checked).trigger('change');
    });

    $(document).on('change', '.bulk-check', function() {
        var $row = $(this).closest('.bulk-row');
        var checked = $(this).prop('checked');
        $row.find('.bulk-simansa-select, .bulk-rdm-id, .bulk-rdm-nama, .bulk-rdm-kurikulum').prop('disabled', !checked);

        if (checked) {
            var idx = $(this).data('idx');
            $row.find('.bulk-rdm-id').attr('name', 'mappings[' + idx + '][rdm_mapel_id]');
            $row.find('.bulk-rdm-nama').attr('name', 'mappings[' + idx + '][rdm_mapel_nama]');
            $row.find('.bulk-rdm-kurikulum').attr('name', 'mappings[' + idx + '][rdm_kurikulum_id]');
            $row.find('.bulk-simansa-select').attr('name', 'mappings[' + idx + '][mata_pelajaran_id]');
        } else {
            $row.find('.bulk-rdm-id, .bulk-rdm-nama, .bulk-rdm-kurikulum, .bulk-simansa-select').removeAttr('name');
        }

        var count = $('.bulk-check:checked').length;
        $('#bulkSelectedCount').text(count + ' dipilih');
        $('#bulkSubmitBtn').prop('disabled', count === 0);
    });

    // Bulk form validation
    $('#bulkMapModal form').on('submit', function(e) {
        var valid = true;
        $('.bulk-check:checked').each(function() {
            var $row = $(this).closest('.bulk-row');
            if (!$row.find('.bulk-simansa-select').val()) {
                $row.find('.bulk-simansa-select').addClass('is-invalid');
                valid = false;
            } else {
                $row.find('.bulk-simansa-select').removeClass('is-invalid');
            }
        });
        if (!valid) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Data Belum Lengkap',
                text: 'Pilih mapel SIMANSA untuk semua baris yang dicentang, atau hapus centang baris yang tidak ingin dipetakan.',
                confirmButtonColor: '#3085d6',
            });
        }
    });

    // Auto-map confirmation
    $('#btnAutoMap').on('click', function() {
        Swal.fire({
            title: 'Auto-Map Mapel?',
            html: '<p>Auto-map akan mencocokkan <strong>{{ count($suggestions) }}</strong> mapel yang namanya identik secara otomatis berdasarkan kurikulum.</p><p class="text-muted small mb-0">Mapping yang sudah ada tidak akan ditimpa.</p>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#17a2b8',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-magic"></i> Ya, Lanjutkan',
            cancelButtonText: 'Batal',
            reverseButtons: true,
        }).then(function(result) {
            if (result.isConfirmed) {
                $('#autoMapForm').submit();
            }
        });
    });

    // Delete mapping confirmation
    $(document).on('click', '.btn-delete', function() {
        var $form = $(this).closest('.form-delete');
        var nama = $(this).data('nama');
        Swal.fire({
            title: 'Hapus Mapping?',
            html: 'Mapping untuk mapel <strong>"' + nama + '"</strong> akan dihapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash-alt"></i> Ya, Hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true,
        }).then(function(result) {
            if (result.isConfirmed) {
                $form.submit();
            }
        });
    });
});
</script>
@stop

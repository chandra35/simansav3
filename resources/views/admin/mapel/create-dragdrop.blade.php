@extends('adminlte::page')

@section('title', 'Tambah Mata Pelajaran - Drag & Drop')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-plus-circle"></i> Tambah Mata Pelajaran (Drag & Drop)</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.mapel.index') }}">Mata Pelajaran</a></li>
                <li class="breadcrumb-item active">Tambah</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> 
        <strong>Petunjuk:</strong> 
        <ol class="mb-0 mt-2">
            <li>Pilih kurikulum untuk memuat template mapel</li>
            <li>Drag mapel dari template ke kelompok yang sesuai (A, B, C, dll)</li>
            <li>Atur KKM untuk setiap mapel (default: 75)</li>
            <li>Pilih tingkat dan semester yang akan diterapkan</li>
            <li>Klik Simpan untuk menyimpan semua mapel</li>
        </ol>
    </div>

    <form action="{{ route('admin.mapel.bulk-store') }}" method="POST" id="bulkMapelForm">
        @csrf

        <!-- Konfigurasi Dasar -->
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-cog"></i> Konfigurasi Dasar</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="kurikulum_id">Kurikulum <span class="text-danger">*</span></label>
                            <select name="kurikulum_id" id="kurikulum_id" class="form-control" required>
                                <option value="">-- Pilih Kurikulum --</option>
                                @foreach($kurikulums as $k)
                                    <option value="{{ $k->id }}" data-kode="{{ $k->kode }}">{{ $k->nama_kurikulum }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tahun_pelajaran_id">Tahun Pelajaran <span class="text-danger">*</span></label>
                            <select name="tahun_pelajaran_id" id="tahun_pelajaran_id" class="form-control bg-light" required readonly disabled>
                                @foreach($tahunPelajarans as $tp)
                                    @if($tp->is_active)
                                        <option value="{{ $tp->id }}" selected>
                                            {{ $tp->nama_tahun_pelajaran }} (Aktif)
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            {{-- Hidden input untuk submit karena disabled select tidak terkirim --}}
                            @foreach($tahunPelajarans as $tp)
                                @if($tp->is_active)
                                    <input type="hidden" name="tahun_pelajaran_id_actual" value="{{ $tp->id }}">
                                @endif
                            @endforeach
                            <small class="text-muted">Mapel akan dibuat untuk tahun pelajaran aktif</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="jurusan_id">Jurusan (Opsional)</label>
                            <select name="jurusan_id" id="jurusan_id" class="form-control">
                                <option value="">-- Semua Jurusan --</option>
                                @foreach($jurusans as $j)
                                    <option value="{{ $j->id }}">{{ $j->nama_jurusan }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Semester <span class="text-danger">*</span></label>
                            <div class="mt-2">
                                <div class="custom-control custom-checkbox custom-control-inline">
                                    <input type="checkbox" class="custom-control-input" id="sem1" name="semester[]" value="1">
                                    <label class="custom-control-label" for="sem1">Ganjil</label>
                                </div>
                                <div class="custom-control custom-checkbox custom-control-inline">
                                    <input type="checkbox" class="custom-control-input" id="sem2" name="semester[]" value="2">
                                    <label class="custom-control-label" for="sem2">Genap</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Terapkan untuk Tingkat <span class="text-danger">*</span></label>
                            <div class="row">
                                @for($i = 1; $i <= 12; $i++)
                                    <div class="col-md-2 col-sm-3 col-4">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="tingkat{{ $i }}" name="tingkat[]" value="{{ $i }}">
                                            <label class="custom-control-label" for="tingkat{{ $i }}">Tingkat {{ $i }}</label>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Drag & Drop Area -->
        <div class="row" id="dragDropArea" style="display: none;">
            <!-- Kolom Kiri: Template Mapel -->
            <div class="col-md-5">
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-list"></i> Template Mata Pelajaran</h3>
                        <div class="card-tools">
                            <span class="badge badge-light" id="templateCount">0 mapel</span>
                        </div>
                    </div>
                    <div class="card-body p-2" style="max-height: 600px; overflow-y: auto; background-color: #f8f9fa;">
                        <div id="templateMapelList"></div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Mapel Terpilih dengan Kelompok -->
            <div class="col-md-7">
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-check-circle"></i> Mapel Terpilih - Drag ke Kelompok</h3>
                        <div class="card-tools">
                            <span class="badge badge-light" id="selectedCount">0 mapel dipilih</span>
                        </div>
                    </div>
                    <div class="card-body p-2" style="max-height: 600px; overflow-y: auto;">
                        <!-- Kelompok A -->
                        <div class="kelompok-container mb-2" data-kelompok="A">
                            <div class="kelompok-header" style="background-color: #17a2b8; color: white; padding: 8px 12px; border-radius: 4px; cursor: pointer;">
                                <strong><i class="fas fa-folder"></i> Kelompok A (Wajib Umum)</strong>
                                <span class="badge badge-light float-right kelompok-count">0</span>
                            </div>
                            <div class="kelompok-dropzone p-2" id="kelompok-A" style="min-height: 60px; border: 2px dashed #17a2b8; border-radius: 4px; background-color: #e3f2fd;"></div>
                        </div>

                        <!-- Kelompok B -->
                        <div class="kelompok-container mb-2" data-kelompok="B">
                            <div class="kelompok-header" style="background-color: #28a745; color: white; padding: 8px 12px; border-radius: 4px; cursor: pointer;">
                                <strong><i class="fas fa-folder"></i> Kelompok B (Pengembangan)</strong>
                                <span class="badge badge-light float-right kelompok-count">0</span>
                            </div>
                            <div class="kelompok-dropzone p-2" id="kelompok-B" style="min-height: 60px; border: 2px dashed #28a745; border-radius: 4px; background-color: #e8f5e9;"></div>
                        </div>

                        <!-- Kelompok C -->
                        <div class="kelompok-container mb-2" data-kelompok="C">
                            <div class="kelompok-header" style="background-color: #ffc107; color: #212529; padding: 8px 12px; border-radius: 4px; cursor: pointer;">
                                <strong><i class="fas fa-folder"></i> Kelompok C (Peminatan)</strong>
                                <span class="badge badge-dark float-right kelompok-count">0</span>
                            </div>
                            <div class="kelompok-dropzone p-2" id="kelompok-C" style="min-height: 60px; border: 2px dashed #ffc107; border-radius: 4px; background-color: #fff8e1;"></div>
                        </div>

                        <!-- PAI & Bahasa Arab -->
                        <div class="kelompok-container mb-2" data-kelompok="PAI">
                            <div class="kelompok-header" style="background-color: #6f42c1; color: white; padding: 8px 12px; border-radius: 4px; cursor: pointer;">
                                <strong><i class="fas fa-mosque"></i> PAI & Bahasa Arab (Madrasah)</strong>
                                <span class="badge badge-light float-right kelompok-count">0</span>
                            </div>
                            <div class="kelompok-dropzone p-2" id="kelompok-PAI" style="min-height: 60px; border: 2px dashed #6f42c1; border-radius: 4px; background-color: #f3e5f5;"></div>
                        </div>

                        <!-- Muatan Lokal -->
                        <div class="kelompok-container mb-2" data-kelompok="Muatan Lokal">
                            <div class="kelompok-header" style="background-color: #fd7e14; color: white; padding: 8px 12px; border-radius: 4px; cursor: pointer;">
                                <strong><i class="fas fa-map-marker-alt"></i> Muatan Lokal</strong>
                                <span class="badge badge-light float-right kelompok-count">0</span>
                            </div>
                            <div class="kelompok-dropzone p-2" id="kelompok-muatan-lokal" style="min-height: 60px; border: 2px dashed #fd7e14; border-radius: 4px; background-color: #fff3e0;"></div>
                        </div>

                        <!-- Lainnya -->
                        <div class="kelompok-container mb-2" data-kelompok="">
                            <div class="kelompok-header" style="background-color: #6c757d; color: white; padding: 8px 12px; border-radius: 4px; cursor: pointer;">
                                <strong><i class="fas fa-folder-open"></i> Lainnya (Belum Dikelompokkan)</strong>
                                <span class="badge badge-light float-right kelompok-count">0</span>
                            </div>
                            <div class="kelompok-dropzone p-2" id="kelompok-lainnya" style="min-height: 60px; border: 2px dashed #6c757d; border-radius: 4px; background-color: #f5f5f5;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group mt-3">
            <button type="submit" class="btn btn-primary btn-lg" id="btnSubmit" disabled>
                <i class="fas fa-save"></i> Simpan Semua Mapel Terpilih
            </button>
            <a href="{{ route('admin.mapel.index') }}" class="btn btn-secondary btn-lg">
                <i class="fas fa-times"></i> Batal
            </a>
        </div>
    </form>
</div>
@stop

@section('css')
<style>
    .mapel-item {
        background: white;
        border: 1px solid #dee2e6;
        padding: 10px 12px;
        margin-bottom: 8px;
        border-radius: 4px;
        cursor: grab;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .mapel-item:hover {
        border-color: #007bff;
        box-shadow: 0 2px 8px rgba(0,123,255,0.2);
        transform: translateY(-2px);
    }
    
    .mapel-item.dragging {
        opacity: 0.5;
        cursor: grabbing;
    }
    
    .mapel-item.selected {
        background-color: #d4edda;
        border-color: #28a745;
    }
    
    .mapel-info {
        flex: 1;
    }
    
    .mapel-name {
        font-weight: 600;
        color: #212529;
        margin-bottom: 4px;
    }
    
    .mapel-meta {
        font-size: 0.85rem;
        color: #6c757d;
    }
    
    .mapel-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .kkm-input-small {
        width: 70px;
        text-align: center;
        font-weight: 600;
    }
    
    .kelompok-dropzone {
        transition: all 0.3s;
    }
    
    .kelompok-dropzone.drag-over {
        background-color: #fff9c4 !important;
        border-style: solid !important;
        border-width: 3px !important;
    }
    
    .kelompok-header {
        margin-bottom: 8px;
    }
    
    .kelompok-header:hover {
        opacity: 0.9;
    }
    
    .badge-kode {
        font-size: 0.75rem;
        padding: 3px 8px;
    }
    
    .drag-handle {
        cursor: grab;
        color: #6c757d;
        margin-right: 8px;
    }
    
    .drag-handle:active {
        cursor: grabbing;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
const mapelTemplates = @json(config('mapel_template'));
let selectedMapel = {};
let mapelCounter = 0;

$(document).ready(function() {
    // Load template when kurikulum selected
    $('#kurikulum_id').on('change', function() {
        const kode = $(this).find(':selected').data('kode');
        if (kode) {
            loadMapelTemplate(kode);
            $('#dragDropArea').slideDown();
        } else {
            $('#dragDropArea').slideUp();
            $('#templateMapelList').html('');
        }
    });
    
    // Form submit validation
    $('#bulkMapelForm').on('submit', function(e) {
        e.preventDefault();
        
        const tingkat = $('input[name="tingkat[]"]:checked').map(function() { return $(this).val(); }).get();
        const semester = $('input[name="semester[]"]:checked').map(function() { return $(this).val(); }).get();
        const totalMapel = Object.keys(selectedMapel).length;
        
        if (!tingkat || tingkat.length === 0) {
            Swal.fire('Perhatian', 'Pilih minimal 1 tingkat!', 'warning');
            return false;
        }
        
        if (!semester || semester.length === 0) {
            Swal.fire('Perhatian', 'Pilih minimal 1 semester!', 'warning');
            return false;
        }
        
        if (totalMapel === 0) {
            Swal.fire('Perhatian', 'Pilih minimal 1 mata pelajaran!', 'warning');
            return false;
        }
        
        // Add hidden inputs for selected mapel
        Object.values(selectedMapel).forEach(mapel => {
            $('<input>').attr({
                type: 'hidden',
                name: `mapel[${mapel.id}]`,
                value: JSON.stringify(mapel)
            }).appendTo(this);
            
            $('<input>').attr({
                type: 'hidden',
                name: `kkm_${mapel.id}`,
                value: mapel.kkm || 75
            }).appendTo(this);
        });
        
        this.submit();
    });
});

function loadMapelTemplate(kodeKurikulum) {
    const templates = mapelTemplates[kodeKurikulum];
    if (!templates) {
        $('#templateMapelList').html('<div class="alert alert-warning">Template tidak ditemukan untuk kurikulum ini.</div>');
        return;
    }
    
    let html = '';
    let totalMapel = 0;
    
    Object.entries(templates).forEach(([groupKey, group]) => {
        html += `
            <div class="mb-3">
                <div style="background-color: #e9ecef; padding: 6px 10px; border-radius: 4px; margin-bottom: 8px;">
                    <strong>${group.label}</strong>
                    <small class="text-muted d-block">${group.description}</small>
                </div>
        `;
        
        group.mapel.forEach((mapel, index) => {
            const mapelId = `mapel_${groupKey}_${index}`;
            totalMapel++;
            
            html += `
                <div class="mapel-item" draggable="true" data-mapel-id="${mapelId}" data-mapel='${JSON.stringify(mapel)}'>
                    <span class="drag-handle"><i class="fas fa-grip-vertical"></i></span>
                    <div class="mapel-info">
                        <div class="mapel-name">${mapel.nama_mapel}</div>
                        <div class="mapel-meta">
                            <span class="badge badge-primary badge-kode">${mapel.kode_mapel}</span>
                            <span class="badge badge-secondary">${mapel.jam_pelajaran} jam/minggu</span>
                            ${mapel.kelompok ? `<span class="badge badge-info">Kel. ${mapel.kelompok}</span>` : ''}
                        </div>
                    </div>
                    <div class="mapel-actions">
                        <input type="number" class="form-control form-control-sm kkm-input-small" 
                               value="${mapel.kkm_default || 75}" min="0" max="100" 
                               data-mapel-id="${mapelId}" placeholder="KKM">
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
    });
    
    $('#templateMapelList').html(html);
    $('#templateCount').text(`${totalMapel} mapel`);
    initDragDrop();
}

function initDragDrop() {
    // Make template items draggable
    const templateItems = document.querySelectorAll('#templateMapelList .mapel-item');
    templateItems.forEach(item => {
        item.addEventListener('dragstart', handleDragStart);
        item.addEventListener('dragend', handleDragEnd);
    });
    
    // Make kelompok dropzones sortable
    const dropzones = document.querySelectorAll('.kelompok-dropzone');
    dropzones.forEach(zone => {
        new Sortable(zone, {
            group: 'mapel',
            animation: 150,
            ghostClass: 'bg-info',
            onAdd: function(evt) {
                handleDropToKelompok(evt);
            },
            onRemove: function(evt) {
                updateCounts();
            },
            onUpdate: function(evt) {
                updateCounts();
            }
        });
        
        zone.addEventListener('dragover', handleDragOver);
        zone.addEventListener('drop', handleDrop);
        zone.addEventListener('dragleave', handleDragLeave);
    });
}

function handleDragStart(e) {
    e.currentTarget.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'copy';
    e.dataTransfer.setData('text/html', e.currentTarget.innerHTML);
    e.dataTransfer.setData('mapel-id', e.currentTarget.dataset.mapelId);
    e.dataTransfer.setData('mapel-data', e.currentTarget.dataset.mapel);
}

function handleDragEnd(e) {
    e.currentTarget.classList.remove('dragging');
}

function handleDragOver(e) {
    if (e.preventDefault) e.preventDefault();
    e.dataTransfer.dropEffect = 'copy';
    e.currentTarget.classList.add('drag-over');
    return false;
}

function handleDragLeave(e) {
    e.currentTarget.classList.remove('drag-over');
}

function handleDrop(e) {
    if (e.stopPropagation) e.stopPropagation();
    e.preventDefault();
    
    e.currentTarget.classList.remove('drag-over');
    
    const mapelId = e.dataTransfer.getData('mapel-id');
    const mapelData = JSON.parse(e.dataTransfer.getData('mapel-data'));
    const kelompok = e.currentTarget.closest('.kelompok-container').dataset.kelompok;
    const kkm = document.querySelector(`input[data-mapel-id="${mapelId}"]`)?.value || 75;
    
    // Check if already in selected
    if (!selectedMapel[mapelId]) {
        addMapelToKelompok(mapelId, mapelData, kelompok, kkm, e.currentTarget);
    }
    
    return false;
}

function handleDropToKelompok(evt) {
    const item = evt.item;
    const mapelId = item.dataset.mapelId;
    const mapelData = JSON.parse(item.dataset.mapel);
    const kelompok = evt.to.closest('.kelompok-container').dataset.kelompok;
    
    if (!selectedMapel[mapelId]) {
        const kkm = item.querySelector('.kkm-input-small')?.value || 75;
        selectedMapel[mapelId] = {
            id: mapelId,
            ...mapelData,
            kelompok: kelompok,
            kkm: kkm
        };
    } else {
        selectedMapel[mapelId].kelompok = kelompok;
    }
    
    updateCounts();
}

function addMapelToKelompok(mapelId, mapelData, kelompok, kkm, dropzone) {
    selectedMapel[mapelId] = {
        id: mapelId,
        ...mapelData,
        kelompok: kelompok,
        kkm: kkm
    };
    
    const html = `
        <div class="mapel-item selected" draggable="true" data-mapel-id="${mapelId}" data-mapel='${JSON.stringify(mapelData)}'>
            <span class="drag-handle"><i class="fas fa-grip-vertical"></i></span>
            <div class="mapel-info">
                <div class="mapel-name">${mapelData.nama_mapel}</div>
                <div class="mapel-meta">
                    <span class="badge badge-primary badge-kode">${mapelData.kode_mapel}</span>
                    <span class="badge badge-secondary">${mapelData.jam_pelajaran} jam</span>
                </div>
            </div>
            <div class="mapel-actions">
                <input type="number" class="form-control form-control-sm kkm-input-small" 
                       value="${kkm}" min="0" max="100" data-mapel-id="${mapelId}">
                <button type="button" class="btn btn-sm btn-danger" onclick="removeMapel('${mapelId}')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    
    $(dropzone).append(html);
    updateCounts();
}

function removeMapel(mapelId) {
    delete selectedMapel[mapelId];
    $(`.mapel-item[data-mapel-id="${mapelId}"]`).closest('.kelompok-dropzone').find(`[data-mapel-id="${mapelId}"]`).remove();
    updateCounts();
}

function updateCounts() {
    const total = Object.keys(selectedMapel).length;
    $('#selectedCount').text(`${total} mapel dipilih`);
    $('#btnSubmit').prop('disabled', total === 0);
    
    // Update per kelompok
    $('.kelompok-container').each(function() {
        const count = $(this).find('.kelompok-dropzone .mapel-item').length;
        $(this).find('.kelompok-count').text(count);
    });
    
    // Update KKM in selectedMapel when input changes
    $('.kkm-input-small').off('change').on('change', function() {
        const mapelId = $(this).data('mapel-id');
        if (selectedMapel[mapelId]) {
            selectedMapel[mapelId].kkm = $(this).val();
        }
    });
}
</script>
@stop

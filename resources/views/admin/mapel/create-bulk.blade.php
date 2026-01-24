@extends('adminlte::page')

@section('title', 'Tambah Mata Pelajaran')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-plus-circle"></i> Tambah Mata Pelajaran</h1>
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
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> 
        <strong>Petunjuk:</strong> Pilih kurikulum, centang mata pelajaran yang ingin diaktifkan, atur KKM jika diperlukan, lalu simpan.
    </div>

    <form action="{{ route('admin.mapel.bulk-store') }}" method="POST" id="form-bulk-mapel">
        @csrf
        
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-cog"></i> Konfigurasi Dasar</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="kurikulum_id">Pilih Kurikulum <span class="text-danger">*</span></label>
                            <select name="kurikulum_id" id="kurikulum_id" class="form-control @error('kurikulum_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Kurikulum --</option>
                                @foreach($kurikulums as $kurikulum)
                                    <option value="{{ $kurikulum->id }}" data-kode="{{ $kurikulum->kode }}">
                                        {{ $kurikulum->nama_kurikulum }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kurikulum_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Template mata pelajaran akan muncul sesuai kurikulum yang dipilih</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="jurusan_id">Jurusan (Optional)</label>
                            <select name="jurusan_id" id="jurusan_id" class="form-control">
                                <option value="">-- Umum (Semua Jurusan) --</option>
                                @foreach($jurusans as $jurusan)
                                    <option value="{{ $jurusan->id }}">{{ $jurusan->nama_jurusan }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Kosongkan jika mapel untuk semua jurusan</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Terapkan untuk Tingkat <span class="text-danger">*</span></label>
                            <div class="row">
                                @for($i = 1; $i <= 12; $i++)
                                    <div class="col-md-1 col-sm-2 col-4">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="tingkat{{ $i }}" 
                                                   name="tingkat[]" value="{{ $i }}">
                                            <label class="custom-control-label" for="tingkat{{ $i }}">{{ $i }}</label>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                            <small class="text-muted">Pilih tingkat kelas yang akan diajar (akan diterapkan ke semua mapel terpilih)</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Semester <span class="text-danger">*</span></label><br>
                            <div class="custom-control custom-checkbox custom-control-inline">
                                <input type="checkbox" class="custom-control-input" id="semester1" name="semester[]" value="1">
                                <label class="custom-control-label" for="semester1">Semester 1 (Ganjil)</label>
                            </div>
                            <div class="custom-control custom-checkbox custom-control-inline">
                                <input type="checkbox" class="custom-control-input" id="semester2" name="semester[]" value="2">
                                <label class="custom-control-label" for="semester2">Semester 2 (Genap)</label>
                            </div>
                            <br>
                            <small class="text-muted">Pilih semester yang diajar (akan diterapkan ke semua mapel terpilih)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="template-container" style="display: none;">
            <!-- Template akan dimuat via AJAX -->
        </div>

        <div class="card" id="action-card" style="display: none;">
            <div class="card-footer">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Simpan Semua Mata Pelajaran Terpilih
                </button>
                <a href="{{ route('admin.mapel.index') }}" class="btn btn-secondary btn-lg">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </form>
@stop

@section('css')
    <style>
        .mapel-item {
            padding: 10px;
            border-bottom: 1px solid #e9ecef;
        }
        .mapel-item:last-child {
            border-bottom: none;
        }
        .mapel-item:hover {
            background-color: #f8f9fa;
        }
        .kkm-input {
            width: 70px;
        }
        .group-header {
            background-color: #e9ecef;
            padding: 10px;
            font-weight: bold;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .select-all-group {
            float: right;
            font-weight: normal;
            font-size: 0.9em;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            let mapelTemplates = @json(config('mapel_template'));

            $('#kurikulum_id').change(function() {
                const kurikulumId = $(this).val();
                const kodeKurikulum = $(this).find(':selected').data('kode');
                
                if (!kurikulumId || !kodeKurikulum) {
                    $('#template-container').hide();
                    $('#action-card').hide();
                    return;
                }

                loadMapelTemplate(kodeKurikulum);
            });

            function loadMapelTemplate(kodeKurikulum) {
                const template = mapelTemplates[kodeKurikulum];
                
                if (!template) {
                    alert('Template untuk kurikulum ini belum tersedia');
                    return;
                }

                let html = '';
                let groupIndex = 0;

                Object.keys(template).forEach(function(groupKey) {
                    const group = template[groupKey];
                    groupIndex++;
                    
                    html += `
                        <div class="card card-outline card-success">
                            <div class="card-header group-header">
                                <input type="checkbox" class="select-all-group" id="select-all-${groupIndex}" data-group="${groupIndex}">
                                <label for="select-all-${groupIndex}" class="select-all-group" style="margin-right: 10px; cursor: pointer;">
                                    <i class="fas fa-check-square"></i> Pilih Semua
                                </label>
                                <i class="fas fa-book"></i> ${group.label}
                                <small class="text-muted" style="font-weight: normal; display: block; margin-top: 5px;">
                                    ${group.description}
                                </small>
                            </div>
                            <div class="card-body">
                    `;

                    group.mapel.forEach(function(mapel, idx) {
                        const mapelId = `mapel_${groupIndex}_${idx}`;
                        const kkmInputName = `kkm_${groupIndex}_${idx}`;
                        
                        html += `
                            <div class="mapel-item">
                                <div class="row align-items-center">
                                    <div class="col-md-1 col-sm-2">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input mapel-checkbox group-${groupIndex}" 
                                                   id="${mapelId}" name="mapel[${mapelId}]" 
                                                   value='${JSON.stringify(mapel)}'>
                                            <label class="custom-control-label" for="${mapelId}"></label>
                                        </div>
                                    </div>
                                    <div class="col-md-5 col-sm-10">
                                        <strong>${mapel.nama_mapel}</strong>
                                        <span class="badge badge-info">${mapel.kode_mapel}</span>
                                    </div>
                                    <div class="col-md-2 col-sm-4">
                                        <small class="text-muted">
                                            <i class="fas fa-clock"></i> ${mapel.jam_pelajaran} Jam/Minggu
                                        </small>
                                    </div>
                                    <div class="col-md-2 col-sm-4">
                                        <small class="text-muted">${mapel.kategori || '-'}</small>
                                    </div>
                                    <div class="col-md-2 col-sm-4">
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">KKM</span>
                                            </div>
                                            <input type="number" class="form-control kkm-input" 
                                                   name="${kkmInputName}" 
                                                   min="0" max="100" value="${mapel.kkm_default}" 
                                                   placeholder="75">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });

                    html += `
                            </div>
                        </div>
                    `;
                });

                $('#template-container').html(html).show();
                $('#action-card').show();

                // Event listener untuk select all per group
                $('.select-all-group').change(function() {
                    const groupId = $(this).data('group');
                    const isChecked = $(this).prop('checked');
                    $(`.group-${groupId}`).prop('checked', isChecked);
                });
            }

            // Form validation
            $('#form-bulk-mapel').submit(function(e) {
                const selectedMapel = $('input[name="mapel[]"]:checked').length;
                const selectedTingkat = $('input[name="tingkat[]"]:checked').length;
                const selectedSemester = $('input[name="semester[]"]:checked').length;

                if (selectedMapel === 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Peringatan',
                        text: 'Pilih minimal 1 mata pelajaran!'
                    });
                    return false;
                }

                if (selectedTingkat === 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Peringatan',
                        text: 'Pilih minimal 1 tingkat kelas!'
                    });
                    return false;
                }

                if (selectedSemester === 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Peringatan',
                        text: 'Pilih minimal 1 semester!'
                    });
                    return false;
                }

                return true;
            });
        });
    </script>
@stop

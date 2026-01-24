@extends('adminlte::page')

@section('title', 'Cetak Dokumen')

@section('content_header')
    <h1><i class="fas fa-print"></i> Cetak Dokumen</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            {{-- Card: Cetak Absensi Kelas --}}
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-clipboard-check"></i> Cetak Absensi Kelas (Batch)</h3>
                </div>
                <form action="{{ route('admin.cetak.absensi-batch') }}" method="POST" id="formCetakAbsensi" target="_blank">
                    @csrf
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Cetak Absensi Sekaligus</strong><br>
                            Pilih filter untuk mencetak absensi beberapa kelas sekaligus. Setiap kelas akan dicetak dalam halaman terpisah dalam satu file PDF.
                        </div>

                        {{-- Filter Section --}}
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filter_tahun_pelajaran">
                                        <i class="fas fa-calendar-alt"></i> Tahun Pelajaran <span class="text-danger">*</span>
                                    </label>
                                    <select name="tahun_pelajaran_id" id="filter_tahun_pelajaran" class="form-control" required>
                                        <option value="">-- Pilih Tahun Pelajaran --</option>
                                        @foreach($tahunPelajarans as $tp)
                                            <option value="{{ $tp->id }}" {{ $tp->is_active ? 'selected' : '' }}>
                                                {{ $tp->nama }} {{ $tp->is_active ? '(Aktif)' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filter_tingkat">
                                        <i class="fas fa-layer-group"></i> Tingkat <span class="text-danger">*</span>
                                    </label>
                                    <select name="tingkat" id="filter_tingkat" class="form-control" required>
                                        <option value="">-- Pilih Tingkat --</option>
                                        @foreach($tingkatOptions as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filter_jurusan"><i class="fas fa-graduation-cap"></i> Jurusan</label>
                                    <select name="jurusan_id" id="filter_jurusan" class="form-control">
                                        <option value="">-- Semua Jurusan --</option>
                                        @foreach($jurusans as $jurusan)
                                            <option value="{{ $jurusan->id }}">{{ $jurusan->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filter_kurikulum"><i class="fas fa-book"></i> Kurikulum</label>
                                    <select name="kurikulum_id" id="filter_kurikulum" class="form-control">
                                        <option value="">-- Semua Kurikulum --</option>
                                        @foreach($kurikulums as $kurikulum)
                                            <option value="{{ $kurikulum->id }}">{{ $kurikulum->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <button type="button" class="btn btn-info" id="btnLoadKelas">
                                    <i class="fas fa-search"></i> Cari Kelas
                                </button>
                            </div>
                        </div>

                        <hr>

                        {{-- Kelas List Section --}}
                        <div id="kelasList" style="display: none;">
                            <h5><i class="fas fa-list"></i> Pilih Kelas yang Akan Dicetak</h5>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="selectAll">
                                    <label class="custom-control-label font-weight-bold" for="selectAll">
                                        Pilih Semua
                                    </label>
                                </div>
                            </div>
                            
                            <div class="row" id="kelasCheckboxes">
                                <!-- Checkboxes will be loaded here -->
                            </div>

                            <div id="selectedCount" class="alert alert-success mt-3" style="display: none;">
                                <i class="fas fa-check-circle"></i> <strong><span id="countText">0</span> kelas</strong> dipilih
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-lg" id="btnCetak" disabled>
                            <i class="fas fa-print"></i> Cetak Absensi
                        </button>
                        <button type="button" class="btn btn-secondary btn-lg" id="btnReset">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                    </div>
                </form>
            </div>

            {{-- Card: Fitur Cetak Lainnya (Placeholder) --}}
            <div class="card card-secondary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-file-alt"></i> Fitur Cetak Lainnya</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h4>Daftar Nilai</h4>
                                    <p>Coming Soon</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <a href="#" class="small-box-footer disabled">
                                    Coming Soon <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h4>Rapor</h4>
                                    <p>Coming Soon</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-book"></i>
                                </div>
                                <a href="#" class="small-box-footer disabled">
                                    Coming Soon <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h4>Surat Keterangan</h4>
                                    <p>Coming Soon</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-file-signature"></i>
                                </div>
                                <a href="#" class="small-box-footer disabled">
                                    Coming Soon <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Load Kelas by Filter
            $('#btnLoadKelas').on('click', function() {
                const tahunPelajaran = $('#filter_tahun_pelajaran').val();
                const tingkat = $('#filter_tingkat').val();
                const jurusan = $('#filter_jurusan').val();
                const kurikulum = $('#filter_kurikulum').val();

                if (!tahunPelajaran || !tingkat) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Filter Belum Lengkap',
                        text: 'Tahun Pelajaran dan Tingkat harus dipilih!'
                    });
                    return;
                }

                $.ajax({
                    url: '{{ route('admin.cetak.kelas-by-filter') }}',
                    method: 'GET',
                    data: {
                        tahun_pelajaran_id: tahunPelajaran,
                        tingkat: tingkat,
                        jurusan_id: jurusan,
                        kurikulum_id: kurikulum
                    },
                    beforeSend: function() {
                        $('#btnLoadKelas').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Loading...');
                    },
                    success: function(response) {
                        if (response.success && response.data.length > 0) {
                            renderKelasList(response.data);
                            $('#kelasList').slideDown();
                        } else {
                            Swal.fire({
                                icon: 'info',
                                title: 'Tidak Ada Kelas',
                                text: 'Tidak ada kelas yang ditemukan dengan filter tersebut.'
                            });
                            $('#kelasList').slideUp();
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal memuat data kelas.'
                        });
                    },
                    complete: function() {
                        $('#btnLoadKelas').prop('disabled', false).html('<i class="fas fa-search"></i> Cari Kelas');
                    }
                });
            });

            // Render Kelas List
            function renderKelasList(kelasList) {
                let html = '';
                kelasList.forEach(function(kelas) {
                    html += `
                        <div class="col-md-4 mb-2">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input kelas-checkbox" 
                                       id="kelas_${kelas.id}" name="kelas_ids[]" value="${kelas.id}">
                                <label class="custom-control-label" for="kelas_${kelas.id}">
                                    <strong>${kelas.nama_lengkap}</strong><br>
                                    <small class="text-muted">
                                        <i class="fas fa-users"></i> ${kelas.siswa_count} siswa
                                    </small>
                                </label>
                            </div>
                        </div>
                    `;
                });
                $('#kelasCheckboxes').html(html);
                updateSelectedCount();
            }

            // Select All Checkbox
            $('#selectAll').on('change', function() {
                $('.kelas-checkbox').prop('checked', $(this).is(':checked'));
                updateSelectedCount();
            });

            // Individual Checkbox Change
            $(document).on('change', '.kelas-checkbox', function() {
                updateSelectedCount();
                
                // Update select all checkbox
                const total = $('.kelas-checkbox').length;
                const checked = $('.kelas-checkbox:checked').length;
                $('#selectAll').prop('checked', total === checked);
            });

            // Update Selected Count
            function updateSelectedCount() {
                const count = $('.kelas-checkbox:checked').length;
                $('#countText').text(count);
                
                if (count > 0) {
                    $('#selectedCount').slideDown();
                    $('#btnCetak').prop('disabled', false);
                } else {
                    $('#selectedCount').slideUp();
                    $('#btnCetak').prop('disabled', true);
                }
            }

            // Reset Button
            $('#btnReset').on('click', function() {
                $('#formCetakAbsensi')[0].reset();
                $('#kelasList').slideUp();
                $('#selectAll').prop('checked', false);
                updateSelectedCount();
            });

            // Form Submit
            $('#formCetakAbsensi').on('submit', function(e) {
                const count = $('.kelas-checkbox:checked').length;
                if (count === 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Belum Ada Kelas Dipilih',
                        text: 'Pilih minimal 1 kelas untuk dicetak!'
                    });
                    return false;
                }
                
                Swal.fire({
                    icon: 'info',
                    title: 'Sedang Mencetak...',
                    text: `Mencetak ${count} kelas. Mohon tunggu...`,
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Auto close after 3 seconds
                setTimeout(function() {
                    Swal.close();
                }, 3000);
            });
        });
    </script>
@stop

@section('css')
    <style>
        .small-box.disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .small-box.disabled .small-box-footer {
            pointer-events: none;
        }
        .custom-control-label {
            cursor: pointer;
        }
    </style>
@stop

@extends('adminlte::page')

@section('title', 'Tambah Siswa ke Kelas')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-user-plus"></i> Tambah Siswa ke Kelas</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.kelas.index') }}">Kelas</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.kelas.show', $kelas->id) }}">Detail</a></li>
                <li class="breadcrumb-item active">Tambah Siswa</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-4">
            {{-- Kelas Info --}}
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Info Kelas</h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-5">Nama Kelas:</dt>
                        <dd class="col-sm-7">{{ $kelas->nama_lengkap }}</dd>

                        <dt class="col-sm-5">Kode:</dt>
                        <dd class="col-sm-7"><span class="badge badge-info">{{ $kelas->kode_kelas }}</span></dd>

                        <dt class="col-sm-5">Tingkat:</dt>
                        <dd class="col-sm-7">{{ $kelas->getTingkatRomawi() }}</dd>

                        <dt class="col-sm-5">Tahun Pelajaran:</dt>
                        <dd class="col-sm-7">{{ $kelas->tahunPelajaran->nama }}</dd>

                        <dt class="col-sm-5">Kapasitas:</dt>
                        <dd class="col-sm-7">
                            <span class="badge badge-{{ $kelas->capacity_badge_color }}">
                                {{ $kelas->jumlah_siswa }}/{{ $kelas->kapasitas }}
                            </span>
                        </dd>

                        <dt class="col-sm-5">Sisa Tempat:</dt>
                        <dd class="col-sm-7">
                            <strong class="text-{{ $kelas->sisa_tempat > 0 ? 'success' : 'danger' }}">
                                {{ $kelas->sisa_tempat }} kursi
                            </strong>
                        </dd>
                    </dl>
                </div>
            </div>

            {{-- Form Settings --}}
            <div class="card card-secondary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-cog"></i> Pengaturan</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="tanggal_masuk">Tanggal Masuk <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="tanggal_masuk" value="{{ date('Y-m-d') }}" required>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> Tanggal siswa mulai bergabung di kelas
                        </small>
                    </div>

                    <div class="callout callout-warning">
                        <h5><i class="fas fa-exclamation-triangle"></i> Perhatian!</h5>
                        <ul class="mb-0 pl-3">
                            <li>Pilih siswa yang ingin ditambahkan</li>
                            <li>Maksimal {{ $kelas->sisa_tempat }} siswa</li>
                            <li>Nomor absen akan otomatis</li>
                        </ul>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.kelas.show', $kelas->id) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <button type="button" class="btn btn-primary float-right" id="btn-simpan" disabled>
                        <i class="fas fa-save"></i> Simpan (<span id="selected-count">0</span>)
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            {{-- Available Siswa List --}}
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-users"></i> Tambah Siswa</h3>
                </div>
                <div class="card-body">
                    {{-- Nav Tabs --}}
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="pilih-tab" data-toggle="tab" href="#pilih-siswa" role="tab">
                                <i class="fas fa-check-square"></i> Pilih Siswa
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="nisn-tab" data-toggle="tab" href="#nisn-bulk" role="tab">
                                <i class="fas fa-list-ol"></i> Input NISN (Bulk)
                            </a>
                        </li>
                    </ul>

                    {{-- Tab Content --}}
                    <div class="tab-content mt-3" id="myTabContent">
                        {{-- Tab 1: Pilih Siswa (Checkbox) --}}
                        <div class="tab-pane fade show active" id="pilih-siswa" role="tabpanel">
                            <div class="mb-2">
                                <div class="input-group input-group-sm" style="width: 300px;">
                                    <input type="text" id="search-siswa" class="form-control" placeholder="Cari NISN/Nama...">
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    </div>
                                </div>
                            </div>
                <div class="card-body p-0">
                    @if($availableSiswa->count() > 0)
                        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                            <table class="table table-hover" id="siswa-table">
                                <thead class="thead-light sticky-top">
                                    <tr>
                                        <th width="5%">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="check-all">
                                                <label class="custom-control-label" for="check-all"></label>
                                            </div>
                                        </th>
                                        <th width="15%">NISN</th>
                                        <th>Nama Lengkap</th>
                                        <th width="8%">JK</th>
                                        <th width="15%">Tempat Lahir</th>
                                        <th width="12%">Tanggal Lahir</th>
                                    </tr>
                                </thead>
                                <tbody id="siswa-tbody">
                                    @foreach($availableSiswa as $siswa)
                                        <tr class="siswa-row" data-nisn="{{ $siswa->nisn }}" data-nama="{{ strtolower($siswa->nama_lengkap) }}">
                                            <td>
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input siswa-checkbox" 
                                                        id="siswa-{{ $siswa->uuid }}" 
                                                        value="{{ $siswa->uuid }}"
                                                        data-nama="{{ $siswa->nama_lengkap }}">
                                                    <label class="custom-control-label" for="siswa-{{ $siswa->uuid }}"></label>
                                                </div>
                                            </td>
                                            <td>{{ $siswa->nisn }}</td>
                                            <td>{{ $siswa->nama_lengkap }}</td>
                                            <td>
                                                @if($siswa->jenis_kelamin == 'L')
                                                    <span class="badge badge-primary"><i class="fas fa-male"></i> L</span>
                                                @else
                                                    <span class="badge badge-danger"><i class="fas fa-female"></i> P</span>
                                                @endif
                                            </td>
                                            <td>{{ $siswa->tempat_lahir }}</td>
                                            <td>{{ \Carbon\Carbon::parse($siswa->tanggal_lahir)->format('d/m/Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>Tidak ada siswa yang tersedia untuk ditambahkan ke kelas ini.</p>
                            <small>Semua siswa aktif sudah terdaftar di kelas untuk tahun pelajaran ini.</small>
                        </div>
                    @endif
                        </div>

                        {{-- Tab 2: Input NISN Bulk --}}
                        <div class="tab-pane fade" id="nisn-bulk" role="tabpanel">
                            <div class="alert alert-info">
                                <h5><i class="fas fa-info-circle"></i> Petunjuk:</h5>
                                <ul class="mb-0">
                                    <li>Copy-paste <strong>NISN</strong> dari Excel atau file lain</li>
                                    <li>Tulis <strong>satu NISN per baris</strong> (tekan Enter)</li>
                                    <li>NISN harus <strong>10 digit angka</strong></li>
                                    <li>Karakter non-angka akan otomatis dihapus</li>
                                    <li>Maksimal <strong>{{ $kelas->sisa_tempat }} siswa</strong> sekaligus</li>
                                </ul>
                            </div>

                            <form id="formBulkNISN">
                                @csrf
                                <div class="form-group">
                                    <label for="nisn_list">Daftar NISN <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="nisn_list" name="nisn_list" rows="12" 
                                        placeholder="Contoh:&#10;0123456789&#10;0987654321&#10;0112233445&#10;..."></textarea>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-lightbulb"></i> Tip: Copy kolom NISN dari Excel dan paste di sini
                                    </small>
                                </div>

                                <button type="button" class="btn btn-primary btn-lg btn-block" id="btn-proses-nisn">
                                    <i class="fas fa-upload"></i> Proses Bulk Import
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .sticky-top {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #f8f9fa;
        }
        .siswa-row {
            cursor: pointer;
        }
        .siswa-row:hover {
            background-color: #f1f3f5;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            const maxSiswa = {{ $kelas->sisa_tempat }};
            let selectedCount = 0;

            // Update selected count
            function updateSelectedCount() {
                selectedCount = $('.siswa-checkbox:checked').length;
                $('#selected-count').text(selectedCount);
                $('#btn-simpan').prop('disabled', selectedCount === 0);

                // Disable checkboxes if limit reached
                if (selectedCount >= maxSiswa) {
                    $('.siswa-checkbox:not(:checked)').prop('disabled', true);
                    $('#check-all').prop('disabled', true);
                } else {
                    $('.siswa-checkbox').prop('disabled', false);
                    $('#check-all').prop('disabled', false);
                }
            }

            // Check all
            $('#check-all').on('change', function() {
                let isChecked = $(this).is(':checked');
                let uncheckedBoxes = $('.siswa-checkbox:not(:checked):visible');
                
                if (isChecked) {
                    let availableSlots = maxSiswa - selectedCount;
                    uncheckedBoxes.slice(0, availableSlots).prop('checked', true);
                } else {
                    $('.siswa-checkbox:visible').prop('checked', false);
                }
                
                updateSelectedCount();
            });

            // Individual checkbox
            $('.siswa-checkbox').on('change', function() {
                updateSelectedCount();
                
                // Update check-all status
                let totalVisible = $('.siswa-checkbox:visible').length;
                let checkedVisible = $('.siswa-checkbox:checked:visible').length;
                $('#check-all').prop('checked', totalVisible > 0 && totalVisible === checkedVisible);
            });

            // Row click to toggle checkbox
            $('.siswa-row').on('click', function(e) {
                if (!$(e.target).is('input[type="checkbox"]')) {
                    let checkbox = $(this).find('.siswa-checkbox');
                    if (!checkbox.is(':disabled')) {
                        checkbox.prop('checked', !checkbox.is(':checked')).trigger('change');
                    }
                }
            });

            // Search siswa
            $('#search-siswa').on('keyup', function() {
                let searchTerm = $(this).val().toLowerCase();
                
                $('.siswa-row').each(function() {
                    let nisn = $(this).data('nisn');
                    let nama = $(this).data('nama');
                    
                    if (nisn.includes(searchTerm) || nama.includes(searchTerm)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Submit form
            $('#btn-simpan').on('click', function() {
                let selectedSiswa = [];
                $('.siswa-checkbox:checked').each(function() {
                    selectedSiswa.push($(this).val());
                });

                if (selectedSiswa.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Perhatian!',
                        text: 'Pilih minimal 1 siswa untuk ditambahkan.'
                    });
                    return;
                }

                if (selectedSiswa.length > maxSiswa) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Melebihi Kapasitas!',
                        text: 'Maksimal ' + maxSiswa + ' siswa. Anda memilih ' + selectedSiswa.length + ' siswa.'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Konfirmasi',
                    text: 'Tambahkan ' + selectedSiswa.length + ' siswa ke kelas ini?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    confirmButtonText: 'Ya, Tambahkan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#btn-simpan').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
                        
                        $.ajax({
                            url: "{{ route('admin.kelas.siswa.store', $kelas->id) }}",
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                siswa_ids: selectedSiswa,
                                tanggal_masuk: $('#tanggal_masuk').val()
                            },
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: response.message,
                                }).then(() => {
                                    window.location.href = "{{ route('admin.kelas.show', $kelas->id) }}";
                                });
                            },
                            error: function(xhr) {
                                $('#btn-simpan').prop('disabled', false).html('<i class="fas fa-save"></i> Simpan (<span id="selected-count">' + selectedCount + '</span>)');
                                
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                                });
                            }
                        });
                    }
                });
            });

            // === NISN Bulk Import Handler ===
            $('#btn-proses-nisn').on('click', function() {
                let nisnList = $('#nisn_list').val().trim();
                
                if (!nisnList) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Input Kosong',
                        text: 'Silakan masukkan daftar NISN terlebih dahulu.'
                    });
                    return;
                }

                // Parse NISN list
                let nisnArray = nisnList.split('\n')
                    .map(line => line.trim().replace(/[^0-9]/g, '')) // Clean non-digit
                    .filter(nisn => nisn.length === 10); // Only 10 digits

                let uniqueNISN = [...new Set(nisnArray)]; // Remove duplicates

                if (uniqueNISN.length === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'NISN Tidak Valid',
                        html: 'Tidak ada NISN yang valid (10 digit).<br><small>Pastikan setiap NISN terdiri dari 10 digit angka.</small>'
                    });
                    return;
                }

                if (uniqueNISN.length > maxSiswa) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Melebihi Kapasitas!',
                        html: `Maksimal <strong>${maxSiswa} siswa</strong>.<br>Anda memasukkan <strong>${uniqueNISN.length} NISN</strong>.`
                    });
                    return;
                }

                // Confirmation
                Swal.fire({
                    title: 'Konfirmasi Bulk Import',
                    html: `
                        <p>Akan memproses <strong>${uniqueNISN.length} NISN</strong></p>
                        <small class="text-muted">NISN yang tidak ditemukan atau sudah terdaftar akan dilewati</small>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    confirmButtonText: 'Ya, Proses!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#btn-proses-nisn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');

                        $.ajax({
                            url: "{{ route('admin.kelas.siswa.store-nisn', $kelas->id) }}",
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                nisn_list: uniqueNISN.join('\n'),
                                tanggal_masuk: $('#tanggal_masuk').val()
                            },
                            success: function(response) {
                                let icon = 'success';
                                let title = 'Proses Selesai!';
                                let html = `
                                    <div class="text-left">
                                        <p><strong>✅ Berhasil:</strong> ${response.success_count} siswa</p>
                                        <p><strong>❌ Gagal:</strong> ${response.failed_count} NISN</p>
                                `;

                                if (response.errors && response.errors.length > 0) {
                                    html += `<hr><p><strong>Detail Error:</strong></p><ul class="text-left" style="max-height: 200px; overflow-y: auto;">`;
                                    response.errors.forEach(err => {
                                        html += `<li><code>${err.nisn}</code>: ${err.error}</li>`;
                                    });
                                    html += `</ul>`;
                                    
                                    if (response.failed_count > response.success_count) {
                                        icon = 'warning';
                                        title = 'Sebagian Berhasil';
                                    }
                                }

                                html += `</div>`;

                                Swal.fire({
                                    icon: icon,
                                    title: title,
                                    html: html,
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    window.location.href = "{{ route('admin.kelas.show', $kelas->id) }}";
                                });
                            },
                            error: function(xhr) {
                                $('#btn-proses-nisn').prop('disabled', false).html('<i class="fas fa-upload"></i> Proses Bulk Import');
                                
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: xhr.responseJSON?.message || 'Terjadi kesalahan saat memproses NISN'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@stop

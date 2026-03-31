@extends('adminlte::page')

@section('title', 'Detail Menu SNBP')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-graduation-cap"></i> Detail Menu SNBP</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.snbp-menu.index') }}">Menu SNBP</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
        </div>
    @endif

    <!-- Info Card -->
    <div class="card">
        <div class="card-header bg-primary">
            <h3 class="card-title">
                <i class="fas fa-info-circle"></i> {{ $snbpMenu->nama_menu }}
            </h3>
            <div class="card-tools">
                @if($snbpMenu->isEditable())
                    <a href="{{ route('admin.snbp-menu.edit', $snbpMenu) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                @else
                    <span class="badge badge-warning"><i class="fas fa-lock"></i> Readonly</span>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>Tahun Pelajaran:</strong><br>
                    {{ $snbpMenu->tahunPelajaran->nama ?? '-' }}
                    @if($snbpMenu->tahunPelajaran && $snbpMenu->tahunPelajaran->is_active)
                        <span class="badge badge-primary">Aktif</span>
                    @endif
                </div>
                <div class="col-md-3">
                    <strong>Status Menu:</strong><br>
                    @if($snbpMenu->is_active)
                        <span class="badge badge-success">Aktif</span>
                    @else
                        <span class="badge badge-secondary">Non-Aktif</span>
                    @endif
                </div>
                <div class="col-md-3">
                    <strong>Siswa Eligible:</strong><br>
                    <span class="badge badge-success badge-lg">{{ $summary['eligible_total'] }}</span>
                </div>
                <div class="col-md-3">
                    <strong>Siswa Tidak Eligible:</strong><br>
                    <span class="badge badge-danger badge-lg">{{ $snbpMenu->notEligibleSiswa->count() }}</span>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <strong><i class="fas fa-calendar-alt"></i> Periode Tampil:</strong><br>
                    @if($snbpMenu->tanggal_mulai)
                        <i class="fas fa-play text-success"></i> Mulai: <strong>{{ $snbpMenu->tanggal_mulai->format('d F Y, H:i') }}</strong><br>
                    @else
                        <i class="fas fa-play text-muted"></i> Mulai: <em class="text-muted">Tidak ada batas mulai</em><br>
                    @endif
                    @if($snbpMenu->tanggal_berakhir)
                        <i class="fas fa-stop text-danger"></i> Berakhir: <strong>{{ $snbpMenu->tanggal_berakhir->format('d F Y, H:i') }}</strong>
                    @else
                        <i class="fas fa-stop text-muted"></i> Berakhir: <em class="text-muted">Tidak ada batas akhir</em>
                    @endif
                </div>
                <div class="col-md-6">
                    @php
                        $now = now();
                        $isWithinPeriod = $snbpMenu->isWithinPeriod();
                    @endphp
                    <strong><i class="fas fa-clock"></i> Status Periode:</strong><br>
                    @if(!$snbpMenu->tanggal_mulai && !$snbpMenu->tanggal_berakhir)
                        <span class="badge badge-info">Selalu Tampil</span>
                    @elseif($snbpMenu->tanggal_mulai && $now->lt($snbpMenu->tanggal_mulai))
                        <span class="badge badge-warning">Belum Dimulai</span>
                    @elseif($snbpMenu->tanggal_berakhir && $now->gt($snbpMenu->tanggal_berakhir))
                        <span class="badge badge-secondary">Telah Berakhir</span>
                    @else
                        <span class="badge badge-success">Sedang Aktif</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    @if($snbpMenu->isEditable())
    <div class="row mb-3">
        <div class="col-md-6">
            <a href="{{ route('admin.snbp-menu.assign-eligible', $snbpMenu) }}" class="btn btn-success btn-block">
                <i class="fas fa-user-check"></i> Assign Siswa Eligible
            </a>
        </div>
        <div class="col-md-6">
            <a href="{{ route('admin.snbp-menu.assign-not-eligible', $snbpMenu) }}" class="btn btn-secondary btn-block">
                <i class="fas fa-user-times"></i> Assign Siswa Tidak Eligible
            </a>
        </div>
    </div>
    @endif

    <div class="row">
        <!-- Konten Eligible -->
        <div class="col-md-6">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-check-circle"></i> Konten Eligible
                    </h3>
                </div>
                <div class="card-body">
                    {!! $snbpMenu->konten_eligible ?: '<em class="text-muted">Belum ada konten</em>' !!}
                </div>
            </div>
        </div>

        <!-- Konten Tidak Eligible -->
        <div class="col-md-6">
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-times-circle"></i> Konten Tidak Eligible
                    </h3>
                </div>
                <div class="card-body">
                    {!! $snbpMenu->konten_not_eligible ?: '<em class="text-muted">Belum ada konten</em>' !!}
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $summary['sudah_isi'] }}</h3>
                    <p>Siswa eligible sudah isi nomor SNBP</p>
                </div>
                <div class="icon">
                    <i class="fas fa-id-card"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $summary['terhubung_lulusan'] }}</h3>
                    <p>Data SNBP sudah terhubung ke lulusan</p>
                </div>
                <div class="icon">
                    <i class="fas fa-link"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-success card-outline">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-users"></i> Monitoring Siswa Eligible
            </h3>
            <div class="card-tools text-muted small">
                <button type="button" class="btn btn-primary btn-sm mr-2" id="bulkCheckSnbpBtn">
                    <i class="fas fa-sync-alt"></i> Cek Semua Pengumuman
                </button>
                Klik judul kolom untuk sorting. Gunakan pencarian untuk nama, NISN, tanggal lahir, nomor SNBP, kampus, atau prodi.
            </div>
        </div>
        <div class="card-body">
            @if($eligibleSiswa->count() > 0)
            <div class="table-responsive">
                <table id="eligibleSnbpTable" class="table table-striped table-bordered table-hover mb-0">
                    <thead class="bg-success text-white">
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>NISN</th>
                            <th>Nama</th>
                            <th>Tanggal Lahir</th>
                            <th>Status Isi</th>
                            <th>Nomor SNBP</th>
                            <th>Status Cek</th>
                            <th>Update Terakhir</th>
                            <th>Status Lulusan</th>
                            <th>Jalur</th>
                            <th>Universitas</th>
                            <th>Program Studi</th>
                            <th style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($eligibleSiswa as $index => $siswa)
                            @php
                                $registration = $siswa->snbpRegistration;
                                $lulusan = optional($registration)->lulusan;
                                $sudahIsi = filled(optional($registration)->nomor_pendaftaran);
                                $statusIsiLabel = $sudahIsi ? 'Sudah Isi' : 'Belum Isi';
                                $statusIsiSort = $sudahIsi ? 1 : 0;
                                $lulusanTerhubung = $lulusan !== null;
                                $statusLulusanLabel = $lulusanTerhubung ? 'Terhubung' : 'Belum';
                                $statusLulusanSort = $lulusanTerhubung ? 1 : 0;
                                $tanggalLahir = $siswa->tanggal_lahir?->format('d-m-Y') ?? '-';
                                $tanggalLahirSort = $siswa->tanggal_lahir?->format('Y-m-d') ?? '';
                                $jalurMasuk = optional($lulusan)->jalur_masuk ?? '-';
                                $namaUniversitas = optional($lulusan)->nama_universitas
                                    ?? optional($lulusan)->nama_universitas_manual
                                    ?? optional(optional($lulusan)->referensiPerguruanTinggi)->nama
                                    ?? '-';
                                $programStudi = optional($lulusan)->program_studi
                                    ?? optional($lulusan)->program_studi_manual
                                    ?? optional(optional($lulusan)->referensiProgramStudi)->nama_program_studi
                                    ?? '-';
                                $checkStatus = $registration?->check_status ?? 'belum_dicek';
                                $checkLabel = $registration?->check_status_label ?? 'Belum Dicek';
                                $checkBadge = match ($checkStatus) {
                                    'lulus' => 'success',
                                    'tidak_lulus' => 'danger',
                                    'gagal_cek' => 'warning',
                                    default => 'secondary',
                                };
                                $lastChecked = $registration?->last_checked_at?->format('d-m-Y H:i') ?? '-';
                                $checkRoute = $registration
                                    ? route('admin.snbp-menu.check-announcement', [$snbpMenu, $registration])
                                    : null;
                            @endphp
                        <tr
                            data-siswa-id="{{ $siswa->id }}"
                            data-registration-id="{{ $registration?->id }}"
                            data-check-url="{{ $checkRoute }}"
                            data-can-check="{{ $registration && $sudahIsi && $siswa->tanggal_lahir ? '1' : '0' }}"
                            data-siswa-name="{{ $siswa->nama_lengkap }}"
                        >
                            <td>{{ $index + 1 }}</td>
                            <td><code>{{ $siswa->nisn }}</code></td>
                            <td>{{ $siswa->nama_lengkap }}</td>
                            <td data-order="{{ $tanggalLahirSort }}">
                                <span class="text-nowrap">{{ $tanggalLahir }}</span>
                            </td>
                            <td data-order="{{ $statusIsiSort }}">
                                @if($sudahIsi)
                                    <span class="badge badge-info">Sudah Isi</span>
                                @else
                                    <span class="badge badge-secondary">Belum Isi</span>
                                @endif
                            </td>
                            <td data-order="{{ optional($registration)->nomor_pendaftaran ?? '' }}">
                                @if($sudahIsi)
                                    <code class="js-nomor-pendaftaran">{{ $registration->nomor_pendaftaran }}</code>
                                @else
                                    <span class="text-muted">Belum isi</span>
                                @endif
                            </td>
                            <td data-order="{{ $checkStatus }}">
                                <span class="badge badge-{{ $checkBadge }} js-check-status">{{ $checkLabel }}</span>
                                <div class="small text-muted mt-1 js-check-message">{{ $registration?->last_check_message ?? '-' }}</div>
                            </td>
                            <td data-order="{{ $registration?->last_checked_at?->timestamp ?? 0 }}">
                                <span class="js-last-checked">{{ $lastChecked }}</span>
                            </td>
                            <td data-order="{{ $statusLulusanSort }}">
                                @if($lulusanTerhubung)
                                    <span class="badge badge-success">Terhubung</span>
                                @else
                                    <span class="badge badge-secondary">Belum</span>
                                @endif
                            </td>
                            <td>{{ $jalurMasuk }}</td>
                            <td>{{ $namaUniversitas }}</td>
                            <td>{{ $programStudi }}</td>
                            <td>
                                @if($registration && $sudahIsi && $siswa->tanggal_lahir)
                                    <button type="button" class="btn btn-info btn-sm js-check-snbp">
                                        <i class="fas fa-search"></i> Cek
                                    </button>
                                @else
                                    <button type="button" class="btn btn-secondary btn-sm" disabled>
                                        <i class="fas fa-ban"></i> Belum Siap
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-4 text-muted">
                <i class="fas fa-inbox fa-2x mb-2"></i>
                <p>Belum ada siswa eligible</p>
            </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card card-danger card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-users"></i> Daftar Siswa Tidak Eligible ({{ $snbpMenu->notEligibleSiswa->count() }})
                    </h3>
                </div>
                <div class="card-body p-0">
                    @if($snbpMenu->notEligibleSiswa->count() > 0)
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm table-striped mb-0">
                            <thead class="bg-danger text-white" style="position: sticky; top: 0;">
                                <tr>
                                    <th>#</th>
                                    <th>NISN</th>
                                    <th>Nama</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($snbpMenu->notEligibleSiswa as $index => $siswa)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><code>{{ $siswa->nisn }}</code></td>
                                    <td>{{ $siswa->nama_lengkap }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p>Belum ada siswa tidak eligible</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <a href="{{ route('admin.snbp-menu.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<style>
    .badge-lg {
        font-size: 1.2rem;
        padding: 0.5em 0.75em;
    }

    #eligibleSnbpTable code {
        font-size: 0.9rem;
    }

    #bulkCheckLog {
        max-height: 260px;
        overflow-y: auto;
        font-size: 0.9rem;
    }

    .dataTables_wrapper .dataTables_filter input,
    .dataTables_wrapper .dataTables_length select {
        border-radius: 0.35rem;
    }
</style>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
<div class="modal fade" id="bulkCheckProgressModal" tabindex="-1" role="dialog" aria-labelledby="bulkCheckProgressLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkCheckProgressLabel"><i class="fas fa-sync-alt"></i> Progress Cek Pengumuman SNBP</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="progress mb-3" style="height: 20px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" id="bulkCheckProgressBar" role="progressbar" style="width: 0%">0%</div>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="small text-muted" id="bulkCheckProgressText">Menyiapkan pengecekan...</span>
                    <span class="small font-weight-bold" id="bulkCheckProgressCount">0 / 0</span>
                </div>
                <div class="border rounded p-2 bg-light" id="bulkCheckLog"></div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {
        const eligibleTable = $('#eligibleSnbpTable').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 25,
            order: [
                [4, 'desc'],
                [2, 'asc']
            ],
            columnDefs: [
                { orderable: false, targets: [0, 12] }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
            }
        });

        const bulkModal = $('#bulkCheckProgressModal');
        const progressBar = $('#bulkCheckProgressBar');
        const progressText = $('#bulkCheckProgressText');
        const progressCount = $('#bulkCheckProgressCount');
        const progressLog = $('#bulkCheckLog');

        function escapeHtml(text) {
            return $('<div>').text(text ?? '').html();
        }

        function appendLog(message, type = 'secondary') {
            progressLog.append(
                '<div class="text-' + type + ' mb-1">• ' + escapeHtml(message) + '</div>'
            );
            progressLog.scrollTop(progressLog[0].scrollHeight);
        }

        function updateRow(row, result) {
            const badgeMap = {
                lulus: 'success',
                tidak_lulus: 'danger',
                gagal_cek: 'warning',
                belum_dicek: 'secondary',
            };

            row.find('.js-check-status')
                .removeClass('badge-success badge-danger badge-warning badge-secondary')
                .addClass('badge-' + (badgeMap[result.status] || 'secondary'))
                .text(result.status_label || 'Belum Dicek');
            row.find('.js-check-message').text(result.message || '-');
            row.find('.js-last-checked').text(result.checked_at || '-');
        }

        async function runCheck(row) {
            const canCheck = row.data('can-check') === 1 || row.data('can-check') === '1';
            const checkUrl = row.data('check-url');
            const siswaName = row.data('siswa-name');

            if (!canCheck || !checkUrl) {
                throw new Error('Data siswa belum siap dicek. Pastikan nomor pendaftaran dan tanggal lahir sudah lengkap.');
            }

            const button = row.find('.js-check-snbp');
            button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Cek');

            try {
                const response = await $.ajax({
                    url: checkUrl,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    }
                });

                updateRow(row, response.result);
                return {
                    success: true,
                    message: siswaName + ': ' + response.result.message,
                    result: response.result,
                };
            } catch (xhr) {
                const result = xhr.responseJSON?.result;
                const message = xhr.responseJSON?.message || 'Gagal menghubungi checker SNBP.';

                if (result) {
                    updateRow(row, result);
                }

                return {
                    success: false,
                    message: siswaName + ': ' + message,
                    result: result || null,
                };
            } finally {
                button.prop('disabled', false).html('<i class="fas fa-search"></i> Cek');
            }
        }

        $(document).on('click', '.js-check-snbp', async function () {
            const row = $(this).closest('tr');
            const result = await runCheck(row);

            Swal.fire({
                icon: result.success ? 'success' : 'warning',
                title: result.success ? 'Pengecekan Selesai' : 'Pengecekan Belum Berhasil',
                text: result.message,
            });
        });

        $('#bulkCheckSnbpBtn').on('click', async function () {
            const rows = $(eligibleTable.rows().nodes()).filter(function () {
                const row = $(this);
                return row.data('can-check') === 1 || row.data('can-check') === '1';
            });

            if (!rows.length) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tidak Ada Data Siap Cek',
                    text: 'Lengkapi nomor pendaftaran dan tanggal lahir terlebih dahulu.',
                });
                return;
            }

            progressLog.empty();
            progressBar.css('width', '0%').text('0%');
            progressText.text('Memulai pengecekan...');
            progressCount.text('0 / ' + rows.length);
            bulkModal.modal('show');

            let successCount = 0;

            for (let index = 0; index < rows.length; index++) {
                const row = $(rows[index]);
                const current = index + 1;
                const percent = Math.round((current / rows.length) * 100);

                progressText.text('Memproses ' + row.data('siswa-name') + '...');
                progressCount.text(current + ' / ' + rows.length);
                progressBar.css('width', percent + '%').text(percent + '%');

                const result = await runCheck(row);
                appendLog(result.message, result.success ? 'success' : 'warning');

                if (result.success) {
                    successCount++;
                }
            }

            progressText.text('Pengecekan selesai.');
            appendLog('Selesai. Berhasil memeriksa ' + successCount + ' dari ' + rows.length + ' siswa.', 'primary');
            eligibleTable.rows().invalidate().draw(false);
        });
    });
</script>
@stop

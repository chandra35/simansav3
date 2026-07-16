@extends('adminlte::page')

@section('title', 'Data Lulusan')

@section('content_header')
    <h1>Data Lulusan</h1>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Filter Rekap Lulusan</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Tahun Pelajaran</label>
                        <select id="filterTahunPelajaran" class="form-control">
                            @foreach($tahunPelajaranList as $tahun)
                                <option value="{{ $tahun->id }}" {{ optional($selectedTahun)->id === $tahun->id ? 'selected' : '' }}>
                                    {{ $tahun->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Status Pengisian</label>
                        <select id="filterStatusPengisian" class="form-control">
                            <option value="">Semua Status</option>
                            <option value="sudah_isi">Sudah Isi</option>
                            <option value="belum_isi">Belum Isi</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Mode Checker</label>
                        <select id="filterTrackerType" class="form-control">
                            <option value="ALL">Semua Jalur</option>
                            <option value="SNBP">SNBP</option>
                            <option value="SPAN-PTKIN">SPAN-PTKIN</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Jalur Masuk</label>
                        <select id="filterJalurMasuk" class="form-control">
                            <option value="">Semua Jalur</option>
                            @foreach($jalurMasukOptions as $jalur)
                                <option value="{{ $jalur }}">{{ $jalur }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Pencarian</label>
                        <input type="text" id="filterPencarian" class="form-control" placeholder="Nama, NISN, kampus, prodi">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="button" id="btnApplyFilter" class="btn btn-primary">
                <i class="fas fa-filter mr-1"></i> Terapkan Filter
            </button>
            <button type="button" id="btnResetFilter" class="btn btn-default">
                Reset
            </button>
            <button type="button" id="btnSendGraduationEmail" class="btn btn-info">
                <i class="fas fa-envelope mr-1"></i> Kirim Email Pengumuman
            </button>
            <div class="float-md-right mt-2 mt-md-0">
                <a href="#" id="btnExportExcel" class="btn btn-success" data-no-overlay>
                    <i class="fas fa-file-excel mr-1"></i> Export XLS
                </a>
                <a href="#" id="btnExportPdf" class="btn btn-danger" data-no-overlay target="_blank" rel="noopener">
                    <i class="fas fa-file-pdf mr-1"></i> Export PDF
                </a>
            </div>
        </div>
    </div>

    @if($selectedTahun)
        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="info-box bg-info">
                    <span class="info-box-icon"><i class="fas fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Kelas 12</span>
                        <span class="info-box-number" id="summaryTotal">0</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="info-box bg-success">
                    <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Sudah Mengisi</span>
                        <span class="info-box-number" id="summarySudahIsi">0</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="info-box bg-warning">
                    <span class="info-box-icon"><i class="fas fa-edit"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Belum Mengisi</span>
                        <span class="info-box-number" id="summaryBelumIsi">0</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="info-box bg-primary">
                    <span class="info-box-icon"><i class="fas fa-university"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Universitas Tujuan</span>
                        <span class="info-box-number" id="summaryUniversitas">0</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="info-box bg-teal">
                        <span class="info-box-icon"><i class="fas fa-user-check"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text" id="summaryEligibleLabel">Eligible SNBP</span>
                        <span class="info-box-number" id="summaryEligible">0</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="info-box bg-secondary">
                        <span class="info-box-icon"><i class="fas fa-id-card"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text" id="summaryEligibleIsiLabel">Sudah Isi Nomor SNBP</span>
                        <span class="info-box-number" id="summaryEligibleIsi">0</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="info-box bg-success">
                        <span class="info-box-icon"><i class="fas fa-award"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text" id="summaryEligibleLulusLabel">Lulus SNBP</span>
                        <span class="info-box-number" id="summaryEligibleLulus">0</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="info-box bg-danger">
                        <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text" id="summaryEligibleTidakLulusLabel">Tidak Lulus SNBP</span>
                        <span class="info-box-number" id="summaryEligibleTidakLulus">0</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="info-box bg-warning">
                        <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text" id="summaryEligibleGagalLabel">Gagal Cek SNBP</span>
                        <span class="info-box-number" id="summaryEligibleGagal">0</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="info-box bg-dark">
                        <span class="info-box-icon"><i class="fas fa-hourglass-half"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text" id="summaryEligibleBelumDicekLabel">Belum Dicek SNBP</span>
                        <span class="info-box-number" id="summaryEligibleBelumDicek">0</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4">
                <div class="card card-outline card-success h-100">
                    <div class="card-header">
                        <h3 class="card-title">Statistik per Jalur</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Jalur</th>
                                    <th class="text-right">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody id="perJalurTable">
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-3">Memuat statistik...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card card-outline card-secondary h-100">
                    <div class="card-header">
                        <h3 class="card-title" id="checkerStatusTitle">Status Checker SNBP</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th class="text-right">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody id="checkerStatusTable">
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-3">Memuat statistik...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card card-outline card-primary h-100">
                    <div class="card-header">
                        <h3 class="card-title" id="topTrackerUniversityTitle">Top PTN Diterima SNBP</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Perguruan Tinggi</th>
                                    <th class="text-right">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody id="topPtnSnbpTable">
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-3">Memuat statistik...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="card card-outline card-info h-100">
                    <div class="card-header">
                        <h3 class="card-title">Top Universitas Tujuan</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Universitas</th>
                                    <th class="text-right">Jumlah Siswa</th>
                                </tr>
                            </thead>
                            <tbody id="topUniversitasTable">
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-3">Memuat statistik...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card card-outline card-warning h-100">
                    <div class="card-header">
                        <h3 class="card-title" id="topTrackerProgramTitle">Top Prodi Diterima SNBP</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Program Studi</th>
                                    <th class="text-right">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody id="topProdiSnbpTable">
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-3">Memuat statistik...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Daftar Lulusan Kelas 12</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="lulusanTable" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>NISN</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Status</th>
                                <th>Jalur</th>
                                <th id="checkerResultLabel">Hasil SNBP</th>
                                <th id="checkerColumnLabel">Checker SNBP</th>
                                <th>Universitas</th>
                                <th>Jurusan/Fakultas</th>
                                <th>Program Studi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

        <div class="card card-outline card-warning">
            <div class="card-header">
                <h3 class="card-title">Matriks Laporan per Kelas dan Jalur</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-bordered table-sm mb-0" id="matrixTable">
                    <thead>
                        <tr>
                            <th>Kelas</th>
                            @foreach($jalurMasukOptions as $jalur)
                                <th class="text-center">{{ $jalur }}</th>
                            @endforeach
                            <th class="text-center">Eligible</th>
                            <th class="text-center" id="matrixTrackerLabel">Lulus SNBP</th>
                            <th class="text-center">Tidak Lulus</th>
                            <th class="text-center">Sudah Isi</th>
                            <th class="text-center">Belum Isi</th>
                            <th class="text-center">Total</th>
                        </tr>
                    </thead>
                    <tbody id="matrixTableBody">
                        <tr>
                            <td colspan="{{ count($jalurMasukOptions) + 6 }}" class="text-center text-muted py-3">Memuat matriks laporan...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="alert alert-warning">
            Belum ada tahun pelajaran yang tersedia.
        </div>
    @endif

    <div class="modal fade" id="graduationEmailModal" tabindex="-1" role="dialog" aria-labelledby="graduationEmailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title" id="graduationEmailModalLabel">
                        <i class="fas fa-envelope mr-1"></i> Kirim Email Pengumuman Kelulusan
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-light border">
                        Email akan dikirim ke siswa kelas 12 sesuai filter aktif dan memakai template email <code>graduation_announcement</code>.
                        Jika admin mengubah template di menu <strong>Template Email</strong>, isi email berikutnya akan ikut berubah.
                    </div>
                    <div class="form-group">
                        <label for="graduationEmailNote">Catatan Admin Tambahan</label>
                        <textarea id="graduationEmailNote" class="form-control" rows="5" placeholder="Tambahkan informasi tambahan untuk disisipkan ke placeholder [catatan_admin]."></textarea>
                        <small class="form-text text-muted">
                            Catatan ini opsional. Jika kosong, sistem akan memakai catatan default yang sopan dan informatif.
                        </small>
                    </div>
                    <div class="small text-muted" id="graduationEmailFilterSummary"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-info" id="btnSubmitGraduationEmail">
                        <i class="fas fa-paper-plane mr-1"></i> Kirim Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="//cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
    <style>
        #matrixTable th,
        #matrixTable td {
            white-space: nowrap;
            vertical-align: middle;
        }
    </style>
@stop

@section('js')
    <script src="//cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="//cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
    <script>
        let lulusanTable;
        const jalurMasukOptions = @json($jalurMasukOptions);
        const defaultTrackerMeta = {
            summary_total_label: 'Peserta Checker',
            summary_number_label: 'Sudah Ada Nomor',
            summary_passed_label: 'Lulus Checker',
            summary_failed_label: 'Tidak Lulus Checker',
            summary_error_label: 'Gagal Cek',
            summary_pending_label: 'Belum Dicek',
            checker_title: 'Status Checker Semua Jalur',
            top_university_title: 'Top Kampus Diterima Checker',
            top_program_title: 'Top Prodi Diterima Checker',
            checker_column_label: 'Checker',
            result_column_label: 'Hasil Checker',
            matrix_tracker_label: 'Lulus Checker',
            empty_university_text: 'Belum ada siswa diterima via checker.',
            empty_program_text: 'Belum ada prodi dari checker.',
            type: 'Semua Jalur'
        };

        function getFilters() {
            return {
                tahun_pelajaran_id: $('#filterTahunPelajaran').val(),
                status_pengisian: $('#filterStatusPengisian').val(),
                tracker_type: $('#filterTrackerType').val(),
                jalur_masuk: $('#filterJalurMasuk').val(),
                q: $('#filterPencarian').val()
            };
        }

        function updateExportLinks() {
            const params = new URLSearchParams(getFilters()).toString();
            $('#btnExportExcel').attr('href', `{{ route('admin.lulusan.export-excel') }}?${params}`);
            $('#btnExportPdf').attr('href', `{{ route('admin.lulusan.export-pdf') }}?${params}`);
        }

        function parseDownloadFilename(disposition, fallback) {
            if (!disposition) return fallback;

            const utfMatch = disposition.match(/filename\*=UTF-8''([^;]+)/i);
            if (utfMatch && utfMatch[1]) {
                try {
                    return decodeURIComponent(utfMatch[1].replace(/"/g, ''));
                } catch (e) {
                    return utfMatch[1].replace(/"/g, '');
                }
            }

            const regularMatch = disposition.match(/filename="?([^";]+)"?/i);
            return regularMatch && regularMatch[1] ? regularMatch[1] : fallback;
        }

        async function downloadLulusanExcel(url) {
            const $btn = $('#btnExportExcel');
            const originalHtml = $btn.html();

            if (window.showAppGlobalOverlay) {
                window.showAppGlobalOverlay('Menyiapkan export XLS...', 'File sedang dibuat, mohon tunggu');
            }

            $btn.addClass('disabled').attr('aria-disabled', 'true')
                .html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyiapkan...');

            try {
                const response = await fetch(url, {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const blob = await response.blob();
                const firstBytes = new Uint8Array(await blob.slice(0, 4).arrayBuffer());
                const isXlsx = firstBytes[0] === 0x50 && firstBytes[1] === 0x4B && firstBytes[2] === 0x03 && firstBytes[3] === 0x04;

                if (!response.ok || !isXlsx) {
                    let detail = `HTTP ${response.status}`;
                    try {
                        const text = await blob.text();
                        const cleaned = text.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
                        if (cleaned) {
                            detail = cleaned.substring(0, 300);
                        }
                    } catch (e) {
                        // Keep default detail.
                    }

                    throw new Error(detail || 'Server tidak mengirim file XLSX yang valid.');
                }

                const filename = parseDownloadFilename(
                    response.headers.get('Content-Disposition'),
                    `laporan_lulusan_${new Date().toISOString().slice(0, 10)}.xlsx`
                );
                const blobUrl = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = blobUrl;
                link.download = filename.endsWith('.xlsx') ? filename : `${filename}.xlsx`;
                document.body.appendChild(link);
                link.click();
                link.remove();

                setTimeout(() => URL.revokeObjectURL(blobUrl), 30000);
            } catch (error) {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Export XLS gagal',
                        text: error.message || 'File export tidak valid.'
                    });
                } else {
                    alert(error.message || 'Export XLS gagal.');
                }
            } finally {
                if (window.hideAppGlobalOverlay) {
                    window.hideAppGlobalOverlay();
                }

                $btn.removeClass('disabled').removeAttr('aria-disabled').html(originalHtml);
            }
        }

        function updateGraduationEmailSummary() {
            const filters = getFilters();
            const summary = [
                `Tahun: ${$('#filterTahunPelajaran option:selected').text() || '-'}`,
                `Status: ${$('#filterStatusPengisian option:selected').text() || 'Semua Status'}`,
                `Checker: ${$('#filterTrackerType option:selected').text() || 'Semua Jalur'}`,
                `Jalur: ${$('#filterJalurMasuk option:selected').text() || 'Semua Jalur'}`,
                `Pencarian: ${filters.q || '-'}`
            ];

            $('#graduationEmailFilterSummary').text(summary.join(' | '));
        }

        function renderPerJalur(perJalur) {
            const tbody = $('#perJalurTable');
            tbody.empty();

            if (!perJalur || Object.keys(perJalur).length === 0) {
                tbody.html('<tr><td colspan="2" class="text-center text-muted py-3">Tidak ada data.</td></tr>');
                return;
            }

            Object.entries(perJalur).forEach(([jalur, jumlah]) => {
                tbody.append(`
                    <tr>
                        <td>${jalur}</td>
                        <td class="text-right font-weight-bold">${jumlah}</td>
                    </tr>
                `);
            });
        }

        function renderTopUniversitas(topUniversitas) {
            const tbody = $('#topUniversitasTable');
            tbody.empty();

            if (!topUniversitas || topUniversitas.length === 0) {
                tbody.html('<tr><td colspan="2" class="text-center text-muted py-3">Belum ada data universitas.</td></tr>');
                return;
            }

            topUniversitas.forEach(item => {
                const label = item.label ?? item.nama_universitas ?? '-';
                tbody.append(`
                    <tr>
                        <td>${label}</td>
                        <td class="text-right font-weight-bold">${item.jumlah ?? 0}</td>
                    </tr>
                `);
            });
        }

        function renderCheckerStatus(checkerStatus) {
            const tbody = $('#checkerStatusTable');
            tbody.empty();

            const labels = {
                belum_dicek: 'Belum Dicek',
                lulus: 'Lulus',
                tidak_lulus: 'Tidak Lulus',
                gagal_cek: 'Gagal Cek'
            };

            if (!checkerStatus || Object.keys(checkerStatus).length === 0) {
                tbody.html('<tr><td colspan="2" class="text-center text-muted py-3">Tidak ada data.</td></tr>');
                return;
            }

            Object.entries(labels).forEach(([key, label]) => {
                tbody.append(`
                    <tr>
                        <td>${label}</td>
                        <td class="text-right font-weight-bold">${checkerStatus[key] ?? 0}</td>
                    </tr>
                `);
            });
        }

        function renderTopSimpleTable(selector, rows, emptyText = 'Belum ada data.') {
            const tbody = $(selector);
            tbody.empty();

            if (!rows || rows.length === 0) {
                tbody.html(`<tr><td colspan="2" class="text-center text-muted py-3">${emptyText}</td></tr>`);
                return;
            }

            rows.forEach(item => {
                const label = item.label ?? item.nama_universitas ?? item.program_studi ?? '-';
                tbody.append(`
                    <tr>
                        <td>${label}</td>
                        <td class="text-right font-weight-bold">${item.jumlah ?? 0}</td>
                    </tr>
                `);
            });
        }

        function applyTrackerMeta(meta) {
            const trackerMeta = Object.assign({}, defaultTrackerMeta, meta || {});
            $('#summaryEligibleLabel').text(trackerMeta.summary_total_label);
            $('#summaryEligibleIsiLabel').text(trackerMeta.summary_number_label);
            $('#summaryEligibleLulusLabel').text(trackerMeta.summary_passed_label);
            $('#summaryEligibleTidakLulusLabel').text(trackerMeta.summary_failed_label);
            $('#summaryEligibleGagalLabel').text(trackerMeta.summary_error_label);
            $('#summaryEligibleBelumDicekLabel').text(trackerMeta.summary_pending_label);
            $('#checkerStatusTitle').text(trackerMeta.checker_title);
            $('#topTrackerUniversityTitle').text(trackerMeta.top_university_title);
            $('#topTrackerProgramTitle').text(trackerMeta.top_program_title);
            $('#checkerColumnLabel').text(trackerMeta.checker_column_label);
            $('#checkerResultLabel').text(trackerMeta.result_column_label);
            $('#matrixTrackerLabel').text(trackerMeta.matrix_tracker_label);
        }

        function renderMatrix(perKelas) {
            const tbody = $('#matrixTableBody');
            tbody.empty();

            if (!perKelas || perKelas.length === 0) {
                tbody.html(`<tr><td colspan="${jalurMasukOptions.length + 7}" class="text-center text-muted py-3">Tidak ada data untuk filter ini.</td></tr>`);
                return;
            }

            perKelas.forEach(item => {
                const jalurCells = jalurMasukOptions.map(jalur => `<td class="text-center">${item.jalur[jalur] ?? 0}</td>`).join('');

                tbody.append(`
                    <tr>
                        <td>${item.kelas_nama}</td>
                        ${jalurCells}
                        <td class="text-center font-weight-bold text-info">${item.eligible ?? 0}</td>
                        <td class="text-center font-weight-bold text-primary">${item.eligible_lulus ?? 0}</td>
                        <td class="text-center font-weight-bold text-danger">${item.eligible_tidak_lulus ?? 0}</td>
                        <td class="text-center font-weight-bold text-success">${item.sudah_isi}</td>
                        <td class="text-center font-weight-bold text-warning">${item.belum_isi}</td>
                        <td class="text-center font-weight-bold">${item.total}</td>
                    </tr>
                `);
            });
        }

        function loadStats() {
            $.ajax({
                url: '{{ route('admin.lulusan.stats') }}',
                data: getFilters(),
                success: function(response) {
                    const trackerMeta = response.tracker_meta || defaultTrackerMeta;
                    applyTrackerMeta(trackerMeta);
                    $('#summaryTotal').text(response.summary.total ?? 0);
                    $('#summarySudahIsi').text(response.summary.sudah_isi ?? 0);
                    $('#summaryBelumIsi').text(response.summary.belum_isi ?? 0);
                    $('#summaryUniversitas').text(response.summary.total_universitas ?? 0);
                    $('#summaryEligible').text(response.summary.eligible_total ?? 0);
                    $('#summaryEligibleIsi').text(response.summary.eligible_sudah_isi_nomor ?? 0);
                    $('#summaryEligibleLulus').text(response.summary.eligible_lulus ?? 0);
                    $('#summaryEligibleTidakLulus').text(response.summary.eligible_tidak_lulus ?? 0);
                    $('#summaryEligibleGagal').text(response.summary.eligible_gagal_cek ?? 0);
                    $('#summaryEligibleBelumDicek').text(response.summary.eligible_belum_dicek ?? 0);

                    renderPerJalur(response.per_jalur);
                    renderTopUniversitas(response.top_universitas);
                    renderCheckerStatus(response.checker_status);
                    renderTopSimpleTable('#topPtnSnbpTable', response.top_tracker_universitas, trackerMeta.empty_university_text || defaultTrackerMeta.empty_university_text);
                    renderTopSimpleTable('#topProdiSnbpTable', response.top_tracker_prodi, trackerMeta.empty_program_text || defaultTrackerMeta.empty_program_text);
                    renderMatrix(response.per_kelas);
                },
                error: function() {
                    applyTrackerMeta(defaultTrackerMeta);
                    $('#summaryTotal, #summarySudahIsi, #summaryBelumIsi, #summaryUniversitas, #summaryEligible, #summaryEligibleIsi, #summaryEligibleLulus, #summaryEligibleTidakLulus, #summaryEligibleGagal, #summaryEligibleBelumDicek').text('0');
                    renderPerJalur({});
                    renderTopUniversitas([]);
                    renderCheckerStatus({});
                    renderTopSimpleTable('#topPtnSnbpTable', []);
                    renderTopSimpleTable('#topProdiSnbpTable', []);
                    renderMatrix([]);
                }
            });
        }

        $(function () {
            lulusanTable = $('#lulusanTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: '{{ route('admin.lulusan.data') }}',
                    data: function (d) {
                        Object.assign(d, getFilters());
                    }
                },
                columns: [
                    { data: 'nisn', name: 'siswa.nisn' },
                    { data: 'nama_lengkap', name: 'siswa.nama_lengkap' },
                    { data: 'kelas_nama', name: 'kelas.nama_kelas' },
                    { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false },
                    { data: 'jalur_badge', name: 'siswa_lulusan.jalur_masuk', orderable: false, searchable: false },
                    { data: 'result_badge', name: 'result_badge', orderable: false, searchable: false },
                    { data: 'checker_badge', name: 'checker_badge', orderable: false, searchable: false },
                    { data: 'nama_universitas', name: 'siswa_lulusan.nama_universitas' },
                    { data: 'jurusan_fakultas', name: 'siswa_lulusan.jurusan_fakultas' },
                    { data: 'program_studi', name: 'siswa_lulusan.program_studi' }
                ],
                order: [[1, 'asc']],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json'
                }
            });

            $('#btnApplyFilter').on('click', function () {
                lulusanTable.search($('#filterPencarian').val()).draw();
                updateExportLinks();
                loadStats();
            });

            $('#btnResetFilter').on('click', function () {
                $('#filterStatusPengisian').val('');
                $('#filterTrackerType').val('ALL');
                $('#filterJalurMasuk').val('');
                $('#filterPencarian').val('');
                lulusanTable.search('').ajax.reload();
                updateExportLinks();
                loadStats();
            });

            $('#filterPencarian').on('keyup', function (e) {
                if (e.key === 'Enter') {
                    lulusanTable.search(this.value).draw();
                    updateExportLinks();
                    loadStats();
                }
            });

            $('#filterJalurMasuk').on('change', function () {
                const selectedJalur = $(this).val();
                if (selectedJalur === 'SNBP' || selectedJalur === 'SPAN-PTKIN') {
                    $('#filterTrackerType').val(selectedJalur);
                }
                updateExportLinks();
            });

            $('#filterTahunPelajaran, #filterStatusPengisian, #filterTrackerType').on('change', function () {
                updateExportLinks();
            });

            $('#btnExportExcel').on('click', function (e) {
                e.preventDefault();
                const url = $(this).attr('href');
                if (!url || url === '#') {
                    updateExportLinks();
                }
                downloadLulusanExcel($(this).attr('href'));
            });

            $('#btnSendGraduationEmail').on('click', function () {
                updateGraduationEmailSummary();
                $('#graduationEmailModal').modal('show');
            });

            $('#btnSubmitGraduationEmail').on('click', function () {
                const filters = getFilters();
                const note = $('#graduationEmailNote').val();
                const $btn = $(this);

                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Mengirim...');

                $.ajax({
                    url: '{{ route('admin.lulusan.send-graduation-emails') }}',
                    method: 'POST',
                    data: {
                        ...filters,
                        catatan_admin: note,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        $('#graduationEmailModal').modal('hide');

                        const stats = response.stats || {};
                        let html = `
                            <div class="text-left">
                                <p class="mb-2">Proses kirim email selesai.</p>
                                <ul class="mb-0 pl-3">
                                    <li>Total target: ${stats.total ?? 0}</li>
                                    <li>Berhasil: ${stats.sent ?? 0}</li>
                                    <li>Gagal: ${stats.failed ?? 0}</li>
                                    <li>Dilewati: ${stats.skipped ?? 0}</li>
                                </ul>
                            </div>
                        `;

                        if (response.failures && response.failures.length) {
                            html += '<hr><div class="text-left"><strong>Contoh kegagalan:</strong><ul class="mb-0 pl-3">';
                            response.failures.forEach(item => {
                                html += `<li>${item.nama} (${item.email}): ${item.message}</li>`;
                            });
                            html += '</ul></div>';
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Email Diproses',
                            html: html
                        });
                    },
                    error: function (xhr) {
                        const message = xhr.responseJSON?.message || 'Gagal memproses pengiriman email pengumuman kelulusan.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: message
                        });
                    },
                    complete: function () {
                        $btn.prop('disabled', false).html('<i class="fas fa-paper-plane mr-1"></i> Kirim Sekarang');
                    }
                });
            });

            updateExportLinks();
            loadStats();
        });
    </script>
@stop

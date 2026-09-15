@extends('adminlte::page')

@section('title', 'Laporan Absensi Siswa')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-file-signature text-primary mr-2"></i>Laporan Absensi Siswa</h1>
            <p class="text-muted mb-0">Pilih rombel lalu cetak laporan harian atau rekap bulanan tanpa reload halaman.</p>
        </div>
        <a href="{{ route('admin.absensi-siswa.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i>Kembali</a>
    </div>
@stop

@section('content')
    @php($levels = $allowedClasses->pluck('tingkat')->unique()->sort()->values())
    <section class="card card-outline card-primary report-filter">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="card-title"><i class="fas fa-sliders-h mr-2"></i>Atur Laporan</h3>
            <span class="badge badge-light">Filter live</span>
        </div>
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label for="reportLevel">Tingkat</label>
                    <select id="reportLevel" class="form-control">
                        <option value="">Semua tingkat</option>
                        @foreach ($levels as $level)
                            <option value="{{ $level }}" @selected((string) $tingkat === (string) $level)>Tingkat {{ $level }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="reportPeriod">Periode</label>
                    <select id="reportPeriod" class="form-control">
                        <option value="bulan" @selected($periode === 'bulan')>Bulanan detail</option>
                        <option value="hari" @selected($periode === 'hari')>Harian</option>
                    </select>
                </div>
                <div class="col-md-3"><label for="reportDate">Tanggal</label><input id="reportDate" type="date" value="{{ $start->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}" class="form-control"></div>
                <div class="col-md-3"><label for="reportMonth">Bulan</label><input id="reportMonth" type="month" value="{{ $bulan }}" max="{{ now()->format('Y-m') }}" class="form-control"></div>
            </div>

            <div class="report-selection mt-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <strong>Pilih rombel</strong>
                        <small class="text-muted d-block">Checklist langsung memperbarui dokumen yang akan dicetak.</small>
                    </div>
                    <label class="report-select-all mb-0">
                        <input type="checkbox" id="reportSelectAll">
                        <strong>Pilih semua</strong>
                    </label>
                </div>
                <div id="reportClassList" class="class-checklist" aria-live="polite">
                    @forelse ($scopedClasses as $kelas)
                        <label class="report-class-item">
                            <input type="checkbox" value="{{ $kelas->id }}" class="report-class-check">
                            <span>Tingkat {{ $kelas->tingkat }} &middot; {{ $kelas->nama_kelas }}</span>
                        </label>
                    @empty
                        <span class="text-muted">Tidak ada rombel yang dapat diakses.</span>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <div id="reportNotice" class="alert alert-info mb-3">
        <i class="fas fa-info-circle mr-1"></i><strong><span id="reportSelectedCount">0</span> rombel</strong> terpilih.
        <span id="reportNoticeText">Checklist rombel untuk mengaktifkan cetak PDF.</span>
    </div>

    <section class="card report-ready-card">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h3 class="h5 mb-1">Dokumen siap dicetak</h3>
                <p class="text-muted mb-0">Satu rombel atau beberapa rombel yang dipilih akan dimuat ke PDF.</p>
            </div>
            <a id="reportPrintButton" target="_blank" rel="noopener" href="#" class="btn btn-primary disabled" aria-disabled="true">
                <i class="fas fa-file-pdf mr-1"></i>Cetak PDF
            </a>
        </div>
    </section>
@stop

@section('css')
    <style>
        .report-filter, .report-ready-card { border-radius: 14px; }
        .report-selection { border-top: 1px solid #e7edf6; padding-top: 1rem; }
        .class-checklist { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .55rem; padding: .8rem; border: 1px solid #dbe4f0; border-radius: 10px; background: #f8fafc; max-height: 240px; overflow: auto; }
        .report-class-item { margin: 0; padding: .55rem .6rem; border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; font-size: .84rem; cursor: pointer; }
        .report-class-item:has(input:checked) { border-color: #6d5dfc; background: #f3f1ff; color: #382fc4; }
        .report-class-item input { margin-right: .3rem; }
        .report-class-loading { grid-column: 1 / -1; padding: .7rem; color: #64748b; }
        @media (max-width: 767px) { .class-checklist { grid-template-columns: 1fr; } .report-ready-card .card-body { align-items: stretch; flex-direction: column; gap: 1rem; } }
    </style>
@stop

@section('js')
    <script>
        $(function () {
            const classesUrl = @json(route('admin.absensi-siswa.report.classes'));
            const printUrl = @json(route('admin.absensi-siswa.report.print'));
            const level = $('#reportLevel');
            const period = $('#reportPeriod');
            const date = $('#reportDate');
            const month = $('#reportMonth');
            const classList = $('#reportClassList');
            const all = $('#reportSelectAll');
            const count = $('#reportSelectedCount');
            const notice = $('#reportNotice');
            const noticeText = $('#reportNoticeText');
            const printButton = $('#reportPrintButton');

            function checks() { return classList.find('.report-class-check'); }
            function selectedIds() { return checks().filter(':checked').map(function () { return this.value; }).get(); }
            function escapeHtml(value) { return $('<div>').text(value).html(); }

            function syncPrintButton() {
                const ids = selectedIds();
                const isMonthly = period.val() === 'bulan';
                const isTooMany = isMonthly && ids.length > 12;
                count.text(ids.length);
                all.prop('checked', checks().length > 0 && ids.length === checks().length);
                all.prop('indeterminate', ids.length > 0 && ids.length < checks().length);

                if (!ids.length) {
                    notice.removeClass('alert-warning').addClass('alert-info');
                    noticeText.text('Checklist rombel untuk mengaktifkan cetak PDF.');
                    printButton.addClass('disabled').attr({'aria-disabled': 'true', 'href': '#'});
                    return;
                }
                if (isTooMany) {
                    notice.removeClass('alert-info').addClass('alert-warning');
                    noticeText.text('Cetak bulanan maksimal 12 rombel per dokumen. Kurangi pilihan atau cetak per tingkat.');
                    printButton.addClass('disabled').attr({'aria-disabled': 'true', 'href': '#'});
                    return;
                }

                const params = new URLSearchParams({
                    periode: period.val(),
                    tanggal: date.val(),
                    bulan: month.val(),
                    rombel_filter: '1',
                    _ts: String(Date.now())
                });
                if (level.val()) params.set('tingkat', level.val());
                ids.forEach(id => params.append('kelas_ids[]', id));
                notice.removeClass('alert-warning').addClass('alert-info');
                noticeText.text('Dokumen akan memuat hanya rombel yang dicentang.');
                printButton.removeClass('disabled').attr({'aria-disabled': 'false', 'href': printUrl + '?' + params.toString()});
            }

            function bindClassEvents() {
                checks().on('change', syncPrintButton);
                syncPrintButton();
            }

            function loadClasses() {
                const selectedLevel = level.val();
                classList.html('<div class="report-class-loading"><i class="fas fa-spinner fa-spin mr-1"></i>Memuat rombel...</div>');
                all.prop('checked', false).prop('indeterminate', false);
                $.getJSON(classesUrl, selectedLevel ? { tingkat: selectedLevel } : {})
                    .done(function (response) {
                        const rows = response.classes || [];
                        if (!rows.length) {
                            classList.html('<span class="text-muted">Tidak ada rombel yang dapat diakses.</span>');
                        } else {
                            classList.html(rows.map(function (item) {
                                return '<label class="report-class-item"><input type="checkbox" value="' + escapeHtml(item.id) + '" class="report-class-check"><span>Tingkat ' + escapeHtml(item.tingkat) + ' &middot; ' + escapeHtml(item.nama) + '</span></label>';
                            }).join(''));
                        }
                        bindClassEvents();
                    })
                    .fail(function () {
                        classList.html('<span class="text-danger">Rombel gagal dimuat. Silakan muat ulang halaman.</span>');
                        syncPrintButton();
                    });
            }

            all.on('change', function () { checks().prop('checked', this.checked); syncPrintButton(); });
            level.on('change', loadClasses);
            period.add(date).add(month).on('change input', syncPrintButton);
            printButton.on('click', function (event) { if ($(this).hasClass('disabled')) event.preventDefault(); });
            bindClassEvents();
        });
    </script>
@stop

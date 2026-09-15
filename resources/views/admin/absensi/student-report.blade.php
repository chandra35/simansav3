@extends('adminlte::page')

@section('title', 'Laporan Absensi Siswa')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-file-signature text-primary mr-2"></i>Laporan Absensi Siswa</h1>
            <p class="text-muted mb-0">Cetak laporan harian atau rekap detail bulanan per rombel.</p>
        </div>
        <a href="{{ route('admin.absensi-siswa.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i>Kembali
        </a>
    </div>
@stop

@section('content')
    @if (session('toastr_error'))
        <div class="alert alert-warning"><i class="fas fa-exclamation-triangle mr-1"></i>{{ session('toastr_error') }}</div>
    @endif

    @php($levels = $allowedClasses->pluck('tingkat')->unique()->sort()->values())

    <section class="card card-outline card-primary report-filter">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-sliders-h mr-2"></i>Atur Laporan</h3></div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.absensi-siswa.report') }}">
                <input type="hidden" name="rombel_filter" value="1">
                <div class="row align-items-end">
                    <div class="col-md-2">
                        <label>Tingkat</label>
                        <select name="tingkat" class="form-control">
                            <option value="">Semua tingkat</option>
                            @foreach ($levels as $level)
                                <option value="{{ $level }}" @selected((string) $tingkat === (string) $level)>Tingkat {{ $level }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Periode</label>
                        <select name="periode" class="form-control">
                            <option value="bulan" @selected($periode === 'bulan')>Bulanan detail</option>
                            <option value="hari" @selected($periode === 'hari')>Harian</option>
                        </select>
                    </div>
                    <div class="col-md-3"><label>Tanggal</label><input type="date" name="tanggal" value="{{ $start->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}" class="form-control"></div>
                    <div class="col-md-2"><label>Bulan</label><input type="month" name="bulan" value="{{ $bulan }}" max="{{ now()->format('Y-m') }}" class="form-control"></div>
                    <div class="col-md-3"><button class="btn btn-primary btn-block"><i class="fas fa-sync-alt mr-1"></i>Tampilkan</button></div>
                </div>

                <div class="mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="mb-0">Pilih rombel <small class="text-muted">(wajib dipilih)</small></label>
                        <label class="report-select-all mb-0">
                            <input type="checkbox" id="reportSelectAll" @checked($scopedClasses->count() > 0 && $classes->count() === $scopedClasses->count())>
                            <strong>Pilih semua</strong>
                            <span class="text-muted">(<span id="reportSelectedCount">{{ $classes->count() }}</span> dipilih)</span>
                        </label>
                    </div>
                    <div class="class-checklist">
                        @forelse ($scopedClasses as $kelas)
                            <label>
                                <input type="checkbox" name="kelas_ids[]" value="{{ $kelas->id }}" class="report-class-check" @checked($classes->contains('id', $kelas->id))>
                                <span>Tingkat {{ $kelas->tingkat }} &middot; {{ $kelas->nama_kelas }}</span>
                            </label>
                        @empty
                            <span class="text-muted">Tidak ada rombel yang dapat diakses pada tingkat ini.</span>
                        @endforelse
                    </div>
                    <small class="form-text text-muted mt-2"><i class="fas fa-info-circle mr-1"></i>Hanya rombel yang dicentang yang akan dimuat dan dicetak.</small>
                </div>
            </form>
        </div>
    </section>

    <div class="alert alert-info">
        <i class="fas fa-info-circle mr-1"></i>
        <strong>{{ $classes->count() }} rombel</strong> terpilih &middot;
        {{ $periode === 'bulan' ? 'laporan memuat kolom setiap tanggal dalam bulan.' : 'laporan memuat status seluruh siswa pada tanggal terpilih.' }}
    </div>

    <section class="card">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h3 class="h5 mb-1">Dokumen siap dicetak</h3>
                <p class="text-muted mb-0">Data diambil dari absensi siswa dan dibatasi oleh akses admin.</p>
            </div>
            @if ($classes->isNotEmpty())
                <a target="_blank" rel="noopener" href="{{ route('admin.absensi-siswa.report.print', array_filter(['periode' => $periode, 'tanggal' => $start->format('Y-m-d'), 'bulan' => $periode === 'bulan' ? $bulan : null, 'tingkat' => $tingkat, 'rombel_filter' => 1, 'kelas_ids' => $classes->pluck('id')->all(), '_ts' => now()->timestamp])) }}" class="btn btn-primary">
                    <i class="fas fa-file-pdf mr-1"></i>Cetak PDF
                </a>
            @else
                <button type="button" class="btn btn-secondary" disabled><i class="fas fa-check-square mr-1"></i>Pilih rombel terlebih dahulu</button>
            @endif
        </div>
    </section>
@stop

@section('css')
    <style>
        .report-filter { border-radius: 14px; }
        .class-checklist { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .55rem; padding: .8rem; border: 1px solid #dbe4f0; border-radius: 10px; background: #f8fafc; max-height: 190px; overflow: auto; }
        .class-checklist label { margin: 0; padding: .45rem .55rem; border: 1px solid #e2e8f0; border-radius: 7px; background: #fff; font-size: .82rem; }
        @media (max-width: 767px) { .class-checklist { grid-template-columns: 1fr; } .card-body.d-flex { align-items: stretch; flex-direction: column; gap: 1rem; } }
    </style>
@stop

@section('js')
    <script>
        $(function () {
            const checks = $('.report-class-check');
            const all = $('#reportSelectAll');
            const count = $('#reportSelectedCount');
            function sync() {
                const selected = checks.filter(':checked').length;
                count.text(selected);
                all.prop('checked', checks.length > 0 && selected === checks.length);
                all.prop('indeterminate', selected > 0 && selected < checks.length);
            }
            all.on('change', function () { checks.prop('checked', this.checked); sync(); });
            checks.on('change', sync);
            sync();
        });
    </script>
@stop

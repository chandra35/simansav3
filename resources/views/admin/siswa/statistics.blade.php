@extends('adminlte::page')

@section('title', 'Statistik Siswa - SIMANSA')

@php
    $selectedClass = $kelasId ? $classes->firstWhere('id', $kelasId) : null;
    $scopeLabel = $selectedClass ? $selectedClass->nama_kelas.$selectedClass->asrama_suffix : ($tingkat ? 'Tingkat '.($tingkat === 10 ? 'X' : ($tingkat === 11 ? 'XI' : 'XII')) : 'Semua siswa');
    $listUrl = fn (array $extra = []) => route('admin.siswa.index', array_merge($filterQuery, $extra));
    $schoolStudentsUrl = fn (array $school) => route('admin.siswa.index', array_filter([
        'tingkat' => $tingkat,
        'school_npsn' => $school['npsn'] ?: null,
        'school_name' => blank($school['npsn']) ? $school['school_name'] : null,
    ], fn ($value) => filled($value)));
@endphp

@section('content_header')
    <div class="simansa-stat-hero">
        <div class="simansa-stat-hero__main">
            <div class="simansa-stat-hero__eyebrow">
                <i class="fas fa-chart-pie"></i>
                Manajemen Data
            </div>
            <h1 class="simansa-stat-hero__title">Statistik Siswa</h1>
            <p class="simansa-stat-hero__subtitle">
                Pantau kelengkapan biodata, status login, sebaran domisili, dan sebaran asal sekolah dari satu tampilan analitik.
            </p>
        </div>
        <div class="simansa-stat-hero__meta">
            <div class="simansa-stat-hero-chip">
                <span class="simansa-stat-hero-chip__label">Sudah Login</span>
                <span class="simansa-stat-hero-chip__value">{{ number_format($kpi['sudah_login']) }}</span>
            </div>
            <div class="simansa-stat-hero-chip">
                <span class="simansa-stat-hero-chip__label">Belum Pernah Login</span>
                <span class="simansa-stat-hero-chip__value">{{ number_format($kpi['belum_pernah_login']) }}</span>
            </div>
        </div>
    </div>
@stop

@section('content')
<section class="simansa-stat-filter mb-4">
    <div class="simansa-stat-filter__intro"><i class="fas fa-filter"></i><div><h2>Filter Statistik</h2><p>Semua kartu, grafik, peta, domisili, dan sekolah asal mengikuti pilihan ini · {{ $activeYear?->nama ?? 'Tahun aktif belum tersedia' }}</p></div></div>
    <form method="GET" action="{{ route('admin.siswa.statistics') }}">
        <div class="form-group mb-0"><label for="statTingkat">Tingkat</label><select id="statTingkat" name="tingkat" class="form-control"><option value="">Semua tingkat</option><option value="10" @selected($tingkat===10)>Kelas X</option><option value="11" @selected($tingkat===11)>Kelas XI</option><option value="12" @selected($tingkat===12)>Kelas XII</option></select></div>
        <div class="form-group mb-0"><label for="statKelas">Kelas Tahun Aktif</label><select id="statKelas" name="kelas_id" class="form-control" @disabled(!$tingkat)><option value="">{{ $tingkat ? 'Semua kelas tingkat ini' : 'Pilih tingkat dahulu' }}</option>@foreach($classes as $class)<option value="{{ $class->id }}" data-level="{{ $class->tingkat }}" @selected($kelasId===$class->id)>{{ $class->nama_kelas }}{{ $class->asrama_suffix }}</option>@endforeach</select></div>
        <button class="btn btn-primary"><i class="fas fa-chart-bar mr-1"></i>Terapkan</button><a href="{{ route('admin.siswa.statistics') }}" class="btn btn-outline-secondary"><i class="fas fa-redo mr-1"></i>Reset</a>
    </form>
</section>
<div class="row">
    <div class="col-md-6 col-xl mb-4">
        <a href="{{ $listUrl() }}" class="simansa-kpi-link">
            <div class="simansa-kpi simansa-kpi--blue">
                <div class="simansa-kpi__icon"><i class="fas fa-users"></i></div>
                <div class="simansa-kpi__body">
                    <div class="simansa-kpi__label">Total Siswa</div>
                    <div class="simansa-kpi__value">{{ number_format($kpi['total_siswa']) }}</div>
                    <div class="simansa-kpi__desc">{{ $scopeLabel }} · {{ number_format($kpi['laki_laki']) }} laki-laki dan {{ number_format($kpi['perempuan']) }} perempuan.</div>
                    <div class="simansa-kpi__view-link"><i class="fas fa-arrow-right mr-1"></i>Lihat Semua Siswa</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-xl mb-4">
        <a href="{{ $listUrl(['status' => 'lengkap']) }}" class="simansa-kpi-link">
            <div class="simansa-kpi simansa-kpi--green">
                <div class="simansa-kpi__icon"><i class="fas fa-check-circle"></i></div>
                <div class="simansa-kpi__body">
                    <div class="simansa-kpi__label">Data Lengkap</div>
                    <div class="simansa-kpi__value">{{ number_format($kpi['data_lengkap']) }}</div>
                    <div class="simansa-kpi__desc">{{ $kpi['persen_lengkap'] }}% lengkap · {{ number_format($kpi['belum_lengkap']) }} siswa masih perlu melengkapi data.</div>
                    <div class="simansa-kpi__view-link"><i class="fas fa-arrow-right mr-1"></i>Lihat Daftar</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-xl mb-4">
        <a href="{{ $listUrl(['login_status' => 'sudah']) }}" class="simansa-kpi-link">
            <div class="simansa-kpi simansa-kpi--amber">
                <div class="simansa-kpi__icon"><i class="fas fa-sign-in-alt"></i></div>
                <div class="simansa-kpi__body">
                    <div class="simansa-kpi__label">Sudah Login</div>
                    <div class="simansa-kpi__value">{{ number_format($kpi['sudah_login']) }}</div>
                    <div class="simansa-kpi__desc">{{ $kpi['persen_login'] }}% dari {{ number_format($kpi['total_siswa']) }} siswa pada filter aktif.</div>
                    <div class="simansa-kpi__view-link"><i class="fas fa-arrow-right mr-1"></i>Lihat Daftar</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-xl mb-4">
        <a href="{{ $listUrl(['login_status' => 'belum']) }}" class="simansa-kpi-link">
            <div class="simansa-kpi simansa-kpi--rose">
                <div class="simansa-kpi__icon"><i class="fas fa-user-clock"></i></div>
                <div class="simansa-kpi__body">
                    <div class="simansa-kpi__label">Belum Pernah Login</div>
                    <div class="simansa-kpi__value">{{ number_format($kpi['belum_pernah_login']) }}</div>
                    <div class="simansa-kpi__desc">{{ 100 - $kpi['persen_login'] }}% belum login · {{ number_format($kpi['sudah_login']) }} siswa sudah aktif.</div>
                    <div class="simansa-kpi__view-link"><i class="fas fa-arrow-right mr-1"></i>Lihat Daftar</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-xl mb-4">
        <a href="{{ $listUrl(['npsn_status' => 'kosong']) }}" class="simansa-kpi-link">
            <div class="simansa-kpi simansa-kpi--slate">
                <div class="simansa-kpi__icon"><i class="fas fa-school"></i></div>
                <div class="simansa-kpi__body">
                    <div class="simansa-kpi__label">NPSN Kosong</div>
                    <div class="simansa-kpi__value">{{ number_format($kpi['npsn_kosong']) }}</div>
                    <div class="simansa-kpi__desc">{{ $kpi['persen_npsn_kosong'] }}% kosong · {{ number_format($kpi['npsn_terisi']) }} siswa sudah memiliki NPSN asal sekolah.</div>
                    <div class="simansa-kpi__view-link"><i class="fas fa-arrow-right mr-1"></i>Lihat Daftar</div>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12 mb-4">
        <section class="simansa-analytics-section">
            <div class="simansa-section-head">
                <div>
                    <h3>NPSN Kosong · {{ $tingkat ? $scopeLabel : 'Kelas X' }}</h3>
                    <p>Gunakan checker NISN SIMANSA untuk mengisi NPSN asal sekolah. Jika belum ditemukan, sistem mencoba data matrikulasi/PPDB.</p>
                </div>
                <div class="simansa-section-actions">
                    <button type="button" class="btn btn-sm btn-primary" id="btnBulkCheckNpsn" @if($missingNpsnStudents->isEmpty()) disabled @endif>
                        <i class="fas fa-tasks mr-1"></i>Bulk Check
                    </button>
                    <a href="{{ $listUrl(['npsn_status' => 'kosong', 'tingkat' => $tingkat ?: 10]) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-list mr-1"></i>Lihat Semua
                    </a>
                </div>
            </div>
            <div class="table-responsive simansa-table-shell">
                <table class="table table-hover simansa-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>NISN</th>
                            <th>No. Tes</th>
                            <th>Kelas/Tingkat</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($missingNpsnStudents as $index => $student)
                            <tr id="missing-npsn-row-{{ $student['id'] }}">
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="simansa-table-title">{{ $student['nama_lengkap'] }}</div>
                                </td>
                                <td>{{ $student['nisn'] ?: '-' }}</td>
                                <td>{{ $student['nomor_tes'] ?: '-' }}</td>
                                <td>{{ $student['kelas'] }}</td>
                                <td>
                                    <span class="badge badge-warning simansa-npsn-status" id="missing-npsn-status-{{ $student['id'] }}">Belum dicek</span>
                                </td>
                                <td class="text-right">
                                    <button
                                        type="button"
                                        class="btn btn-xs btn-primary btn-check-npsn"
                                        data-url="{{ route('admin.siswa.statistics.check-npsn-ppdb', $student['id']) }}"
                                        data-id="{{ $student['id'] }}"
                                        data-name="{{ $student['nama_lengkap'] }}"
                                        data-nisn="{{ $student['nisn'] ?: '-' }}">
                                        <i class="fas fa-search mr-1"></i>Cek NISN
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Tidak ada siswa pada cakupan ini dengan NPSN asal sekolah kosong.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<div id="bulkNpsnOverlay" class="simansa-progress-overlay" aria-hidden="true">
    <div class="simansa-progress-modal">
        <div class="simansa-progress-modal__head">
            <div>
                <div class="simansa-progress-eyebrow" id="bulkProgressEyebrow"><i class="fas fa-id-card mr-1"></i>Bulk Check NPSN</div>
                <h3 id="bulkProgressTitle">Mengecek NPSN Asal Sekolah</h3>
                <p id="bulkProgressDescription">Proses berjalan satu per satu agar layanan checker tetap stabil.</p>
            </div>
            <button type="button" class="btn btn-sm btn-light" id="btnCloseBulkOverlay" disabled>
                <i class="fas fa-times mr-1"></i>Tutup
            </button>
        </div>
        <div class="simansa-progress-summary">
            <div>
                <span id="bulkNpsnProgressText">Menyiapkan proses...</span>
                <strong id="bulkNpsnCounter">0/0</strong>
            </div>
            <div class="progress simansa-progress-bar">
                <div id="bulkNpsnProgressBar" class="progress-bar bg-primary" role="progressbar" style="width: 0%"></div>
            </div>
        </div>
        <div class="simansa-progress-log" id="bulkNpsnLog"></div>
    </div>
</div>

<div class="row">
    <div class="col-12 col-xl-7 mb-4">
        <section class="simansa-analytics-section">
            <div class="simansa-section-head">
                <div>
                    <h3>Ringkasan Operasional</h3>
                    <p>Perbandingan status akun dan kelengkapan data siswa.</p>
                </div>
            </div>
            <div class="simansa-chart-grid simansa-chart-grid--summary">
                <div class="simansa-chart-panel">
                    <h4>Status Kelengkapan</h4>
                    <div class="simansa-chart-canvas simansa-chart-canvas--compact">
                        <canvas id="completionChart"></canvas>
                    </div>
                </div>
                <div class="simansa-chart-panel">
                    <h4>Status Login</h4>
                    <div class="simansa-chart-canvas simansa-chart-canvas--compact">
                        <canvas id="loginChart"></canvas>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <div class="col-12 col-xl-5 mb-4">
        <section class="simansa-analytics-section">
            <div class="simansa-section-head">
                <div>
                    <h3>Asal Sekolah</h3>
                    <p>Proporsi asal siswa berdasarkan bentuk pendidikan sekolah.</p>
                </div>
            </div>
            <div class="simansa-chart-panel simansa-chart-panel--full">
                <h4>Distribusi SMP / MTs / Lainnya</h4>
                <div class="simansa-chart-canvas simansa-chart-canvas--education">
                    <canvas id="educationSpreadChart"></canvas>
                </div>
            </div>
        </section>
    </div>
</div>

<div class="row">
    <div class="col-12 col-xl-7 mb-4">
        <section class="simansa-analytics-section">
            <div class="simansa-section-head">
                <div>
                    <h3>Sebaran Alamat</h3>
                    <p>Wilayah domisili siswa berdasarkan alamat siswa atau alamat orang tua yang dipakai.</p>
                </div>
            </div>
            <div class="simansa-chart-grid">
                <div class="simansa-chart-panel">
                    <h4>Kabupaten / Kota Terbanyak</h4>
                    <div class="simansa-chart-canvas simansa-chart-canvas--wide">
                        <canvas id="addressCityChart"></canvas>
                    </div>
                </div>
                <div class="simansa-chart-panel">
                    <h4>Kecamatan Terbanyak</h4>
                    <div class="simansa-chart-canvas simansa-chart-canvas--wide">
                        <canvas id="addressDistrictChart"></canvas>
                    </div>
                </div>
                <div class="simansa-chart-panel simansa-chart-panel--span">
                    <h4>Kelurahan / Desa Terbanyak</h4>
                    <p class="simansa-chart-hint">Klik batang untuk melihat siswa dari kelurahan tersebut sesuai filter tingkat dan kelas.</p>
                    <div class="simansa-chart-canvas simansa-chart-canvas--wide">
                        <canvas id="addressVillageChart"></canvas>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <div class="col-12 col-xl-5 mb-4">
        <section class="simansa-analytics-section">
            <div class="simansa-section-head">
                <div>
                    <h3>Wilayah Asal Sekolah</h3>
                    <p>Kota/kabupaten asal sekolah yang paling banyak menyumbang siswa.</p>
                </div>
            </div>
            <div class="simansa-chart-panel simansa-chart-panel--full">
                <h4>Sebaran Kota Asal Sekolah</h4>
                <div class="simansa-chart-canvas simansa-chart-canvas--wide">
                    <canvas id="schoolCityChart"></canvas>
                </div>
            </div>
        </section>
    </div>
</div>

<div class="row">
    <div class="col-12 col-xl-8 mb-4">
        <section class="simansa-analytics-section">
            <div class="simansa-section-head">
                <div>
                    <h3>Peta Sebaran</h3>
                    <p>Marker dihasilkan dari agregasi wilayah domisili dan sekolah asal yang paling dominan.</p>
                </div>
                <div class="simansa-map-toggles">
                    <button type="button" class="btn btn-sm btn-primary" id="toggleAddressLayer">Alamat</button>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="toggleSchoolLayer">Asal Sekolah</button>
                </div>
            </div>
            <div id="studentSpreadMap" class="simansa-map"></div>
            <div class="simansa-map-note">
                Geolokasi peta menggunakan hasil geocoding nama wilayah/sekolah. Jika ada titik yang belum muncul, biasanya karena lokasi belum berhasil dikenali secara publik.
            </div>
        </section>
    </div>
    <div class="col-12 col-xl-4 mb-4">
        <section class="simansa-analytics-section">
            <div class="simansa-section-head">
                <div>
                    <h3>Provinsi Teratas</h3>
                    <p>Wilayah provinsi domisili dengan jumlah siswa terbanyak.</p>
                </div>
            </div>
            <div class="simansa-list-panel simansa-list-panel--scroll">
                @forelse($addressProvinceSpread as $index => $item)
                    <div class="simansa-list-row">
                        <div class="simansa-list-rank">{{ $index + 1 }}</div>
                        <div class="simansa-list-copy">
                            <div class="simansa-list-title">{{ $item['name'] }}</div>
                            <div class="simansa-list-subtitle">{{ number_format($item['count']) }} siswa</div>
                        </div>
                        <a href="{{ $listUrl(['address_scope' => 'province', 'address_name' => $item['name']]) }}" class="btn btn-xs btn-outline-primary ml-auto">
                            Lihat Siswa
                        </a>
                    </div>
                @empty
                    <div class="simansa-empty-state">Belum ada data wilayah provinsi yang bisa diringkas.</div>
                @endforelse
            </div>
        </section>
    </div>
</div>

<div class="row">
    <div class="col-12 mb-4">
        @php
            $nsmCheckCandidates = $originSchools->filter(fn ($school) => filled($school['npsn'] ?? null));
        @endphp
        <section class="simansa-analytics-section">
            <div class="simansa-section-head">
                <div>
                    <h3>Data Sekolah Asal</h3>
                    <p>Seluruh sekolah asal pada {{ strtolower($scopeLabel) }}, diurutkan berdasarkan jumlah siswa · {{ number_format($originSchools->count()) }} sekolah.</p>
                </div>
                <button type="button" class="btn btn-sm btn-primary" id="btnBulkCheckNsm" @if($nsmCheckCandidates->isEmpty()) disabled @endif>
                    <i class="fas fa-database mr-1"></i>Bulk Lengkapi
                </button>
            </div>
            <div class="table-responsive simansa-table-shell simansa-school-table-shell">
                <table class="table table-hover simansa-table simansa-school-table">
                    <colgroup>
                        <col class="simansa-school-col-number">
                        <col class="simansa-school-col-name">
                        <col class="simansa-school-col-region">
                        <col class="simansa-school-col-total">
                        <col class="simansa-school-col-emis">
                        <col class="simansa-school-col-action">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Sekolah</th>
                            <th>Wilayah</th>
                            <th class="text-right">Jumlah</th>
                            <th class="text-center" title="Siswa yang belum ditandai masuk EMIS">Belum Ada di EMIS</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($originSchools as $index => $school)
                            @php
                                $canCheckNsm = filled($school['npsn'] ?? null);
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="simansa-table-title">{{ $school['school_name'] }}</div>
                                    <div class="simansa-table-subtitle" id="school-nsm-status-{{ $school['npsn'] }}">
                                        NPSN: {{ $school['npsn'] ?: '-' }} | NSM: {{ $school['nsm'] ?: '-' }}
                                    </div>
                                    <div class="simansa-school-metadata">
                                        <span>{{ $school['school_status'] }}</span>
                                        <span>{{ $school['education_form'] }}</span>
                                        <span>{{ $school['ministry'] }}</span>
                                        <span>Akreditasi {{ $school['accreditation'] }}</span>
                                    </div>
                                </td>
                                <td class="simansa-school-region">{{ collect([$school['district_name'], $school['city_name'], $school['province_name']])->filter()->implode(', ') ?: '-' }}</td>
                                <td class="text-right font-weight-bold">{{ number_format($school['count']) }}</td>
                                <td class="text-center">
                                    @if($school['missing_emis_count'] > 0)
                                        <button
                                            type="button"
                                            class="btn btn-xs btn-outline-danger btn-school-missing-emis"
                                            data-url="{{ route('admin.siswa.statistics.school-missing-emis', array_merge(['sekolah' => $school['npsn']], $filterQuery)) }}"
                                            data-school="{{ $school['school_name'] }}"
                                            data-count="{{ $school['missing_emis_count'] }}">
                                            <i class="fas fa-user-clock mr-1"></i>{{ number_format($school['missing_emis_count']) }}
                                        </button>
                                    @else
                                        <span class="badge badge-success"><i class="fas fa-check mr-1"></i>0</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <div class="simansa-school-actions">
                                    @if($canCheckNsm)
                                        <button
                                            type="button"
                                            class="btn btn-xs btn-primary btn-check-school-nsm"
                                            data-url="{{ route('admin.siswa.statistics.check-school-nsm', $school['npsn']) }}"
                                            data-npsn="{{ $school['npsn'] }}"
                                            data-school="{{ $school['school_name'] }}">
                                            <i class="fas fa-sync-alt mr-1"></i>Lengkapi
                                        </button>
                                    @endif
                                    <a href="{{ $schoolStudentsUrl($school) }}" class="btn btn-xs btn-outline-primary">
                                        Lihat Siswa
                                    </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Belum ada data asal sekolah pada filter ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
    <div class="col-12 mb-4">
        <section class="simansa-analytics-section">
            <div class="simansa-section-head">
                <div>
                    <h3>Kota Domisili Teratas</h3>
                    <p>Wilayah alamat siswa paling dominan untuk pemetaan sebaran.</p>
                </div>
            </div>
            <div class="table-responsive simansa-table-shell">
                <table class="table table-hover simansa-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Kabupaten / Kota</th>
                            <th>Provinsi</th>
                            <th class="text-right">Jumlah</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($addressCitySpread as $index => $city)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="simansa-table-title">{{ $city['name'] }}</td>
                                <td>{{ $city['province_name'] ?: '-' }}</td>
                                <td class="text-right font-weight-bold">{{ number_format($city['count']) }}</td>
                                <td class="text-right">
                                    <a href="{{ $listUrl(['address_scope' => 'city', 'address_name' => $city['name'], 'province_name' => $city['province_name']]) }}" class="btn btn-xs btn-outline-primary">
                                        Lihat Siswa
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Belum ada data alamat yang cukup untuk ditampilkan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<div class="modal fade" id="schoolMissingEmisModal" tabindex="-1" role="dialog" aria-labelledby="schoolMissingEmisTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content simansa-emis-modal">
            <div class="modal-header simansa-emis-modal__header">
                <div>
                    <div class="simansa-emis-modal__eyebrow"><i class="fas fa-cloud mr-1"></i>Monitoring EMIS · Siswa Belum Terdaftar</div>
                    <h4 class="modal-title" id="schoolMissingEmisTitle">Memuat nama sekolah...</h4>
                    <div class="simansa-emis-modal__school-meta" id="schoolMissingEmisIdentity">
                        <span>NPSN: -</span>
                        <span>NSM: -</span>
                    </div>
                    <p id="schoolMissingEmisSubtitle">Memuat informasi sekolah...</p>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="schoolMissingEmisSummary" class="simansa-emis-school-summary"></div>
                <div id="schoolMissingEmisLoading" class="simansa-emis-loading">
                    <i class="fas fa-circle-notch fa-spin"></i>
                    <strong>Memuat siswa...</strong>
                    <span>Data dibatasi sesuai tingkat dan kelas yang sedang dipilih.</span>
                </div>
                <div id="schoolMissingEmisStudents" class="simansa-emis-student-grid"></div>
            </div>
            <div class="modal-footer">
                <small class="text-muted mr-auto"><i class="fas fa-info-circle mr-1"></i>Status berdasarkan penandaan EMIS pada data siswa SIMANSA.</small>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        .simansa-stat-hero {
            display: flex;
            justify-content: space-between;
            align-items: stretch;
            gap: 1rem;
            padding: 1.35rem 1.45rem;
            border-radius: 16px;
            background: #3b82f6;
            color: #fff;
            box-shadow: 0 14px 32px rgba(59, 130, 246, 0.22);
        }

        .simansa-stat-hero__main {
            max-width: 760px;
        }

        .simansa-stat-hero__eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 0.65rem;
            color: rgba(255, 255, 255, 0.92);
        }

        .simansa-stat-hero__title {
            font-size: 1.45rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 0.35rem;
        }

        .simansa-stat-hero__subtitle {
            margin-bottom: 0;
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.55;
            max-width: 760px;
        }

        .simansa-stat-hero__meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.85rem;
            min-width: 280px;
        }

        .simansa-stat-hero-chip {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 0.95rem 1rem;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .simansa-stat-hero-chip__label {
            font-size: 0.78rem;
            color: rgba(255, 255, 255, 0.78);
            margin-bottom: 0.35rem;
        }

        .simansa-stat-hero-chip__value {
            font-size: 1.45rem;
            font-weight: 700;
        }

        .simansa-stat-filter{display:flex;justify-content:space-between;align-items:flex-end;gap:1rem;padding:1rem 1.15rem;border:1px solid #bfdbfe;border-radius:14px;background:linear-gradient(135deg,#f8fbff,#f0fdfa);box-shadow:0 8px 22px rgba(15,23,42,.04)}
        .simansa-stat-filter__intro{display:flex;align-items:center;gap:.75rem;min-width:280px}.simansa-stat-filter__intro>i{display:grid;place-items:center;width:42px;height:42px;border-radius:11px;background:#2563eb;color:#fff}.simansa-stat-filter__intro h2{font-size:1rem;font-weight:800;color:#0f172a;margin:0}.simansa-stat-filter__intro p{font-size:.78rem;color:#64748b;margin:.2rem 0 0}.simansa-stat-filter form{display:grid;grid-template-columns:minmax(155px,1fr) minmax(210px,1.4fr) auto auto;align-items:end;gap:.6rem;flex:1;max-width:760px}.simansa-stat-filter label{font-size:.7rem;text-transform:uppercase;color:#64748b;font-weight:800}.simansa-stat-filter .form-control,.simansa-stat-filter .btn{height:38px;border-radius:8px}.simansa-stat-filter .form-control{border-color:#cbd5e1}

        .simansa-kpi {
            display: flex;
            gap: 0.85rem;
            min-height: 156px;
            padding: 1rem 1.05rem;
            border-radius: 14px;
            background: #fff;
            border: 1px solid #dbe4f0;
            border-top: 4px solid #3b82f6;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
        }

        .simansa-kpi__icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
            flex-shrink: 0;
        }

        .simansa-kpi__label {
            font-size: 0.78rem;
            color: #64748b;
            margin-bottom: 0.25rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            font-weight: 700;
        }

        .simansa-kpi__value {
            font-size: 1.55rem;
            line-height: 1.1;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.35rem;
        }

        .simansa-kpi__desc {
            color: #64748b;
            line-height: 1.45;
            font-size: 0.84rem;
        }

        .simansa-kpi--blue { border-top-color: #3b82f6; }
        .simansa-kpi--green { border-top-color: #16a34a; }
        .simansa-kpi--amber { border-top-color: #f59e0b; }
        .simansa-kpi--rose { border-top-color: #e11d48; }
        .simansa-kpi--slate { border-top-color: #0f766e; }
        .simansa-kpi--blue .simansa-kpi__icon,
        .simansa-kpi--green .simansa-kpi__icon,
        .simansa-kpi--amber .simansa-kpi__icon,
        .simansa-kpi--rose .simansa-kpi__icon,
        .simansa-kpi--slate .simansa-kpi__icon { background: #eef4ff; color: #2563eb; }

        a.simansa-kpi-link {
            display: block;
            text-decoration: none !important;
            color: inherit;
        }
        a.simansa-kpi-link .simansa-kpi {
            transition: box-shadow 0.18s, transform 0.18s, border-color 0.18s;
        }
        a.simansa-kpi-link:hover .simansa-kpi,
        a.simansa-kpi-link:focus .simansa-kpi {
            box-shadow: 0 16px 34px rgba(15, 23, 42, 0.1);
            transform: translateY(-2px);
            border-color: #bfdbfe;
        }
        a.simansa-kpi-link .simansa-kpi__view-link {
            display: inline-block;
            margin-top: 0.5rem;
            font-size: 0.8rem;
            font-weight: 600;
            opacity: 0.55;
            letter-spacing: 0.02em;
        }
        a.simansa-kpi-link:hover .simansa-kpi__view-link {
            opacity: 1;
        }
        .simansa-kpi--blue  .simansa-kpi__view-link { color: #1d4ed8; }
        .simansa-kpi--green .simansa-kpi__view-link { color: #15803d; }
        .simansa-kpi--amber .simansa-kpi__view-link { color: #b45309; }
        .simansa-kpi--rose  .simansa-kpi__view-link { color: #be123c; }
        .simansa-kpi--slate .simansa-kpi__view-link { color: #0369a1; }

        .simansa-npsn-status {
            min-width: 92px;
            padding: 0.42rem 0.55rem;
        }

        .simansa-section-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.55rem;
            flex-wrap: wrap;
        }

        .simansa-progress-overlay {
            position: fixed;
            inset: 0;
            z-index: 1080;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
            background: rgba(15, 23, 42, 0.58);
            backdrop-filter: blur(4px);
        }

        .simansa-progress-overlay.is-active {
            display: flex;
        }

        .simansa-progress-modal {
            width: min(760px, 100%);
            max-height: min(720px, 92vh);
            display: flex;
            flex-direction: column;
            border-radius: 18px;
            background: #fff;
            border: 1px solid #d9e3f0;
            box-shadow: 0 26px 70px rgba(15, 23, 42, 0.28);
            overflow: hidden;
        }

        .simansa-progress-modal__head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            padding: 1.1rem 1.2rem;
            color: #fff;
            background: linear-gradient(135deg, #2563eb 0%, #0f766e 100%);
        }

        .simansa-progress-modal__head h3 {
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0.2rem 0 0.25rem;
        }

        .simansa-progress-modal__head p {
            color: rgba(255, 255, 255, 0.84);
            margin: 0;
        }

        .simansa-progress-eyebrow {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            opacity: 0.9;
        }

        .simansa-progress-summary {
            padding: 1rem 1.2rem;
            border-bottom: 1px solid #e5edf7;
            background: #f8fafc;
        }

        .simansa-progress-summary > div:first-child {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 0.75rem;
            color: #334155;
        }

        .simansa-progress-bar {
            height: 0.72rem;
            border-radius: 999px;
            background: #e2e8f0;
        }

        .simansa-progress-log {
            padding: 0.25rem 1.2rem 1rem;
            overflow: auto;
            min-height: 220px;
            max-height: 380px;
            background: #fff;
        }

        .simansa-progress-log-row {
            display: grid;
            grid-template-columns: 92px minmax(0, 1fr);
            gap: 0.75rem;
            padding: 0.82rem 0;
            border-bottom: 1px solid #edf2f7;
            color: #334155;
        }

        .simansa-progress-log-row:last-child {
            border-bottom: 0;
        }

        .simansa-progress-log-row strong {
            color: #0f172a;
        }

        .simansa-progress-log-meta {
            color: #64748b;
            font-size: 0.86rem;
            margin-top: 0.15rem;
        }

        .simansa-analytics-section {
            padding: 1.1rem 1.25rem;
            border-radius: 14px;
            background: #fff;
            border: 1px solid #dbe4f0;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
            height: 100%;
        }

        .simansa-school-table-shell{max-height:520px;overflow-y:auto;overflow-x:hidden}
        .simansa-school-table-shell thead th{position:sticky;top:0;z-index:2;background:#f8fafc}
        .simansa-school-table-shell .simansa-table{width:100%;min-width:0}

        .simansa-school-table {
            table-layout: fixed;
        }

        .simansa-school-table .simansa-school-col-number { width: 4%; }
        .simansa-school-table .simansa-school-col-name { width: 34%; }
        .simansa-school-table .simansa-school-col-region { width: 26%; }
        .simansa-school-table .simansa-school-col-total { width: 8%; }
        .simansa-school-table .simansa-school-col-emis { width: 13%; }
        .simansa-school-table .simansa-school-col-action { width: 15%; }

        .simansa-school-table th,
        .simansa-school-table td {
            padding: .62rem .55rem;
            vertical-align: middle;
        }

        .simansa-school-table thead th {
            font-size: .7rem;
            line-height: 1.25;
        }

        .simansa-school-table .simansa-table-title {
            font-size: .84rem;
            line-height: 1.28;
        }

        .simansa-school-table .simansa-table-subtitle {
            font-size: .76rem;
            line-height: 1.3;
        }

        .simansa-school-region {
            color: #334155;
            font-size: .77rem;
            line-height: 1.4;
        }

        .simansa-school-metadata {
            display: flex;
            flex-wrap: wrap;
            gap: .12rem .35rem;
            margin-top: .24rem;
            color: #64748b;
            font-size: .69rem;
            line-height: 1.3;
        }

        .simansa-school-metadata span {
            display: inline-flex;
            align-items: center;
        }

        .simansa-school-metadata span + span::before {
            content: "•";
            margin-right: .35rem;
            color: #cbd5e1;
        }

        .simansa-school-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: .3rem;
        }

        .simansa-school-actions .btn,
        .simansa-school-table .btn-school-missing-emis {
            white-space: nowrap;
            line-height: 1.35;
        }

        .simansa-school-table tbody tr {
            transition: background-color .18s ease;
        }

        .simansa-emis-modal {
            border: 0;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 28px 80px rgba(15, 23, 42, 0.28);
        }

        #schoolMissingEmisModal .simansa-emis-modal__header {
            align-items: flex-start;
            padding: 1.2rem 1.35rem;
            color: #0f172a !important;
            border: 0 !important;
            border-bottom: 1px solid #dbe4f0 !important;
            background: #fff !important;
        }

        #schoolMissingEmisModal .simansa-emis-modal__header h4 {
            margin: .2rem 0 .25rem;
            color: #0f172a !important;
            font-weight: 800;
            text-shadow: none !important;
        }

        #schoolMissingEmisModal .simansa-emis-modal__header p {
            margin: .35rem 0 0;
            color: #64748b !important;
        }

        #schoolMissingEmisModal .simansa-emis-modal__school-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0;
            margin-top: .25rem;
            color: #64748b !important;
            font-size: .82rem;
            font-weight: 400;
            line-height: 1.45;
        }

        #schoolMissingEmisModal .simansa-emis-modal__school-meta span {
            min-height: 0;
            padding: 0;
            border: 0 !important;
            border-radius: 0;
            color: #64748b !important;
            background: transparent !important;
            box-shadow: none !important;
        }

        #schoolMissingEmisModal .simansa-emis-modal__school-meta span + span::before {
            content: '|';
            margin: 0 .38rem;
            color: #cbd5e1;
        }

        #schoolMissingEmisModal .simansa-emis-modal__eyebrow {
            color: #2563eb !important;
            font-size: .75rem;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        #schoolMissingEmisModal .simansa-emis-modal__header .close {
            color: #475569 !important;
            opacity: 1 !important;
            text-shadow: none;
        }

        .simansa-emis-school-summary {
            display: grid;
            grid-template-columns: minmax(180px, 280px);
            gap: .75rem;
            margin-bottom: 1rem;
        }

        .simansa-emis-summary-card {
            min-width: 0;
            padding: .85rem 1rem;
            border: 1px solid #dbe4f0;
            border-radius: 14px;
            background: #f8fafc;
        }

        .simansa-emis-summary-card span {
            display: block;
            margin-bottom: .25rem;
            color: #64748b;
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .simansa-emis-summary-card strong {
            display: block;
            color: #0f172a;
            overflow-wrap: anywhere;
        }

        .simansa-emis-loading,
        .simansa-emis-empty {
            min-height: 260px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: .55rem;
            color: #64748b;
            text-align: center;
        }

        .simansa-emis-loading i,
        .simansa-emis-empty i {
            color: #6366f1;
            font-size: 2rem;
        }

        .simansa-emis-student-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .75rem;
        }

        .simansa-emis-student {
            display: grid;
            grid-template-columns: 62px minmax(0, 1fr) auto;
            align-items: center;
            gap: .85rem;
            padding: .8rem;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #fff;
            transition: border-color .2s ease, box-shadow .2s ease;
        }

        .simansa-emis-student:hover {
            border-color: #a5b4fc;
            box-shadow: 0 10px 24px rgba(79, 70, 229, .1);
        }

        .simansa-emis-student.is-emis-updated {
            border-color: #86efac;
            background: #f0fdf4;
        }

        .simansa-emis-student img {
            width: 62px;
            height: 72px;
            border-radius: 12px;
            object-fit: cover;
            background: #eef2ff;
        }

        .simansa-emis-student__name {
            color: #0f172a;
            font-weight: 800;
            line-height: 1.3;
        }

        .simansa-emis-student__meta {
            margin-top: .22rem;
            color: #64748b;
            font-size: .82rem;
            line-height: 1.45;
        }

        .simansa-emis-student__actions {
            display: flex;
            flex-direction: column;
            gap: .35rem;
        }

        .simansa-section-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .simansa-section-head h3 {
            font-size: 1.02rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.3rem;
        }

        .simansa-section-head p {
            color: #64748b;
            line-height: 1.55;
            margin-bottom: 0;
            max-width: 720px;
        }

        .simansa-chart-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .simansa-chart-grid--summary {
            align-items: stretch;
        }

        .simansa-chart-panel,
        .simansa-list-panel {
            padding: 1rem;
            border-radius: 12px;
            background: #f8fafc;
            border: 1px solid #e5edf7;
            height: 100%;
        }

        .simansa-chart-panel--full {
            min-height: 100%;
        }

        .simansa-chart-panel--span {
            grid-column: 1 / -1;
        }

        .simansa-chart-hint {
            color: #64748b;
            font-size: 0.8rem;
            margin: -0.15rem 0 0.7rem;
        }

        .simansa-chart-panel h4 {
            font-size: 0.96rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.85rem;
        }

        .simansa-chart-canvas {
            position: relative;
            width: 100%;
        }

        .simansa-chart-canvas canvas {
            width: 100% !important;
            height: 100% !important;
            display: block;
        }

        .simansa-chart-canvas--compact {
            height: 240px;
        }

        .simansa-chart-canvas--wide {
            height: 300px;
        }

        .simansa-chart-canvas--education {
            height: 320px;
            max-height: 320px;
            overflow: hidden;
        }

        .simansa-list-row {
            display: flex;
            align-items: flex-start;
            gap: 0.85rem;
            padding: 0.85rem 0;
            border-bottom: 1px solid #e5edf7;
        }

        .simansa-list-row:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .simansa-list-rank {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            background: #eff6ff;
            color: #2563eb;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .simansa-list-title {
            font-weight: 700;
            color: #0f172a;
        }

        .simansa-list-subtitle {
            color: #64748b;
            font-size: 0.88rem;
            margin-top: 0.15rem;
        }

        .simansa-map {
            width: 100%;
            height: 460px;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #d9e3f0;
        }

        .simansa-map-note {
            margin-top: 0.85rem;
            color: #64748b;
            font-size: 0.88rem;
            line-height: 1.6;
        }

        .simansa-map-toggles {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .simansa-table thead th {
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #64748b;
            border-top: 0;
            position: sticky;
            top: 0;
            background: #f8fafc;
            z-index: 2;
        }

        .simansa-table-title {
            font-weight: 700;
            color: #0f172a;
        }

        .simansa-table-subtitle {
            color: #64748b;
            font-size: 0.84rem;
            margin-top: 0.15rem;
        }

        .simansa-empty-state {
            color: #64748b;
            font-size: 0.9rem;
        }

        .simansa-table-shell,
        .simansa-list-panel--scroll {
            max-height: 520px;
            overflow: auto;
        }

        .simansa-table-shell {
            border: 1px solid #e5edf7;
            border-radius: 12px;
            background: #f8fafc;
            padding: 0;
        }

        .simansa-table {
            margin-bottom: 0;
        }

        .simansa-table td,
        .simansa-table th {
            white-space: normal;
            vertical-align: top;
        }

        .simansa-table td {
            padding-top: 0.85rem;
            padding-bottom: 0.85rem;
        }

        .simansa-list-panel--scroll {
            scrollbar-gutter: stable;
        }

        @media (min-width: 1200px) {
            .simansa-chart-panel--full {
                min-height: 360px;
            }

            .simansa-table-shell {
                max-height: 560px;
            }

            .simansa-list-panel--scroll {
                max-height: 560px;
            }

            .simansa-chart-canvas--education {
                height: 340px;
                max-height: 340px;
            }
        }

        @media (max-width: 1199.98px) {
            .simansa-stat-hero {
                flex-direction: column;
            }

            .simansa-stat-hero__meta {
                min-width: 0;
            }

            .simansa-stat-filter{align-items:stretch;flex-direction:column}.simansa-stat-filter form{max-width:none;width:100%}

            .simansa-table-shell,
            .simansa-list-panel--scroll {
                max-height: none;
                overflow: visible;
            }

            .simansa-chart-canvas--education,
            .simansa-chart-canvas--wide {
                height: 280px;
                max-height: 280px;
            }
        }

        @media (max-width: 767.98px) {
            .simansa-stat-hero,
            .simansa-analytics-section,
            .simansa-kpi {
                border-radius: 18px;
            }

            .simansa-stat-hero {
                padding: 1rem;
            }

            .simansa-stat-hero__title {
                font-size: 1.6rem;
            }

            .simansa-stat-hero__meta,
            .simansa-chart-grid {
                grid-template-columns: 1fr;
            }

            .simansa-stat-filter form{grid-template-columns:1fr 1fr}.simansa-stat-filter form .btn{width:100%}

            .simansa-analytics-section,
            .simansa-chart-panel,
            .simansa-list-panel {
                padding: 0.95rem;
            }

            .simansa-map {
                height: 360px;
            }

            .simansa-chart-canvas--compact {
                height: 220px;
            }

            .simansa-chart-canvas--education,
            .simansa-chart-canvas--wide {
                height: 260px;
                max-height: 260px;
            }

            .simansa-emis-school-summary,
            .simansa-emis-student-grid {
                grid-template-columns: 1fr;
            }

            .simansa-emis-student {
                grid-template-columns: 54px minmax(0, 1fr);
            }

            .simansa-emis-student img {
                width: 54px;
                height: 64px;
            }

            .simansa-emis-student__actions {
                grid-column: 1 / -1;
                display: grid;
                grid-template-columns: 1fr 1fr;
            }

            .simansa-emis-student__actions .btn {
                width: 100%;
            }

            .simansa-school-table-shell {
                overflow: visible;
                max-height: none;
                border: 0;
                background: transparent;
            }

            .simansa-school-table-shell .simansa-table thead {
                display: none;
            }

            .simansa-school-table-shell .simansa-table,
            .simansa-school-table-shell .simansa-table tbody,
            .simansa-school-table-shell .simansa-table tr,
            .simansa-school-table-shell .simansa-table td {
                display: block;
                width: 100%;
            }

            .simansa-school-table colgroup {
                display: none;
            }

            .simansa-school-table-shell .simansa-table tr {
                margin-bottom: .75rem;
                padding: .85rem;
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                background: #fff;
            }

            .simansa-school-table-shell .simansa-table td {
                padding: .3rem 0;
                border: 0;
                text-align: left !important;
            }

            .simansa-school-table-shell .simansa-table td:first-child {
                display: none;
            }

            .simansa-school-actions {
                justify-content: flex-start;
                margin-top: .15rem;
            }

            .simansa-school-actions .btn {
                flex: 1 1 120px;
            }
        }

        @media(max-width:575.98px){.simansa-stat-filter form{grid-template-columns:1fr}.simansa-stat-filter__intro{min-width:0}}
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const completionData = @json([
            'Lengkap' => $kpi['data_lengkap'],
            'Belum Lengkap' => $kpi['belum_lengkap'],
        ]);
        const loginData = @json([
            'Sudah Login' => $kpi['sudah_login'],
            'Belum Pernah Login' => $kpi['belum_pernah_login'],
        ]);
        const educationSpread = @json($educationSpread);
        const addressCitySpread = @json($addressCitySpread);
        const addressDistrictSpread = @json($addressDistrictSpread);
        const addressVillageSpread = @json($addressVillageSpread);
        const schoolCitySpread = @json($schoolCitySpread);
        const mapAddressPoints = @json($mapAddressPoints);
        const mapSchoolPoints = @json($mapSchoolPoints);
        const siswaIndexBaseUrl = @json(route('admin.siswa.index'));
        const activeStatisticsFilters = @json($filterQuery);
        const csrfToken = @json(csrf_token());
        let activeMissingEmisSchoolButton = null;
        let activeMissingEmisCount = 0;

        function formatNumber(value) {
            return new Intl.NumberFormat('id-ID').format(value || 0);
        }

        function navigateToStudentList(params) {
            const url = new URL(siswaIndexBaseUrl, window.location.origin);
            Object.entries(Object.assign({}, activeStatisticsFilters, params)).forEach(([key, value]) => {
                if (value !== null && value !== undefined && value !== '') {
                    url.searchParams.set(key, value);
                }
            });
            window.location.href = url.toString();
        }

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, function (char) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                }[char];
            });
        }

        function renderMissingEmisSummary(school, count) {
            $('#schoolMissingEmisTitle').text(school.name || 'Sekolah asal');
            $('#schoolMissingEmisIdentity').html(`
                <span>NPSN: ${escapeHtml(school.npsn || '-')}</span>
                <span>NSM: ${escapeHtml(school.nsm || '-')}</span>
            `);
            $('#schoolMissingEmisSummary').html(`
                <div class="simansa-emis-summary-card">
                    <span>Belum di EMIS</span>
                    <strong id="schoolMissingEmisCount">${escapeHtml(formatNumber(count))} siswa</strong>
                </div>
            `);
        }

        function renderMissingEmisStudents(students) {
            if (!students.length) {
                $('#schoolMissingEmisStudents').html(`
                    <div class="simansa-emis-empty" style="grid-column:1/-1">
                        <i class="fas fa-check-circle text-success"></i>
                        <strong>Semua siswa sudah ditandai masuk EMIS</strong>
                        <span>Tidak ada data yang perlu ditampilkan pada filter ini.</span>
                    </div>
                `);
                return;
            }

            $('#schoolMissingEmisStudents').html(students.map(student => {
                const birth = [student.tempat_lahir, student.tanggal_lahir].filter(Boolean).join(', ') || '-';
                const emisControl = student.can_toggle_emis
                    ? `<button type="button"
                            class="btn btn-sm btn-outline-danger btn-modal-toggle-emis"
                            data-url="${escapeHtml(student.toggle_emis_url)}">
                            <i class="far fa-circle mr-1"></i>Belum EMIS
                       </button>`
                    : `<span class="badge badge-secondary"><i class="far fa-circle mr-1"></i>Belum EMIS</span>`;

                return `
                    <article class="simansa-emis-student" data-student-id="${escapeHtml(student.id)}">
                        <img src="${escapeHtml(student.foto_url)}" alt="Foto ${escapeHtml(student.nama_lengkap)}" loading="lazy">
                        <div>
                            <div class="simansa-emis-student__name">${escapeHtml(student.nama_lengkap)}</div>
                            <div class="simansa-emis-student__meta">
                                <i class="fas fa-id-card mr-1"></i>NISN: ${escapeHtml(student.nisn || '-')}<br>
                                <i class="fas fa-map-marker-alt mr-1"></i>${escapeHtml(birth)}<br>
                                <i class="fas fa-door-open mr-1"></i>${escapeHtml(student.kelas || '-')}
                            </div>
                        </div>
                        <div class="simansa-emis-student__actions">
                            ${emisControl}
                            <a href="${escapeHtml(student.detail_url)}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye mr-1"></i>Detail
                            </a>
                        </div>
                    </article>
                `;
            }).join(''));
        }

        $('.btn-school-missing-emis').on('click', function () {
            const button = $(this);
            const schoolName = button.data('school') || 'Sekolah asal';
            activeMissingEmisSchoolButton = button;
            activeMissingEmisCount = Number(button.data('count')) || 0;

            $('#schoolMissingEmisTitle').text(schoolName);
            $('#schoolMissingEmisIdentity').html('<span>NPSN: -</span><span>NSM: -</span>');
            $('#schoolMissingEmisSubtitle').text('Memuat detail sekolah...');
            $('#schoolMissingEmisSummary, #schoolMissingEmisStudents').empty();
            $('#schoolMissingEmisLoading').show();
            $('#schoolMissingEmisModal').modal('show');

            $.get(button.data('url'))
                .done(function (response) {
                    renderMissingEmisSummary(response.school || {}, response.count || 0);
                    $('#schoolMissingEmisSubtitle').text([
                        response.school?.education_form,
                        response.school?.location
                    ].filter(Boolean).join(' · '));
                    renderMissingEmisStudents(response.students || []);
                })
                .fail(function (xhr) {
                    $('#schoolMissingEmisStudents').html(`
                        <div class="simansa-emis-empty" style="grid-column:1/-1">
                            <i class="fas fa-exclamation-triangle text-danger"></i>
                            <strong>Data gagal dimuat</strong>
                            <span>${escapeHtml(xhr.responseJSON?.message || 'Silakan tutup modal dan coba kembali.')}</span>
                        </div>
                    `);
                })
                .always(function () {
                    $('#schoolMissingEmisLoading').hide();
                });
        });

        $(document).on('click', '.btn-modal-toggle-emis', function () {
            const button = $(this);
            const card = button.closest('.simansa-emis-student');

            button.prop('disabled', true).html('<i class="fas fa-circle-notch fa-spin mr-1"></i>Menyimpan');

            $.post(button.data('url'), {_token: csrfToken})
                .done(function (response) {
                    if (!response.success || !response.emis_registered) {
                        button.prop('disabled', false)
                            .html('<i class="far fa-circle mr-1"></i>Belum EMIS');
                        return;
                    }

                    card.addClass('is-emis-updated');
                    button.removeClass('btn-outline-danger')
                        .addClass('btn-success')
                        .html('<i class="fas fa-check-circle mr-1"></i>Sudah EMIS');

                    activeMissingEmisCount = Math.max(activeMissingEmisCount - 1, 0);
                    $('#schoolMissingEmisCount').text(`${formatNumber(activeMissingEmisCount)} siswa`);

                    if (activeMissingEmisSchoolButton) {
                        activeMissingEmisSchoolButton.data('count', activeMissingEmisCount);
                        if (activeMissingEmisCount > 0) {
                            activeMissingEmisSchoolButton
                                .html(`<i class="fas fa-user-clock mr-1"></i>${formatNumber(activeMissingEmisCount)}`);
                        } else {
                            activeMissingEmisSchoolButton.replaceWith(
                                '<span class="badge badge-success"><i class="fas fa-check mr-1"></i>0</span>'
                            );
                            activeMissingEmisSchoolButton = null;
                        }
                    }

                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Siswa ditandai sudah masuk EMIS',
                        showConfirmButton: false,
                        timer: 1800
                    });

                    setTimeout(function () {
                        card.fadeOut(220, function () {
                            card.remove();
                            if (!$('#schoolMissingEmisStudents .simansa-emis-student').length) {
                                renderMissingEmisStudents([]);
                            }
                        });
                    }, 550);
                })
                .fail(function (xhr) {
                    button.prop('disabled', false)
                        .removeClass('btn-success')
                        .addClass('btn-outline-danger')
                        .html('<i class="far fa-circle mr-1"></i>Belum EMIS');
                    Swal.fire({
                        icon: 'error',
                        title: 'Status gagal diperbarui',
                        text: xhr.responseJSON?.message || 'Pastikan akun Anda memiliki akses Super Admin.',
                        confirmButtonText: 'Tutup'
                    });
                });
        });

        function appendBulkLog(statusClass, title, message) {
            const badgeClass = statusClass === 'success'
                ? 'badge-success'
                : (statusClass === 'danger' ? 'badge-danger' : (statusClass === 'warning' ? 'badge-warning' : 'badge-info'));
            const label = statusClass === 'success'
                ? 'Berhasil'
                : (statusClass === 'danger' ? 'Gagal' : (statusClass === 'warning' ? 'Sebagian' : 'Proses'));
            $('#bulkNpsnLog').prepend(`
                <div class="simansa-progress-log-row">
                    <div><span class="badge ${badgeClass}">${label}</span></div>
                    <div>
                        <strong>${escapeHtml(title)}</strong>
                        <div class="simansa-progress-log-meta">${escapeHtml(message)}</div>
                    </div>
                </div>
            `);
        }

        function setBulkProgress(done, total, text) {
            const percent = total > 0 ? Math.round((done / total) * 100) : 0;
            $('#bulkNpsnCounter').text(`${done}/${total}`);
            $('#bulkNpsnProgressText').text(text);
            $('#bulkNpsnProgressBar').css('width', `${percent}%`).attr('aria-valuenow', percent);
        }

        function configureBulkOverlay(mode) {
            if (mode === 'nsm') {
                $('#bulkProgressEyebrow').html('<i class="fas fa-database mr-1"></i>Bulk Lengkapi Sekolah');
                $('#bulkProgressTitle').text('Melengkapi Data Sekolah Asal');
                $('#bulkProgressDescription').text('Data umum diambil dari Referensi Kemendikdasmen, lalu NSM dan detail madrasah dilengkapi dari EMIS bila tersedia.');
            } else {
                $('#bulkProgressEyebrow').html('<i class="fas fa-id-card mr-1"></i>Bulk Check NPSN');
                $('#bulkProgressTitle').text('Mengecek NPSN Asal Sekolah');
                $('#bulkProgressDescription').text('Proses berjalan satu per satu agar layanan checker tetap stabil.');
            }
        }

        function runNpsnCheck(button, options = {}) {
            const url = button.data('url');
            const studentId = button.data('id');
            const studentName = button.data('name') || 'Siswa';
            const nisn = button.data('nisn') || '-';
            const statusBadge = $('#missing-npsn-status-' + studentId);

            button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Mengecek');
            statusBadge.removeClass('badge-warning badge-success badge-danger').addClass('badge-info').text('Mengecek...');

            return $.ajax({
                url,
                method: 'POST',
                data: {_token: csrfToken},
                success(response) {
                    statusBadge.removeClass('badge-info badge-warning badge-danger').addClass('badge-success').text(response.npsn);
                    button.removeClass('btn-primary').addClass('btn-success').html('<i class="fas fa-check mr-1"></i>Terisi');

                    if (options.log) {
                        appendBulkLog('success', `${studentName} (${nisn})`, `${response.school_name} (${response.npsn}) dari ${response.source}.`);
                    }

                    if (!options.silent) {
                        Swal.fire({
                            icon: 'success',
                            title: 'NPSN terisi',
                            text: `${response.school_name} (${response.npsn}) dari ${response.source}.`,
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error(xhr) {
                    const message = xhr.responseJSON?.message || 'Gagal mengecek data PPDB.';
                    statusBadge.removeClass('badge-info badge-warning badge-success').addClass('badge-danger').text('Gagal');
                    button.prop('disabled', false).removeClass('btn-success').addClass('btn-primary').html('<i class="fas fa-search mr-1"></i>Cek NISN');

                    if (options.log) {
                        appendBulkLog('danger', `${studentName} (${nisn})`, message);
                    }

                    if (!options.silent) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Belum ketemu',
                            text: message,
                            confirmButtonText: 'OK'
                        });
                    }
                }
            });
        }

        $('.btn-check-npsn').on('click', function () {
            runNpsnCheck($(this));
        });

        function runSchoolNsmCheck(button, options = {}) {
            const url = button.data('url');
            const npsn = button.data('npsn') || '-';
            const schoolName = button.data('school') || 'Sekolah';
            const statusText = $('#school-nsm-status-' + npsn);

            button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Mengecek');
            statusText.text(`NPSN: ${npsn} | NSM: mengecek...`);

            return $.ajax({
                url,
                method: 'POST',
                data: {_token: csrfToken},
                success(response) {
                    const complete = Boolean(response.complete && response.nsm);
                    const warningText = (response.warnings || []).filter(Boolean).join(' ');
                    statusText.text(`NPSN: ${response.npsn || npsn} | NSM: ${response.nsm || '-'}`);
                    button.removeClass('btn-primary btn-success btn-warning');

                    if (complete) {
                        button.addClass('btn-success').html('<i class="fas fa-check mr-1"></i>Terisi');
                    } else {
                        button.prop('disabled', false).addClass('btn-warning').html('<i class="fas fa-exclamation-triangle mr-1"></i>Sebagian');
                    }

                    if (options.log) {
                        const sources = (response.sources || []).join(' + ') || 'sumber resmi';
                        const logMessage = complete
                            ? `Data sekolah berhasil dilengkapi dari ${sources}.`
                            : `${response.message || 'NSM belum terisi.'}${warningText ? ` ${warningText}` : ''}`;
                        appendBulkLog(complete ? 'success' : 'warning', `${schoolName} (${npsn})`, logMessage);
                    }

                    if (!options.silent) {
                        Swal.fire({
                            icon: complete ? 'success' : 'warning',
                            title: complete ? 'NSM berhasil dilengkapi' : 'Data baru terisi sebagian',
                            text: `${response.message || `${schoolName} berhasil dilengkapi.`}${warningText ? ` ${warningText}` : ''}`,
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error(xhr) {
                    const message = xhr.responseJSON?.message || 'Data sekolah belum berhasil dilengkapi.';
                    statusText.text(`NPSN: ${npsn} | NSM: -`);
                    button.prop('disabled', false).removeClass('btn-success').addClass('btn-primary').html('<i class="fas fa-sync-alt mr-1"></i>Lengkapi');

                    if (options.log) {
                        appendBulkLog('danger', `${schoolName} (${npsn})`, message);
                    }

                    if (!options.silent) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Belum berhasil',
                            text: message,
                            confirmButtonText: 'OK'
                        });
                    }
                }
            });
        }

        $('.btn-check-school-nsm').on('click', function () {
            runSchoolNsmCheck($(this));
        });

        $('#btnCloseBulkOverlay').on('click', function () {
            $('#bulkNpsnOverlay').removeClass('is-active').attr('aria-hidden', 'true');
        });

        $('#btnBulkCheckNpsn').on('click', async function () {
            configureBulkOverlay('npsn');
            const buttons = $('.btn-check-npsn').filter(function () {
                return !$(this).hasClass('btn-success');
            }).toArray().map((el) => $(el));
            const total = buttons.length;

            if (total === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'Tidak ada data',
                    text: 'Semua siswa pada daftar ini sudah terisi atau tidak ada yang perlu dicek.',
                    confirmButtonText: 'OK'
                });
                return;
            }

            $('#bulkNpsnLog').empty();
            $('#bulkNpsnOverlay').addClass('is-active').attr('aria-hidden', 'false');
            $('#btnCloseBulkOverlay').prop('disabled', true);
            $('#btnBulkCheckNpsn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Bulk Check');
            setBulkProgress(0, total, 'Menyiapkan pengecekan...');

            let successCount = 0;
            let failedCount = 0;

            for (let index = 0; index < total; index++) {
                const button = buttons[index];
                const name = button.data('name') || 'Siswa';
                const nisn = button.data('nisn') || '-';

                setBulkProgress(index, total, `Mengecek ${name} (${nisn})...`);
                appendBulkLog('info', `${name} (${nisn})`, 'Sedang mengecek NISN dan asal sekolah...');

                try {
                    await runNpsnCheck(button, {silent: true, log: true});
                    successCount++;
                } catch (error) {
                    failedCount++;
                }

                setBulkProgress(index + 1, total, `${index + 1} dari ${total} siswa selesai dicek.`);
            }

            $('#btnCloseBulkOverlay').prop('disabled', false);
            $('#btnBulkCheckNpsn').prop('disabled', false).html('<i class="fas fa-tasks mr-1"></i>Bulk Check');
            setBulkProgress(total, total, `Selesai: ${successCount} berhasil, ${failedCount} gagal.`);
        });

        $('#btnBulkCheckNsm').on('click', async function () {
            configureBulkOverlay('nsm');
            const buttons = $('.btn-check-school-nsm').filter(function () {
                return !$(this).hasClass('btn-success');
            }).toArray().map((el) => $(el));
            const total = buttons.length;

            if (total === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'Tidak ada sekolah',
                    text: 'Tidak ada sekolah pada daftar ini yang perlu dilengkapi.',
                    confirmButtonText: 'OK'
                });
                return;
            }

            $('#bulkNpsnLog').empty();
            $('#bulkNpsnOverlay').addClass('is-active').attr('aria-hidden', 'false');
            $('#btnCloseBulkOverlay').prop('disabled', true);
            $('#btnBulkCheckNsm').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Bulk Lengkapi');
            setBulkProgress(0, total, 'Menyiapkan pelengkapan data sekolah...');

            let successCount = 0;
            let failedCount = 0;

            for (let index = 0; index < total; index++) {
                const button = buttons[index];
                const schoolName = button.data('school') || 'Sekolah';
                const npsn = button.data('npsn') || '-';

                setBulkProgress(index, total, `Mengecek ${schoolName} (${npsn})...`);
                appendBulkLog('info', `${schoolName} (${npsn})`, 'Mengambil data dari Referensi Kemendikdasmen dan EMIS jika madrasah.');

                try {
                    await runSchoolNsmCheck(button, {silent: true, log: true});
                    successCount++;
                } catch (error) {
                    failedCount++;
                }

                setBulkProgress(index + 1, total, `${index + 1} dari ${total} sekolah selesai dicek.`);
            }

            $('#btnCloseBulkOverlay').prop('disabled', false);
            $('#btnBulkCheckNsm').prop('disabled', false).html('<i class="fas fa-database mr-1"></i>Bulk Lengkapi');
            setBulkProgress(total, total, `Selesai: ${successCount} berhasil, ${failedCount} gagal.`);
        });

        function buildBarChart(canvasId, labels, values, color, horizontal = false) {
            const ctx = document.getElementById(canvasId);
            if (!ctx || typeof Chart === 'undefined') return null;

            return new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: color,
                        borderRadius: 10,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: horizontal ? 'y' : 'x',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return ' ' + formatNumber(context.raw) + ' siswa';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: { color: '#64748b' },
                            grid: { color: 'rgba(148, 163, 184, 0.15)' }
                        },
                        y: {
                            ticks: { color: '#64748b', precision: 0 },
                            grid: { color: 'rgba(148, 163, 184, 0.15)' }
                        }
                    }
                }
            });
        }

        function buildDoughnutChart(canvasId, source, colors) {
            const ctx = document.getElementById(canvasId);
            if (!ctx || typeof Chart === 'undefined') return null;

            return new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(source),
                    datasets: [{
                        data: Object.values(source),
                        backgroundColor: colors,
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 10,
                                color: '#475569'
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return ' ' + context.label + ': ' + formatNumber(context.raw) + ' siswa';
                                }
                            }
                        }
                    },
                    cutout: '66%'
                }
            });
        }

        function cacheKey(query) {
            return 'simansa-geocode-' + btoa(unescape(encodeURIComponent(query))).replace(/=/g, '');
        }

        function isValidCoordinatePair(lat, lon) {
            return Number.isFinite(lat)
                && Number.isFinite(lon)
                && lat >= -90
                && lat <= 90
                && lon >= -180
                && lon <= 180;
        }

        async function geocodePoint(point) {
            const key = cacheKey(point.location_query);
            const cached = localStorage.getItem(key);
            if (cached) {
                try {
                    const payload = JSON.parse(cached);
                    if (isValidCoordinatePair(payload.lat, payload.lon)) {
                        return payload;
                    }

                    localStorage.removeItem(key);
                } catch (error) {
                    localStorage.removeItem(key);
                }
            }

            const url = `https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&q=${encodeURIComponent(point.location_query)}`;
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                return null;
            }

            const result = await response.json();
            if (!result.length) {
                return null;
            }

            const payload = {
                lat: parseFloat(result[0].lat),
                lon: parseFloat(result[0].lon),
                display_name: result[0].display_name
            };

            if (!isValidCoordinatePair(payload.lat, payload.lon)) {
                return null;
            }

            localStorage.setItem(key, JSON.stringify(payload));
            return payload;
        }

        function markerStyle(type, count) {
            const radius = Math.max(10, Math.min(26, 10 + Math.round((count || 1) / 4)));
            return {
                radius: radius,
                fillColor: type === 'sekolah' ? '#14b8a6' : '#2563eb',
                color: '#ffffff',
                weight: 2,
                opacity: 1,
                fillOpacity: 0.82
            };
        }

        function fitLayerBounds(map, layer, maxFitBoundsZoom) {
            const bounds = layer.getBounds();
            if (!bounds.isValid()) {
                return;
            }

            const northEast = bounds.getNorthEast();
            const southWest = bounds.getSouthWest();
            const samePoint = northEast.lat === southWest.lat && northEast.lng === southWest.lng;

            if (samePoint) {
                map.setView([northEast.lat, northEast.lng], Math.min(maxFitBoundsZoom, 11));
                return;
            }

            map.fitBounds(bounds.pad(0.2), {
                maxZoom: maxFitBoundsZoom,
                animate: false
            });
        }

        async function populateLayer(map, points, layer, typeLabel, activeLayer, maxFitBoundsZoom) {
            for (const point of points) {
                try {
                    const location = await geocodePoint(point);
                    if (!location || !isValidCoordinatePair(location.lat, location.lon)) {
                        continue;
                    }

                    const marker = L.circleMarker([location.lat, location.lon], markerStyle(point.type, point.count));
                    marker.bindPopup(`
                        <div style="min-width:220px">
                            <strong>${point.label}</strong><br>
                            <span>${typeLabel}</span><br>
                            <span>Jumlah: <strong>${formatNumber(point.count)}</strong> siswa</span><br>
                            <small class="text-muted">${location.display_name}</small>
                        </div>
                    `);
                    layer.addLayer(marker);
                } catch (error) {
                    console.warn('Geocoding gagal untuk', point.location_query, error);
                }
            }

            if (activeLayer === (typeLabel === 'Alamat Domisili' ? 'address' : 'school')) {
                fitLayerBounds(map, layer, maxFitBoundsZoom);
            }
        }

        function bindChartDrilldown(canvasId, chart, sourceItems, buildParams) {
            const canvas = document.getElementById(canvasId);
            if (!canvas || !chart) {
                return;
            }

            canvas.addEventListener('click', function(event) {
                const points = chart.getElementsAtEventForMode(event, 'nearest', { intersect: true }, true) || [];
                if (!points.length) return;

                const item = sourceItems[points[0].index];
                if (!item) return;

                navigateToStudentList(buildParams(item));
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const levelSelect = document.getElementById('statTingkat');
            const classSelect = document.getElementById('statKelas');
            if (levelSelect && classSelect) {
                const refreshClasses = () => {
                    const level = levelSelect.value;
                    classSelect.disabled = !level;
                    Array.from(classSelect.options).forEach((option, index) => {
                        if (index === 0) return;
                        option.hidden = option.dataset.level !== level;
                        option.disabled = option.dataset.level !== level;
                    });
                    if (!level || classSelect.selectedOptions[0]?.disabled) classSelect.value = '';
                    classSelect.options[0].text = level ? 'Semua kelas tingkat ini' : 'Pilih tingkat dahulu';
                };
                levelSelect.addEventListener('change', refreshClasses);
                refreshClasses();
            }
            const completionChart = buildDoughnutChart('completionChart', completionData, ['#10b981', '#f59e0b']);
            const loginChart = buildDoughnutChart('loginChart', loginData, ['#2563eb', '#f43f5e']);

            const educationChartItems = educationSpread;
            const addressCityChartItems = addressCitySpread.slice(0, 10);
            const addressDistrictChartItems = addressDistrictSpread.slice(0, 10);
            const addressVillageChartItems = addressVillageSpread.slice(0, 10);
            const schoolCityChartItems = schoolCitySpread.slice(0, 10);

            const educationChart = buildBarChart(
                'educationSpreadChart',
                educationChartItems.map(item => item.label),
                educationChartItems.map(item => item.count),
                ['#2563eb', '#06b6d4', '#8b5cf6', '#22c55e', '#f59e0b'],
                true
            );

            const addressCityChart = buildBarChart(
                'addressCityChart',
                addressCityChartItems.map(item => item.name),
                addressCityChartItems.map(item => item.count),
                '#0ea5e9',
                true
            );

            const addressDistrictChart = buildBarChart(
                'addressDistrictChart',
                addressDistrictChartItems.map(item => item.name),
                addressDistrictChartItems.map(item => item.count),
                '#8b5cf6',
                true
            );

            const addressVillageChart = buildBarChart(
                'addressVillageChart',
                addressVillageChartItems.map(item => item.name),
                addressVillageChartItems.map(item => item.count),
                '#10b981',
                true
            );

            const schoolCityChart = buildBarChart(
                'schoolCityChart',
                schoolCityChartItems.map(item => item.name),
                schoolCityChartItems.map(item => item.count),
                '#14b8a6',
                true
            );

            bindChartDrilldown('educationSpreadChart', educationChart, educationChartItems, function(item) {
                return { education_form: item.label };
            });

            bindChartDrilldown('addressCityChart', addressCityChart, addressCityChartItems, function(item) {
                return { address_scope: 'city', address_name: item.name, province_name: item.province_name };
            });

            bindChartDrilldown('addressDistrictChart', addressDistrictChart, addressDistrictChartItems, function(item) {
                return { address_scope: 'district', address_name: item.name, province_name: item.province_name };
            });

            bindChartDrilldown('addressVillageChart', addressVillageChart, addressVillageChartItems, function(item) {
                return {
                    address_scope: 'village',
                    address_name: item.name,
                    district_name: item.district_name,
                    city_name: item.city_name,
                    province_name: item.province_name
                };
            });

            bindChartDrilldown('schoolCityChart', schoolCityChart, schoolCityChartItems, function(item) {
                return { school_city_name: item.name, school_province_name: item.province_name };
            });

            const mapElement = document.getElementById('studentSpreadMap');
            if (!mapElement || typeof L === 'undefined') {
                return;
            }

            const DEFAULT_MAP_CENTER = [-2.5, 118];
            const DEFAULT_MAP_ZOOM = 5;
            const MAX_FIT_BOUNDS_ZOOM = 12;
            const map = L.map('studentSpreadMap', {
                scrollWheelZoom: false,
                minZoom: 4,
                maxZoom: 17
            }).setView(DEFAULT_MAP_CENTER, DEFAULT_MAP_ZOOM);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            const addressLayer = L.layerGroup().addTo(map);
            const schoolLayer = L.layerGroup();
            let activeLayer = 'address';

            function setLayer(type) {
                activeLayer = type;

                if (type === 'address') {
                    if (!map.hasLayer(addressLayer)) map.addLayer(addressLayer);
                    if (map.hasLayer(schoolLayer)) map.removeLayer(schoolLayer);
                    document.getElementById('toggleAddressLayer').className = 'btn btn-sm btn-primary';
                    document.getElementById('toggleSchoolLayer').className = 'btn btn-sm btn-outline-primary';
                    fitLayerBounds(map, addressLayer, MAX_FIT_BOUNDS_ZOOM);
                } else {
                    if (!map.hasLayer(schoolLayer)) map.addLayer(schoolLayer);
                    if (map.hasLayer(addressLayer)) map.removeLayer(addressLayer);
                    document.getElementById('toggleAddressLayer').className = 'btn btn-sm btn-outline-primary';
                    document.getElementById('toggleSchoolLayer').className = 'btn btn-sm btn-primary';
                    fitLayerBounds(map, schoolLayer, MAX_FIT_BOUNDS_ZOOM);
                }
            }

            document.getElementById('toggleAddressLayer')?.addEventListener('click', function() {
                setLayer('address');
            });

            document.getElementById('toggleSchoolLayer')?.addEventListener('click', function() {
                setLayer('school');
            });

            window.addEventListener('load', function() {
                map.invalidateSize();
            });

            populateLayer(map, mapAddressPoints, addressLayer, 'Alamat Domisili', activeLayer, MAX_FIT_BOUNDS_ZOOM);
            populateLayer(map, mapSchoolPoints, schoolLayer, 'Asal Sekolah', activeLayer, MAX_FIT_BOUNDS_ZOOM);
        });
    </script>
@stop

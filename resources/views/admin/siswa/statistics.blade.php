@extends('adminlte::page')

@section('title', 'Statistik Siswa - SIMANSA')

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
<div class="row">
    <div class="col-md-6 col-xl mb-4">
        <a href="{{ route('admin.siswa.index') }}" class="simansa-kpi-link">
            <div class="simansa-kpi simansa-kpi--blue">
                <div class="simansa-kpi__icon"><i class="fas fa-users"></i></div>
                <div class="simansa-kpi__body">
                    <div class="simansa-kpi__label">Total Siswa</div>
                    <div class="simansa-kpi__value">{{ number_format($kpi['total_siswa']) }}</div>
                    <div class="simansa-kpi__desc">Seluruh siswa yang saat ini tercatat di SIMANSA.</div>
                    <div class="simansa-kpi__view-link"><i class="fas fa-arrow-right mr-1"></i>Lihat Semua Siswa</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-xl mb-4">
        <a href="{{ route('admin.siswa.index', ['status' => 'lengkap']) }}" class="simansa-kpi-link">
            <div class="simansa-kpi simansa-kpi--green">
                <div class="simansa-kpi__icon"><i class="fas fa-check-circle"></i></div>
                <div class="simansa-kpi__body">
                    <div class="simansa-kpi__label">Data Lengkap</div>
                    <div class="simansa-kpi__value">{{ number_format($kpi['data_lengkap']) }}</div>
                    <div class="simansa-kpi__desc">Data diri dan data orang tua sudah lengkap.</div>
                    <div class="simansa-kpi__view-link"><i class="fas fa-arrow-right mr-1"></i>Lihat Daftar</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-xl mb-4">
        <a href="{{ route('admin.siswa.index', ['login_status' => 'sudah']) }}" class="simansa-kpi-link">
            <div class="simansa-kpi simansa-kpi--amber">
                <div class="simansa-kpi__icon"><i class="fas fa-sign-in-alt"></i></div>
                <div class="simansa-kpi__body">
                    <div class="simansa-kpi__label">Sudah Login</div>
                    <div class="simansa-kpi__value">{{ number_format($kpi['sudah_login']) }}</div>
                    <div class="simansa-kpi__desc">Sudah pernah tercatat login ke sistem.</div>
                    <div class="simansa-kpi__view-link"><i class="fas fa-arrow-right mr-1"></i>Lihat Daftar</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-xl mb-4">
        <a href="{{ route('admin.siswa.index', ['login_status' => 'belum']) }}" class="simansa-kpi-link">
            <div class="simansa-kpi simansa-kpi--rose">
                <div class="simansa-kpi__icon"><i class="fas fa-user-clock"></i></div>
                <div class="simansa-kpi__body">
                    <div class="simansa-kpi__label">Belum Pernah Login</div>
                    <div class="simansa-kpi__value">{{ number_format($kpi['belum_pernah_login']) }}</div>
                    <div class="simansa-kpi__desc">Akun aktif tetapi belum pernah punya riwayat login.</div>
                    <div class="simansa-kpi__view-link"><i class="fas fa-arrow-right mr-1"></i>Lihat Daftar</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-xl mb-4">
        <a href="{{ route('admin.siswa.index', ['npsn_status' => 'kosong']) }}" class="simansa-kpi-link">
            <div class="simansa-kpi simansa-kpi--slate">
                <div class="simansa-kpi__icon"><i class="fas fa-school"></i></div>
                <div class="simansa-kpi__body">
                    <div class="simansa-kpi__label">NPSN Kosong</div>
                    <div class="simansa-kpi__value">{{ number_format($kpi['npsn_kosong']) }}</div>
                    <div class="simansa-kpi__desc">{{ number_format($kpi['npsn_kosong_kelas_10']) }} siswa kelas 10 perlu dicek dari data PPDB.</div>
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
                    <h3>NPSN Kosong Kelas 10</h3>
                    <p>Gunakan checker NISN SIMANSA untuk mengisi NPSN asal sekolah. Jika belum ditemukan, sistem mencoba data matrikulasi/PPDB.</p>
                </div>
                <div class="simansa-section-actions">
                    <button type="button" class="btn btn-sm btn-primary" id="btnBulkCheckNpsn" @if($missingNpsnKelas10->isEmpty()) disabled @endif>
                        <i class="fas fa-tasks mr-1"></i>Bulk Check
                    </button>
                    <a href="{{ route('admin.siswa.index', ['npsn_status' => 'kosong', 'tingkat' => 10]) }}" class="btn btn-sm btn-outline-primary">
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
                        @forelse($missingNpsnKelas10 as $index => $student)
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
                                <td colspan="7" class="text-center text-muted py-4">Tidak ada siswa kelas 10 dengan NPSN asal sekolah kosong.</td>
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
                        <a href="{{ route('admin.siswa.index', ['address_scope' => 'province', 'address_name' => $item['name']]) }}" class="btn btn-xs btn-outline-primary ml-auto">
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
    <div class="col-12 col-xl-7 mb-4">
        @php
            $nsmCheckCandidates = $topSchools->filter(fn ($school) => filled($school['npsn'] ?? null));
        @endphp
        <section class="simansa-analytics-section">
            <div class="simansa-section-head">
                <div>
                    <h3>Sekolah Terbanyak</h3>
                    <p>Daftar sekolah asal dengan jumlah siswa tertinggi.</p>
                </div>
                <button type="button" class="btn btn-sm btn-primary" id="btnBulkCheckNsm" @if($nsmCheckCandidates->isEmpty()) disabled @endif>
                    <i class="fas fa-database mr-1"></i>Bulk Lengkapi
                </button>
            </div>
            <div class="table-responsive simansa-table-shell">
                <table class="table table-hover simansa-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Sekolah</th>
                            <th>Bentuk</th>
                            <th>Wilayah</th>
                            <th class="text-right">Jumlah</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topSchools as $index => $school)
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
                                </td>
                                <td>{{ $school['education_form'] }}</td>
                                <td>{{ collect([$school['city_name'], $school['province_name']])->filter()->implode(', ') ?: '-' }}</td>
                                <td class="text-right font-weight-bold">{{ number_format($school['count']) }}</td>
                                <td class="text-right">
                                    @if($canCheckNsm)
                                        <button
                                            type="button"
                                            class="btn btn-xs btn-primary btn-check-school-nsm mb-1"
                                            data-url="{{ route('admin.siswa.statistics.check-school-nsm', $school['npsn']) }}"
                                            data-npsn="{{ $school['npsn'] }}"
                                            data-school="{{ $school['school_name'] }}">
                                            <i class="fas fa-sync-alt mr-1"></i>Lengkapi
                                        </button>
                                    @endif
                                    <a href="{{ route('admin.siswa.index', ['school_npsn' => $school['npsn'], 'school_name' => $school['school_name'], 'education_form' => $school['education_form'], 'school_city_name' => $school['city_name'], 'school_province_name' => $school['province_name']]) }}" class="btn btn-xs btn-outline-primary">
                                        Lihat Siswa
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Belum ada data asal sekolah yang bisa ditampilkan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
    <div class="col-12 col-xl-5 mb-4">
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
                                    <a href="{{ route('admin.siswa.index', ['address_scope' => 'city', 'address_name' => $city['name'], 'province_name' => $city['province_name']]) }}" class="btn btn-xs btn-outline-primary">
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
        }
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
        const schoolCitySpread = @json($schoolCitySpread);
        const mapAddressPoints = @json($mapAddressPoints);
        const mapSchoolPoints = @json($mapSchoolPoints);
        const siswaIndexBaseUrl = @json(route('admin.siswa.index'));
        const csrfToken = @json(csrf_token());

        function formatNumber(value) {
            return new Intl.NumberFormat('id-ID').format(value || 0);
        }

        function navigateToStudentList(params) {
            const url = new URL(siswaIndexBaseUrl, window.location.origin);
            Object.entries(params).forEach(([key, value]) => {
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

        function appendBulkLog(statusClass, title, message) {
            const badgeClass = statusClass === 'success' ? 'badge-success' : (statusClass === 'danger' ? 'badge-danger' : 'badge-info');
            const label = statusClass === 'success' ? 'Berhasil' : (statusClass === 'danger' ? 'Gagal' : 'Proses');
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
                    statusText.text(`NPSN: ${response.npsn || npsn} | NSM: ${response.nsm || '-'}`);
                    button.removeClass('btn-primary').addClass('btn-success').html('<i class="fas fa-check mr-1"></i>Terisi');

                    if (options.log) {
                        const sources = (response.sources || []).join(' + ') || 'sumber resmi';
                        appendBulkLog('success', `${schoolName} (${npsn})`, `Data sekolah berhasil dilengkapi dari ${sources}.`);
                    }

                    if (!options.silent) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Data sekolah diperbarui',
                            text: response.message || `${schoolName} berhasil dilengkapi.`,
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
            const completionChart = buildDoughnutChart('completionChart', completionData, ['#10b981', '#f59e0b']);
            const loginChart = buildDoughnutChart('loginChart', loginData, ['#2563eb', '#f43f5e']);

            const educationChartItems = educationSpread;
            const addressCityChartItems = addressCitySpread.slice(0, 10);
            const addressDistrictChartItems = addressDistrictSpread.slice(0, 10);
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

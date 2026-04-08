@extends('adminlte::page')

@section('title', 'Kesehatan Data Akademik')

@section('content_header')
    <div class="simansa-page-hero">
        <div class="simansa-page-hero__content">
            <div class="simansa-page-hero__eyebrow">
                <i class="fas fa-shield-alt"></i>
                Quality Dashboard
            </div>
            <h1 class="simansa-page-hero__title">Kesehatan Data Akademik</h1>
            <p class="simansa-page-hero__subtitle">
                Pantau kesehatan data inti SIMANSA sebelum rollover tahun ajaran, mutasi, dan finalisasi nilai.
            </p>
        </div>
        <div class="simansa-page-hero__meta">
            <div class="simansa-hero-chip">
                <span class="simansa-hero-chip__label">Tahun Aktif</span>
                <span class="simansa-hero-chip__value">{{ $audit['tahun_pelajaran_aktif'] ?? '-' }}</span>
            </div>
            <div class="simansa-hero-chip">
                <span class="simansa-hero-chip__label">Status</span>
                <span class="simansa-hero-chip__value">{{ ($audit['summary']['issues'] ?? 0) === 0 ? 'Sehat' : 'Perlu Tindak Lanjut' }}</span>
            </div>
        </div>
    </div>
@stop

@section('content')
    <style>
        .academic-health-stat {
            position: relative;
            overflow: hidden;
            border: 0;
            border-radius: 22px;
            color: #fff;
            min-height: 182px;
            padding: 1.4rem 1.4rem 1.2rem;
            box-shadow: 0 24px 50px rgba(15, 23, 42, .12);
        }

        .academic-health-stat::after {
            content: "";
            position: absolute;
            right: -26px;
            bottom: -32px;
            width: 138px;
            height: 138px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .11);
        }

        .academic-health-stat--blue { background: linear-gradient(135deg, #4f46e5, #6366f1); }
        .academic-health-stat--green { background: linear-gradient(135deg, #10b981, #34d399); }
        .academic-health-stat--amber { background: linear-gradient(135deg, #f59e0b, #fbbf24); color: #172554; }
        .academic-health-stat--rose { background: linear-gradient(135deg, #fb7185, #f43f5e); }

        .academic-health-stat__label {
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            opacity: .88;
            margin-bottom: .8rem;
        }

        .academic-health-stat__value {
            font-size: 2.2rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: .9rem;
        }

        .academic-health-stat__desc {
            position: relative;
            z-index: 1;
            font-size: .97rem;
            line-height: 1.55;
            opacity: .95;
        }

        .health-panel {
            border: 0;
            border-radius: 22px;
            box-shadow: 0 22px 48px rgba(15, 23, 42, .08);
            overflow: hidden;
        }

        .health-panel .card-header {
            background: linear-gradient(135deg, rgba(37, 99, 235, .98), rgba(13, 148, 136, .9));
            color: #fff;
            border-bottom: 0;
            padding: 1rem 1.25rem;
        }

        .health-table th {
            white-space: nowrap;
            color: #475569;
            font-weight: 700;
            background: #f8fafc;
        }

        .health-check-title {
            color: #0f172a;
            font-weight: 700;
        }

        .health-badge {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .38rem .8rem;
            border-radius: 999px;
            font-size: .84rem;
            font-weight: 700;
        }

        .health-badge--ok {
            background: rgba(34, 197, 94, .12);
            color: #166534;
        }

        .health-badge--warn {
            background: rgba(239, 68, 68, .12);
            color: #b91c1c;
        }

        .health-empty {
            border: 1px dashed rgba(148, 163, 184, .45);
            border-radius: 18px;
            padding: 1.15rem 1.2rem;
            background: rgba(248, 250, 252, .9);
            color: #475569;
        }
    </style>

    @if(!($audit['ok'] ?? false))
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ $audit['message'] ?? 'Audit belum dapat dijalankan.' }}
        </div>
    @endif

    <div class="row">
        <div class="col-md-6 col-xl-3 mb-4">
            <div class="academic-health-stat academic-health-stat--blue">
                <div class="academic-health-stat__label">Total Pengecekan</div>
                <div class="academic-health-stat__value">{{ $audit['summary']['total_checks'] ?? 0 }}</div>
                <div class="academic-health-stat__desc">Jumlah indikator yang dipantau untuk kelas, mutasi, dan nilai.</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3 mb-4">
            <div class="academic-health-stat academic-health-stat--green">
                <div class="academic-health-stat__label">Check Sehat</div>
                <div class="academic-health-stat__value">{{ $audit['summary']['healthy_checks'] ?? 0 }}</div>
                <div class="academic-health-stat__desc">Indikator yang saat ini bersih dan tidak memerlukan pembersihan data.</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3 mb-4">
            <div class="academic-health-stat academic-health-stat--rose">
                <div class="academic-health-stat__label">Masalah Aktif</div>
                <div class="academic-health-stat__value">{{ $audit['summary']['issues'] ?? 0 }}</div>
                <div class="academic-health-stat__desc">Poin yang perlu ditindaklanjuti sebelum pergantian tahun ajaran.</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3 mb-4">
            <div class="academic-health-stat academic-health-stat--amber">
                <div class="academic-health-stat__label">Sampel Tersedia</div>
                <div class="academic-health-stat__value">{{ $audit['summary']['warnings'] ?? 0 }}</div>
                <div class="academic-health-stat__desc">Kelompok data yang sudah disiapkan contoh record-nya untuk ditelusuri cepat.</div>
            </div>
        </div>
    </div>

    <div class="card health-panel mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0"><i class="fas fa-clipboard-check mr-2"></i>Ringkasan Audit Akademik</h3>
            <span class="badge badge-light px-3 py-2">{{ now()->format('d M Y H:i') }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 health-table">
                    <thead>
                        <tr>
                            <th style="width: 58px;">#</th>
                            <th>Indikator</th>
                            <th style="width: 140px;">Jumlah</th>
                            <th style="width: 180px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($checks as $index => $check)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="health-check-title">{{ $check['label'] }}</div>
                                </td>
                                <td class="font-weight-bold">{{ number_format($check['count']) }}</td>
                                <td>
                                    @if(($check['count'] ?? 0) === 0)
                                        <span class="health-badge health-badge--ok">
                                            <i class="fas fa-check-circle"></i> Sehat
                                        </span>
                                    @else
                                        <span class="health-badge health-badge--warn">
                                            <i class="fas fa-exclamation-triangle"></i> Perlu Dicek
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-5 mb-4">
            <div class="card health-panel h-100">
                <div class="card-header">
                    <h3 class="card-title mb-0"><i class="fas fa-bullseye mr-2"></i>Prioritas Tindak Lanjut</h3>
                </div>
                <div class="card-body">
                    @if($criticalChecks->isEmpty())
                        <div class="health-empty">
                            <i class="fas fa-check-circle text-success mr-1"></i>
                            Audit akademik saat ini bersih. SIMANSA siap dipakai tanpa warning prioritas tinggi.
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($criticalChecks as $check)
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="pr-3">
                                            <div class="font-weight-bold text-danger">{{ $check['label'] }}</div>
                                            <small class="text-muted">Periksa data terkait sebelum rollover atau penguncian nilai.</small>
                                        </div>
                                        <span class="badge badge-danger px-3 py-2">{{ number_format($check['count']) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-7 mb-4">
            <div class="card health-panel h-100">
                <div class="card-header">
                    <h3 class="card-title mb-0"><i class="fas fa-lightbulb mr-2"></i>Catatan Operasional</h3>
                </div>
                <div class="card-body">
                    <div class="health-empty mb-3">
                        Jalankan audit ini sebelum:
                        finalisasi mutasi, aktivasi tahun pelajaran baru, penutupan nilai semester, dan promosi siswa ke status alumni.
                    </div>
                    <ul class="mb-0 pl-3 text-muted" style="line-height: 1.8;">
                        <li>Fokus utama tetap pada <strong>siswa_kelas</strong> sebagai sumber data akademik tahunan.</li>
                        <li><strong>kelas_saat_ini_id</strong> dipantau sebagai cache agar tidak drift dari histori aktif.</li>
                        <li>Jika ada masalah, benahi data sumbernya lebih dulu sebelum rollover tahun pelajaran.</li>
                        <li>Gunakan command <code>php artisan akademik:audit --json</code> bila butuh output teknis untuk backup/otomasi.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @foreach($samples as $title => $rows)
        <div class="card health-panel mb-4">
            <div class="card-header">
                <h3 class="card-title mb-0"><i class="fas fa-search mr-2"></i>{{ $title }}</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0 health-table">
                        <thead>
                            <tr>
                                @foreach(array_keys((array) $rows[0]) as $column)
                                    <th>{{ \Illuminate\Support\Str::headline($column) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $row)
                                <tr>
                                    @foreach((array) $row as $value)
                                        <td>{{ $value ?: '-' }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
@stop

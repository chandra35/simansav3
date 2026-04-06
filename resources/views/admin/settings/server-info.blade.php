@extends('adminlte::page')

@section('title', 'Detail Server')

@section('content_header')
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between">
        <div>
            <h1 class="mb-1"><i class="fas fa-server text-primary mr-2"></i>Detail Server</h1>
            <p class="text-muted mb-0">Ringkasan identitas server, konfigurasi aplikasi, DNS, SSL, dan lookup IP untuk membantu identifikasi hosting SIMANSA.</p>
        </div>
        <div class="mt-3 mt-lg-0">
            <a href="{{ route('admin.settings.server-info') }}" class="btn btn-outline-primary">
                <i class="fas fa-sync-alt mr-1"></i> Refresh Data
            </a>
        </div>
    </div>
@stop

@section('content')
    <style>
        .server-info-card {
            border: 0;
            border-radius: 18px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }

        .server-info-card .card-header {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.96), rgba(13, 148, 136, 0.88));
            color: #fff;
            border-bottom: 0;
            padding: 1rem 1.25rem;
        }

        .server-kpi {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.96), rgba(255, 255, 255, 0.98));
            border: 1px solid rgba(148, 163, 184, 0.18);
            border-radius: 16px;
            padding: 1rem 1.1rem;
            height: 100%;
        }

        .server-kpi-label {
            color: #64748b;
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .05em;
            text-transform: uppercase;
            margin-bottom: .45rem;
        }

        .server-kpi-value {
            color: #0f172a;
            font-size: 1.1rem;
            font-weight: 700;
            line-height: 1.35;
            word-break: break-word;
        }

        .server-info-table th {
            width: 34%;
            color: #475569;
            font-weight: 700;
            background: #f8fafc;
        }

        .server-info-table td {
            color: #0f172a;
            word-break: break-word;
        }

        .server-code {
            background: #0f172a;
            color: #e2e8f0;
            border-radius: 14px;
            padding: 1rem;
            font-size: .84rem;
            max-height: 320px;
            overflow: auto;
            white-space: pre-wrap;
        }

        .server-pill {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .4rem .75rem;
            border-radius: 999px;
            font-weight: 700;
            font-size: .82rem;
        }

        .server-pill-ok {
            background: rgba(34, 197, 94, .12);
            color: #166534;
        }

        .server-pill-muted {
            background: rgba(148, 163, 184, .14);
            color: #475569;
        }
    </style>

    <div class="row">
        <div class="col-md-4 col-xl-2 mb-3">
            <div class="server-kpi">
                <div class="server-kpi-label">Public IP</div>
                <div class="server-kpi-value">{{ $server['public_ip'] ?? '-' }}</div>
            </div>
        </div>
        <div class="col-md-4 col-xl-2 mb-3">
            <div class="server-kpi">
                <div class="server-kpi-label">Resolved Host IP</div>
                <div class="server-kpi-value">{{ $server['resolved_ip'] ?? '-' }}</div>
            </div>
        </div>
        <div class="col-md-4 col-xl-2 mb-3">
            <div class="server-kpi">
                <div class="server-kpi-label">Laravel</div>
                <div class="server-kpi-value">{{ $server['laravel_version'] }}</div>
            </div>
        </div>
        <div class="col-md-4 col-xl-2 mb-3">
            <div class="server-kpi">
                <div class="server-kpi-label">PHP</div>
                <div class="server-kpi-value">{{ $server['php_version'] }}</div>
            </div>
        </div>
        <div class="col-md-4 col-xl-2 mb-3">
            <div class="server-kpi">
                <div class="server-kpi-label">Disk Free</div>
                <div class="server-kpi-value">{{ $resources['disk_free'] ?? '-' }}</div>
            </div>
        </div>
        <div class="col-md-4 col-xl-2 mb-3">
            <div class="server-kpi">
                <div class="server-kpi-label">Database</div>
                <div class="server-kpi-value">{{ $database['database'] ?? '-' }}</div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card server-info-card">
                <div class="card-header">
                    <h3 class="card-title mb-0"><i class="fas fa-network-wired mr-2"></i>Identitas Server & Aplikasi</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0 server-info-table">
                            <tbody>
                                <tr><th>APP URL</th><td>{{ $server['app_url'] ?: '-' }}</td></tr>
                                <tr><th>Host</th><td>{{ $server['app_host'] }}</td></tr>
                                <tr><th>Skema</th><td>{{ $server['app_scheme'] }}</td></tr>
                                <tr><th>Environment</th><td>{{ $server['app_env'] }}</td></tr>
                                <tr><th>Hostname</th><td>{{ $server['hostname'] }}</td></tr>
                                <tr><th>Server Name</th><td>{{ $server['server_name'] }}</td></tr>
                                <tr><th>Server Address</th><td>{{ $server['server_addr'] ?? '-' }}</td></tr>
                                <tr><th>OS</th><td>{{ $server['os'] }}</td></tr>
                                <tr><th>Arsitektur</th><td>{{ $server['architecture'] }}</td></tr>
                                <tr><th>Timezone</th><td>{{ $server['timezone'] }}</td></tr>
                                <tr><th>Waktu Server</th><td>{{ $server['server_time'] }}</td></tr>
                                <tr><th>Server Software</th><td>{{ $server['server_software'] }}</td></tr>
                                <tr><th>Path Aplikasi</th><td>{{ $server['base_path'] }}</td></tr>
                                <tr><th>Storage</th><td>{{ $server['storage_path'] }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card server-info-card">
                <div class="card-header">
                    <h3 class="card-title mb-0"><i class="fas fa-sliders-h mr-2"></i>Resource & Runtime</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0 server-info-table">
                            <tbody>
                                <tr><th>Disk Total</th><td>{{ $resources['disk_total'] ?? '-' }}</td></tr>
                                <tr><th>Disk Free</th><td>{{ $resources['disk_free'] ?? '-' }}</td></tr>
                                <tr><th>Memory Limit</th><td>{{ $resources['memory_limit'] ?: '-' }}</td></tr>
                                <tr><th>Max Execution</th><td>{{ $resources['max_execution_time'] ?: '-' }}</td></tr>
                                <tr><th>Upload Max</th><td>{{ $resources['upload_max_filesize'] ?: '-' }}</td></tr>
                                <tr><th>Post Max</th><td>{{ $resources['post_max_size'] ?: '-' }}</td></tr>
                                <tr><th>Load Average</th><td>{{ $resources['load_average'] ?: '-' }}</td></tr>
                                <tr><th>Cache Driver</th><td>{{ $application['cache_driver'] }}</td></tr>
                                <tr><th>Session Driver</th><td>{{ $application['session_driver'] }}</td></tr>
                                <tr><th>Queue Driver</th><td>{{ $application['queue_driver'] }}</td></tr>
                                <tr><th>Mailer</th><td>{{ $application['mailer'] }}</td></tr>
                                <tr><th>Debug</th><td>{{ $application['app_debug'] }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card server-info-card">
                <div class="card-header">
                    <h3 class="card-title mb-0"><i class="fas fa-database mr-2"></i>Koneksi Database</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="server-pill {{ str_starts_with($database['status'], 'Terhubung') ? 'server-pill-ok' : 'server-pill-muted' }}">
                            <i class="fas {{ str_starts_with($database['status'], 'Terhubung') ? 'fa-check-circle' : 'fa-exclamation-triangle' }}"></i>
                            {{ $database['status'] }}
                        </span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0 server-info-table">
                            <tbody>
                                <tr><th>Driver</th><td>{{ $database['driver'] ?: '-' }}</td></tr>
                                <tr><th>Host</th><td>{{ $database['host'] ?: '-' }}</td></tr>
                                <tr><th>Port</th><td>{{ $database['port'] ?: '-' }}</td></tr>
                                <tr><th>Database</th><td>{{ $database['database'] ?: '-' }}</td></tr>
                                <tr><th>Username</th><td>{{ $database['username'] ?: '-' }}</td></tr>
                                <tr><th>Total Tabel</th><td>{{ $database['table_count'] ?? '-' }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card server-info-card">
                <div class="card-header">
                    <h3 class="card-title mb-0"><i class="fas fa-lock mr-2"></i>SSL & DNS</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive mb-4">
                        <table class="table table-striped table-hover mb-0 server-info-table">
                            <tbody>
                                <tr><th>Status SSL</th><td>{{ $sslInfo['status'] ?? 'Tidak dicek' }}</td></tr>
                                <tr><th>Subject</th><td>{{ $sslInfo['subject'] ?? '-' }}</td></tr>
                                <tr><th>Issuer</th><td>{{ $sslInfo['issuer'] ?? '-' }}</td></tr>
                                <tr><th>Berlaku Mulai</th><td>{{ $sslInfo['valid_from'] ?? '-' }}</td></tr>
                                <tr><th>Berlaku Sampai</th><td>{{ $sslInfo['valid_to'] ?? '-' }}</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Tipe</th>
                                    <th>Host</th>
                                    <th>Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dnsRecords as $record)
                                    <tr>
                                        <td>{{ $record['type'] ?? '-' }}</td>
                                        <td>{{ $record['host'] ?? '-' }}</td>
                                        <td>{{ $record['ip'] ?? $record['ipv6'] ?? $record['target'] ?? $record['txt'] ?? $record['pri'] ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">Record DNS tidak terbaca dari server ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card server-info-card">
                <div class="card-header">
                    <h3 class="card-title mb-0"><i class="fas fa-globe-asia mr-2"></i>Lookup IP / RDAP</h3>
                </div>
                <div class="card-body">
                    @if($ipLookup && !empty($ipLookup['rdap']))
                        <div class="table-responsive mb-3">
                            <table class="table table-striped table-hover mb-0 server-info-table">
                                <tbody>
                                    <tr><th>IP</th><td>{{ $server['public_ip'] ?? '-' }}</td></tr>
                                    <tr><th>Name</th><td>{{ $ipLookup['rdap']['name'] ?? '-' }}</td></tr>
                                    <tr><th>Handle</th><td>{{ $ipLookup['rdap']['handle'] ?? '-' }}</td></tr>
                                    <tr><th>Country</th><td>{{ $ipLookup['rdap']['country'] ?? '-' }}</td></tr>
                                    <tr><th>Type</th><td>{{ $ipLookup['rdap']['type'] ?? '-' }}</td></tr>
                                    <tr><th>Range</th><td>{{ ($ipLookup['rdap']['start_address'] ?? '-') . ' s/d ' . ($ipLookup['rdap']['end_address'] ?? '-') }}</td></tr>
                                    <tr><th>WHOIS Server</th><td>{{ $ipLookup['rdap']['port43'] ?? '-' }}</td></tr>
                                </tbody>
                            </table>
                        </div>

                        @if(!empty($ipLookup['rdap']['entities']))
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Entitas</th>
                                            <th>Role</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($ipLookup['rdap']['entities'] as $entity)
                                            <tr>
                                                <td>{{ $entity['handle'] ?: '-' }}</td>
                                                <td>{{ $entity['roles'] ?: '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @elseif(!empty($ipLookup['rdap']['error']))
                            <div class="alert alert-warning mb-0">{{ $ipLookup['rdap']['error'] }}</div>
                        @endif
                    @else
                        <div class="alert alert-warning mb-0">
                            Lookup RDAP belum tersedia dari server ini. Coba refresh lagi setelah koneksi internet server stabil.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card server-info-card">
                <div class="card-header">
                    <h3 class="card-title mb-0"><i class="fas fa-search mr-2"></i>WHOIS Ringkas</h3>
                </div>
                <div class="card-body">
                    @if(!empty($ipLookup['whois']['summary']))
                        <div class="server-code">{{ implode("\n", $ipLookup['whois']['summary']) }}</div>
                    @elseif(!empty($ipLookup['whois']['error']))
                        <div class="alert alert-warning mb-0">{{ $ipLookup['whois']['error'] }}</div>
                    @else
                        <div class="alert alert-secondary mb-0">
                            Utility WHOIS belum tersedia di server ini, jadi halaman memakai lookup RDAP sebagai sumber utama identifikasi IP.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

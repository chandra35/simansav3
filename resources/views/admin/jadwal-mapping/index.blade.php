@extends('adminlte::page')

@section('title', 'Mapping Jadwal')
@section('plugins.Select2', true)
@section('plugins.Sweetalert2', true)

@section('content')
<div class="container-fluid py-3 jadwal-mapping-page">
    <section class="mapping-hero mb-3">
        <div>
            <div class="hero-eyebrow"><i class="fas fa-link mr-2"></i>INTEGRASI JADWAL</div>
            <h1>Mapping Kode Guru & Mapel</h1>
            <p>Kunci kode Excel ke data SIMANSA secara aman, terukur, dan tetap dapat diedit pada tahun berikutnya.</p>
        </div>
        <div class="hero-actions">
            <a href="{{ route('admin.mapel.index') }}" class="btn btn-light">
                <i class="fas fa-book mr-1"></i> Daftar Mapel
            </a>
            <a href="{{ route('admin.jadwal-pelajaran.index') }}" class="btn btn-warning">
                <i class="fas fa-calendar-alt mr-1"></i> Isi Jadwal
            </a>
        </div>
    </section>

    @if(session('success'))
        <div id="flash-success" data-message="{{ session('success') }}"></div>
    @endif
    @if(session('error'))
        <div id="flash-error" data-message="{{ session('error') }}"></div>
    @endif

    <section class="mapping-toolbar mb-3">
        <form method="GET" action="{{ route('admin.jadwal-mapping.index') }}" class="year-form">
            <div>
                <label>Tahun Pelajaran</label>
                <select name="tahun_pelajaran_id" class="form-control" onchange="this.form.submit()">
                    @foreach($tahunList as $item)
                        <option value="{{ $item->id }}" @selected($tahun?->id === $item->id)>
                            {{ $item->nama }}{{ $item->is_active ? ' · Aktif' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
        @can('manage-jadwal-mapping')
            @if($tahun && (int) $tahun->tahun_mulai === 2026 && (int) $tahun->tahun_selesai === 2027)
                <form id="refresh-mapping-form" method="POST" action="{{ route('admin.jadwal-mapping.refresh') }}">
                    @csrf
                    <input type="hidden" name="tahun_pelajaran_id" value="{{ $tahun->id }}">
                    <button type="button" class="btn btn-primary" onclick="confirmRefreshMapping()">
                        <i class="fas fa-sync-alt mr-1"></i> Sinkronkan Referensi
                    </button>
                </form>
            @endif
        @endcan
    </section>

    <div class="mapping-stats mb-3">
        <article class="stat-card stat-blue">
            <span>KODE GURU</span>
            <strong>{{ $stats['guru_total'] }}</strong>
            <small>Referensi Excel</small>
        </article>
        <article class="stat-card stat-green">
            <span>GURU TERVERIFIKASI</span>
            <strong>{{ $stats['guru_verified'] }}</strong>
            <small>Kode sudah dikunci</small>
        </article>
        <article class="stat-card stat-orange">
            <span>PERLU DITINJAU</span>
            <strong>{{ $stats['guru_review'] }}</strong>
            <small>Nama mirip atau belum ditemukan</small>
        </article>
        <article class="stat-card stat-purple">
            <span>MAPEL TERVERIFIKASI</span>
            <strong>{{ $stats['mapel_verified'] }}/{{ $stats['mapel_total'] }}</strong>
            <small>Alias menuju katalog mapel</small>
        </article>
    </div>

    <section class="mapping-panel">
        <div class="panel-heading">
            <div>
                <h2>Matrix Alias Jadwal</h2>
                <p>Skor hanya rekomendasi. Status terverifikasi adalah keputusan final yang digunakan saat impor.</p>
            </div>
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input id="mapping-search" type="search" placeholder="Cari kode atau nama..." oninput="filterMappingRows()">
            </div>
        </div>

        <ul class="nav nav-pills mapping-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="pill" href="#guru-pane">
                    <i class="fas fa-chalkboard-teacher mr-1"></i> Guru
                    <span>{{ $guruAliases->count() }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="pill" href="#mapel-pane">
                    <i class="fas fa-book-open mr-1"></i> Mata Pelajaran
                    <span>{{ $mapelAliases->count() }}</span>
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="guru-pane">
                <div class="table-responsive">
                    <table class="table mapping-table mb-0">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama di Excel</th>
                                <th>GTK SIMANSA</th>
                                <th>Keyakinan</th>
                                <th>Status</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($guruAliases as $alias)
                                @php
                                    $statusClass = match($alias->status) {
                                        'verified' => 'success',
                                        'suggested' => 'warning',
                                        'rejected' => 'danger',
                                        default => 'secondary',
                                    };
                                @endphp
                                <tr class="mapping-row" data-search="{{ strtolower($alias->external_code.' '.$alias->external_name.' '.($alias->gtk?->nama_lengkap ?? '')) }}">
                                    <td><span class="code-badge">{{ $alias->external_code }}</span></td>
                                    <td>
                                        <strong>{{ $alias->external_name }}</strong>
                                        @if($alias->context)
                                            <small class="d-block text-info"><i class="fas fa-info-circle"></i> {{ str_replace('_', ' ', $alias->context) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($alias->gtk)
                                            <div class="mapped-person">
                                                <img src="{{ $alias->gtk->foto_profile_url }}" alt="">
                                                <div>
                                                    <strong>{{ $alias->gtk->nama_lengkap }}</strong>
                                                    <small>{{ $alias->gtk->nip ? 'NIP '.$alias->gtk->nip : ($alias->gtk->jenis_ptk ?? 'GTK') }}</small>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted"><i class="fas fa-user-slash mr-1"></i> Belum dipasangkan</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="confidence">
                                            <strong>{{ number_format((float) $alias->confidence, 1) }}%</strong>
                                            <div><span style="width: {{ min(100, (float) $alias->confidence) }}%"></span></div>
                                            <small>{{ str_replace('_', ' ', $alias->match_method ?? '-') }}</small>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-{{ $statusClass }}">{{ ucfirst($alias->status) }}</span></td>
                                    <td class="text-right">
                                        @can('manage-jadwal-mapping')
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                data-mapping="{{ json_encode([
                                                    "url" => route("admin.jadwal-mapping.guru.update", $alias),
                                                    "code" => $alias->external_code,
                                                    "name" => $alias->external_name,
                                                    "target" => $alias->gtk_id,
                                                    "status" => $alias->status,
                                                    "notes" => $alias->notes,
                                                ]) }}"
                                                onclick="openGuruModal(JSON.parse(this.dataset.mapping))">
                                                <i class="fas fa-edit"></i> Tinjau
                                            </button>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="empty-state">Belum ada referensi guru untuk tahun ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="mapel-pane">
                <div class="table-responsive">
                    <table class="table mapping-table mb-0">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama di Excel</th>
                                <th>Mapel SIMANSA</th>
                                <th>Struktur</th>
                                <th>Status</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mapelAliases as $alias)
                                <tr class="mapping-row" data-search="{{ strtolower($alias->external_code.' '.$alias->external_name.' '.($alias->mataPelajaran?->nama_mapel ?? '')) }}">
                                    <td><span class="code-badge mapel-code">{{ $alias->external_code }}</span></td>
                                    <td><strong>{{ $alias->external_name }}</strong></td>
                                    <td>
                                        @if($alias->mataPelajaran)
                                            <strong>{{ $alias->mataPelajaran->nama_mapel }}</strong>
                                            <small class="d-block text-muted">Jadwal {{ $alias->mataPelajaran->kode_tampil_jadwal }} · Internal {{ $alias->mataPelajaran->kode_mapel }}</small>
                                        @else
                                            <span class="text-danger">Belum dipasangkan</span>
                                        @endif
                                    </td>
                                    <td>{{ $alias->mataPelajaran?->struktur_label ?? '-' }} · {{ $alias->mataPelajaran?->fase_text ?? '-' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $alias->status === 'verified' ? 'success' : ($alias->status === 'rejected' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($alias->status) }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        @can('manage-jadwal-mapping')
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                data-mapping="{{ json_encode([
                                                    "url" => route("admin.jadwal-mapping.mapel.update", $alias),
                                                    "code" => $alias->external_code,
                                                    "name" => $alias->external_name,
                                                    "target" => $alias->mata_pelajaran_id,
                                                    "status" => $alias->status,
                                                    "notes" => $alias->notes,
                                                ]) }}"
                                                onclick="openMapelModal(JSON.parse(this.dataset.mapping))">
                                                <i class="fas fa-edit"></i> Edit Alias
                                            </button>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="empty-state">Belum ada referensi mapel untuk tahun ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

@can('manage-jadwal-mapping')
<div class="modal fade" id="guruMappingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" id="guruMappingForm" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <div>
                    <small>MAPPING GURU · KODE <span id="guruModalCode"></span></small>
                    <h5 class="modal-title">Verifikasi identitas guru</h5>
                </div>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-shield-alt mr-1"></i>
                    Periksa foto, NIP, jabatan, dan nama lengkap. Nama yang mirip belum tentu orang yang sama.
                </div>
                <div class="form-group">
                    <label>Nama alias dari Excel</label>
                    <input type="text" class="form-control" name="external_name" id="guruExternalName" required>
                </div>
                <div class="form-group">
                    <label>GTK SIMANSA</label>
                    <select class="form-control select2" name="gtk_id" id="guruTarget" style="width:100%">
                        <option value="">Belum dipasangkan</option>
                        @foreach($gtkOptions as $gtk)
                            <option value="{{ $gtk['id'] }}">
                                {{ $gtk['nama'] }}{{ $gtk['nip'] ? ' · NIP '.$gtk['nip'] : '' }}{{ $gtk['kode'] ? ' · Kode '.$gtk['kode'] : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-5">
                        <label>Status</label>
                        <select class="form-control" name="status" id="guruStatus">
                            <option value="pending">Pending</option>
                            <option value="verified">Terverifikasi</option>
                            <option value="rejected">Ditolak</option>
                        </select>
                    </div>
                    <div class="form-group col-md-7">
                        <label>Catatan</label>
                        <input type="text" class="form-control" name="notes" id="guruNotes" placeholder="Opsional">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-lock mr-1"></i> Simpan Mapping</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="mapelMappingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" id="mapelMappingForm" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <div>
                    <small>ALIAS MAPEL · KODE <span id="mapelModalCode"></span></small>
                    <h5 class="modal-title">Hubungkan dengan katalog mapel</h5>
                </div>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama alias dari Excel</label>
                    <input type="text" class="form-control" name="external_name" id="mapelExternalName" required>
                </div>
                <div class="form-group">
                    <label>Mata Pelajaran SIMANSA</label>
                    <select class="form-control select2" name="mata_pelajaran_id" id="mapelTarget" style="width:100%">
                        <option value="">Belum dipasangkan</option>
                        @foreach($mapelOptions as $mapel)
                            <option value="{{ $mapel['id'] }}">{{ $mapel['nama'] }} · {{ $mapel['kode'] }} · {{ $mapel['fase'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-5">
                        <label>Status</label>
                        <select class="form-control" name="status" id="mapelStatus">
                            <option value="pending">Pending</option>
                            <option value="verified">Terverifikasi</option>
                            <option value="rejected">Ditolak</option>
                        </select>
                    </div>
                    <div class="form-group col-md-7">
                        <label>Catatan</label>
                        <input type="text" class="form-control" name="notes" id="mapelNotes" placeholder="Opsional">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan Alias</button>
            </div>
        </form>
    </div>
</div>
@endcan
@stop

@section('css')
<style>
.jadwal-mapping-page { color:#172033; }
.mapping-hero { min-height:150px; padding:26px 28px; border-radius:22px; color:#fff; display:flex; justify-content:space-between; align-items:center; gap:24px; background:linear-gradient(120deg,#4267ef 0%,#337dcd 48%,#238978 100%); box-shadow:0 18px 40px rgba(44,74,145,.18); }
.mapping-hero h1 { margin:8px 0 4px; font-size:28px; font-weight:800; }
.mapping-hero p { margin:0; opacity:.92; font-size:16px; }
.hero-eyebrow { font-size:13px; font-weight:800; letter-spacing:.04em; }
.hero-actions { display:flex; gap:10px; flex-wrap:wrap; }
.hero-actions .btn { border:0; border-radius:10px; font-weight:700; padding:11px 16px; }
.mapping-toolbar { padding:16px 18px; border:1px solid #dbe3f1; border-radius:16px; background:#fff; display:flex; justify-content:space-between; align-items:flex-end; box-shadow:0 8px 24px rgba(30,55,100,.06); }
.year-form { width:min(360px,100%); }
.mapping-toolbar label { display:block; margin-bottom:5px; color:#64738d; font-size:12px; font-weight:800; text-transform:uppercase; }
.mapping-toolbar .form-control,.mapping-toolbar .btn { height:42px; border-radius:9px; }
.mapping-stats { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; }
.stat-card { position:relative; overflow:hidden; min-height:125px; padding:20px; border:1px solid #dbe3f1; border-top:4px solid var(--accent); border-radius:17px; background:#fff; box-shadow:0 10px 28px rgba(31,54,96,.07); }
.stat-card span { display:block; color:#60708a; font-size:12px; font-weight:800; }
.stat-card strong { display:block; margin:5px 0 2px; color:var(--accent); font-size:29px; line-height:1; }
.stat-card small { color:#8793a8; }
.stat-blue { --accent:#4773f2; }.stat-green { --accent:#25ad69; }.stat-orange { --accent:#e49100; }.stat-purple { --accent:#8154ed; }
.mapping-panel { overflow:hidden; border:1px solid #d9e2f0; border-radius:18px; background:#fff; box-shadow:0 14px 35px rgba(31,54,96,.08); }
.panel-heading { padding:20px 22px 14px; display:flex; justify-content:space-between; align-items:center; gap:20px; }
.panel-heading h2 { margin:0 0 3px; font-size:21px; font-weight:800; }
.panel-heading p { margin:0; color:#687892; }
.search-box { position:relative; width:300px; max-width:100%; }
.search-box i { position:absolute; left:13px; top:12px; color:#8996aa; }
.search-box input { width:100%; height:41px; padding:0 12px 0 37px; border:1px solid #d7e0ee; border-radius:10px; outline:none; }
.mapping-tabs { padding:0 22px 14px; border-bottom:1px solid #e4eaf3; gap:8px; }
.mapping-tabs .nav-link { color:#53627b; font-weight:700; border-radius:10px; }
.mapping-tabs .nav-link.active { background:#4668ed; }
.mapping-tabs span { margin-left:7px; padding:2px 7px; border-radius:10px; background:rgba(255,255,255,.2); font-size:11px; }
.mapping-table { min-width:930px; }
.mapping-table thead th { padding:13px 16px; border:0; border-bottom:1px solid #dfe6f0; background:#f6f8fc; color:#60708a; font-size:12px; text-transform:uppercase; }
.mapping-table tbody td { padding:13px 16px; vertical-align:middle; border-color:#edf1f6; }
.code-badge { display:inline-flex; min-width:39px; height:34px; padding:0 8px; align-items:center; justify-content:center; border-radius:9px; background:#eaf0ff; color:#3159dd; font-weight:800; }
.mapel-code { background:#f0eaff; color:#7541de; }
.mapped-person { display:flex; align-items:center; gap:10px; min-width:260px; }
.mapped-person img { width:42px; height:42px; border-radius:50%; object-fit:cover; border:2px solid #e8edf5; }
.mapped-person strong,.mapped-person small { display:block; }
.mapped-person small { color:#7e8ba0; }
.confidence { min-width:145px; }
.confidence > div { width:100%; height:5px; margin:4px 0; overflow:hidden; border-radius:5px; background:#e8edf4; }
.confidence > div span { display:block; height:100%; border-radius:5px; background:linear-gradient(90deg,#ef9d24,#2ab773); }
.confidence small { color:#8793a6; }
.empty-state { padding:45px !important; text-align:center; color:#8490a4; }
.modal-content { border:0; border-radius:18px; overflow:hidden; box-shadow:0 24px 80px rgba(20,35,70,.22); }
.modal-header { padding:19px 22px; border-bottom:1px solid #e5eaf2; }
.modal-header small { color:#516de4; font-weight:800; }
.modal-title { margin-top:3px; font-weight:800; }
@media(max-width:991px){.mapping-stats{grid-template-columns:repeat(2,1fr)}.mapping-hero{align-items:flex-start;flex-direction:column}.panel-heading{align-items:flex-start;flex-direction:column}.search-box{width:100%}}
@media(max-width:575px){.mapping-stats{grid-template-columns:1fr}.mapping-toolbar{align-items:stretch;flex-direction:column;gap:12px}.mapping-toolbar form,.mapping-toolbar .btn{width:100%}.mapping-hero{padding:22px}.mapping-hero h1{font-size:23px}}
</style>
@stop

@section('js')
<script>
$(function () {
    $('.select2').each(function () {
        $(this).select2({ dropdownParent: $(this).closest('.modal'), width: '100%' });
    });
    const success = document.getElementById('flash-success')?.dataset.message;
    const error = document.getElementById('flash-error')?.dataset.message;
    if (success) Swal.fire({ toast:true, position:'top-end', icon:'success', title:success, showConfirmButton:false, timer:3500 });
    if (error) Swal.fire({ icon:'error', title:'Mapping belum disimpan', text:error });
});

function filterMappingRows() {
    const query = document.getElementById('mapping-search').value.toLowerCase().trim();
    document.querySelectorAll('.mapping-row').forEach(row => {
        row.style.display = !query || row.dataset.search.includes(query) ? '' : 'none';
    });
}

function openGuruModal(data) {
    const form = document.getElementById('guruMappingForm');
    form.action = data.url;
    document.getElementById('guruModalCode').textContent = data.code;
    document.getElementById('guruExternalName').value = data.name || '';
    $('#guruTarget').val(data.target || '').trigger('change');
    document.getElementById('guruStatus').value = ['verified','rejected'].includes(data.status) ? data.status : 'pending';
    document.getElementById('guruNotes').value = data.notes || '';
    $('#guruMappingModal').modal('show');
}

function openMapelModal(data) {
    const form = document.getElementById('mapelMappingForm');
    form.action = data.url;
    document.getElementById('mapelModalCode').textContent = data.code;
    document.getElementById('mapelExternalName').value = data.name || '';
    $('#mapelTarget').val(data.target || '').trigger('change');
    document.getElementById('mapelStatus').value = ['verified','rejected'].includes(data.status) ? data.status : 'pending';
    document.getElementById('mapelNotes').value = data.notes || '';
    $('#mapelMappingModal').modal('show');
}

function confirmRefreshMapping() {
    Swal.fire({
        icon:'question',
        title:'Sinkronkan referensi?',
        text:'Mapping yang sudah diverifikasi tidak akan ditimpa.',
        showCancelButton:true,
        confirmButtonText:'Ya, sinkronkan',
        cancelButtonText:'Batal',
        confirmButtonColor:'#4668ed'
    }).then(result => {
        if (result.isConfirmed) document.getElementById('refresh-mapping-form').submit();
    });
}
</script>
@stop

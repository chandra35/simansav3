@extends('adminlte::page')

@section('title', 'Proses Akhir Tahun')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-graduation-cap"></i> Proses Akhir Tahun Ajaran</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('admin.tahun-pelajaran.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Tahun Pelajaran
                </a>
            </div>
        </div>
    </div>
@endsection

@section('content')
<div class="row">
    {{-- INFO TAHUN AKTIF --}}
    <div class="col-12 mb-3">
        @if($tahunAktif)
        <div class="alert alert-info alert-dismissible mb-0">
            <i class="fas fa-info-circle mr-1"></i>
            Tahun pelajaran aktif: <strong>{{ $tahunAktif->nama }}</strong>
            (Semester <strong>{{ $tahunAktif->semester_aktif }}</strong>) &mdash;
            Wizard ini membantu memproses akhir tahun: kelulusan kelas XII dan kenaikan kelas X→XI dan XI→XII.
        </div>
        @else
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            Belum ada tahun pelajaran aktif. Aktifkan tahun pelajaran terlebih dahulu.
        </div>
        @endif
    </div>
</div>

{{-- RINGKASAN STATISTIK --}}
<div class="row" id="stats-row">
    <div class="col-lg-3 col-6 mb-3">
        <div class="small-box bg-info">
            <div class="inner"><h3 id="stat-10">-</h3><p>Siswa Kelas X</p></div>
            <div class="icon"><i class="fas fa-user-graduate"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6 mb-3">
        <div class="small-box bg-warning">
            <div class="inner"><h3 id="stat-11">-</h3><p>Siswa Kelas XI</p></div>
            <div class="icon"><i class="fas fa-user-graduate"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6 mb-3">
        <div class="small-box bg-danger">
            <div class="inner"><h3 id="stat-12">-</h3><p>Siswa Kelas XII</p></div>
            <div class="icon"><i class="fas fa-user-graduate"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6 mb-3">
        <div class="small-box bg-success">
            <div class="inner"><h3 id="stat-lulus">-</h3><p>Sudah Finalisasi Lulus</p></div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
</div>

{{-- STEP 1: KELULUSAN KELAS XII --}}
<div class="card card-danger card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-graduation-cap mr-2"></i>Langkah 1 — Kelulusan Kelas XII</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="callout callout-info mb-3">
            <h6><i class="fas fa-info-circle mr-1"></i> Cara kerja</h6>
            <p class="mb-1">Langkah ini membaca status yang sudah di-set di halaman <strong>Pengumuman Kelulusan</strong>, lalu memfinalisasi record <code>siswa_kelas</code>:</p>
            <ul class="mb-0">
                <li><strong>Lulus / Lulus Bersyarat</strong> &rarr; <code>siswa_kelas.status = lulus</code></li>
                <li><strong>Tidak Lulus</strong> &rarr; <code>siswa_kelas.status = tinggal_kelas</code></li>
                <li>Siswa yang belum ada pengumuman kelulusan-nya <strong>dilewati</strong> — set dulu via halaman Pengumuman Kelulusan.</li>
            </ul>
        </div>

        {{-- Status summary --}}
        <div id="kelulusan-status" class="mb-3">
            <i class="fas fa-spinner fa-spin"></i> Memuat status...
        </div>

        <a href="{{ route('admin.kelulusan-pengumuman.index') }}" class="btn btn-outline-danger mb-3" target="_blank">
            <i class="fas fa-external-link-alt mr-1"></i> Buka Halaman Pengumuman Kelulusan
        </a>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="tandai-siswa-lulus" checked>
            <label class="form-check-label" for="tandai-siswa-lulus">
                Update <code>status_siswa</code> siswa menjadi <strong>lulus</strong>
            </label>
        </div>

        <button id="btn-proses-kelulusan" class="btn btn-danger" disabled>
            <i class="fas fa-graduation-cap mr-1"></i> Finalisasi Kelulusan Kelas XII
        </button>
        <div id="result-kelulusan" class="mt-3"></div>
    </div>
</div>

{{-- STEP 2: NAIK KELAS --}}
<div class="card card-warning card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-arrow-up mr-2"></i>Langkah 2 — Naik Kelas (X→XI dan XI→XII)</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <p class="text-muted">
            Proses ini memindahkan siswa dari kelas lama ke kelas baru di tahun pelajaran berbeda.
            Record <code>siswa_kelas</code> lama akan ditandai <code>naik_kelas</code> dan record baru dibuat di tahun tujuan.
            Siswa yang sudah terdaftar aktif di tahun tujuan akan dilewati.
        </p>

        <div class="row mb-3">
            <div class="col-md-4">
                <div class="form-group">
                    <label><strong>Tahun Pelajaran Asal</strong></label>
                    <select id="tahun-asal" class="form-control">
                        <option value="">-- Pilih Tahun Asal --</option>
                        @foreach($semuaTahun as $tp)
                            <option value="{{ $tp->id }}" {{ $tahunAktif && $tp->id === $tahunAktif->id ? 'selected' : '' }}>
                                {{ $tp->nama }} {{ $tp->is_active ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label><strong>Tahun Pelajaran Tujuan</strong></label>
                    <select id="tahun-tujuan" class="form-control">
                        <option value="">-- Pilih Tahun Tujuan --</option>
                        @foreach($semuaTahun as $tp)
                            <option value="{{ $tp->id }}">{{ $tp->nama }} {{ $tp->is_active ? '(Aktif)' : '' }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label><strong>Tanggal Masuk Kelas Baru</strong></label>
                    <input type="date" id="tanggal-masuk" class="form-control" value="{{ now()->format('Y-m-d') }}">
                </div>
            </div>
        </div>

        <button id="btn-load-mapping" class="btn btn-info mb-3" disabled>
            <i class="fas fa-sync-alt mr-1"></i> Muat Kelas untuk Mapping
        </button>

        {{-- Tabel Mapping --}}
        <div id="mapping-container" class="d-none">
            <h6 class="font-weight-bold mb-2">Mapping Kelas (Asal → Tujuan)</h6>
            <p class="text-muted small">Pilih kelas tujuan untuk setiap kelas sumber. Baris tanpa kelas tujuan akan dilewati.</p>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>Kelas Asal (Tahun Asal)</th>
                            <th>Tingkat</th>
                            <th>Jml Siswa Aktif</th>
                            <th>→ Kelas Tujuan (Tahun Tujuan)</th>
                        </tr>
                    </thead>
                    <tbody id="mapping-tbody"></tbody>
                </table>
            </div>
            <button id="btn-proses-naik-kelas" class="btn btn-warning mt-2">
                <i class="fas fa-arrow-up mr-1"></i> Proses Naik Kelas
            </button>
            <div id="result-naik-kelas" class="mt-3"></div>
        </div>
    </div>
</div>

{{-- STEP 3: CATATAN --}}
<div class="card card-secondary card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Langkah 3 — Setelah Proses</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <p class="mb-2">Setelah proses naik kelas selesai, lakukan langkah-langkah berikut secara manual:</p>
        <ol>
            <li>
                <strong>Arsipkan tahun pelajaran lama</strong> — Buka halaman
                <a href="{{ route('admin.tahun-pelajaran.index') }}">Tahun Pelajaran</a>,
                set status tahun lama menjadi <code>selesai</code>, dan aktifkan tahun baru.
            </li>
            <li>
                <strong>Jadwal pelajaran</strong> — Salin jadwal dari tahun sebelumnya via tombol
                <strong>Copy Jadwal</strong> di halaman
                <a href="{{ route('admin.jadwal-pelajaran.index') }}">Jadwal Pelajaran</a>,
                atau buat jadwal baru secara manual.
            </li>
            <li>
                <strong>Wali kelas</strong> — Assign ulang wali kelas di halaman
                <a href="{{ route('admin.kelas.index') }}">Manajemen Kelas</a>.
            </li>
            <li>
                <strong>Siswa baru (PPDB)</strong> — Import siswa kelas X baru via
                <a href="{{ route('admin.siswa.import') }}">Import Siswa</a>.
            </li>
            <li>
                <strong>Verifikasi</strong> — Cek daftar siswa per kelas di halaman
                <a href="{{ route('admin.kelas.index') }}">Kelas</a>
                untuk memastikan semua siswa sudah terpindahkan dengan benar.
            </li>
        </ol>
    </div>
</div>
@endsection

@section('css')
<style>
.kelas-checkbox-label {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    padding: .25rem .6rem;
    border: 1px solid #dee2e6;
    border-radius: .25rem;
    cursor: pointer;
    user-select: none;
    font-size: .85rem;
    background: #fff;
    transition: all .15s;
}
.kelas-checkbox-label:hover { background: #f8f9fa; }
.kelas-checkbox-label input:checked ~ span { font-weight: 600; }
.kelas-checkbox-label:has(input:checked) {
    background: #fff3cd;
    border-color: #ffc107;
}
</style>
@endsection

@section('js')
<script>
(function () {
    'use strict';

    const tahunAktifId = @json(optional($tahunAktif)->id);
    const csrfToken   = document.querySelector('meta[name="csrf-token"]').content;

    // --- UTIL ---
    function alertBox(html, type) {
        return `<div class="alert alert-${type} alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>${html}
        </div>`;
    }

    function esc(s) {
        const d = document.createElement('div');
        d.textContent = String(s ?? '');
        return d.innerHTML;
    }

    async function parseJsonResponse(response) {
        const contentType = response.headers.get('content-type') || '';
        const payload = contentType.includes('application/json') ? await response.json() : null;
        if (!response.ok) {
            throw new Error(payload?.message || payload?.error || `HTTP ${response.status}`);
        }
        return payload || {};
    }

    // --- STATS ---
    function loadStats(tahunId) {
        if (!tahunId) return;
        fetch(`{{ route('admin.kenaikan-kelas.data') }}?tahun_pelajaran_id=${tahunId}`)
            .then(r => r.json())
            .then(d => {
                document.getElementById('stat-10').textContent    = d.siswa_10 ?? '-';
                document.getElementById('stat-11').textContent    = d.siswa_11 ?? '-';
                document.getElementById('stat-12').textContent    = d.siswa_12 ?? '-';
                document.getElementById('stat-lulus').textContent = d.siswa_12_lulus ?? '-';
            })
            .catch(() => {});
    }

    // --- STATUS KELULUSAN (Step 1) ---
    function loadStatusKelulusan(tahunId) {
        if (!tahunId) return;
        fetch(`{{ route('admin.kenaikan-kelas.status-kelulusan') }}?tahun_pelajaran_id=${tahunId}`, {
            headers: { 'Accept': 'application/json' }
        })
            .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
            .then(d => {
                const belum = d.belum_ada_pengumuman;
                let html = `<div class="row">`;
                html += `<div class="col-sm-3"><div class="info-box"><span class="info-box-icon bg-info"><i class="fas fa-users"></i></span><div class="info-box-content"><span class="info-box-text">Total Kelas XII</span><span class="info-box-number">${d.total}</span></div></div></div>`;
                html += `<div class="col-sm-3"><div class="info-box"><span class="info-box-icon bg-success"><i class="fas fa-check"></i></span><div class="info-box-content"><span class="info-box-text">Sudah Lulus/Lulus Bersyarat</span><span class="info-box-number">${d.sudah_lulus}</span></div></div></div>`;
                html += `<div class="col-sm-3"><div class="info-box"><span class="info-box-icon bg-warning"><i class="fas fa-times"></i></span><div class="info-box-content"><span class="info-box-text">Tidak Lulus</span><span class="info-box-number">${d.sudah_tidak_lulus}</span></div></div></div>`;
                html += `<div class="col-sm-3"><div class="info-box ${belum > 0 ? 'bg-danger' : ''}"><span class="info-box-icon ${belum > 0 ? 'bg-danger' : 'bg-secondary'}"><i class="fas fa-question"></i></span><div class="info-box-content"><span class="info-box-text">Belum Ada Pengumuman</span><span class="info-box-number ${belum > 0 ? 'text-white' : ''}">${belum}</span></div></div></div>`;
                html += `</div>`;
                if (belum > 0) {
                    html += `<div class="alert alert-warning"><i class="fas fa-exclamation-triangle mr-1"></i> <strong>${belum} siswa</strong> belum memiliki record pengumuman kelulusan. Set status mereka terlebih dahulu di halaman Pengumuman Kelulusan sebelum finalisasi.</div>`;
                }
                if (d.sudah_finalisasi > 0) {
                    html += `<div class="alert alert-success"><i class="fas fa-check-circle mr-1"></i> ${d.sudah_finalisasi} siswa sudah difinalisasi (siswa_kelas.status bukan aktif).</div>`;
                }
                document.getElementById('kelulusan-status').innerHTML = html;
                document.getElementById('btn-proses-kelulusan').disabled = (d.sudah_lulus + d.sudah_tidak_lulus) === 0;
            })
            .catch(err => {
                console.error('statusKelulusan error:', err);
                document.getElementById('kelulusan-status').innerHTML = '<span class="text-danger">Gagal memuat status kelulusan: ' + (err.message || 'Cek Console browser.') + '</span>';
            });
    }

    if (tahunAktifId) { loadStats(tahunAktifId); loadStatusKelulusan(tahunAktifId); }

    // --- STEP 1: FINALISASI KELULUSAN ---
    document.getElementById('btn-proses-kelulusan').addEventListener('click', function () {
        if (!confirm('Finalisasi kelulusan kelas XII?\nSiswa dengan status Lulus/Lulus Bersyarat akan ditandai lulus, Tidak Lulus akan ditandai tinggal kelas.\nTindakan ini tidak dapat dibatalkan secara otomatis.')) return;

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...';

        fetch('{{ route('admin.kenaikan-kelas.proses-kelulusan') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({
                tahun_pelajaran_id: tahunAktifId,
                tandai_siswa_lulus: document.getElementById('tandai-siswa-lulus').checked,
            })
        })
        .then(parseJsonResponse)
        .then(d => {
            const type = d.success ? 'success' : 'warning';
            document.getElementById('result-kelulusan').innerHTML = alertBox(
                `<i class="fas fa-check-circle mr-1"></i> ${esc(d.message)}`, type
            );
            loadStats(tahunAktifId);
            loadStatusKelulusan(tahunAktifId);
        })
        .catch(err => {
            document.getElementById('result-kelulusan').innerHTML = alertBox(esc(err.message || 'Terjadi kesalahan. Coba lagi.'), 'danger');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-graduation-cap mr-1"></i> Finalisasi Kelulusan Kelas XII';
        });
    });

    // --- STEP 2: NAIK KELAS ---
    const elTahunAsal   = document.getElementById('tahun-asal');
    const elTahunTujuan = document.getElementById('tahun-tujuan');
    const btnLoadMapping = document.getElementById('btn-load-mapping');

    function updateLoadBtn() {
        btnLoadMapping.disabled = !(elTahunAsal.value && elTahunTujuan.value && elTahunAsal.value !== elTahunTujuan.value);
    }
    elTahunAsal.addEventListener('change', updateLoadBtn);
    elTahunTujuan.addEventListener('change', updateLoadBtn);

    async function fetchKelas(tahunId, tingkat) {
        const url = `{{ route('admin.kenaikan-kelas.kelas-by-tahun') }}?tahun_pelajaran_id=${tahunId}&tingkat=${tingkat}`;
        const r = await fetch(url);
        return r.json();
    }

    async function getSiswaCount(kelasId, tahunId) {
        const url = `{{ route('admin.kenaikan-kelas.preview') }}?kelas_id=${kelasId}&tahun_pelajaran_id=${tahunId}`;
        const r = await fetch(url);
        const d = await r.json();
        return Array.isArray(d) ? d.length : 0;
    }

    btnLoadMapping.addEventListener('click', async function () {
        const asalId   = elTahunAsal.value;
        const tujuanId = elTahunTujuan.value;
        if (!asalId || !tujuanId) return;

        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Memuat...';

        try {
            // Ambil kelas tingkat 10 dan 11 dari tahun asal
            const [kelas10, kelas11] = await Promise.all([
                fetchKelas(asalId, 10),
                fetchKelas(asalId, 11),
            ]);

            // Ambil kelas tingkat 11 dan 12 dari tahun tujuan (untuk dropdown)
            const [kelas11tujuan, kelas12tujuan] = await Promise.all([
                fetchKelas(tujuanId, 11),
                fetchKelas(tujuanId, 12),
            ]);

            const kelasAsal = [...kelas10, ...kelas11];

            // Hitung siswa aktif per kelas asal
            const counts = await Promise.all(kelasAsal.map(k => getSiswaCount(k.id, asalId)));

            const tbody = document.getElementById('mapping-tbody');
            tbody.innerHTML = kelasAsal.map((k, i) => {
                const opsiTujuan = k.tingkat === 10 ? kelas11tujuan : kelas12tujuan;
                const opts = opsiTujuan.map(t =>
                    `<option value="${esc(t.id)}">${esc(t.nama_kelas)}</option>`
                ).join('');
                return `<tr>
                    <td>${esc(k.nama_kelas)}</td>
                    <td><span class="badge badge-secondary">${k.tingkat}</span></td>
                    <td>${counts[i]}</td>
                    <td>
                        <select class="form-control form-control-sm kelas-tujuan-select"
                                data-asal="${esc(k.id)}" style="min-width:180px;">
                            <option value="">-- Lewati --</option>
                            ${opts}
                        </select>
                    </td>
                </tr>`;
            }).join('') || '<tr><td colspan="4" class="text-center text-muted">Tidak ada kelas X/XI di tahun asal.</td></tr>';

            document.getElementById('mapping-container').classList.remove('d-none');
        } catch (e) {
            alert('Gagal memuat data kelas. Coba lagi.');
        } finally {
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-sync-alt mr-1"></i> Muat Kelas untuk Mapping';
        }
    });

    document.getElementById('btn-proses-naik-kelas').addEventListener('click', function () {
        const selects = document.querySelectorAll('.kelas-tujuan-select');
        const mapping = [];
        selects.forEach(s => {
            if (s.value) {
                mapping.push({ kelas_asal_id: s.dataset.asal, kelas_tujuan_id: s.value });
            }
        });

        if (mapping.length === 0) {
            alert('Pilih setidaknya satu pasangan kelas asal → kelas tujuan.');
            return;
        }

        if (!confirm(`Proses naik kelas untuk ${mapping.length} pasangan kelas?\nTindakan ini tidak dapat dibatalkan secara otomatis.`)) return;

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...';

        fetch('{{ route('admin.kenaikan-kelas.proses-naik-kelas') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({
                tahun_asal_id:   elTahunAsal.value,
                tahun_tujuan_id: elTahunTujuan.value,
                mapping:         mapping,
                tanggal_masuk:   document.getElementById('tanggal-masuk').value,
            })
        })
        .then(parseJsonResponse)
        .then(d => {
            let html = `<i class="fas fa-check mr-1"></i> ${esc(d.message)}`;
            if (d.errors && d.errors.length > 0) {
                html += '<ul class="mb-0 mt-1">' + d.errors.map(e => `<li>${esc(e)}</li>`).join('') + '</ul>';
            }
            const type = d.errors && d.errors.length ? 'warning' : 'success';
            document.getElementById('result-naik-kelas').innerHTML = alertBox(html, type);
            loadStats(elTahunAsal.value);
        })
        .catch(err => {
            document.getElementById('result-naik-kelas').innerHTML = alertBox(esc(err.message || 'Terjadi kesalahan. Coba lagi.'), 'danger');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-arrow-up mr-1"></i> Proses Naik Kelas';
        });
    });
})();
</script>
@endsection

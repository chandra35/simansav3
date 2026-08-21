@extends('adminlte::page')

@section('title', 'Cek NIP EMIS GTK')

@section('content_header')
<div class="d-flex flex-wrap justify-content-between align-items-center">
    <div>
        <h1 class="m-0 text-dark">Cek NIP EMIS GTK</h1>
        <small class="text-muted">Verifikasi data PTK terhadap SIMPEG Kementerian Agama</small>
    </div>
    <a href="{{ route('admin.pengaturan.cek-nip.index') }}" class="btn btn-outline-secondary mt-2 mt-sm-0">
        <i class="fas fa-exchange-alt mr-1"></i> Buka Cek NIP Pintar
    </a>
</div>
@stop

@section('content')
<div class="emis-nip-page">
    <div class="card hero-card border-0 mb-4">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <span class="source-label"><i class="fas fa-database mr-1"></i> SUMBER EMIS GTK / SIMPEG</span>
                    <h2 class="mt-2 mb-2">Validasi identitas ASN secara ringkas dan aman</h2>
                    <p class="mb-0 text-white-50">Bandingkan nama, tanggal lahir, status pegawai, golongan, jabatan, pendidikan, dan data penggajian.</p>
                </div>
                <div class="col-lg-5 mt-3 mt-lg-0">
                    <div class="credential-state {{ $credentialConfigured ? 'is-ready' : 'is-missing' }}">
                        <i class="fas {{ $credentialConfigured ? 'fa-check-circle' : 'fa-exclamation-triangle' }}"></i>
                        <div>
                            <strong>{{ $credentialConfigured ? 'Sesi siap digunakan' : 'Sesi belum dikonfigurasi' }}</strong>
                            <small>{{ $credentialConfigured ? 'Cookie tersimpan terenkripsi' : 'Tambahkan cookie melalui Update API Token' }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-primary search-card">
        <div class="card-body">
            <form id="nip-form" autocomplete="off">
                @csrf
                <label for="nip">Nomor Induk Pegawai</label>
                <div class="input-group input-group-lg">
                    <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-id-card"></i></span></div>
                    <input id="nip" name="nip" class="form-control" inputmode="numeric" pattern="[0-9]{18}" maxlength="18" placeholder="Masukkan 18 digit NIP" aria-describedby="nip-help" required>
                    <div class="input-group-append">
                        <button class="btn btn-primary px-4" id="check-button" type="submit" {{ $credentialConfigured ? '' : 'disabled' }}>
                            <i class="fas fa-search mr-1"></i> Periksa
                        </button>
                    </div>
                </div>
                <small id="nip-help" class="form-text text-muted"><span id="digit-count">0</span>/18 digit. Data hanya ditampilkan dan tidak otomatis mengubah data GTK SIMANSA.</small>
            </form>
        </div>
    </div>

    <div id="result-alert" class="alert d-none" role="alert"></div>

    <section id="result" class="d-none" aria-live="polite">
        <div class="row">
            <div class="col-lg-4">
                <div class="card h-100 result-card">
                    <div class="card-body text-center">
                        <div id="valid-icon" class="validation-icon"><i class="fas fa-check"></i></div>
                        <p class="result-kicker mb-1">STATUS VALIDASI</p>
                        <h3 id="valid-title">Data valid</h3>
                        <div id="valid-flags" class="flag-list mt-3"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8 mt-3 mt-lg-0">
                <div class="card h-100 result-card">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-user-check mr-2 text-primary"></i>Perbandingan Identitas</h3></div>
                    <div class="card-body">
                        <div class="identity-grid">
                            <div><span>Nama PTK</span><strong id="nama-ptk">-</strong></div>
                            <div><span>Nama SIMPEG</span><strong id="nama-simpeg">-</strong></div>
                            <div><span>Tanggal lahir PTK</span><strong id="lahir-ptk">-</strong></div>
                            <div><span>Tanggal lahir SIMPEG</span><strong id="lahir-simpeg">-</strong></div>
                        </div>
                        <div class="mt-3">
                            <div class="d-flex justify-content-between"><span>Kemiripan nama</span><strong id="similarity-label">0%</strong></div>
                            <div class="progress progress-sm mt-2"><div id="similarity-bar" class="progress-bar bg-info" role="progressbar"></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card result-card mt-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-briefcase mr-2 text-primary"></i>Data Kepegawaian SIMPEG</h3></div>
            <div class="card-body"><div id="simpeg-grid" class="detail-grid"></div></div>
        </div>
    </section>
</div>
@stop

@section('css')
<style>
.emis-nip-page{max-width:1200px;margin:0 auto 2rem}.hero-card{background:linear-gradient(125deg,#183b68,#167d8d);color:#fff;border-radius:16px;overflow:hidden;box-shadow:0 12px 30px rgba(24,59,104,.16)}
.source-label{font-size:.75rem;font-weight:700;letter-spacing:.08em;background:rgba(255,255,255,.14);padding:.4rem .65rem;border-radius:20px}.credential-state{display:flex;align-items:center;gap:.75rem;padding:1rem;border-radius:12px;background:rgba(255,255,255,.12)}.credential-state i{font-size:1.6rem}.credential-state strong,.credential-state small{display:block}.credential-state.is-ready i{color:#69e6a6}.credential-state.is-missing i{color:#ffd166}
.search-card,.result-card{border-radius:12px;box-shadow:0 5px 18px rgba(31,45,61,.07)}.validation-icon{width:64px;height:64px;margin:0 auto 1rem;border-radius:50%;display:grid;place-items:center;background:#dff6e9;color:#198754;font-size:1.5rem}.validation-icon.invalid{background:#fde7e9;color:#dc3545}.result-kicker{font-size:.75rem;font-weight:700;letter-spacing:.09em;color:#748198}.flag-list{display:flex;flex-wrap:wrap;justify-content:center;gap:.4rem}.flag-pill{border-radius:20px;padding:.3rem .6rem;font-size:.77rem;background:#eef2f7}.flag-pill.ok{color:#13744a;background:#e0f4ea}.flag-pill.bad{color:#a72a36;background:#fbe7e9}
.identity-grid,.detail-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}.identity-grid>div,.detail-item{padding:.85rem;border:1px solid #e7ebf0;border-radius:10px;background:#fbfcfe}.identity-grid span,.detail-item span{display:block;color:#738095;font-size:.75rem;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.3rem}.identity-grid strong,.detail-item strong{overflow-wrap:anywhere}.detail-item.wide{grid-column:1/-1}
@media(max-width:575.98px){.hero-card h2{font-size:1.45rem}.input-group-lg>.form-control{font-size:1rem}.input-group-append .btn{padding-left:1rem!important;padding-right:1rem!important}.identity-grid,.detail-grid{grid-template-columns:1fr}.detail-item.wide{grid-column:auto}}
</style>
@stop

@section('js')
<script>
(() => {
    const form = document.getElementById('nip-form');
    const input = document.getElementById('nip');
    const button = document.getElementById('check-button');
    const result = document.getElementById('result');
    const alertBox = document.getElementById('result-alert');
    const safe = value => value === null || value === undefined || value === '' ? '-' : String(value);
    const setText = (id, value) => document.getElementById(id).textContent = safe(value);
    const boolLabel = (label, value) => `<span class="flag-pill ${value ? 'ok' : 'bad'}"><i class="fas ${value ? 'fa-check' : 'fa-times'} mr-1"></i>${label}</span>`;
    const money = value => new Intl.NumberFormat('id-ID', {style:'currency',currency:'IDR',maximumFractionDigits:0}).format(Number(value || 0));

    input.addEventListener('input', () => {
        input.value = input.value.replace(/\D/g, '').slice(0, 18);
        document.getElementById('digit-count').textContent = input.value.length;
    });

    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (!/^\d{18}$/.test(input.value)) {
            showError('NIP harus terdiri dari tepat 18 digit.'); return;
        }
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Memeriksa';
        alertBox.classList.add('d-none'); result.classList.add('d-none');
        try {
            const response = await fetch(@json(route('admin.pengaturan.cek-nip-emisgtk.check')), {
                method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':@json(csrf_token())},
                body:JSON.stringify({nip:input.value})
            });
            const payload = await response.json();
            if (!response.ok || !payload.success) throw new Error(payload.message || 'Pemeriksaan gagal.');
            render(payload.data); result.classList.remove('d-none');
        } catch (error) { showError(error.message || 'Tidak dapat menghubungi server.'); }
        finally { button.disabled = false; button.innerHTML = '<i class="fas fa-search mr-1"></i> Periksa'; }
    });

    function showError(message) { alertBox.className='alert alert-danger'; alertBox.textContent=message; alertBox.classList.remove('d-none'); }
    function render(data) {
        const v=data.validation, s=data.simpeg, valid=!!v.is_valid;
        document.getElementById('valid-icon').classList.toggle('invalid',!valid);
        document.getElementById('valid-icon').innerHTML=`<i class="fas ${valid?'fa-check':'fa-times'}"></i>`;
        setText('valid-title',valid?'Data valid':'Perlu pemeriksaan'); setText('nama-ptk',v.nama_ptk); setText('nama-simpeg',v.nama_simpeg);
        setText('lahir-ptk',v.tgl_lahir_ptk); setText('lahir-simpeg',v.tgl_lahir_simpeg_display||v.tgl_lahir_simpeg);
        const similarity=Math.max(0,Math.min(100,Number(v.name_similarity||0))); setText('similarity-label',similarity.toFixed(1)+'%'); document.getElementById('similarity-bar').style.width=similarity+'%';
        document.getElementById('valid-flags').innerHTML=boolLabel('Nama sesuai',v.name_match)+boolLabel('Tanggal lahir sesuai',v.birth_date_match)+boolLabel('Dapat dilanjutkan',v.can_continue_with_confirmation);
        const fields=[['NIP',data.nip],['Status pegawai',s.status_pegawai],['Golongan',s.golongan],['TMT golongan',s.tmt_golongan],['Masa kerja',`${s.mk_golongan||0} tahun ${s.mk_golongan_bulan||0} bulan`],['Gaji pokok',money(s.gaji_pokok)],['Sumber gaji',s.gaji_pokok_source_label],['Unit kerja',s.unit_kerja],['Jabatan',s.jabatan,true],['Jenjang pendidikan',s.jenjang_pendidikan],['Pendidikan',s.pendidikan,true]];
        const grid=document.getElementById('simpeg-grid'); grid.replaceChildren(...fields.map(([label,value,wide])=>{const item=document.createElement('div');item.className='detail-item'+(wide?' wide':'');const span=document.createElement('span');span.textContent=label;const strong=document.createElement('strong');strong.textContent=safe(value);item.append(span,strong);return item;}));
    }
})();
</script>
@stop

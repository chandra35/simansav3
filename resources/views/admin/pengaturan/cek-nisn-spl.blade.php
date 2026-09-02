@extends('adminlte::page')

@section('title', 'Cek SPL EMIS')

@section('content_header')
<div class="d-flex flex-wrap justify-content-between align-items-center">
    <div>
        <h1 class="m-0 text-dark"><i class="fas fa-search-location mr-2 text-primary"></i>Cek SPL EMIS</h1>
        <small class="text-muted">Pemeriksaan riwayat peserta didik melalui layanan EMIS</small>
    </div>
    <a href="{{ route('admin.pengaturan.cek-nisn.index') }}" class="btn btn-outline-secondary mt-2 mt-sm-0">
        <i class="fas fa-id-card mr-1"></i> Cek NISN Siswa
    </a>
</div>
@stop

@section('content')
<style>
    #splNisnPage{max-width:1180px;margin:0 auto 2rem;color:#1f2937}
    #splNisnPage .spl-hero{border:0!important;border-radius:18px!important;overflow:hidden;background:linear-gradient(120deg,#264488 0%,#2475aa 55%,#239b91 100%)!important;box-shadow:0 16px 34px rgba(35,78,136,.18)!important;color:#fff}
    #splNisnPage .spl-hero .card-body{padding:1.6rem 1.75rem!important}
    #splNisnPage .spl-kicker{display:inline-flex;align-items:center;gap:.4rem;padding:.35rem .65rem;border:1px solid rgba(255,255,255,.18);border-radius:99px;background:rgba(255,255,255,.12);font-size:.7rem;font-weight:700;letter-spacing:.07em}
    #splNisnPage .spl-hero h2{color:#fff!important;font-size:1.65rem!important;font-weight:700!important;margin:.65rem 0 .35rem!important}
    #splNisnPage .spl-hero p{margin:0;color:rgba(255,255,255,.83);font-size:.9rem;line-height:1.55}
    #splNisnPage .credential-state{display:flex;align-items:center;gap:.8rem;padding:1rem;border:1px solid rgba(255,255,255,.18);border-radius:13px;background:rgba(12,35,76,.2)}
    #splNisnPage .credential-state i{font-size:1.55rem}.credential-state strong,.credential-state small{display:block;color:#fff}.credential-state small{margin-top:.12rem;color:rgba(255,255,255,.72);font-size:.76rem}
    #splNisnPage .credential-state.ready i{color:#72edb0}#splNisnPage .credential-state.missing i{color:#ffd166}
    #splNisnPage .spl-card{border:1px solid #e2e8f0!important;border-radius:14px!important;box-shadow:0 7px 20px rgba(31,45,61,.07)!important;overflow:hidden}
    #splNisnPage .search-title{display:flex;align-items:center;gap:.75rem;margin-bottom:1.1rem}.search-title .icon{width:42px;height:42px;display:grid;place-items:center;border-radius:11px;background:#eaf0ff;color:#3d55d6}.search-title h3{margin:0;font-size:1rem;font-weight:700}.search-title p{margin:.16rem 0 0;color:#718096;font-size:.79rem}
    #splNisnPage .nisn-label{font-size:.76rem;text-transform:uppercase;letter-spacing:.06em;color:#556174;margin-bottom:.45rem}.nisn-wrap{display:flex;align-items:stretch;border:1px solid #cfd8e7;border-radius:11px;overflow:hidden;background:#fff;transition:.18s}.nisn-wrap:focus-within{border-color:#5268e8;box-shadow:0 0 0 3px rgba(82,104,232,.12)}.nisn-prefix{width:48px;display:grid;place-items:center;color:#62718a;background:#f7f9fc;border-right:1px solid #e3e8f0}.nisn-wrap input{min-width:0;height:48px;border:0!important;box-shadow:none!important;padding:.6rem .85rem;font-size:1rem;letter-spacing:.06em}.nisn-wrap button{margin:5px;border:0;border-radius:8px;padding:0 1.25rem;white-space:nowrap;font-weight:600}
    #splNisnPage .result-summary{display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:.8rem;border-bottom:1px solid #e7ebf1;padding:1rem 1.25rem}.result-summary h3{font-size:1rem;margin:0;font-weight:700}.result-summary p{font-size:.78rem;color:#6d7b91;margin:.15rem 0 0}.record-count{padding:.35rem .65rem;border-radius:20px;background:#e9f7ef;color:#137b48;font-size:.77rem;font-weight:700}
    #splNisnPage .spl-table{margin:0}.spl-table thead th{border-top:0;border-bottom:2px solid #dce3ec;background:#f8fafc;color:#58677d;font-size:.71rem;letter-spacing:.04em;text-transform:uppercase;white-space:nowrap}.spl-table td{vertical-align:middle;font-size:.84rem;color:#334155}.spl-table td small{display:block;color:#738197;margin-top:.22rem}.status-pill{display:inline-flex;align-items:center;gap:.3rem;border-radius:16px;padding:.28rem .55rem;font-size:.73rem;font-weight:700}.status-pill.ready{background:#e1f5e9;color:#18794e}.status-pill.disabled{background:#fff1e8;color:#b45309}.id-code{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:.72rem;overflow-wrap:anywhere;color:#63728a}
    #splNisnPage .reference-button{font-size:.77rem;white-space:nowrap}#splNisnPage .reference-panel{border-top:1px solid #e7ebf1;background:#fbfcfe}#splNisnPage .reference-panel-inner{padding:1rem 1.15rem}#splNisnPage .reference-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.75rem}#splNisnPage .reference-item{border:1px solid #e3e9f1;border-radius:9px;background:#fff;padding:.7rem .8rem}#splNisnPage .reference-item span{display:block;font-size:.67rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#718096;margin-bottom:.2rem}#splNisnPage .reference-item strong{font-size:.82rem;overflow-wrap:anywhere}#splNisnPage .reference-heading{font-size:.81rem;font-weight:700;color:#334155;margin:0 0 .55rem}#splNisnPage .reference-student{margin-top:.8rem}#splNisnPage .reference-hint{font-size:.75rem;color:#718096;margin:.35rem 0 0}#splNisnPage .reference-status{font-size:.79rem;color:#718096;padding:.45rem 0}#splNisnPage .reference-status.error{color:#b45309}
    #splNisnPage .record-mobile{display:none}.spl-empty{padding:2.25rem 1rem;text-align:center;color:#728097}.spl-empty i{display:block;font-size:1.45rem;color:#a9b4c4;margin-bottom:.6rem}
    @media(max-width:767.98px){#splNisnPage .spl-hero .card-body{padding:1.3rem!important}#splNisnPage .spl-hero h2{font-size:1.35rem!important}#splNisnPage .nisn-wrap{flex-wrap:wrap}#splNisnPage .nisn-prefix{width:44px}#splNisnPage .nisn-wrap input{width:calc(100% - 44px)}#splNisnPage .nisn-wrap button{width:calc(100% - 10px);height:42px;margin-top:0}#splNisnPage .spl-table-wrap{display:none}#splNisnPage .record-mobile{display:grid;gap:.75rem;padding:1rem}#splNisnPage .spl-record{border:1px solid #e2e8f0;border-radius:12px;padding:.9rem;background:#fff}#splNisnPage .spl-record h4{font-size:.95rem;margin:0;color:#1f2937}#splNisnPage .spl-record-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.65rem;margin-top:.75rem}#splNisnPage .spl-record-grid div{min-width:0}#splNisnPage .spl-record-grid span{display:block;font-size:.68rem;color:#728097;text-transform:uppercase;letter-spacing:.04em}#splNisnPage .spl-record-grid strong{display:block;font-size:.79rem;margin-top:.16rem;overflow-wrap:anywhere}#splNisnPage .spl-record .id-code{margin-top:.65rem}#splNisnPage .reference-grid{grid-template-columns:1fr}#splNisnPage .reference-panel-inner{padding:.85rem}#splNisnPage .credential-state{margin-top:1rem}}
</style>

<div id="splNisnPage">
    <div class="card spl-hero mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <span class="spl-kicker"><i class="fas fa-shield-alt"></i> LAYANAN EMIS · SPL</span>
                    <h2>Periksa kelayakan tarik data berdasarkan identitas</h2>
                    <p>Pilih NISN atau NIK untuk melihat setiap riwayat peserta didik yang tersedia di layanan SPL EMIS. Data ditampilkan untuk verifikasi dan tidak mengubah data SIMANSA.</p>
                </div>
                <div class="col-lg-4">
                    <div class="credential-state {{ $credentialConfigured ? 'ready' : 'missing' }}">
                        <i class="fas {{ $credentialConfigured ? 'fa-check-circle' : 'fa-exclamation-triangle' }}"></i>
                        <div>
                            <strong>{{ $credentialConfigured ? 'Token EMIS tersimpan' : 'Token EMIS belum tersedia' }}</strong>
                            <small>{{ $credentialConfigured ? 'Akses token diperiksa saat pencarian' : 'Perbarui token melalui menu Update API Token' }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card spl-card mb-3">
        <div class="card-body">
            <div class="search-title"><div class="icon"><i class="fas fa-fingerprint"></i></div><div><h3>Pencarian identitas SPL</h3><p>Pilih jenis identitas yang tersedia pada layanan EMIS.</p></div></div>
            <div id="spl-nisn-form" data-check-url="{{ route('admin.pengaturan.cek-nisn-spl.check') }}">
                <div class="row">
                    <div class="col-md-3 mb-3 mb-md-0"><label for="identity-type" class="nisn-label">Jenis pemeriksaan</label><select id="identity-type" name="type" class="form-control"><option value="nisn">NISN</option><option value="nik">NIK</option></select></div>
                    <div class="col-md-9"><label for="identity-number" id="identity-label" class="nisn-label">Nomor Induk Siswa Nasional</label><div class="nisn-wrap"><span class="nisn-prefix"><i class="fas fa-id-card"></i></span><input id="identity-number" name="number" class="form-control" inputmode="numeric" pattern="[0-9]+" maxlength="10" placeholder="Masukkan 10 digit NISN" aria-describedby="identity-help" required><button class="btn btn-primary" id="check-button" type="button" {{ $credentialConfigured ? '' : 'disabled' }}><i class="fas fa-search mr-1"></i> Cek SPL</button></div><small id="identity-help" class="form-text text-muted"><span id="digit-count">0</span>/<span id="digit-limit">10</span> digit · Token EMIS diproses aman di server dan tidak ditampilkan pada halaman ini.</small></div>
                </div>
            </div>
        </div>
    </div>

    <div id="result-alert" class="alert d-none mb-3" role="alert"></div>

    <section id="result" class="card spl-card d-none" aria-live="polite">
        <div class="result-summary"><div><h3><i class="fas fa-history mr-2 text-primary"></i>Riwayat SPL</h3><p id="result-description">Riwayat peserta didik dari EMIS.</p></div><span id="record-count" class="record-count">0 riwayat</span></div>
        <div class="spl-table-wrap table-responsive"><table class="table spl-table"><thead><tr><th>Peserta didik</th><th>Identitas</th><th>Informasi SPL</th><th>Status</th><th>Referensi</th></tr></thead><tbody id="result-body"></tbody></table></div>
        <div id="result-mobile" class="record-mobile"></div>
    </section>
</div>
@stop

@section('js')
<script>
(() => {
    const form = document.getElementById('spl-nisn-form'), input = document.getElementById('identity-number'), type = document.getElementById('identity-type'), button = document.getElementById('check-button');
    const result = document.getElementById('result'), alertBox = document.getElementById('result-alert');
    const identities = {nisn:{label:'Nomor Induk Siswa Nasional',placeholder:'Masukkan 10 digit NISN',digits:10},nik:{label:'Nomor Induk Kependudukan',placeholder:'Masukkan 16 digit NIK',digits:16}};
    const safe = value => value === null || value === undefined || value === '' ? '-' : String(value);
    const date = value => { if (!value) return '-'; const parsed = new Date(value); return Number.isNaN(parsed.getTime()) ? safe(value) : parsed.toLocaleDateString('id-ID',{day:'2-digit',month:'long',year:'numeric'}); };
    const gender = value => value === 'L' ? 'Laki-laki' : value === 'P' ? 'Perempuan' : safe(value);
    const statusText = record => Number(record.is_disable) === 1 ? 'Tidak tersedia' : (record.keterangan || 'Tersedia');

    function syncIdentityInput() { const identity=identities[type.value]; input.value=input.value.replace(/\D/g,'').slice(0,identity.digits); input.maxLength=identity.digits; input.placeholder=identity.placeholder; document.getElementById('identity-label').textContent=identity.label; document.getElementById('digit-limit').textContent=identity.digits; document.getElementById('digit-count').textContent=input.value.length; }
    type.addEventListener('change', syncIdentityInput); input.addEventListener('input', syncIdentityInput); syncIdentityInput();
    async function checkSpl() {
        const identity=identities[type.value];
        if (!new RegExp('^\\d{'+identity.digits+'}$').test(input.value)) return showError((type.value === 'nisn' ? 'NISN' : 'NIK')+' harus terdiri dari tepat '+identity.digits+' digit angka.');
        button.disabled = true; button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Memeriksa'; result.classList.add('d-none'); alertBox.classList.add('d-none');
        try {
            const response = await fetch(form.dataset.checkUrl, {method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':@json(csrf_token())},body:JSON.stringify({type:type.value,number:input.value})});
            const payload = await response.json();
            if (!response.ok || !payload.success) throw new Error(payload.message || 'Pemeriksaan SPL gagal.');
            render(payload.data); result.classList.remove('d-none');
        } catch (error) { showError(error.message || 'Tidak dapat menghubungi server.'); }
        finally { button.disabled = false; button.innerHTML = '<i class="fas fa-search mr-1"></i> Cek SPL'; }
    }
    button.addEventListener('click', checkSpl);
    input.addEventListener('keydown', event => { if (event.key === 'Enter') { event.preventDefault(); checkSpl(); } });
    function showError(message) { alertBox.className='alert alert-danger'; alertBox.textContent=message; alertBox.classList.remove('d-none'); }
    function cell(content, className='') { const td=document.createElement('td'); if(className) td.className=className; td.textContent=safe(content); return td; }
    function status(record) { const span=document.createElement('span'); const unavailable=Number(record.is_disable)===1; span.className='status-pill '+(unavailable?'disabled':'ready'); span.innerHTML='<i class="fas '+(unavailable?'fa-ban':'fa-check-circle')+'"></i>'; span.append(document.createTextNode(statusText(record))); return span; }
    function info(label,value) { const div=document.createElement('div'), span=document.createElement('span'), strong=document.createElement('strong'); span.textContent=label; strong.textContent=safe(value); div.append(span,strong); return div; }
    const schoolCache = new Map();
    const studentProfileUrl = id => 'https://vervalpd.data.kemendikdasmen.go.id/index.php/Cprofil/profil?id='+encodeURIComponent(id || '');
    function referencePanel(record) {
        const panel=document.createElement('div'); panel.className='collapse reference-panel';
        const inner=document.createElement('div'); inner.className='reference-panel-inner';
        const schoolTitle=document.createElement('p'); schoolTitle.className='reference-heading'; schoolTitle.innerHTML='<i class="fas fa-school mr-1 text-primary"></i>Profil sekolah asal';
        const schoolState=document.createElement('div'); schoolState.className='reference-status'; schoolState.textContent='Buka panel untuk memuat metadata sekolah.'; schoolState.dataset.schoolState='true';
        const student=document.createElement('div'); student.className='reference-student'; const studentTitle=document.createElement('p'); studentTitle.className='reference-heading'; studentTitle.innerHTML='<i class="fas fa-user-graduate mr-1 text-primary"></i>Profil peserta didik';
        const studentGrid=document.createElement('div'); studentGrid.className='reference-grid'; studentGrid.append(info('Peserta didik ID',record.peserta_didik_id));
        const hint=document.createElement('p'); hint.className='reference-hint';
        if (record.peserta_didik_id) { const link=document.createElement('a'); link.href=studentProfileUrl(record.peserta_didik_id); link.target='_blank'; link.rel='noopener noreferrer'; link.textContent='Buka profil di Verval PD'; hint.append(link,document.createTextNode(' · Portal Verval PD memerlukan sesi login tersendiri.')); } else { hint.textContent='ID peserta didik tidak tersedia pada respons SPL.'; }
        student.append(studentTitle,studentGrid,hint); inner.append(schoolTitle,schoolState,student); panel.append(inner); return panel;
    }
    function referenceButton(record,panel) {
        const button=document.createElement('button'); button.type='button'; button.className='btn btn-outline-primary btn-sm reference-button'; button.innerHTML='<i class="fas fa-chevron-down mr-1"></i> Detail'; button.setAttribute('aria-expanded','false');
        button.addEventListener('click', async () => { const open=!panel.classList.contains('show'); panel.classList.toggle('show',open); button.setAttribute('aria-expanded',String(open)); button.innerHTML=open?'<i class="fas fa-chevron-up mr-1"></i> Tutup':'<i class="fas fa-chevron-down mr-1"></i> Detail'; if(open) await loadSchoolProfile(panel,record.sekolah_id); });
        return button;
    }
    async function loadSchoolProfile(panel,schoolId) {
        const state=panel.querySelector('[data-school-state]'); if (!state || state.dataset.loaded === 'true') return;
        if (!schoolId) { state.classList.add('error'); state.textContent='ID sekolah tidak tersedia pada respons SPL.'; state.dataset.loaded='true'; return; }
        state.textContent='Memuat metadata sekolah…';
        try { let profile=schoolCache.get(schoolId); if (!profile) { const response=await fetch(@json(route('admin.pengaturan.cek-nisn-spl.school-profile')), {method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':@json(csrf_token())},body:JSON.stringify({school_id:schoolId})}); const payload=await response.json(); if(!response.ok || !payload.success) throw new Error(payload.message || 'Profil sekolah tidak tersedia.'); profile=payload.data; schoolCache.set(schoolId,profile); }
            const grid=document.createElement('div'); grid.className='reference-grid'; grid.append(info('Nama sekolah',profile.nama),info('NPSN',profile.npsn),info('Bentuk pendidikan',profile.bentuk_pendidikan),info('Status sekolah',profile.status_sekolah),info('Alamat',profile.alamat));
            const link=document.createElement('a'); link.href=profile.source_url; link.target='_blank'; link.rel='noopener noreferrer'; link.className='btn btn-outline-secondary btn-sm mt-2'; link.innerHTML='<i class="fas fa-external-link-alt mr-1"></i> Buka profil sekolah'; state.replaceChildren(grid,link); state.dataset.loaded='true';
        } catch (error) { state.classList.add('error'); state.textContent=error.message || 'Metadata sekolah tidak dapat dimuat.'; state.dataset.loaded='true'; }
    }
    function render(data) {
        const records=Array.isArray(data.records)?data.records:[]; const body=document.getElementById('result-body'), mobile=document.getElementById('result-mobile'); body.replaceChildren(); mobile.replaceChildren();
        const typeLabel=data.type === 'nik' ? 'NIK' : 'NISN'; document.getElementById('record-count').textContent=records.length+' riwayat'; document.getElementById('result-description').textContent=typeLabel+' '+safe(data.number)+' · '+records.length+' data riwayat dari EMIS.';
        records.forEach(record => {
            const row=document.createElement('tr'), person=cell(record.nama), personMeta=document.createElement('small'); personMeta.textContent='NISN: '+safe(record.nisn); person.append(personMeta);
            const identity=cell(''), nik=document.createElement('strong'); nik.textContent='NIK: '+safe(record.nik); const mom=document.createElement('small'); mom.textContent='Ibu: '+safe(record.nama_ibu_kandung); identity.append(nik,mom);
            const spl=cell(''), details=document.createElement('strong'); details.textContent='Keluar: '+date(record.tanggal_keluar); const level=document.createElement('small'); level.textContent='Tingkat pendidikan ID: '+safe(record.tingkat_pendidikan_id); spl.append(details,level);
            const statusCell=document.createElement('td'); statusCell.append(status(record)); const birth=document.createElement('small'); birth.textContent=gender(record.jenis_kelamin)+' · Lahir '+date(record.tanggal_lahir); statusCell.append(birth); const desktopPanel=referencePanel(record); const action=document.createElement('td'); action.append(referenceButton(record,desktopPanel)); row.append(person,identity,spl,statusCell,action); body.append(row); const detailRow=document.createElement('tr'); const detailCell=document.createElement('td'); detailCell.colSpan=5; detailCell.className='p-0'; detailCell.append(desktopPanel); detailRow.append(detailCell); body.append(detailRow);
            const card=document.createElement('article'); card.className='spl-record'; const title=document.createElement('h4'); title.textContent=safe(record.nama); const cardStatus=status(record); card.append(title,cardStatus); const grid=document.createElement('div'); grid.className='spl-record-grid'; grid.append(info('NISN',record.nisn),info('NIK',record.nik),info('Jenis kelamin',gender(record.jenis_kelamin)),info('Tanggal lahir',date(record.tanggal_lahir)),info('Tanggal keluar',date(record.tanggal_keluar)),info('Tingkat pendidikan ID',record.tingkat_pendidikan_id),info('Nama ibu kandung',record.nama_ibu_kandung),info('Jenis keluar ID',record.jenis_keluar_id)); card.append(grid); const code=document.createElement('div'); code.className='id-code'; code.textContent='Peserta didik ID: '+safe(record.peserta_didik_id); card.append(code); const mobilePanel=referencePanel(record); const mobileButton=referenceButton(record,mobilePanel); mobileButton.classList.add('mt-2'); card.append(mobileButton,mobilePanel); mobile.append(card);
        });
    }
})();
</script>
@stop

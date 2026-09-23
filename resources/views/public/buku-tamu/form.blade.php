<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Buku Tamu PTSP - {{ $setting->nama_sekolah ?? 'SIMANSA' }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root { --primary: #0f67b1; --ink: #0f2b4e; }
        body { min-height: 100vh; background: radial-gradient(circle at top right, #2ec4b6 0, transparent 38%), linear-gradient(135deg, #0b5595, #17a79d); }
        .guest-card { max-width: 720px; margin: 1.5rem auto; border: 0; border-radius: 1.2rem; box-shadow: 0 1.2rem 3.5rem rgba(0,0,0,.2); overflow: hidden; }
        .guest-header { color: #fff; text-align: center; background: linear-gradient(135deg, rgba(15,43,78,.98), rgba(16,92,151,.92)); }
        .school-logo { width: 76px; height: 76px; object-fit: contain; padding: .55rem; border-radius: 1.2rem; background: rgba(255,255,255,.96); box-shadow: 0 8px 20px rgba(0,0,0,.14); }
        .guest-header .eyebrow { color: #9ed8ff; font-size: .72rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
        .form-label { font-weight: 700; color: #26384d; }
        .form-control, .form-select { min-height: 46px; border-radius: .75rem; }
        textarea.form-control { min-height: 90px; }
        .success-panel { border: 1px solid #a7e3c1; border-radius: 1rem; background: linear-gradient(135deg,#f0fff6,#fff); text-align: center; }
        .success-icon { width: 64px; height: 64px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; background: #d7f8e3; color: #16834b; font-size: 2rem; }
        .section-label { color: #64748b; font-size: .72rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
        .required { color: #dc3545; }
        .visit-info { border: 1px solid #dceeff; border-radius: .9rem; background: #f5fbff; color: #31516d; }
        .visit-info strong { color: #0f67b1; }
    </style>
</head>
<body>
<main class="container py-2 py-md-5">
    <section class="card guest-card">
        <header class="guest-header p-4 p-md-5">
            <img src="{{ $setting->logo_sekolah_url }}" class="school-logo mb-3" alt="Logo {{ $setting->nama_sekolah ?? 'sekolah' }}">
            <div class="eyebrow mb-2">Layanan PTSP · Buku Tamu</div>
            <h1 class="h3 mb-2">Selamat Datang</h1>
            <p class="mb-0 opacity-75">{{ $setting->nama_sekolah ?? 'SIMANSA' }}</p>
        </header>
        <div class="card-body p-4 p-md-5">
            @if(session('success'))
                <div class="success-panel p-4 mb-4" role="status">
                    <div class="success-icon mb-3"><span aria-hidden="true">✓</span></div>
                    <h2 class="h5 text-success mb-2">{{ session('success') }}</h2>
                    <p class="text-muted mb-3">{{ session('success_meta') }}</p>
                    <a href="{{ route('public.buku-tamu.token', $token) }}" class="btn btn-outline-success">Isi Kunjungan Baru</a>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger" role="alert"><strong>Data belum tersimpan.</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif

            <div class="visit-info p-3 mb-4 d-flex align-items-start gap-3"><i class="fas fa-info-circle mt-1"></i><div><strong>Kunjungan hari ini</strong><div class="small">{{ now('Asia/Jakarta')->translatedFormat('l, d F Y') }} · Data digunakan untuk pencatatan layanan PTSP.</div></div></div>
            <form method="POST" action="{{ route('public.buku-tamu.store', $token) }}" novalidate>
                @csrf
                <div class="section-label mb-3">Identitas Pengunjung</div>
                <div class="mb-3">
                    <label class="form-label" for="jenis_tamu">Jenis Pengunjung <span class="required">*</span></label>
                    <select id="jenis_tamu" name="jenis_tamu" class="form-select @error('jenis_tamu') is-invalid @enderror" required>
                        <option value="">Pilih jenis pengunjung</option>
                        <option value="instansi" @selected(old('jenis_tamu') === 'instansi')>Instansi</option>
                        <option value="lembaga" @selected(old('jenis_tamu') === 'lembaga')>Lembaga</option>
                        <option value="individu" @selected(old('jenis_tamu') === 'individu')>Individu</option>
                    </select>
                    @error('jenis_tamu')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="nama" id="namaLabel">Nama <span class="required">*</span></label>
                    <input id="nama" name="nama" value="{{ old('nama') }}" class="form-control @error('nama') is-invalid @enderror" required maxlength="150" autocomplete="name">
                    @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div id="originSection" class="mb-3 d-none">
                    <label class="form-label" for="alamat_instansi" id="alamatInstansiLabel">Nama Instansi/Lembaga <span class="required">*</span></label>
                    <input id="alamat_instansi" name="alamat_instansi" value="{{ old('alamat_instansi') }}" class="form-control @error('alamat_instansi') is-invalid @enderror" required maxlength="2000">
                    @error('alamat_instansi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="nomor_hp">No. HP <span class="required">*</span></label>
                    <input id="nomor_hp" name="nomor_hp" value="{{ old('nomor_hp') }}" class="form-control @error('nomor_hp') is-invalid @enderror" required maxlength="18" inputmode="tel" autocomplete="tel" placeholder="Contoh: 081234567890">
                    <div class="form-text">Boleh memakai format 08..., 62..., atau +62....</div>
                    @error('nomor_hp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <fieldset id="addressSection" class="border-0 p-0 m-0 d-none">
                <div class="section-label mt-4 mb-3">Alamat Pengunjung</div>
                <div class="mb-3">
                    <label class="form-label" for="alamat">Alamat Jalan/Detail <span class="required">*</span></label>
                    <textarea id="alamat" name="alamat" rows="2" class="form-control @error('alamat') is-invalid @enderror" required maxlength="2000" placeholder="Jalan, nomor rumah, gedung, atau keterangan alamat">{{ old('alamat') }}</textarea>
                    @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label" for="provinsi_code">Provinsi <span class="required">*</span></label><select id="provinsi_code" name="provinsi_code" class="form-select" required><option value="">Pilih Provinsi</option>@foreach($provinces as $province)<option value="{{ $province->code }}" @selected(old('provinsi_code') === $province->code)>{{ $province->name }}</option>@endforeach</select></div>
                    <div class="col-md-6"><label class="form-label" for="kota_code">Kabupaten/Kota <span class="required">*</span></label><select id="kota_code" name="kota_code" class="form-select" required disabled><option value="">Pilih Kabupaten/Kota</option></select></div>
                    <div class="col-md-6"><label class="form-label" for="kecamatan_code">Kecamatan <span class="required">*</span></label><select id="kecamatan_code" name="kecamatan_code" class="form-select" required disabled><option value="">Pilih Kecamatan</option></select></div>
                    <div class="col-md-6"><label class="form-label" for="kelurahan_code">Kelurahan/Desa <span class="required">*</span></label><select id="kelurahan_code" name="kelurahan_code" class="form-select" required disabled><option value="">Pilih Kelurahan/Desa</option></select></div>
                </div>
                </fieldset>

                <div class="section-label mt-4 mb-3">Keperluan</div>
                <div class="mb-4"><label class="form-label" for="keperluan">Keperluan Kunjungan <span class="required">*</span></label><textarea id="keperluan" name="keperluan" rows="3" class="form-control @error('keperluan') is-invalid @enderror" required maxlength="2000">{{ old('keperluan') }}</textarea>@error('keperluan')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div id="submitHint" class="alert alert-light border text-muted small mb-3" role="status" aria-live="polite"><i class="fas fa-info-circle mr-1"></i> Lengkapi seluruh data wajib untuk menampilkan tombol simpan.</div>
                <button id="submitButton" class="btn btn-primary btn-lg w-100 d-none" type="submit"><span class="submit-label">Simpan Data Kunjungan</span><span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span></button>
            </form>
        </div>
    </section>
</main>
<script>
const oldValues = @json(['kota_code' => old('kota_code'), 'kecamatan_code' => old('kecamatan_code'), 'kelurahan_code' => old('kelurahan_code')]);
const selectData = {
    kota_code: { url: code => `{{ url('/buku-tamu-api/cities') }}/${code}`, next: 'kecamatan_code', label: 'Pilih Kabupaten/Kota' },
    kecamatan_code: { url: code => `{{ url('/buku-tamu-api/districts') }}/${code}`, next: 'kelurahan_code', label: 'Pilih Kecamatan' },
    kelurahan_code: { url: code => `{{ url('/buku-tamu-api/villages') }}/${code}`, next: null, label: 'Pilih Kelurahan/Desa' },
};
function resetSelect(id, label) { const el = document.getElementById(id); el.innerHTML = `<option value="">${label}</option>`; el.disabled = true; }
function loadNext(targetId, sourceCode, selected = '') {
    if (!sourceCode) return;
    const config = selectData[targetId]; const target = document.getElementById(targetId); target.innerHTML = `<option value="">Memuat...</option>`; target.disabled = true;
    fetch(config.url(sourceCode), { headers: { Accept: 'application/json' } }).then(response => response.json()).then(items => { target.innerHTML = `<option value="">${config.label}</option>`; items.forEach(item => { const option = new Option(item.name, item.code, false, item.code === selected); target.add(option); }); target.disabled = false; updateSubmitState(); if (config.next && selected) loadNext(config.next, selected, oldValues[config.next]); }).catch(() => { target.innerHTML = `<option value="">Gagal memuat data</option>`; updateSubmitState(); });
}
document.getElementById('provinsi_code').addEventListener('change', function () { resetSelect('kota_code', 'Pilih Kabupaten/Kota'); resetSelect('kecamatan_code', 'Pilih Kecamatan'); resetSelect('kelurahan_code', 'Pilih Kelurahan/Desa'); loadNext('kota_code', this.value, oldValues.kota_code); });
document.getElementById('kota_code').addEventListener('change', function () { resetSelect('kecamatan_code', 'Pilih Kecamatan'); resetSelect('kelurahan_code', 'Pilih Kelurahan/Desa'); loadNext('kecamatan_code', this.value, oldValues.kecamatan_code); });
document.getElementById('kecamatan_code').addEventListener('change', function () { resetSelect('kelurahan_code', 'Pilih Kelurahan/Desa'); loadNext('kelurahan_code', this.value, oldValues.kelurahan_code); });
const form = document.querySelector('form'); const type = document.getElementById('jenis_tamu'); const nameLabel = document.getElementById('namaLabel'); const originSection = document.getElementById('originSection'); const originInput = document.getElementById('alamat_instansi'); const phoneInput = document.getElementById('nomor_hp'); const submitButton = document.getElementById('submitButton'); const submitHint = document.getElementById('submitHint');
function normalizePhone(value) { let phone = value.replace(/[\s().-]+/g, ''); if (phone.startsWith('+62')) return '0' + phone.slice(3); if (phone.startsWith('62')) return '0' + phone.slice(2); return phone; }
function updateSubmitState() { const requiredFields = [...form.querySelectorAll('[required]')].filter(element => !element.disabled); const fieldsComplete = requiredFields.every(element => (element.value || '').trim() !== ''); const phoneValid = /^08[1-9][0-9]{7,10}$/.test(normalizePhone(phoneInput.value)); const ready = type.value !== '' && fieldsComplete && phoneValid; submitButton.classList.toggle('d-none', !ready); submitHint.classList.toggle('d-none', ready); }
function updateLabels() { const value = type.value; const section = document.getElementById('addressSection'); section.classList.toggle('d-none', !value); section.querySelectorAll('input, select, textarea').forEach(element => { element.disabled = !value; }); const showOrigin = value === 'instansi' || value === 'lembaga'; originSection.classList.toggle('d-none', !showOrigin); originInput.disabled = !showOrigin; originInput.required = showOrigin; nameLabel.innerHTML = `${value === 'individu' ? 'Nama' : 'Nama Penanggung Jawab'} <span class="required">*</span>`; updateSubmitState(); }
type.addEventListener('change', updateLabels); form.addEventListener('input', updateSubmitState); form.addEventListener('change', updateSubmitState); updateLabels();
form.addEventListener('submit', function (event) { updateSubmitState(); if (submitButton.classList.contains('d-none')) { event.preventDefault(); return; } submitButton.disabled = true; submitButton.querySelector('.submit-label').textContent = 'Menyimpan...'; submitButton.querySelector('.spinner-border').classList.remove('d-none'); });
if (document.getElementById('provinsi_code').value) document.getElementById('provinsi_code').dispatchEvent(new Event('change'));
updateSubmitState();
</script>
</body>
</html>

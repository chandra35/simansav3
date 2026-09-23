<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Buku Tamu - {{ $setting->nama_sekolah ?? 'SIMANSA' }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { min-height: 100vh; background: linear-gradient(135deg, #0f5fa8, #18a7a0); }
        .guest-card { max-width: 620px; margin: 2rem auto; border: 0; border-radius: 1rem; box-shadow: 0 1rem 3rem rgba(0,0,0,.18); }
        .guest-header { color: #fff; background: rgba(15, 43, 78, .82); border-radius: 1rem 1rem 0 0; }
        .form-label { font-weight: 600; }
    </style>
</head>
<body>
<main class="container py-3 py-md-5">
    <section class="card guest-card">
        <header class="guest-header p-4 text-center">
            <h1 class="h4 mb-2">Buku Tamu PTSP</h1>
            <p class="mb-0">{{ $setting->nama_sekolah ?? 'SIMANSA' }}</p>
        </header>
        <div class="card-body p-4 p-md-5">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            <p class="text-muted">Silakan isi data kunjungan Anda dengan lengkap.</p>
            <form method="POST" action="{{ route('public.buku-tamu.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Tanggal Kunjungan</label>
                    <input type="text" class="form-control" value="{{ now()->translatedFormat('l, d F Y') }}" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="nama">Nama</label>
                    <input id="nama" name="nama" value="{{ old('nama') }}" class="form-control @error('nama') is-invalid @enderror" required maxlength="150" autocomplete="name">
                    @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="alamat_instansi">Alamat Instansi/Lembaga/Individu</label>
                    <textarea id="alamat_instansi" name="alamat_instansi" rows="3" class="form-control @error('alamat_instansi') is-invalid @enderror" required maxlength="2000">{{ old('alamat_instansi') }}</textarea>
                    @error('alamat_instansi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="nomor_hp">Nomor HP</label>
                    <input id="nomor_hp" name="nomor_hp" value="{{ old('nomor_hp') }}" class="form-control @error('nomor_hp') is-invalid @enderror" required maxlength="30" inputmode="tel" autocomplete="tel">
                    @error('nomor_hp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label" for="keperluan">Keperluan</label>
                    <textarea id="keperluan" name="keperluan" rows="3" class="form-control @error('keperluan') is-invalid @enderror" required maxlength="2000">{{ old('keperluan') }}</textarea>
                    @error('keperluan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button class="btn btn-primary w-100" type="submit">Simpan Data Kunjungan</button>
            </form>
        </div>
    </section>
</main>
</body>
</html>

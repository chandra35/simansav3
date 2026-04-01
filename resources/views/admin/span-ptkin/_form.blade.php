@csrf
@if(isset($method) && strtoupper($method) !== 'POST')
    @method($method)
@endif

<div class="card">
    <div class="card-body">
        <div class="form-group">
            <label>Nama Menu</label>
            <input type="text" name="nama_menu" class="form-control @error('nama_menu') is-invalid @enderror" value="{{ old('nama_menu', $spanPtkinMenu->nama_menu ?? 'SPAN-PTKIN') }}">
            @error('nama_menu')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        @if(!isset($spanPtkinMenu))
        <div class="form-group">
            <label>Tahun Pelajaran</label>
            <select name="tahun_pelajaran_id" class="form-control @error('tahun_pelajaran_id') is-invalid @enderror">
                @foreach($tahunPelajaranList as $tahun)
                    <option value="{{ $tahun->id }}" @selected(old('tahun_pelajaran_id', $activeTahun->id ?? null) === $tahun->id)>
                        {{ $tahun->nama }}{{ $tahun->is_active ? ' (Aktif)' : '' }}
                    </option>
                @endforeach
            </select>
            @error('tahun_pelajaran_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        @endif

        <div class="form-group">
            <label>Informasi untuk Siswa</label>
            <textarea name="konten_informasi" rows="8" class="form-control @error('konten_informasi') is-invalid @enderror">{{ old('konten_informasi', $spanPtkinMenu->konten_informasi ?? '') }}</textarea>
            @error('konten_informasi')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Tanggal Mulai</label>
                    <input type="datetime-local" name="tanggal_mulai" class="form-control @error('tanggal_mulai') is-invalid @enderror" value="{{ old('tanggal_mulai', isset($spanPtkinMenu) && $spanPtkinMenu->tanggal_mulai ? $spanPtkinMenu->tanggal_mulai->format('Y-m-d\\TH:i') : '') }}">
                    @error('tanggal_mulai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Tanggal Berakhir</label>
                    <input type="datetime-local" name="tanggal_berakhir" class="form-control @error('tanggal_berakhir') is-invalid @enderror" value="{{ old('tanggal_berakhir', isset($spanPtkinMenu) && $spanPtkinMenu->tanggal_berakhir ? $spanPtkinMenu->tanggal_berakhir->format('Y-m-d\\TH:i') : '') }}">
                    @error('tanggal_berakhir')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" @checked(old('is_active', $spanPtkinMenu->is_active ?? true))>
            <label class="custom-control-label" for="is_active">Aktifkan menu ini</label>
        </div>
    </div>
    <div class="card-footer">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Simpan
        </button>
        <a href="{{ route('admin.span-ptkin-menu.index') }}" class="btn btn-secondary">
            Kembali
        </a>
    </div>
</div>

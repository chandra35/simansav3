@php
    $composerStudent = $selectedStudent ?? null;
    $composerDate = old('tanggal', now()->toDateString());
@endphp

<div class="modal fade" id="modalTambahCatatan" tabindex="-1" role="dialog" aria-labelledby="modalTambahCatatanLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <form method="POST" action="{{ route('admin.gtk.wali.catatan.store') }}" id="formTambahCatatan" class="modal-content">
            @csrf
            <input type="hidden" name="form_context" value="create">
            <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
            <input type="hidden" name="siswa_id" id="catatanSiswaId" value="{{ old('siswa_id', $composerStudent?->id) }}">
            <div class="modal-header bg-light">
                <h5 class="modal-title text-dark" id="modalTambahCatatanLabel"><i class="fas fa-pen-fancy mr-1"></i> Tulis Catatan Siswa</h5>
                <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors focus:outline-none" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="selected-student mb-3">
                    <img id="catatanSiswaFoto" src="{{ $composerStudent?->foto_profile_url }}" alt="Foto siswa">
                    <div class="min-w-0">
                        <div class="text-muted small text-uppercase font-weight-bold">Catatan untuk</div>
                        <div class="font-weight-bold text-dark selected-student-name" id="catatanSiswaNama">{{ $composerStudent?->nama_lengkap ?? 'Pilih siswa' }}</div>
                        <small class="text-muted" id="catatanSiswaIdentitas">NISN {{ $composerStudent?->nisn ?: '—' }} · {{ $kelas->nama_kelas }}</small>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="tanggal">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" id="tanggal" value="{{ $composerDate }}" max="{{ now()->toDateString() }}" class="form-control @error('tanggal') is-invalid @enderror" required>
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="kategori">Kategori</label>
                        <select name="kategori" id="kategori" class="form-control">
                            <option value="">Umum</option>
                            @foreach($kategoriList as $key => $label)<option value="{{ $key }}" {{ old('kategori') === $key ? 'selected' : '' }}>{{ $label }}</option>@endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group mb-2">
                    <div class="d-flex justify-content-between align-items-center"><label for="catatan" class="mb-1">Isi Catatan <span class="text-danger">*</span></label><small class="text-muted"><span id="noteCounter">0</span>/5000</small></div>
                    <textarea name="catatan" id="catatan" class="form-control @error('catatan') is-invalid @enderror" required>{{ old('catatan') }}</textarea>
                </div>
                <div class="visual-tools mb-3" aria-label="Emoji dan simbol cepat">
                    <div class="small font-weight-bold text-muted mb-2"><i class="far fa-smile mr-1"></i> Emoji & simbol cepat</div>
                    <div class="symbol-list">@foreach(['🙂','😊','👏','⭐','✅','⚠️','📌','📚','🏆','💡','❤️','→','•','✓','★'] as $symbol)<button type="button" class="btn btn-light btn-sm btn-insert-symbol" data-target="#catatan" data-symbol="{{ $symbol }}" title="Sisipkan {{ $symbol }}">{{ $symbol }}</button>@endforeach</div>
                </div>
                <div class="quick-prompts mb-3">
                    <div class="small font-weight-bold text-muted mb-2"><i class="fas fa-magic mr-1"></i> Awalan cepat</div>
                    @foreach(['Menunjukkan perkembangan baik dalam ', 'Perlu pendampingan pada ', 'Tindak lanjut yang disepakati: '] as $prompt)<button type="button" class="btn btn-sm btn-outline-secondary btn-insert-prompt mb-1" data-target="#catatan" data-prompt="{{ $prompt }}">{{ $prompt }}</button>@endforeach
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="is_penting" name="is_penting" value="1" {{ old('is_penting') ? 'checked' : '' }}>
                    <label class="custom-control-label" for="is_penting"><i class="fas fa-star text-warning mr-1"></i> Tandai penting untuk perhatian khusus</label>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan Catatan</button></div>
        </form>
    </div>
</div>

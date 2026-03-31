<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-id-card"></i> Nomor Pendaftaran SNBP
        </h3>
    </div>
    <form action="{{ route('siswa.snbp.store') }}" method="POST">
        @csrf
        <div class="card-body">
            <div class="alert {{ filled($registration?->nomor_pendaftaran) ? 'alert-success' : 'alert-warning' }}">
                <i class="fas {{ filled($registration?->nomor_pendaftaran) ? 'fa-check-circle' : 'fa-exclamation-triangle' }}"></i>
                @if(filled($registration?->nomor_pendaftaran))
                    Nomor pendaftaran SNBP Anda sudah tersimpan. Anda masih bisa memperbaruinya bila ada koreksi.
                @else
                    Karena Anda berstatus eligible, nomor pendaftaran SNBP wajib dilengkapi agar sistem bisa menyiapkan pengecekan pengumuman otomatis.
                @endif
            </div>

            <div class="form-group">
                <label for="nomor_pendaftaran">Nomor Pendaftaran SNBP</label>
                <input
                    type="text"
                    name="nomor_pendaftaran"
                    id="nomor_pendaftaran"
                    class="form-control @error('nomor_pendaftaran') is-invalid @enderror"
                    value="{{ old('nomor_pendaftaran', $registration?->nomor_pendaftaran) }}"
                    placeholder="Masukkan nomor pendaftaran SNBP"
                    required
                >
                <small class="text-muted">Ambil dari kartu peserta SNBP Anda. Sistem akan memakai nomor ini bersama tanggal lahir akun untuk proses checker.</small>
                @error('nomor_pendaftaran')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100 bg-light">
                        <div class="text-muted small mb-1">Status Checker</div>
                        <div class="font-weight-bold">{{ $registration?->check_status_label ?? 'Belum Dicek' }}</div>
                        <div class="small text-muted mt-1">Update terakhir: {{ $registration?->last_checked_at?->format('d M Y H:i') ?? '-' }}</div>
                    </div>
                </div>
                <div class="col-md-6 mt-3 mt-md-0">
                    <div class="border rounded p-3 h-100 bg-light">
                        <div class="text-muted small mb-1">Keterkaitan ke Data Lulusan</div>
                        @if($linkedLulusan)
                            <div class="font-weight-bold text-success">{{ $linkedLulusan->nama_universitas ?: '-' }}</div>
                            <div class="small text-muted">{{ $linkedLulusan->program_studi ?: '-' }}</div>
                        @else
                            <div class="font-weight-bold text-muted">Belum terhubung</div>
                            <div class="small text-muted">Akan otomatis terhubung saat Anda mengisi data lulusan dengan jalur SNBP.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Simpan Nomor Pendaftaran
            </button>
        </div>
    </form>
</div>

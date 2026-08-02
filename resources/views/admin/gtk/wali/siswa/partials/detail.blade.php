<div class="row gtk-wali-student-detail">
    <div class="col-lg-4 mb-3 mb-lg-0">
        <div class="text-center">
            @if($siswa->foto_profile)
                <img src="{{ asset('storage/'.$siswa->foto_profile) }}" alt="Foto {{ $siswa->nama_lengkap }}" class="img-circle elevation-1 mb-3" style="width:120px;height:120px;object-fit:cover;">
            @else
                <span class="d-inline-flex align-items-center justify-content-center rounded-circle text-white mb-3" style="width:120px;height:120px;background:#4F46E5;font-size:2.5rem;font-weight:600;">
                    {{ strtoupper(substr($siswa->nama_lengkap, 0, 1)) }}
                </span>
            @endif
            <h5 class="font-weight-bold mb-1">{{ $siswa->nama_lengkap }}</h5>
            <p class="text-muted mb-2">{{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
            <div>
                <span class="badge {{ $siswa->data_diri_completed ? 'badge-success' : 'badge-danger' }}">Data Diri {{ $siswa->data_diri_completed ? 'Lengkap' : 'Belum' }}</span>
                <span class="badge {{ $siswa->data_ortu_completed ? 'badge-success' : 'badge-danger' }}">Data Ortu {{ $siswa->data_ortu_completed ? 'Lengkap' : 'Belum' }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <h6 class="text-primary font-weight-bold border-bottom pb-2"><i class="fas fa-id-card mr-1"></i> Identitas</h6>
        <dl class="row mb-4">
            <dt class="col-sm-4">NISN</dt><dd class="col-sm-8">{{ $siswa->nisn ?: '—' }}</dd>
            <dt class="col-sm-4">NIK</dt><dd class="col-sm-8">{{ $siswa->nik ?: '—' }}</dd>
            <dt class="col-sm-4">Tempat, Tgl Lahir</dt><dd class="col-sm-8">{{ $siswa->tempat_lahir ?: '—' }}{{ $siswa->tanggal_lahir ? ', '.$siswa->tanggal_lahir->translatedFormat('d F Y') : '' }}</dd>
            <dt class="col-sm-4">Agama</dt><dd class="col-sm-8">{{ $siswa->agama ?: '—' }}</dd>
            <dt class="col-sm-4">No. HP</dt>
            <dd class="col-sm-8">
                @if($siswa->nomor_hp)
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $siswa->nomor_hp) }}" data-no-overlay title="Hubungi {{ $siswa->nama_lengkap }}"><i class="fas fa-phone-alt mr-1"></i>{{ $siswa->nomor_hp }}</a>
                @else
                    —
                @endif
            </dd>
            <dt class="col-sm-4">Alamat</dt><dd class="col-sm-8">{{ $siswa->getAlamatLengkapSiswa() ?: '—' }}</dd>
            <dt class="col-sm-4">Asal Sekolah</dt><dd class="col-sm-8">{{ optional($siswa->sekolahAsal)->nama ?? ($siswa->npsn_asal_sekolah ? 'NPSN '.$siswa->npsn_asal_sekolah : '—') }}</dd>
        </dl>

        <h6 class="text-primary font-weight-bold border-bottom pb-2"><i class="fas fa-users mr-1"></i> Orang Tua</h6>
        @if($siswa->ortu)
            <dl class="row mb-4">
                <dt class="col-sm-4">Nama Ayah</dt><dd class="col-sm-8">{{ $siswa->ortu->nama_ayah ?: '—' }}</dd>
                <dt class="col-sm-4">No. HP Ayah</dt>
                <dd class="col-sm-8">
                    @if($siswa->ortu->hp_ayah)
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $siswa->ortu->hp_ayah) }}" data-no-overlay title="Hubungi ayah {{ $siswa->nama_lengkap }}"><i class="fas fa-phone-alt mr-1"></i>{{ $siswa->ortu->hp_ayah }}</a>
                    @else
                        —
                    @endif
                </dd>
                <dt class="col-sm-4">Nama Ibu</dt><dd class="col-sm-8">{{ $siswa->ortu->nama_ibu ?: '—' }}</dd>
                <dt class="col-sm-4">No. HP Ibu</dt>
                <dd class="col-sm-8">
                    @if($siswa->ortu->hp_ibu)
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $siswa->ortu->hp_ibu) }}" data-no-overlay title="Hubungi ibu {{ $siswa->nama_lengkap }}"><i class="fas fa-phone-alt mr-1"></i>{{ $siswa->ortu->hp_ibu }}</a>
                    @else
                        —
                    @endif
                </dd>
            </dl>
        @else
            <p class="text-muted">Data orang tua belum tersedia.</p>
        @endif

        <h6 class="text-primary font-weight-bold border-bottom pb-2"><i class="fas fa-sticky-note mr-1"></i> Catatan Terakhir</h6>
        @forelse($catatan as $c)
            <div class="pb-2 mb-2 border-bottom">
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">{{ $c->tanggal->translatedFormat('d M Y') }}</span>
                    @if($c->kategori)<span class="badge badge-info">{{ $c->kategori_label }}</span>@endif
                </div>
                <div>{{ $c->catatan }}</div>
            </div>
        @empty
            <p class="text-muted mb-0">Belum ada catatan.</p>
        @endforelse
    </div>
</div>

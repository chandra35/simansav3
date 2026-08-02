@extends('adminlte::page')

@section('title', 'Detail Siswa — '.$siswa->nama_lengkap)

@section('content_header')
    <div class="simansa-hero">
        <div class="simansa-hero__main">
            <div class="simansa-hero__eyebrow"><i class="fas fa-user-graduate"></i> Kelas Saya</div>
            <h1 class="simansa-hero__title">{{ $siswa->nama_lengkap }}</h1>
            <p class="simansa-hero__subtitle">NISN {{ $siswa->nisn ?: '—' }} · {{ optional($siswa->kelasAktif->first())->nama_kelas ?? 'Tanpa rombel' }}</p>
        </div>
        <div class="simansa-hero__side">
            <a href="{{ url()->previous() }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="card simansa-management-card">
                <div class="card-body text-center">
                    @if($siswa->foto_profile)
                        <img src="{{ asset('storage/'.$siswa->foto_profile) }}" alt="foto" class="img-circle elevation-1 mb-3" style="width:120px;height:120px;object-fit:cover;">
                    @else
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle text-white mb-3" style="width:120px;height:120px;background:#4F46E5;font-size:2.5rem;font-weight:600;">
                            {{ strtoupper(substr($siswa->nama_lengkap, 0, 1)) }}
                        </span>
                    @endif
                    <h5 class="font-weight-600 mb-1">{{ $siswa->nama_lengkap }}</h5>
                    <p class="text-muted mb-2">{{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
                    <div>
                        <span class="badge {{ $siswa->data_diri_completed ? 'badge-success' : 'badge-danger' }}">Data Diri {{ $siswa->data_diri_completed ? 'Lengkap' : 'Belum' }}</span>
                        <span class="badge {{ $siswa->data_ortu_completed ? 'badge-success' : 'badge-danger' }}">Data Ortu {{ $siswa->data_ortu_completed ? 'Lengkap' : 'Belum' }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card simansa-management-card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-id-card"></i> Identitas</h3></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">NISN</dt><dd class="col-sm-8">{{ $siswa->nisn ?: '—' }}</dd>
                        <dt class="col-sm-4">NIK</dt><dd class="col-sm-8">{{ $siswa->nik ?: '—' }}</dd>
                        <dt class="col-sm-4">Tempat, Tgl Lahir</dt><dd class="col-sm-8">{{ $siswa->tempat_lahir ?: '—' }}{{ $siswa->tanggal_lahir ? ', '.$siswa->tanggal_lahir->translatedFormat('d F Y') : '' }}</dd>
                        <dt class="col-sm-4">Agama</dt><dd class="col-sm-8">{{ $siswa->agama ?: '—' }}</dd>
                        <dt class="col-sm-4">Anak ke / Saudara</dt><dd class="col-sm-8">{{ $siswa->anak_ke ?: '—' }} / {{ $siswa->jumlah_saudara ?: '—' }}</dd>
                        <dt class="col-sm-4">No. HP</dt>
                        <dd class="col-sm-8">
                            @if($siswa->nomor_hp)
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $siswa->nomor_hp) }}" title="Hubungi {{ $siswa->nama_lengkap }}"><i class="fas fa-phone-alt mr-1"></i>{{ $siswa->nomor_hp }}</a>
                            @else
                                —
                            @endif
                        </dd>
                        <dt class="col-sm-4">Alamat</dt><dd class="col-sm-8">{{ $siswa->getAlamatLengkapSiswa() ?: '—' }}</dd>
                        <dt class="col-sm-4">Asal Sekolah</dt><dd class="col-sm-8">{{ optional($siswa->sekolahAsal)->nama ?? ($siswa->npsn_asal_sekolah ? 'NPSN '.$siswa->npsn_asal_sekolah : '—') }}</dd>
                        <dt class="col-sm-4">Akun Login</dt><dd class="col-sm-8">{{ optional($siswa->user)->email ?? '—' }}</dd>
                    </dl>
                </div>
            </div>

            @if($siswa->ortu)
            <div class="card simansa-management-card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-users"></i> Data Orang Tua / Wali</h3></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Nama Ayah</dt><dd class="col-sm-8">{{ $siswa->ortu->nama_ayah ?: '—' }}</dd>
                        <dt class="col-sm-4">Nama Ibu</dt><dd class="col-sm-8">{{ $siswa->ortu->nama_ibu ?: '—' }}</dd>
                        <dt class="col-sm-4">No. HP Ayah</dt>
                        <dd class="col-sm-8">
                            @if($siswa->ortu->hp_ayah)
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $siswa->ortu->hp_ayah) }}" title="Hubungi ayah {{ $siswa->nama_lengkap }}"><i class="fas fa-phone-alt mr-1"></i>{{ $siswa->ortu->hp_ayah }}</a>
                            @else
                                —
                            @endif
                        </dd>
                        <dt class="col-sm-4">No. HP Ibu</dt>
                        <dd class="col-sm-8">
                            @if($siswa->ortu->hp_ibu)
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $siswa->ortu->hp_ibu) }}" title="Hubungi ibu {{ $siswa->nama_lengkap }}"><i class="fas fa-phone-alt mr-1"></i>{{ $siswa->ortu->hp_ibu }}</a>
                            @else
                                —
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>
            @endif

            <div class="card simansa-management-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0"><i class="fas fa-sticky-note"></i> Catatan Wali Kelas Terakhir</h3>
                    <a href="{{ route('admin.gtk.wali.catatan.index', ['siswa_id' => $siswa->id]) }}" class="btn btn-sm btn-outline-secondary">Kelola Catatan</a>
                </div>
                <div class="card-body">
                    @forelse($catatan as $c)
                        <div class="mb-2 pb-2 border-bottom">
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
        </div>
    </div>
@stop

@extends('adminlte::page')

@section('title', 'Riwayat Alumni')

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center">
        <div><h1 class="mb-1"><i class="fas fa-history text-primary mr-2"></i>Riwayat Alumni</h1><p class="text-muted mb-0">Rekam jejak akademik dan data setelah kelulusan.</p></div>
        <a href="{{ route('admin.alumni.index', ['tahun_pelajaran_id' => $graduation->tahun_pelajaran_id]) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i> Kembali ke Alumni</a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="card alumni-profile-card shadow-sm">
                <div class="card-body text-center">
                    <img src="{{ $siswa->foto_profile_url }}" class="alumni-profile-photo" alt="Foto {{ $siswa->nama_lengkap }}">
                    <h3 class="mt-3 mb-1">{{ $siswa->nama_lengkap }}</h3>
                    <p class="text-muted mb-2">NISN {{ $siswa->nisn ?: '-' }}</p>
                    <span class="badge badge-primary px-3 py-2"><i class="fas fa-graduation-cap mr-1"></i> Alumni {{ $graduation->tahunPelajaran?->nama ?: '-' }}</span>
                </div>
                <div class="list-group list-group-flush alumni-profile-list">
                    <div class="list-group-item"><span>NIS Lokal</span><strong>{{ $siswa->nis_lokal ?: '-' }}</strong></div>
                    <div class="list-group-item"><span>Jenis Kelamin</span><strong>{{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</strong></div>
                    <div class="list-group-item"><span>Tempat, Tanggal Lahir</span><strong>{{ $siswa->tempat_lahir ?: '-' }}{{ $siswa->tanggal_lahir ? ', '.$siswa->tanggal_lahir->translatedFormat('d M Y') : '' }}</strong></div>
                    <div class="list-group-item"><span>Nomor HP</span><strong>{{ $siswa->nomor_hp ?: '-' }}</strong></div>
                    <div class="list-group-item"><span>Email</span><strong>{{ $siswa->user?->email ?: '-' }}</strong></div>
                    <div class="list-group-item"><span>Sekolah Asal</span><strong>{{ $siswa->sekolahAsal?->nama ?: '-' }}</strong></div>
                    <div class="list-group-item"><span>Orang Tua</span><strong>{{ collect([$siswa->ortu?->nama_ayah, $siswa->ortu?->nama_ibu])->filter()->implode(' / ') ?: '-' }}</strong></div>
                </div>
            </div>

            <div class="card card-outline card-success shadow-sm">
                <div class="card-header"><h3 class="card-title font-weight-bold"><i class="fas fa-award mr-2"></i>Kelulusan</h3></div>
                <div class="card-body">
                    <dl class="row mb-0 alumni-dl">
                        <dt class="col-5">Tahun</dt><dd class="col-7">{{ $graduation->tahunPelajaran?->nama ?: '-' }}</dd>
                        <dt class="col-5">Kelas akhir</dt><dd class="col-7">{{ $graduation->kelas?->nama_kelas ?: '-' }}</dd>
                        <dt class="col-5">Jurusan</dt><dd class="col-7">{{ $graduation->kelas?->jurusan?->nama_jurusan ?: '-' }}</dd>
                        <dt class="col-5">Tanggal</dt><dd class="col-7">{{ $graduation->tanggal_keluar?->translatedFormat('d F Y') ?: '-' }}</dd>
                        <dt class="col-5">Catatan</dt><dd class="col-7">{{ $graduation->catatan_perpindahan ?: 'Lulus' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header border-0"><h3 class="card-title font-weight-bold"><i class="fas fa-stream text-primary mr-2"></i>Histori Kelas</h3></div>
                <div class="card-body">
                    <div class="alumni-timeline">
                        @forelse($siswa->siswaKelasRecords as $record)
                            @php
                                $statusMeta = [
                                    'aktif' => ['Aktif', 'primary', 'fa-book-open'],
                                    'naik_kelas' => ['Naik Kelas', 'info', 'fa-level-up-alt'],
                                    'tinggal_kelas' => ['Tinggal Kelas', 'warning', 'fa-redo'],
                                    'lulus' => ['Lulus', 'success', 'fa-graduation-cap'],
                                    'keluar' => ['Keluar', 'danger', 'fa-sign-out-alt'],
                                ][$record->status] ?? [ucwords(str_replace('_', ' ', $record->status)), 'secondary', 'fa-circle'];
                            @endphp
                            <div class="alumni-timeline-item">
                                <div class="alumni-timeline-icon bg-{{ $statusMeta[1] }}"><i class="fas {{ $statusMeta[2] }}"></i></div>
                                <div class="alumni-timeline-content">
                                    <div class="d-flex flex-wrap justify-content-between"><div><h5 class="mb-1">{{ $record->kelas?->nama_kelas ?: 'Tingkat '.$record->tingkat.' (tanpa rombel)' }}</h5><span class="badge badge-{{ $statusMeta[1] }}">{{ $statusMeta[0] }}</span></div><strong class="text-primary">{{ $record->tahunPelajaran?->nama ?: '-' }}</strong></div>
                                    <div class="text-muted small mt-2"><i class="far fa-calendar-alt mr-1"></i>{{ $record->tanggal_masuk?->translatedFormat('d M Y') ?: '?' }} — {{ $record->tanggal_keluar?->translatedFormat('d M Y') ?: 'sekarang' }}</div>
                                    @if($record->kelas?->jurusan)<div class="small mt-1"><i class="fas fa-bookmark text-muted mr-1"></i>{{ $record->kelas->jurusan->nama_jurusan }}</div>@endif
                                    @if($record->catatan_perpindahan)<p class="mb-0 mt-2 alumni-note">{{ $record->catatan_perpindahan }}</p>@endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">Histori kelas belum tersedia.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="card card-outline card-info shadow-sm">
                <div class="card-header"><h3 class="card-title font-weight-bold"><i class="fas fa-university mr-2"></i>Riwayat Setelah Lulus</h3></div>
                <div class="card-body">
                    @forelse($siswa->dataLulusan as $tujuan)
                        <div class="alumni-destination {{ !$loop->last ? 'border-bottom pb-3 mb-3' : '' }}">
                            <div class="d-flex flex-wrap justify-content-between"><h5 class="mb-1">{{ $tujuan->nama_universitas ?: $tujuan->nama_universitas_manual ?: 'Tujuan belum dicatat' }}</h5><span class="badge badge-info">{{ $tujuan->jalur_masuk ?: 'Jalur belum dicatat' }}</span></div>
                            <div class="text-muted">{{ $tujuan->program_studi ?: $tujuan->program_studi_manual ?: $tujuan->jurusan_fakultas ?: '-' }}</div>
                            <small>{{ $tujuan->tahunPelajaran?->nama ?: '-' }}@if($tujuan->keterangan) · {{ $tujuan->keterangan }}@endif</small>
                        </div>
                    @empty
                        <div class="text-center py-4"><i class="fas fa-info-circle text-info fa-2x mb-2 d-block"></i><strong>Data setelah lulus belum diisi</strong><div class="text-muted">Informasi kampus atau tujuan lanjutan alumni belum tersedia.</div></div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .alumni-profile-card{border-radius:.8rem;overflow:hidden}.alumni-profile-card .card-body{background:linear-gradient(145deg,#eff6ff,#fff)}.alumni-profile-photo{width:112px;height:112px;border-radius:50%;object-fit:cover;border:4px solid #fff;box-shadow:0 5px 18px rgba(15,23,42,.16)}.alumni-profile-list .list-group-item{display:flex;justify-content:space-between;gap:1rem;font-size:.85rem}.alumni-profile-list span{color:#64748b}.alumni-profile-list strong{text-align:right;overflow-wrap:anywhere}.alumni-dl dt{color:#64748b;font-weight:600}.alumni-dl dd{font-weight:600}.alumni-timeline{position:relative;padding-left:2.8rem}.alumni-timeline:before{content:"";position:absolute;left:17px;top:12px;bottom:12px;width:2px;background:#dbeafe}.alumni-timeline-item{position:relative;padding-bottom:1.35rem}.alumni-timeline-item:last-child{padding-bottom:0}.alumni-timeline-icon{position:absolute;left:-2.8rem;top:0;width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;border:3px solid #fff;box-shadow:0 2px 8px rgba(15,23,42,.18)}.alumni-timeline-content{border:1px solid #e5e7eb;border-radius:.7rem;padding:1rem;background:#fff}.alumni-note{background:#f8fafc;border-left:3px solid #93c5fd;padding:.55rem .7rem;color:#475569;border-radius:.25rem}.alumni-destination h5{color:#1e3a8a}@media(max-width:575.98px){.alumni-profile-list .list-group-item{display:block}.alumni-profile-list strong{display:block;text-align:left;margin-top:.2rem}}
</style>
@stop

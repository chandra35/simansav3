@extends('adminlte::page')

@section('title', 'Pengumuman Kelulusan')

@section('content_header')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h1 class="mb-1">Pengumuman Kelulusan Kelas 12</h1>
            <p class="text-muted mb-0">Kelola hasil kelulusan siswa kelas 12 untuk tahun ajaran aktif.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <span class="badge badge-light px-3 py-2">
                <i class="fas fa-calendar-alt mr-1"></i> {{ $tahunAktif->nama }}
            </span>
            <span class="badge badge-{{ $setting->graduation_announcement_enabled ? 'success' : 'secondary' }} px-3 py-2">
                <i class="fas fa-bullhorn mr-1"></i>
                {{ $setting->graduation_announcement_enabled ? 'Sudah Dibuka' : 'Masih Ditutup' }}
            </span>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm simansa-surface-card">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                        <div>
                            <div class="text-uppercase text-muted small font-weight-bold mb-2">Publikasi Kelulusan</div>
                            <h3 class="mb-2">Kontrol Akses Siswa Kelas 12</h3>
                            <p class="text-muted mb-0">
                                Saat fitur dibuka, menu pengumuman hanya muncul untuk siswa kelas 12 pada tahun ajaran aktif.
                            </p>
                        </div>
                        <form action="{{ route('admin.kelulusan-pengumuman.publish') }}" method="POST" class="d-flex align-items-center gap-2">
                            @csrf
                            <input type="hidden" name="graduation_announcement_enabled" value="{{ $setting->graduation_announcement_enabled ? 0 : 1 }}">
                            <button type="submit" class="btn {{ $setting->graduation_announcement_enabled ? 'btn-outline-secondary' : 'btn-success' }}">
                                <i class="fas {{ $setting->graduation_announcement_enabled ? 'fa-eye-slash' : 'fa-eye' }} mr-1"></i>
                                {{ $setting->graduation_announcement_enabled ? 'Tutup Pengumuman' : 'Buka Pengumuman' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm simansa-surface-card h-100">
                <div class="card-body p-4">
                    <div class="text-uppercase text-muted small font-weight-bold mb-2">Catatan Custom Menu</div>
                    <h4 class="mb-2">Bisa dipakai, tapi bukan inti fitur ini</h4>
                    <p class="text-muted mb-2">
                        Custom Menu cocok untuk surat pengantar, video sambutan, atau pesan tambahan yang ditugaskan ke siswa tertentu.
                    </p>
                    <p class="text-muted mb-0">
                        Untuk hasil kelulusan, sistem ini sengaja dibuat khusus agar aksesnya otomatis mengikuti kelas 12, tahun ajaran aktif, dan toggle publish dari admin.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-gradient-success shadow-sm">
                <div class="inner">
                    <h3>{{ $stats['lulus'] }}</h3>
                    <p>Lulus</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-gradient-warning shadow-sm">
                <div class="inner">
                    <h3>{{ $stats['lulus_bersyarat'] }}</h3>
                    <p>Lulus Bersyarat</p>
                </div>
                <div class="icon"><i class="fas fa-exclamation-circle"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-gradient-danger shadow-sm">
                <div class="inner">
                    <h3>{{ $stats['tidak_lulus'] }}</h3>
                    <p>Tidak Lulus</p>
                </div>
                <div class="icon"><i class="fas fa-times-circle"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-gradient-info shadow-sm">
                <div class="inner">
                    <h3>{{ $stats['total'] }}</h3>
                    <p>Total Siswa Kelas 12</p>
                </div>
                <div class="icon"><i class="fas fa-user-graduate"></i></div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header border-0">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h3 class="card-title font-weight-bold mb-1">Data Pengumuman Kelulusan</h3>
                    <div class="text-muted small">Isi status per siswa lalu simpan. Catatan hanya wajib untuk status Lulus Bersyarat.</div>
                </div>
                <form action="{{ route('admin.kelulusan-pengumuman.index') }}" method="GET" class="d-flex flex-wrap align-items-center gap-2">
                    <select name="kelas_id" class="form-control">
                        <option value="">Semua Rombel Kelas 12</option>
                        @foreach($kelasList as $kelas)
                            <option value="{{ $kelas->id }}" @selected($selectedKelasId === $kelas->id)>{{ $kelas->nama_kelas }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter mr-1"></i> Terapkan
                    </button>
                </form>
            </div>
        </div>
        <div class="card-body p-0">
            <form action="{{ route('admin.kelulusan-pengumuman.save') }}" method="POST">
                @csrf
                <input type="hidden" name="kelas_filter" value="{{ $selectedKelasId }}">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th class="pl-4">Siswa</th>
                                <th>Rombel</th>
                                <th style="width: 220px;">Status</th>
                                <th>Catatan</th>
                                <th class="pr-4">Dibuka</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($students as $row)
                                @php($item = $announcementMap->get($row->siswa->id))
                                <tr>
                                    <td class="pl-4">
                                        <div class="font-weight-bold">{{ $row->siswa->nama_lengkap }}</div>
                                        <div class="text-muted small">{{ $row->siswa->nisn }} @if($row->siswa->user?->username) | {{ $row->siswa->user->username }} @endif</div>
                                    </td>
                                    <td>
                                        <span class="badge badge-light">{{ $row->kelas->nama_kelas }}</span>
                                    </td>
                                    <td>
                                        <select name="statuses[{{ $row->siswa->id }}]" class="form-control">
                                            <option value="">Belum Ditentukan</option>
                                            @foreach($statusOptions as $value => $label)
                                                <option value="{{ $value }}" @selected(optional($item)->status === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <textarea name="notes[{{ $row->siswa->id }}]" rows="2" class="form-control" placeholder="Catatan tambahan, khususnya untuk Lulus Bersyarat">{{ old("notes.{$row->siswa->id}", optional($item)->catatan) }}</textarea>
                                    </td>
                                    <td class="pr-4">
                                        @if(optional($item)->opened_at)
                                            <span class="badge badge-success">Sudah</span>
                                            <div class="text-muted small mt-1">{{ $item->opened_at->format('d M Y H:i') }}</div>
                                        @else
                                            <span class="badge badge-secondary">Belum</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">
                                        Belum ada siswa kelas 12 pada tahun ajaran aktif.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($students->isNotEmpty())
                    <div class="card-footer bg-white border-0 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save mr-1"></i> Simpan Pengumuman
                        </button>
                    </div>
                @endif
            </form>
        </div>
    </div>
@stop

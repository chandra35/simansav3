@extends('adminlte::page')

@section('title', 'Profil Alumni')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-1"><i class="fas fa-user-graduate text-primary mr-2"></i>{{ $alumni->nama_lengkap }}</h1>
            <p class="text-muted mb-0">Profil Bank Data Alumni</p>
        </div>
        <div class="d-flex align-items-center">
            @if($canExportLegger)
                <a href="{{ route('admin.alumni.export-legger', ['alumni' => $alumni, 'include_semester_6' => 1]) }}" class="btn btn-success mr-2">
                    <i class="fas fa-file-excel mr-1"></i>Export Leger
                </a>
            @endif
            <a href="{{ route('admin.alumni.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i>Kembali</a>
        </div>
    </div>
@stop

@section('content')
    @php
        $tracking = $alumni->tracking_lulusan;
        $statusOptions = [
            'belum_terdata' => 'Belum terdata', 'kuliah' => 'Kuliah', 'bekerja' => 'Bekerja',
            'wirausaha' => 'Wirausaha', 'pesantren' => 'Pesantren', 'lainnya' => 'Lainnya',
        ];
        $verificationOptions = [
            'belum_diverifikasi' => 'Belum diverifikasi', 'terverifikasi' => 'Terverifikasi', 'perlu_tinjau' => 'Perlu tinjau',
        ];
    @endphp

    <div class="row">
        <div class="col-lg-4">
            <div class="card card-outline card-primary">
                <div class="card-body">
                    <span class="badge badge-primary mb-2">{{ $alumni->angkatan ?: ($alumni->tahun_lulus ?: 'Angkatan belum dicatat') }}</span>
                    <h3>{{ $alumni->nama_lengkap }}</h3>
                    <p class="text-muted mb-3">{{ $alumni->siswa ? 'Terhubung ke riwayat Siswa SIMANSA' : 'Arsip historis mandiri' }}</p>
                    <dl class="row small mb-0">
                        <dt class="col-5">NISN</dt><dd class="col-7">{{ $alumni->nisn ?: '-' }}</dd>
                        <dt class="col-5">NIK</dt><dd class="col-7">{{ $alumni->nik ?: '-' }}</dd>
                        <dt class="col-5">Kontak</dt><dd class="col-7">{{ $alumni->nomor_hp ?: '-' }}</dd>
                        <dt class="col-5">Email</dt><dd class="col-7">{{ $alumni->email ?: '-' }}</dd>
                        <dt class="col-5">Domisili</dt><dd class="col-7">{{ collect([$alumni->kabupaten_kota, $alumni->provinsi])->filter()->implode(', ') ?: '-' }}</dd>
                    </dl>
                </div>
            </div>

            @if($alumni->siswa)
                <div class="card card-outline card-info">
                    <div class="card-header"><h3 class="card-title">Riwayat SIMANSA</h3></div>
                    <div class="card-body">
                        <p class="mb-1"><strong>Sekolah asal:</strong> {{ $alumni->siswa->sekolahAsal?->nama ?: '-' }}</p>
                        <p class="mb-0"><strong>Orang tua:</strong> {{ collect([$alumni->siswa->ortu?->nama_ayah, $alumni->siswa->ortu?->nama_ibu])->filter()->implode(' / ') ?: '-' }}</p>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-8">
            @if($tracking)
                <div class="card card-outline card-info shadow-sm">
                    <div class="card-header border-0">
                        <h3 class="card-title font-weight-bold"><i class="fas fa-history mr-2"></i>Histori Tracking PTN / Studi Lanjut</h3>
                        <div class="card-tools"><span class="badge badge-info">Data historis</span></div>
                    </div>
                    <div class="card-body pt-0">
                        <p class="text-muted small mb-3">Data ini adalah rekam hasil tracking lulusan dan tidak menimpa pilihan/alamat studi terkini pada profil alumni.</p>
                        <div class="row small">
                            <div class="col-md-4 mb-2"><span class="text-muted d-block">Jalur</span><strong>{{ $tracking['jalur_masuk'] ?: '-' }}</strong></div>
                            <div class="col-md-4 mb-2"><span class="text-muted d-block">Hasil checker</span><strong>{{ $tracking['status_checker'] ? ucfirst(str_replace('_', ' ', $tracking['status_checker'])) : 'Belum ada' }}</strong></div>
                            <div class="col-md-4 mb-2"><span class="text-muted d-block">Diperbarui</span><strong>{{ $alumni->tracking_lulusan_updated_at?->format('d/m/Y H:i') ?: '-' }}</strong></div>
                            @if(!empty($tracking['nomor_pendaftaran_snbp']))<div class="col-md-4 mb-2"><span class="text-muted d-block">Nomor SNBP</span><strong>{{ $tracking['nomor_pendaftaran_snbp'] }}</strong></div>@endif
                            @if(!empty($tracking['nomor_pendaftaran_span_ptkin']))<div class="col-md-4 mb-2"><span class="text-muted d-block">Nomor SPAN-PTKIN</span><strong>{{ $tracking['nomor_pendaftaran_span_ptkin'] }}</strong></div>@endif
                            <div class="col-md-6 mb-2"><span class="text-muted d-block">Kampus tercatat</span><strong>{{ $tracking['nama_universitas'] ?: '-' }}</strong></div>
                            <div class="col-md-6 mb-2"><span class="text-muted d-block">Program studi tercatat</span><strong>{{ $tracking['program_studi'] ?: '-' }}</strong></div>
                            @if(!empty($tracking['jurusan_fakultas']))<div class="col-md-6 mb-2"><span class="text-muted d-block">Fakultas / jurusan</span><strong>{{ $tracking['jurusan_fakultas'] }}</strong></div>@endif
                            @if(!empty($tracking['keterangan']))<div class="col-md-6 mb-2"><span class="text-muted d-block">Keterangan tracker</span><strong>{{ $tracking['keterangan'] }}</strong></div>@endif
                        </div>
                    </div>
                </div>
            @endif

            <div class="card shadow-sm">
                <form method="post" action="{{ route('admin.alumni.update', $alumni) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">Kondisi Terkini Alumni</h3>
                        @can('edit-siswa')
                            <div class="card-tools"><button class="btn btn-sm btn-primary" type="submit"><i class="fas fa-save mr-1"></i>Simpan</button></div>
                        @endcan
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">Bagian ini dapat diperbarui sesuai kondisi alumni saat ini. Nilainya tidak akan ditimpa oleh sinkronisasi tracking PTN.</p>
                        <fieldset @cannot('edit-siswa') disabled @endcannot>
                            <div class="form-row">
                                <div class="form-group col-md-4"><label>Angkatan</label><input class="form-control" name="angkatan" value="{{ $alumni->angkatan }}"></div>
                                <div class="form-group col-md-3"><label>Tahun lulus</label><input class="form-control" type="number" name="tahun_lulus" value="{{ $alumni->tahun_lulus }}"></div>
                                <div class="form-group col-md-5"><label>Status setelah lulus</label><select class="form-control" name="status_setelah_lulus">@foreach($statusOptions as $key => $label)<option value="{{ $key }}" {{ $alumni->status_setelah_lulus === $key ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></div>
                                <div class="form-group col-md-6"><label>Institusi pendidikan lanjutan</label><input class="form-control" name="institusi_lanjutan" value="{{ $alumni->institusi_lanjutan }}" placeholder="Nama kampus/pesantren"></div>
                                <div class="form-group col-md-6"><label>Program studi</label><input class="form-control" name="program_studi" value="{{ $alumni->program_studi }}"></div>
                                <div class="form-group col-md-6"><label>Pekerjaan / usaha</label><input class="form-control" name="pekerjaan" value="{{ $alumni->pekerjaan }}"></div>
                                <div class="form-group col-md-6"><label>Instansi / tempat kerja</label><input class="form-control" name="instansi" value="{{ $alumni->instansi }}"></div>
                                <div class="form-group col-md-6"><label>Nomor HP</label><input class="form-control" name="nomor_hp" value="{{ $alumni->nomor_hp }}"></div>
                                <div class="form-group col-md-6"><label>Email</label><input class="form-control" name="email" value="{{ $alumni->email }}"></div>
                                <div class="form-group col-md-6"><label>Kabupaten/Kota</label><input class="form-control" name="kabupaten_kota" value="{{ $alumni->kabupaten_kota }}"></div>
                                <div class="form-group col-md-6"><label>Provinsi</label><input class="form-control" name="provinsi" value="{{ $alumni->provinsi }}"></div>
                                <div class="form-group col-md-6"><label>Status verifikasi</label><select class="form-control" name="status_verifikasi">@foreach($verificationOptions as $key => $label)<option value="{{ $key }}" {{ $alumni->status_verifikasi === $key ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></div>
                                <div class="form-group col-12"><label>Catatan</label><textarea class="form-control" rows="3" name="catatan">{{ $alumni->catatan }}</textarea></div>
                            </div>
                        </fieldset>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

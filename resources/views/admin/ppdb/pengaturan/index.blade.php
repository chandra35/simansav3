@extends('adminlte::page')

@section('title', 'Pengaturan PPDB')

@section('content_header')
    <h1><i class="fas fa-cogs mr-2"></i>Pengaturan PPDB</h1>
@stop

@section('content')
<div class="row">
    <div class="col-lg-8">
        <form action="{{ route('admin.settings.ppdb.update') }}" method="POST">
            @csrf
            @method('PUT')
            
            @if(session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <!-- Periode Pendaftaran -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-calendar-alt mr-2"></i>Periode Pendaftaran</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="tahun_pelajaran_id">Tahun Pelajaran</label>
                        <select name="tahun_pelajaran_id" id="tahun_pelajaran_id" class="form-control">
                            <option value="">-- Pilih Tahun Pelajaran --</option>
                            @foreach($tahunPelajaran as $tp)
                                <option value="{{ $tp->id }}" {{ old('tahun_pelajaran_id', $pengaturan->tahun_pelajaran_id ?? '') == $tp->id ? 'selected' : '' }}>
                                    {{ $tp->tahun_pelajaran }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tanggal_buka">Tanggal Buka Pendaftaran</label>
                                <input type="date" name="tanggal_buka" id="tanggal_buka" class="form-control" 
                                       value="{{ old('tanggal_buka', $pengaturan->tanggal_buka ? $pengaturan->tanggal_buka->format('Y-m-d') : '') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tanggal_tutup">Tanggal Tutup Pendaftaran</label>
                                <input type="date" name="tanggal_tutup" id="tanggal_tutup" class="form-control" 
                                       value="{{ old('tanggal_tutup', $pengaturan->tanggal_tutup ? $pengaturan->tanggal_tutup->format('Y-m-d') : '') }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="tanggal_pengumuman">Tanggal Pengumuman</label>
                                <input type="date" name="tanggal_pengumuman" id="tanggal_pengumuman" class="form-control" 
                                       value="{{ old('tanggal_pengumuman', $pengaturan->tanggal_pengumuman ? $pengaturan->tanggal_pengumuman->format('Y-m-d') : '') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="tanggal_daftar_ulang_mulai">Daftar Ulang Mulai</label>
                                <input type="date" name="tanggal_daftar_ulang_mulai" id="tanggal_daftar_ulang_mulai" class="form-control" 
                                       value="{{ old('tanggal_daftar_ulang_mulai', $pengaturan->tanggal_daftar_ulang_mulai ? $pengaturan->tanggal_daftar_ulang_mulai->format('Y-m-d') : '') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="tanggal_daftar_ulang_selesai">Daftar Ulang Selesai</label>
                                <input type="date" name="tanggal_daftar_ulang_selesai" id="tanggal_daftar_ulang_selesai" class="form-control" 
                                       value="{{ old('tanggal_daftar_ulang_selesai', $pengaturan->tanggal_daftar_ulang_selesai ? $pengaturan->tanggal_daftar_ulang_selesai->format('Y-m-d') : '') }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="biaya_pendaftaran">Biaya Pendaftaran (Rp)</label>
                                <input type="number" name="biaya_pendaftaran" id="biaya_pendaftaran" class="form-control" 
                                       value="{{ old('biaya_pendaftaran', $pengaturan->biaya_pendaftaran ?? 0) }}" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="rekening_pembayaran">Rekening Pembayaran</label>
                                <input type="text" name="rekening_pembayaran" id="rekening_pembayaran" class="form-control" 
                                       value="{{ old('rekening_pembayaran', $pengaturan->rekening_pembayaran ?? '') }}"
                                       placeholder="No. Rekening - Nama Bank - Atas Nama">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Jalur Pendaftaran -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-route mr-2"></i>Jalur Pendaftaran</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">Aktifkan jalur pendaftaran yang tersedia.</p>
                    
                    @foreach($defaultJalur as $key => $jalur)
                    @php
                        $isAktif = isset($pengaturan->jalur_tersedia[$key]) 
                            ? $pengaturan->jalur_tersedia[$key]['aktif'] 
                            : $jalur['aktif'];
                    @endphp
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" 
                                   id="jalur_{{ $key }}" name="jalur_{{ $key }}" value="1"
                                   {{ $isAktif ? 'checked' : '' }}>
                            <label class="custom-control-label" for="jalur_{{ $key }}">
                                <strong>{{ $jalur['nama'] }}</strong>
                                <small class="text-muted d-block">{{ $jalur['deskripsi'] }}</small>
                            </label>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Informasi -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Informasi Pendaftaran</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="persyaratan">Persyaratan Pendaftaran</label>
                        <textarea name="persyaratan" id="persyaratan" class="form-control" rows="5"
                                  placeholder="Daftar persyaratan pendaftaran...">{{ old('persyaratan', $pengaturan->persyaratan ?? '') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="alur_pendaftaran">Alur Pendaftaran</label>
                        <textarea name="alur_pendaftaran" id="alur_pendaftaran" class="form-control" rows="5"
                                  placeholder="Langkah-langkah pendaftaran...">{{ old('alur_pendaftaran', $pengaturan->alur_pendaftaran ?? '') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="kontak_info">Informasi Kontak</label>
                        <textarea name="kontak_info" id="kontak_info" class="form-control" rows="3"
                                  placeholder="Nomor telepon, email, alamat...">{{ old('kontak_info', $pengaturan->kontak_info ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save mr-2"></i>Simpan Pengaturan
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <div class="col-lg-4">
        <!-- Status Info -->
        <div class="card {{ $pengaturan->id && $pengaturan->isPendaftaranDibuka() ? 'bg-success' : 'bg-secondary' }}">
            <div class="card-body text-white">
                <h5><i class="fas fa-info-circle mr-2"></i>Status Pendaftaran</h5>
                @if($pengaturan->id && $pengaturan->isPendaftaranDibuka())
                    <p class="h3 mb-0">DIBUKA</p>
                    @if($pengaturan->tanggal_tutup)
                        <small>Sampai {{ $pengaturan->tanggal_tutup->format('d M Y') }}</small>
                    @endif
                @else
                    <p class="h3 mb-0">DITUTUP</p>
                    @if($pengaturan->id && !$pengaturan->pendaftaran_dibuka)
                        <small>Pendaftaran dinonaktifkan</small>
                    @elseif($pengaturan->tanggal_buka && now() < $pengaturan->tanggal_buka)
                        <small>Dibuka pada {{ $pengaturan->tanggal_buka->format('d M Y') }}</small>
                    @elseif($pengaturan->tanggal_tutup && now() > $pengaturan->tanggal_tutup)
                        <small>Periode pendaftaran telah berakhir</small>
                    @else
                        <small>Belum ada pengaturan</small>
                    @endif
                @endif
            </div>
            @if($pengaturan->id)
            <div class="card-footer bg-white">
                <form action="{{ route('admin.settings.ppdb.toggle') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-block {{ $pengaturan->pendaftaran_dibuka ? 'btn-danger' : 'btn-success' }}">
                        <i class="fas {{ $pengaturan->pendaftaran_dibuka ? 'fa-lock' : 'fa-lock-open' }} mr-2"></i>
                        {{ $pengaturan->pendaftaran_dibuka ? 'Tutup Pendaftaran' : 'Buka Pendaftaran' }}
                    </button>
                </form>
            </div>
            @endif
        </div>

        <!-- Aksi Cepat -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-bolt mr-2"></i>Aksi Cepat</h3>
            </div>
            <div class="card-body">
                <a href="{{ route('operator.pendaftar.index') }}" class="btn btn-info btn-block">
                    <i class="fas fa-users mr-2"></i>Kelola Pendaftar
                </a>
                <a href="{{ route('admin.settings.jurusan.index') }}" class="btn btn-secondary btn-block">
                    <i class="fas fa-graduation-cap mr-2"></i>Kelola Jurusan
                </a>
                <a href="{{ route('ppdb.pendaftaran.index') }}" class="btn btn-outline-primary btn-block" target="_blank">
                    <i class="fas fa-external-link-alt mr-2"></i>Lihat Halaman Pendaftaran
                </a>
            </div>
        </div>

        <!-- Info Periode -->
        @if($pengaturan->id)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-calendar mr-2"></i>Info Periode</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tr>
                        <td>Tahun Pelajaran</td>
                        <td class="text-right"><strong>{{ $pengaturan->tahunPelajaran->tahun_pelajaran ?? '-' }}</strong></td>
                    </tr>
                    <tr>
                        <td>Periode</td>
                        <td class="text-right">{{ $pengaturan->periode_pendaftaran }}</td>
                    </tr>
                    <tr>
                        <td>Biaya</td>
                        <td class="text-right">{{ $pengaturan->formatted_biaya }}</td>
                    </tr>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>
@stop

@section('css')
<style>
    .custom-control-label {
        cursor: pointer;
    }
</style>
@stop

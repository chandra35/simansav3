@extends('adminlte::page')

@section('title', 'Data Lulusan')

@section('content_header')
    <h1>Data Lulusan</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Input Data Lanjut Studi</h3>
                </div>
                <form action="{{ route('siswa.lulusan.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h5><i class="icon fas fa-info-circle"></i> Informasi</h5>
                            Data ini dipakai untuk tracking lulusan. Silakan isi sesuai hasil diterima yang sudah final.
                        </div>

                        <div class="form-group">
                            <label>Tahun Pelajaran</label>
                            <input type="text" class="form-control" value="{{ $targetTahunPelajaran->nama }}" readonly>
                        </div>

                        <div class="form-group">
                            <label>Kelas Acuan</label>
                            <input type="text" class="form-control" value="{{ optional($targetSiswaKelas->kelas)->nama_lengkap ?? optional($targetSiswaKelas->kelas)->nama_kelas ?? '-' }}" readonly>
                        </div>

                        <div class="form-group">
                            <label for="jalur_masuk">Jalur Diterima</label>
                            <select name="jalur_masuk" id="jalur_masuk" class="form-control @error('jalur_masuk') is-invalid @enderror" required>
                                <option value="">Pilih Jalur Diterima</option>
                                @foreach($jalurMasukOptions as $jalur)
                                    <option value="{{ $jalur }}" {{ old('jalur_masuk', $dataLulusan->jalur_masuk) === $jalur ? 'selected' : '' }}>
                                        {{ $jalur }}
                                    </option>
                                @endforeach
                            </select>
                            @error('jalur_masuk')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="nama_universitas">Nama Universitas / Kampus</label>
                            <input type="text" name="nama_universitas" id="nama_universitas" class="form-control @error('nama_universitas') is-invalid @enderror" value="{{ old('nama_universitas', $dataLulusan->nama_universitas) }}" required>
                            @error('nama_universitas')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="jurusan_fakultas">Jurusan / Fakultas</label>
                            <input type="text" name="jurusan_fakultas" id="jurusan_fakultas" class="form-control @error('jurusan_fakultas') is-invalid @enderror" value="{{ old('jurusan_fakultas', $dataLulusan->jurusan_fakultas) }}">
                            @error('jurusan_fakultas')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="program_studi">Program Studi</label>
                            <input type="text" name="program_studi" id="program_studi" class="form-control @error('program_studi') is-invalid @enderror" value="{{ old('program_studi', $dataLulusan->program_studi) }}" required>
                            @error('program_studi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="keterangan">Keterangan Tambahan</label>
                            <textarea name="keterangan" id="keterangan" rows="4" class="form-control @error('keterangan') is-invalid @enderror" placeholder="Opsional">{{ old('keterangan', $dataLulusan->keterangan) }}</textarea>
                            @error('keterangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Simpan Data Lulusan
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">Ringkasan</h3>
                </div>
                <div class="card-body">
                    <dl class="mb-0">
                        <dt>Nama Siswa</dt>
                        <dd>{{ $siswa->nama_lengkap }}</dd>

                        <dt>NISN</dt>
                        <dd>{{ $siswa->nisn }}</dd>

                        <dt>Status Data</dt>
                        <dd>
                            @if($dataLulusan->exists)
                                <span class="badge badge-success">Sudah Mengisi</span>
                            @else
                                <span class="badge badge-warning">Belum Mengisi</span>
                            @endif
                        </dd>

                        <dt>Terakhir Diperbarui</dt>
                        <dd>{{ $dataLulusan->updated_at?->format('d M Y H:i') ?? '-' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@stop

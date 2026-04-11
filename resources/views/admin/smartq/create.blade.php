@extends('adminlte::page')

@section('title', 'Buat Periode SMART-Q')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-plus-circle"></i> Buat Periode Seleksi SMART-Q</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.smartq.index') }}">SMART-Q</a></li>
                <li class="breadcrumb-item active">Buat Periode</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <form action="{{ route('admin.smartq.store') }}" method="POST">
        @csrf
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Informasi Periode</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nama Periode <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                                   value="{{ old('nama', 'Seleksi SMART-Q ' . date('Y') . '/' . (date('Y')+1)) }}" required>
                            @error('nama') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tahun Pelajaran <span class="text-danger">*</span></label>
                            <select name="tahun_pelajaran_id" class="form-control @error('tahun_pelajaran_id') is-invalid @enderror" required>
                                <option value="">-- Pilih --</option>
                                @foreach($tahunPelajarans as $tp)
                                    <option value="{{ $tp->id }}" {{ old('tahun_pelajaran_id') == $tp->id || $tp->is_active ? 'selected' : '' }}>
                                        {{ $tp->nama }} {{ $tp->is_active ? '(Aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tahun_pelajaran_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Kuota Siswa <span class="text-danger">*</span></label>
                            <input type="number" name="kuota" class="form-control @error('kuota') is-invalid @enderror"
                                   value="{{ old('kuota', 30) }}" min="1" required>
                            @error('kuota') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_mulai" class="form-control @error('tanggal_mulai') is-invalid @enderror"
                                   value="{{ old('tanggal_mulai', date('Y-m-d')) }}" required>
                            @error('tanggal_mulai') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_selesai" class="form-control @error('tanggal_selesai') is-invalid @enderror"
                                   value="{{ old('tanggal_selesai', date('Y-m-d', strtotime('+30 days'))) }}" required>
                            @error('tanggal_selesai') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>URL Moodle</label>
                            <input type="url" name="moodle_base_url" class="form-control" placeholder="https://elearning.man1metro.sch.id"
                                   value="{{ old('moodle_base_url') }}">
                            <small class="text-muted">Opsional, untuk integrasi CBT</small>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="3" placeholder="Deskripsi periode seleksi...">{{ old('deskripsi') }}</textarea>
                </div>
            </div>
        </div>

        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-balance-scale"></i> Komponen Nilai Default</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Komponen nilai default akan dibuat otomatis. Anda bisa mengubahnya nanti di halaman detail periode.
                </div>
                <table class="table table-bordered table-sm">
                    <thead class="bg-light">
                        <tr>
                            <th>Komponen</th>
                            <th width="120">Bobot</th>
                            <th width="120">Sumber</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><i class="fas fa-laptop text-primary"></i> Tes CBT (Moodle)</td>
                            <td>40%</td>
                            <td><span class="badge badge-primary">Moodle</span></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-quran text-success"></i> Tahfidz Al-Quran</td>
                            <td>25%</td>
                            <td><span class="badge badge-secondary">Manual</span></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-brain text-info"></i> Psikotes</td>
                            <td>20%</td>
                            <td><span class="badge badge-secondary">Manual</span></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-comments text-warning"></i> Wawancara</td>
                            <td>15%</td>
                            <td><span class="badge badge-secondary">Manual</span></td>
                        </tr>
                        <tr class="bg-light font-weight-bold">
                            <td>Total</td>
                            <td>100%</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mb-4">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save"></i> Buat Periode Seleksi
            </button>
            <a href="{{ route('admin.smartq.index') }}" class="btn btn-secondary btn-lg ml-2">
                <i class="fas fa-arrow-left"></i> Batal
            </a>
        </div>
    </form>
@stop

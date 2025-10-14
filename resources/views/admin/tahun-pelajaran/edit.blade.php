@extends('adminlte::page')

@section('title', 'Edit Tahun Pelajaran')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-edit"></i> Edit Tahun Pelajaran</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.tahun-pelajaran.index') }}">Tahun Pelajaran</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('admin.tahun-pelajaran.update', $tahunPelajaran->id) }}" method="POST" id="formTahunPelajaran">
                @csrf
                @method('PUT')
                
                {{-- Status Badge --}}
                <div class="callout callout-info">
                    <h5><i class="fas fa-info-circle"></i> Status:</h5>
                    {!! $tahunPelajaran->status_badge !!}
                    @if($tahunPelajaran->is_active)
                        <span class="badge badge-success ml-2">
                            <i class="fas fa-check-circle"></i> Tahun Aktif
                        </span>
                    @endif
                </div>

                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-info-circle"></i> Informasi Dasar</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="kurikulum_id">Kurikulum <span class="text-danger">*</span></label>
                                    <select name="kurikulum_id" id="kurikulum_id" class="form-control @error('kurikulum_id') is-invalid @enderror" required>
                                        <option value="">-- Pilih Kurikulum --</option>
                                        @foreach($kurikulums as $kurikulum)
                                            <option value="{{ $kurikulum->id }}" {{ (old('kurikulum_id', $tahunPelajaran->kurikulum_id) == $kurikulum->id) ? 'selected' : '' }}>
                                                {{ $kurikulum->formatted_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('kurikulum_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nama">Nama Tahun Pelajaran <span class="text-danger">*</span></label>
                                    <input type="text" name="nama" id="nama" class="form-control @error('nama') is-invalid @enderror" 
                                           value="{{ old('nama', $tahunPelajaran->nama) }}" placeholder="Contoh: 2024/2025" required>
                                    @error('nama')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tahun_mulai">Tahun Mulai <span class="text-danger">*</span></label>
                                    <input type="number" name="tahun_mulai" id="tahun_mulai" class="form-control @error('tahun_mulai') is-invalid @enderror" 
                                           value="{{ old('tahun_mulai', $tahunPelajaran->tahun_mulai) }}" min="2000" max="2100" required>
                                    @error('tahun_mulai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tahun_selesai">Tahun Selesai <span class="text-danger">*</span></label>
                                    <input type="number" name="tahun_selesai" id="tahun_selesai" class="form-control @error('tahun_selesai') is-invalid @enderror" 
                                           value="{{ old('tahun_selesai', $tahunPelajaran->tahun_selesai) }}" min="2000" max="2100" required>
                                    @error('tahun_selesai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tanggal_mulai">Tanggal Mulai <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control @error('tanggal_mulai') is-invalid @enderror" 
                                           value="{{ old('tanggal_mulai', $tahunPelajaran->tanggal_mulai?->format('Y-m-d')) }}" required>
                                    @error('tanggal_mulai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tanggal_selesai">Tanggal Selesai <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control @error('tanggal_selesai') is-invalid @enderror" 
                                           value="{{ old('tanggal_selesai', $tahunPelajaran->tanggal_selesai?->format('Y-m-d')) }}" required>
                                    @error('tanggal_selesai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-cog"></i> Pengaturan</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="semester_aktif">Semester Aktif <span class="text-danger">*</span></label>
                                    <select name="semester_aktif" id="semester_aktif" class="form-control @error('semester_aktif') is-invalid @enderror" required>
                                        <option value="Ganjil" {{ old('semester_aktif', $tahunPelajaran->semester_aktif) == 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                                        <option value="Genap" {{ old('semester_aktif', $tahunPelajaran->semester_aktif) == 'Genap' ? 'selected' : '' }}>Genap</option>
                                    </select>
                                    @error('semester_aktif')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if($tahunPelajaran->is_active)
                                        <small class="form-text text-info">
                                            <i class="fas fa-info-circle"></i> Gunakan tombol "Ganti Semester" di halaman utama untuk mengubah
                                        </small>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="status">Status <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                        <option value="non-aktif" {{ old('status', $tahunPelajaran->status) == 'non-aktif' ? 'selected' : '' }}>Non Aktif</option>
                                        <option value="aktif" {{ old('status', $tahunPelajaran->status) == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                        <option value="selesai" {{ old('status', $tahunPelajaran->status) == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="kuota_ppdb">Kuota PPDB <span class="text-danger">*</span></label>
                                    <input type="number" name="kuota_ppdb" id="kuota_ppdb" class="form-control @error('kuota_ppdb') is-invalid @enderror" 
                                           value="{{ old('kuota_ppdb', $tahunPelajaran->kuota_ppdb) }}" min="0" required>
                                    @error('kuota_ppdb')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Tersedia: <strong>{{ $tahunPelajaran->kuota_tersedia }}</strong> dari {{ $tahunPelajaran->kuota_ppdb }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-default">
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-6">
                                <a href="{{ route('admin.tahun-pelajaran.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                            </div>
                            <div class="col-md-6 text-right">
                                <button type="reset" class="btn btn-warning">
                                    <i class="fas fa-undo"></i> Reset
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // Auto-generate nama tahun pelajaran
            $('#tahun_mulai, #tahun_selesai').on('change', function() {
                const tahunMulai = $('#tahun_mulai').val();
                const tahunSelesai = $('#tahun_selesai').val();
                
                if (tahunMulai && tahunSelesai) {
                    $('#nama').val(tahunMulai + '/' + tahunSelesai);
                }
            });

            // Form validation
            $('#formTahunPelajaran').on('submit', function(e) {
                const tahunMulai = parseInt($('#tahun_mulai').val());
                const tahunSelesai = parseInt($('#tahun_selesai').val());
                
                if (tahunSelesai <= tahunMulai) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi Gagal',
                        text: 'Tahun selesai harus lebih besar dari tahun mulai'
                    });
                    return false;
                }
                
                const tanggalMulai = new Date($('#tanggal_mulai').val());
                const tanggalSelesai = new Date($('#tanggal_selesai').val());
                
                if (tanggalSelesai <= tanggalMulai) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi Gagal',
                        text: 'Tanggal selesai harus setelah tanggal mulai'
                    });
                    return false;
                }
            });
        });
    </script>
@stop

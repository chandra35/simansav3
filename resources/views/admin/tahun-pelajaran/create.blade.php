@extends('adminlte::page')

@section('title', 'Tambah Tahun Pelajaran')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-calendar-plus"></i> Tambah Tahun Pelajaran</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.tahun-pelajaran.index') }}">Tahun Pelajaran</a></li>
                <li class="breadcrumb-item active">Tambah</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <section class="simansa-form-hero simansa-form-hero--primary">
        <div>
            <span class="simansa-form-hero__eyebrow">Periode Baru</span>
            <h2 class="simansa-form-hero__title">Siapkan tahun pelajaran berikutnya dengan format yang rapi</h2>
            <p class="simansa-form-hero__desc">
                Isi periode, kurikulum, dan semester awal. Status `Belum Aktif` cocok dipakai untuk menyiapkan tahun ajaran baru
                tanpa langsung mengubah periode aktif sistem.
            </p>
        </div>
    </section>

    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('admin.tahun-pelajaran.store') }}" method="POST" id="formTahunPelajaran">
                @csrf
                
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
                                            <option value="{{ $kurikulum->id }}" {{ old('kurikulum_id') == $kurikulum->id ? 'selected' : '' }}>
                                                {{ $kurikulum->formatted_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('kurikulum_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Pilih kurikulum yang akan digunakan
                                    </small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nama">Nama Tahun Pelajaran <span class="text-danger">*</span></label>
                                    <input type="text" name="nama" id="nama" class="form-control @error('nama') is-invalid @enderror" 
                                           value="{{ old('nama') }}" placeholder="Contoh: 2024/2025" required>
                                    @error('nama')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Format: YYYY/YYYY
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tahun_mulai">Tahun Mulai <span class="text-danger">*</span></label>
                                    <input type="number" name="tahun_mulai" id="tahun_mulai" class="form-control @error('tahun_mulai') is-invalid @enderror" 
                                           value="{{ old('tahun_mulai', date('Y')) }}" min="2000" max="2100" required>
                                    @error('tahun_mulai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tahun_selesai">Tahun Selesai <span class="text-danger">*</span></label>
                                    <input type="number" name="tahun_selesai" id="tahun_selesai" class="form-control @error('tahun_selesai') is-invalid @enderror" 
                                           value="{{ old('tahun_selesai', date('Y') + 1) }}" min="2000" max="2100" required>
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
                                           value="{{ old('tanggal_mulai') }}" required>
                                    @error('tanggal_mulai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tanggal_selesai">Tanggal Selesai <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control @error('tanggal_selesai') is-invalid @enderror" 
                                           value="{{ old('tanggal_selesai') }}" required>
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
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="semester_aktif">Semester Awal <span class="text-danger">*</span></label>
                                    <select name="semester_aktif" id="semester_aktif" class="form-control @error('semester_aktif') is-invalid @enderror" required>
                                        <option value="Ganjil" {{ old('semester_aktif') == 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                                        <option value="Genap" {{ old('semester_aktif') == 'Genap' ? 'selected' : '' }}>Genap</option>
                                    </select>
                                    @error('semester_aktif')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Status <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                        <option value="non-aktif" {{ old('status') == 'non-aktif' ? 'selected' : '' }}>Belum Aktif</option>
                                        <option value="aktif" {{ old('status') == 'aktif' ? 'selected' : '' }}>Sedang Digunakan</option>
                                        <option value="selesai" {{ old('status') == 'selesai' ? 'selected' : '' }}>Arsip</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Gunakan tombol "Set Aktif" untuk mengaktifkan tahun pelajaran
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="jumlah_hari_kerja">Hari Kerja <span class="text-danger">*</span></label>
                                    <select name="jumlah_hari_kerja" id="jumlah_hari_kerja" class="form-control @error('jumlah_hari_kerja') is-invalid @enderror" required>
                                        <option value="5" @selected(old('jumlah_hari_kerja', 5) == 5)>5 hari (Senin–Jumat)</option>
                                        <option value="6" @selected(old('jumlah_hari_kerja') == 6)>6 hari (Senin–Sabtu)</option>
                                    </select>
                                    <small class="form-text text-muted">Menentukan hari pada jadwal, impor Wakakur, dan format absensi.</small>
                                    @error('jumlah_hari_kerja')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                                    <i class="fas fa-save"></i> Simpan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
    <style>
        .simansa-form-hero {
            padding: 1.6rem 1.8rem;
            margin-bottom: 1.2rem;
            border-radius: 22px;
            color: #fff;
            box-shadow: 0 18px 40px rgba(38, 71, 208, 0.14);
        }
        .simansa-form-hero--primary {
            background: linear-gradient(135deg, #3050d4 0%, #2f7c90 100%);
        }
        .simansa-form-hero__eyebrow {
            display: inline-block;
            margin-bottom: 0.65rem;
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            opacity: 0.82;
        }
        .simansa-form-hero__title {
            margin: 0 0 0.45rem;
            font-size: 1.7rem;
            font-weight: 800;
            line-height: 1.15;
        }
        .simansa-form-hero__desc {
            margin: 0;
            max-width: 780px;
            color: rgba(255, 255, 255, 0.92);
        }
    </style>
@stop

@section('js')
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

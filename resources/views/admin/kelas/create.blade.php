@extends('adminlte::page')

@section('title', 'Tambah Kelas')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-plus-circle"></i> Tambah Kelas</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.kelas.index') }}">Kelas</a></li>
                <li class="breadcrumb-item active">Tambah</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <form action="{{ route('admin.kelas.store') }}" method="POST">
        @csrf
        
        <div class="row">
            <div class="col-md-8">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-info-circle"></i> Informasi Kelas</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="tahun_pelajaran_id">Tahun Pelajaran <span class="text-danger">*</span></label>
                            <select class="form-control @error('tahun_pelajaran_id') is-invalid @enderror" 
                                id="tahun_pelajaran_id" name="tahun_pelajaran_id" required>
                                <option value="">Pilih Tahun Pelajaran</option>
                                @foreach($tahunPelajarans as $tp)
                                    <option value="{{ $tp->id }}" {{ old('tahun_pelajaran_id') == $tp->id ? 'selected' : '' }}>
                                        {{ $tp->nama }} {{ $tp->is_active ? '(Aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tahun_pelajaran_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="kurikulum_id">Kurikulum <span class="text-danger">*</span></label>
                                    <select class="form-control @error('kurikulum_id') is-invalid @enderror" 
                                        id="kurikulum_id" name="kurikulum_id" required>
                                        <option value="">Pilih Kurikulum</option>
                                        @foreach($kurikulums as $k)
                                            <option value="{{ $k->id }}" data-has-jurusan="{{ $k->has_jurusan }}" 
                                                {{ old('kurikulum_id') == $k->id ? 'selected' : '' }}>
                                                {{ $k->formatted_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('kurikulum_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group" id="jurusan-group" style="display: none;">
                                    <label for="jurusan_id">Jurusan/Peminatan</label>
                                    <select class="form-control @error('jurusan_id') is-invalid @enderror" 
                                        id="jurusan_id" name="jurusan_id">
                                        <option value="">Tanpa Jurusan</option>
                                        @foreach($jurusans as $j)
                                            <option value="{{ $j->id }}" {{ old('jurusan_id') == $j->id ? 'selected' : '' }}>
                                                {{ $j->nama_jurusan }} ({{ $j->singkatan }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('jurusan_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Kosongkan jika kurikulum tidak memiliki jurusan
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tingkat">Tingkat <span class="text-danger">*</span></label>
                                    <select class="form-control @error('tingkat') is-invalid @enderror" 
                                        id="tingkat" name="tingkat" required>
                                        <option value="">Pilih Tingkat</option>
                                        @foreach($tingkatOptions as $value => $label)
                                            <option value="{{ $value }}" {{ old('tingkat') == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('tingkat')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nama_kelas">Nama Kelas <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nama_kelas') is-invalid @enderror" 
                                        id="nama_kelas" name="nama_kelas" value="{{ old('nama_kelas') }}" 
                                        placeholder="Contoh: X IPA 1, XI IPS 2" required maxlength="50">
                                    @error('nama_kelas')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Format: Tingkat + Jurusan + Nomor
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="wali_kelas_id">Wali Kelas</label>
                            <select class="form-control @error('wali_kelas_id') is-invalid @enderror" 
                                id="wali_kelas_id" name="wali_kelas_id">
                                <option value="">Belum ditugaskan</option>
                                @foreach($waliKelas as $wk)
                                    <option value="{{ $wk->id }}" {{ old('wali_kelas_id') == $wk->id ? 'selected' : '' }}>
                                        {{ $wk->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('wali_kelas_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Guru yang bertanggung jawab atas kelas ini
                            </small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="kapasitas">Kapasitas <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('kapasitas') is-invalid @enderror" 
                                        id="kapasitas" name="kapasitas" value="{{ old('kapasitas', 36) }}" 
                                        required min="1" max="50">
                                    @error('kapasitas')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Maksimal jumlah siswa (1-50)
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ruang_kelas">Ruang Kelas</label>
                                    <input type="text" class="form-control @error('ruang_kelas') is-invalid @enderror" 
                                        id="ruang_kelas" name="ruang_kelas" value="{{ old('ruang_kelas') }}" 
                                        placeholder="Contoh: R.01, Lab Komputer" maxlength="50">
                                    @error('ruang_kelas')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                                id="deskripsi" name="deskripsi" rows="3" 
                                placeholder="Catatan tambahan tentang kelas...">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-cog"></i> Pengaturan</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="is_active">Status</label>
                            <select class="form-control" id="is_active" name="is_active">
                                <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ old('is_active') == 0 ? 'selected' : '' }}>Non-Aktif</option>
                            </select>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Kelas aktif dapat menerima siswa
                            </small>
                        </div>

                        <div class="callout callout-info">
                            <h5><i class="fas fa-info-circle"></i> Catatan:</h5>
                            <ul class="mb-0 pl-3">
                                <li>Kode kelas akan digenerate otomatis</li>
                                <li>Siswa dapat ditambahkan setelah kelas dibuat</li>
                                <li>Wali kelas dapat diubah kapan saja</li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('admin.kelas.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <button type="reset" class="btn btn-warning">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                        <button type="submit" class="btn btn-primary float-right">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Toggle jurusan field based on kurikulum selection
            $('#kurikulum_id').on('change', function() {
                let hasJurusan = $(this).find(':selected').data('has-jurusan');
                if (hasJurusan == 1) {
                    $('#jurusan-group').show();
                    $('#jurusan_id').prop('required', true);
                } else {
                    $('#jurusan-group').hide();
                    $('#jurusan_id').prop('required', false).val('');
                }
            });

            // Trigger on page load if old value exists
            if ($('#kurikulum_id').val()) {
                $('#kurikulum_id').trigger('change');
            }
        });
    </script>
@stop

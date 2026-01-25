@extends('adminlte::page')

@section('title', 'Edit Jadwal Pelajaran')

@section('content_header')
    <h1><i class="fas fa-edit mr-2"></i>Edit Jadwal Pelajaran</h1>
@stop

@section('plugins.Select2', true)

@section('content')
    <div class="card">
        <form action="{{ route('admin.jadwal-pelajaran.update', $jadwalPelajaran->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tahun_pelajaran_id">Tahun Pelajaran <span class="text-danger">*</span></label>
                            <select name="tahun_pelajaran_id" id="tahun_pelajaran_id" class="form-control @error('tahun_pelajaran_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Tahun Pelajaran --</option>
                                @foreach($tahunPelajaran as $tp)
                                    <option value="{{ $tp->id }}" {{ old('tahun_pelajaran_id', $jadwalPelajaran->tahun_pelajaran_id) == $tp->id ? 'selected' : '' }}>
                                        {{ $tp->nama }} {{ $tp->is_aktif ? '(Aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tahun_pelajaran_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="kelas_id">Kelas <span class="text-danger">*</span></label>
                            <select name="kelas_id" id="kelas_id" class="form-control select2 @error('kelas_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($kelas as $k)
                                    <option value="{{ $k->id }}" {{ old('kelas_id', $jadwalPelajaran->kelas_id) == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama_lengkap }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kelas_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="mapel_id">Mata Pelajaran <span class="text-danger">*</span></label>
                            <select name="mapel_id" id="mapel_id" class="form-control select2 @error('mapel_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                @foreach($mapel as $m)
                                    <option value="{{ $m->id }}" {{ old('mapel_id', $jadwalPelajaran->mapel_id) == $m->id ? 'selected' : '' }}>
                                        {{ $m->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('mapel_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="guru_id">Guru Pengajar <span class="text-danger">*</span></label>
                            <select name="gtk_id" id="guru_id" class="form-control select2 @error('gtk_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Guru --</option>
                                @foreach($guru as $g)
                                    <option value="{{ $g->id }}" {{ old('gtk_id', $jadwalPelajaran->gtk_id) == $g->id ? 'selected' : '' }}>
                                        {{ $g->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('gtk_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="hari">Hari <span class="text-danger">*</span></label>
                            <select name="hari" id="hari" class="form-control @error('hari') is-invalid @enderror" required>
                                @foreach($hari as $h)
                                    <option value="{{ $h }}" {{ old('hari', $jadwalPelajaran->hari) == $h ? 'selected' : '' }}>{{ $h }}</option>
                                @endforeach
                            </select>
                            @error('hari')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="jam_ke">Jam Ke <span class="text-danger">*</span></label>
                            <input type="number" name="jam_ke" id="jam_ke" 
                                class="form-control @error('jam_ke') is-invalid @enderror" 
                                value="{{ old('jam_ke', $jadwalPelajaran->jam_ke) }}" min="1" max="12" required>
                            @error('jam_ke')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="waktu_mulai">Waktu Mulai <span class="text-danger">*</span></label>
                                    <input type="time" name="waktu_mulai" id="waktu_mulai" 
                                        class="form-control @error('waktu_mulai') is-invalid @enderror" 
                                        value="{{ old('waktu_mulai', $jadwalPelajaran->waktu_mulai) }}" required>
                                    @error('waktu_mulai')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="waktu_selesai">Waktu Selesai <span class="text-danger">*</span></label>
                                    <input type="time" name="waktu_selesai" id="waktu_selesai" 
                                        class="form-control @error('waktu_selesai') is-invalid @enderror" 
                                        value="{{ old('waktu_selesai', $jadwalPelajaran->waktu_selesai) }}" required>
                                    @error('waktu_selesai')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="ruangan">Ruangan</label>
                            <input type="text" name="ruangan" id="ruangan" 
                                class="form-control @error('ruangan') is-invalid @enderror" 
                                value="{{ old('ruangan', $jadwalPelajaran->ruangan) }}">
                            @error('ruangan')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="semester">Semester <span class="text-danger">*</span></label>
                            <select name="semester" id="semester" class="form-control @error('semester') is-invalid @enderror" required>
                                <option value="1" {{ old('semester', $jadwalPelajaran->semester) == '1' ? 'selected' : '' }}>Semester 1 (Ganjil)</option>
                                <option value="2" {{ old('semester', $jadwalPelajaran->semester) == '2' ? 'selected' : '' }}>Semester 2 (Genap)</option>
                            </select>
                            @error('semester')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('admin.jadwal-pelajaran.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
                <button type="submit" class="btn btn-primary float-right">
                    <i class="fas fa-save mr-1"></i> Update
                </button>
            </div>
        </form>
    </div>
@stop

@section('js')
<script>
$(function() {
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });
});
</script>
@stop

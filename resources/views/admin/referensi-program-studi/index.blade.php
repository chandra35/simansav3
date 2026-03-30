@extends('adminlte::page')

@section('title', 'Referensi Program Studi')

@section('content_header')
    <h1>Referensi Program Studi</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Tambah Referensi Prodi</h3>
                </div>
                <form action="{{ route('admin.referensi-program-studi.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="referensi_perguruan_tinggi_id">Kampus</label>
                            <select name="referensi_perguruan_tinggi_id" id="referensi_perguruan_tinggi_id" class="form-control" required>
                                <option value="">Pilih Kampus</option>
                                @foreach($campuses as $campus)
                                    <option value="{{ $campus->id }}" {{ old('referensi_perguruan_tinggi_id', $selectedCampusId) === $campus->id ? 'selected' : '' }}>
                                        {{ $campus->nama }} ({{ $campus->jenis }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="nama">Nama Prodi</label>
                            <input type="text" name="nama" id="nama" class="form-control" value="{{ old('nama') }}" required>
                        </div>
                        <div class="form-group">
                            <label for="jenjang">Jenjang</label>
                            <input type="text" name="jenjang" id="jenjang" class="form-control" value="{{ old('jenjang') }}" placeholder="Contoh: S1, D4, PROFESI">
                        </div>
                        <div class="form-group">
                            <label for="fakultas">Bidang / Fakultas</label>
                            <input type="text" name="fakultas" id="fakultas" class="form-control" value="{{ old('fakultas') }}">
                        </div>
                        <div class="form-group">
                            <label for="sumber_referensi">Sumber Referensi</label>
                            <input type="text" name="sumber_referensi" id="sumber_referensi" class="form-control" value="{{ old('sumber_referensi', 'kurasi manual') }}">
                        </div>
                        <div class="form-group mb-0">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" name="is_active" class="custom-control-input" id="is_active" value="1" checked>
                                <label class="custom-control-label" for="is_active">Aktif</label>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">Sinkron PDDIKTI</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-2">Sinkronisasi resmi dijalankan dari server/terminal agar master prodi lokal mengikuti data PDDIKTI.</p>
                    <code>php artisan referensi:sync-prodi-pddikti --only-active</code>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Daftar Referensi Prodi</h3>
                    <div class="card-tools">
                        <form method="GET" action="{{ route('admin.referensi-program-studi.index') }}">
                            <div class="input-group input-group-sm" style="width: 320px;">
                                <select name="referensi_perguruan_tinggi_id" class="form-control">
                                    <option value="">Semua Kampus</option>
                                    @foreach($campuses as $campus)
                                        <option value="{{ $campus->id }}" {{ $selectedCampusId === $campus->id ? 'selected' : '' }}>
                                            {{ $campus->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="submit">Filter</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Prodi</th>
                                <th>Kampus</th>
                                <th>Bidang</th>
                                <th>Sumber</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($studyPrograms as $studyProgram)
                                <tr>
                                    <td>
                                        <strong>{{ trim(($studyProgram->jenjang ? $studyProgram->jenjang . ' ' : '') . $studyProgram->nama) }}</strong>
                                    </td>
                                    <td>{{ $studyProgram->perguruanTinggi?->nama ?? '-' }}</td>
                                    <td>{{ $studyProgram->fakultas ?? '-' }}</td>
                                    <td>{{ $studyProgram->sumber_referensi ?? '-' }}</td>
                                    <td>
                                        @if($studyProgram->is_active)
                                            <span class="badge badge-success">Aktif</span>
                                        @else
                                            <span class="badge badge-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-xs btn-warning" data-toggle="modal" data-target="#editProdiModal{{ $studyProgram->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('admin.referensi-program-studi.destroy', $studyProgram) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus referensi program studi ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <div class="modal fade" id="editProdiModal{{ $studyProgram->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Referensi Prodi</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form action="{{ route('admin.referensi-program-studi.update', $studyProgram) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label>Kampus</label>
                                                        <select name="referensi_perguruan_tinggi_id" class="form-control" required>
                                                            @foreach($campuses as $campus)
                                                                <option value="{{ $campus->id }}" {{ $studyProgram->referensi_perguruan_tinggi_id === $campus->id ? 'selected' : '' }}>
                                                                    {{ $campus->nama }} ({{ $campus->jenis }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Nama Prodi</label>
                                                        <input type="text" name="nama" class="form-control" value="{{ $studyProgram->nama }}" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Jenjang</label>
                                                        <input type="text" name="jenjang" class="form-control" value="{{ $studyProgram->jenjang }}">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Bidang / Fakultas</label>
                                                        <input type="text" name="fakultas" class="form-control" value="{{ $studyProgram->fakultas }}">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Sumber Referensi</label>
                                                        <input type="text" name="sumber_referensi" class="form-control" value="{{ $studyProgram->sumber_referensi }}">
                                                    </div>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" name="is_active" class="custom-control-input" id="prodiSwitch{{ $studyProgram->id }}" value="1" {{ $studyProgram->is_active ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="prodiSwitch{{ $studyProgram->id }}">Aktif</label>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Belum ada referensi program studi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($studyPrograms->hasPages())
                    <div class="card-footer clearfix">
                        {{ $studyPrograms->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@stop

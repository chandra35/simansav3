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
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">Belum ada referensi program studi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($studyPrograms->hasPages())
    <div class="card-footer d-flex flex-column flex-md-row align-items-md-center justify-content-between">
        <div class="text-muted small mb-2 mb-md-0">
            Menampilkan {{ $studyPrograms->firstItem() }}-{{ $studyPrograms->lastItem() }} dari {{ $studyPrograms->total() }} data
        </div>
        <div class="mb-0">
            {{ $studyPrograms->onEachSide(1)->links('pagination::bootstrap-4') }}
        </div>
    </div>
@endif

@foreach($studyPrograms as $studyProgram)
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
@endforeach

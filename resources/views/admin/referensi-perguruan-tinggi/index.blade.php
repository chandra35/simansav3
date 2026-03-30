@extends('adminlte::page')

@section('title', 'Referensi Kampus')

@section('content_header')
    <h1>Referensi Kampus</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Tambah Referensi Kampus</h3>
                </div>
                <form action="{{ route('admin.referensi-perguruan-tinggi.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="nama">Nama Kampus</label>
                            <input type="text" name="nama" id="nama" class="form-control" value="{{ old('nama') }}" required>
                        </div>
                        <div class="form-group">
                            <label for="jenis">Jenis</label>
                            <select name="jenis" id="jenis" class="form-control" required>
                                @foreach($jenisOptions as $jenis)
                                    <option value="{{ $jenis }}" {{ old('jenis') === $jenis ? 'selected' : '' }}>{{ $jenis }}</option>
                                @endforeach
                            </select>
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
        </div>
        <div class="col-lg-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Daftar Referensi Kampus</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Jenis</th>
                                <th>Sumber</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($references as $reference)
                                <tr>
                                    <td>{{ $reference->nama }}</td>
                                    <td><span class="badge badge-info">{{ $reference->jenis }}</span></td>
                                    <td>{{ $reference->sumber_referensi ?? '-' }}</td>
                                    <td>
                                        @if($reference->is_active)
                                            <span class="badge badge-success">Aktif</span>
                                        @else
                                            <span class="badge badge-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-xs btn-warning" data-toggle="modal" data-target="#editModal{{ $reference->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('admin.referensi-perguruan-tinggi.destroy', $reference) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus referensi kampus ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <div class="modal fade" id="editModal{{ $reference->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Referensi Kampus</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form action="{{ route('admin.referensi-perguruan-tinggi.update', $reference) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label>Nama Kampus</label>
                                                        <input type="text" name="nama" class="form-control" value="{{ $reference->nama }}" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Jenis</label>
                                                        <select name="jenis" class="form-control" required>
                                                            @foreach($jenisOptions as $jenis)
                                                                <option value="{{ $jenis }}" {{ $reference->jenis === $jenis ? 'selected' : '' }}>{{ $jenis }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Sumber Referensi</label>
                                                        <input type="text" name="sumber_referensi" class="form-control" value="{{ $reference->sumber_referensi }}">
                                                    </div>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" name="is_active" class="custom-control-input" id="switch{{ $reference->id }}" value="1" {{ $reference->is_active ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="switch{{ $reference->id }}">Aktif</label>
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
                                    <td colspan="5" class="text-center text-muted py-4">Belum ada referensi kampus.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@extends('adminlte::page')

@section('title', 'Edit Periode SMART-Q')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-edit"></i> Edit: {{ $smartq->nama }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.smartq.index') }}">SMART-Q</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    {{-- Edit Periode --}}
    <form action="{{ route('admin.smartq.update', $smartq) }}" method="POST">
        @csrf @method('PUT')
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Informasi Periode</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nama Periode <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control" value="{{ old('nama', $smartq->nama) }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tahun Pelajaran <span class="text-danger">*</span></label>
                            <select name="tahun_pelajaran_id" class="form-control" required>
                                @foreach($tahunPelajarans as $tp)
                                    <option value="{{ $tp->id }}" {{ $smartq->tahun_pelajaran_id == $tp->id ? 'selected' : '' }}>
                                        {{ $tp->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                @foreach(['pendaftaran','seleksi','pengumuman','selesai'] as $s)
                                    <option value="{{ $s }}" {{ $smartq->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Kuota</label>
                            <input type="number" name="kuota" class="form-control" value="{{ old('kuota', $smartq->kuota) }}" min="1" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" class="form-control" value="{{ old('tanggal_mulai', $smartq->tanggal_mulai->format('Y-m-d')) }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" class="form-control" value="{{ old('tanggal_selesai', $smartq->tanggal_selesai->format('Y-m-d')) }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>URL Moodle</label>
                            <input type="url" name="moodle_base_url" class="form-control" value="{{ old('moodle_base_url', $smartq->moodle_base_url) }}">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="2">{{ old('deskripsi', $smartq->deskripsi) }}</textarea>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
                <a href="{{ route('admin.smartq.show', $smartq) }}" class="btn btn-secondary ml-2">Batal</a>
            </div>
        </div>
    </form>

    {{-- Edit Komponen Nilai --}}
    <form action="{{ route('admin.smartq.komponen.update', $smartq) }}" method="POST" id="formKomponen">
        @csrf @method('PUT')
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-balance-scale"></i> Komponen Penilaian</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool btn-sm text-white" onclick="addKomponen()">
                        <i class="fas fa-plus"></i> Tambah Komponen
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0" id="tblKomponen">
                    <thead class="bg-light">
                        <tr>
                            <th width="40">#</th>
                            <th>Nama Komponen</th>
                            <th width="120">Kode</th>
                            <th width="100">Bobot (%)</th>
                            <th width="100">Nilai Maks</th>
                            <th width="130">Sumber</th>
                            <th width="60"></th>
                        </tr>
                    </thead>
                    <tbody id="komponenBody">
                        @foreach($smartq->komponenNilais as $i => $k)
                            <tr>
                                <td class="row-num">{{ $i + 1 }}</td>
                                <td>
                                    <input type="hidden" name="komponen[{{ $i }}][id]" value="{{ $k->id }}">
                                    <input type="text" name="komponen[{{ $i }}][nama]" class="form-control form-control-sm" value="{{ $k->nama }}" required>
                                </td>
                                <td><input type="text" name="komponen[{{ $i }}][kode]" class="form-control form-control-sm" value="{{ $k->kode }}" required></td>
                                <td><input type="number" name="komponen[{{ $i }}][bobot]" class="form-control form-control-sm bobot-input" value="{{ $k->bobot }}" step="0.01" min="0" max="100" required></td>
                                <td><input type="number" name="komponen[{{ $i }}][nilai_maksimal]" class="form-control form-control-sm" value="{{ $k->nilai_maksimal }}" min="1" required></td>
                                <td>
                                    <select name="komponen[{{ $i }}][sumber]" class="form-control form-control-sm">
                                        <option value="manual" {{ $k->sumber === 'manual' ? 'selected' : '' }}>Manual</option>
                                        <option value="moodle" {{ $k->sumber === 'moodle' ? 'selected' : '' }}>Moodle</option>
                                    </select>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-xs btn-danger" onclick="this.closest('tr').remove(); reindex()">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-light font-weight-bold">
                            <td colspan="3">Total Bobot</td>
                            <td id="totalBobot">{{ $smartq->total_bobot }}%</td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-info"><i class="fas fa-save"></i> Simpan Komponen</button>
            </div>
        </div>
    </form>
@stop

@section('js')
@include('admin.smartq._overlay')
<script>
let kompIdx = {{ $smartq->komponenNilais->count() }};

function addKomponen() {
    const i = kompIdx++;
    const row = `<tr>
        <td class="row-num">${i+1}</td>
        <td><input type="text" name="komponen[${i}][nama]" class="form-control form-control-sm" required></td>
        <td><input type="text" name="komponen[${i}][kode]" class="form-control form-control-sm" required></td>
        <td><input type="number" name="komponen[${i}][bobot]" class="form-control form-control-sm bobot-input" step="0.01" min="0" max="100" required></td>
        <td><input type="number" name="komponen[${i}][nilai_maksimal]" class="form-control form-control-sm" value="100" min="1" required></td>
        <td><select name="komponen[${i}][sumber]" class="form-control form-control-sm">
            <option value="manual">Manual</option><option value="moodle">Moodle</option></select></td>
        <td class="text-center"><button type="button" class="btn btn-xs btn-danger" onclick="this.closest('tr').remove(); reindex()"><i class="fas fa-trash"></i></button></td>
    </tr>`;
    document.getElementById('komponenBody').insertAdjacentHTML('beforeend', row);
    reindex();
}

function reindex() {
    document.querySelectorAll('#komponenBody tr').forEach((tr, i) => {
        tr.querySelector('.row-num').textContent = i + 1;
    });
    updateTotal();
}

function updateTotal() {
    let total = 0;
    document.querySelectorAll('.bobot-input').forEach(el => { total += parseFloat(el.value) || 0; });
    const el = document.getElementById('totalBobot');
    el.textContent = total.toFixed(2) + '%';
    el.style.color = Math.abs(total - 100) < 0.01 ? 'green' : 'red';
}

document.addEventListener('input', e => { if (e.target.classList.contains('bobot-input')) updateTotal(); });
</script>
@stop

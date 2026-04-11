@extends('adminlte::page')

@section('title', 'Input Nilai SMART-Q')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-pencil-alt"></i> Input Nilai: {{ $smartq->nama }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.smartq.index') }}">SMART-Q</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.smartq.show', $smartq) }}">{{ $smartq->nama }}</a></li>
                <li class="breadcrumb-item active">Input Nilai</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i>
        Komponen dengan sumber <span class="badge badge-primary">Moodle</span> bisa diisi manual atau di-sync dari Moodle.
        Komponen <span class="badge badge-secondary">Manual</span> harus diisi di sini.
        Nilai yang dimasukkan akan dikonversi ke skala 100 berdasarkan nilai maksimal komponen.
    </div>

    <form action="{{ route('admin.smartq.nilai.simpan', $smartq) }}" method="POST">
        @csrf
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-table"></i> Tabel Nilai ({{ $pesertas->count() }} peserta)</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.smartq.show', $smartq) }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead class="bg-gradient-dark text-white">
                            <tr>
                                <th width="40" class="text-center">#</th>
                                <th width="90">No. Peserta</th>
                                <th width="200">Nama Siswa</th>
                                @foreach($smartq->komponenNilais as $k)
                                    <th class="text-center" width="140">
                                        {{ $k->nama }}<br>
                                        <small>Maks: {{ $k->nilai_maksimal }} | {!! $k->sumber_badge !!}</small>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pesertas as $i => $p)
                                <tr>
                                    <td class="text-center">{{ $i + 1 }}</td>
                                    <td><code>{{ $p->nomor_peserta }}</code></td>
                                    <td>
                                        <strong>{{ $p->siswa->nama_lengkap ?? '-' }}</strong>
                                        <br><small class="text-muted">{{ $p->kelasAsal->nama_lengkap ?? '-' }}</small>
                                    </td>
                                    @foreach($smartq->komponenNilais as $k)
                                        @php $nilai = $p->getNilaiKomponen($k->id); @endphp
                                        <td>
                                            <input type="number"
                                                   name="nilai[{{ $p->id }}][{{ $k->id }}][nilai]"
                                                   class="form-control form-control-sm text-center"
                                                   value="{{ $nilai?->nilai }}"
                                                   step="0.01" min="0" max="{{ $k->nilai_maksimal }}"
                                                   placeholder="0 - {{ $k->nilai_maksimal }}">
                                            @if($nilai?->moodle_attempt_id)
                                                <small class="text-primary"><i class="fas fa-cloud"></i> Moodle synced</small>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-save"></i> Simpan Semua Nilai & Hitung Ranking
                </button>
            </div>
        </div>
    </form>
@stop

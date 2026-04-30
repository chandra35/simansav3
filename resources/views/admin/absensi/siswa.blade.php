@extends('adminlte::page')

@section('title', 'Absensi Siswa')

@php
    $statusOptions = [
        'hadir' => ['label' => 'Hadir', 'class' => 'success'],
        'izin' => ['label' => 'Izin', 'class' => 'info'],
        'sakit' => ['label' => 'Sakit', 'class' => 'warning'],
        'alpa' => ['label' => 'Alpa', 'class' => 'danger'],
        'dispen' => ['label' => 'Dispen', 'class' => 'secondary'],
    ];
@endphp

@section('content_header')
    <h1><i class="fas fa-user-check"></i> Absensi Siswa</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-1"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="card card-primary card-outline">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.absensi-siswa.index') }}" class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="tanggal"><i class="fas fa-calendar-alt"></i> Tanggal</label>
                        <input type="date" class="form-control" id="tanggal" name="tanggal" value="{{ $tanggal }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="mode"><i class="fas fa-layer-group"></i> Mode Absensi</label>
                        <select name="mode" id="mode" class="form-control">
                            @if($canManageHarian)
                                <option value="harian" {{ $mode === 'harian' ? 'selected' : '' }}>Harian Wali Kelas</option>
                            @endif
                            @if($canManageMapel)
                                <option value="mapel" {{ $mode === 'mapel' ? 'selected' : '' }}>Per Mapel / Jam Pelajaran</option>
                            @endif
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="kelas_id"><i class="fas fa-school"></i> Kelas</label>
                        <select name="kelas_id" id="kelas_id" class="form-control">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($kelasOptions as $kelas)
                                <option value="{{ $kelas->id }}" {{ $selectedKelas && $selectedKelas->id === $kelas->id ? 'selected' : '' }}>
                                    {{ $kelas->nama_lengkap }} - {{ $kelas->tahunPelajaran->nama ?? '-' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-sync"></i> Muat Data
                    </button>
                </div>

                @if($mode === 'mapel')
                    <div class="col-md-12">
                        <div class="form-group mb-0">
                            <label for="jadwal_pelajaran_id"><i class="fas fa-clock"></i> Jadwal Pelajaran</label>
                            <select name="jadwal_pelajaran_id" id="jadwal_pelajaran_id" class="form-control" onchange="this.form.submit()">
                                <option value="">-- Pilih Jadwal --</option>
                                @foreach($jadwalOptions as $jadwal)
                                    <option value="{{ $jadwal->id }}" {{ $selectedJadwalId === $jadwal->id ? 'selected' : '' }}>
                                        Jam {{ $jadwal->jam_ke }} | {{ $jadwal->mapel_nama ?? 'Mapel' }} | {{ optional($jadwal->gtk->user)->name ?? 'Guru' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endif
            </form>
        </div>
    </div>

    @if($selectedKelas)
        <div class="row">
            <div class="col-lg-2 col-6">
                <div class="small-box bg-primary">
                    <div class="inner"><h3>{{ $students->count() }}</h3><p>Total Siswa</p></div>
                    <div class="icon"><i class="fas fa-users"></i></div>
                </div>
            </div>
            @foreach($summary as $key => $count)
                <div class="col-lg-2 col-6">
                    <div class="small-box bg-{{ $statusOptions[$key]['class'] }}">
                        <div class="inner"><h3>{{ $count }}</h3><p>{{ $statusOptions[$key]['label'] }}</p></div>
                        <div class="icon"><i class="fas fa-chart-bar"></i></div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clipboard-list"></i>
                    {{ $mode === 'harian' ? 'Absensi Harian' : 'Absensi Mapel' }}
                    - {{ $selectedKelas->nama_lengkap }}
                </h3>
                <div class="card-tools">
                    @if($session)
                        <span class="badge badge-success">Tersimpan {{ $session->updated_at?->format('d/m/Y H:i') }}</span>
                    @else
                        <span class="badge badge-secondary">Belum disimpan</span>
                    @endif
                </div>
            </div>
            <form method="POST" action="{{ route('admin.absensi-siswa.store') }}">
                @csrf
                <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                <input type="hidden" name="mode" value="{{ $mode }}">
                <input type="hidden" name="kelas_id" value="{{ $selectedKelas->id }}">
                @if($mode === 'mapel')
                    <input type="hidden" name="jadwal_pelajaran_id" value="{{ $selectedJadwalId }}">
                @endif

                <div class="card-body">
                    @if($mode === 'mapel' && !$selectedJadwalId)
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle mr-1"></i>Pilih jadwal pelajaran terlebih dahulu untuk absensi per mapel.
                        </div>
                    @elseif($students->isEmpty())
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle mr-1"></i>Belum ada siswa aktif di kelas ini.
                        </div>
                    @else
                        <div class="d-flex flex-wrap mb-3">
                            <button type="button" class="btn btn-sm btn-success mr-2 mb-2 quick-status" data-status="hadir">Set Semua Hadir</button>
                            <button type="button" class="btn btn-sm btn-warning mr-2 mb-2 quick-status" data-status="sakit">Set Semua Sakit</button>
                            <button type="button" class="btn btn-sm btn-info mr-2 mb-2 quick-status" data-status="izin">Set Semua Izin</button>
                            <button type="button" class="btn btn-sm btn-danger mb-2 quick-status" data-status="alpa">Set Semua Alpa</button>
                        </div>

                        <div class="form-group">
                            <label for="session_notes"><i class="fas fa-sticky-note"></i> Catatan Sesi</label>
                            <textarea class="form-control" name="session_notes" id="session_notes" rows="2" placeholder="Opsional">{{ old('session_notes', $session?->notes) }}</textarea>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th width="50">No</th>
                                        <th>NISN</th>
                                        <th>Nama Siswa</th>
                                        <th width="180">Status</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($students as $index => $siswa)
                                        @php $record = $existingRecords->get($siswa->id); @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $siswa->nisn }}</td>
                                            <td>{{ $siswa->nama_lengkap }}</td>
                                            <td>
                                                <select name="statuses[{{ $siswa->id }}]" class="form-control form-control-sm student-status">
                                                    @foreach($statusOptions as $value => $meta)
                                                        <option value="{{ $value }}" {{ old("statuses.$siswa->id", $record?->status ?? 'hadir') === $value ? 'selected' : '' }}>
                                                            {{ $meta['label'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm" name="notes[{{ $siswa->id }}]" value="{{ old("notes.$siswa->id", $record?->notes) }}" placeholder="Catatan opsional">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                @if(($mode !== 'mapel' || $selectedJadwalId) && $students->isNotEmpty())
                    <div class="card-footer d-flex justify-content-between">
                        <div class="text-muted small">
                            Mode saat ini: <strong>{{ $mode === 'harian' ? 'Harian Wali Kelas' : 'Per Mapel' }}</strong> |
                            Metode: <strong>manual</strong> |
                            Siap untuk integrasi <strong>face/hybrid</strong> di sesi yang sama.
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Simpan Absensi
                        </button>
                    </div>
                @endif
            </form>
        </div>
    @elseif($kelasOptions->isEmpty())
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-circle mr-1"></i>Tidak ada kelas yang bisa Anda kelola untuk mode ini pada tanggal yang dipilih.
        </div>
    @endif
@stop

@section('js')
<script>
    $(function () {
        $('.quick-status').on('click', function () {
            const targetStatus = $(this).data('status');
            $('.student-status').val(targetStatus);
        });
    });
</script>
@stop

@extends('adminlte::page')

@section('title', 'Detail Mata Pelajaran')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-eye"></i> Detail Mata Pelajaran</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.mapel.index') }}">Mata Pelajaran</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-info-circle"></i> Informasi Mata Pelajaran</h3>
            <div class="card-tools">
                <a href="{{ route('admin.mapel.edit', $mapel->id) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="{{ route('admin.mapel.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td width="40%" class="text-bold">Kode Mapel</td>
                            <td>: <span class="badge badge-info">{{ $mapel->kode_mapel }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-bold">Kode Jadwal Wakakur</td>
                            <td>: <span class="badge badge-primary">{{ $mapel->kode_jadwal ?? '-' }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-bold">Nama Mapel</td>
                            <td>: {{ $mapel->nama_mapel }}</td>
                        </tr>
                        <tr>
                            <td class="text-bold">Kurikulum</td>
                            <td>: {{ $mapel->kurikulum->nama_kurikulum ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-bold">Jurusan</td>
                            <td>: {{ $mapel->jurusan->nama_jurusan ?? 'Umum (Semua Jurusan)' }}</td>
                        </tr>
                        <tr>
                            <td class="text-bold">Kelompok</td>
                            <td>: {!! $mapel->kelompok_badge ?? '-' !!}</td>
                        </tr>
                        <tr>
                            <td class="text-bold">Kategori</td>
                            <td>: {{ $mapel->kategori ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td width="40%" class="text-bold">Jam Pelajaran/Minggu</td>
                            <td>: {{ $mapel->jam_pelajaran }} Jam</td>
                        </tr>
                        <tr>
                            <td class="text-bold">KKM</td>
                            <td>: {{ $mapel->kkm ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-bold">Tingkat</td>
                            <td>: {{ $mapel->tingkat_text }}</td>
                        </tr>
                        <tr>
                            <td class="text-bold">Semester</td>
                            <td>: {{ $mapel->semester_text }}</td>
                        </tr>
                        <tr>
                            <td class="text-bold">Status</td>
                            <td>: {!! $mapel->status_badge !!}</td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($mapel->deskripsi)
                <div class="row mt-3">
                    <div class="col-md-12">
                        <h6 class="text-bold">Deskripsi:</h6>
                        <p>{{ $mapel->deskripsi }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Madrasah Info --}}
    @if($mapel->is_mapel_agama || $mapel->is_rumpun_pai || $mapel->is_bahasa_arab)
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-mosque"></i> Informasi Madrasah (Kemenag)</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            @if($mapel->is_mapel_agama)
                                <tr>
                                    <td width="40%" class="text-bold">Mapel Agama</td>
                                    <td>: <span class="badge badge-success"><i class="fas fa-check"></i> Ya</span></td>
                                </tr>
                                <tr>
                                    <td class="text-bold">Jenis Agama</td>
                                    <td>: {!! $mapel->jenis_agama_badge !!}</td>
                                </tr>
                            @endif
                            @if($mapel->is_rumpun_pai)
                                <tr>
                                    <td class="text-bold">Rumpun PAI</td>
                                    <td>: <span class="badge badge-success"><i class="fas fa-check"></i> Ya</span></td>
                                </tr>
                                <tr>
                                    <td class="text-bold">Sub PAI</td>
                                    <td>: {!! $mapel->sub_pai_badge !!}</td>
                                </tr>
                            @endif
                            @if($mapel->is_bahasa_arab)
                                <tr>
                                    <td class="text-bold">Bahasa Arab</td>
                                    <td>: <span class="badge badge-success"><i class="fas fa-check"></i> Ya</span></td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Kurikulum Specific Info --}}
    @if($mapel->is_mapel_pilihan || $mapel->is_projek_p5 || $mapel->is_muatan_lokal || $mapel->capaian_pembelajaran)
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-graduation-cap"></i> Informasi Kurikulum Khusus</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        @if($mapel->is_mapel_pilihan)
                            <p><span class="badge badge-primary"><i class="fas fa-check"></i> Mapel Pilihan (Kurikulum Merdeka)</span></p>
                        @endif
                        @if($mapel->is_projek_p5)
                            <p><span class="badge badge-info"><i class="fas fa-project-diagram"></i> Projek P5 (Penguatan Profil Pelajar Pancasila)</span></p>
                        @endif
                        @if($mapel->is_muatan_lokal)
                            <p><span class="badge badge-warning"><i class="fas fa-map-marker-alt"></i> Muatan Lokal (KTSP)</span></p>
                        @endif
                    </div>
                </div>

                @if($mapel->capaian_pembelajaran)
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <h6 class="text-bold">Capaian Pembelajaran:</h6>
                            <p style="white-space: pre-line;">{{ $mapel->capaian_pembelajaran }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Audit Info --}}
    <div class="card card-secondary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-history"></i> Informasi Audit</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td width="40%" class="text-bold">Dibuat</td>
                            <td>: {{ $mapel->created_at->format('d M Y, H:i') }}</td>
                        </tr>
                        <tr>
                            <td class="text-bold">Terakhir Diupdate</td>
                            <td>: {{ $mapel->updated_at->format('d M Y, H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="card">
        <div class="card-footer">
            <a href="{{ route('admin.mapel.edit', $mapel->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <button type="button" class="btn btn-danger" id="btn-delete">
                <i class="fas fa-trash"></i> Hapus
            </button>
            <a href="{{ route('admin.mapel.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            $('#btn-delete').click(function() {
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data mata pelajaran '{{ $mapel->nama_mapel }}' akan dihapus!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('admin.mapel.destroy', $mapel->id) }}',
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: response.message,
                                        timer: 2000,
                                        showConfirmButton: false
                                    }).then(() => {
                                        window.location.href = '{{ route('admin.mapel.index') }}';
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal!',
                                        text: response.message
                                    });
                                }
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: 'Terjadi kesalahan saat menghapus data'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@stop

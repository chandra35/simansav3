@extends('adminlte::page')

@section('title', 'Detail Surat Keterangan')

@section('content_header')
    <h1><i class="fas fa-file-alt mr-2"></i>Detail Surat Keterangan</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Surat Keterangan</h3>
                    <div class="card-tools">
                        <span class="badge badge-{{ $suratKeterangan->status_badge }}">
                            {{ $suratKeterangan->status_label }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Nomor Surat</th>
                            <td><strong>{{ $suratKeterangan->nomor_surat }}</strong></td>
                        </tr>
                        <tr>
                            <th>Jenis Surat</th>
                            <td>{{ $suratKeterangan->template?->nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Surat</th>
                            <td>{{ $suratKeterangan->tanggal_surat?->format('d F Y') }}</td>
                        </tr>
                        <tr>
                            <th>Siswa</th>
                            <td>{{ $suratKeterangan->siswa?->nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>NISN</th>
                            <td>{{ $suratKeterangan->siswa?->nisn ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Keperluan</th>
                            <td>{{ $suratKeterangan->keperluan }}</td>
                        </tr>
                        @if($suratKeterangan->keterangan_tambahan)
                            <tr>
                                <th>Keterangan Tambahan</th>
                                <td>{{ $suratKeterangan->keterangan_tambahan }}</td>
                            </tr>
                        @endif
                        @if($suratKeterangan->catatan)
                            <tr>
                                <th>Catatan</th>
                                <td>{{ $suratKeterangan->catatan }}</td>
                            </tr>
                        @endif
                    </table>
                    
                    @if($suratKeterangan->status == 'approved')
                        <div class="alert alert-success mt-3">
                            <i class="fas fa-check-circle mr-2"></i>
                            <strong>Disetujui oleh:</strong> {{ $suratKeterangan->approvedBy?->name ?? '-' }}
                            pada {{ $suratKeterangan->approved_at?->format('d M Y H:i') }}
                        </div>
                    @endif
                    
                    @if($suratKeterangan->printed_at)
                        <div class="alert alert-info">
                            <i class="fas fa-print mr-2"></i>
                            <strong>Dicetak pada:</strong> {{ $suratKeterangan->printed_at->format('d M Y H:i') }}
                            ({{ $suratKeterangan->print_count }}x dicetak)
                        </div>
                    @endif
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.surat-keterangan.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                    
                    <div class="float-right">
                        @if($suratKeterangan->status == 'approved')
                            <a href="{{ route('admin.surat-keterangan.print', $suratKeterangan->id) }}" target="_blank" class="btn btn-primary">
                                <i class="fas fa-print mr-1"></i> Cetak PDF
                            </a>
                        @endif
                        @can('manage-layanan-surat')
                        @if($suratKeterangan->status == 'pending')
                            <button type="button" class="btn btn-success btn-approve" data-id="{{ $suratKeterangan->id }}">
                                <i class="fas fa-check mr-1"></i> Setujui
                            </button>
                        @endif
                        
                        <a href="{{ route('admin.surat-keterangan.edit', $suratKeterangan->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </a>
                        @endcan
                    </div>
                </div>
            </div>
            
            @if($suratKeterangan->status == 'approved' || $suratKeterangan->status == 'printed')
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-eye mr-1"></i> Preview Surat</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            Preview surat akan muncul saat dicetak. Klik tombol "Cetak PDF" untuk melihat dokumen lengkap.
                        </div>
                    </div>
                </div>
            @endif
        </div>
        
        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-user-graduate"></i> Data Siswa</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <th>Nama</th>
                            <td>{{ $suratKeterangan->siswa?->nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>NISN</th>
                            <td>{{ $suratKeterangan->siswa?->nisn ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>NIS</th>
                            <td>{{ $suratKeterangan->siswa?->nis ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Kelas</th>
                            <td>{{ $suratKeterangan->siswa?->kelasSaatIni?->nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>TTL</th>
                            <td>
                                {{ $suratKeterangan->siswa?->tempat_lahir ?? '-' }}, 
                                {{ $suratKeterangan->siswa?->tanggal_lahir?->format('d M Y') ?? '-' }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-history"></i> Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="timeline timeline-inverse">
                        <div class="time-label">
                            <span class="bg-secondary">{{ $suratKeterangan->created_at->format('d M Y') }}</span>
                        </div>
                        <div>
                            <i class="fas fa-file bg-primary"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="far fa-clock"></i> {{ $suratKeterangan->created_at->format('H:i') }}</span>
                                <h3 class="timeline-header">Surat Dibuat</h3>
                            </div>
                        </div>
                        
                        @if($suratKeterangan->approved_at)
                            <div>
                                <i class="fas fa-check bg-success"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="far fa-clock"></i> {{ $suratKeterangan->approved_at->format('H:i') }}</span>
                                    <h3 class="timeline-header">Disetujui</h3>
                                    <div class="timeline-body">
                                        Oleh {{ $suratKeterangan->approvedBy?->name ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        @if($suratKeterangan->printed_at)
                            <div>
                                <i class="fas fa-print bg-info"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="far fa-clock"></i> {{ $suratKeterangan->printed_at->format('H:i') }}</span>
                                    <h3 class="timeline-header">Dicetak ({{ $suratKeterangan->print_count }}x)</h3>
                                </div>
                            </div>
                        @endif
                        
                        <div>
                            <i class="far fa-clock bg-gray"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
$(function() {
    $('.btn-approve').on('click', function() {
        var id = $(this).data('id');
        
        Swal.fire({
            title: 'Setujui Surat?',
            text: "Surat akan dapat dicetak setelah disetujui",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Setujui!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('admin/surat-keterangan') }}/" + id + "/approve",
                    method: 'POST',
                    data: {_token: '{{ csrf_token() }}'},
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            location.reload();
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function() {
                        toastr.error('Terjadi kesalahan');
                    }
                });
            }
        });
    });
});
</script>
@stop

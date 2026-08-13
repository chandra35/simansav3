@extends('adminlte::page')

@section('title', 'Detail Pembayaran')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-receipt mr-2"></i>Detail Pembayaran</h1>
        <a href="{{ route('admin.pembayaran.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i>Informasi Transaksi</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">No. Transaksi</th>
                                    <td><strong>{{ $pembayaran->nomor_transaksi }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Tanggal Bayar</th>
                                    <td>{{ $pembayaran->tanggal_bayar?->format('d/m/Y') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Metode Pembayaran</th>
                                    <td>{{ $pembayaran->metode_label }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <span class="badge badge-{{ $pembayaran->status_badge }} badge-lg">
                                            {{ $pembayaran->status_label }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Jumlah Bayar</th>
                                    <td>
                                        <span class="text-success font-weight-bold" style="font-size: 1.2rem;">
                                            Rp {{ number_format($pembayaran->jumlah_bayar, 0, ',', '.') }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Diverifikasi Oleh</th>
                                    <td>{{ $pembayaran->verifiedBy?->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Waktu Verifikasi</th>
                                    <td>{{ $pembayaran->verified_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Catatan</th>
                                    <td>{{ $pembayaran->catatan ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-file-invoice mr-1"></i>Informasi Tagihan</h3>
                </div>
                <div class="card-body">
                    @if($pembayaran->tagihan)
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">NIS</th>
                                        <td>{{ $pembayaran->tagihan->siswa?->nis ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Nama Siswa</th>
                                        <td>{{ $pembayaran->tagihan->siswa?->nama ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Kelas</th>
                                        <td>{{ $pembayaran->tagihan->siswa?->kelasSaatIni?->kelas?->nama ?? '-' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">Jenis Pembayaran</th>
                                        <td>{{ $pembayaran->tagihan->jenisPembayaran?->nama ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Periode</th>
                                        <td>{{ $pembayaran->tagihan->bulan_label ?? '-' }} {{ $pembayaran->tagihan->tahun }}</td>
                                    </tr>
                                    <tr>
                                        <th>Status Tagihan</th>
                                        <td>
                                            <span class="badge badge-{{ $pembayaran->tagihan->status_badge }}">
                                                {{ $pembayaran->tagihan->status_label }}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <a href="{{ route('admin.pembayaran.tagihan.show', $pembayaran->tagihan->id) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-eye mr-1"></i> Lihat Tagihan
                        </a>
                    @else
                        <p class="text-muted">Data tagihan tidak ditemukan</p>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            @if($pembayaran->bukti_pembayaran)
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-file-image mr-1"></i>Bukti Pembayaran</h3>
                    </div>
                    <div class="card-body text-center">
                        @php
                            $ext = pathinfo($pembayaran->bukti_pembayaran, PATHINFO_EXTENSION);
                        @endphp
                        @if(in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif']))
                            <a href="{{ asset('storage/' . $pembayaran->bukti_pembayaran) }}" target="_blank">
                                <img src="{{ asset('storage/' . $pembayaran->bukti_pembayaran) }}" 
                                    alt="Bukti Pembayaran" class="img-fluid img-thumbnail" style="max-height: 400px;">
                            </a>
                        @else
                            <a href="{{ asset('storage/' . $pembayaran->bukti_pembayaran) }}" target="_blank" class="btn btn-primary btn-lg">
                                <i class="fas fa-file-pdf mr-1"></i> Lihat PDF
                            </a>
                        @endif
                    </div>
                </div>
            @endif
            
            @can('manage-keuangan')
            @if($pembayaran->status === 'pending')
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-tasks mr-1"></i>Aksi</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Pembayaran ini memerlukan verifikasi:</p>
                        <button type="button" class="btn btn-success btn-block mb-2 btn-verify">
                            <i class="fas fa-check mr-1"></i> Verifikasi Pembayaran
                        </button>
                        <button type="button" class="btn btn-danger btn-block btn-reject">
                            <i class="fas fa-times mr-1"></i> Tolak Pembayaran
                        </button>
                    </div>
                </div>
            @endif
            @endcan
            
            <div class="card">
                <div class="card-header bg-secondary">
                    <h3 class="card-title"><i class="fas fa-print mr-1"></i>Cetak</h3>
                </div>
                <div class="card-body">
                    <button type="button" class="btn btn-primary btn-block" onclick="window.print()">
                        <i class="fas fa-print mr-1"></i> Cetak Kwitansi
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Reject -->
    <div class="modal fade" id="modal-reject" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="form-reject">
                    @csrf
                    <div class="modal-header bg-danger">
                        <h5 class="modal-title text-white"><i class="fas fa-times mr-1"></i>Tolak Pembayaran</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea name="alasan" class="form-control" rows="3" required 
                                placeholder="Masukkan alasan penolakan pembayaran..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger"><i class="fas fa-times mr-1"></i>Tolak</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(function() {
            // Verify
            $('.btn-verify').click(function() {
                if (confirm('Apakah Anda yakin ingin memverifikasi pembayaran ini?')) {
                    $.ajax({
                        url: '{{ route("admin.pembayaran.verify", $pembayaran->id) }}',
                        type: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            if (response.success) {
                                toastr.success(response.message);
                                location.reload();
                            }
                        },
                        error: function(xhr) {
                            toastr.error(xhr.responseJSON?.message || 'Terjadi kesalahan');
                        }
                    });
                }
            });
            
            // Reject
            $('.btn-reject').click(function() {
                $('#modal-reject').modal('show');
            });
            
            $('#form-reject').submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: '{{ route("admin.pembayaran.reject", $pembayaran->id) }}',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#modal-reject').modal('hide');
                            toastr.success(response.message);
                            location.reload();
                        }
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Terjadi kesalahan');
                    }
                });
            });
        });
    </script>
@stop

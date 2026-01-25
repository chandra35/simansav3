@extends('adminlte::page')

@section('title', 'Detail Tagihan')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-file-invoice mr-2"></i>Detail Tagihan</h1>
        <a href="{{ route('admin.pembayaran.tagihan') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user-graduate mr-1"></i>Informasi Siswa</h3>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">NIS</th>
                            <td>{{ $tagihan->siswa?->nis ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Nama</th>
                            <td>{{ $tagihan->siswa?->nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Kelas</th>
                            <td>{{ $tagihan->siswa?->kelasSaatIni?->kelas?->nama ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-file-invoice-dollar mr-1"></i>Informasi Tagihan</h3>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Jenis Pembayaran</th>
                            <td>{{ $tagihan->jenisPembayaran?->nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Kategori</th>
                            <td>{{ $tagihan->jenisPembayaran?->kategori_label ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Periode</th>
                            <td>{{ $tagihan->bulan_label ?? '-' }} {{ $tagihan->tahun }}</td>
                        </tr>
                        <tr>
                            <th>Jatuh Tempo</th>
                            <td>{{ $tagihan->tanggal_jatuh_tempo?->format('d/m/Y') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge badge-{{ $tagihan->status_badge }} badge-lg">
                                    {{ $tagihan->status_label }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card card-success card-outline">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title"><i class="fas fa-money-bill-wave mr-1"></i>Rincian Pembayaran</h3>
                    @if($tagihan->status !== 'lunas')
                        <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#modal-bayar">
                            <i class="fas fa-plus mr-1"></i> Tambah Pembayaran
                        </button>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="info-box bg-gradient-primary">
                                <span class="info-box-icon"><i class="fas fa-file-invoice-dollar"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Tagihan</span>
                                    <span class="info-box-number">{{ $tagihan->nominal_tagihan_format }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-gradient-success">
                                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Terbayar</span>
                                    <span class="info-box-number">{{ $tagihan->nominal_terbayar_format }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-gradient-{{ $tagihan->sisa_tagihan > 0 ? 'danger' : 'secondary' }}">
                                <span class="info-box-icon"><i class="fas fa-exclamation-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Sisa Tagihan</span>
                                    <span class="info-box-number">{{ $tagihan->sisa_tagihan_format }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <h5><i class="fas fa-history mr-1"></i>Riwayat Pembayaran</h5>
                    <table class="table table-bordered table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th width="30">No</th>
                                <th>No. Transaksi</th>
                                <th>Tanggal</th>
                                <th>Metode</th>
                                <th>Jumlah</th>
                                <th>Status</th>
                                <th>Diverifikasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tagihan->pembayaran as $idx => $p)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td>
                                        <a href="{{ route('admin.pembayaran.show', $p->id) }}">
                                            {{ $p->nomor_transaksi }}
                                        </a>
                                    </td>
                                    <td>{{ $p->tanggal_bayar?->format('d/m/Y') ?? '-' }}</td>
                                    <td>{{ $p->metode_label }}</td>
                                    <td>Rp {{ number_format($p->jumlah_bayar, 0, ',', '.') }}</td>
                                    <td>
                                        <span class="badge badge-{{ $p->status_badge }}">{{ $p->status_label }}</span>
                                    </td>
                                    <td>
                                        @if($p->verified_at)
                                            {{ $p->verifiedBy?->name ?? '-' }}<br>
                                            <small class="text-muted">{{ $p->verified_at->format('d/m/Y H:i') }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Belum ada pembayaran</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Bayar -->
    <div class="modal fade" id="modal-bayar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="form-bayar" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="tagihan_id" value="{{ $tagihan->id }}">
                    <div class="modal-header bg-success">
                        <h5 class="modal-title text-white"><i class="fas fa-money-bill mr-1"></i>Tambah Pembayaran</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong>Sisa Tagihan:</strong> {{ $tagihan->sisa_tagihan_format }}
                        </div>
                        <div class="form-group">
                            <label>Jumlah Bayar <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input type="number" name="jumlah_bayar" class="form-control" 
                                    max="{{ $tagihan->sisa_tagihan }}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Metode Pembayaran <span class="text-danger">*</span></label>
                            <select name="metode_pembayaran" class="form-control" required>
                                <option value="">-- Pilih Metode --</option>
                                <option value="tunai">Tunai</option>
                                <option value="transfer">Transfer Bank</option>
                                <option value="qris">QRIS</option>
                                <option value="virtual_account">Virtual Account</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tanggal Bayar <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_bayar" class="form-control" 
                                value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="form-group">
                            <label>Bukti Pembayaran</label>
                            <input type="file" name="bukti_pembayaran" class="form-control-file" 
                                accept="image/*,.pdf">
                            <small class="text-muted">Format: JPG, PNG, PDF. Max: 5MB</small>
                        </div>
                        <div class="form-group">
                            <label>Catatan</label>
                            <textarea name="catatan" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i>Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(function() {
            $('#form-bayar').submit(function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                
                $.ajax({
                    url: '{{ route("admin.pembayaran.store") }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            $('#modal-bayar').modal('hide');
                            toastr.success(response.message);
                            location.reload();
                        }
                    },
                    error: function(xhr) {
                        var errors = xhr.responseJSON?.errors;
                        if (errors) {
                            var msg = Object.values(errors).flat().join('<br>');
                            toastr.error(msg);
                        } else {
                            toastr.error(xhr.responseJSON?.message || 'Terjadi kesalahan');
                        }
                    }
                });
            });
        });
    </script>
@stop

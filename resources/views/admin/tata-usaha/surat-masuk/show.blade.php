@extends('adminlte::page')
@section('title', 'Detail Surat Masuk')
@section('content_header')<div class="row mb-2"><div class="col-sm-6"><h1><i class="fas fa-file-invoice text-primary"></i> Detail Surat Masuk</h1></div><div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="{{ route('admin.tata-usaha.surat-masuk.index') }}">Surat Masuk</a></li><li class="breadcrumb-item active">{{ $item->nomor_berkas }}</li></ol></div></div>@stop
@section('content')<div class="tu-surat-detail"><div class="card bg-gradient-primary text-white mb-4"><div class="card-body"><div class="d-flex justify-content-between align-items-center flex-wrap"><div><h3 class="mb-1">{{ $item->nomor_berkas }}</h3><p class="mb-0">Record surat masuk tahun {{ $item->tahun }}</p></div><span class="badge badge-light text-dark px-3 py-2">{{ $item->status_label }}</span></div></div></div>
<div class="row"><div class="col-lg-7"><div class="card card-outline card-primary"><div class="card-header"><h3 class="card-title">Data yang Dicatat PTSP</h3><div class="card-tools"><a href="{{ route('admin.tata-usaha.surat-masuk.edit', $item) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit mr-1"></i>Edit</a></div></div><div class="card-body"><dl class="row mb-0"><dt class="col-sm-4">Berkas</dt><dd class="col-sm-8"><strong>{{ $item->nomor_berkas }}</strong></dd><dt class="col-sm-4">Kode Sistem</dt><dd class="col-sm-8"><code>{{ $item->kode_unik ?: '-' }}</code></dd><dt class="col-sm-4">Tanggal/Nomor</dt><dd class="col-sm-8">{{ $item->tanggal_nomor }}</dd><dt class="col-sm-4">Asal</dt><dd class="col-sm-8">{{ $item->asal }}</dd><dt class="col-sm-4">Isi Ringkasan</dt><dd class="col-sm-8">{!! nl2br(e($item->isi_ringkasan)) !!}</dd><dt class="col-sm-4">Diterima</dt><dd class="col-sm-8">{{ $item->diterima_tanggal?->format('d-m-Y') }}</dd></dl></div></div></div>
<div class="col-lg-5"><div class="card card-outline card-primary"><div class="card-header"><h3 class="card-title">Dokumen & Aksi</h3></div><div class="card-body"><button type="button" class="btn btn-info btn-block" data-toggle="modal" data-target="#disposisiPreviewModal"><i class="fas fa-eye mr-1"></i> Preview Lembar Disposisi</button>@if($item->print_path)<a href="{{ route('admin.tata-usaha.surat-masuk.download', [$item, 'print']) }}" data-no-overlay class="btn btn-outline-info btn-block"><i class="fas fa-file-pdf mr-1"></i> Download Print Tersimpan ({{ $item->print_count }}x)</a>@endif @if($item->surat_masuk_path)<a href="{{ route('admin.tata-usaha.surat-masuk.download', [$item, 'surat-masuk']) }}" data-no-overlay class="btn btn-outline-secondary btn-block"><i class="fas fa-paperclip mr-1"></i> Surat Masuk Asli</a>@endif<hr><h5 class="mb-2">Upload Hasil Disposisi</h5><p class="text-muted small">Unggah lembar yang sudah diisi manual oleh Kepala TU/Kepala Madrasah.</p><form method="post" enctype="multipart/form-data" action="{{ route('admin.tata-usaha.surat-masuk.upload-result', $item) }}">@csrf<input type="file" name="hasil_disposisi" class="form-control-file mb-2" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required><button class="btn btn-success btn-block"><i class="fas fa-upload mr-1"></i> Simpan Hasil Disposisi</button></form>@if($item->hasil_disposisi_path)<div class="alert alert-success mt-3 mb-0"><i class="fas fa-check-circle mr-1"></i> Selesai<br><a href="{{ route('admin.tata-usaha.surat-masuk.download', [$item, 'hasil-disposisi']) }}" data-no-overlay>{{ $item->hasil_disposisi_nama }}</a><br><small>{{ $item->hasil_disposisi_uploaded_at?->timezone('Asia/Jakarta')->format('d-m-Y H:i').' WIB' }}</small></div>@endif</div></div></div></div></div>

<div class="modal fade" id="disposisiPreviewModal" tabindex="-1" role="dialog" aria-labelledby="disposisiPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content disposisi-preview-modal">
            <div class="modal-header">
                <div><h5 class="modal-title mb-1" id="disposisiPreviewModalLabel"><i class="fas fa-file-pdf text-danger mr-2"></i>Preview Lembar Disposisi</h5><small class="text-muted">Berkas {{ $item->nomor_berkas }} · {{ $item->asal }}</small></div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body p-0"><div class="disposisi-preview-frame"><div class="disposisi-preview-loading"><i class="fas fa-spinner fa-spin mr-2"></i>Menyiapkan preview PDF...</div><iframe id="disposisiPreviewFrame" title="Preview Lembar Disposisi" loading="lazy"></iframe></div></div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-dismiss="modal"><i class="fas fa-times mr-1"></i>Tutup</button><a href="{{ route('admin.tata-usaha.surat-masuk.print', $item) }}" target="_blank" data-no-overlay class="btn btn-primary"><i class="fas fa-print mr-1"></i>Print Disposisi</a></div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .disposisi-preview-modal{height:92vh}.disposisi-preview-modal .modal-body{min-height:0}.disposisi-preview-frame{position:relative;height:100%;min-height:520px;background:#e9eef5}.disposisi-preview-frame iframe{display:block;width:100%;height:100%;min-height:520px;border:0;background:#fff}.disposisi-preview-loading{position:absolute;z-index:1;top:50%;left:50%;transform:translate(-50%,-50%);color:#64748b;font-weight:600;white-space:nowrap}.disposisi-preview-frame iframe[src]{position:relative;z-index:2}@media(max-width:767.98px){.disposisi-preview-modal{height:88vh}.disposisi-preview-frame,.disposisi-preview-frame iframe{min-height:420px}}
</style>
@stop

@section('js')
<script>
$(function(){
    const modal=$('#disposisiPreviewModal'),frame=$('#disposisiPreviewFrame'),previewUrl=@json(route('admin.tata-usaha.surat-masuk.print',$item));
    modal.on('show.bs.modal',function(){frame.attr('src',previewUrl);});
    modal.on('hidden.bs.modal',function(){frame.attr('src','');window.hideAppGlobalOverlay&&window.hideAppGlobalOverlay();});
    modal.on('shown.bs.modal',function(){window.hideAppGlobalOverlay&&window.hideAppGlobalOverlay();});
    $(document).on('click','[data-no-overlay]',function(){window.hideAppGlobalOverlay&&window.hideAppGlobalOverlay();});
});
</script>
@stop

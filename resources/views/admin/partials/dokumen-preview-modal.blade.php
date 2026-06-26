<div class="modal fade" id="adminDokumenPreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="adminDokumenPreviewTitle">
                    <i class="fas fa-eye mr-1"></i> Preview Dokumen
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0 bg-light" style="min-height: 70vh;">
                <div id="adminDokumenPreviewLoading" class="text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-3x text-info"></i>
                    <p class="mt-3 mb-0 text-muted">Memuat dokumen...</p>
                </div>
                <div id="adminDokumenPreviewImage" class="text-center p-3" style="display: none;">
                    <img src="" alt="Preview dokumen" class="img-fluid rounded shadow-sm" style="max-height: 70vh;">
                </div>
                <div id="adminDokumenPreviewPdf" style="display: none;">
                    <iframe src="" frameborder="0" style="width: 100%; height: 70vh;"></iframe>
                </div>
                <div id="adminDokumenPreviewUnsupported" class="text-center py-5 px-3" style="display: none;">
                    <i class="fas fa-file-download fa-3x text-secondary"></i>
                    <h5 class="mt-3">Preview tidak tersedia</h5>
                    <p class="text-muted mb-0">Gunakan tombol download untuk membuka dokumen ini di perangkat Anda.</p>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" id="adminDokumenPreviewDownload" class="btn btn-success">
                    <i class="fas fa-download mr-1"></i> Download Dokumen
                </a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    function resetAdminDokumenPreview() {
        $('#adminDokumenPreviewLoading').show();
        $('#adminDokumenPreviewImage').hide().find('img').attr('src', '');
        $('#adminDokumenPreviewPdf').hide().find('iframe').attr('src', '');
        $('#adminDokumenPreviewUnsupported').hide();
    }

    $(document).on('click', '.js-preview-admin-dokumen', function () {
        const $button = $(this);
        const previewUrl = $button.data('preview-url');
        const downloadUrl = $button.data('download-url') || previewUrl;
        const title = $button.data('title') || 'Preview Dokumen';
        const extension = String($button.data('extension') || '').toLowerCase();

        resetAdminDokumenPreview();
        $('#adminDokumenPreviewTitle').html('<i class="fas fa-eye mr-1"></i> ' + title);
        $('#adminDokumenPreviewDownload').attr('href', downloadUrl);
        $('#adminDokumenPreviewModal').modal('show');

        if (extension === 'pdf') {
            $('#adminDokumenPreviewPdf iframe').attr('src', previewUrl);
            $('#adminDokumenPreviewLoading').hide();
            $('#adminDokumenPreviewPdf').show();
            return;
        }

        if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension)) {
            const image = new Image();
            image.onload = function () {
                $('#adminDokumenPreviewImage img').attr('src', previewUrl);
                $('#adminDokumenPreviewLoading').hide();
                $('#adminDokumenPreviewImage').show();
            };
            image.onerror = function () {
                $('#adminDokumenPreviewLoading').hide();
                $('#adminDokumenPreviewUnsupported').show();
            };
            image.src = previewUrl;
            return;
        }

        $('#adminDokumenPreviewLoading').hide();
        $('#adminDokumenPreviewUnsupported').show();
    });

    $('#adminDokumenPreviewModal').on('hidden.bs.modal', resetAdminDokumenPreview);
})();
</script>

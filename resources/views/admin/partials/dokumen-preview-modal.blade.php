<style>
    #adminDokumenPreviewModal .modal-dialog {
        max-width: min(96vw, 1400px);
    }

    #adminDokumenPreviewModal .modal-content {
        display: flex;
        flex-direction: column;
        height: 92vh;
    }

    #adminDokumenPreviewModal .modal-body {
        min-height: 0;
        overflow: hidden;
    }

    #adminDokumenPreviewModal .admin-doc-preview-frame {
        width: 100%;
        height: 100%;
        min-height: 78vh;
    }

    #adminDokumenPreviewModal .admin-doc-preview-image-wrap {
        height: 100%;
        min-height: 78vh;
        overflow: auto;
        background: #111827;
    }

    #adminDokumenPreviewModal .admin-doc-preview-image-wrap img {
        max-width: none;
        width: auto;
        height: auto;
        max-height: none;
        object-fit: contain;
    }

    #adminDokumenPreviewModal .admin-doc-preview-image-wrap.is-fit img {
        max-width: 100%;
        max-height: 78vh;
        width: auto;
        height: auto;
    }
</style>

<div class="modal fade" id="adminDokumenPreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="adminDokumenPreviewTitle">
                    <i class="fas fa-eye mr-1"></i> Preview Dokumen
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0 bg-light flex-grow-1">
                <div id="adminDokumenPreviewLoading" class="text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-3x text-info"></i>
                    <p class="mt-3 mb-0 text-muted">Memuat dokumen...</p>
                </div>
                <div id="adminDokumenPreviewImage" class="admin-doc-preview-image-wrap is-fit text-center p-3" style="display: none;">
                    <img src="" alt="Preview dokumen" class="rounded shadow-sm">
                </div>
                <div id="adminDokumenPreviewPdf" style="display: none;">
                    <iframe src="" frameborder="0" class="admin-doc-preview-frame"></iframe>
                </div>
                <div id="adminDokumenPreviewBrowser" style="display: none;">
                    <iframe src="" frameborder="0" class="admin-doc-preview-frame"></iframe>
                </div>
                <div id="adminDokumenPreviewUnsupported" class="text-center py-5 px-3" style="display: none;">
                    <i class="fas fa-file-download fa-3x text-secondary"></i>
                    <h5 class="mt-3">Preview tidak tersedia</h5>
                    <p class="text-muted mb-0">Gunakan tombol download untuk membuka dokumen ini di perangkat Anda.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="adminDokumenPreviewFitToggle" class="btn btn-outline-primary" style="display: none;">
                    <i class="fas fa-search-plus mr-1"></i> Ukuran Asli
                </button>
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
    const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    const browserPreviewExtensions = ['txt', 'csv', 'json', 'xml', 'html', 'htm', 'svg'];

    function showElement(id) {
        const element = document.getElementById(id);
        if (element) element.style.display = '';
    }

    function hideElement(id) {
        const element = document.getElementById(id);
        if (element) element.style.display = 'none';
    }

    function setHtml(id, html) {
        const element = document.getElementById(id);
        if (element) element.innerHTML = html;
    }

    function setAttr(selector, attr, value) {
        const element = document.querySelector(selector);
        if (element) element.setAttribute(attr, value || '');
    }

    function resetAdminDokumenPreview() {
        showElement('adminDokumenPreviewLoading');
        hideElement('adminDokumenPreviewImage');
        hideElement('adminDokumenPreviewPdf');
        hideElement('adminDokumenPreviewBrowser');
        hideElement('adminDokumenPreviewUnsupported');
        hideElement('adminDokumenPreviewFitToggle');
        setAttr('#adminDokumenPreviewImage img', 'src', '');
        setAttr('#adminDokumenPreviewPdf iframe', 'src', '');
        setAttr('#adminDokumenPreviewBrowser iframe', 'src', '');
        const imageWrap = document.getElementById('adminDokumenPreviewImage');
        if (imageWrap) imageWrap.classList.add('is-fit');
        setHtml('adminDokumenPreviewFitToggle', '<i class="fas fa-search-plus mr-1"></i> Ukuran Asli');
    }

    function showPreviewModal() {
        if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
            window.jQuery('#adminDokumenPreviewModal').modal('show');
            return;
        }

        const modal = document.getElementById('adminDokumenPreviewModal');
        if (modal) modal.style.display = 'block';
    }

    function hidePreviewModal() {
        if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
            window.jQuery('#adminDokumenPreviewModal').modal('hide');
            return;
        }

        const modal = document.getElementById('adminDokumenPreviewModal');
        if (modal) modal.style.display = 'none';
    }

    document.addEventListener('click', function (event) {
        const button = event.target.closest('.js-preview-admin-dokumen');
        if (!button) return;

        const previewUrl = button.dataset.previewUrl;
        const downloadUrl = button.dataset.downloadUrl || previewUrl;
        const title = button.dataset.title || 'Preview Dokumen';
        const extension = String(button.dataset.extension || '').toLowerCase();
        const mimeType = String(button.dataset.mimeType || '').toLowerCase();

        resetAdminDokumenPreview();
        setHtml('adminDokumenPreviewTitle', '<i class="fas fa-eye mr-1"></i> ' + title);
        setAttr('#adminDokumenPreviewDownload', 'href', downloadUrl);
        showPreviewModal();

        if (extension === 'pdf' || mimeType === 'application/pdf') {
            setAttr('#adminDokumenPreviewPdf iframe', 'src', previewUrl);
            hideElement('adminDokumenPreviewLoading');
            showElement('adminDokumenPreviewPdf');
            return;
        }

        if (imageExtensions.includes(extension) || mimeType.startsWith('image/')) {
            const image = new Image();
            image.onload = function () {
                setAttr('#adminDokumenPreviewImage img', 'src', previewUrl);
                hideElement('adminDokumenPreviewLoading');
                showElement('adminDokumenPreviewImage');
                showElement('adminDokumenPreviewFitToggle');
            };
            image.onerror = function () {
                hideElement('adminDokumenPreviewLoading');
                showElement('adminDokumenPreviewUnsupported');
            };
            image.src = previewUrl;
            return;
        }

        if (mimeType.startsWith('text/') || browserPreviewExtensions.includes(extension)) {
            setAttr('#adminDokumenPreviewBrowser iframe', 'src', previewUrl);
            hideElement('adminDokumenPreviewLoading');
            showElement('adminDokumenPreviewBrowser');
            return;
        }

        hideElement('adminDokumenPreviewLoading');
        showElement('adminDokumenPreviewUnsupported');
    });

    document.addEventListener('click', function (event) {
        const toggleButton = event.target.closest('#adminDokumenPreviewFitToggle');
        if (!toggleButton) return;

        const imageWrap = document.getElementById('adminDokumenPreviewImage');
        if (!imageWrap) return;

        const isFit = imageWrap.classList.toggle('is-fit');
        toggleButton.innerHTML = isFit
            ? '<i class="fas fa-search-plus mr-1"></i> Ukuran Asli'
            : '<i class="fas fa-compress mr-1"></i> Sesuaikan Layar';
    });

    document.addEventListener('click', function (event) {
        const closeButton = event.target.closest('#adminDokumenPreviewModal [data-dismiss="modal"]');
        if (!closeButton) return;

        hidePreviewModal();
        resetAdminDokumenPreview();
    });

    if (window.jQuery) {
        window.jQuery(function () {
            window.jQuery('#adminDokumenPreviewModal').on('hidden.bs.modal', resetAdminDokumenPreview);
        });
    }
})();
</script>

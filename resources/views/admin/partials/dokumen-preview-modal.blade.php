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
        transform-origin: top left;
    }

    #adminDokumenPreviewPdf,
    #adminDokumenPreviewBrowser {
        height: 100%;
        min-height: 78vh;
        overflow: auto;
        background: #ffffff;
    }

    #adminDokumenPreviewModal .admin-doc-preview-image-wrap {
        height: 100%;
        min-height: 78vh;
        overflow: auto;
        background: #111827;
        cursor: default;
    }

    #adminDokumenPreviewModal .admin-doc-preview-image-wrap img {
        max-width: none;
        width: auto;
        height: auto;
        max-height: none;
        object-fit: contain;
        transform-origin: center center;
        transition: transform .15s ease;
        user-select: none;
    }

    #adminDokumenPreviewModal .admin-doc-preview-image-wrap.is-fit img {
        max-width: 100%;
        max-height: 78vh;
        width: auto;
        height: auto;
    }

    #adminDokumenPreviewModal .admin-doc-preview-image-wrap.is-pan {
        cursor: grab;
    }

    #adminDokumenPreviewModal .admin-doc-preview-image-wrap.is-pan.is-dragging {
        cursor: grabbing;
    }

    #adminDokumenPreviewModal .admin-doc-preview-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: .35rem;
        align-items: center;
        padding: .45rem .75rem;
        border-bottom: 1px solid #dbe7f4;
        background: #f8fafc;
    }

    #adminDokumenPreviewModal .admin-doc-preview-toolbar .btn {
        min-width: 38px;
    }

    #adminDokumenPreviewModal .admin-doc-preview-zoom-label {
        min-width: 56px;
        text-align: center;
        font-weight: 700;
        color: #334155;
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
            <div id="adminDokumenPreviewToolbar" class="admin-doc-preview-toolbar" style="display: none;">
                <button type="button" class="btn btn-sm btn-outline-secondary js-admin-doc-tool" data-action="zoom-out" title="Zoom out">
                    <i class="fas fa-search-minus"></i>
                </button>
                <span id="adminDokumenPreviewZoomLabel" class="admin-doc-preview-zoom-label">100%</span>
                <button type="button" class="btn btn-sm btn-outline-secondary js-admin-doc-tool" data-action="zoom-in" title="Zoom in">
                    <i class="fas fa-search-plus"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary js-admin-doc-tool" data-action="rotate-left" title="Putar kiri">
                    <i class="fas fa-undo"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary js-admin-doc-tool" data-action="rotate-right" title="Putar kanan">
                    <i class="fas fa-redo"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary js-admin-doc-tool" data-action="reset" title="Reset tampilan">
                    <i class="fas fa-compress-arrows-alt"></i>
                </button>
                <button type="button" id="adminDokumenPreviewPanToggle" class="btn btn-sm btn-outline-secondary js-admin-doc-tool" data-action="pan" title="Mode geser dokumen" style="display: none;">
                    <i class="fas fa-hand-paper"></i>
                </button>
                <small id="adminDokumenPreviewSelectHint" class="text-muted ml-auto">
                    PDF teks asli bisa diblok/select dari viewer PDF. Gambar/scan perlu OCR agar teksnya bisa dipilih.
                </small>
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
                <a href="#" id="adminDokumenPreviewDownload" class="btn btn-success" data-no-overlay download>
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
    const transformState = {
        scale: 1,
        rotate: 0,
        mode: null,
        pan: false,
        dragging: false,
        startX: 0,
        startY: 0,
        scrollLeft: 0,
        scrollTop: 0,
    };

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

    function resetTransform() {
        transformState.scale = 1;
        transformState.rotate = 0;
        transformState.pan = false;
        transformState.dragging = false;
        updateTransform();
    }

    function getActivePreviewElement() {
        if (transformState.mode === 'image') {
            return document.querySelector('#adminDokumenPreviewImage img');
        }

        if (transformState.mode === 'pdf') {
            return document.querySelector('#adminDokumenPreviewPdf iframe');
        }

        if (transformState.mode === 'browser') {
            return document.querySelector('#adminDokumenPreviewBrowser iframe');
        }

        return null;
    }

    function updateTransform() {
        const element = getActivePreviewElement();
        const transform = 'scale(' + transformState.scale + ') rotate(' + transformState.rotate + 'deg)';
        if (element) {
            element.style.transform = transform;
        }

        setHtml('adminDokumenPreviewZoomLabel', Math.round(transformState.scale * 100) + '%');

        const panToggle = document.getElementById('adminDokumenPreviewPanToggle');
        if (panToggle) {
            panToggle.classList.toggle('active', transformState.pan);
            panToggle.classList.toggle('btn-secondary', transformState.pan);
            panToggle.classList.toggle('btn-outline-secondary', !transformState.pan);
        }

        const imageWrap = document.getElementById('adminDokumenPreviewImage');
        if (imageWrap) {
            imageWrap.classList.toggle('is-pan', transformState.pan);
            imageWrap.classList.toggle('is-dragging', transformState.dragging);
        }
    }

    function resetAdminDokumenPreview() {
        showElement('adminDokumenPreviewLoading');
        hideElement('adminDokumenPreviewImage');
        hideElement('adminDokumenPreviewPdf');
        hideElement('adminDokumenPreviewBrowser');
        hideElement('adminDokumenPreviewUnsupported');
        hideElement('adminDokumenPreviewFitToggle');
        hideElement('adminDokumenPreviewToolbar');
        hideElement('adminDokumenPreviewPanToggle');
        setAttr('#adminDokumenPreviewImage img', 'src', '');
        setAttr('#adminDokumenPreviewPdf iframe', 'src', '');
        setAttr('#adminDokumenPreviewBrowser iframe', 'src', '');
        const imageWrap = document.getElementById('adminDokumenPreviewImage');
        if (imageWrap) imageWrap.classList.add('is-fit');
        setHtml('adminDokumenPreviewFitToggle', '<i class="fas fa-search-plus mr-1"></i> Ukuran Asli');
        transformState.mode = null;
        resetTransform();
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

    function downloadWithoutLeavingPage(url) {
        if (!url || url === '#') return;

        let frame = document.getElementById('adminDokumenDownloadFrame');
        if (!frame) {
            frame = document.createElement('iframe');
            frame.id = 'adminDokumenDownloadFrame';
            frame.name = 'adminDokumenDownloadFrame';
            frame.style.display = 'none';
            document.body.appendChild(frame);
        }

        frame.src = url;
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
            transformState.mode = 'pdf';
            setAttr('#adminDokumenPreviewPdf iframe', 'src', previewUrl);
            hideElement('adminDokumenPreviewLoading');
            showElement('adminDokumenPreviewPdf');
            showElement('adminDokumenPreviewToolbar');
            updateTransform();
            return;
        }

        if (imageExtensions.includes(extension) || mimeType.startsWith('image/')) {
            const image = new Image();
            image.onload = function () {
                transformState.mode = 'image';
                setAttr('#adminDokumenPreviewImage img', 'src', previewUrl);
                hideElement('adminDokumenPreviewLoading');
                showElement('adminDokumenPreviewImage');
                showElement('adminDokumenPreviewFitToggle');
                showElement('adminDokumenPreviewToolbar');
                showElement('adminDokumenPreviewPanToggle');
                updateTransform();
            };
            image.onerror = function () {
                hideElement('adminDokumenPreviewLoading');
                showElement('adminDokumenPreviewUnsupported');
            };
            image.src = previewUrl;
            return;
        }

        if (mimeType.startsWith('text/') || browserPreviewExtensions.includes(extension)) {
            transformState.mode = 'browser';
            setAttr('#adminDokumenPreviewBrowser iframe', 'src', previewUrl);
            hideElement('adminDokumenPreviewLoading');
            showElement('adminDokumenPreviewBrowser');
            showElement('adminDokumenPreviewToolbar');
            updateTransform();
            return;
        }

        hideElement('adminDokumenPreviewLoading');
        showElement('adminDokumenPreviewUnsupported');
    });

    document.addEventListener('click', function (event) {
        const toolButton = event.target.closest('.js-admin-doc-tool');
        if (!toolButton) return;

        const action = toolButton.dataset.action;
        if (action === 'zoom-in') {
            transformState.scale = Math.min(4, +(transformState.scale + .15).toFixed(2));
        } else if (action === 'zoom-out') {
            transformState.scale = Math.max(.25, +(transformState.scale - .15).toFixed(2));
        } else if (action === 'rotate-left') {
            transformState.rotate = (transformState.rotate - 90) % 360;
        } else if (action === 'rotate-right') {
            transformState.rotate = (transformState.rotate + 90) % 360;
        } else if (action === 'reset') {
            resetTransform();
            return;
        } else if (action === 'pan') {
            transformState.pan = !transformState.pan;
        }

        updateTransform();
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

    document.addEventListener('mousedown', function (event) {
        const imageWrap = event.target.closest('#adminDokumenPreviewImage');
        if (!imageWrap || !transformState.pan) return;

        transformState.dragging = true;
        transformState.startX = event.pageX - imageWrap.offsetLeft;
        transformState.startY = event.pageY - imageWrap.offsetTop;
        transformState.scrollLeft = imageWrap.scrollLeft;
        transformState.scrollTop = imageWrap.scrollTop;
        updateTransform();
        event.preventDefault();
    });

    document.addEventListener('mousemove', function (event) {
        const imageWrap = document.getElementById('adminDokumenPreviewImage');
        if (!imageWrap || !transformState.dragging) return;

        const x = event.pageX - imageWrap.offsetLeft;
        const y = event.pageY - imageWrap.offsetTop;
        imageWrap.scrollLeft = transformState.scrollLeft - (x - transformState.startX);
        imageWrap.scrollTop = transformState.scrollTop - (y - transformState.startY);
    });

    document.addEventListener('mouseup', function () {
        if (!transformState.dragging) return;

        transformState.dragging = false;
        updateTransform();
    });

    document.addEventListener('click', function (event) {
        const downloadButton = event.target.closest('#adminDokumenPreviewDownload');
        if (!downloadButton) return;

        event.preventDefault();
        downloadWithoutLeavingPage(downloadButton.getAttribute('href'));
        window.setTimeout(function () {
            hidePreviewModal();
        }, 150);
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

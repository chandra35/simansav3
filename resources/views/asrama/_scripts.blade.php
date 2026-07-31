<div class="asrama-progress" id="asramaProgress" role="status" aria-live="polite">
    <div class="asrama-progress__box">
        <div class="asrama-progress__icon"><i class="fas fa-sync-alt fa-spin"></i></div>
        <h5 class="font-weight-bold mb-1" id="asramaProgressTitle">Memproses data</h5>
        <p class="text-muted mb-3" id="asramaProgressText">Mohon tunggu, jangan menutup halaman.</p>
        <div class="progress"><div class="progress-bar"></div></div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.jQuery && jQuery.fn.select2) {
        jQuery('.asrama-select').each(function () {
            const modal = jQuery(this).closest('.modal');
            jQuery(this).select2({
                theme: 'bootstrap4',
                width: '100%',
                allowClear: Boolean(jQuery(this).data('allow-clear')),
                placeholder: jQuery(this).data('placeholder') || 'Pilih data',
                dropdownParent: modal.length ? modal : jQuery(document.body)
            });
        });
    }
    document.querySelectorAll('form[data-asrama-loading]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) return;
            const confirmText = form.dataset.confirm;
            if (confirmText && !window.confirm(confirmText)) {
                event.preventDefault();
                return;
            }
            const overlay = document.getElementById('asramaProgress');
            document.getElementById('asramaProgressTitle').textContent = form.dataset.loadingTitle || 'Memproses data';
            document.getElementById('asramaProgressText').textContent = form.dataset.loadingText || 'Mohon tunggu, jangan menutup halaman.';
            overlay.classList.add('is-visible');
            form.querySelectorAll('button[type="submit"]').forEach(function (button) {
                button.disabled = true;
                button.dataset.originalHtml = button.innerHTML;
                button.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-1"></i> Memproses...';
            });
        });
    });
    document.querySelectorAll('[data-check-all]').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            document.querySelectorAll(checkbox.dataset.checkAll).forEach(function (target) {
                target.checked = checkbox.checked;
            });
        });
    });
});
</script>

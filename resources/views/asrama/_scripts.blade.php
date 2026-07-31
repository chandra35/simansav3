<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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
    // Overlay progress ditangani global oleh master.blade.php; di sini hanya konfirmasi + anti double-submit.
    document.querySelectorAll('form[data-asrama-loading]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) return;
            const confirmText = form.dataset.confirm;
            if (confirmText && !window.confirm(confirmText)) {
                event.preventDefault();
                return;
            }
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

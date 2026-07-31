<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    document.querySelectorAll('[data-check-all]').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            document.querySelectorAll(checkbox.dataset.checkAll).forEach(function (target) {
                target.checked = checkbox.checked;
            });
        });
    });
});

// Capture phase: berjalan sebelum listener overlay global di master + tetap bekerja untuk form hasil AJAX.
document.addEventListener('submit', function (event) {
    const form = event.target.closest ? event.target.closest('form[data-asrama-loading]') : null;
    if (!form) return;

    const confirmText = form.dataset.confirm;
    if (confirmText && form.dataset.confirmed !== '1') {
        event.preventDefault();
        const ask = window.Swal
            ? Swal.fire({
                title: 'Konfirmasi',
                text: confirmText,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, lanjutkan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc3545',
                reverseButtons: true
            }).then(function (result) { return result.isConfirmed; })
            : Promise.resolve(window.confirm(confirmText));
        ask.then(function (ok) {
            if (!ok) return;
            form.dataset.confirmed = '1';
            if (form.requestSubmit) form.requestSubmit(); else form.submit();
        });
        return;
    }

    form.querySelectorAll('button[type="submit"]').forEach(function (button) {
        button.disabled = true;
        button.dataset.originalHtml = button.innerHTML;
        button.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-1"></i> Memproses...';
    });
}, true);
</script>

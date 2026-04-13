{{-- SMART-Q Progress Overlay (reusable partial) --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
.smartq-overlay {
    position: fixed;
    inset: 0;
    z-index: 99999;
    display: none;
}
.smartq-overlay.active { display: block; }
.smartq-overlay-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    animation: sqOverlayFadeIn .3s ease;
}
.smartq-overlay-content {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    animation: sqOverlaySlideUp .4s ease;
}
.smartq-overlay-spinner {
    width: 72px; height: 72px;
    margin: 0 auto 24px;
    position: relative;
}
.sq-ring {
    width: 72px; height: 72px;
    border: 3px solid transparent;
    border-top-color: #fff;
    border-radius: 50%;
    position: absolute;
    top: 0; left: 0;
    animation: sqRingSpin .9s linear infinite;
}
.sq-ring-2 {
    width: 52px; height: 52px;
    top: 10px; left: 10px;
    border-top-color: rgba(255,255,255,.35);
    animation-direction: reverse;
    animation-duration: 1.2s;
}
.sq-ring-3 {
    width: 34px; height: 34px;
    top: 19px; left: 19px;
    border-top-color: rgba(255,255,255,.18);
    animation-duration: 1.6s;
}
.smartq-overlay-icon {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    font-size: 1.2rem;
    color: rgba(255,255,255,.85);
    animation: sqPulse 2s ease-in-out infinite;
}
.smartq-overlay-title {
    color: #fff;
    font-size: 1.25rem;
    font-weight: 700;
    letter-spacing: .3px;
    margin-bottom: 6px;
    text-shadow: 0 2px 6px rgba(0,0,0,.3);
    transition: opacity .25s ease;
}
.smartq-overlay-subtitle {
    color: rgba(255,255,255,.6);
    font-size: .85rem;
    font-weight: 400;
}
.smartq-overlay-progress {
    margin: 18px auto 0;
    width: 240px;
    height: 4px;
    border-radius: 999px;
    background: rgba(255,255,255,.15);
    overflow: hidden;
}
.smartq-overlay-progress-bar {
    height: 100%;
    width: 30%;
    border-radius: 999px;
    background: linear-gradient(90deg, rgba(255,255,255,.1), rgba(255,255,255,.7), rgba(255,255,255,.1));
    animation: sqProgressSlide 1.6s ease-in-out infinite;
}

@keyframes sqOverlayFadeIn {
    from { opacity: 0; } to { opacity: 1; }
}
@keyframes sqOverlaySlideUp {
    from { opacity: 0; transform: translate(-50%, -44%); }
    to { opacity: 1; transform: translate(-50%, -50%); }
}
@keyframes sqRingSpin {
    to { transform: rotate(360deg); }
}
@keyframes sqPulse {
    0%, 100% { opacity: .85; transform: translate(-50%,-50%) scale(1); }
    50% { opacity: .5; transform: translate(-50%,-50%) scale(1.1); }
}
@keyframes sqProgressSlide {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(400%); }
}
</style>

<div id="smartqOverlay" class="smartq-overlay">
    <div class="smartq-overlay-backdrop"></div>
    <div class="smartq-overlay-content">
        <div class="smartq-overlay-spinner">
            <div class="sq-ring"></div>
            <div class="sq-ring sq-ring-2"></div>
            <div class="sq-ring sq-ring-3"></div>
            <div class="smartq-overlay-icon" id="sqOverlayIcon">
                <i class="fas fa-star"></i>
            </div>
        </div>
        <div class="smartq-overlay-title" id="sqOverlayTitle">Memproses...</div>
        <div class="smartq-overlay-subtitle" id="sqOverlaySubtitle">Mohon tunggu, jangan tutup halaman ini</div>
        <div class="smartq-overlay-progress">
            <div class="smartq-overlay-progress-bar"></div>
        </div>
    </div>
</div>

<script>
function showSmartqOverlay(title, subtitle, icon) {
    document.getElementById('sqOverlayTitle').textContent = title || 'Memproses...';
    document.getElementById('sqOverlaySubtitle').textContent = subtitle || 'Mohon tunggu, jangan tutup halaman ini';
    if (icon) document.getElementById('sqOverlayIcon').innerHTML = '<i class="fas fa-' + icon + '"></i>';
    document.getElementById('smartqOverlay').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function hideSmartqOverlay() {
    document.getElementById('smartqOverlay').classList.remove('active');
    document.body.style.overflow = '';
}

function smartqOverlayMessages(messages, interval) {
    let idx = 0;
    const el = document.getElementById('sqOverlayTitle');
    return setInterval(function() {
        idx = Math.min(idx + 1, messages.length - 1);
        el.style.opacity = '0';
        setTimeout(function() {
            el.textContent = messages[idx];
            el.style.opacity = '1';
        }, 200);
    }, interval || 2000);
}

// Auto-hide on back/forward navigation
window.addEventListener('pageshow', function(e) {
    if (e.persisted) hideSmartqOverlay();
});

/**
 * Professional SweetAlert2 confirmation for SMART-Q forms.
 * Usage: smartqConfirm(formElement, { title, text, icon, confirmText, cancelText, confirmColor })
 *   or:  smartqConfirm(formElement, { ... }).then(fn) for custom post-confirm logic
 */
function smartqConfirm(form, opts) {
    opts = opts || {};
    return Swal.fire({
        title: opts.title || 'Konfirmasi',
        html: opts.text || opts.html || 'Apakah Anda yakin?',
        icon: opts.icon || 'warning',
        showCancelButton: true,
        confirmButtonText: opts.confirmText || '<i class="fas fa-check"></i> Ya, Lanjutkan',
        cancelButtonText: opts.cancelText || '<i class="fas fa-times"></i> Batal',
        confirmButtonColor: opts.confirmColor || '#3085d6',
        cancelButtonColor: '#6c757d',
        reverseButtons: true,
        focusCancel: true,
        customClass: {
            popup: 'shadow-lg',
            confirmButton: 'btn btn-md mr-2',
            cancelButton: 'btn btn-md',
        },
        buttonsStyling: true,
    }).then(function(result) {
        if (result.isConfirmed && form) {
            form.submit();
        }
        return result;
    });
}

/**
 * Attach SweetAlert2 confirm to a form with onsubmit interception.
 * Usage: smartqConfirmForm('#myForm', { title, text, icon, confirmText })
 */
function smartqConfirmForm(selector, opts) {
    var form = typeof selector === 'string' ? document.querySelector(selector) : selector;
    if (!form) return;
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        smartqConfirm(form, opts);
    });
}

// Global auto-overlay for page navigation/refresh inside SMART-Q pages
(function smartqAutoOverlayInit() {
    var isSubmitting = false;

    document.addEventListener('click', function(e) {
        var link = e.target.closest('a[href]');
        if (!link) return;
        if (e.defaultPrevented) return;
        if (link.hasAttribute('data-no-overlay')) return;

        var href = (link.getAttribute('href') || '').trim();
        if (!href || href === '#' || href.startsWith('javascript:')) return;
        if (href.startsWith('mailto:') || href.startsWith('tel:')) return;
        if (link.target === '_blank' || link.hasAttribute('download')) return;
        if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey || e.button !== 0) return;

        showSmartqOverlay('Memuat halaman...', 'Mohon tunggu sebentar', 'spinner');
    });

    document.addEventListener('submit', function(e) {
        if (e.defaultPrevented) return;
        var form = e.target;
        if (!form || form.hasAttribute('data-no-overlay')) return;
        isSubmitting = true;
        showSmartqOverlay('Memproses data...', 'Mohon tunggu, jangan tutup halaman ini', 'save');
    });

    window.addEventListener('beforeunload', function() {
        // Show overlay on refresh/direct navigation as browser starts unloading page
        showSmartqOverlay(
            isSubmitting ? 'Menyelesaikan proses...' : 'Memuat ulang halaman...',
            'Mohon tunggu sebentar',
            isSubmitting ? 'save' : 'sync-alt'
        );
    });
})();
</script>

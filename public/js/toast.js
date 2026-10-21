/**
 * Toast notification — zero dependencies, konsisten design system BUKANWIL.
 * Varian: success, error, warning.
 * Trigger: toastShow(type, title, message, duration)
 */
(function () {
    'use strict';

    var container = null;

    // SVG icons
    var icons = {
        success: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>',
        error: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>',
        warning: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M12 3a9 9 0 100 18 9 9 0 000-18z"/></svg>'
    };

    function ensureContainer() {
        if (container) return container;
        container = document.createElement('div');
        container.className = 'toast-container';
        container.setAttribute('aria-live', 'polite');
        container.setAttribute('aria-atomic', 'false');
        document.body.appendChild(container);
        return container;
    }

    function escapeHTML(str) {
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    /**
     * Show toast notification.
     * @param {string} type - 'success', 'error', atau 'warning'
     * @param {string} title - Judul singkat
     * @param {string} message - Pesan detail (opsional)
     * @param {number} duration - Durasi tampil (ms), default 4000. 0 = tidak auto-dismiss.
     */
    function toastShow(type, title, message, duration) {
        if (!type || !title) return;

        var validTypes = ['success', 'error', 'warning'];
        if (validTypes.indexOf(type) === -1) type = 'success';

        var autoDismiss = typeof duration === 'number' ? duration : 4000;
        var icon = icons[type] || icons.success;

        var toast = document.createElement('div');
        toast.className = 'toast ' + type;
        toast.setAttribute('role', type === 'error' ? 'alert' : 'status');

        var html = '<div class="toast-icon">' + icon + '</div>';
        html += '<div class="toast-content">';
        html += '<div class="toast-title">' + escapeHTML(title) + '</div>';
        if (message) {
            html += '<div class="toast-message">' + escapeHTML(message) + '</div>';
        }
        html += '</div>';
        html += '<button type="button" class="toast-close" aria-label="Tutup">';
        html += '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width:100%;height:100%"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>';
        html += '</button>';

        toast.innerHTML = html;

        var closeBtn = toast.querySelector('.toast-close');
        closeBtn.addEventListener('click', function () {
            dismissToast(toast);
        });

        ensureContainer().appendChild(toast);

        // trigger reflow untuk animasi
        void toast.offsetWidth;
        toast.classList.add('show');

        if (autoDismiss > 0) {
            setTimeout(function () {
                dismissToast(toast);
            }, autoDismiss);
        }
    }

    function dismissToast(toast) {
        toast.classList.remove('show');
        toast.classList.add('hide');
        setTimeout(function () {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 200);
    }

    // Ekspor global
    window.toastShow = toastShow;
})();

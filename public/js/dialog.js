/**
 * Modal dialog konfirmasi kustom — zero dependencies, konsisten design system BUKANWIL.
 * Reusable: confirmDialog(options)
 * Options:
 *   - title: string (default: 'Konfirmasi')
 *   - message: string (default: 'Apakah Anda yakin ingin melanjutkan?')
 *   - confirmText: string (default: 'Konfirmasi')
 *   - cancelText: string (default: 'Batal')
 *   - danger: boolean (default: false)
 *   - onConfirm: function (callback saat tombol konfirmasi diklik)
 */
(function () {
    'use strict';

    var activeDialog = null;
    var lastFocusedElement = null;

    function escapeHTML(str) {
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function closeDialog(dialog) {
        if (!dialog) return;

        dialog.classList.remove('dialog-show');
        dialog.classList.add('dialog-hide');

        setTimeout(function () {
            if (dialog.parentNode) {
                dialog.parentNode.removeChild(dialog);
            }
            if (activeDialog === dialog) {
                activeDialog = null;
            }
            document.body.classList.remove('dialog-open');
            if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
                try {
                    lastFocusedElement.focus();
                } catch (e) {
                    // ignore focus error
                }
                lastFocusedElement = null;
            }
        }, 150);
    }

    /**
     * Buka modal konfirmasi kustom
     * @param {Object} options
     */
    function confirmDialog(options) {
        options = options || {};
        var title = options.title || 'Konfirmasi';
        var message = options.message || 'Apakah Anda yakin ingin melanjutkan?';
        var confirmText = options.confirmText || 'Konfirmasi';
        var cancelText = options.cancelText || 'Batal';
        var isDanger = Boolean(options.danger);
        var onConfirm = typeof options.onConfirm === 'function' ? options.onConfirm : null;

        // Tutup dialog sebelumnya jika masih ada
        if (activeDialog) {
            closeDialog(activeDialog);
        }

        lastFocusedElement = document.activeElement;

        var overlay = document.createElement('div');
        overlay.className = 'dialog-overlay';
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');
        overlay.setAttribute('aria-labelledby', 'dialog-title');
        overlay.setAttribute('aria-describedby', 'dialog-message');

        var confirmBtnClass = isDanger ? 'dialog-btn dialog-btn-danger' : 'dialog-btn dialog-btn-primary';

        var html = '';
        html += '<div class=\"dialog-panel\">';
        html += '  <div class=\"dialog-header\">';
        html += '    <h3 id=\"dialog-title\" class=\"dialog-title\">' + escapeHTML(title) + '</h3>';
        html += '  </div>';
        html += '  <div class=\"dialog-body\">';
        html += '    <p id=\"dialog-message\" class=\"dialog-message\">' + escapeHTML(message) + '</p>';
        html += '  </div>';
        html += '  <div class=\"dialog-footer\">';
        html += '    <button type=\"button\" class=\"dialog-btn dialog-btn-secondary\" data-action=\"cancel\">' + escapeHTML(cancelText) + '</button>';
        html += '    <button type=\"button\" class=\"' + confirmBtnClass + '\" data-action=\"confirm\">' + escapeHTML(confirmText) + '</button>';
        html += '  </div>';
        html += '</div>';

        overlay.innerHTML = html;

        var cancelBtn = overlay.querySelector('[data-action=\"cancel\"]');
        var confirmBtn = overlay.querySelector('[data-action=\"confirm\"]');
        var panel = overlay.querySelector('.dialog-panel');

        function handleCancel() {
            closeDialog(overlay);
        }

        function handleConfirm() {
            closeDialog(overlay);
            if (onConfirm) {
                try {
                    onConfirm();
                } catch (err) {
                    console.error('Error in onConfirm callback:', err);
                }
            }
        }

        cancelBtn.addEventListener('click', handleCancel);
        confirmBtn.addEventListener('click', handleConfirm);

        // Klik overlay di luar panel untuk menutup/batal
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) {
                handleCancel();
            }
        });

        // Keydown handler: Escape untuk batal, Tab trapping fokus sederhana
        function keyHandler(e) {
            if (!activeDialog) return;
            if (e.key === 'Escape' || e.keyCode === 27) {
                e.preventDefault();
                handleCancel();
                return;
            }
            if (e.key === 'Tab' || e.keyCode === 9) {
                var focusables = [cancelBtn, confirmBtn];
                var first = focusables[0];
                var last = focusables[focusables.length - 1];

                if (e.shiftKey) {
                    if (document.activeElement === first) {
                        e.preventDefault();
                        last.focus();
                    }
                } else {
                    if (document.activeElement === last) {
                        e.preventDefault();
                        first.focus();
                    }
                }
            }
        }

        overlay._keyHandler = keyHandler;
        document.addEventListener('keydown', keyHandler);

        document.body.appendChild(overlay);
        document.body.classList.add('dialog-open');
        activeDialog = overlay;

        // Force reflow agar transisi berjalan
        void overlay.offsetWidth;
        overlay.classList.add('dialog-show');

        // Autofokus ke tombol konfirmasi (atau batal jika tombol berbahaya agar aman)
        if (isDanger) {
            cancelBtn.focus();
        } else {
            confirmBtn.focus();
        }

        // Cleanup keydown listener saat modal ditutup
        var observer = new MutationObserver(function (mutations, obs) {
            if (!document.body.contains(overlay)) {
                document.removeEventListener('keydown', keyHandler);
                obs.disconnect();
            }
        });
        observer.observe(document.body, { childList: true });
    }

    // Ekspor global
    window.confirmDialog = confirmDialog;
})();

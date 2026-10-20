/**
 * Public form behaviours — conditional fields + foto compression.
 * IIFE, zero dependencies.
 */
(function () {
    'use strict';

    // ----- Helpers -----
    function $(sel) {
        return document.querySelector(sel);
    }

    function onReady(fn) {
        if (document.readyState !== 'loading') {
            fn();
        } else {
            document.addEventListener('DOMContentLoaded', fn);
        }
    }

    // ----- Conditional field: nama_instansi -----
    function initConditionalInstansi() {
        var radios = document.querySelectorAll('input[name="asal_pengunjung"]');
        var wrap = $('#wrap-nama-instansi');
        var input = $('#nama_instansi');

        if (! wrap || ! input) return;

        function update() {
            var selected = document.querySelector('input[name="asal_pengunjung"]:checked');
            var isInstansiOrLembaga = selected && (selected.value === 'instansi' || selected.value === 'lembaga');

            wrap.style.display = isInstansiOrLembaga ? 'block' : 'none';
            input.required = isInstansiOrLembaga;

            if (! isInstansiOrLembaga) {
                input.value = '';
            }
        }

        radios.forEach(function (radio) {
            radio.addEventListener('change', update);
        });

        update();
    }

    // ----- Foto compression -----
    var MAX_RAW_SIZE = 10 * 1024 * 1024; // 10 MB batas pilih file
    var TARGET_SIZE = 1 * 1024 * 1024;   // target < 1 MB setelah kompresi
    var MAX_EDGE = 1920;                 // resize sisi terpanjang

    function showFotoError(msg) {
        var el = $('#foto-error');
        if (! el) return;
        el.textContent = msg || '';
        el.classList.remove('hidden');
    }

    function hideFotoError() {
        var el = $('#foto-error');
        if (! el) return;
        el.textContent = '';
        el.classList.add('hidden');
    }

    function blobToFile(blob, name) {
        return new File([blob], name, { type: blob.type, lastModified: Date.now() });
    }

    function readFile(file) {
        return new Promise(function (resolve, reject) {
            var reader = new FileReader();
            reader.onload = function () { resolve(reader.result); };
            reader.onerror = function () { reject(reader.error); };
            reader.readAsDataURL(file);
        });
    }

    function loadImage(dataUrl) {
        return new Promise(function (resolve, reject) {
            var img = new Image();
            img.onload = function () { resolve(img); };
            img.onerror = function () { reject(new Error('Gagal memuat gambar')); };
            img.src = dataUrl;
        });
    }

    function compressBlob(img, quality, maxEdge) {
        return new Promise(function (resolve, reject) {
            var canvas = document.createElement('canvas');
            var w = img.naturalWidth || img.width;
            var h = img.naturalHeight || img.height;

            // resize sisi terpanjang
            if (w > maxEdge || h > maxEdge) {
                if (w > h) {
                    h = Math.round(h * (maxEdge / w));
                    w = maxEdge;
                } else {
                    w = Math.round(w * (maxEdge / h));
                    h = maxEdge;
                }
            }

            canvas.width = w;
            canvas.height = h;
            var ctx = canvas.getContext('2d');
            if (! ctx) {
                reject(new Error('Canvas tidak tersedia'));
                return;
            }
            ctx.fillStyle = '#fff';
            ctx.fillRect(0, 0, w, h);
            ctx.drawImage(img, 0, 0, w, h);

            canvas.toBlob(
                function (blob) {
                    if (! blob) {
                        reject(new Error('toBlob gagal'));
                        return;
                    }
                    resolve(blob);
                },
                'image/jpeg',
                quality
            );
        });
    }

    function compressImage(file) {
        return readFile(file).then(function (dataUrl) {
            return loadImage(dataUrl);
        }).then(function (img) {
            var originalName = file.name.replace(/\.[^/.]+$/, '');
            var attempt = function (quality, maxEdge) {
                return compressBlob(img, quality, maxEdge).then(function (blob) {
                    if (blob.size <= TARGET_SIZE || quality <= 0.3) {
                        return blobToFile(blob, originalName + '.jpg');
                    }
                    // turunkan kualitas bertahap
                    return attempt(quality - 0.1, maxEdge);
                });
            };

            // coba dulu resize + kualitas tinggi
            return attempt(0.92, MAX_EDGE).then(function (compressedFile) {
                if (compressedFile.size > TARGET_SIZE) {
                    // masih besar, turunkan max edge sekali lagi
                    return attempt(0.92, Math.round(MAX_EDGE * 0.75)).then(function (smallerFile) {
                        return smallerFile;
                    });
                }
                return compressedFile;
            });
        });
    }

    function initFotoCompression() {
        var form = document.querySelector('form[action*="tamu/submit"]') || document.querySelector('form[enctype="multipart/form-data"]');
        var input = $('#foto');
        var submitButton = form ? form.querySelector('button[type="submit"]') : null;
        var isCompressing = false;

        if (! form || ! input) return;

        form.addEventListener('submit', function (e) {
            hideFotoError();

            var file = input.files && input.files[0];
            if (! file) return; // tanpa foto, submit normal

            // cek ukuran mentah > 10 MB
            if (file.size > MAX_RAW_SIZE) {
                e.preventDefault();
                showFotoError('Ukuran file terlalu besar. Maksimal 10 MB.');
                return;
            }

            // tidak perlu kompres bila sudah di bawah target
            if (file.size <= TARGET_SIZE) return;

            if (isCompressing) {
                e.preventDefault();
                return;
            }

            e.preventDefault();
            isCompressing = true;
            if (submitButton) submitButton.disabled = true;

            compressImage(file).then(function (compressedFile) {
                if (compressedFile.size > TARGET_SIZE) {
                    showFotoError('Ukuran foto tetap melebihi 1 MB setelah dikompres. Silakan pilih foto lain.');
                    return;
                }
                var dt = new DataTransfer();
                dt.items.add(compressedFile);
                input.files = dt.files;
                form.submit();
            }).catch(function () {
                showFotoError('Gagal mengompres foto. Silakan pilih foto lain atau coba lagi.');
            }).finally(function () {
                isCompressing = false;
                if (submitButton) submitButton.disabled = false;
            });
        });
    }

    // ----- Boot -----
    onReady(function () {
        initConditionalInstansi();
        initFotoCompression();
    });
})();

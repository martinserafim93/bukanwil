/**
 * Public form behaviours — geolocation + conditional fields.
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

    // ----- Geolocation -----
    function initGeolocation() {
        var btn = $('#btn-deteksi-lokasi');
        var status = $('#status-lokasi');
        var latInput = $('#lat');
        var lngInput = $('#lng');
        var manual = $('#input-manual-lokasi');

        if (! btn) return;

        function showManual() {
            if (manual) manual.style.display = 'grid';
        }

        function setStatus(text, type) {
            if (! status) return;
            status.textContent = text;
            status.className = 'form-hint mt-2 ' + (type === 'error' ? 'text-danger' : 'text-success');
        }

        function onSuccess(position) {
            var lat = position.coords.latitude;
            var lng = position.coords.longitude;

            if (latInput) latInput.value = lat.toFixed(8);
            if (lngInput) lngInput.value = lng.toFixed(8);

            setStatus('Lokasi terdeteksi.');

            if (manual) manual.style.display = 'none';
        }

        function onError(error) {
            var message = 'Gagal mendeteksi lokasi. Silakan isi manual.';
            if (error && error.code === 1) {
                message = 'Akses lokasi ditolak. Silakan isi manual.';
            } else if (error && error.code === 2) {
                message = 'Informasi lokasi tidak tersedia. Silakan isi manual.';
            } else if (error && error.code === 3) {
                message = 'Waktu deteksi lokasi habis. Silakan isi manual.';
            }
            setStatus(message, 'error');
            showManual();
        }

        btn.addEventListener('click', function () {
            if (! navigator.geolocation) {
                setStatus('Browser tidak mendukung geolokasi. Silakan isi manual.', 'error');
                showManual();
                return;
            }

            setStatus('Mendeteksi lokasi...');
            navigator.geolocation.getCurrentPosition(onSuccess, onError, {
                enableHighAccuracy: false,
                timeout: 10000,
                maximumAge: 0,
            });
        });

        // Sync optional manual lat/lng into hidden inputs before submit
        var form = btn.closest('form');
        if (form) {
            form.addEventListener('submit', function () {
                var latManual = $('#lat_manual');
                var lngManual = $('#lng_manual');
                if (! latInput.value && latManual && latManual.value) {
                    latInput.value = latManual.value;
                }
                if (! lngInput.value && lngManual && lngManual.value) {
                    lngInput.value = lngManual.value;
                }
            });
        }
    }

    // ----- Boot -----
    onReady(function () {
        initConditionalInstansi();
        initGeolocation();
    });
})();

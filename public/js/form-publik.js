/**
 * Public form behaviours — conditional fields.
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

    // ----- Boot -----
    onReady(function () {
        initConditionalInstansi();
    });
})();

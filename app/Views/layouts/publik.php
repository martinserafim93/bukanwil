<?php
/**
 * Layout Publik - BUKANWIL
 * Header minimalis + area konten + footer responsif.
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="BUKANWIL adalah buku tamu digital PTSP Kanwil Kemenag Provinsi Kalimantan Utara. Pengunjung mengisi data kunjungan secara daring tanpa akun, dan petugas mengelolanya lewat dashboard admin.">
    <title><?= esc($this->renderSection('title')) ?: 'Form Buku Tamu' ?> - <?= esc($pengaturan['judul_aplikasi'] ?? 'BUKANWIL') ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="icon" type="image/png" href="<?= base_url('images/logo-kemenag-transparent.png') ?>">

    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>?v=<?= filemtime(FCPATH . 'css/app.css') ?>">
    <script src="<?= base_url('js/toast.js') ?>?v=<?= filemtime(FCPATH . 'js/toast.js') ?>" defer></script>
</head>
<body class="bg-ground text-ink antialiased min-h-screen flex flex-col">

    <div class="brand-stripe" aria-hidden="true"></div>

    <main class="flex-1 py-8 md:py-12 flex flex-col justify-center">
        <div class="max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8">
            <?= $this->renderSection('content') ?>
        </div>
    </main>

    <footer class="bg-ground-warm mt-auto">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-sm text-ink-muted">
            <p>
                Tim Humas dan Komunikasi Publik - Kanwil Kementerian Agama Provinsi Kalimantan Utara
            </p>
            <a href="<?= site_url('admin/login') ?>" class="text-xs font-semibold text-brand-700 hover:underline">
                Portal Petugas PTSP
            </a>
        </div>
    </footer>

    <script>
        // Auto-trigger toast dari flash session
        document.addEventListener('DOMContentLoaded', function () {
            <?php if (session('sukses')): ?>
                toastShow('success', 'Berhasil', <?= json_encode(session('sukses')) ?>);
            <?php endif; ?>
            <?php if (session('error')): ?>
                toastShow('error', 'Terjadi Kesalahan', <?= json_encode(session('error')) ?>);
            <?php endif; ?>

            var clockEl = document.getElementById('live-wita-clock');
            if (clockEl && typeof Intl !== 'undefined' && Intl.DateTimeFormat) {
                var fmt = new Intl.DateTimeFormat('id-ID', {
                    timeZone: 'Asia/Makassar',
                    weekday: 'short',
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: false
                });
                var updateClock = function () {
                    clockEl.textContent = fmt.format(new Date()) + ' WITA';
                };
                updateClock();
                setInterval(updateClock, 1000);
            }
        });
    </script>

    <?= $this->renderSection('scripts') ?>
</body>
</html>

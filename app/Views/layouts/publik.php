<?php
/**
 * Layout Publik — BUKANWIL
 * Header minimalis + area konten + footer sederhana.
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="BUKANWIL adalah buku tamu digital PTSP Kanwil Kemenag Provinsi Kalimantan Utara. Pengunjung mengisi data kunjungan secara daring tanpa akun, dan petugas mengelolanya lewat dashboard admin.">
    <title><?= esc($this->renderSection('title')) ?? 'Form Buku Tamu' ?> — <?= esc($pengaturan['judul_aplikasi'] ?? 'BUKANWIL') ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="icon" type="image/png" href="<?= base_url('images/logo-kemenag-transparent.png') ?>">

    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>?v=<?= filemtime(FCPATH . 'css/app.css') ?>">
    <script src="<?= base_url('js/toast.js') ?>?v=<?= filemtime(FCPATH . 'js/toast.js') ?>" defer></script>
</head>
<body class="bg-ground text-ink antialiased min-h-screen flex flex-col">

    <header class="bg-surface border-b border-border">
        <div class="brand-stripe" aria-hidden="true"></div>
        <div class="container-readable py-8 md:py-10">
            <div class="flex flex-col items-center text-center">
                <img
                    src="<?= base_url('images/logo-kemenag.jpg') ?>"
                    alt="Logo Kementerian Agama"
                    class="h-16 w-auto md:h-20 object-contain mb-4"
                >
                <h1 class="text-3xl md:text-4xl font-serif text-brand-700 leading-tight">
                    <?= esc($pengaturan['judul_aplikasi'] ?? 'BUKANWIL') ?>
                </h1>
                <p class="text-base md:text-lg italic text-ink-muted mt-1">
                    <?= esc($pengaturan['deskripsi_aplikasi'] ?? 'Buku Tamu Kanwil Kemenag Kaltara') ?>
                </p>
                <p class="text-sm md:text-base font-semibold text-ink mt-1">
                    <?= esc($pengaturan['nama_instansi_ptsp'] ?? 'Kantor Wilayah Kementerian Agama Provinsi Kalimantan Utara') ?>
                </p>
            </div>
        </div>
    </header>

    <main class="flex-1 py-8 md:py-12">
        <?= $this->renderSection('content') ?>
    </main>

    <footer class="bg-surface border-t border-border mt-auto">
        <div class="container-readable py-6">
            <p class="text-center text-sm text-ink-muted">
                Tim Humas dan Komunikasi Publik<br>
                &mdash; Kanwil Kementerian Agama Provinsi Kalimantan Utara &mdash;
            </p>
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
        });
    </script>

    <?= $this->renderSection('scripts') ?>
</body>
</html>

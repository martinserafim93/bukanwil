<?php
/**
 * Layout Publik — SANTRI
 * Header minimalis + area konten + footer sederhana.
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($this->renderSection('title')) ?? 'Form Buku Tamu' ?> — SANTRI</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
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
                    <?= esc($pengaturan['judul_aplikasi'] ?? 'SANTRI') ?>
                </h1>
                <p class="text-sm md:text-base font-semibold text-ink mt-1">
                    <?= esc($pengaturan['nama_instansi_ptsp'] ?? 'Kanwil Kemenag Provinsi Kalimantan Utara') ?>
                </p>
                <?php if (! empty($pengaturan['teks_header'])): ?>
                    <p class="text-sm text-ink-muted mt-1"><?= esc($pengaturan['teks_header']) ?></p>
                <?php else: ?>
                    <p class="text-sm text-ink-muted mt-1">Pelayanan Terpadu Satu Pintu</p>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="flex-1 py-8 md:py-12">
        <?= $this->renderSection('content') ?>
    </main>

    <footer class="bg-surface border-t border-border mt-auto">
        <div class="container-readable py-6">
            <p class="text-center text-sm text-ink-muted">
                &copy; <?= date('Y') ?> <?= esc($pengaturan['nama_instansi_ptsp'] ?? 'Kanwil Kemenag Kaltara') ?>. Sistem Buku Tamu Digital PTSP.
            </p>
        </div>
    </footer>

    <?= $this->renderSection('scripts') ?>
</body>
</html>

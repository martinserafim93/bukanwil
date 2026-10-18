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
    <title><?= esc($this->renderSection('title')) ?? 'Buku Tamu PTSP' ?> — SANTRI</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
</head>
<body class="bg-ground text-ink antialiased min-h-screen flex flex-col">

    <header class="bg-surface border-b border-border">
        <div class="brand-stripe" aria-hidden="true"></div>
        <div class="container-readable py-6">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-500 text-white font-bold text-sm" aria-hidden="true">
                    Km
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Kementerian Agama RI</p>
                    <h1 class="text-sm md:text-base font-bold leading-tight">Kanwil Kemenag Provinsi Kalimantan Utara</h1>
                </div>
            </div>
        </div>
    </header>

    <main class="flex-1 py-8 md:py-12">
        <?= $this->renderSection('content') ?>
    </main>

    <footer class="bg-surface border-t border-border mt-auto">
        <div class="container-readable py-6">
            <p class="text-center text-sm text-ink-muted">
                &copy; <?= date('Y') ?> Kanwil Kemenag Kaltara. Sistem Buku Tamu Digital PTSP.
            </p>
        </div>
    </footer>

    <?= $this->renderSection('scripts') ?>
</body>
</html>

<?php
/**
 * Layout Admin — SANTRI
 * Sidebar + topbar + area konten.
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($this->renderSection('title')) ?? 'Admin' ?> — SANTRI</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
</head>
<body class="bg-ground text-ink antialiased min-h-screen flex">

    <!-- Sidebar -->
    <aside class="hidden md:flex md:w-64 shrink-0 flex-col bg-surface border-r border-border">
        <div class="brand-stripe" aria-hidden="true"></div>
        <div class="p-6 border-b border-border">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-500 text-white font-bold text-sm" aria-hidden="true">
                    Km
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">SANTRI</p>
                    <p class="text-sm font-bold leading-tight">Admin Panel</p>
                </div>
            </div>
        </div>

        <nav class="flex-1 p-4 space-y-1">
            <a href="#" class="flex items-center gap-3 px-4 py-3 text-sm font-medium bg-brand-50 text-brand-700">
                <span>Dashboard</span>
            </a>
            <a href="#" class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-ink-muted hover:bg-surface-raised hover:text-ink transition-colors">
                <span>Daftar Tamu</span>
            </a>
            <a href="#" class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-ink-muted hover:bg-surface-raised hover:text-ink transition-colors">
                <span>Kelola Admin</span>
            </a>
            <a href="#" class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-ink-muted hover:bg-surface-raised hover:text-ink transition-colors">
                <span>Pengaturan</span>
            </a>
        </nav>

        <div class="p-4 border-t border-border">
            <a href="#" class="block w-full text-center px-4 py-2 text-sm font-medium text-danger hover:bg-danger-bg rounded-full transition-colors">
                Logout
            </a>
        </div>
    </aside>

    <!-- Main area -->
    <div class="flex-1 flex flex-col min-w-0">
        <!-- Topbar -->
        <header class="bg-surface border-b border-border">
            <div class="brand-stripe" aria-hidden="true"></div>
            <div class="flex items-center justify-between px-6 py-4">
                <h2 class="text-lg font-bold"><?= esc($this->renderSection('title')) ?? 'Dashboard' ?></h2>
                <div class="flex items-center gap-4">
                    <span class="hidden sm:inline-block text-sm text-ink-muted">Administrator</span>
                    <div class="h-8 w-8 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 font-bold text-xs" aria-hidden="true">
                        AD
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 p-6 md:p-8">
            <?= $this->renderSection('content') ?>
        </main>
    </div>

    <?= $this->renderSection('scripts') ?>
</body>
</html>

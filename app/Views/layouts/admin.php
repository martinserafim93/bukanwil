<?php
/**
 * Layout Admin — BUKANWIL
 * Sidebar + topbar + area konten.
 */

// Ambil data admin dari session
$adminNama = session('admin_nama') ?? 'Administrator';
$adminInisial = strtoupper(substr(trim($adminNama), 0, 2)) ?: 'AD';

// Helper untuk menentukan apakah link aktif
function isActive(string $path): string
{
    $current = service('request')->getPath();
    return ($current === $path) ? 'bg-brand-50 text-brand-700' : 'text-ink-muted hover:bg-surface-raised hover:text-ink';
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title', true) ?> — <?= esc($pengaturan['judul_aplikasi'] ?? 'BUKANWIL') ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <?= $this->renderSection('styles') ?>

    <link rel="icon" type="image/png" href="<?= base_url('images/logo-kemenag-transparent.png') ?>">

    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
    <script src="<?= base_url('js/toast.js') ?>" defer></script>
    <script src="<?= base_url('js/dialog.js') ?>" defer></script>
</head>
<body class="bg-ground text-ink antialiased min-h-screen flex">

    <!-- Sidebar -->
    <aside class="hidden md:flex md:w-64 shrink-0 flex-col bg-surface border-r border-border">
        <div class="brand-stripe" aria-hidden="true"></div>
        <div class="p-6 border-b border-border">
            <div class="flex items-center gap-3">
                <img
                    src="<?= base_url('images/logo-kemenag.jpg') ?>"
                    alt="Logo Kementerian Agama"
                    class="h-10 w-auto object-contain"
                >
                <div>
                    <p class="text-sm font-bold leading-tight text-brand-700">
                        <?= esc($pengaturan['judul_aplikasi'] ?? 'BUKANWIL') ?>
                    </p>
                    <p class="text-xs text-ink-muted">Admin Panel</p>
                </div>
            </div>
        </div>

        <nav class="flex-1 p-4 space-y-1">
            <a href="<?= site_url('admin') ?>" class="flex items-center gap-3 px-4 py-3 text-sm font-medium <?= isActive('admin') ?>">
                <span>Dashboard</span>
            </a>
            <a href="<?= site_url('admin/tamu') ?>" class="flex items-center gap-3 px-4 py-3 text-sm font-medium <?= isActive('admin/tamu') ?>">
                <span>Daftar Tamu</span>
            </a>
            <?php if (session('role') === 'superadmin'): ?>
            <a href="<?= site_url('admin/admin-user') ?>" class="flex items-center gap-3 px-4 py-3 text-sm font-medium <?= isActive('admin/admin-user') ?>">
                <span>Kelola Admin</span>
            </a>
            <a href="<?= site_url('admin/pengaturan') ?>" class="flex items-center gap-3 px-4 py-3 text-sm font-medium <?= isActive('admin/pengaturan') ?>">
                <span>Pengaturan</span>
            </a>
            <?php endif; ?>
        </nav>

        <div class="p-4 border-t border-border">
            <a href="<?= site_url('admin/logout') ?>" id="admin-logout-btn" class="block w-full text-center px-4 py-2 text-sm font-medium text-danger hover:bg-danger-bg rounded-full transition-colors">
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
                <h2 class="text-lg font-bold"><?= $this->renderSection('title') ?></h2>
                <div class="flex items-center gap-4">
                    <div class="relative inline-flex items-center">
                        <span class="hidden sm:inline-block text-sm text-ink-muted"><?= esc((string) $adminNama) ?></span>
                        <div class="h-8 w-8 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 font-bold text-xs ml-3" aria-hidden="true">
                            <?= esc((string) $adminInisial) ?>
                        </div>
                        <?php if (($unreadCount ?? 0) > 0): ?>
                            <span class="absolute -top-1 -right-2 inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-danger rounded-full" aria-label="<?= esc((string) $unreadCount) ?> notifikasi belum dibaca">
                                <?= esc((string) ((int) $unreadCount)) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 p-6 md:p-8">
            <?= $this->renderSection('content') ?>
        </main>
    </div>

    <script>
        // Auto-trigger toast dari flash session & wiring modal dialog
        document.addEventListener('DOMContentLoaded', function () {
            <?php if (session('sukses')): ?>
                toastShow('success', 'Berhasil', <?= json_encode(session('sukses')) ?>);
            <?php endif; ?>
            <?php if (session('error')): ?>
                toastShow('error', 'Terjadi Kesalahan', <?= json_encode(session('error')) ?>);
            <?php endif; ?>

            var logoutBtn = document.getElementById('admin-logout-btn');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    var logoutUrl = this.getAttribute('href');
                    if (typeof confirmDialog === 'function') {
                        confirmDialog({
                            title: 'Konfirmasi Logout',
                            message: 'Apakah Anda yakin ingin keluar dari panel admin?',
                            confirmText: 'Logout',
                            cancelText: 'Batal',
                            danger: true,
                            onConfirm: function () {
                                window.location.href = logoutUrl;
                            }
                        });
                    } else {
                        window.location.href = logoutUrl;
                    }
                });
            }
        });
    </script>

    <?= $this->renderSection('scripts') ?>
</body>
</html>

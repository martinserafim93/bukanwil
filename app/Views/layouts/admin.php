<?php
/**
 * Layout Admin - BUKANWIL
 * Sidebar + topbar + area konten.
 */

// Ambil data admin dari session
$adminNama    = session('admin_nama') ?? 'Administrator';
$adminRole    = session('role') ?? 'admin';
$adminInisial = strtoupper(substr(trim($adminNama), 0, 2)) ?: 'AD';

// Helper untuk menentukan apakah link aktif (mendukung sub-rute)
function isActive(string $path): string
{
    $current = trim(service('request')->getPath(), '/');
    $target  = trim($path, '/');
    $active  = ($target === 'admin')
        ? ($current === 'admin')
        : ($current === $target || str_starts_with($current, $target . '/'));

    return $active
        ? 'bg-brand-50 text-brand-700 font-semibold'
        : 'text-ink-muted hover:bg-surface-raised hover:text-ink font-medium';
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title', true) ?> - <?= esc($pengaturan['judul_aplikasi'] ?? 'BUKANWIL') ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <?= $this->renderSection('styles') ?>

    <link rel="icon" type="image/png" href="<?= base_url('images/logo-kemenag-transparent.png') ?>">

    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>?v=<?= filemtime(FCPATH . 'css/app.css') ?>">
    <script src="<?= base_url('js/toast.js') ?>?v=<?= filemtime(FCPATH . 'js/toast.js') ?>" defer></script>
    <script src="<?= base_url('js/dialog.js') ?>?v=<?= filemtime(FCPATH . 'js/dialog.js') ?>" defer></script>
</head>
<body class="bg-ground text-ink antialiased min-h-screen flex flex-col">

    <div class="brand-stripe" aria-hidden="true"></div>

    <div class="flex-1 flex min-h-0">
        <!-- Sidebar Desktop -->
        <aside class="hidden md:flex md:w-64 shrink-0 flex-col bg-surface border-r border-border">
            <div class="p-5 border-b border-border">
                <div class="flex items-center gap-3">
                    <img
                        src="<?= base_url('images/logo-kemenag-transparent.png') ?>"
                        alt="Logo Kementerian Agama"
                        class="h-10 w-10 object-contain"
                    >
                    <div class="min-w-0">
                        <p class="text-sm font-bold leading-tight text-brand-700 truncate">
                            <?= esc($pengaturan['judul_aplikasi'] ?? 'BUKANWIL') ?>
                        </p>
                        <p class="text-xs text-ink-muted">Panel Petugas PTSP</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 p-4 space-y-1" aria-label="Navigasi Utama Admin">
                <a href="<?= site_url('admin') ?>" class="flex items-center gap-3 px-4 py-2.5 text-sm rounded-lg transition-colors <?= isActive('admin') ?>">
                    <span>Dashboard</span>
                </a>
                <a href="<?= site_url('admin/tamu') ?>" class="flex items-center justify-between gap-3 px-4 py-2.5 text-sm rounded-lg transition-colors <?= isActive('admin/tamu') ?>">
                    <span>Daftar Tamu</span>
                    <?php if (($unreadCount ?? 0) > 0): ?>
                        <span class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold text-white bg-danger rounded-full tabular-nums" aria-label="<?= esc((string) ((int) $unreadCount)) ?> tamu belum dibaca">
                            <?= esc((string) ((int) $unreadCount)) ?>
                        </span>
                    <?php endif; ?>
                </a>
                <?php if (session('role') === 'superadmin'): ?>
                <a href="<?= site_url('admin/admin-user') ?>" class="flex items-center gap-3 px-4 py-2.5 text-sm rounded-lg transition-colors <?= isActive('admin/admin-user') ?>">
                    <span>Kelola Admin</span>
                </a>
                <a href="<?= site_url('admin/pengaturan') ?>" class="flex items-center gap-3 px-4 py-2.5 text-sm rounded-lg transition-colors <?= isActive('admin/pengaturan') ?>">
                    <span>Pengaturan</span>
                </a>
                <?php endif; ?>
            </nav>

            <div class="p-4 border-t border-border space-y-2">
                <a href="<?= site_url('/') ?>" target="_blank" rel="noopener" class="block w-full text-center px-4 py-2 text-xs font-semibold text-brand-700 hover:bg-brand-50 rounded-lg transition-colors">
                    Lihat Buku Tamu Publik &nearr;
                </a>
                <form id="admin-logout-form" action="<?= site_url('admin/logout') ?>" method="post">
                    <?= csrf_field() ?>
                    <button type="submit" id="admin-logout-btn" class="block w-full text-center px-4 py-2 text-sm font-medium text-danger hover:bg-danger-bg rounded-lg transition-colors cursor-pointer">
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Area -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Topbar -->
            <header class="bg-surface border-b border-border">
                <div class="flex items-center justify-between px-4 sm:px-6 py-3.5">
                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            id="mobile-menu-btn"
                            class="md:hidden inline-flex items-center justify-center p-2 text-ink hover:bg-surface-raised rounded-lg border border-border"
                            aria-expanded="false"
                            aria-controls="mobile-nav-panel"
                            aria-label="Buka menu navigasi"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <span class="text-sm font-semibold text-ink-muted">
                            <?= $this->renderSection('title') ?>
                        </span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-bold text-ink leading-tight"><?= esc((string) $adminNama) ?></p>
                            <p class="text-xs text-ink-muted"><?= esc(ucfirst((string) $adminRole)) ?></p>
                        </div>
                        <div class="h-9 w-9 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 font-bold text-xs" aria-hidden="true">
                            <?= esc((string) $adminInisial) ?>
                        </div>
                    </div>
                </div>

                <!-- Panel Navigasi Mobile (< 768px) -->
                <nav
                    id="mobile-nav-panel"
                    class="hidden md:hidden bg-surface border-t border-border px-4 py-3 space-y-1"
                    aria-label="Navigasi Mobile Admin"
                >
                    <a href="<?= site_url('admin') ?>" class="flex items-center gap-3 px-3 py-2 text-sm rounded-lg <?= isActive('admin') ?>">
                        <span>Dashboard</span>
                    </a>
                    <a href="<?= site_url('admin/tamu') ?>" class="flex items-center justify-between gap-3 px-3 py-2 text-sm rounded-lg <?= isActive('admin/tamu') ?>">
                        <span>Daftar Tamu</span>
                        <?php if (($unreadCount ?? 0) > 0): ?>
                            <span class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold text-white bg-danger rounded-full tabular-nums">
                                <?= esc((string) ((int) $unreadCount)) ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <?php if (session('role') === 'superadmin'): ?>
                    <a href="<?= site_url('admin/admin-user') ?>" class="flex items-center gap-3 px-3 py-2 text-sm rounded-lg <?= isActive('admin/admin-user') ?>">
                        <span>Kelola Admin</span>
                    </a>
                    <a href="<?= site_url('admin/pengaturan') ?>" class="flex items-center gap-3 px-3 py-2 text-sm rounded-lg <?= isActive('admin/pengaturan') ?>">
                        <span>Pengaturan</span>
                    </a>
                    <?php endif; ?>
                    <div class="pt-2 mt-2 border-t border-border flex items-center justify-between gap-2">
                        <a href="<?= site_url('/') ?>" target="_blank" rel="noopener" class="text-xs font-semibold text-brand-700 hover:underline">
                            Buku Tamu Publik &nearr;
                        </a>
                        <button type="button" class="mobile-logout-trigger text-xs font-semibold text-danger hover:underline cursor-pointer">
                            Logout
                        </button>
                    </div>
                </nav>
            </header>

            <main class="flex-1 p-4 sm:p-6 md:p-8">
                <?= $this->renderSection('content') ?>
            </main>
        </div>
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
            var logoutForm = document.getElementById('admin-logout-form');
            if (logoutBtn && logoutForm) {
                logoutBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    if (typeof confirmDialog === 'function') {
                        confirmDialog({
                            title: 'Konfirmasi Logout',
                            message: 'Apakah Anda yakin ingin keluar dari panel admin?',
                            confirmText: 'Logout',
                            cancelText: 'Batal',
                            danger: true,
                            onConfirm: function () {
                                logoutForm.submit();
                            }
                        });
                    } else {
                        logoutForm.submit();
                    }
                });
            }

            // Mobile menu toggle
            var mobileMenuBtn = document.getElementById('mobile-menu-btn');
            var mobileNavPanel = document.getElementById('mobile-nav-panel');
            if (mobileMenuBtn && mobileNavPanel) {
                mobileMenuBtn.addEventListener('click', function () {
                    var isExpanded = mobileMenuBtn.getAttribute('aria-expanded') === 'true';
                    mobileMenuBtn.setAttribute('aria-expanded', !isExpanded);
                    mobileNavPanel.classList.toggle('hidden');
                });
            }

            // Mobile logout trigger click delegation
            var mobileLogoutTriggers = document.querySelectorAll('.mobile-logout-trigger');
            mobileLogoutTriggers.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    if (logoutBtn) {
                        logoutBtn.click();
                    }
                });
            });
        });
    </script>

    <?= $this->renderSection('scripts') ?>
</body>
</html>

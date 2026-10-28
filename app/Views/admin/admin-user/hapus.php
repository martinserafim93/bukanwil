<?php
/**
 * Delete admin confirmation - BUKANWIL
 *
 * @var array<string, mixed> $admin
 */

$this->extend('layouts/admin');

?>

<?php $this->section('title'); ?>
Hapus Admin
<?php $this->endSection(); ?>

<?php $this->section('content'); ?>

<div class="max-w-xl">
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-serif font-bold text-ink">Konfirmasi Hapus Admin</h1>
        <p class="text-base text-ink-muted mt-1">Anda akan menghapus akun petugas berikut. Tindakan ini tidak dapat dibatalkan.</p>
    </div>

    <div class="bg-surface border border-border p-6 rounded-lg" data-ui>
        <div class="bg-surface-raised border border-border p-4 mb-6 rounded-lg">
            <p class="text-xs font-medium text-ink-muted mb-1">Nama Petugas</p>
            <p class="font-semibold text-ink mb-3"><?= esc((string) ($admin['nama'] ?? '')) ?></p>

            <p class="text-xs font-medium text-ink-muted mb-1">Username</p>
            <p class="font-semibold text-ink mb-3"><?= esc((string) ($admin['username'] ?? '')) ?></p>

            <p class="text-xs font-medium text-ink-muted mb-1">Tingkat Role</p>
            <p class="font-semibold text-ink capitalize"><?= esc((string) ($admin['role'] ?? '')) ?></p>
        </div>

        <form action="<?= site_url('admin/admin-user/' . ((int) $admin['id']) . '/hapus') ?>" method="post" class="flex items-center gap-3">
            <?= csrf_field() ?>
            <button type="submit" class="btn-danger">Ya, Hapus Admin</button>
            <a href="<?= site_url('admin/admin-user') ?>" class="btn-secondary">Batal</a>
        </form>
    </div>
</div>

<?php $this->endSection(); ?>

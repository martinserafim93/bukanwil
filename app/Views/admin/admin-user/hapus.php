<?php
/**
 * Delete admin confirmation — SANTRI
 *
 * @var array<string, mixed> $admin
 */

$this->extend('layouts/admin');

?>

<?php $this->section('title'); ?>
Hapus Admin
<?php $this->endSection(); ?>

<?php $this->section('content'); ?>

<div class="bg-surface border border-border p-6 max-w-xl">
    <h1 class="text-xl font-bold mb-2">Hapus Admin</h1>
    <p class="text-ink-muted mb-6">
        Anda akan menghapus admin berikut. Tindakan ini tidak dapat dibatalkan.
    </p>

    <div class="bg-surface-raised border border-border p-4 mb-6">
        <p class="text-sm text-ink-muted mb-1">Nama</p>
        <p class="font-semibold text-ink mb-3"><?= esc((string) ($admin['nama'] ?? '')) ?></p>

        <p class="text-sm text-ink-muted mb-1">Username</p>
        <p class="font-semibold text-ink mb-3"><?= esc((string) ($admin['username'] ?? '')) ?></p>

        <p class="text-sm text-ink-muted mb-1">Role</p>
        <p class="font-semibold text-ink"><?= esc((string) ($admin['role'] ?? '')) ?></p>
    </div>

    <form action="<?= site_url('admin/admin-user/' . ((int) $admin['id']) . '/hapus') ?>" method="post" class="flex items-center gap-3">
        <?= csrf_field() ?>
        <button type="submit" class="btn-danger">Ya, Hapus</button>
        <a href="<?= site_url('admin/admin-user') ?>" class="btn-secondary">Batal</a>
    </form>
</div>

<?php $this->endSection(); ?>

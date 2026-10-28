<?php
/**
 * Admin user list - BUKANWIL
 *
 * @var array<int, array<string, mixed>> $admins
 * @var int $jumlahSuperadmin
 */

$this->extend('layouts/admin');

?>

<?php $this->section('title'); ?>
Kelola Admin
<?php $this->endSection(); ?>

<?php $this->section('content'); ?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl sm:text-3xl font-serif font-bold text-ink">Kelola Akun Admin</h1>
        <p class="text-base text-ink-muted mt-1">Daftar pengguna dengan hak akses petugas PTSP dan Super Administrator.</p>
    </div>
    <a href="<?= site_url('admin/admin-user/buat') ?>" class="btn-primary text-sm">
        Tambah Admin
    </a>
</div>

<div class="bg-surface border border-border overflow-x-auto rounded-lg" data-ui>
    <table class="w-full text-left text-sm">
        <thead class="bg-surface-raised border-b border-border">
            <tr>
                <th class="px-4 py-3 font-semibold text-ink-muted">#</th>
                <th class="px-4 py-3 font-semibold text-ink-muted">Nama</th>
                <th class="px-4 py-3 font-semibold text-ink-muted">Username</th>
                <th class="px-4 py-3 font-semibold text-ink-muted">Role</th>
                <th class="px-4 py-3 font-semibold text-ink-muted">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border">
            <?php foreach ($admins as $index => $row): ?>
                <?php
                    $isSuperadmin = $row['role'] === 'superadmin';
                    $badgeClass = $isSuperadmin
                        ? 'bg-brand-100 text-brand-700'
                        : 'bg-surface-raised text-ink-muted border border-border';
                ?>
                <tr class="hover:bg-surface-raised/50">
                    <td class="px-4 py-3 text-ink-muted tabular-nums"><?= esc((string) ((int) $index + 1)) ?></td>
                    <td class="px-4 py-3 font-medium text-ink"><?= esc((string) ($row['nama'] ?? '')) ?></td>
                    <td class="px-4 py-3 text-ink"><?= esc((string) ($row['username'] ?? '')) ?></td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $badgeClass ?>">
                            <?= esc((string) ($row['role'] ?? '')) ?>
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <a href="<?= site_url('admin/admin-user/' . ((int) $row['id']) . '/edit') ?>" class="text-brand-600 hover:text-brand-700 font-medium">
                                Edit
                            </a>
                            <a href="<?= site_url('admin/admin-user/' . ((int) $row['id']) . '/hapus') ?>" class="text-danger hover:text-danger/80 font-medium">
                                Hapus
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php $this->endSection(); ?>

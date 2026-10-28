<?php
/**
 * Admin user form - BUKANWIL
 *
 * Used for both create and edit modes.
 *
 * @var array<string, mixed>|null $admin
 * @var string $actionUrl
 * @var string $judul
 */

$this->extend('layouts/admin');

?>

<?php $this->section('title'); ?>
<?= esc($judul) ?>
<?php $this->endSection(); ?>

<?php $this->section('content'); ?>

<div class="max-w-2xl">
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-serif font-bold text-ink"><?= esc($judul) ?></h1>
        <p class="text-base text-ink-muted mt-1">Atur nama, username, kata sandi, dan tingkat kewenangan akun.</p>
    </div>

    <div class="bg-surface border border-border p-6 rounded-lg" data-ui>
        <form action="<?= $actionUrl ?>" method="post" class="space-y-5">
            <?= csrf_field() ?>

            <div>
                <label for="nama" class="form-label">Nama</label>
                <input type="text" id="nama" name="nama" value="<?= esc((string) old('nama', $admin['nama'] ?? '')) ?>" class="form-field" required>
                <?php if (isset(session('errors')['nama'])): ?>
                    <p class="text-danger text-sm mt-1"><?= esc((string) session('errors')['nama']) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" value="<?= esc((string) old('username', $admin['username'] ?? '')) ?>" class="form-field" required>
                <?php if (isset(session('errors')['username'])): ?>
                    <p class="text-danger text-sm mt-1"><?= esc((string) session('errors')['username']) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" value="" class="form-field" <?= $admin === null ? 'required' : '' ?>>
                <?php if ($admin !== null): ?>
                    <p class="text-ink-muted text-xs mt-1">Kosongkan jika tidak ingin mengubah password.</p>
                <?php endif; ?>
                <?php if (isset(session('errors')['password'])): ?>
                    <p class="text-danger text-sm mt-1"><?= esc((string) session('errors')['password']) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label for="password_confirm" class="form-label">Konfirmasi Password</label>
                <input type="password" id="password_confirm" name="password_confirm" value="" class="form-field" <?= $admin === null ? 'required' : '' ?>>
                <?php if (isset(session('errors')['password_confirm'])): ?>
                    <p class="text-danger text-sm mt-1"><?= esc((string) session('errors')['password_confirm']) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label for="role" class="form-label">Role</label>
                <?php if (session('role') === 'superadmin'): ?>
                    <select id="role" name="role" class="form-field" required>
                        <option value="admin" <?= old('role', $admin['role'] ?? '') === 'admin' ? 'selected' : '' ?>>admin</option>
                        <option value="superadmin" <?= old('role', $admin['role'] ?? '') === 'superadmin' ? 'selected' : '' ?>>superadmin</option>
                    </select>
                <?php else: ?>
                    <input type="hidden" name="role" value="admin">
                    <select id="role" name="role" class="form-field" disabled>
                        <option value="admin" selected>admin</option>
                    </select>
                <?php endif; ?>
                <?php if (isset(session('errors')['role'])): ?>
                    <p class="text-danger text-sm mt-1"><?= esc((string) session('errors')['role']) ?></p>
                <?php endif; ?>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="btn-primary">Simpan</button>
                <a href="<?= site_url('admin/admin-user') ?>" class="btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php $this->endSection(); ?>

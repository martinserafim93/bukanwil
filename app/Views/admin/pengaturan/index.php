<?php
/**
 * Admin settings form — BUKANWIL
 *
 * @var array<string, string> $pengaturan
 * @var int $unreadCount
 */

$this->extend('layouts/admin');

?>

<?php $this->section('title'); ?>
<?= esc('Pengaturan') ?>
<?php $this->endSection(); ?>

<?php $this->section('content'); ?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <h1 class="text-xl font-bold"><?= esc('Pengaturan Aplikasi') ?></h1>
</div>

<?php if (session('sukses')): ?>
    <div class="bg-success-bg border border-success text-success px-4 py-3 rounded mb-6" role="alert">
        <?= esc((string) session('sukses')) ?>
    </div>
<?php endif; ?>

<?php if (session('error')): ?>
    <div class="bg-danger-bg border border-danger text-danger px-4 py-3 rounded mb-6" role="alert">
        <?= esc((string) session('error')) ?>
    </div>
<?php endif; ?>

<div class="bg-surface border border-border p-6">
    <form action="<?= site_url('admin/pengaturan') ?>" method="post" class="space-y-5">
        <?= csrf_field() ?>

        <div>
            <label for="judul_aplikasi" class="form-label"><?= esc('Judul Aplikasi') ?></label>
            <input type="text" id="judul_aplikasi" name="judul_aplikasi" value="<?= esc((string) old('judul_aplikasi', $pengaturan['judul_aplikasi'] ?? '')) ?>" class="form-field" required>
            <?php if (session('errors.judul_aplikasi')): ?>
                <p class="text-danger text-sm mt-1"><?= esc((string) session('errors.judul_aplikasi')) ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label for="deskripsi_aplikasi" class="form-label"><?= esc('Deskripsi Aplikasi') ?></label>
            <input type="text" id="deskripsi_aplikasi" name="deskripsi_aplikasi" value="<?= esc((string) old('deskripsi_aplikasi', $pengaturan['deskripsi_aplikasi'] ?? '')) ?>" class="form-field" required>
            <?php if (session('errors.deskripsi_aplikasi')): ?>
                <p class="text-danger text-sm mt-1"><?= esc((string) session('errors.deskripsi_aplikasi')) ?></p>
            <?php endif; ?>
            <p class="text-xs text-ink-muted mt-1">Deskripsi singkat yang tampil di bawah judul pada halaman publik.</p>
        </div>

        <div>
            <label for="nama_instansi_ptsp" class="form-label"><?= esc('Nama Instansi PTSP') ?></label>
            <input type="text" id="nama_instansi_ptsp" name="nama_instansi_ptsp" value="<?= esc((string) old('nama_instansi_ptsp', $pengaturan['nama_instansi_ptsp'] ?? '')) ?>" class="form-field" required>
            <?php if (session('errors.nama_instansi_ptsp')): ?>
                <p class="text-danger text-sm mt-1"><?= esc((string) session('errors.nama_instansi_ptsp')) ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label for="teks_header" class="form-label"><?= esc('Teks Header') ?></label>
            <textarea id="teks_header" name="teks_header" rows="5" class="form-field" required><?= esc((string) old('teks_header', $pengaturan['teks_header'] ?? '')) ?></textarea>
            <?php if (session('errors.teks_header')): ?>
                <p class="text-danger text-sm mt-1"><?= esc((string) session('errors.teks_header')) ?></p>
            <?php endif; ?>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="btn-primary"><?= esc('Simpan') ?></button>
        </div>
    </form>
</div>

<?php $this->endSection(); ?>

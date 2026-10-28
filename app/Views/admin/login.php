<?php
/**
 * Standalone admin login page - BUKANWIL
 * Extends the public layout (no admin sidebar).
 */
$this->extend('layouts/publik');
$this->section('title');
echo 'Login Administrator';
$this->endSection();
$this->section('content');
?>

<section class="max-w-4xl mx-auto">
    <div class="card-surface grid grid-cols-1 md:grid-cols-12 overflow-hidden">
        <!-- Sisi Kiri: Panel Identitas Petugas -->
        <aside class="md:col-span-5 bg-brand-700 text-white p-6 sm:p-8 lg:p-10 flex flex-col justify-between text-center">
            <div class="my-auto py-2">
                <img
                    src="<?= base_url('images/logo-kemenag-transparent.png') ?>"
                    alt="Logo Kementerian Agama"
                    class="h-16 w-16 object-contain mx-auto mb-5"
                >
                <p class="text-2xl font-serif font-bold text-white leading-tight mb-1">
                    <?= esc($pengaturan['judul_aplikasi'] ?? 'BUKANWIL') ?>
                </p>
                <h2 class="text-base font-medium text-brand-100 mb-4">Panel Administrasi PTSP</h2>
                <p class="text-sm text-brand-50 leading-relaxed">
                    Akses khusus petugas penerima tamu dan administrator untuk memantau kunjungan, meninjau keperluan, serta mengunduh laporan rekapitulasi.
                </p>
            </div>
            <div class="pt-6 border-t border-brand-600">
                <a href="<?= site_url('/') ?>" class="inline-flex items-center justify-center gap-2 text-xs font-semibold text-accent-200 hover:underline">
                    &larr; Kembali ke Buku Tamu Publik
                </a>
            </div>
        </aside>

        <!-- Sisi Kanan: Formulir Login -->
        <div class="md:col-span-7 p-6 sm:p-10 bg-surface">
            <div class="mb-8">
                <h1 class="text-2xl sm:text-3xl font-bold text-ink font-serif mb-2">Login Administrator</h1>
                <p class="text-lg text-ink-muted">Masukkan kredensial petugas untuk mengelola data buku tamu.</p>
            </div>

            <form action="<?= site_url('admin/login') ?>" method="post" class="space-y-5">
                <?= csrf_field() ?>

                <div>
                    <label for="username" class="form-label">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        value="<?= esc(old('username')) ?>"
                        class="form-field"
                        placeholder="Masukkan username petugas"
                        autocomplete="username"
                        required
                    >
                </div>

                <div>
                    <label for="password" class="form-label">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-field"
                        placeholder="Masukkan kata sandi"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn-primary w-full">Masuk ke Panel Admin</button>
                </div>
            </form>
        </div>
    </div>
</section>

<?php $this->endSection(); ?>

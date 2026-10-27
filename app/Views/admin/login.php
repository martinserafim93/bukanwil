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
        <div class="md:col-span-5 bg-brand-700 text-white p-6 sm:p-8 flex flex-col justify-between">
            <div>
                <img
                    src="<?= base_url('images/logo-kemenag-transparent.png') ?>"
                    alt="Logo Kementerian Agama"
                    class="h-14 w-14 object-contain mb-6"
                >
                <h2 class="text-xl font-serif font-bold text-white mb-2">Panel Administrasi PTSP</h2>
                <p class="text-sm text-brand-100 leading-relaxed">
                    Akses khusus petugas penerima tamu dan administrator untuk memantau kunjungan, meninjau keperluan, serta mengunduh laporan rekapitulasi.
                </p>
            </div>
            <div>
                <a href="<?= site_url('/') ?>" class="inline-flex items-center gap-2 text-xs font-semibold text-accent-200 hover:underline mt-8">
                    &larr; Kembali ke Buku Tamu Publik
                </a>
            </div>
        </div>

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

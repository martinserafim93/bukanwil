<?php
/**
 * Shell halaman login admin — SANTRI
 * Belum ada logika autentikasi.
 */
$this->extend('layouts/admin');
$this->section('title'); echo 'Login'; $this->endSection();
$this->section('content');
?>

<div class="min-h-full flex items-center justify-center">
    <div class="w-full max-w-md card-surface p-8 md:p-10">
        <div class="mb-8 text-center">
            <h2 class="text-2xl font-bold text-ink mb-2 font-serif">Login Administrator</h2>
            <p class="text-sm text-ink-muted">Masukkan akun untuk mengelola data tamu.</p>
        </div>

        <form action="#" method="post" class="space-y-6">
            <div>
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-field" placeholder="Username admin" autocomplete="username">
            </div>

            <div>
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-field" placeholder="Password" autocomplete="current-password">
            </div>

            <button type="submit" class="btn-primary w-full">Masuk</button>
        </form>
    </div>
</div>

<?php $this->endSection(); ?>

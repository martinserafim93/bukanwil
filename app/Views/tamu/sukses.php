<?php
/**
 * Public guestbook success page — SANTRI
 */
$this->extend('layouts/publik');
$this->section('title', 'Terima Kasih');
$this->section('content');
?>

<section class="container-readable">
    <div class="card-surface p-8 md:p-12 text-center">
        <div class="mb-6 flex justify-center">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-success-bg text-success" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
        </div>
        <h2 class="text-2xl md:text-3xl font-bold text-ink mb-3 font-serif">Terima kasih, data kunjungan Anda telah tercatat.</h2>
        <p class="text-ink-muted mb-8">Silakan isi form baru untuk tamu berikutnya.</p>
        <a href="<?= site_url('/') ?>" class="btn-primary inline-block">Isi Form Lagi</a>
    </div>
</section>

<?php $this->endSection(); ?>

<?php
/**
 * Public guestbook success page - BUKANWIL
 */
$this->extend('layouts/publik');
$this->section('title');
echo 'Terima Kasih';
$this->endSection();
$this->section('content');
?>

<section class="max-w-2xl mx-auto">
    <div class="brand-stripe" aria-hidden="true"></div>
    <div class="card-surface p-8 sm:p-12">
        <div class="flex items-center gap-3 pb-6 mb-6 border-b border-border">
            <img
                src="<?= base_url('images/logo-kemenag-transparent.png') ?>"
                alt="Logo Kementerian Agama"
                class="h-10 w-10 object-contain"
            >
            <div>
                <p class="text-sm font-bold text-brand-700 leading-tight">
                    <?= esc($pengaturan['judul_aplikasi'] ?? 'BUKANWIL') ?>
                </p>
                <p class="text-xs text-ink-muted">
                    <?= esc($pengaturan['nama_instansi_ptsp'] ?? 'Kantor Wilayah Kementerian Agama Provinsi Kalimantan Utara') ?>
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
            <span class="inline-flex items-center gap-2 px-3 py-1 bg-success-bg text-success text-xs font-semibold">
                Kunjungan Tercatat
            </span>
            <span class="text-xs text-ink-muted tabular-nums">
                <?= esc(date('d/m/Y H:i')) ?> WITA
            </span>
        </div>

        <h1 class="text-2xl sm:text-3xl font-bold text-ink font-serif mt-2 mb-3">
            Kunjungan Berhasil Dicatat
        </h1>

        <p class="text-base text-ink-muted leading-relaxed mb-8">
            Terima kasih, data kehadiran Anda telah masuk ke dalam sistem Pelayanan Terpadu Satu Pintu (PTSP). Silakan menuju meja petugas pelayanan atau isi formulir baru untuk pengunjung berikutnya.
        </p>

        <div class="flex flex-wrap items-center gap-4">
            <a href="<?= site_url('/') ?>" class="btn-primary">Isi Form Lagi</a>
        </div>
    </div>
</section>

<?php $this->endSection(); ?>

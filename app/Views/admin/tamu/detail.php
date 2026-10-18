<?php
/**
 * Admin detail tamu — SANTRI
 *
 * @var array<string, mixed> $tamu
 */

$this->extend('layouts/admin');

?>

<?php $this->section('title'); ?>
Detail Tamu
<?php $this->endSection(); ?>

<?php $this->section('content'); ?>

<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h1 class="text-2xl font-bold text-ink">
            <?= esc((string) ($tamu['nama'] ?? 'Detail Tamu')) ?>
        </h1>
        <a href="<?= site_url('admin/tamu') ?>" class="btn-secondary text-sm inline-flex items-center">
            Kembali
        </a>
    </div>

    <div class="bg-surface border border-border p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted mb-1">Jenis Kelamin</p>
                <p class="text-sm text-ink"><?= esc((string) ($tamu['jenis_kelamin'] ?? '-')) ?></p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted mb-1">Asal Pengunjung</p>
                <p class="text-sm text-ink capitalize"><?= esc((string) ($tamu['asal_pengunjung'] ?? '-')) ?></p>
            </div>

            <?php if (! empty($tamu['nama_instansi'])): ?>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted mb-1">Nama Instansi</p>
                    <p class="text-sm text-ink"><?= esc((string) $tamu['nama_instansi']) ?></p>
                </div>
            <?php endif; ?>

            <div class="md:col-span-2">
                <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted mb-1">Alamat</p>
                <p class="text-sm text-ink whitespace-pre-wrap"><?= esc((string) ($tamu['alamat'] ?? '-')) ?></p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted mb-1">No. HP / WA</p>
                <p class="text-sm text-ink"><?= esc((string) ($tamu['no_hp'] ?? '-')) ?></p>
            </div>

            <div class="md:col-span-2">
                <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted mb-1">Keperluan</p>
                <p class="text-sm text-ink whitespace-pre-wrap"><?= esc((string) ($tamu['keperluan'] ?? '-')) ?></p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted mb-1">Tanggal Kunjungan</p>
                <p class="text-sm text-ink">
                    <?php if (! empty($tamu['created_at'])): ?>
                        <?= esc(date('d/m/Y H:i', strtotime((string) $tamu['created_at']))) ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted mb-1">Status</p>
                <?php if (! empty($tamu['is_read'])): ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success/10 text-success">Dibaca</span>
                <?php else: ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-brand-100 text-brand-700">Belum Dibaca</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (! empty($tamu['foto'])): ?>
        <div class="bg-surface border border-border p-6">
            <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted mb-4">Foto</p>
            <img
                src="<?= base_url('uploads/foto/' . esc((string) $tamu['foto'], 'url')) ?>"
                alt="<?= esc((string) ($tamu['nama'] ?? 'Foto tamu')) ?>"
                class="w-full max-w-md h-auto object-cover rounded border border-border"
            >
        </div>
    <?php endif; ?>
</div>

<?php $this->endSection(); ?>

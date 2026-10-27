<?php
/**
 * Admin detail tamu — BUKANWIL
 *
 * @var array<string, mixed> $tamu
 */

$this->extend('layouts/admin');

?>

<?php $this->section('title'); ?>
Detail Tamu
<?php $this->endSection(); ?>

<?php

$this->section('content');

$isSuperadmin = session('role') === 'superadmin';
$id = (int) ($tamu['id'] ?? 0);
$isDeleted = ! empty($tamu['deleted_at']);
$isArchived = ! empty($tamu['is_archived']);

?>

<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-ink">
                <?= esc((string) ($tamu['nama'] ?? 'Detail Tamu')) ?>
            </h1>
            <div class="flex items-center gap-2 mt-1">
                <?php if ($isDeleted): ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-danger/10 text-danger border border-danger/20">
                        Tong Sampah (Soft Deleted)
                    </span>
                <?php elseif ($isArchived): ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-surface-raised border border-border text-ink-muted">
                        Arsip Riwayat
                    </span>
                <?php else: ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-brand/10 text-brand">
                        Tamu Aktif
                    </span>
                <?php endif; ?>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= site_url('admin/tamu' . ($isDeleted ? '?tab=sampah' : ($isArchived ? '?tab=arsip' : ''))) ?>" class="btn-secondary text-sm inline-flex items-center">
                Kembali
            </a>
        </div>
    </div>

    <!-- Alert status if in trash or archive -->
    <?php if ($isDeleted): ?>
        <div class="p-4 bg-danger/10 border border-danger/20 text-danger text-sm rounded">
            <strong>Pemberitahuan:</strong> Rekaman tamu ini telah dipindahkan ke Tong Sampah. Data ini tidak dihitung dalam statistik dashboard.
        </div>
    <?php elseif ($isArchived): ?>
        <div class="p-4 bg-surface-raised border border-border text-ink-muted text-sm rounded">
            <strong>Status Arsip:</strong> Rekaman ini tersimpan dalam arsip riwayat lampau.
        </div>
    <?php endif; ?>

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
                        <?= esc(date('d/m/Y H:i', strtotime((string) $tamu['created_at']))) ?> WITA
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted mb-1">Status Peninjauan</p>
                <?php if (! empty($tamu['is_read'])): ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success/10 text-success">Sudah Ditinjau</span>
                <?php else: ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-brand/10 text-brand">Belum Ditinjau</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (! empty($tamu['foto'])): ?>
        <div class="bg-surface border border-border p-6">
            <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted mb-4">Foto Pengunjung</p>
            <?php
            $fotoPath = (string) $tamu['foto'];
            $src = str_starts_with($fotoPath, 'uploads/') ? $fotoPath : 'uploads/foto/' . $fotoPath;
            ?>
            <img
                src="<?= base_url(esc($src, 'url')) ?>"
                alt="<?= esc((string) ($tamu['nama'] ?? 'Foto tamu')) ?>"
                class="w-full max-w-md h-auto object-cover rounded border border-border"
            >
        </div>
    <?php endif; ?>

    <!-- Superadmin Lifecycle Action Bar -->
    <?php if ($isSuperadmin): ?>
        <div class="bg-surface border border-border p-5 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h4 class="text-sm font-semibold text-ink">Tindakan Pengelolaan Data Tamu</h4>
                <p class="text-xs text-ink-muted">Aksi khusus Super Administrator untuk pengarsipan dan pembersihan.</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <?php if ($isDeleted): ?>
                    <!-- Restore -->
                    <form action="<?= site_url('admin/tamu/' . $id . '/pulihkan') ?>" method="post" class="inline">
                        <?= csrf_field() ?>
                        <button type="submit"
                                class="btn-primary text-sm action-confirm-btn"
                                data-title="Pulihkan Data Tamu"
                                data-message="Pulihkan data <?= esc((string) $tamu['nama']) ?> kembali ke daftar aktif?"
                                data-confirm-text="Ya, Pulihkan"
                                data-danger="false">
                            Pulihkan ke Aktif
                        </button>
                    </form>

                    <!-- Hard Purge -->
                    <form action="<?= site_url('admin/tamu/' . $id . '/hapus-permanen') ?>" method="post" class="inline">
                        <?= csrf_field() ?>
                        <button type="submit"
                                class="btn-danger text-sm action-confirm-btn"
                                data-title="HAPUS PERMANEN"
                                data-message="PERINGATAN: Rekaman dan berkas foto fisik <?= esc((string) $tamu['nama']) ?> akan dihapus selamanya dari database dan server. Tindakan ini TIDAK BISA dibatalkan!"
                                data-confirm-text="HAPUS SELAMANYA"
                                data-danger="true">
                            Hapus Permanen
                        </button>
                    </form>
                <?php elseif ($isArchived): ?>
                    <!-- Batal Arsip -->
                    <form action="<?= site_url('admin/tamu/' . $id . '/batal-arsip') ?>" method="post" class="inline">
                        <?= csrf_field() ?>
                        <button type="submit"
                                class="btn-primary text-sm action-confirm-btn"
                                data-title="Kembalikan ke Aktif"
                                data-message="Kembalikan data <?= esc((string) $tamu['nama']) ?> ke daftar tamu aktif?"
                                data-confirm-text="Ya, Kembalikan"
                                data-danger="false">
                            Kembalikan ke Aktif
                        </button>
                    </form>

                    <!-- Hapus ke Sampah -->
                    <form action="<?= site_url('admin/tamu/' . $id . '/hapus') ?>" method="post" class="inline">
                        <?= csrf_field() ?>
                        <button type="submit"
                                class="btn-secondary text-danger text-sm action-confirm-btn"
                                data-title="Pindahkan ke Tong Sampah"
                                data-message="Pindahkan data arsip <?= esc((string) $tamu['nama']) ?> ke Tong Sampah?"
                                data-confirm-text="Ya, Buang ke Sampah"
                                data-danger="true">
                            Pindahkan ke Tong Sampah
                        </button>
                    </form>
                <?php else: ?>
                    <!-- Arsipkan -->
                    <form action="<?= site_url('admin/tamu/' . $id . '/arsipkan') ?>" method="post" class="inline">
                        <?= csrf_field() ?>
                        <button type="submit"
                                class="btn-secondary text-sm action-confirm-btn"
                                data-title="Arsipkan Tamu"
                                data-message="Pindahkan rekaman <?= esc((string) $tamu['nama']) ?> ke Arsip Riwayat?"
                                data-confirm-text="Ya, Arsipkan"
                                data-danger="false">
                            Arsipkan
                        </button>
                    </form>

                    <!-- Hapus ke Sampah -->
                    <form action="<?= site_url('admin/tamu/' . $id . '/hapus') ?>" method="post" class="inline">
                        <?= csrf_field() ?>
                        <button type="submit"
                                class="btn-secondary text-danger text-sm action-confirm-btn"
                                data-title="Pindahkan ke Tong Sampah"
                                data-message="Pindahkan data <?= esc((string) $tamu['nama']) ?> ke Tong Sampah? Data tidak akan dihitung di statistik."
                                data-confirm-text="Ya, Buang ke Sampah"
                                data-danger="true">
                            Pindahkan ke Tong Sampah
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var confirmButtons = document.querySelectorAll('.action-confirm-btn');
    confirmButtons.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            var form = this.closest('form');
            var title = this.getAttribute('data-title') || 'Konfirmasi';
            var message = this.getAttribute('data-message') || 'Lanjutkan tindakan ini?';
            var confirmText = this.getAttribute('data-confirm-text') || 'Ya, Lanjutkan';
            var isDanger = this.getAttribute('data-danger') === 'true';

            if (typeof confirmDialog === 'function') {
                confirmDialog({
                    title: title,
                    message: message,
                    confirmText: confirmText,
                    cancelText: 'Batal',
                    danger: isDanger,
                    onConfirm: function () {
                        form.submit();
                    }
                });
            } else if (confirm(message)) {
                form.submit();
            }
        });
    });
});
</script>

<?php $this->endSection(); ?>

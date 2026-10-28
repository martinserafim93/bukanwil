<?php
/**
 * Admin guest list with lifecycle management (Aktif, Arsip, Tong Sampah) - BUKANWIL
 *
 * @var array<int, array<string, mixed>> $tamu
 * @var \CodeIgniter\Pager\Pager         $pager
 * @var int                             $total
 * @var array<string, mixed>            $filter_aktif
 * @var string                          $tab_aktif
 * @var int                             $count_aktif
 * @var int                             $count_arsip
 * @var int                             $count_sampah
 */

$this->extend('layouts/admin');

?>

<?php $this->section('title'); ?>
Daftar Tamu
<?php $this->endSection(); ?>

<?php

$this->section('content');

$isSuperadmin = session('role') === 'superadmin';
$opsiJenisKelamin = ['Laki-laki', 'Perempuan'];
$opsiAsal         = ['perorangan', 'instansi', 'lembaga'];

$filterQueryString = http_build_query(
    array_filter($filter_aktif, fn ($v) => $v !== '' && $v !== null)
);
$exportUrl = site_url('admin/tamu/export') . ($filterQueryString ? '?' . $filterQueryString : '');

?>

<!-- Header Halaman Utama -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl sm:text-3xl font-serif font-bold text-ink">Daftar Kunjungan Tamu</h1>
        <p class="text-base text-ink-muted mt-1">Kelola buku tamu aktif, arsip riwayat kunjungan, dan pembersihan data.</p>
    </div>
    <div class="flex flex-wrap items-center gap-3">
        <a href="<?= $exportUrl ?>" class="btn-secondary text-sm">
            Export CSV
        </a>
        <?php if ($tab_aktif === 'aktif'): ?>
            <form action="<?= site_url('admin/tamu/baca-semua') ?>" method="post" class="inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn-primary text-sm">
                    Tandai Semua Dibaca
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- Tab Navigation Bar -->
<div class="border-b border-border mb-6 flex flex-wrap gap-2 text-sm font-medium">
    <a href="<?= site_url('admin/tamu?tab=aktif') ?>"
       class="inline-flex items-center gap-2 px-4 py-3 border-b-2 transition-colors <?= $tab_aktif === 'aktif' ? 'border-brand text-brand font-semibold' : 'border-transparent text-ink-muted hover:text-ink hover:border-border' ?>">
        <span>Tamu Aktif</span>
        <span class="inline-flex items-center justify-center px-2 py-0.5 text-xs rounded-full tabular-nums <?= $tab_aktif === 'aktif' ? 'bg-brand/10 text-brand' : 'bg-surface-raised border border-border text-ink-muted' ?>">
            <?= esc((string) $count_aktif) ?>
        </span>
    </a>

    <a href="<?= site_url('admin/tamu?tab=arsip') ?>"
       class="inline-flex items-center gap-2 px-4 py-3 border-b-2 transition-colors <?= $tab_aktif === 'arsip' ? 'border-brand text-brand font-semibold' : 'border-transparent text-ink-muted hover:text-ink hover:border-border' ?>">
        <span>Arsip Riwayat</span>
        <span class="inline-flex items-center justify-center px-2 py-0.5 text-xs rounded-full tabular-nums <?= $tab_aktif === 'arsip' ? 'bg-brand/10 text-brand' : 'bg-surface-raised border border-border text-ink-muted' ?>">
            <?= esc((string) $count_arsip) ?>
        </span>
    </a>

    <?php if ($isSuperadmin): ?>
        <a href="<?= site_url('admin/tamu?tab=sampah') ?>"
           class="inline-flex items-center gap-2 px-4 py-3 border-b-2 transition-colors <?= $tab_aktif === 'sampah' ? 'border-danger text-danger font-semibold' : 'border-transparent text-ink-muted hover:text-danger hover:border-border' ?>">
            <span>Tong Sampah</span>
            <span class="inline-flex items-center justify-center px-2 py-0.5 text-xs rounded-full tabular-nums <?= $tab_aktif === 'sampah' ? 'bg-danger/10 text-danger' : 'bg-surface-raised border border-border text-ink-muted' ?>">
                <?= esc((string) $count_sampah) ?>
            </span>
        </a>
    <?php endif; ?>
</div>

<!-- Bulk Archive Panel (Superadmin only on Arsip tab) -->
<?php if ($isSuperadmin && $tab_aktif === 'arsip'): ?>
    <div class="bg-surface border border-border p-5 mb-6 rounded-lg" data-ui>
        <h2 class="text-sm font-bold text-ink mb-2">Alat Pengarsipan Massal (Tutup Buku Tahunan)</h2>
        <form action="<?= site_url('admin/tamu/arsipkan-massal') ?>" method="post" class="flex flex-wrap items-center gap-3">
            <?= csrf_field() ?>
            <label for="tahun_cutoff" class="text-sm text-ink-muted">Arsipkan semua tamu aktif sebelum tahun:</label>
            <input type="number" id="tahun_cutoff" name="tahun" min="2020" max="<?= esc(date('Y')) ?>" value="<?= esc(date('Y')) ?>" class="form-field w-28 text-center tabular-nums" required>
            <button type="submit"
                    class="btn-secondary text-sm action-confirm-btn"
                    data-title="Konfirmasi Pengarsipan Massal"
                    data-message="Seluruh data tamu aktif yang dibuat sebelum tahun tersebut akan dipindahkan ke Arsip. Lanjutkan?"
                    data-confirm-text="Ya, Arsipkan Massal"
                    data-danger="false">
                Proses Arsipkan Massal
            </button>
        </form>
    </div>
<?php endif; ?>

<!-- Filter Panel -->
<div class="bg-surface border border-border p-5 mb-6 rounded-lg" data-ui>
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-sm font-bold text-ink">Filter & Pencarian Data (<?= esc(ucfirst($tab_aktif)) ?>)</h2>
        <span class="text-xs text-ink-muted tabular-nums">Total hasil: <strong class="text-ink"><?= esc((string) $total) ?></strong> data</span>
    </div>
    <form action="<?= site_url('admin/tamu') ?>" method="get" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <input type="hidden" name="tab" value="<?= esc($tab_aktif) ?>">

        <div>
            <label for="q" class="form-label">Pencarian</label>
            <input type="text" id="q" name="q" value="<?= esc((string) ($filter_aktif['q'] ?? '')) ?>" class="form-field" placeholder="Nama / No. HP / Instansi">
        </div>

        <?php if ($tab_aktif === 'arsip'): ?>
            <div>
                <label for="tahun" class="form-label">Tahun Kunjungan</label>
                <input type="number" id="tahun" name="tahun" min="2020" max="<?= esc(date('Y')) ?>" value="<?= esc((string) ($filter_aktif['tahun'] ?? '')) ?>" class="form-field tabular-nums" placeholder="Semua Tahun">
            </div>
        <?php endif; ?>

        <div>
            <label for="tanggal_dari" class="form-label">Dari Tanggal</label>
            <input type="date" id="tanggal_dari" name="tanggal_dari" value="<?= esc((string) ($filter_aktif['tanggal_dari'] ?? '')) ?>" class="form-field">
        </div>

        <div>
            <label for="tanggal_sampai" class="form-label">Sampai Tanggal</label>
            <input type="date" id="tanggal_sampai" name="tanggal_sampai" value="<?= esc((string) ($filter_aktif['tanggal_sampai'] ?? '')) ?>" class="form-field">
        </div>

        <div>
            <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
            <select id="jenis_kelamin" name="jenis_kelamin" class="form-field">
                <option value="">Semua</option>
                <?php foreach ($opsiJenisKelamin as $jk): ?>
                    <option value="<?= esc($jk) ?>" <?= ($filter_aktif['jenis_kelamin'] ?? '') === $jk ? 'selected' : '' ?>>
                        <?= esc($jk) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="asal" class="form-label">Asal Pengunjung</label>
            <select id="asal" name="asal" class="form-field">
                <option value="">Semua</option>
                <?php foreach ($opsiAsal as $a): ?>
                    <option value="<?= esc($a) ?>" <?= ($filter_aktif['asal'] ?? '') === $a ? 'selected' : '' ?>>
                        <?= esc(ucfirst($a)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="keperluan" class="form-label">Keperluan</label>
            <input type="text" id="keperluan" name="keperluan" value="<?= esc((string) ($filter_aktif['keperluan'] ?? '')) ?>" class="form-field" placeholder="Cari keperluan">
        </div>

        <div class="md:col-span-2 lg:col-span-2 flex items-end gap-3">
            <button type="submit" class="btn-primary">Filter</button>
            <a href="<?= site_url('admin/tamu?tab=' . esc($tab_aktif, 'url')) ?>" class="btn-secondary">Reset</a>
        </div>
    </form>
</div>

<!-- Guest Table -->
<?php if (empty($tamu)): ?>
    <div class="bg-surface border border-border p-10 text-center rounded-lg" data-ui>
        <p class="text-ink-muted">Belum ada data tamu pada tab ini.</p>
    </div>
<?php else: ?>
    <div class="overflow-x-auto bg-surface border border-border mb-6 rounded-lg" data-ui>
        <table class="w-full text-left text-sm">
            <thead class="bg-surface-raised border-b border-border">
                <tr>
                    <th class="px-4 py-3 font-semibold text-ink-muted">#</th>
                    <th class="px-4 py-3 font-semibold text-ink-muted">Nama</th>
                    <th class="px-4 py-3 font-semibold text-ink-muted">Asal</th>
                    <th class="px-4 py-3 font-semibold text-ink-muted">Instansi</th>
                    <th class="px-4 py-3 font-semibold text-ink-muted">No. HP</th>
                    <th class="px-4 py-3 font-semibold text-ink-muted">Keperluan</th>
                    <th class="px-4 py-3 font-semibold text-ink-muted">Tanggal</th>
                    <th class="px-4 py-3 font-semibold text-ink-muted">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                <?php foreach ($tamu as $row): ?>
                    <tr class="hover:bg-surface-raised/50">
                        <td class="px-4 py-3 text-ink-muted tabular-nums"><?= esc((string) ((int) $row['id'])) ?></td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-ink"><?= esc((string) $row['nama']) ?></span>
                                <?php if ($tab_aktif === 'aktif' && empty($row['is_read'])): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-brand/10 text-brand">Baru</span>
                                <?php endif; ?>
                                <?php if (! empty($row['is_archived'])): ?>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-surface-raised border border-border text-ink-muted">Arsip</span>
                                <?php endif; ?>
                                <?php if (! empty($row['deleted_at'])): ?>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-danger/10 text-danger">Sampah</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-ink capitalize"><?= esc((string) ($row['asal_pengunjung'] ?? '')) ?></td>
                        <td class="px-4 py-3 text-ink"><?= esc((string) ($row['nama_instansi'] ?? '')) ?></td>
                        <td class="px-4 py-3 text-ink tabular-nums"><?= esc((string) ($row['no_hp'] ?? '')) ?></td>
                        <td class="px-4 py-3 text-ink truncate max-w-xs" title="<?= esc((string) ($row['keperluan'] ?? '')) ?>">
                            <?= esc((string) ($row['keperluan'] ?? '')) ?>
                        </td>
                        <td class="px-4 py-3 text-ink whitespace-nowrap tabular-nums">
                            <?php if (! empty($row['created_at'])): ?>
                                <?= esc(date('d/m/Y H:i', strtotime((string) $row['created_at']))) ?>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <a href="<?= site_url('admin/tamu/' . ((int) $row['id'])) ?>" class="text-brand hover:underline font-medium">
                                    Detail
                                </a>

                                <?php if ($isSuperadmin): ?>
                                    <?php if ($tab_aktif === 'aktif'): ?>
                                        <!-- Tombol Arsip -->
                                        <form action="<?= site_url('admin/tamu/' . ((int) $row['id']) . '/arsipkan') ?>" method="post" class="inline">
                                            <?= csrf_field() ?>
                                            <button type="submit"
                                                    class="text-ink-muted hover:text-ink font-medium action-confirm-btn"
                                                    data-title="Arsipkan Tamu"
                                                    data-message="Pindahkan rekaman kunjungan <?= esc((string) $row['nama']) ?> ke tab Arsip Riwayat?"
                                                    data-confirm-text="Ya, Arsipkan"
                                                    data-danger="false">
                                                Arsip
                                            </button>
                                        </form>

                                        <!-- Tombol Buang ke Sampah (Soft Delete) -->
                                        <form action="<?= site_url('admin/tamu/' . ((int) $row['id']) . '/hapus') ?>" method="post" class="inline">
                                            <?= csrf_field() ?>
                                            <button type="submit"
                                                    class="text-danger hover:underline font-medium action-confirm-btn"
                                                    data-title="Pindahkan ke Tong Sampah"
                                                    data-message="Pindahkan data <?= esc((string) $row['nama']) ?> ke Tong Sampah? Data tidak akan dihitung pada statistik dashboard."
                                                    data-confirm-text="Ya, Buang ke Sampah"
                                                    data-danger="true">
                                                Hapus
                                            </button>
                                        </form>
                                    <?php elseif ($tab_aktif === 'arsip'): ?>
                                        <!-- Tombol Batal Arsip -->
                                        <form action="<?= site_url('admin/tamu/' . ((int) $row['id']) . '/batal-arsip') ?>" method="post" class="inline">
                                            <?= csrf_field() ?>
                                            <button type="submit"
                                                    class="text-brand hover:underline font-medium action-confirm-btn"
                                                    data-title="Kembalikan ke Aktif"
                                                    data-message="Kembalikan rekaman kunjungan <?= esc((string) $row['nama']) ?> ke tab Tamu Aktif?"
                                                    data-confirm-text="Ya, Kembalikan"
                                                    data-danger="false">
                                                Aktifkan
                                            </button>
                                        </form>

                                        <!-- Tombol Buang ke Sampah dari Arsip -->
                                        <form action="<?= site_url('admin/tamu/' . ((int) $row['id']) . '/hapus') ?>" method="post" class="inline">
                                            <?= csrf_field() ?>
                                            <button type="submit"
                                                    class="text-danger hover:underline font-medium action-confirm-btn"
                                                    data-title="Pindahkan ke Tong Sampah"
                                                    data-message="Pindahkan data arsip <?= esc((string) $row['nama']) ?> ke Tong Sampah?"
                                                    data-confirm-text="Ya, Buang ke Sampah"
                                                    data-danger="true">
                                                Hapus
                                            </button>
                                        </form>
                                    <?php elseif ($tab_aktif === 'sampah'): ?>
                                        <!-- Tombol Restore -->
                                        <form action="<?= site_url('admin/tamu/' . ((int) $row['id']) . '/pulihkan') ?>" method="post" class="inline">
                                            <?= csrf_field() ?>
                                            <button type="submit"
                                                    class="text-brand hover:underline font-medium action-confirm-btn"
                                                    data-title="Pulihkan Data Tamu"
                                                    data-message="Pulihkan data <?= esc((string) $row['nama']) ?> kembali ke daftar aktif?"
                                                    data-confirm-text="Ya, Pulihkan"
                                                    data-danger="false">
                                                Pulihkan
                                            </button>
                                        </form>

                                        <!-- Tombol Hapus Permanen -->
                                        <form action="<?= site_url('admin/tamu/' . ((int) $row['id']) . '/hapus-permanen') ?>" method="post" class="inline">
                                            <?= csrf_field() ?>
                                            <button type="submit"
                                                    class="text-danger hover:underline font-bold action-confirm-btn"
                                                    data-title="HAPUS PERMANEN"
                                                    data-message="PERINGATAN: Rekaman dan berkas foto fisik <?= esc((string) $row['nama']) ?> akan dihapus selamanya dari database dan server. Tindakan ini TIDAK BISA dibatalkan!"
                                                    data-confirm-text="HAPUS SELAMANYA"
                                                    data-danger="true">
                                                Hapus Permanen
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pager->getPageCount() > 1): ?>
        <div class="flex justify-end">
            <?= $pager->links('default', 'admin_tailwind') ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var confirmButtons = document.querySelectorAll('.action-confirm-btn');
    confirmButtons.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            var form = this.closest('form');
            var title = this.getAttribute('data-title') || 'Konfirmasi';
            var message = this.getAttribute('data-message') || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
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

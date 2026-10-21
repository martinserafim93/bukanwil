<?php
/**
 * Admin guest list — BUKANWIL
 *
 * @var array<int, array<string, mixed>> $tamu
 * @var \CodeIgniter\Pager\Pager         $pager
 * @var int                             $total
 * @var array<string, mixed>            $filter_aktif
 */

$this->extend('layouts/admin');

?>

<?php $this->section('title'); ?>
Daftar Tamu
<?php $this->endSection(); ?>

<?php

$this->section('content');

$opsiJenisKelamin = ['Laki-laki', 'Perempuan'];
$opsiAsal         = ['perorangan', 'instansi', 'lembaga'];

$filterQueryString = http_build_query(
    array_filter($filter_aktif, fn ($v) => $v !== '' && $v !== null)
);
$exportUrl = site_url('admin/tamu/export') . ($filterQueryString ? '?' . $filterQueryString : '');

?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <p class="text-sm text-ink-muted">
            Total hasil: <span class="font-semibold text-ink"><?= esc((string) $total) ?></span>
        </p>
    </div>
    <div class="flex flex-wrap items-center gap-3">
        <a href="<?= $exportUrl ?>" class="btn-secondary text-sm">
            Export CSV
        </a>
        <form action="<?= site_url('admin/tamu/baca-semua') ?>" method="post" class="inline">
            <?= csrf_field() ?>
            <button type="submit" class="btn-primary text-sm">
                Tandai Semua Dibaca
            </button>
        </form>
    </div>
</div>

<div class="bg-surface border border-border p-5 mb-8">
    <h3 class="text-sm font-semibold uppercase tracking-wide text-ink-muted mb-4">Filter Data</h3>
    <form action="<?= site_url('admin/tamu') ?>" method="get" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
            <label for="q" class="form-label">Pencarian</label>
            <input type="text" id="q" name="q" value="<?= esc((string) ($filter_aktif['q'] ?? '')) ?>" class="form-field" placeholder="Nama / No. HP / Instansi">
        </div>

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
            <a href="<?= site_url('admin/tamu') ?>" class="btn-secondary">Reset</a>
        </div>
    </form>
</div>

<?php if (empty($tamu)): ?>
    <div class="bg-surface border border-border p-10 text-center">
        <p class="text-ink-muted">Belum ada data tamu.</p>
    </div>
<?php else: ?>
    <div class="overflow-x-auto bg-surface border border-border mb-6">
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
                        <td class="px-4 py-3 text-ink-muted"><?= esc((string) ((int) $row['id'])) ?></td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-ink"><?= esc((string) $row['nama']) ?></span>
                                <?php if (empty($row['is_read'])): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-brand-100 text-brand-700">Baru</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-ink capitalize"><?= esc((string) ($row['asal_pengunjung'] ?? '')) ?></td>
                        <td class="px-4 py-3 text-ink"><?= esc((string) ($row['nama_instansi'] ?? '')) ?></td>
                        <td class="px-4 py-3 text-ink"><?= esc((string) ($row['no_hp'] ?? '')) ?></td>
                        <td class="px-4 py-3 text-ink truncate max-w-xs" title="<?= esc((string) ($row['keperluan'] ?? '')) ?>">
                            <?= esc((string) ($row['keperluan'] ?? '')) ?>
                        </td>
                        <td class="px-4 py-3 text-ink whitespace-nowrap">
                            <?php if (! empty($row['created_at'])): ?>
                                <?= esc(date('d/m/Y H:i', strtotime((string) $row['created_at']))) ?>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3">
                            <a href="<?= site_url('admin/tamu/' . ((int) $row['id'])) ?>" class="text-brand-600 hover:text-brand-700 font-medium">
                                Detail
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pager->getPageCount() > 1): ?>
        <div class="flex justify-end">
            <?= $pager->links('default') ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php $this->endSection(); ?>

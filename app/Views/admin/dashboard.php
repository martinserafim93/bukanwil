<?php

use CodeIgniter\View\View;

/**
 * @var View $this
 * @var array<string, string> $pengaturan
 * @var int $unreadCount
 * @var array<string, mixed> $statistik
 * @var bool $defaultPasswordWarning
 * @var int|null $superadminId
 */

$this->extend('layouts/admin');

?>

<?php $this->section('title'); ?>
Dashboard Admin
<?php $this->endSection(); ?>

<?php

$this->section('content');

// Hitung rasio jenis kelamin
$perJenis = $statistik['per_jenis'] ?? [];
$totalJenis = (int) array_sum(array_column($perJenis, 'count'));
$lakiCount = 0;
$perempuanCount = 0;
foreach ($perJenis as $item) {
    if ($item['label'] === 'Laki-laki') {
        $lakiCount = (int) $item['count'];
    } elseif ($item['label'] === 'Perempuan') {
        $perempuanCount = (int) $item['count'];
    }
}
$lakiPercent = $totalJenis > 0 ? round(($lakiCount / $totalJenis) * 100) : 0;
$perempuanPercent = $totalJenis > 0 ? round(($perempuanCount / $totalJenis) * 100) : 0;

// Data chart line (tren 7 hari)
$tren = $statistik['tren_7hari'] ?? [];
$trenLabels = array_column($tren, 'tanggal');
$trenData = array_column($tren, 'count');

// Data chart doughnut (per jenis kelamin)
$jenisLabels = [];
$jenisData = [];
foreach ($perJenis as $item) {
    $jenisLabels[] = $item['label'];
    $jenisData[] = (int) $item['count'];
}

?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl sm:text-3xl font-serif font-bold text-ink">Dashboard Statistik PTSP</h1>
        <p class="text-base text-ink-muted mt-1">Ringkasan kehadiran pengunjung, tren harian, dan distribusi layanan buku tamu.</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="<?= site_url('admin/tamu') ?>" class="btn-secondary text-sm">Kelola Daftar Tamu</a>
    </div>
</div>

<?php if (! empty($defaultPasswordWarning)): ?>
    <div class="mb-6 p-4 bg-warning-bg border border-warning/30 flex items-start gap-3 rounded-lg" role="alert">
        <svg class="w-5 h-5 mt-0.5 shrink-0 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <div class="flex-1 text-sm text-ink">
            <strong class="font-bold text-warning block mb-1">Peringatan Keamanan: Kata Sandi Bawaan Masih Digunakan!</strong>
            <p class="text-ink-muted mb-2">
                Akun Super Administrator masih menggunakan kata sandi awal (<code>Admin@2026</code>). Segera ubah kata sandi akun untuk mencegah akses yang tidak sah.
            </p>
            <?php if (session('role') === 'superadmin'): ?>
                <a href="<?= site_url('admin/admin-user/' . ((int) ($superadminId ?? session('admin_id'))) . '/edit') ?>" class="inline-flex items-center gap-1 font-semibold text-warning hover:underline">
                    Ganti Kata Sandi Sekarang &rarr;
                </a>
            <?php else: ?>
                <span class="text-xs text-ink-muted italic">Silakan hubungi Super Administrator untuk memperbarui kata sandi utama sistem.</span>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Baris 4 Kartu KPI -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8 items-stretch">
    <!-- Total Tamu -->
    <div class="bg-surface border border-border p-5 flex flex-col justify-between rounded-lg">
        <div>
            <p class="text-sm font-medium text-ink-muted mb-2">Total Kunjungan</p>
            <p class="text-3xl font-bold text-ink tabular-nums"><?= esc((string) ($statistik['total_tamu'] ?? 0)) ?></p>
        </div>
        <p class="text-xs text-ink-muted mt-3 pt-3 border-t border-border/60">Akumulasi tamu aktif tercatat</p>
    </div>

    <!-- Tamu Hari Ini -->
    <div class="bg-surface border border-border p-5 flex flex-col justify-between rounded-lg">
        <div>
            <p class="text-sm font-medium text-ink-muted mb-2">Tamu Hari Ini</p>
            <p class="text-3xl font-bold text-brand-700 tabular-nums"><?= esc((string) ($statistik['tamu_hari_ini'] ?? 0)) ?></p>
        </div>
        <p class="text-xs text-ink-muted mt-3 pt-3 border-t border-border/60">Kunjungan masuk hari ini (WITA)</p>
    </div>

    <!-- Belum Ditinjau -->
    <div class="bg-surface border border-border p-5 flex flex-col justify-between rounded-lg">
        <div>
            <p class="text-sm font-medium text-ink-muted mb-2">Belum Ditinjau</p>
            <p class="text-3xl font-bold <?= (($statistik['unread_count'] ?? 0) > 0) ? 'text-danger' : 'text-ink' ?> tabular-nums">
                <?= esc((string) ($statistik['unread_count'] ?? 0)) ?>
            </p>
        </div>
        <p class="text-xs text-ink-muted mt-3 pt-3 border-t border-border/60">Menunggu peninjauan petugas</p>
    </div>

    <!-- Rasio Jenis Kelamin -->
    <div class="bg-surface border border-border p-5 flex flex-col justify-between rounded-lg">
        <div>
            <p class="text-sm font-medium text-ink-muted mb-2">Rasio Jenis Kelamin</p>
            <div class="space-y-1">
                <div class="flex items-baseline justify-between">
                    <span class="text-xs text-ink-muted">Laki-laki</span>
                    <span class="text-base font-bold text-brand-700 tabular-nums"><?= esc((string) $lakiPercent) ?>%</span>
                </div>
                <div class="flex items-baseline justify-between">
                    <span class="text-xs text-ink-muted">Perempuan</span>
                    <span class="text-base font-bold text-brand-gold tabular-nums"><?= esc((string) $perempuanPercent) ?>%</span>
                </div>
            </div>
        </div>
        <p class="text-xs text-ink-muted mt-3 pt-3 border-t border-border/60">Proporsi demografi gender</p>
    </div>
</div>

<!-- Baris Grafik Tren & Proporsi -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8 items-stretch">
    <!-- Line Chart: Tren 7 Hari -->
    <div class="lg:col-span-2 bg-surface border border-border p-5 flex flex-col justify-between rounded-lg">
        <div>
            <h2 class="text-base font-bold text-ink mb-1">Tren Kunjungan 7 Hari Terakhir</h2>
            <p class="text-xs text-ink-muted mb-4">Volume tamu per hari selama sepekan</p>
        </div>
        <div class="relative h-72">
            <canvas id="trenChart"></canvas>
        </div>
    </div>

    <!-- Doughnut Chart: Per Jenis Kelamin -->
    <div class="bg-surface border border-border p-5 flex flex-col justify-between rounded-lg">
        <div>
            <h2 class="text-base font-bold text-ink mb-1">Proporsi Jenis Kelamin</h2>
            <p class="text-xs text-ink-muted mb-4">Perbandingan demografi pengunjung</p>
        </div>
        <div class="relative h-64">
            <canvas id="jenisChart"></canvas>
        </div>
    </div>
</div>

<!-- Baris Top 5 Keperluan & Asal Pengunjung -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
    <!-- Top 5 Keperluan -->
    <div class="bg-surface border border-border p-5 flex flex-col justify-between rounded-lg">
        <div>
            <h2 class="text-base font-bold text-ink mb-1">5 Keperluan Terbanyak</h2>
            <p class="text-xs text-ink-muted mb-4">Tujuan kunjungan paling sering diajukan</p>
        </div>
        <?php $topKeperluan = $statistik['top_keperluan'] ?? []; ?>
        <?php if (empty($topKeperluan)): ?>
            <p class="text-sm text-ink-muted py-4">Belum ada data keperluan.</p>
        <?php else: ?>
            <?php
            $maxKeperluan = 1;
            foreach ($topKeperluan as $item) {
                if ((int) ($item['count'] ?? 0) > $maxKeperluan) {
                    $maxKeperluan = (int) $item['count'];
                }
            }
            ?>
            <div class="space-y-3">
                <?php foreach ($topKeperluan as $item): ?>
                    <?php
                    $itemCount = (int) ($item['count'] ?? 0);
                    $pct = ($maxKeperluan > 0) ? round(($itemCount / $maxKeperluan) * 100) : 0;
                    ?>
                    <div>
                        <div class="flex items-center justify-between text-sm mb-1">
                            <span class="text-ink font-medium truncate max-w-[75%]"><?= esc((string) ($item['keperluan'] ?? '')) ?></span>
                            <span class="font-bold text-ink tabular-nums"><?= esc((string) $itemCount) ?></span>
                        </div>
                        <div class="w-full bg-ground-warm h-2 rounded-full overflow-hidden">
                            <div class="bg-brand-500 h-2 rounded-full" style="width: <?= $pct ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Asal Pengunjung -->
    <div class="bg-surface border border-border p-5 flex flex-col justify-between rounded-lg">
        <div>
            <h2 class="text-base font-bold text-ink mb-1">Distribusi Asal Pengunjung</h2>
            <p class="text-xs text-ink-muted mb-4">Kategori kedatangan tamu PTSP</p>
        </div>
        <?php $perAsal = $statistik['per_asal'] ?? []; ?>
        <?php if (empty($perAsal)): ?>
            <p class="text-sm text-ink-muted py-4">Belum ada data asal pengunjung.</p>
        <?php else: ?>
            <?php
            $totalAsal = 0;
            foreach ($perAsal as $item) {
                $totalAsal += (int) ($item['count'] ?? 0);
            }
            if ($totalAsal === 0) {
                $totalAsal = 1;
            }
            ?>
            <div class="space-y-3">
                <?php foreach ($perAsal as $item): ?>
                    <?php
                    $itemCount = (int) ($item['count'] ?? 0);
                    $pct = round(($itemCount / $totalAsal) * 100);
                    ?>
                    <div>
                        <div class="flex items-center justify-between text-sm mb-1">
                            <span class="text-ink font-medium capitalize"><?= esc((string) ($item['label'] ?? '')) ?></span>
                            <span class="font-bold text-ink tabular-nums"><?= esc((string) $itemCount) ?> (<?= $pct ?>%)</span>
                        </div>
                        <div class="w-full bg-ground-warm h-2 rounded-full overflow-hidden">
                            <div class="bg-brand-gold h-2 rounded-full" style="width: <?= $pct ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $this->endSection(); ?>

<?php $this->section('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7"></script>
<script>
    (function() {
        const trenCtx = document.getElementById('trenChart').getContext('2d');
        new Chart(trenCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($trenLabels, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                datasets: [{
                    label: 'Jumlah Tamu',
                    data: <?= json_encode($trenData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                    borderColor: '#006B2E',
                    backgroundColor: 'rgba(0, 107, 46, 0.08)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#006B2E',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                }
            }
        });

        const jenisCtx = document.getElementById('jenisChart').getContext('2d');
        new Chart(jenisCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($jenisLabels, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                datasets: [{
                    data: <?= json_encode($jenisData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                    backgroundColor: ['#006B2E', '#E5A400'],
                    borderColor: '#FFFFFF',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    })();
</script>
<?php $this->endSection(); ?>

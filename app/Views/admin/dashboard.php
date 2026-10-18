<?php

use CodeIgniter\View\View;

/**
 * @var View $this
 * @var array<string, string> $pengaturan
 * @var int $unreadCount
 * @var array<string, mixed> $statistik
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

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <!-- Total Tamu -->
    <div class="bg-surface-raised border border-border p-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted mb-1">Total Tamu</p>
        <p class="text-3xl font-bold text-ink"><?= esc((string) ($statistik['total_tamu'] ?? 0)) ?></p>
    </div>

    <!-- Tamu Hari Ini -->
    <div class="bg-surface-raised border border-border p-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted mb-1">Tamu Hari Ini</p>
        <p class="text-3xl font-bold text-ink"><?= esc((string) ($statistik['tamu_hari_ini'] ?? 0)) ?></p>
    </div>

    <!-- Belum Dibaca -->
    <div class="bg-surface-raised border border-border p-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted mb-1">Belum Dibaca</p>
        <p class="text-3xl font-bold text-ink"><?= esc((string) ($statistik['unread_count'] ?? 0)) ?></p>
    </div>

    <!-- Rasio Jenis Kelamin -->
    <div class="bg-surface-raised border border-border p-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted mb-1">Rasio Jenis Kelamin</p>
        <div class="flex items-baseline gap-2">
            <span class="text-2xl font-bold text-brand-700"><?= esc((string) $lakiPercent) ?>%</span>
            <span class="text-sm text-ink-muted">Laki-laki</span>
        </div>
        <div class="flex items-baseline gap-2">
            <span class="text-2xl font-bold text-brand-gold"><?= esc((string) $perempuanPercent) ?>%</span>
            <span class="text-sm text-ink-muted">Perempuan</span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Line Chart: Tren 7 Hari -->
    <div class="lg:col-span-2 bg-surface-raised border border-border p-5">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-ink-muted mb-4">Tren Kunjungan 7 Hari Terakhir</h3>
        <div class="relative h-72">
            <canvas id="trenChart"></canvas>
        </div>
    </div>

    <!-- Doughnut Chart: Per Jenis Kelamin -->
    <div class="bg-surface-raised border border-border p-5">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-ink-muted mb-4">Jenis Kelamin</h3>
        <div class="relative h-64">
            <canvas id="jenisChart"></canvas>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Top 5 Keperluan -->
    <div class="bg-surface-raised border border-border p-5">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-ink-muted mb-4">Top 5 Keperluan</h3>
        <?php $topKeperluan = $statistik['top_keperluan'] ?? []; ?>
        <?php if (empty($topKeperluan)): ?>
            <p class="text-sm text-ink-muted">Belum ada data keperluan.</p>
        <?php else: ?>
            <ul class="divide-y divide-border">
                <?php foreach ($topKeperluan as $item): ?>
                    <li class="py-3 flex items-center justify-between">
                        <span class="text-sm text-ink truncate max-w-[70%]"><?= esc((string) ($item['keperluan'] ?? '')) ?></span>
                        <span class="text-sm font-semibold text-ink-muted"><?= esc((string) ((int) ($item['count'] ?? 0))) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <!-- Per Asal -->
    <div class="bg-surface-raised border border-border p-5">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-ink-muted mb-4">Asal Pengunjung</h3>
        <?php $perAsal = $statistik['per_asal'] ?? []; ?>
        <?php if (empty($perAsal)): ?>
            <p class="text-sm text-ink-muted">Belum ada data asal pengunjung.</p>
        <?php else: ?>
            <ul class="divide-y divide-border">
                <?php foreach ($perAsal as $item): ?>
                    <li class="py-3 flex items-center justify-between">
                        <span class="text-sm text-ink capitalize"><?= esc((string) ($item['label'] ?? '')) ?></span>
                        <span class="text-sm font-semibold text-ink-muted"><?= esc((string) ((int) ($item['count'] ?? 0))) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
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
                    backgroundColor: 'rgba(0, 107, 46, 0.1)',
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

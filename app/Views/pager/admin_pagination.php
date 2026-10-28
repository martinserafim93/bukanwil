<?php

use CodeIgniter\Pager\PagerRenderer;

/**
 * Template Pagination Tailwind untuk Panel Admin
 *
 * @var PagerRenderer $pager
 */
$pager->setSurroundCount(2);
?>

<nav aria-label="Navigasi halaman" class="flex flex-wrap items-center gap-1.5" data-ui>
    <?php if ($pager->hasPrevious()): ?>
        <a
            href="<?= $pager->getFirst() ?>"
            aria-label="Halaman pertama"
            class="px-2.5 py-1.5 text-xs font-medium text-ink bg-surface border border-border rounded-lg hover:bg-surface-raised transition-colors tabular-nums"
        >
            &laquo; Pertama
        </a>
        <a
            href="<?= $pager->getPrevious() ?>"
            aria-label="Halaman sebelumnya"
            class="px-2.5 py-1.5 text-xs font-medium text-ink bg-surface border border-border rounded-lg hover:bg-surface-raised transition-colors tabular-nums"
        >
            &lsaquo; Sebelumnya
        </a>
    <?php endif; ?>

    <?php foreach ($pager->links() as $link): ?>
        <?php if ($link['active']): ?>
            <span
                aria-current="page"
                class="px-3 py-1.5 text-xs font-bold text-white bg-brand-500 border border-brand-500 rounded-lg tabular-nums"
            >
                <?= $link['title'] ?>
            </span>
        <?php else: ?>
            <a
                href="<?= $link['uri'] ?>"
                class="px-3 py-1.5 text-xs font-medium text-ink bg-surface border border-border rounded-lg hover:bg-surface-raised transition-colors tabular-nums"
            >
                <?= $link['title'] ?>
            </a>
        <?php endif; ?>
    <?php endforeach; ?>

    <?php if ($pager->hasNext()): ?>
        <a
            href="<?= $pager->getNext() ?>"
            aria-label="Halaman berikutnya"
            class="px-2.5 py-1.5 text-xs font-medium text-ink bg-surface border border-border rounded-lg hover:bg-surface-raised transition-colors tabular-nums"
        >
            Berikutnya &rsaquo;
        </a>
        <a
            href="<?= $pager->getLast() ?>"
            aria-label="Halaman terakhir"
            class="px-2.5 py-1.5 text-xs font-medium text-ink bg-surface border border-border rounded-lg hover:bg-surface-raised transition-colors tabular-nums"
        >
            Terakhir &raquo;
        </a>
    <?php endif; ?>
</nav>

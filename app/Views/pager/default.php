<?php
/**
 * Minimal Tailwind pager template for CI4.
 *
 * @var \CodeIgniter\Pager\Pager $pager
 */

$pagel = $pager;
?>

<?php if ($pagel->getPageCount() > 1): ?>
    <nav aria-label="Pagination" class="inline-flex items-center gap-2">
        <?php if ($pagel->hasPreviousPage()): ?>
            <a href="<?= esc($pagel->getPreviousPageURI()) ?>" class="px-3 py-1.5 text-sm border border-border rounded hover:bg-surface-raised text-ink">
                Prev
            </a>
        <?php else: ?>
            <span class="px-3 py-1.5 text-sm border border-border rounded text-ink-muted cursor-not-allowed">
                Prev
            </span>
        <?php endif; ?>

        <?php foreach ($pagel->links() as $link): ?>
            <?php if ($link['active']): ?>
                <span class="px-3 py-1.5 text-sm border border-brand-500 bg-brand-500 text-white rounded">
                    <?= esc((string) $link['title']) ?>
                </span>
            <?php else: ?>
                <a href="<?= esc($link['uri']) ?>" class="px-3 py-1.5 text-sm border border-border rounded hover:bg-surface-raised text-ink">
                    <?= esc((string) $link['title']) ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if ($pagel->hasNextPage()): ?>
            <a href="<?= esc($pagel->getNextPageURI()) ?>" class="px-3 py-1.5 text-sm border border-border rounded hover:bg-surface-raised text-ink">
                Next
            </a>
        <?php else: ?>
            <span class="px-3 py-1.5 text-sm border border-border rounded text-ink-muted cursor-not-allowed">
                Next
            </span>
        <?php endif; ?>
    </nav>
<?php endif; ?>

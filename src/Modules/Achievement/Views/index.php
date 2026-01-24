<?php /** @var array $achievements */ use Core\Renderer; ?>
<main style="padding:1rem;max-width:900px;margin:0 auto">
    <h1>Achievements</h1>
    <?php if (!empty($categories)): ?>
        <form method="get" action="/achievements">
            <label>Filter by category:
                <select name="category" onchange="this.form.submit()">
                    <option value="">All</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= Renderer::e($c->id) ?>" <?= (isset($category_id) && $category_id == $c->id) ? 'selected' : '' ?>><?= Renderer::e($c->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </form>
    <?php endif; ?>

    <p><a href="/achievements/create">Create new achievement</a></p>

    <?php 
    // Group achievements by category if not filtering by single category
    $grouped = [];
    if (!isset($category_id) || $category_id === null) {
        foreach ($achievements as $a) {
            if (!isset($grouped[$a->categoryId])) {
                $grouped[$a->categoryId] = [];
            }
            $grouped[$a->categoryId][] = $a;
        }
        
        // Sort each group by display_order
        foreach ($grouped as $catId => &$group) {
            usort($group, function($a, $b) {
                return $a->displayOrder <=> $b->displayOrder;
            });
        }
        
        // Sort categories by display_order
        $sortedCats = $categories;
        usort($sortedCats, function($a, $b) {
            return $a->displayOrder <=> $b->displayOrder;
        });
    ?>
        <?php foreach ($sortedCats as $cat): ?>
            <?php if (isset($grouped[$cat->id])): ?>
                <h2><?= Renderer::e($cat->name) ?></h2>
                <ul id="achievements-list-<?= Renderer::e($cat->id) ?>" class="achievements-list">
                    <?php foreach ($grouped[$cat->id] as $a): ?>
                        <li data-id="<?= Renderer::e($a->id) ?>" draggable="true" class="draggable-item">
                            <span class="drag-handle">☰</span>
                            <span><strong><?= Renderer::e($a->title) ?></strong>
                            (<?= Renderer::e($a->points) ?> pts)</span>
                            <a href="/achievements/<?= Renderer::e($a->id) ?>/edit" style="margin-left:auto;font-size:0.9rem">[edit]</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <script>
                    if (window.sortableInit) {
                        window.sortableInit('#achievements-list-<?= Renderer::e($cat->id) ?>','li[data-id]','achievement');
                    }
                </script>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php } else { ?>
        <!-- Filtered view: show single category -->
        <ul id="achievements-list">
            <?php foreach ($achievements as $a): ?>
                <li data-id="<?= Renderer::e($a->id) ?>" draggable="true" class="draggable-item">
                    <span class="drag-handle">☰</span>
                    <span><strong><?= Renderer::e($a->title) ?></strong>
                    (<?= Renderer::e($a->points) ?> pts)</span>
                    <a href="/achievements/<?= Renderer::e($a->id) ?>/edit" style="margin-left:auto;font-size:0.9rem">[edit]</a>
                </li>
            <?php endforeach; ?>
        </ul>
        <script>
            if (window.sortableInit) {
                window.sortableInit('#achievements-list','li[data-id]','achievement');
            }
        </script>
    <?php } ?>
</main>

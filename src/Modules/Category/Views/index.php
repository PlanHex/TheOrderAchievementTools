<?php use Core\Renderer; ?>
<main style="padding:1rem;max-width:900px;margin:0 auto">
    <h1>Categories</h1>
    <p><a href="/categories/create">Create new category</a></p>
    <ul id="categories-list">
        <?php foreach ($categories as $c): ?>
            <li data-id="<?= Renderer::e($c->id) ?>" draggable="true" class="draggable-item">
                <span class="drag-handle">☰</span>
                <span><?= Renderer::e($c->name) ?> (order: <?= Renderer::e($c->displayOrder) ?>)</span>
                <form method="post" action="/categories/<?= Renderer::e($c->id) ?>/sort-alphabetically" style="display:inline;margin-left:auto">
                    <?= \Core\Csrf::input() ?>
                    <button type="submit" style="font-size:0.9rem;padding:0.25rem 0.5rem">Sort achievements A-Z</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>

    <script>
        if (window.sortableInit) {
            window.sortableInit('#categories-list','li[data-id]','category');
        }
    </script>
</main>

<?php use Core\Renderer; ?>
<main style="padding:1rem;max-width:900px;margin:0 auto">
    <h1>Categories</h1>
    <p><a href="/categories/create">Create new category</a></p>
    <ul id="categories-list">
        <?php foreach ($categories as $c): ?>
            <li data-id="<?= Renderer::e($c->id) ?>" draggable="true" class="draggable-item">
                <span class="drag-handle">☰</span>
                <span><?= Renderer::e($c->name) ?> (order: <?= Renderer::e($c->displayOrder) ?>)</span>
            </li>
        <?php endforeach; ?>
    </ul>

    <script>
        if (window.sortableInit) {
            window.sortableInit('#categories-list','li[data-id]','category');
        }
    </script>
</main>

<?php use Core\Renderer; ?>
<main style="padding:1rem;max-width:900px;margin:0 auto">
    <h1>Categories</h1>
    <p><a href="/categories/create">Create new category</a></p>
    <ul id="categories-list">
        <?php foreach ($categories as $c): ?>
            <li>
                <span><?= Renderer::e($c->name) ?> (order: <?= Renderer::e($c->displayOrder) ?>)</span>
            </li>
        <?php endforeach; ?>
    </ul>
</main>

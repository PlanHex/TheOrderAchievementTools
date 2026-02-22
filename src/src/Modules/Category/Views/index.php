<?php use Core\Renderer; ?>
<main style="padding:1rem;max-width:900px;margin:0 auto">
    <h1>Categories</h1>
    <p><a href="/categories/create">Create new category</a></p>
    <ul id="categories-list">
        <?php foreach ($categories as $c): ?>
            <li style="display:flex;align-items:center;justify-content:space-between;padding:0.5rem 0;border-bottom:1px solid #eee">
                <span><?= Renderer::e($c->name) ?> (order: <?= (int)$c->displayOrder ?>)</span>
                <div style="display:flex;gap:0.5rem">
                    <a href="/categories/<?= (int)$c->id ?>/edit" style="padding:0.25rem 0.5rem">Edit</a>
                    <form method="post" action="/categories/<?= (int)$c->id ?>/delete" style="margin:0;display:inline">
                        <?= \Core\Csrf::input() ?>
                        <button type="submit" style="padding:0.25rem 0.5rem" onclick="return confirm('Delete this category? Achievements must be deleted first.')">Delete</button>
                    </form>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</main>

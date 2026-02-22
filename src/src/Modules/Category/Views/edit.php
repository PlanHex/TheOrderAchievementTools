<?php use Core\Renderer; ?>
<main style="padding:1rem;max-width:900px;margin:0 auto">
    <h1>Edit Category</h1>
    <form method="post" action="/categories/<?= (int)$category->id ?>/update">
        <?= \Core\Csrf::input() ?>
        <div>
            <label>Name: <input name="name" value="<?= Renderer::e($category->name) ?>" required></label>
        </div>
        <div>
            <label>Display Order: <input name="display_order" type="number" value="<?= (int)$category->displayOrder ?>"></label>
        </div>
        <div style="margin-top:1rem">
            <button type="submit">Save</button>
            <a href="/categories">Cancel</a>
        </div>
    </form>
</main>

<?php use Core\Renderer; ?>
<main style="padding:1rem;max-width:900px;margin:0 auto">
    <h1>Edit Achievement</h1>
    <form method="post" action="/achievements/<?= Renderer::e($achievement->id) ?>/update">
        <?= \Core\Csrf::input() ?>
        <div>
            <label>Title: <input name="title" required value="<?= Renderer::e($achievement->title) ?>"></label>
        </div>
        <div>
            <label>Category:
                <select name="category_id">
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= Renderer::e($c->id) ?>" <?= $achievement->categoryId == $c->id ? 'selected' : '' ?>><?= Renderer::e($c->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
        <div>
            <label>Points: <input name="points" type="number" value="<?= Renderer::e($achievement->points) ?>"></label>
        </div>
        <div>
            <label>Image URL: <input name="image_url" value="<?= Renderer::e($achievement->imageUrl ?? '') ?>"></label>
        </div>
        <div>
            <label>Description:<br>
                <textarea name="description" rows="4" cols="60"><?= Renderer::e($achievement->description ?? '') ?></textarea>
            </label>
        </div>
        <div>
            <button type="submit">Update</button>
            <a href="/achievements">Cancel</a>
        </div>
    </form>
</main>

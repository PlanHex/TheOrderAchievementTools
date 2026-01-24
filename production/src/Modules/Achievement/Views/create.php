<?php use Core\Renderer; ?>
<main style="padding:1rem;max-width:900px;margin:0 auto">
    <h1>Create Achievement</h1>
    <form method="post" action="/achievements/store">
        <?= \Core\Csrf::input() ?>
        <div>
            <label>Title: <input name="title" required></label>
        </div>
        <div>
            <label>Category:
                <select name="category_id">
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= Renderer::e($c->id) ?>"><?= Renderer::e($c->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
        <div>
            <label>Points: <input name="points" type="number" value="0"></label>
        </div>
        <div>
            <label>Image URL: <input name="image_url"></label>
        </div>
        <div>
            <label>Description:<br>
                <textarea name="description" rows="4" cols="60"></textarea>
            </label>
        </div>
        <div>
            <button type="submit">Create</button>
            <a href="/achievements">Cancel</a>
        </div>
    </form>
</main>

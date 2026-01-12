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

    <ul id="achievements-list">
        <?php foreach ($achievements as $a): ?>
            <li data-id="<?= Renderer::e($a->id) ?>" draggable="true" class="draggable-item">
                <span class="drag-handle">☰</span>
                <span><strong><?= Renderer::e($a->title) ?></strong>
                (<?= Renderer::e($a->points) ?> pts)</span>
            </li>
        <?php endforeach; ?>
    </ul>

    <script>
        if (window.sortableInit) {
            window.sortableInit('#achievements-list','li[data-id]','achievement');
        }
    </script>
</main>

<?php use Core\Renderer; ?>
<main style="padding:1rem;max-width:900px;margin:0 auto">
    <h1>Sort achievements in category: <?= Renderer::e($category->name) ?></h1>

    <form method="post" action="/achievements/<?= Renderer::e($category->id) ?>/reorder">
        <?= \Core\Csrf::input() ?>
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr>
                    <th style="text-align:left">Achievement</th>
                    <th style="width:140px">Display Order</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($achievements as $a): ?>
                <tr>
                    <td><?= Renderer::e($a->title) ?></td>
                    <td>
                        <input type="number" name="orders[<?= Renderer::e($a->id) ?>]" value="<?= Renderer::e($a->displayOrder) ?>" style="width:100px">
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div style="margin-top:1rem">
            <button type="submit">Save order</button>
            <a href="/achievements?category=<?= Renderer::e($category->id) ?>" style="margin-left:1rem">Cancel</a>
        </div>
    </form>
</main>

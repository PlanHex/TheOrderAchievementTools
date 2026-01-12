<?php use Core\Renderer; ?>
<main style="padding:1rem;max-width:900px;margin:0 auto">
    <h1>Users</h1>
    <ul>
        <?php foreach ($users as $u): ?>
            <li>
                <?= Renderer::e($u->name) ?> — <a href="/export/roster?user_id=<?= Renderer::e($u->id) ?>">Export roster</a>
            </li>
        <?php endforeach; ?>
    </ul>
</main>

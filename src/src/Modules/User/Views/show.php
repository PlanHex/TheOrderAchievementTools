<?php use Core\Renderer; ?>
<main style="padding:1rem;max-width:900px;margin:0 auto">
    <h1>User: <?= Renderer::e($user->name) ?></h1>
    <p><a href="/users" class="btn">Back to Users</a>
       <a href="/export/roster?user_id=<?= (int)$user->id ?>" class="btn" style="margin-left:0.5rem">Export Roster</a></p>

    <h2>Assigned achievements</h2>
    <?php if (empty($achievements)): ?>
        <p>No achievements assigned.</p>
    <?php else: ?>
        <ul id="user-achievements-list">
            <?php foreach ($achievements as $a): ?>
                <li>
                    <span><?= Renderer::e($a->title) ?> (<?= Renderer::e($a->points) ?> pts)</span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</main>

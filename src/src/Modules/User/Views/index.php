<?php use Core\Renderer; ?>
<main style="padding:1rem;max-width:900px;margin:0 auto">
    <h1>Users</h1>
    <p><a href="/users/create">Create new user</a></p>
    <ul style="list-style:none;padding:0">
        <?php foreach ($users as $u): ?>
            <li style="display:flex;align-items:center;justify-content:space-between;padding:0.5rem 0;border-bottom:1px solid #eee">
                <span><?= Renderer::e($u->name) ?></span>
                <div style="display:flex;gap:0.5rem">
                    <a href="/users/<?= (int)$u->id ?>">View</a>
                    <a href="/users/<?= (int)$u->id ?>/edit">Edit</a>
                    <a href="/export/roster?user_id=<?= (int)$u->id ?>">Export</a>
                    <form method="post" action="/users/<?= (int)$u->id ?>/delete" style="margin:0;display:inline">
                        <?= \Core\Csrf::input() ?>
                        <button type="submit" onclick="return confirm('Delete this user? All achievement assignments will be lost.')">Delete</button>
                    </form>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</main>

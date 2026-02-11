<?php use Core\Renderer; ?>
<main style="padding:1rem;max-width:900px;margin:0 auto">
    <h1>Edit User: <?= Renderer::e($user->name) ?></h1>

    <form method="post" action="/users/<?= (int)$user->id ?>/update">
        <?= \Core\Csrf::input() ?>
        <div>
            <label>Name: <input name="name" value="<?= Renderer::e($user->name) ?>" required></label>
        </div>
        <div style="margin-top:1rem">
            <button type="submit">Save</button>
            <a href="/users" style="margin-left:1rem">Cancel</a>
        </div>
    </form>

    <hr />

    <?php
    // $achievements: Achievement[] (all)
    // $assignedMap: array achievement_id => display_order
    $assignedMap = $assignedMap ?? [];
    $assigned = [];
    $unassigned = [];
    foreach ($achievements as $a) {
        if (isset($assignedMap[$a->id])) {
            $assigned[] = ['ach' => $a, 'order' => $assignedMap[$a->id]];
        } else {
            $unassigned[] = $a;
        }
    }

    // compute next default order
    $nextOrder = 1;
    if (!empty($assignedMap)) {
        $nextOrder = max(array_values($assignedMap)) + 1;
    }
    ?>

    <h2>Assign Achievements</h2>
    <p>Search and click <strong>Add</strong> to assign an achievement to this user.</p>
    <div>
        <input id="achievement-search" placeholder="Search achievements by name" style="width:100%;padding:0.5rem;margin-bottom:0.5rem">
        <ul id="achievements-list" style="list-style:none;padding:0;margin:0">
            <?php foreach ($unassigned as $a): ?>
                <li data-name="<?= htmlspecialchars($a->title, ENT_QUOTES) ?>" style="display:flex;align-items:center;padding:0.25rem 0;border-bottom:1px solid #eee">
                    <span style="flex:1"><?= Renderer::e($a->title) ?> (<?= Renderer::e($a->points) ?> pts)</span>
                    <form method="post" action="/users/<?= (int)$user->id ?>/achievements/add" style="margin:0">
                        <?= \Core\Csrf::input() ?>
                        <input type="hidden" name="achievement_id" value="<?= (int)$a->id ?>">
                        <input type="hidden" name="display_order" value="<?= (int)$nextOrder ?>">
                        <button type="submit">Add</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <hr />

    <h2>Assigned Achievements</h2>
    <?php if (empty($assigned)): ?>
        <p>No achievements assigned.</p>
    <?php else: ?>
        <form method="post" action="/users/<?= (int)$user->id ?>/achievements/reorder">
            <?= \Core\Csrf::input() ?>
            <table style="width:100%;border-collapse:collapse">
                <thead>
                    <tr>
                        <th style="text-align:left">Achievement</th>
                        <th style="width:140px">Display Order</th>
                        <th style="width:120px"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assigned as $row): $a = $row['ach']; $order = $row['order']; ?>
                    <tr>
                        <td><?= Renderer::e($a->title) ?></td>
                        <td><input type="number" name="orders[<?= (int)$a->id ?>]" value="<?= (int)$order ?>" style="width:100px"></td>
                        <td>
                            <form method="post" action="/users/<?= (int)$user->id ?>/achievements/<?= (int)$a->id ?>/remove" style="margin:0">
                                <?= \Core\Csrf::input() ?>
                                <button type="submit">Remove</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div style="margin-top:1rem">
                <button type="submit">Save order</button>
            </div>
        </form>
    <?php endif; ?>

    <script src="/assets/js/search.js"></script>
    <script>window.searchableInit && window.searchableInit('#achievement-search','#achievements-list');</script>
</main>

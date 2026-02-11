[b]COMPLETE ROSTER[/b]

<?php foreach ($usersData as $userData): ?>
[b]<?= \Core\Renderer::e($userData['user']->name) ?>[/b]

<?php foreach ($userData['achievements'] as $ach): ?>
• <?= \Core\Renderer::e($ach->title) ?> [+<?= (int)$ach->points ?>]
<?php endforeach; ?>

<?php endforeach; ?>

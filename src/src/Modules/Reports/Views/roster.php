[b]<?= \Core\Renderer::e($user->name) ?> ACHIEVEMENTS[/b]

<?php foreach ($achievements as $ach): ?>
• <?= \Core\Renderer::e($ach->title) ?> [+<?= (int)$ach->points ?>]
<?php if ($ach->description): ?>
  <?= \Core\Renderer::e($ach->description) ?>
<?php endif; ?>

<?php endforeach; ?>

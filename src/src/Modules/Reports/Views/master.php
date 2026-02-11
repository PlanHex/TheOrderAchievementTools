[b]MASTER ACHIEVEMENT LIST[/b]

<?php foreach ($groups as $catId => $data): ?>
[b]<?= \Core\Renderer::e($data['category']->name) ?>[/b]

<?php foreach ($data['achievements'] as $ach): ?>
• <?= \Core\Renderer::e($ach->title) ?> [+<?= (int)$ach->points ?>]
<?php if ($ach->description): ?>
  <?= \Core\Renderer::e($ach->description) ?>
<?php endif; ?>

<?php endforeach; ?>
<?php endforeach; ?>

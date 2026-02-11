<?php use Core\Renderer; ?>
<main style="padding:1rem;max-width:900px;margin:0 auto">
    <h1>Complete Roster (BBCode)</h1>
    <pre style="white-space:pre-wrap;background:#fff;padding:1rem;border:1px solid #ddd">
<?php foreach ($usersData as $userData):
    $user = $userData['user'];
    $achievements = $userData['achievements'];
?>
[B]<?= Renderer::e($user->name) ?>[/B]

<?php
// Build the image list with title attributes
$imgList = [];
foreach ($achievements as $a) {
    $titleAttr = 'title="' . htmlspecialchars($a->title) . ' (' . $a->points . 'p)"';
    $imgTag = '[IMG ' . $titleAttr . ']' . htmlspecialchars($a->imageUrl) . '[/IMG]';
    $imgList[] = $imgTag;
}
$renderString = implode(' ', $imgList);

// Output the rendered images (empty if no achievements)
if (!empty($imgList)) {
    echo $renderString . "\n\n";
    
    // Output the Code block
    echo "Code:\n";
    echo "[plain]" . $renderString . "[/plain]\n";
}
?>

<?php endforeach; ?></pre>
</main>

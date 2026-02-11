<?php use Core\Renderer; ?>
<main style="padding:1rem;max-width:900px;margin:0 auto">
    <h1>Roster for <?= Renderer::e($user->name) ?> (BBCode)</h1>
    <pre style="white-space:pre-wrap;background:#fff;padding:1rem;border:1px solid #ddd">
<?php
// Build the image list with title attributes
$imgList = [];
foreach ($achievements as $a) {
    $titleAttr = 'title="' . htmlspecialchars($a->title) . ' (' . $a->points . 'p)"';
    $imgTag = '[IMG ' . $titleAttr . ']' . htmlspecialchars($a->imageUrl) . '[/IMG]';
    $imgList[] = $imgTag;
}
$renderString = implode(' ', $imgList);

// Output the rendered images (only if achievements exist)
if (!empty($imgList)) {
    echo $renderString . "\n\n";
    
    // Output the Code block
    echo "Code:\n";
    echo "[plain]" . $renderString . "[/plain]\n";
}
?></pre>
</main>

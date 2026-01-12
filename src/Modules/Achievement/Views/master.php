<?php use Core\Renderer; ?>
<main style="padding:1rem;max-width:900px;margin:0 auto">
    <h1>Master List (BBCode)</h1>
    <pre style="white-space:pre-wrap;background:#fff;padding:1rem;border:1px solid #ddd">
<?php foreach ($groups as $g):
    $cat = $g['category'];
    echo "[b]" . htmlspecialchars($cat->name) . "[/b]\n";
    foreach ($g['achievements'] as $a) {
        echo "[img]" . htmlspecialchars($a->imageUrl) . "[/img] ";
        echo "[b]" . htmlspecialchars($a->title) . "[/b] — " . htmlspecialchars($a->description) . "\n";
    }
    echo "\n";
endforeach; ?></pre>
</main>

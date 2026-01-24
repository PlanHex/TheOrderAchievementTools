<?php use Core\Renderer; ?>
<main style="padding:1rem;max-width:900px;margin:0 auto">
    <h1>Master List (BBCode)</h1>
    <pre style="white-space:pre-wrap;background:#fff;padding:1rem;border:1px solid #ddd">
<?php foreach ($groups as $g):
    $cat = $g['category'];
    echo "[B]" . htmlspecialchars($cat->name) . "[/B]\n\n";
    foreach ($g['achievements'] as $a) {
        // Format: [IMG]url[/IMG][B]Title (Pts)[/B], Category - Description [code][IMG]url[/IMG][/code]
        $ptsStr = "(" . $a->points . "p)";
        $descStr = "";
        if (!empty($a->description)) {
            $descStr = ", " . htmlspecialchars($cat->name) . " - " . htmlspecialchars($a->description);
        }
        echo "[IMG]" . htmlspecialchars($a->imageUrl) . "[/IMG][B]" . htmlspecialchars($a->title) . " " . $ptsStr . "[/B]" . $descStr . "[code][IMG]" . htmlspecialchars($a->imageUrl) . "[/IMG][/code]\n";
        echo "\n";
    }
    echo "\n";
endforeach; ?></pre>
</main>

<?php use Core\Renderer; ?>
<main style="padding:1rem;max-width:900px;margin:0 auto">
    <h1>Roster for <?= Renderer::e($user->name) ?> (BBCode)</h1>
    <pre style="white-space:pre-wrap;background:#fff;padding:1rem;border:1px solid #ddd">
<?php foreach ($achievements as $a) {
    echo "[img]" . htmlspecialchars($a->imageUrl) . "[/img] ";
    echo "[b]" . htmlspecialchars($a->title) . "[/b] — " . htmlspecialchars($a->description) . "\n";
} ?></pre>
</main>

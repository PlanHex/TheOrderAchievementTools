<?php
// Basic front controller placeholder
session_start();

$config = require __DIR__ . '/../config/app.php';

// Very small router fallback — will be replaced by src/Core/Router.php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($path === '/' || $path === '/index.php') {
    require __DIR__ . '/../templates/header.php';
    echo "<main style=\"padding:1rem\"><h1>The Order Achievements Tool</h1>";
    echo "<p>Mode: <strong>" . htmlspecialchars($config['mode']) . "</strong></p>";
    echo "<ul><li><a href=\"/export/master\">Export: Master List</a></li>";
    echo "<li><a href=\"/export/roster?user_id=1\">Export: Roster (user 1)</a></li></ul></main>";
    require __DIR__ . '/../templates/footer.php';
    exit;
}

// Not found (placeholder)
http_response_code(404);
require __DIR__ . '/../templates/header.php';
echo "<main style=\"padding:1rem\"><h1>404 — Not Found</h1><p>Route: " . htmlspecialchars($path) . "</p></main>";
require __DIR__ . '/../templates/footer.php';

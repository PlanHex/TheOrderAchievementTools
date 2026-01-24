<?php
// Seed demo session data by instantiating InMemory repositories.
session_start();
unset($_SESSION['inmemory_categories'], $_SESSION['inmemory_achievements'], $_SESSION['inmemory_users'], $_SESSION['inmemory_user_achievements']);

// basic autoloader
spl_autoload_register(function ($class) {
    // Scripts are in development/scripts, production code is in production/src
    $base = __DIR__ . '/../../production/';
    $file = $base . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) { require $file; return true; }
    $file = $base . 'src/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) { require $file; return true; }
    return false;
});

$dataDir = __DIR__ . '/../../data';
new \Infrastructure\Persistence\InMemory\CategoryRepository($dataDir);
new \Infrastructure\Persistence\InMemory\AchievementRepository($dataDir);
new \Infrastructure\Persistence\InMemory\UserRepository($dataDir);
// Only echo when executed directly (not when required by tests)
if (php_sapi_name() === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === realpath(__FILE__)) {
    echo "Seeded session with demo CSV data.\n";
}

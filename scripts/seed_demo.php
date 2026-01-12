<?php
// Seed demo session data by instantiating InMemory repositories.
session_start();
unset($_SESSION['inmemory_categories'], $_SESSION['inmemory_achievements'], $_SESSION['inmemory_users'], $_SESSION['inmemory_user_achievements']);

// basic autoloader
spl_autoload_register(function ($class) {
    $base = __DIR__ . '/../';
    $file = $base . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) { require $file; return true; }
    $file = $base . 'src/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) { require $file; return true; }
    return false;
});

$dataDir = __DIR__ . '/../data';
new \Infrastructure\Persistence\InMemory\CategoryRepository($dataDir);
new \Infrastructure\Persistence\InMemory\AchievementRepository($dataDir);
new \Infrastructure\Persistence\InMemory\UserRepository($dataDir);

echo "Seeded session with demo CSV data.\n";

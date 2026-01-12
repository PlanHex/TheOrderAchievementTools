<?php
// Simple smoke test: ensure CSVs load and repositories expose counts.
session_start();

// basic autoloader
spl_autoload_register(function ($class) {
    $base = __DIR__ . '/../';
    $file = $base . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) { require $file; return true; }
    $file = $base . 'src/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) { require $file; return true; }
    return false;
});

echo "Running smoke tests...\n";

$container = new \Core\Container();
$catRepo = $container->get('category_repository');
$achRepo = $container->get('achievement_repository');
$userRepo = $container->get('user_repository');

$cats = $catRepo->all();
$achs = $achRepo->all();
$users = $userRepo->all();

echo sprintf("Categories: %d\n", count($cats));
echo sprintf("Achievements: %d\n", count($achs));
echo sprintf("Users: %d\n", count($users));

$ok = count($cats) > 0 && count($achs) > 0 && count($users) > 0;
exit($ok ? 0 : 2);

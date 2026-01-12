<?php
// Simple bootstrap for tests: set up session and autoloader
session_start();

spl_autoload_register(function ($class) {
    $base = __DIR__ . '/../';
    $file = $base . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) { require $file; return true; }
    $file = $base . 'src/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) { require $file; return true; }
    return false;
});

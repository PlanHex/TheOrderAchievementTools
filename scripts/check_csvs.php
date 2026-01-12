<?php
// Check presence and size of expected data CSVs
$files = [
    __DIR__ . '/../data/categories.csv',
    __DIR__ . '/../data/achievements.csv',
    __DIR__ . '/../data/users.csv',
    __DIR__ . '/../data/user_achievements.csv',
];
$ok = true;
foreach ($files as $f) {
    if (!file_exists($f)) {
        echo "Missing: $f\n";
        $ok = false;
    } elseif (filesize($f) < 10) {
        echo "Too small: $f\n";
        $ok = false;
    } else {
        echo "OK: $f\n";
    }
}
exit($ok ? 0 : 1);

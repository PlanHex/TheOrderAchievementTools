<?php
/**
 * Database Setup Script
 *
 * Initializes MySQL database with schema for local development or CI/CD pipelines.
 * Usage: php development/scripts/setup-db.php [--config=/path/to/config.php]
 *
 * This script:
 * 1. Reads database credentials from production/config/database.php
 * 2. Loads SQL schema from data/sql/sql_tables.sql
 * 3. Creates database and tables
 * 4. Optionally seeds with CSV data (for testing)
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Parse command-line arguments
$configFile = __DIR__ . '/../../production/config/database.php';
$schemaFile = __DIR__ . '/../../data/sql/sql_tables.sql';
$seedData = false;

foreach ($argv as $i => $arg) {
    if (strpos($arg, '--config=') === 0) {
        $configFile = str_replace('--config=', '', $arg);
    }
    if ($arg === '--seed') {
        $seedData = true;
    }
    if ($arg === '--help' || $arg === '-h') {
        showHelp();
        exit(0);
    }
}

// Verify files exist
if (!file_exists($configFile)) {
    echo "❌ Config file not found: $configFile\n";
    exit(1);
}

if (!file_exists($schemaFile)) {
    echo "❌ Schema file not found: $schemaFile\n";
    exit(1);
}

// Load database config
$dbConfig = require $configFile;

if (!isset($dbConfig['host'], $dbConfig['user'], $dbConfig['password'])) {
    echo "❌ Invalid database config: missing host, user, or password\n";
    exit(1);
}

$host = $dbConfig['host'];
$user = $dbConfig['user'];
$pass = $dbConfig['password'];
$database = $dbConfig['database'] ?? 'order_achievements';

echo "🔧 Setting up database: $database\n";
echo "   Host: $host\n";
echo "   User: $user\n";

try {
    // Connect to MySQL (without specifying database)
    $pdo = new PDO(
        "mysql:host=$host",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]
    );

    // Read schema file
    $schema = file_get_contents($schemaFile);

    // Split by statement delimiter and execute each
    $statements = array_filter(array_map('trim', preg_split('/;/', $schema)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            echo "   ▪ Executing: " . substr($statement, 0, 50) . "...\n";
            $pdo->exec($statement);
        }
    }

    echo "✅ Database initialized successfully!\n";

    // Optional: Seed with CSV data
    if ($seedData) {
        echo "🌱 Seeding data from CSV files...\n";
        seedFromCsv($pdo, __DIR__ . '/../../data');
        echo "✅ Data seeded successfully!\n";
    }

    // Verify tables exist
    $tables = $pdo->query("SHOW TABLES IN $database")->fetchAll(PDO::FETCH_COLUMN);
    echo "\n📊 Tables created: " . count($tables) . "\n";
    foreach ($tables as $table) {
        echo "   ✓ $table\n";
    }

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

/**
 * Seed database from CSV files in development/testing
 */
function seedFromCsv($pdo, $dataDir)
{
    $csvFiles = [
        'categories.csv' => 'categories',
        'achievements.csv' => 'achievements',
        'users.csv' => 'users',
        'user_achievements.csv' => 'user_achievements',
    ];

    foreach ($csvFiles as $file => $table) {
        $path = "$dataDir/$file";
        
        if (!file_exists($path)) {
            echo "   ⚠️  Missing: $file (skipping)\n";
            continue;
        }

        $rows = 0;
        if ($handle = fopen($path, 'r')) {
            $header = fgetcsv($handle);
            if (!$header) {
                fclose($handle);
                continue;
            }

            while (($data = fgetcsv($handle)) !== false) {
                if (empty($data[0])) continue; // Skip empty rows

                $columns = implode(',', array_map(fn($col) => "`$col`", $header));
                $placeholders = implode(',', array_fill(0, count($header), '?'));
                $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";

                try {
                    $pdo->prepare($sql)->execute(array_map(function($val) {
                        // Convert empty strings to null for nullable fields
                        return $val === '' ? null : $val;
                    }, $data));
                    $rows++;
                } catch (PDOException $e) {
                    echo "   ⚠️  Error importing row from $file: " . $e->getMessage() . "\n";
                }
            }
            fclose($handle);
            echo "   ✓ Seeded $table: $rows rows\n";
        }
    }
}

function showHelp()
{
    echo <<<HELP
Database Setup Script

Usage: php development/scripts/setup-db.php [OPTIONS]

Options:
  --config=PATH     Path to database config file (default: production/config/database.php)
  --seed            Seed database with CSV data (optional)
  -h, --help        Show this help message

Examples:
  # Basic setup
  php development/scripts/setup-db.php

  # Setup and seed with CSV data
  php development/scripts/setup-db.php --seed

  # Use custom config
  php development/scripts/setup-db.php --config=/path/to/custom-db.php --seed

HELP;
}

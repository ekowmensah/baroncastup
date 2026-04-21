<?php
/**
 * Migration Runner
 * 
 * Simple script to run database migrations safely
 */

// Ensure this is run from command line only
if (php_sapi_name() !== 'cli') {
    die("This script can only be run from the command line for security reasons.\n");
}

echo "SmartCast Migration Runner\n";
echo "==========================\n\n";

// Check if migration file is specified
if ($argc < 2) {
    echo "Usage: php run_migration.php <migration_file>\n";
    echo "Example: php run_migration.php migrations/migrate_old_events_revenue.php\n\n";
    echo "Available migrations:\n";
    
    $migrationDir = __DIR__ . '/migrations';
    if (is_dir($migrationDir)) {
        $files = glob($migrationDir . '/*.php');
        foreach ($files as $file) {
            echo "  - " . basename($file) . "\n";
        }
    } else {
        echo "  No migrations directory found.\n";
    }
    exit(1);
}

$migrationFile = $argv[1];

// Ensure the file exists
if (!file_exists($migrationFile)) {
    die("Migration file not found: {$migrationFile}\n");
}

// Confirm before running
echo "About to run migration: " . basename($migrationFile) . "\n";
echo "This will modify your database. Are you sure? (yes/no): ";

$handle = fopen("php://stdin", "r");
$confirmation = trim(fgets($handle));
fclose($handle);

if (strtolower($confirmation) !== 'yes') {
    echo "Migration cancelled.\n";
    exit(0);
}

echo "\nRunning migration...\n";
echo str_repeat("-", 40) . "\n";

// Include and run the migration
require_once $migrationFile;

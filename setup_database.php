<?php
/**
 * Database Setup Script
 * Run this once after deployment to initialize the database
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/db.php';

echo "Starting database setup...\n\n";

$pdo = Database::connect();

if (!$pdo) {
    die("ERROR: Could not connect to database. Check your .env credentials.\n");
}

echo "✓ Database connection successful\n";

// Read and execute SQL file
$sql = file_get_contents(__DIR__ . '/database.sql');

if (!$sql) {
    die("ERROR: Could not read database.sql file\n");
}

// Split by semicolon and execute each statement
$statements = array_filter(array_map('trim', explode(';', $sql)));

foreach ($statements as $statement) {
    if (empty($statement) || strpos($statement, '--') === 0) {
        continue;
    }
    
    try {
        $pdo->exec($statement);
        echo "✓ Executed: " . substr($statement, 0, 50) . "...\n";
    } catch (PDOException $e) {
        echo "⚠ Warning: " . $e->getMessage() . "\n";
    }
}

echo "\n✓ Database setup complete!\n";
echo "\nDefault Admin Credentials:\n";
echo "Username: admin\n";
echo "Password: admin123\n";
echo "\nPlease change these credentials in the admin panel.\n";

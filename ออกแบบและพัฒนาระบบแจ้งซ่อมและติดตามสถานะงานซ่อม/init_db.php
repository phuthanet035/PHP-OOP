<?php

declare(strict_types=1);

/**
 * Database Auto-Installer and Seeder CLI Script
 * Run: php init_db.php
 */

require_once __DIR__ . '/src/Core/Autoloader.php';
use App\Core\Autoloader;

Autoloader::register();
Autoloader::addNamespace('App', __DIR__ . '/src');

// Load .env
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (str_contains($line, '=')) {
            [$name, $value] = explode('=', $line, 2);
            $_ENV[trim($name)] = trim($value, " \t\n\r\0\x0B\"'");
        }
    }
}

echo "========================================================\n";
echo " Smart IT Helpdesk & Notification System\n";
echo " Database Initialization Script\n";
echo "========================================================\n\n";

$sqlFile = __DIR__ . '/database.sql';
if (!file_exists($sqlFile)) {
    die("Error: database.sql not found!\n");
}

try {
    $driver = $_ENV['DB_CONNECTION'] ?? 'mysql';

    if ($driver === 'sqlite') {
        $dbPath = $_ENV['DB_DATABASE'] ?? __DIR__ . '/storage/database.sqlite';
        echo "Initializing SQLite database at: {$dbPath}\n";
        $pdo = new PDO("sqlite:{$dbPath}");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } else {
        $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $port = $_ENV['DB_PORT'] ?? '3306';
        $dbname = $_ENV['DB_DATABASE'] ?? 'smart_it_helpdesk_oop';
        $user = $_ENV['DB_USERNAME'] ?? 'root';
        $pass = $_ENV['DB_PASSWORD'] ?? '';

        echo "Connecting to MySQL server at {$host}:{$port}...\n";
        $pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        echo "Creating database '{$dbname}' if not exists...\n";
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        $pdo->exec("USE `{$dbname}`;");
    }

    echo "Importing schema and seeders from database.sql...\n";
    $sql = file_get_contents($sqlFile);
    
    // Split and execute SQL statements
    $pdo->exec($sql);

    echo "\nSUCCESS! Database initialized and seeded successfully.\n";
    echo "Default Users Available:\n";
    echo "  - 👑 Admin:      admin@helpdesk.com      / password123\n";
    echo "  - 🔧 Technician: tech@helpdesk.com       / password123\n";
    echo "  - 🔧 Network:    surachai@helpdesk.com   / password123\n";
    echo "  - 👤 User:       user@helpdesk.com       / password123\n";
    echo "  - 👤 Staff:      naphaporn@helpdesk.com  / password123\n\n";

} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

<?php

try {
    $envPath = __DIR__ . '/.env';
    if (!file_exists($envPath)) {
        die("No .env file found.\n");
    }

    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && substr($line, 0, 1) !== '#') {
            list($key, $value) = explode('=', $line, 2);
            $env[trim($key)] = trim($value);
        }
    }

    $host = $env['DB_HOST'] ?? '127.0.0.1';
    $user = $env['DB_USERNAME'] ?? 'root';
    $pass = $env['DB_PASSWORD'] ?? '';
    $pass = str_replace('"', '', $pass); // simple quote removal if quoted

    echo "Connecting to $host as $user...\n";

    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `f_commerce_mvp`");
    echo "Database 'f_commerce_mvp' created successfully (or already exists).\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

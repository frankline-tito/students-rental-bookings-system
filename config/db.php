<?php
// ================================================================
// Database connection (PDO)
// Update these if your MySQL setup is different from default XAMPP.
// ================================================================
$DB_HOST = 'localhost';
$DB_NAME = 'rental_system';
$DB_USER = 'root';
$DB_PASS = '';

try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed. Make sure MySQL is running and you've imported database/schema.sql. Details: " . $e->getMessage());
}

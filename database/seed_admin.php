<?php
/**
 * ONE-TIME SETUP SCRIPT
 * Visit this file once in your browser (e.g. http://localhost/rental-system/database/seed_admin.php)
 * to create the first admin account. Delete this file afterwards for security.
 */
require_once __DIR__ . '/../config/db.php';

$defaultEmail    = 'admin@campusnest.com';
$defaultPassword = 'Admin@123';

$check = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$check->execute([$defaultEmail]);

if ($check->fetch()) {
    echo "<h3>An admin account already exists for $defaultEmail.</h3><p>Delete this file now for security.</p>";
    exit;
}

$hash = password_hash($defaultPassword, PASSWORD_DEFAULT);
$pdo->prepare(
    "INSERT INTO users (role, full_name, email, phone, password, status) VALUES ('admin','System Admin', ?, '0700000000', ?, 'approved')"
)->execute([$defaultEmail, $hash]);

echo "<h2>✅ Admin account created!</h2>";
echo "<p><strong>Email:</strong> $defaultEmail<br><strong>Password:</strong> $defaultPassword</p>";
echo "<p style='color:red;font-weight:bold;'>Delete this file (database/seed_admin.php) now, and change the password after logging in.</p>";

<?php
require_once 'db.php';

echo "Checking test accounts...\n\n";

// Check admin
$stmt = $conn->prepare("SELECT username, password, role FROM users WHERE username = 'admin'");
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

if ($admin) {
    echo "Admin account found:\n";
    echo "  Username: {$admin['username']}\n";
    echo "  Role: {$admin['role']}\n";
    echo "  Password hash: {$admin['password']}\n";
    echo "  Password verify 'admin123': " . (password_verify('admin123', $admin['password']) ? "YES" : "NO") . "\n";
    echo "\n";
} else {
    echo "Admin account NOT FOUND!\n\n";
}

// Check testuser
$stmt = $conn->prepare("SELECT username, password, role FROM users WHERE username = 'testuser'");
$stmt->execute();
$result = $stmt->get_result();
$testuser = $result->fetch_assoc();
$stmt->close();

if ($testuser) {
    echo "Testuser account found:\n";
    echo "  Username: {$testuser['username']}\n";
    echo "  Role: {$testuser['role']}\n";
    echo "  Password hash: {$testuser['password']}\n";
    echo "  Password verify 'user123': " . (password_verify('user123', $testuser['password']) ? "YES" : "NO") . "\n";
    echo "\n";
} else {
    echo "Testuser account NOT FOUND!\n\n";
}

// List all users
echo "All users in database:\n";
$result = $conn->query("SELECT username, role FROM users ORDER BY username");
while ($user = $result->fetch_assoc()) {
    echo "  - {$user['username']} ({$user['role']})\n";
}

// Generate correct hashes
echo "\n\nCorrect password hashes:\n";
echo "admin123: " . password_hash('admin123', PASSWORD_DEFAULT) . "\n";
echo "user123: " . password_hash('user123', PASSWORD_DEFAULT) . "\n";

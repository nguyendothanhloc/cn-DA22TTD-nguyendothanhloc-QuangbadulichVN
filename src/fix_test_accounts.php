<?php
require_once 'db.php';

echo "Fixing test accounts...\n\n";

// Clean up old test users
echo "1. Cleaning up test users...\n";
$conn->query("DELETE FROM users WHERE username LIKE 'testuser_%'");
$conn->query("DELETE FROM users WHERE username LIKE 'manual_test_%'");
$conn->query("DELETE FROM users WHERE username LIKE 'sqltest_%'");
echo "   ✅ Test users cleaned up\n\n";

// Update admin password
echo "2. Updating admin password...\n";
$admin_password = password_hash('admin123', PASSWORD_DEFAULT);
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
$stmt->bind_param("s", $admin_password);
$stmt->execute();
$stmt->close();
echo "   ✅ Admin password updated\n";
echo "   Password: admin123\n\n";

// Check if testuser exists, if not create it
echo "3. Checking testuser account...\n";
$stmt = $conn->prepare("SELECT id FROM users WHERE username = 'testuser'");
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows === 0) {
    echo "   Creating testuser account...\n";
    $testuser_password = password_hash('user123', PASSWORD_DEFAULT);
    $stmt = $conn->prepare(
        "INSERT INTO users (username, password, role, fullname, email, phone) VALUES ('testuser', ?, 'user', 'Test User', 'testuser@test.com', '0900000002')"
    );
    $stmt->bind_param("s", $testuser_password);
    $stmt->execute();
    $stmt->close();
    echo "   ✅ Testuser account created\n";
} else {
    echo "   Testuser exists, updating password...\n";
    $testuser_password = password_hash('user123', PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = 'testuser'");
    $stmt->bind_param("s", $testuser_password);
    $stmt->execute();
    $stmt->close();
    echo "   ✅ Testuser password updated\n";
}
echo "   Password: user123\n\n";

// Verify accounts
echo "4. Verifying accounts...\n";

$stmt = $conn->prepare("SELECT username, password, role FROM users WHERE username = 'admin'");
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

echo "   Admin:\n";
echo "     Username: {$admin['username']}\n";
echo "     Role: {$admin['role']}\n";
echo "     Password verify: " . (password_verify('admin123', $admin['password']) ? "✅ YES" : "❌ NO") . "\n\n";

$stmt = $conn->prepare("SELECT username, password, role FROM users WHERE username = 'testuser'");
$stmt->execute();
$result = $stmt->get_result();
$testuser = $result->fetch_assoc();
$stmt->close();

echo "   Testuser:\n";
echo "     Username: {$testuser['username']}\n";
echo "     Role: {$testuser['role']}\n";
echo "     Password verify: " . (password_verify('user123', $testuser['password']) ? "✅ YES" : "❌ NO") . "\n\n";

echo "✅ All test accounts fixed!\n";

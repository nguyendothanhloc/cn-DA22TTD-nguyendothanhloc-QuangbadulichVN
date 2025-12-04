<?php
require_once 'db.php';

echo "<h2>Reset Mật khẩu</h2>";

// Hash mật khẩu mới
$admin_password = password_hash('admin123', PASSWORD_DEFAULT);
$user_password = password_hash('user123', PASSWORD_DEFAULT);

// Update admin password
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
$stmt->bind_param("s", $admin_password);
if ($stmt->execute()) {
    echo "<p style='color: green;'>✅ Đã reset mật khẩu cho admin: <strong>admin123</strong></p>";
} else {
    echo "<p style='color: red;'>❌ Lỗi reset admin: " . $stmt->error . "</p>";
}

// Update user1 password
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = 'user1'");
$stmt->bind_param("s", $user_password);
if ($stmt->execute()) {
    echo "<p style='color: green;'>✅ Đã reset mật khẩu cho user1: <strong>user123</strong></p>";
} else {
    echo "<p style='color: red;'>❌ Lỗi reset user1: " . $stmt->error . "</p>";
}

// Update user2 password
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = 'user2'");
$stmt->bind_param("s", $user_password);
if ($stmt->execute()) {
    echo "<p style='color: green;'>✅ Đã reset mật khẩu cho user2: <strong>user123</strong></p>";
} else {
    echo "<p style='color: red;'>❌ Lỗi reset user2: " . $stmt->error . "</p>";
}

// Update user3 password
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = 'user3'");
$stmt->bind_param("s", $user_password);
if ($stmt->execute()) {
    echo "<p style='color: green;'>✅ Đã reset mật khẩu cho user3: <strong>user123</strong></p>";
} else {
    echo "<p style='color: red;'>❌ Lỗi reset user3: " . $stmt->error . "</p>";
}

$stmt->close();

echo "<hr>";
echo "<h3>Tài khoản sau khi reset:</h3>";
echo "<ul>";
echo "<li><strong>Admin:</strong> username: <code>admin</code> / password: <code>admin123</code></li>";
echo "<li><strong>User 1:</strong> username: <code>user1</code> / password: <code>user123</code></li>";
echo "<li><strong>User 2:</strong> username: <code>user2</code> / password: <code>user123</code></li>";
echo "<li><strong>User 3:</strong> username: <code>user3</code> / password: <code>user123</code></li>";
echo "</ul>";

echo "<hr>";
echo "<p><a href='check_users.php'>← Quay lại kiểm tra</a> | <a href='login.php'>Đăng nhập →</a></p>";
?>

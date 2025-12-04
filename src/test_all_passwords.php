<?php
/**
 * Script test nhiều mật khẩu có thể để tìm mật khẩu đúng
 */

include 'db.php';

echo "<h2>Kiểm tra tất cả mật khẩu có thể cho tài khoản admin</h2>";

// Lấy thông tin admin
$stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = 'admin'");
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    
    echo "<h3>Thông tin tài khoản:</h3>";
    echo "<p>Username: " . $admin['username'] . "</p>";
    echo "<p>Role: " . $admin['role'] . "</p>";
    echo "<p>Password hash: " . substr($admin['password'], 0, 30) . "...</p>";
    echo "<p>Is hashed: " . (substr($admin['password'], 0, 4) === '$2y$' ? 'YES' : 'NO') . "</p>";
    
    echo "<hr>";
    echo "<h3>Test các mật khẩu có thể:</h3>";
    
    // Danh sách mật khẩu có thể
    $possible_passwords = [
        'admin123',
        'admin',
        '123456',
        'password',
        '12345678',
        'admin@123',
        'Admin123',
        '1234',
        'admin1234',
        'root',
        'test123'
    ];
    
    $found = false;
    
    foreach ($possible_passwords as $test_password) {
        if (substr($admin['password'], 0, 4) === '$2y$') {
            // Password đã hash - dùng password_verify
            $is_valid = password_verify($test_password, $admin['password']);
        } else {
            // Password chưa hash - so sánh trực tiếp
            $is_valid = ($test_password === $admin['password']);
        }
        
        if ($is_valid) {
            echo "<p style='background: #d4edda; padding: 10px; border-left: 4px solid #28a745;'>";
            echo "<strong style='color: green;'>✓ TÌM THẤY!</strong> Mật khẩu đúng là: <strong style='font-size: 18px;'>" . htmlspecialchars($test_password) . "</strong>";
            echo "</p>";
            $found = true;
            break;
        } else {
            echo "<p style='color: #999;'>✗ " . htmlspecialchars($test_password) . " - Sai</p>";
        }
    }
    
    if (!$found) {
        echo "<hr>";
        echo "<p style='background: #fff3cd; padding: 10px; border-left: 4px solid #ffc107;'>";
        echo "<strong>⚠ Không tìm thấy mật khẩu trong danh sách.</strong><br>";
        echo "Bạn có nhớ mật khẩu cũ không? Nếu không, tôi sẽ reset về 'admin123'";
        echo "</p>";
        
        echo "<form method='post' style='margin-top: 20px;'>";
        echo "<input type='hidden' name='reset_password' value='1'>";
        echo "<button type='submit' style='background: #667eea; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;'>";
        echo "Reset mật khẩu về 'admin123'";
        echo "</button>";
        echo "</form>";
    }
    
} else {
    echo "<p style='color: red;'>Không tìm thấy tài khoản admin!</p>";
}

$stmt->close();

// Xử lý reset password
if (isset($_POST['reset_password'])) {
    $new_password = 'admin123';
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
    $update_stmt->bind_param("s", $hashed_password);
    
    if ($update_stmt->execute()) {
        echo "<hr>";
        echo "<div style='background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin-top: 20px;'>";
        echo "<h3 style='color: green; margin-top: 0;'>✓ Reset mật khẩu thành công!</h3>";
        echo "<p><strong>Username:</strong> admin</p>";
        echo "<p><strong>Password mới:</strong> admin123</p>";
        echo "<p><a href='login.php' style='color: #667eea; font-weight: bold;'>→ Đi đến trang đăng nhập</a></p>";
        echo "</div>";
    } else {
        echo "<p style='color: red;'>Lỗi reset password: " . $conn->error . "</p>";
    }
    
    $update_stmt->close();
}

$conn->close();
?>

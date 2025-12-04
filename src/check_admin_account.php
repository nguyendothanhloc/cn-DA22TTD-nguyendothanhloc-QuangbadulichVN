<?php
/**
 * Script kiểm tra và sửa tài khoản admin
 */

include 'db.php';

echo "<h2>Kiểm tra tài khoản Admin</h2>";

// Kiểm tra tài khoản admin hiện tại
$stmt = $conn->prepare("SELECT id, username, password, role, fullname, email FROM users WHERE username = 'admin'");
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    
    echo "<h3>Thông tin tài khoản admin hiện tại:</h3>";
    echo "<pre>";
    echo "ID: " . $admin['id'] . "\n";
    echo "Username: " . $admin['username'] . "\n";
    echo "Role: " . $admin['role'] . "\n";
    echo "Fullname: " . $admin['fullname'] . "\n";
    echo "Email: " . $admin['email'] . "\n";
    echo "Password (first 20 chars): " . substr($admin['password'], 0, 20) . "...\n";
    echo "Password is hashed: " . (substr($admin['password'], 0, 4) === '$2y$' ? 'YES' : 'NO') . "\n";
    echo "</pre>";
    
    // Test password
    echo "<h3>Test mật khẩu 'admin123':</h3>";
    $test_password = 'admin123';
    
    if (substr($admin['password'], 0, 4) === '$2y$') {
        $is_valid = password_verify($test_password, $admin['password']);
        echo "<p>Password verify result: " . ($is_valid ? '<strong style="color: green;">ĐÚNG ✓</strong>' : '<strong style="color: red;">SAI ✗</strong>') . "</p>";
        
        if (!$is_valid) {
            echo "<p style='color: orange;'>Mật khẩu hash không khớp với 'admin123'. Đang tạo lại hash mới...</p>";
            
            // Tạo hash mới
            $new_hash = password_hash($test_password, PASSWORD_DEFAULT);
            
            // Cập nhật database
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
            $update_stmt->bind_param("s", $new_hash);
            
            if ($update_stmt->execute()) {
                echo "<p style='color: green;'><strong>✓ Đã cập nhật mật khẩu thành công!</strong></p>";
                echo "<p>Hash mới: " . substr($new_hash, 0, 30) . "...</p>";
                
                // Verify lại
                if (password_verify($test_password, $new_hash)) {
                    echo "<p style='color: green;'><strong>✓ Xác nhận: Mật khẩu 'admin123' hiện đã hoạt động!</strong></p>";
                }
            } else {
                echo "<p style='color: red;'>✗ Lỗi cập nhật: " . $conn->error . "</p>";
            }
            
            $update_stmt->close();
        } else {
            echo "<p style='color: green;'><strong>✓ Mật khẩu 'admin123' đang hoạt động bình thường!</strong></p>";
        }
    } else {
        echo "<p style='color: orange;'>Mật khẩu chưa được hash. Đang hash và cập nhật...</p>";
        
        // Hash và cập nhật
        $new_hash = password_hash($test_password, PASSWORD_DEFAULT);
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
        $update_stmt->bind_param("s", $new_hash);
        
        if ($update_stmt->execute()) {
            echo "<p style='color: green;'><strong>✓ Đã hash và cập nhật mật khẩu thành công!</strong></p>";
        }
        
        $update_stmt->close();
    }
    
} else {
    echo "<p style='color: red;'><strong>✗ Không tìm thấy tài khoản admin!</strong></p>";
    echo "<p>Đang tạo tài khoản admin mới...</p>";
    
    // Tạo tài khoản admin mới
    $username = 'admin';
    $password = 'admin123';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $role = 'admin';
    $fullname = 'Administrator';
    $email = 'admin@test.com';
    $phone = '0900000001';
    
    $insert_stmt = $conn->prepare(
        "INSERT INTO users (username, password, role, fullname, email, phone) VALUES (?, ?, ?, ?, ?, ?)"
    );
    $insert_stmt->bind_param("ssssss", $username, $hashed_password, $role, $fullname, $email, $phone);
    
    if ($insert_stmt->execute()) {
        echo "<p style='color: green;'><strong>✓ Đã tạo tài khoản admin thành công!</strong></p>";
        echo "<p>Username: admin</p>";
        echo "<p>Password: admin123</p>";
    } else {
        echo "<p style='color: red;'>✗ Lỗi tạo tài khoản: " . $conn->error . "</p>";
    }
    
    $insert_stmt->close();
}

$stmt->close();

// Kiểm tra tài khoản testuser
echo "<hr>";
echo "<h3>Kiểm tra tài khoản testuser:</h3>";

$stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = 'testuser'");
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "<p>Username: " . $user['username'] . "</p>";
    echo "<p>Role: " . $user['role'] . "</p>";
    
    $test_password = 'user123';
    if (substr($user['password'], 0, 4) === '$2y$') {
        $is_valid = password_verify($test_password, $user['password']);
        echo "<p>Password 'user123': " . ($is_valid ? '<strong style="color: green;">ĐÚNG ✓</strong>' : '<strong style="color: red;">SAI ✗</strong>') . "</p>";
        
        if (!$is_valid) {
            $new_hash = password_hash($test_password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = 'testuser'");
            $update_stmt->bind_param("s", $new_hash);
            $update_stmt->execute();
            $update_stmt->close();
            echo "<p style='color: green;'>✓ Đã cập nhật mật khẩu testuser</p>";
        }
    }
} else {
    echo "<p style='color: orange;'>Không tìm thấy tài khoản testuser. Đang tạo...</p>";
    
    $username = 'testuser';
    $password = 'user123';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $role = 'user';
    $fullname = 'Test User';
    $email = 'testuser@test.com';
    $phone = '0900000002';
    
    $insert_stmt = $conn->prepare(
        "INSERT INTO users (username, password, role, fullname, email, phone) VALUES (?, ?, ?, ?, ?, ?)"
    );
    $insert_stmt->bind_param("ssssss", $username, $hashed_password, $role, $fullname, $email, $phone);
    
    if ($insert_stmt->execute()) {
        echo "<p style='color: green;'>✓ Đã tạo tài khoản testuser thành công!</p>";
    }
    
    $insert_stmt->close();
}

$stmt->close();
$conn->close();

echo "<hr>";
echo "<h3>Kết luận:</h3>";
echo "<p><strong>Bây giờ bạn có thể đăng nhập với:</strong></p>";
echo "<ul>";
echo "<li>Admin: <strong>admin</strong> / <strong>admin123</strong></li>";
echo "<li>User: <strong>testuser</strong> / <strong>user123</strong></li>";
echo "</ul>";
echo "<p><a href='login.php' style='color: #667eea; font-weight: bold;'>→ Đi đến trang đăng nhập</a></p>";
?>

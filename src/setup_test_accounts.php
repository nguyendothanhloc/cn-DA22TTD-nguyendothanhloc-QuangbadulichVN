<?php
/**
 * Script để thiết lập lại mật khẩu cho tài khoản test
 * Chạy script này sau khi import database để đảm bảo mật khẩu hoạt động đúng
 */

include 'db.php';

echo "<h2>Thiết lập lại mật khẩu cho tài khoản test</h2>";
echo "<hr>";

// Danh sách tài khoản cần cập nhật
$accounts = [
    [
        'username' => 'admin',
        'password' => 'admin123',
        'role' => 'admin',
        'fullname' => 'Admin Test',
        'email' => 'admin@test.com',
        'phone' => '0900000001'
    ],
    [
        'username' => 'testuser',
        'password' => 'user123',
        'role' => 'user',
        'fullname' => 'Test User',
        'email' => 'testuser@test.com',
        'phone' => '0900000002'
    ],
    [
        'username' => 'user1',
        'password' => 'user123',
        'role' => 'user',
        'fullname' => 'Nguyễn Văn A',
        'email' => 'nguyenvana@email.com',
        'phone' => '0912345678'
    ],
    [
        'username' => 'user2',
        'password' => 'user123',
        'role' => 'user',
        'fullname' => 'Trần Thị B',
        'email' => 'tranthib@email.com',
        'phone' => '0923456789'
    ]
];

$success_count = 0;
$error_count = 0;

foreach ($accounts as $account) {
    $username = $account['username'];
    $password = $account['password'];
    $role = $account['role'];
    $fullname = $account['fullname'];
    $email = $account['email'];
    $phone = $account['phone'];
    
    // Hash mật khẩu bằng password_hash
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Kiểm tra xem user đã tồn tại chưa
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        // User đã tồn tại - cập nhật mật khẩu
        $update_stmt = $conn->prepare("UPDATE users SET password = ?, role = ?, fullname = ?, email = ?, phone = ? WHERE username = ?");
        $update_stmt->bind_param("ssssss", $hashed_password, $role, $fullname, $email, $phone, $username);
        
        if ($update_stmt->execute()) {
            echo "✅ Đã cập nhật tài khoản: <strong>$username</strong> (Password: $password)<br>";
            $success_count++;
        } else {
            echo "❌ Lỗi cập nhật tài khoản: <strong>$username</strong><br>";
            $error_count++;
        }
        $update_stmt->close();
    } else {
        // User chưa tồn tại - tạo mới
        $insert_stmt = $conn->prepare("INSERT INTO users (username, password, role, fullname, email, phone) VALUES (?, ?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("ssssss", $username, $hashed_password, $role, $fullname, $email, $phone);
        
        if ($insert_stmt->execute()) {
            echo "✅ Đã tạo tài khoản mới: <strong>$username</strong> (Password: $password)<br>";
            $success_count++;
        } else {
            echo "❌ Lỗi tạo tài khoản: <strong>$username</strong><br>";
            $error_count++;
        }
        $insert_stmt->close();
    }
    
    $check_stmt->close();
}

echo "<hr>";
echo "<h3>Kết quả:</h3>";
echo "<p>✅ Thành công: $success_count tài khoản</p>";
echo "<p>❌ Lỗi: $error_count tài khoản</p>";

echo "<hr>";
echo "<h3>Thông tin đăng nhập:</h3>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr style='background-color: #f0f0f0;'>";
echo "<th>Username</th><th>Password</th><th>Role</th>";
echo "</tr>";
foreach ($accounts as $account) {
    $bg = $account['role'] === 'admin' ? '#ffe6e6' : '#e6f3ff';
    echo "<tr style='background-color: $bg;'>";
    echo "<td><strong>{$account['username']}</strong></td>";
    echo "<td><strong>{$account['password']}</strong></td>";
    echo "<td>{$account['role']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";
echo "<p><a href='login.php' style='padding: 10px 20px; background-color: #667eea; color: white; text-decoration: none; border-radius: 5px;'>Đi đến trang đăng nhập</a></p>";

$conn->close();
?>

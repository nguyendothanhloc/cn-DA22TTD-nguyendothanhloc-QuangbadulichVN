<?php
require_once 'db.php';

echo "<h2>Kiểm tra tài khoản trong Database</h2>";

// Lấy tất cả users
$sql = "SELECT id, username, password, role, fullname FROM users";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr style='background: #4CAF50; color: white;'>";
    echo "<th>ID</th><th>Username</th><th>Role</th><th>Fullname</th><th>Password Hash</th><th>Test Password</th>";
    echo "</tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td><strong>" . $row['username'] . "</strong></td>";
        echo "<td>" . $row['role'] . "</td>";
        echo "<td>" . $row['fullname'] . "</td>";
        echo "<td style='font-size: 10px; max-width: 200px; word-break: break-all;'>" . substr($row['password'], 0, 50) . "...</td>";
        
        // Test password
        $test_password = ($row['role'] === 'admin') ? 'admin123' : 'user123';
        $is_valid = password_verify($test_password, $row['password']);
        
        if ($is_valid) {
            echo "<td style='background: #d4edda; color: #155724;'>✅ Password: <strong>$test_password</strong></td>";
        } else {
            echo "<td style='background: #f8d7da; color: #721c24;'>❌ Password không đúng</td>";
        }
        
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p style='color: red;'>Không có user nào trong database!</p>";
}

echo "<hr>";
echo "<h3>Hướng dẫn:</h3>";
echo "<ul>";
echo "<li>Nếu thấy ✅ → Tài khoản đúng, có thể đăng nhập</li>";
echo "<li>Nếu thấy ❌ → Mật khẩu trong database không khớp, cần reset</li>";
echo "</ul>";

echo "<hr>";
echo "<h3>Reset mật khẩu:</h3>";
echo "<p>Nếu cần reset mật khẩu, <a href='reset_password.php'>click vào đây</a></p>";
?>

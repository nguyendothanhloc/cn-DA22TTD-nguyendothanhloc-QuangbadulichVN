<?php
/**
 * Script kiểm tra và xác minh tài khoản có thể đăng nhập được không
 */

include 'db.php';

echo "<h2>Kiểm tra tài khoản đăng nhập</h2>";
echo "<hr>";

// Danh sách tài khoản cần test
$test_accounts = [
    ['username' => 'admin', 'password' => 'admin123'],
    ['username' => 'testuser', 'password' => 'user123'],
    ['username' => 'user1', 'password' => 'user123'],
    ['username' => 'user2', 'password' => 'user123']
];

echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background-color: #f0f0f0;'>";
echo "<th>Username</th><th>Password Test</th><th>Trạng thái</th><th>Chi tiết</th>";
echo "</tr>";

foreach ($test_accounts as $account) {
    $username = $account['username'];
    $password = $account['password'];
    
    // Lấy thông tin user từ database
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Kiểm tra password
        $password_valid = password_verify($password, $user['password']);
        
        if ($password_valid) {
            $status = "✅ OK";
            $bg_color = "#d4edda";
            $detail = "Đăng nhập thành công! Role: " . $user['role'];
        } else {
            $status = "❌ FAILED";
            $bg_color = "#f8d7da";
            $detail = "Mật khẩu không đúng. Cần chạy setup_test_accounts.php";
        }
    } else {
        $status = "❌ NOT FOUND";
        $bg_color = "#fff3cd";
        $detail = "Tài khoản không tồn tại. Cần import database.sql";
    }
    
    echo "<tr style='background-color: $bg_color;'>";
    echo "<td><strong>$username</strong></td>";
    echo "<td>$password</td>";
    echo "<td><strong>$status</strong></td>";
    echo "<td>$detail</td>";
    echo "</tr>";
    
    $stmt->close();
}

echo "</table>";

echo "<hr>";
echo "<h3>Hướng dẫn khắc phục:</h3>";
echo "<ul>";
echo "<li>Nếu có tài khoản <strong>FAILED</strong>: Chạy <a href='setup_test_accounts.php' style='color: #667eea; font-weight: bold;'>setup_test_accounts.php</a></li>";
echo "<li>Nếu có tài khoản <strong>NOT FOUND</strong>: Import lại file database.sql</li>";
echo "<li>Nếu tất cả <strong>OK</strong>: Bạn có thể <a href='login.php' style='color: #28a745; font-weight: bold;'>đăng nhập</a> ngay!</li>";
echo "</ul>";

$conn->close();
?>

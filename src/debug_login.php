<?php
/**
 * Script debug để kiểm tra vấn đề đăng nhập
 */

include 'db.php';

echo "<h2>🔍 Debug thông tin đăng nhập</h2>";
echo "<hr>";

// Kiểm tra kết nối database
echo "<h3>1. Kiểm tra kết nối Database</h3>";
if ($conn->ping()) {
    echo "✅ Kết nối database thành công!<br>";
    echo "Database: <strong>travel_booking</strong><br>";
} else {
    echo "❌ Không thể kết nối database!<br>";
    echo "Lỗi: " . $conn->error . "<br>";
    exit;
}

echo "<hr>";

// Kiểm tra bảng users
echo "<h3>2. Kiểm tra bảng Users</h3>";
$check_table = $conn->query("SHOW TABLES LIKE 'users'");
if ($check_table->num_rows > 0) {
    echo "✅ Bảng 'users' tồn tại<br>";
} else {
    echo "❌ Bảng 'users' không tồn tại! Cần import database.sql<br>";
    exit;
}

echo "<hr>";

// Kiểm tra số lượng users
echo "<h3>3. Kiểm tra số lượng Users</h3>";
$count_result = $conn->query("SELECT COUNT(*) as total FROM users");
$count = $count_result->fetch_assoc()['total'];
echo "Tổng số users: <strong>$count</strong><br>";

if ($count == 0) {
    echo "❌ Không có user nào! Cần import database.sql<br>";
    exit;
}

echo "<hr>";

// Kiểm tra chi tiết từng tài khoản
echo "<h3>4. Chi tiết tài khoản</h3>";
$users_result = $conn->query("SELECT username, password, role FROM users ORDER BY role DESC, username");

echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background-color: #f0f0f0;'>";
echo "<th>Username</th><th>Password Hash</th><th>Role</th><th>Trạng thái</th></tr>";

while ($user = $users_result->fetch_assoc()) {
    $username = $user['username'];
    $password_hash = $user['password'];
    $role = $user['role'];
    
    // Kiểm tra password
    $status = "";
    $bg_color = "";
    
    if ($password_hash === 'temp_password') {
        $status = "⚠️ Chưa setup - Cần chạy setup_test_accounts.php";
        $bg_color = "#fff3cd";
    } else if (strlen($password_hash) < 20) {
        $status = "❌ Password không hợp lệ";
        $bg_color = "#f8d7da";
    } else {
        // Test password với các mật khẩu phổ biến
        $test_passwords = [
            'admin123' => 'admin',
            'user123' => 'testuser,user1,user2'
        ];
        
        $password_ok = false;
        foreach ($test_passwords as $test_pass => $applicable_users) {
            if (strpos($applicable_users, $username) !== false) {
                if (password_verify($test_pass, $password_hash)) {
                    $status = "✅ OK - Password: $test_pass";
                    $bg_color = "#d4edda";
                    $password_ok = true;
                    break;
                }
            }
        }
        
        if (!$password_ok) {
            $status = "❌ Password không khớp - Cần chạy lại setup_test_accounts.php";
            $bg_color = "#f8d7da";
        }
    }
    
    echo "<tr style='background-color: $bg_color;'>";
    echo "<td><strong>$username</strong></td>";
    echo "<td style='font-family: monospace; font-size: 0.8em;'>" . substr($password_hash, 0, 30) . "...</td>";
    echo "<td>$role</td>";
    echo "<td>$status</td>";
    echo "</tr>";
}

echo "</table>";

echo "<hr>";

// Đưa ra khuyến nghị
echo "<h3>5. Khuyến nghị</h3>";

$needs_setup = false;
$check_result = $conn->query("SELECT password FROM users LIMIT 1");
if ($check_result->num_rows > 0) {
    $first_user = $check_result->fetch_assoc();
    if ($first_user['password'] === 'temp_password' || strlen($first_user['password']) < 20) {
        $needs_setup = true;
    }
}

if ($needs_setup) {
    echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; border-left: 5px solid #ffc107;'>";
    echo "<strong>⚠️ CẦN THIẾT LẬP MẬT KHẨU!</strong><br><br>";
    echo "Bạn cần chạy script để thiết lập mật khẩu đúng:<br>";
    echo "<a href='setup_test_accounts.php' style='display: inline-block; margin-top: 10px; padding: 15px 30px; background: #ffc107; color: #333; text-decoration: none; border-radius: 5px; font-weight: bold;'>🔧 Chạy Setup Mật khẩu</a>";
    echo "</div>";
} else {
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; border-left: 5px solid #28a745;'>";
    echo "<strong>✅ HỆ THỐNG ĐÃ SẴN SÀNG!</strong><br><br>";
    echo "Tất cả tài khoản đã được thiết lập đúng. Bạn có thể đăng nhập ngay:<br>";
    echo "<a href='login.php' style='display: inline-block; margin-top: 10px; padding: 15px 30px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>🔐 Đăng nhập</a>";
    echo "</div>";
}

echo "<hr>";

// Test đăng nhập thực tế
echo "<h3>6. Test đăng nhập Admin</h3>";
echo "<form method='post' style='background: #f8f9fa; padding: 20px; border-radius: 8px;'>";
echo "<p>Thử đăng nhập với tài khoản admin:</p>";
echo "<input type='text' name='test_username' value='admin' readonly style='padding: 10px; margin: 5px; width: 200px;'><br>";
echo "<input type='password' name='test_password' placeholder='Nhập mật khẩu' style='padding: 10px; margin: 5px; width: 200px;'><br>";
echo "<button type='submit' name='test_login' style='padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; margin: 5px;'>Test Login</button>";
echo "</form>";

if (isset($_POST['test_login'])) {
    $test_username = $_POST['test_username'];
    $test_password = $_POST['test_password'];
    
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $test_username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if (password_verify($test_password, $user['password'])) {
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin-top: 10px;'>";
            echo "✅ <strong>ĐĂNG NHẬP THÀNH CÔNG!</strong><br>";
            echo "Username: {$user['username']}<br>";
            echo "Role: {$user['role']}<br>";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin-top: 10px;'>";
            echo "❌ <strong>SAI MẬT KHẨU!</strong><br>";
            echo "Mật khẩu bạn nhập không đúng.<br>";
            echo "Nếu bạn dùng 'admin123' mà vẫn sai, hãy chạy lại setup_test_accounts.php<br>";
            echo "</div>";
        }
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin-top: 10px;'>";
        echo "❌ <strong>TÀI KHOẢN KHÔNG TỒN TẠI!</strong><br>";
        echo "</div>";
    }
    
    $stmt->close();
}

$conn->close();
?>

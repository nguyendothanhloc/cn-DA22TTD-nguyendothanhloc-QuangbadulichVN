<?php
/**
 * Script kiểm tra xem hệ thống đã được thiết lập chưa
 * Tự động redirect đến trang hướng dẫn nếu chưa setup
 */

include 'db.php';

// Kiểm tra xem database đã có dữ liệu chưa
$check_query = "SELECT COUNT(*) as count FROM users";
$result = $conn->query($check_query);

if ($result) {
    $row = $result->fetch_assoc();
    $user_count = $row['count'];
    
    if ($user_count == 0) {
        // Chưa có user nào - chưa import database
        header('Location: setup_guide.html');
        exit;
    }
    
    // Kiểm tra xem mật khẩu đã được setup đúng chưa
    $test_query = "SELECT password FROM users WHERE username = 'admin' LIMIT 1";
    $test_result = $conn->query($test_query);
    
    if ($test_result && $test_result->num_rows > 0) {
        $admin = $test_result->fetch_assoc();
        
        // Kiểm tra xem password có phải là temp_password không
        if ($admin['password'] === 'temp_password') {
            // Chưa chạy setup_test_accounts.php
            header('Location: setup_guide.html');
            exit;
        }
        
        // Kiểm tra xem password hash có hoạt động không
        if (!password_verify('admin123', $admin['password'])) {
            // Password hash không đúng - cần chạy lại setup
            header('Location: setup_guide.html');
            exit;
        }
    }
} else {
    // Lỗi query - có thể database chưa tồn tại
    header('Location: setup_guide.html');
    exit;
}

$conn->close();

// Nếu đã setup đầy đủ, return true
return true;
?>

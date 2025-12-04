<?php
/**
 * Trang test đăng nhập đơn giản - không có session, CSRF
 */

include 'db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    echo "<h3>Debug Info:</h3>";
    echo "<p>Username nhập vào: '" . htmlspecialchars($username) . "' (length: " . strlen($username) . ")</p>";
    echo "<p>Password nhập vào: '" . htmlspecialchars($password) . "' (length: " . strlen($password) . ")</p>";
    
    if (empty($username) || empty($password)) {
        $message = "Vui lòng điền đầy đủ thông tin";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password, role, fullname FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            echo "<p>Tìm thấy user trong database</p>";
            echo "<p>Password hash trong DB: " . substr($user['password'], 0, 30) . "...</p>";
            
            // Verify password
            if (substr($user['password'], 0, 4) === '$2y$') {
                $password_valid = password_verify($password, $user['password']);
                echo "<p>Dùng password_verify: " . ($password_valid ? '<strong style="color: green;">ĐÚNG</strong>' : '<strong style="color: red;">SAI</strong>') . "</p>";
            } else {
                $password_valid = ($password === $user['password']);
                echo "<p>So sánh trực tiếp: " . ($password_valid ? '<strong style="color: green;">ĐÚNG</strong>' : '<strong style="color: red;">SAI</strong>') . "</p>";
            }
            
            if ($password_valid) {
                $message = "<div style='background: #d4edda; padding: 15px; border-left: 4px solid #28a745;'>";
                $message .= "<h3 style='color: green; margin-top: 0;'>✓ ĐĂNG NHẬP THÀNH CÔNG!</h3>";
                $message .= "<p><strong>Username:</strong> " . htmlspecialchars($user['username']) . "</p>";
                $message .= "<p><strong>Role:</strong> " . htmlspecialchars($user['role']) . "</p>";
                $message .= "<p><strong>Fullname:</strong> " . htmlspecialchars($user['fullname']) . "</p>";
                $message .= "<p style='margin-top: 15px;'><a href='login.php' style='color: #667eea; font-weight: bold;'>→ Thử đăng nhập qua trang login.php chính thức</a></p>";
                $message .= "</div>";
            } else {
                $message = "<div style='background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545;'>";
                $message .= "<strong style='color: #721c24;'>✗ Mật khẩu sai!</strong>";
                $message .= "<p>Hãy kiểm tra lại mật khẩu bạn đang gõ.</p>";
                $message .= "</div>";
            }
        } else {
            $message = "<div style='background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545;'>";
            $message .= "<strong style='color: #721c24;'>✗ Không tìm thấy username này!</strong>";
            $message .= "</div>";
            echo "<p>Không tìm thấy user '" . htmlspecialchars($username) . "' trong database</p>";
        }
        
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Đăng Nhập</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #2c3e50;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
        }
        button {
            background: #667eea;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        button:hover {
            background: #5568d3;
        }
        .hint {
            background: #e7f3ff;
            padding: 15px;
            border-left: 4px solid #2196F3;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🔍 Test Đăng Nhập (Debug Mode)</h2>
        
        <?php if ($message): ?>
            <?= $message ?>
            <hr>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="admin" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" value="admin123" required>
            </div>
            
            <button type="submit">Test Đăng Nhập</button>
        </form>
        
        <div class="hint">
            <strong>💡 Gợi ý:</strong>
            <ul style="margin: 10px 0;">
                <li>Username mặc định: <code>admin</code></li>
                <li>Password mặc định: <code>admin123</code></li>
                <li>Trang này sẽ hiển thị chi tiết debug để tìm lỗi</li>
            </ul>
        </div>
    </div>
</body>
</html>

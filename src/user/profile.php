<?php
session_start();
require_once '../auth.php';
require_once '../db.php';

// Require user role
requireRole('user');

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    // Validate required fields
    if (empty($fullname) || empty($email) || empty($phone)) {
        $errors[] = "Vui lòng điền đầy đủ thông tin";
    }
    
    // Validate email
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không hợp lệ";
    }
    
    // Validate phone
    if (!empty($phone) && !preg_match('/^[0-9]{10,11}$/', $phone)) {
        $errors[] = "Số điện thoại không hợp lệ (10-11 số)";
    }
    
    // If changing password
    if (!empty($new_password)) {
        if (empty($current_password)) {
            $errors[] = "Vui lòng nhập mật khẩu hiện tại";
        } else {
            // Verify current password
            if (!password_verify($current_password, $user['password'])) {
                $errors[] = "Mật khẩu hiện tại không đúng";
            }
        }
        
        if (strlen($new_password) < 6) {
            $errors[] = "Mật khẩu mới phải có ít nhất 6 ký tự";
        }
        
        if ($new_password !== $confirm_password) {
            $errors[] = "Mật khẩu mới không khớp";
        }
    }
    
    if (empty($errors)) {
        // Update user info
        if (!empty($new_password)) {
            // Update with new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET fullname = ?, email = ?, phone = ?, password = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $fullname, $email, $phone, $hashed_password, $user_id);
        } else {
            // Update without password change
            $stmt = $conn->prepare("UPDATE users SET fullname = ?, email = ?, phone = ? WHERE id = ?");
            $stmt->bind_param("sssi", $fullname, $email, $phone, $user_id);
        }
        
        if ($stmt->execute()) {
            $success = "Cập nhật thông tin thành công!";
            $_SESSION['fullname'] = $fullname;
            // Refresh user data
            $user['fullname'] = $fullname;
            $user['email'] = $email;
            $user['phone'] = $phone;
        } else {
            $errors[] = "Có lỗi xảy ra khi cập nhật!";
        }
        $stmt->close();
    }
    
    if (!empty($errors)) {
        $error = implode('<br>', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin cá nhân</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/animations.css">
    <style>
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }
        
        h2 {
            color: #1a4d2e;
            border-bottom: 3px solid #4caf50;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .nav-menu {
            margin-bottom: 20px;
            padding: 15px 20px;
            background: linear-gradient(135deg, #1a4d2e 0%, #0d3320 100%);
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .nav-menu span {
            color: white;
            font-weight: 600;
        }
        
        .nav-menu div {
            display: flex;
            gap: 15px;
        }
        
        .nav-menu a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .nav-menu a:hover {
            background-color: #4caf50;
        }
        
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .form-section {
            margin-bottom: 30px;
            padding-bottom: 30px;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .form-section:last-child {
            border-bottom: none;
        }
        
        .form-section h3 {
            color: #1a4d2e;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #4caf50;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%);
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #66bb6a 0%, #388e3c 100%);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #f44336;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #4caf50;
        }
        
        .info-box {
            background: #e8f5e9;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #4caf50;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav-menu">
            <span>Xin chào, <?= htmlspecialchars($_SESSION['fullname'] ?? $_SESSION['username'], ENT_QUOTES, 'UTF-8') ?></span>
            <div>
                <a href="home.php">Trang chủ</a>
                <a href="my_bookings.php">Lịch sử đặt vé</a>
                <a href="profile.php">Thông tin cá nhân</a>
                <a href="../logout.php">Đăng xuất</a>
            </div>
        </div>
        
        <h2>Thông tin cá nhân</h2>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <div class="form-container">
            <form method="post">
                <div class="form-section">
                    <h3>Thông tin tài khoản</h3>
                    <div class="info-box">
                        <strong>Tên đăng nhập:</strong> <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="fullname">Họ và tên: <span style="color: red;">*</span></label>
                        <input type="text" id="fullname" name="fullname" 
                               value="<?= htmlspecialchars($user['fullname'], ENT_QUOTES, 'UTF-8') ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email: <span style="color: red;">*</span></label>
                        <input type="email" id="email" name="email" 
                               value="<?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Số điện thoại: <span style="color: red;">*</span></label>
                        <input type="tel" id="phone" name="phone" 
                               value="<?= htmlspecialchars($user['phone'], ENT_QUOTES, 'UTF-8') ?>" 
                               required>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>Đổi mật khẩu (Tùy chọn)</h3>
                    <p style="color: #666; margin-bottom: 15px;">Chỉ điền nếu bạn muốn đổi mật khẩu</p>
                    
                    <div class="form-group">
                        <label for="current_password">Mật khẩu hiện tại:</label>
                        <input type="password" id="current_password" name="current_password" 
                               placeholder="Nhập mật khẩu hiện tại">
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">Mật khẩu mới:</label>
                        <input type="password" id="new_password" name="new_password" 
                               placeholder="Nhập mật khẩu mới (tối thiểu 6 ký tự)">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Xác nhận mật khẩu mới:</label>
                        <input type="password" id="confirm_password" name="confirm_password" 
                               placeholder="Nhập lại mật khẩu mới">
                    </div>
                </div>
                
                <div style="margin-top: 30px;">
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                    <a href="home.php" class="btn btn-secondary">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

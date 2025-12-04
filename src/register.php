<?php
// Start session with secure settings
session_start([
    'cookie_httponly' => true,
    'use_strict_mode' => true
]);

include 'db.php';

// Validate existing session before redirecting
if (isset($_SESSION['user_id'])) {
    // Verify that the user still exists in database
    $stmt = $conn->prepare("SELECT id, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // User exists, redirect to appropriate page
        $user = $result->fetch_assoc();
        if ($user['role'] === 'admin') {
            header('Location: admin/dashboard.php');
        } else {
            header('Location: user/home.php');
        }
        exit;
    } else {
        // User doesn't exist anymore, clear invalid session
        session_unset();
        session_destroy();
        session_start([
            'cookie_httponly' => true,
            'use_strict_mode' => true
        ]);
    }
    $stmt->close();
}

// Initialize error and success messages
$err = '';
$success = '';

// Display any success message from session
if (isset($_SESSION['success_message'])) {
    $success = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Preserve form data on error
$form_data = [
    'username' => '',
    'fullname' => '',
    'email' => '',
    'phone' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $err = "Invalid request. Please try again.";
    } else {
        // Get and sanitize input
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $fullname = trim($_POST['fullname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        
        // Preserve form data
        $form_data = [
            'username' => htmlspecialchars($username, ENT_QUOTES, 'UTF-8'),
            'fullname' => htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8'),
            'email' => htmlspecialchars($email, ENT_QUOTES, 'UTF-8'),
            'phone' => htmlspecialchars($phone, ENT_QUOTES, 'UTF-8')
        ];
        
        // Server-side validation
        $errors = [];
        
        // Validate required fields
        if (empty($username) || empty($password) || empty($confirm_password) || 
            empty($fullname) || empty($email) || empty($phone)) {
            $errors[] = "Vui lòng điền đầy đủ thông tin";
        }
        
        // Validate username format (3-50 chars, alphanumeric and underscore)
        if (!empty($username) && !preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
            $errors[] = "Tên đăng nhập phải từ 3-50 ký tự và chỉ chứa chữ, số, gạch dưới";
        }
        
        // Validate password length
        if (!empty($password) && strlen($password) < 6) {
            $errors[] = "Mật khẩu phải có ít nhất 6 ký tự";
        }
        
        // Validate password match
        if (!empty($password) && !empty($confirm_password) && $password !== $confirm_password) {
            $errors[] = "Mật khẩu không khớp";
        }
        
        // Validate fullname length
        if (!empty($fullname) && (strlen($fullname) < 2 || strlen($fullname) > 100)) {
            $errors[] = "Họ tên phải từ 2-100 ký tự";
        }
        
        // Validate email format
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email không hợp lệ";
        }
        
        // Validate phone format (10-11 digits)
        if (!empty($phone) && !preg_match('/^[0-9]{10,11}$/', $phone)) {
            $errors[] = "Số điện thoại không hợp lệ (10-11 số)";
        }
        
        // Check username uniqueness
        if (empty($errors) && !empty($username)) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $errors[] = "Tên đăng nhập đã tồn tại";
            }
            $stmt->close();
        }
        
        // If no errors, create user account
        if (empty($errors)) {
            // Hash password using bcrypt
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user with prepared statement
            $stmt = $conn->prepare("INSERT INTO users (username, password, role, fullname, email, phone) VALUES (?, ?, 'user', ?, ?, ?)");
            $stmt->bind_param("sssss", $username, $hashed_password, $fullname, $email, $phone);
            
            if ($stmt->execute()) {
                // Registration successful
                $stmt->close();
                
                // Regenerate session ID
                session_regenerate_id(true);
                
                // Set success message in session
                $_SESSION['success_message'] = "Đăng ký thành công! Vui lòng đăng nhập.";
                
                // Redirect to login page
                header('Location: login.php');
                exit;
            } else {
                $errors[] = "Đã xảy ra lỗi, vui lòng thử lại";
                $stmt->close();
            }
        }
        
        // Display errors
        if (!empty($errors)) {
            $err = implode('<br>', $errors);
        }
    }
    
    // Regenerate CSRF token after failed registration
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - Hệ thống Du lịch</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/animations.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a4d2e 0%, #4caf50 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            max-width: 500px;
            width: 100%;
            background: white;
            padding: 50px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideIn 0.5s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-container h2 {
            text-align: center;
            color: #1a4d2e;
            margin-bottom: 35px;
            font-size: 2em;
            font-weight: 700;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 0.95em;
        }
        
        .form-group input {
            width: 100%;
            padding: 14px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1em;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #4caf50;
            box-shadow: 0 0 0 4px rgba(76, 175, 80, 0.1);
        }
        
        .btn-primary {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1em;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.4);
            margin-top: 10px;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #66bb6a 0%, #388e3c 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.5);
        }
        
        .btn-primary:active {
            transform: translateY(0);
            box-shadow: 0 2px 10px rgba(76, 175, 80, 0.3);
        }
        
        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            border-left: 4px solid #f44336;
            animation: shake 0.5s;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        
        .success-message {
            background-color: #e8f5e9;
            color: #2e7d32;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            border-left: 4px solid #4caf50;
        }
        
        .auth-link {
            text-align: center;
            margin-top: 25px;
            color: #666;
            font-size: 0.95em;
        }
        
        .auth-link a {
            color: #4caf50;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s ease;
        }
        
        .auth-link a:hover {
            color: #2e7d32;
            text-decoration: underline;
        }
    </style>
    <script>
        // Client-side validation
        function validateForm() {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const fullname = document.getElementById('fullname').value.trim();
            const email = document.getElementById('email').value.trim();
            const phone = document.getElementById('phone').value.trim();
            
            let errors = [];
            
            // Check required fields
            if (!username || !password || !confirmPassword || !fullname || !email || !phone) {
                errors.push('Vui lòng điền đầy đủ thông tin');
            }
            
            // Validate username format
            if (username && !/^[a-zA-Z0-9_]{3,50}$/.test(username)) {
                errors.push('Tên đăng nhập phải từ 3-50 ký tự và chỉ chứa chữ, số, gạch dưới');
            }
            
            // Validate password length
            if (password && password.length < 6) {
                errors.push('Mật khẩu phải có ít nhất 6 ký tự');
            }
            
            // Validate password match
            if (password && confirmPassword && password !== confirmPassword) {
                errors.push('Mật khẩu không khớp');
            }
            
            // Validate fullname length
            if (fullname && (fullname.length < 2 || fullname.length > 100)) {
                errors.push('Họ tên phải từ 2-100 ký tự');
            }
            
            // Validate email format
            if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                errors.push('Email không hợp lệ');
            }
            
            // Validate phone format
            if (phone && !/^[0-9]{10,11}$/.test(phone)) {
                errors.push('Số điện thoại không hợp lệ (10-11 số)');
            }
            
            if (errors.length > 0) {
                alert(errors.join('\n'));
                return false;
            }
            
            return true;
        }
    </script>
</head>
<body>
    <div class="login-container">
        <h2>Đăng ký tài khoản</h2>
        
        <?php if (!empty($success)): ?>
            <div class="success-message">
                <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($err)): ?>
            <div class="error-message">
                <?= $err ?>
            </div>
        <?php endif; ?>
        
        <form method="post" action="register.php" onsubmit="return validateForm()">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
            
            <div class="form-group">
                <label for="username">Tên đăng nhập: <span style="color: red;">*</span></label>
                <input type="text" id="username" name="username" 
                       placeholder="Nhập tên đăng nhập (3-50 ký tự)" 
                       value="<?= $form_data['username'] ?>" 
                       required>
            </div>
            
            <div class="form-group">
                <label for="password">Mật khẩu: <span style="color: red;">*</span></label>
                <input type="password" id="password" name="password" 
                       placeholder="Nhập mật khẩu (tối thiểu 6 ký tự)" 
                       required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Xác nhận mật khẩu: <span style="color: red;">*</span></label>
                <input type="password" id="confirm_password" name="confirm_password" 
                       placeholder="Nhập lại mật khẩu" 
                       required>
            </div>
            
            <div class="form-group">
                <label for="fullname">Họ và tên: <span style="color: red;">*</span></label>
                <input type="text" id="fullname" name="fullname" 
                       placeholder="Nhập họ và tên đầy đủ" 
                       value="<?= $form_data['fullname'] ?>" 
                       required>
            </div>
            
            <div class="form-group">
                <label for="email">Email: <span style="color: red;">*</span></label>
                <input type="email" id="email" name="email" 
                       placeholder="Nhập địa chỉ email" 
                       value="<?= $form_data['email'] ?>" 
                       required>
            </div>
            
            <div class="form-group">
                <label for="phone">Số điện thoại: <span style="color: red;">*</span></label>
                <input type="tel" id="phone" name="phone" 
                       placeholder="Nhập số điện thoại (10-11 số)" 
                       value="<?= $form_data['phone'] ?>" 
                       required>
            </div>
            
            <button type="submit" class="btn-primary btn-block">Đăng ký</button>
        </form>
        
        <div class="auth-link">
            Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a>
        </div>
    </div>
</body>
</html>

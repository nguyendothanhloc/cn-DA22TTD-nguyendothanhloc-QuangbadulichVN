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

// Initialize error message
$err = '';

// Display any login message from session
if (isset($_SESSION['login_message'])) {
    $err = $_SESSION['login_message'];
    unset($_SESSION['login_message']);
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $err = "Invalid request. Please try again.";
    } else {
        // Get and sanitize input
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Validate input
        if (empty($username) || empty($password)) {
            $err = "Vui lòng điền đầy đủ thông tin";
        } else {
            // Use prepared statement to prevent SQL injection
            $stmt = $conn->prepare("SELECT id, username, password, role, fullname FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                // Verify password
                // Note: For existing users with plain text passwords, we need to handle both cases
                // In production, all passwords should be hashed
                $password_valid = false;
                
                // Check if password is hashed (starts with $2y$ for bcrypt)
                if (substr($user['password'], 0, 4) === '$2y$') {
                    // Use password_verify for hashed passwords
                    $password_valid = password_verify($password, $user['password']);
                } else {
                    // Temporary: support plain text passwords for backward compatibility
                    // This should be removed after all passwords are migrated to hashed format
                    $password_valid = ($password === $user['password']);
                    
                    // If login successful with plain password, update to hashed password
                    if ($password_valid) {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $update_stmt->bind_param("si", $hashed_password, $user['id']);
                        $update_stmt->execute();
                        $update_stmt->close();
                    }
                }
                
                if ($password_valid) {
                    // Regenerate session ID to prevent session fixation
                    session_regenerate_id(true);
                    
                    // Store user information in session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['fullname'] = $user['fullname'];
                    
                    // Generate new CSRF token for the authenticated session
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    
                    // Redirect based on role
                    if ($user['role'] === 'admin') {
                        header('Location: admin/dashboard.php');
                    } else {
                        header('Location: user/home.php');
                    }
                    exit;
                } else {
                    $err = "Sai tài khoản hoặc mật khẩu";
                }
            } else {
                $err = "Sai tài khoản hoặc mật khẩu";
            }
            
            $stmt->close();
        }
    }
    
    // Regenerate CSRF token after failed login
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Hệ thống Du lịch</title>
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
            max-width: 450px;
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
            margin-bottom: 25px;
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
            padding: 15px;
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
</head>
<body>
    <div class="login-container">
        <h2>Đăng nhập</h2>
        
        <?php if (!empty($err)): ?>
            <?php if (strpos($err, 'thành công') !== false): ?>
                <div class="success-message">
                    <?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php else: ?>
                <div class="error-message">
                    <?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <form method="post" action="login.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
            
            <div class="form-group">
                <label for="username">Tài khoản:</label>
                <input type="text" id="username" name="username" placeholder="Nhập tài khoản" required>
            </div>
            
            <div class="form-group">
                <label for="password">Mật khẩu:</label>
                <input type="password" id="password" name="password" placeholder="Nhập mật khẩu" required>
            </div>
            
            <button type="submit" class="btn-primary">Đăng nhập</button>
        </form>
        
        <div class="auth-link">
            Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
        </div>
    </div>
</body>
</html>
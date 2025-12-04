<?php
session_start();
require_once '../auth.php';
require_once '../db.php';

// Require admin role
requireRole('admin');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $note = trim($_POST['note'] ?? '');
    
    if (empty($name)) {
        $error = "Tên địa điểm không được để trống!";
    } else {
        // Handle image upload
        $image_name = null;
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $file_type = $_FILES['image']['type'];
            
            if (in_array($file_type, $allowed_types)) {
                $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $image_name = 'place_' . uniqid() . '.' . $file_extension;
                $upload_path = '../uploads/places/' . $image_name;
                
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    $error = "Không thể tải lên hình ảnh!";
                }
            } else {
                $error = "Chỉ chấp nhận file ảnh (JPG, PNG, GIF)!";
            }
        }
        
        if (empty($error)) {
            // Use prepared statement to prevent SQL injection
            $stmt = $conn->prepare("INSERT INTO places(name, note, image) VALUES(?, ?, ?)");
            $stmt->bind_param("sss", $name, $note, $image_name);
            
            if ($stmt->execute()) {
                $success = "Thêm địa điểm thành công!";
                header('Location: places.php');
                exit;
            } else {
                $error = "Có lỗi xảy ra khi thêm địa điểm!";
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm địa điểm - Admin</title>
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
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input[type="text"],
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4caf50;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="nav-menu">
            <span>Admin: <?= htmlspecialchars($_SESSION['fullname'] ?? $_SESSION['username'], ENT_QUOTES, 'UTF-8') ?></span>
            <div>
                <a href="places.php">Địa điểm</a>
                <a href="tours.php">Tour</a>
                <a href="bookings.php">Đơn đặt vé</a>
                <a href="../logout.php">Đăng xuất</a>
            </div>
        </div>
        
        <h2>Thêm địa điểm mới</h2>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        
        <div class="form-container">
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Tên địa điểm: <span style="color: red;">*</span></label>
                    <input type="text" id="name" name="name" placeholder="Nhập tên địa điểm" required>
                </div>
                
                <div class="form-group">
                    <label for="note">Ghi chú:</label>
                    <textarea id="note" name="note" placeholder="Nhập ghi chú về địa điểm"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="image">Hình ảnh:</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <p style="margin-top: 5px; color: #666; font-size: 0.9em;">
                        <em>Chấp nhận: JPG, PNG, GIF</em>
                    </p>
                </div>
                
                <div style="margin-top: 30px;">
                    <button type="submit" class="btn btn-primary">Thêm địa điểm</button>
                    <a href="places.php" class="btn btn-secondary">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
<?php
session_start();
require_once '../auth.php';
require_once '../db.php';

// Require admin role
requireRole('admin');

// Get place ID
$id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);

if ($id <= 0) {
    header('Location: places.php');
    exit;
}

// Get place data
$stmt = $conn->prepare("SELECT * FROM places WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$place = $result->fetch_assoc();
$stmt->close();

if (!$place) {
    header('Location: places.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $note = trim($_POST['note'] ?? '');
    
    if (empty($name)) {
        $error = "Tên địa điểm không được để trống!";
    } else {
        // Handle image upload
        $image_name = $place['image']; // Keep old image by default
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $file_type = $_FILES['image']['type'];
            
            if (in_array($file_type, $allowed_types)) {
                $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $image_name = 'place_' . uniqid() . '.' . $file_extension;
                $upload_path = '../uploads/places/' . $image_name;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    // Delete old image if exists
                    if (!empty($place['image']) && file_exists('../uploads/places/' . $place['image'])) {
                        unlink('../uploads/places/' . $place['image']);
                    }
                } else {
                    $error = "Không thể tải lên hình ảnh!";
                }
            } else {
                $error = "Chỉ chấp nhận file ảnh (JPG, PNG, GIF)!";
            }
        }
        
        if (empty($error)) {
            // Update place
            $stmt = $conn->prepare("UPDATE places SET name = ?, note = ?, image = ? WHERE id = ?");
            $stmt->bind_param("sssi", $name, $note, $image_name, $id);
            
            if ($stmt->execute()) {
                $success = "Cập nhật địa điểm thành công!";
                // Refresh place data
                $place['name'] = $name;
                $place['note'] = $note;
                $place['image'] = $image_name;
            } else {
                $error = "Có lỗi xảy ra khi cập nhật!";
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
    <title>Sửa địa điểm - Admin</title>
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
        
        .current-image {
            margin-top: 10px;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 6px;
        }
        
        .current-image img {
            max-width: 200px;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
        
        <h2>Sửa địa điểm</h2>
        
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
                    <input type="text" id="name" name="name" 
                           value="<?= htmlspecialchars($place['name'], ENT_QUOTES, 'UTF-8') ?>" 
                           placeholder="Nhập tên địa điểm" required>
                </div>
                
                <div class="form-group">
                    <label for="note">Ghi chú:</label>
                    <textarea id="note" name="note" 
                              placeholder="Nhập ghi chú về địa điểm"><?= htmlspecialchars($place['note'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="image">Hình ảnh:</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    
                    <?php if (!empty($place['image']) && file_exists('../uploads/places/' . $place['image'])): ?>
                        <div class="current-image">
                            <p><strong>Hình ảnh hiện tại:</strong></p>
                            <img src="../uploads/places/<?= htmlspecialchars($place['image'], ENT_QUOTES, 'UTF-8') ?>" 
                                 alt="<?= htmlspecialchars($place['name'], ENT_QUOTES, 'UTF-8') ?>">
                            <p style="margin-top: 10px; color: #666; font-size: 0.9em;">
                                <em>Chọn file mới để thay đổi hình ảnh</em>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div style="margin-top: 30px;">
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                    <a href="places.php" class="btn btn-secondary">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

<?php
session_start();
require_once '../auth.php';
require_once '../db.php';
require_once '../helpers.php';

// Require admin role
requireRole('admin');

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize input
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $available_seats = trim($_POST['available_seats'] ?? '');
    $departure_date = trim($_POST['departure_date'] ?? '');
    $place_id = trim($_POST['place_id'] ?? '');
    
    // Prepare data for validation
    $tour_data = [
        'name' => $name,
        'description' => $description,
        'price' => $price,
        'available_seats' => $available_seats,
        'departure_date' => $departure_date,
        'place_id' => $place_id
    ];
    
    // Use validation function from helpers.php
    $errors = validateTourData($tour_data);
    
    // Handle image upload
    $image_name = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        $file_type = $_FILES['image']['type'];
        $file_size = $_FILES['image']['size'];
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_name = $_FILES['image']['name'];
        
        // Validate file type
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Chỉ chấp nhận file ảnh định dạng: JPG, PNG, GIF, WEBP";
        }
        
        // Validate file size
        if ($file_size > $max_size) {
            $errors[] = "Kích thước file không được vượt quá 5MB";
        }
        
        if (empty($errors)) {
            // Generate unique filename
            $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
            $image_name = uniqid('tour_') . '.' . $file_extension;
            $upload_path = '../uploads/tours/' . $image_name;
            
            // Create directory if not exists
            if (!file_exists('../uploads/tours/')) {
                mkdir('../uploads/tours/', 0755, true);
            }
            
            // Move uploaded file
            if (!move_uploaded_file($file_tmp, $upload_path)) {
                $errors[] = "Có lỗi khi upload ảnh";
                $image_name = null;
            }
        }
    }
    
    // If no errors, insert into database
    if (empty($errors)) {
        // Use prepared statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO tours (name, description, price, available_seats, departure_date, place_id, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdisis", $name, $description, $price, $available_seats, $departure_date, $place_id, $image_name);
        
        if ($stmt->execute()) {
            $success = true;
            // Redirect to tours page after 2 seconds
            header("refresh:2;url=tours.php");
        } else {
            $errors[] = "Có lỗi xảy ra khi thêm tour: " . $stmt->error;
            
            // Delete uploaded image if database insert fails
            if ($image_name && file_exists('../uploads/tours/' . $image_name)) {
                unlink('../uploads/tours/' . $image_name);
            }
        }
        
        $stmt->close();
    }
}

// Get all places for dropdown
$places = $conn->query("SELECT id, name FROM places ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Tour - Admin</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/animations.css">
    <style>
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #1a4d2e;
        }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus {
            outline: none;
            border-color: #4caf50;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
        }
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
            font-family: inherit;
        }
        .form-group input[type="file"] {
            padding: 10px;
            border: 2px dashed #4caf50;
            background: #f0f9f4;
        }
        .form-group small {
            color: #666;
            font-size: 0.9em;
        }
        .btn-primary {
            background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-right: 10px;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(76, 175, 80, 0.3);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #66bb6a 0%, #388e3c 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(76, 175, 80, 0.4);
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
            border-left: 4px solid #dc3545;
        }
        .success-message {
            background-color: #e8f5e9;
            color: #1b5e20;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
            border-left: 4px solid #4caf50;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h2 {
            color: #1a4d2e;
            border-bottom: 3px solid #4caf50;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Thêm Tour Mới</h2>
        <div class="nav-menu">
            <span>Admin: <?= htmlspecialchars($_SESSION['fullname'] ?? $_SESSION['username'], ENT_QUOTES, 'UTF-8') ?></span>
            <div>
                <a href="dashboard.php">Địa điểm</a>
                <a href="tours.php">Tour</a>
                <a href="bookings.php">Đơn đặt vé</a>
                <a href="../logout.php">Đăng xuất</a>
            </div>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success-message">
                Thêm tour thành công! Đang chuyển hướng...
            </div>
        <?php endif; ?>
        
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Tên Tour: <span style="color: red;">*</span></label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">Mô tả:</label>
                <textarea id="description" name="description" rows="4"><?= htmlspecialchars($_POST['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="image">Hình ảnh Tour:</label>
                <input type="file" id="image" name="image" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                <small style="color: #666; display: block; margin-top: 5px;">
                    Định dạng: JPG, PNG, GIF, WEBP. Kích thước tối đa: 5MB
                </small>
            </div>
            
            <div class="form-group">
                <label for="price">Giá (VNĐ): <span style="color: red;">*</span></label>
                <input type="number" id="price" name="price" step="0.01" min="0" value="<?= htmlspecialchars($_POST['price'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="available_seats">Số chỗ: <span style="color: red;">*</span></label>
                <input type="number" id="available_seats" name="available_seats" min="0" value="<?= htmlspecialchars($_POST['available_seats'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="departure_date">Ngày khởi hành: <span style="color: red;">*</span></label>
                <input type="date" id="departure_date" name="departure_date" value="<?= htmlspecialchars($_POST['departure_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="place_id">Địa điểm: <span style="color: red;">*</span></label>
                <select id="place_id" name="place_id" required>
                    <option value="">-- Chọn địa điểm --</option>
                    <?php while($place = $places->fetch_assoc()): ?>
                        <option value="<?= $place['id'] ?>" <?= (isset($_POST['place_id']) && $_POST['place_id'] == $place['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($place['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <button type="submit" class="btn-primary">Thêm Tour</button>
            <a href="tours.php" class="btn-secondary">Hủy</a>
        </form>
    </div>
</body>
</html>

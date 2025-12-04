<?php
session_start();
require_once '../auth.php';
require_once '../db.php';
require_once '../helpers.php';

// Require user role
requireRole('user');

// Get search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 0;

// Build query with search and filter
$sql = "SELECT t.*, p.name as place_name 
        FROM tours t 
        LEFT JOIN places p ON t.place_id = p.id 
        WHERE 1=1";

$params = [];
$types = '';

// Add search condition
if (!empty($search)) {
    $sql .= " AND (t.name LIKE ? OR t.description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

// Add price filter
if ($min_price > 0) {
    $sql .= " AND t.price >= ?";
    $params[] = $min_price;
    $types .= 'd';
}

if ($max_price > 0) {
    $sql .= " AND t.price <= ?";
    $params[] = $max_price;
    $types .= 'd';
}

// Sort by departure date (nearest first)
$sql .= " ORDER BY t.departure_date ASC";

// Execute query
if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $tours = $stmt->get_result();
} else {
    $tours = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách Tour Du lịch</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/animations.css">
    <style>
        .container {
            max-width: 1400px;
            width: 100%;
            margin: 0 auto;
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
            box-shadow: 0 4px 6px rgba(26, 77, 46, 0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .nav-menu span {
            color: white;
            font-weight: 600;
            font-size: 1.1em;
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
            font-weight: 500;
        }
        
        .nav-menu a:hover {
            background-color: #4caf50;
            transform: translateY(-2px);
        }
        .search-filter-form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border: 1px solid #e8f5e9;
        }
        .form-row {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }
        .form-group {
            flex: 1;
            min-width: 200px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #1a4d2e;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #4caf50;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-weight: 600;
            height: 44px;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%);
            color: white;
            box-shadow: 0 2px 4px rgba(76, 175, 80, 0.3);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #66bb6a 0%, #388e3c 100%);
            box-shadow: 0 4px 8px rgba(76, 175, 80, 0.4);
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        .btn-success {
            background: linear-gradient(135deg, #66bb6a 0%, #4caf50 100%);
            color: white;
            box-shadow: 0 2px 4px rgba(76, 175, 80, 0.3);
        }
        .btn-success:hover {
            background: linear-gradient(135deg, #81c784 0%, #66bb6a 100%);
            box-shadow: 0 4px 8px rgba(76, 175, 80, 0.4);
            transform: translateY(-2px);
        }
        .btn:hover {
            opacity: 0.9;
        }
        .tours-list {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-top: 20px;
        }
        
        @media (max-width: 1200px) {
            .tours-list {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .tours-list {
                grid-template-columns: 1fr;
            }
        }
        .tour-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .tour-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(76, 175, 80, 0.2);
        }
        
        .tour-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: #e8f5e9;
        }
        
        .tour-card-content {
            padding: 15px;
        }
        .tour-card h3 {
            margin-top: 0;
            color: #1a4d2e;
            font-size: 1.3em;
        }
        .tour-info {
            margin: 10px 0;
        }
        .tour-info p {
            margin: 5px 0;
            color: #666;
        }
        .tour-price {
            font-size: 1.3em;
            font-weight: bold;
            color: #2e7d32;
            margin: 10px 0;
        }
        .tour-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-weight: bold;
            margin: 10px 0;
        }
        .status-available {
            background: #c8e6c9;
            color: #1b5e20;
        }
        .status-soldout {
            background: #f8d7da;
            color: #721c24;
        }
        .tour-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
            align-items: stretch;
        }
        .tour-actions a {
            flex: 1;
            min-width: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav-menu">
            <span>Xin chào, <a href="profile.php" style="color: white; text-decoration: underline;"><?= htmlspecialchars($_SESSION['fullname'] ?? $_SESSION['username'], ENT_QUOTES, 'UTF-8') ?></a></span>
            <div>
                <a href="home.php">Trang chủ</a>
                <a href="my_bookings.php">Lịch sử đặt vé</a>
                <a href="profile.php">Thông tin cá nhân</a>
                <a href="../logout.php">Đăng xuất</a>
            </div>
        </div>
        
        <h2>Danh sách Tour Du lịch</h2>
        
        <!-- Search and Filter Form -->
        <div class="search-filter-form">
            <form method="GET" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label>Tìm kiếm:</label>
                        <input type="text" name="search" placeholder="Nhập tên hoặc mô tả tour..." value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="form-group">
                        <label>Giá từ (VNĐ):</label>
                        <input type="number" name="min_price" placeholder="0" value="<?= $min_price > 0 ? $min_price : '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Giá đến (VNĐ):</label>
                        <input type="number" name="max_price" placeholder="0" value="<?= $max_price > 0 ? $max_price : '' ?>">
                    </div>
                </div>
                <div class="form-row">
                    <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                    <a href="home.php" class="btn btn-secondary">Xóa bộ lọc</a>
                </div>
            </form>
        </div>
        
        <!-- Tours List -->
        <div class="tours-list">
            <?php if ($tours->num_rows > 0): ?>
                <?php while($tour = $tours->fetch_assoc()): ?>
                    <div class="tour-card">
                        <?php 
                        $image_path = !empty($tour['image']) && file_exists('../uploads/tours/' . $tour['image']) 
                            ? '../uploads/tours/' . $tour['image'] 
                            : '../uploads/tours/placeholder.svg';
                        ?>
                        <img src="<?= htmlspecialchars($image_path, ENT_QUOTES, 'UTF-8') ?>" 
                             alt="<?= htmlspecialchars($tour['name'], ENT_QUOTES, 'UTF-8') ?>" 
                             class="tour-image">
                        
                        <div class="tour-card-content">
                            <h3><?= htmlspecialchars($tour['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                            
                            <div class="tour-info">
                            <p><strong>Mô tả:</strong> <?= htmlspecialchars(substr($tour['description'], 0, 100), ENT_QUOTES, 'UTF-8') ?>...</p>
                            <p><strong>Địa điểm:</strong> <?= htmlspecialchars($tour['place_name'] ?? 'Chưa xác định', ENT_QUOTES, 'UTF-8') ?></p>
                            <p><strong>Ngày khởi hành:</strong> <?= formatDate($tour['departure_date']) ?></p>
                            <p><strong>Số chỗ còn lại:</strong> <?= $tour['available_seats'] ?></p>
                        </div>
                        
                        <div class="tour-price">
                            <?= formatPrice($tour['price']) ?>
                        </div>
                        
                        <?php 
                        $tour_status = $tour['status'] ?? 'active';
                        if ($tour_status === 'inactive'): 
                        ?>
                            <div class="tour-status status-soldout">Tạm ngưng</div>
                        <?php elseif ($tour['available_seats'] == 0): ?>
                            <div class="tour-status status-soldout">Hết chỗ</div>
                        <?php else: ?>
                            <div class="tour-status status-available">Còn chỗ</div>
                        <?php endif; ?>
                        
                            <div class="tour-actions">
                                <a href="tour_detail.php?id=<?= $tour['id'] ?>" class="btn btn-primary">Xem chi tiết</a>
                                <?php if ($tour_status === 'active' && $tour['available_seats'] > 0): ?>
                                    <a href="tour_detail.php?id=<?= $tour['id'] ?>#book" class="btn btn-success">Đặt tour</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Không tìm thấy tour nào phù hợp.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
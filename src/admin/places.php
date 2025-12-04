<?php
session_start();
require_once '../auth.php';
require_once '../db.php';

// Require admin role
requireRole('admin');

// Get all places
$places = $conn->query("SELECT * FROM places ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Địa điểm - Admin</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/animations.css">
    <style>
        .container {
            max-width: 1400px;
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
            box-shadow: 0 4px 6px rgba(26, 77, 46, 0.3);
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
        
        .action-bar {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%);
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #66bb6a 0%, #388e3c 100%);
        }
        
        .btn-warning {
            background: #ff9800;
            color: white;
        }
        
        .btn-warning:hover {
            background: #f57c00;
        }
        
        .btn-danger {
            background: #f44336;
            color: white;
        }
        
        .btn-danger:hover {
            background: #d32f2f;
        }
        
        .places-table {
            width: 100%;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .places-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .places-table th {
            background: #1a4d2e;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        .places-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .places-table tr:hover {
            background: #f5f5f5;
        }
        
        .place-image {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
        
        .no-image {
            width: 80px;
            height: 60px;
            background: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 5px;
            color: #999;
            font-size: 0.8em;
        }
        
        .actions {
            display: flex;
            gap: 10px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .empty-state svg {
            width: 100px;
            height: 100px;
            margin-bottom: 20px;
            opacity: 0.3;
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
        
        <h2>Quản lý Địa điểm</h2>
        
        <div class="action-bar">
            <div>
                <span style="color: #666;">Tổng số địa điểm: <strong><?= $places->num_rows ?></strong></span>
            </div>
            <a href="add_place.php" class="btn btn-primary">+ Thêm địa điểm mới</a>
        </div>
        
        <div class="places-table">
            <?php if ($places->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Hình ảnh</th>
                            <th>Tên địa điểm</th>
                            <th>Ghi chú</th>
                            <th>Ngày tạo</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($place = $places->fetch_assoc()): ?>
                            <tr>
                                <td><?= $place['id'] ?></td>
                                <td>
                                    <?php if (!empty($place['image']) && file_exists('../uploads/places/' . $place['image'])): ?>
                                        <img src="../uploads/places/<?= htmlspecialchars($place['image'], ENT_QUOTES, 'UTF-8') ?>" 
                                             alt="<?= htmlspecialchars($place['name'], ENT_QUOTES, 'UTF-8') ?>" 
                                             class="place-image">
                                    <?php else: ?>
                                        <div class="no-image">Chưa có ảnh</div>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= htmlspecialchars($place['name'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                <td><?= htmlspecialchars($place['note'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= date('d/m/Y', strtotime($place['created_at'])) ?></td>
                                <td>
                                    <div class="actions">
                                        <a href="edit_place.php?id=<?= $place['id'] ?>" class="btn btn-warning">Sửa</a>
                                        <a href="delete_place.php?id=<?= $place['id'] ?>" 
                                           class="btn btn-danger" 
                                           onclick="return confirm('Bạn có chắc muốn xóa địa điểm này?')">Xóa</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <h3>Chưa có địa điểm nào</h3>
                    <p>Hãy thêm địa điểm đầu tiên của bạn!</p>
                    <a href="add_place.php" class="btn btn-primary">+ Thêm địa điểm</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

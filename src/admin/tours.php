<?php
session_start();
require_once '../auth.php';
require_once '../db.php';
require_once '../helpers.php';

// Require admin role
requireRole('admin');

// Get all tours with place information using JOIN
$sql = "SELECT t.*, p.name as place_name 
        FROM tours t 
        LEFT JOIN places p ON t.place_id = p.id 
        ORDER BY t.departure_date ASC";
$tours = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Tour - Admin</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/animations.css">
    <style>
        .tours-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .tours-table th, .tours-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .tours-table th {
            background-color: #4CAF50;
            color: white;
        }
        .tours-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .tours-table tr:hover {
            background-color: #ddd;
        }
        .btn-action {
            padding: 5px 10px;
            margin: 0 2px;
            text-decoration: none;
            border-radius: 3px;
            display: inline-block;
        }
        .btn-edit {
            background-color: #2196F3;
            color: white;
        }
        .btn-delete {
            background-color: #f44336;
            color: white;
        }
        .btn-add {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-bottom: 20px;
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
            display: inline-block;
        }
        .status-active {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        .status-inactive {
            background-color: #ffebee;
            color: #c62828;
        }
        .btn-toggle {
            background-color: #ff9800;
            color: white;
            font-size: 0.85em;
        }
        .btn-toggle:hover {
            background-color: #f57c00;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Quản lý Tour Du lịch</h2>
        <div class="nav-menu">
            <span>Admin: <?= htmlspecialchars($_SESSION['fullname'] ?? $_SESSION['username'], ENT_QUOTES, 'UTF-8') ?></span>
            <div>
                <a href="dashboard.php">Địa điểm</a>
                <a href="tours.php">Tour</a>
                <a href="bookings.php">Đơn đặt vé</a>
                <a href="../logout.php">Đăng xuất</a>
            </div>
        </div>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div style="background-color: #4CAF50; color: white; padding: 10px; margin-bottom: 15px; border-radius: 4px;">
                <?= htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div style="background-color: #f44336; color: white; padding: 10px; margin-bottom: 15px; border-radius: 4px;">
                <?= htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        
        <a href="add_tour.php" class="btn-add">+ Thêm Tour Mới</a>
        
        <table class="tours-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên Tour</th>
                    <th>Địa điểm</th>
                    <th>Giá (VNĐ)</th>
                    <th>Số chỗ còn lại</th>
                    <th>Ngày khởi hành</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($tours && $tours->num_rows > 0): ?>
                    <?php while($tour = $tours->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($tour['id'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><strong><?= htmlspecialchars($tour['name'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                            <td><?= htmlspecialchars($tour['place_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= formatPrice($tour['price']) ?></td>
                            <td><?= htmlspecialchars($tour['available_seats'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= formatDate($tour['departure_date']) ?></td>
                            <td>
                                <?php 
                                $status = $tour['status'] ?? 'active';
                                $status_class = $status === 'active' ? 'status-active' : 'status-inactive';
                                $status_text = $status === 'active' ? 'Hoạt động' : 'Tạm ngưng';
                                ?>
                                <span class="status-badge <?= $status_class ?>"><?= $status_text ?></span>
                            </td>
                            <td>
                                <a href="toggle_tour_status.php?id=<?= $tour['id'] ?>" class="btn-action btn-toggle" title="Bật/Tắt trạng thái">
                                    <?= ($status === 'active') ? 'Tắt' : 'Bật' ?>
                                </a>
                                <a href="edit_tour.php?id=<?= $tour['id'] ?>" class="btn-action btn-edit">Sửa</a>
                                <a href="delete_tour.php?id=<?= $tour['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Bạn có chắc muốn xóa tour này?')">Xóa</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align: center;">Chưa có tour nào</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

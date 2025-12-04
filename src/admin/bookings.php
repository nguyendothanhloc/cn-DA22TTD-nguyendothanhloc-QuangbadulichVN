<?php
session_start();
require_once '../auth.php';
require_once '../db.php';
require_once '../helpers.php';

// Require admin role
requireRole('admin');

// Get filter status from URL
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

// Build SQL query with optional status filter
$sql = "SELECT b.*, 
               u.fullname as user_name, u.username,
               t.name as tour_name, t.departure_date
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN tours t ON b.tour_id = t.id";

// Add WHERE clause if status filter is set
if (!empty($filter_status) && in_array($filter_status, ['pending', 'confirmed', 'cancelled'])) {
    $sql .= " WHERE b.status = ?";
}

$sql .= " ORDER BY b.booking_date DESC";

// Prepare and execute query
if (!empty($filter_status) && in_array($filter_status, ['pending', 'confirmed', 'cancelled'])) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $filter_status);
    $stmt->execute();
    $bookings = $stmt->get_result();
} else {
    $bookings = $conn->query($sql);
}

// Status translations
$status_text = [
    'pending' => 'Chờ xác nhận',
    'confirmed' => 'Đã xác nhận',
    'cancelled' => 'Đã hủy'
];

$status_class = [
    'pending' => 'status-pending',
    'confirmed' => 'status-confirmed',
    'cancelled' => 'status-cancelled'
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Đơn đặt vé - Admin</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/animations.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 1400px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h2 {
            color: #1a4d2e;
            border-bottom: 3px solid #4caf50;
            padding-bottom: 10px;
            margin-bottom: 20px;
            margin-top: 0;
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
        .filter-form {
            margin: 20px 0;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
            border-left: 4px solid #2196F3;
        }
        .filter-form label {
            margin-right: 10px;
            font-weight: bold;
        }
        .filter-form select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-right: 10px;
        }
        .filter-form button {
            padding: 8px 20px;
            background-color: #2196F3;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .filter-form button:hover {
            background-color: #0b7dda;
        }
        .filter-form a {
            margin-left: 10px;
            color: #666;
            text-decoration: none;
        }
        .bookings-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .bookings-table th, .bookings-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .bookings-table th {
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
        }
        .bookings-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .bookings-table tr:hover {
            background-color: #e8f5e9;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 0.9em;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-confirmed {
            background: #d4edda;
            color: #155724;
        }
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        .btn-action {
            padding: 6px 12px;
            margin: 0 2px;
            text-decoration: none;
            border-radius: 3px;
            display: inline-block;
            font-size: 0.9em;
            border: none;
            cursor: pointer;
        }
        .btn-confirm {
            background-color: #4CAF50;
            color: white;
        }
        .btn-confirm:hover {
            background-color: #45a049;
        }
        .btn-cancel {
            background-color: #f44336;
            color: white;
        }
        .btn-cancel:hover {
            background-color: #da190b;
        }
        .btn-disabled {
            background-color: #ccc;
            color: #666;
            cursor: not-allowed;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }
        @media (max-width: 1200px) {
            .bookings-table {
                font-size: 0.85em;
            }
            .bookings-table th,
            .bookings-table td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Quản lý Đơn đặt vé</h2>
        <div class="nav-menu">
            <span>Admin: <?= htmlspecialchars($_SESSION['fullname'] ?? $_SESSION['username'], ENT_QUOTES, 'UTF-8') ?></span>
            <div>
                <a href="dashboard.php">Địa điểm</a>
                <a href="tours.php">Tour</a>
                <a href="bookings.php">Đơn đặt vé</a>
                <a href="../logout.php">Đăng xuất</a>
            </div>
        </div>
        
        <?php if (isset($_SESSION['booking_success'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['booking_success'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php unset($_SESSION['booking_success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['booking_error'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($_SESSION['booking_error'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php unset($_SESSION['booking_error']); ?>
        <?php endif; ?>
        
        <!-- Filter Form -->
        <div class="filter-form">
            <form method="GET" action="bookings.php">
                <label for="status">Lọc theo trạng thái:</label>
                <select name="status" id="status">
                    <option value="">Tất cả</option>
                    <option value="pending" <?= $filter_status === 'pending' ? 'selected' : '' ?>>Chờ xác nhận</option>
                    <option value="confirmed" <?= $filter_status === 'confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
                    <option value="cancelled" <?= $filter_status === 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                </select>
                <button type="submit">Lọc</button>
                <a href="bookings.php">Xóa bộ lọc</a>
            </form>
        </div>
        
        <?php if ($bookings && $bookings->num_rows > 0): ?>
            <table class="bookings-table">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Người đặt</th>
                        <th>Tour</th>
                        <th>Số lượng</th>
                        <th>Ngày đặt</th>
                        <th>Ngày khởi hành</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($booking = $bookings->fetch_assoc()): ?>
                        <tr>
                            <td><strong>#<?= $booking['id'] ?></strong></td>
                            <td>
                                <?= htmlspecialchars($booking['user_name'] ?? $booking['username'], ENT_QUOTES, 'UTF-8') ?>
                                <br>
                                <small style="color: #666;">(<?= htmlspecialchars($booking['username'], ENT_QUOTES, 'UTF-8') ?>)</small>
                            </td>
                            <td><?= htmlspecialchars($booking['tour_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= $booking['num_people'] ?> người</td>
                            <td><?= formatDate($booking['booking_date']) ?></td>
                            <td><?= formatDate($booking['departure_date']) ?></td>
                            <td><strong><?= formatPrice($booking['total_price']) ?></strong></td>
                            <td>
                                <span class="status-badge <?= $status_class[$booking['status']] ?>">
                                    <?= $status_text[$booking['status']] ?? $booking['status'] ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($booking['status'] === 'pending'): ?>
                                    <a href="update_booking.php?id=<?= $booking['id'] ?>&action=confirm" 
                                       class="btn-action btn-confirm"
                                       onclick="return confirm('Xác nhận đơn đặt vé #<?= $booking['id'] ?>?')">
                                        Xác nhận
                                    </a>
                                    <a href="update_booking.php?id=<?= $booking['id'] ?>&action=cancel" 
                                       class="btn-action btn-cancel"
                                       onclick="return confirm('Hủy đơn đặt vé #<?= $booking['id'] ?>?')">
                                        Hủy
                                    </a>
                                <?php elseif ($booking['status'] === 'confirmed'): ?>
                                    <a href="update_booking.php?id=<?= $booking['id'] ?>&action=cancel" 
                                       class="btn-action btn-cancel"
                                       onclick="return confirm('Hủy đơn đặt vé #<?= $booking['id'] ?>?')">
                                        Hủy
                                    </a>
                                <?php else: ?>
                                    <span class="btn-action btn-disabled">Không thể thao tác</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <h3>Không có đơn đặt vé nào</h3>
                <p>
                    <?php if (!empty($filter_status)): ?>
                        Không tìm thấy đơn đặt vé với trạng thái "<?= $status_text[$filter_status] ?>".
                    <?php else: ?>
                        Chưa có đơn đặt vé nào trong hệ thống.
                    <?php endif; ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

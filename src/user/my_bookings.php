<?php
session_start();
require_once '../auth.php';
require_once '../db.php';
require_once '../helpers.php';

// Require user role
requireRole('user');

// Get user_id from session
$user_id = $_SESSION['user_id'];

// Get all bookings for current user with tour information
$sql = "SELECT b.*, t.name as tour_name, t.departure_date 
        FROM bookings b 
        JOIN tours t ON b.tour_id = t.id 
        WHERE b.user_id = ? 
        ORDER BY b.booking_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookings = $stmt->get_result();

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
    <title>Lịch sử đặt vé</title>
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
        .bookings-table {
            width: 100%;
            background: white;
            border-collapse: collapse;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 5px;
            overflow: hidden;
        }
        .bookings-table th {
            background: linear-gradient(135deg, #1a4d2e 0%, #0d3320 100%);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: bold;
        }
        .bookings-table td {
            padding: 15px;
            border-bottom: 1px solid #ddd;
        }
        .bookings-table tr:last-child td {
            border-bottom: none;
        }
        .bookings-table tr:hover {
            background: #e8f5e9;
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
            background: #c8e6c9;
            color: #1b5e20;
        }
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 0.9em;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .btn-disabled {
            background: #6c757d;
            color: white;
            cursor: not-allowed;
            opacity: 0.6;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .empty-state-icon {
            font-size: 4em;
            color: #ccc;
            margin-bottom: 20px;
        }
        .empty-state h3 {
            color: #666;
            margin-bottom: 10px;
        }
        .empty-state p {
            color: #999;
            margin-bottom: 20px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%);
            color: white;
            box-shadow: 0 2px 4px rgba(76, 175, 80, 0.3);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #66bb6a 0%, #388e3c 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(76, 175, 80, 0.4);
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .alert-success {
            background: #e8f5e9;
            color: #1b5e20;
            border: 1px solid #c8e6c9;
            border-left: 4px solid #4caf50;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        @media (max-width: 768px) {
            .bookings-table {
                font-size: 0.9em;
            }
            .bookings-table th,
            .bookings-table td {
                padding: 10px 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Lịch sử đặt vé</h2>
        
        <div class="nav-menu">
            <span>Xin chào, <?= htmlspecialchars($_SESSION['fullname'] ?? $_SESSION['username'], ENT_QUOTES, 'UTF-8') ?></span>
            <div>
                <a href="home.php">Trang chủ</a>
                <a href="my_bookings.php">Lịch sử đặt vé</a>
                <a href="../logout.php">Đăng xuất</a>
            </div>
        </div>
        
        <?php if (isset($_SESSION['cancel_success'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['cancel_success'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php unset($_SESSION['cancel_success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['cancel_error'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($_SESSION['cancel_error'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php unset($_SESSION['cancel_error']); ?>
        <?php endif; ?>
        
        <?php if ($bookings->num_rows > 0): ?>
            <table class="bookings-table">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Tour</th>
                        <th>Số lượng người</th>
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
                            <td>#<?= $booking['id'] ?></td>
                            <td><?= htmlspecialchars($booking['tour_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= $booking['num_people'] ?> người</td>
                            <td><?= formatDate($booking['booking_date']) ?></td>
                            <td><?= formatDate($booking['departure_date']) ?></td>
                            <td><?= formatPrice($booking['total_price']) ?></td>
                            <td>
                                <span class="status-badge <?= $status_class[$booking['status']] ?>">
                                    <?= $status_text[$booking['status']] ?? $booking['status'] ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($booking['status'] === 'pending'): ?>
                                    <a href="cancel_booking.php?id=<?= $booking['id'] ?>" 
                                       class="btn btn-danger"
                                       onclick="return confirm('Bạn có chắc chắn muốn hủy đơn đặt vé này?')">
                                        Hủy
                                    </a>
                                <?php else: ?>
                                    <span class="btn btn-disabled">Không thể hủy</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">📋</div>
                <h3>Chưa có đơn đặt vé nào</h3>
                <p>Bạn chưa đặt tour nào. Hãy khám phá các tour du lịch hấp dẫn của chúng tôi!</p>
                <a href="home.php" class="btn btn-primary">Xem danh sách tour</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

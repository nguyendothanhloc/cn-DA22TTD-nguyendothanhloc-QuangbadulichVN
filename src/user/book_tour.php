<?php
session_start();
require_once '../auth.php';
require_once '../db.php';
require_once '../helpers.php';

// Require user role
requireRole('user');

// Check if this is a success confirmation page
if (isset($_GET['success']) && isset($_SESSION['booking_success'])) {
    $booking = $_SESSION['booking_success'];
    
    // Clear the session data after retrieving it
    unset($_SESSION['booking_success']);
    
    // Display confirmation page
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Đặt vé thành công</title>
        <link rel="stylesheet" href="../assets/style.css">
        <link rel="stylesheet" href="../assets/animations.css">
        <style>
            .container {
                max-width: 700px;
                margin: 50px auto;
                padding: 20px;
            }
            .success-box {
                background: white;
                border: 2px solid #28a745;
                border-radius: 8px;
                padding: 30px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            }
            .success-icon {
                text-align: center;
                font-size: 4em;
                color: #28a745;
                margin-bottom: 20px;
            }
            .success-title {
                text-align: center;
                color: #28a745;
                font-size: 1.8em;
                margin-bottom: 30px;
            }
            .booking-info {
                background: #f8f9fa;
                padding: 20px;
                border-radius: 5px;
                margin: 20px 0;
            }
            .info-row {
                display: flex;
                justify-content: space-between;
                padding: 12px 0;
                border-bottom: 1px solid #dee2e6;
            }
            .info-row:last-child {
                border-bottom: none;
            }
            .info-label {
                font-weight: bold;
                color: #555;
            }
            .info-value {
                color: #333;
                text-align: right;
            }
            .total-row {
                background: #fff3cd;
                padding: 15px;
                border-radius: 5px;
                margin: 20px 0;
                font-size: 1.2em;
            }
            .status-badge {
                display: inline-block;
                padding: 5px 15px;
                background: #ffc107;
                color: #856404;
                border-radius: 3px;
                font-weight: bold;
            }
            .action-buttons {
                text-align: center;
                margin-top: 30px;
            }
            .btn {
                padding: 12px 30px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                text-decoration: none;
                display: inline-block;
                margin: 5px;
                font-size: 1em;
            }
            .btn-primary {
                background: #007bff;
                color: white;
            }
            .btn-success {
                background: #28a745;
                color: white;
            }
            .btn:hover {
                opacity: 0.9;
            }
            .note {
                background: #e7f3ff;
                border-left: 4px solid #007bff;
                padding: 15px;
                margin: 20px 0;
                color: #004085;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="success-box">
                <div class="success-icon">✓</div>
                <h1 class="success-title">Đặt vé thành công!</h1>
                
                <div class="booking-info">
                    <h3 style="margin-top: 0; color: #333;">Thông tin đặt vé</h3>
                    
                    <div class="info-row">
                        <div class="info-label">Mã đặt vé:</div>
                        <div class="info-value">#<?= $booking['booking_id'] ?></div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">Tour:</div>
                        <div class="info-value"><?= htmlspecialchars($booking['tour_name'], ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">Số lượng người:</div>
                        <div class="info-value"><?= $booking['num_people'] ?> người</div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">Trạng thái:</div>
                        <div class="info-value">
                            <span class="status-badge">
                                <?php
                                $status_text = [
                                    'pending' => 'Chờ xác nhận',
                                    'confirmed' => 'Đã xác nhận',
                                    'cancelled' => 'Đã hủy'
                                ];
                                echo $status_text[$booking['status']] ?? $booking['status'];
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="total-row">
                    <div style="display: flex; justify-content: space-between;">
                        <strong>Tổng tiền:</strong>
                        <strong style="color: #28a745;"><?= formatPrice($booking['total_price']) ?></strong>
                    </div>
                </div>
                
                <div class="note">
                    <strong>Lưu ý:</strong> Đơn đặt vé của bạn đang ở trạng thái "Chờ xác nhận". 
                    Quản trị viên sẽ xem xét và xác nhận đơn của bạn trong thời gian sớm nhất. 
                    Bạn có thể theo dõi trạng thái đơn đặt vé trong mục "Lịch sử đặt vé".
                </div>
                
                <div class="action-buttons">
                    <a href="my_bookings.php" class="btn btn-primary">Xem lịch sử đặt vé</a>
                    <a href="home.php" class="btn btn-success">Tiếp tục xem tour</a>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: home.php');
    exit;
}

// Get form data
$tour_id = isset($_POST['tour_id']) ? intval($_POST['tour_id']) : 0;
$num_people = isset($_POST['num_people']) ? intval($_POST['num_people']) : 0;
$user_id = $_SESSION['user_id'];

// Validate tour_id
if ($tour_id <= 0) {
    $_SESSION['booking_error'] = "Tour không hợp lệ";
    header('Location: home.php');
    exit;
}

// Start transaction to ensure data consistency
$conn->begin_transaction();

try {
    // Get tour information with FOR UPDATE lock to prevent race conditions
    $stmt = $conn->prepare("SELECT id, name, price, available_seats FROM tours WHERE id = ? FOR UPDATE");
    $stmt->bind_param("i", $tour_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        throw new Exception("Tour không tồn tại");
    }
    
    $tour = $result->fetch_assoc();
    
    // Use validation function from helpers.php
    $validation_errors = validateBooking($num_people, $tour['available_seats']);
    if (!empty($validation_errors)) {
        throw new Exception(implode('. ', $validation_errors));
    }
    
    // Calculate total price
    $total_price = $tour['price'] * $num_people;
    
    // Create booking with status = 'pending'
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, tour_id, num_people, total_price, status) VALUES (?, ?, ?, ?, 'pending')");
    $stmt->bind_param("iiid", $user_id, $tour_id, $num_people, $total_price);
    $stmt->execute();
    
    $booking_id = $conn->insert_id;
    
    // Decrease available_seats of tour
    $new_available_seats = $tour['available_seats'] - $num_people;
    $stmt = $conn->prepare("UPDATE tours SET available_seats = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_available_seats, $tour_id);
    $stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    // Store booking info in session for confirmation page
    $_SESSION['booking_success'] = [
        'booking_id' => $booking_id,
        'tour_name' => $tour['name'],
        'num_people' => $num_people,
        'total_price' => $total_price,
        'status' => 'pending'
    ];
    
    // Redirect to confirmation page (same page will show confirmation)
    header('Location: book_tour.php?success=1');
    exit;
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Store error message
    $_SESSION['booking_error'] = $e->getMessage();
    
    // Redirect back to tour detail page
    header('Location: tour_detail.php?id=' . $tour_id);
    exit;
}
?>

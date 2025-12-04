<?php
session_start();
require_once '../auth.php';
require_once '../db.php';

// Require user role
requireRole('user');

// Get booking_id from URL
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

// Validate booking_id
if ($booking_id <= 0) {
    $_SESSION['cancel_error'] = "Đơn đặt vé không hợp lệ";
    header('Location: my_bookings.php');
    exit;
}

// Start transaction to ensure data consistency
$conn->begin_transaction();

try {
    // Get booking information with FOR UPDATE lock
    // Check that booking belongs to current user
    $stmt = $conn->prepare("SELECT b.id, b.user_id, b.tour_id, b.num_people, b.status, t.name as tour_name 
                           FROM bookings b 
                           JOIN tours t ON b.tour_id = t.id 
                           WHERE b.id = ? FOR UPDATE");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        throw new Exception("Đơn đặt vé không tồn tại");
    }
    
    $booking = $result->fetch_assoc();
    
    // Check that booking belongs to current user
    if ($booking['user_id'] != $user_id) {
        throw new Exception("Bạn không có quyền hủy đơn đặt vé này");
    }
    
    // Check that status is 'pending'
    if ($booking['status'] !== 'pending') {
        $status_messages = [
            'confirmed' => 'Không thể hủy đơn đã được xác nhận',
            'cancelled' => 'Đơn đặt vé này đã được hủy trước đó'
        ];
        throw new Exception($status_messages[$booking['status']] ?? 'Không thể hủy đơn đặt vé này');
    }
    
    // Update booking status to 'cancelled'
    $stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    
    // Restore available seats to tour (increase by num_people)
    $stmt = $conn->prepare("UPDATE tours SET available_seats = available_seats + ? WHERE id = ?");
    $stmt->bind_param("ii", $booking['num_people'], $booking['tour_id']);
    $stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    // Set success message
    $_SESSION['cancel_success'] = "Đã hủy đơn đặt vé #" . $booking_id . " thành công. Số chỗ đã được hoàn lại cho tour.";
    
    // Redirect back to bookings page
    header('Location: my_bookings.php');
    exit;
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Store error message
    $_SESSION['cancel_error'] = $e->getMessage();
    
    // Redirect back to bookings page
    header('Location: my_bookings.php');
    exit;
}
?>

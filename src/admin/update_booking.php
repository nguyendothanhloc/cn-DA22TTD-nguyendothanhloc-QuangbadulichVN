<?php
session_start();
require_once '../auth.php';
require_once '../db.php';

// Require admin role
requireRole('admin');

// Get booking_id and action from URL
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Validate inputs
if ($booking_id <= 0) {
    $_SESSION['booking_error'] = "Đơn đặt vé không hợp lệ";
    header('Location: bookings.php');
    exit;
}

if (!in_array($action, ['confirm', 'cancel'])) {
    $_SESSION['booking_error'] = "Hành động không hợp lệ";
    header('Location: bookings.php');
    exit;
}

// Start transaction to ensure data consistency
$conn->begin_transaction();

try {
    // Get booking information with FOR UPDATE lock
    $stmt = $conn->prepare("SELECT b.id, b.user_id, b.tour_id, b.num_people, b.status, 
                                   t.name as tour_name, t.available_seats
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
    
    // Check that booking is not already cancelled
    // Cannot change status of cancelled bookings (Requirement 4.5)
    if ($booking['status'] === 'cancelled') {
        throw new Exception("Không thể thay đổi trạng thái đơn đặt vé đã hủy");
    }
    
    // Process based on action
    if ($action === 'confirm') {
        // Confirm booking: update status to 'confirmed'
        // Only makes sense if current status is 'pending'
        if ($booking['status'] === 'confirmed') {
            throw new Exception("Đơn đặt vé đã được xác nhận trước đó");
        }
        
        $stmt = $conn->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?");
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        
        $success_message = "Đã xác nhận đơn đặt vé #" . $booking_id . " thành công";
        
    } elseif ($action === 'cancel') {
        // Cancel booking: update status to 'cancelled' and restore seats
        
        // Update booking status to 'cancelled'
        $stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        
        // Restore available seats to tour (increase by num_people)
        $stmt = $conn->prepare("UPDATE tours SET available_seats = available_seats + ? WHERE id = ?");
        $stmt->bind_param("ii", $booking['num_people'], $booking['tour_id']);
        $stmt->execute();
        
        $success_message = "Đã hủy đơn đặt vé #" . $booking_id . " thành công. Số chỗ đã được hoàn lại cho tour.";
    }
    
    // Commit transaction
    $conn->commit();
    
    // Set success message
    $_SESSION['booking_success'] = $success_message;
    
    // Redirect back to bookings page
    header('Location: bookings.php');
    exit;
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Store error message
    $_SESSION['booking_error'] = $e->getMessage();
    
    // Redirect back to bookings page
    header('Location: bookings.php');
    exit;
}
?>

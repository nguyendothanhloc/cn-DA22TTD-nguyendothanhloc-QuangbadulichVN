<?php
session_start();
require_once '../auth.php';
require_once '../db.php';

// Require admin role
requireRole('admin');

// Get and validate tour ID
$tour_id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);

if (!$tour_id || $tour_id <= 0) {
    $_SESSION['error_message'] = "ID tour không hợp lệ";
    header('Location: tours.php');
    exit;
}

// Check if tour has any bookings
$stmt = $conn->prepare("SELECT COUNT(*) as booking_count FROM bookings WHERE tour_id = ?");
$stmt->bind_param("i", $tour_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$booking_count = $row['booking_count'];
$stmt->close();

// If tour has bookings, reject deletion
if ($booking_count > 0) {
    $_SESSION['error_message'] = "Không thể xóa tour đã có người đặt. Tour này có $booking_count đơn đặt vé.";
    header('Location: tours.php');
    exit;
}

// If no bookings, proceed with deletion
$stmt = $conn->prepare("DELETE FROM tours WHERE id = ?");
$stmt->bind_param("i", $tour_id);

if ($stmt->execute()) {
    $_SESSION['success_message'] = "Xóa tour thành công";
} else {
    $_SESSION['error_message'] = "Có lỗi xảy ra khi xóa tour: " . $stmt->error;
}

$stmt->close();

// Redirect back to tours page
header('Location: tours.php');
exit;
?>

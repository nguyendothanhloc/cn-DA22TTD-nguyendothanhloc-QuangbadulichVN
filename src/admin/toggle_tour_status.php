<?php
session_start();
require_once '../auth.php';
require_once '../db.php';

// Require admin role
requireRole('admin');

// Get tour ID
$id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);

if ($id > 0) {
    // Get current status
    $stmt = $conn->prepare("SELECT status FROM tours WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $tour = $result->fetch_assoc();
    $stmt->close();
    
    if ($tour) {
        // Toggle status
        $new_status = ($tour['status'] === 'active') ? 'inactive' : 'active';
        
        $stmt = $conn->prepare("UPDATE tours SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $id);
        
        if ($stmt->execute()) {
            $status_text = ($new_status === 'active') ? 'Hoạt động' : 'Tạm ngưng';
            $_SESSION['success_message'] = "Đã chuyển trạng thái tour sang: $status_text";
        } else {
            $_SESSION['error_message'] = "Không thể thay đổi trạng thái tour!";
        }
        $stmt->close();
    }
}

header('Location: tours.php');
exit;
?>

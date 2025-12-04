<?php
session_start();
require_once '../auth.php';
require_once '../db.php';

// Require admin role
requireRole('admin');

// Get and validate ID
$id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);

if ($id > 0) {
    // Get place data to delete image
    $stmt = $conn->prepare("SELECT image FROM places WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $place = $result->fetch_assoc();
    $stmt->close();
    
    if ($place) {
        // Delete image file if exists
        if (!empty($place['image']) && file_exists('../uploads/places/' . $place['image'])) {
            unlink('../uploads/places/' . $place['image']);
        }
        
        // Delete place from database
        $stmt = $conn->prepare("DELETE FROM places WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
}

header('Location: places.php');
exit;
?>
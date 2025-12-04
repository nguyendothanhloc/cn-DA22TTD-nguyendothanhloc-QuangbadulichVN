<?php
// Database configuration
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "travel_booking";
$port       = 3306; // Port MySQL thực tế của bạn

// Create connection with error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($servername, $username, $password, $dbname, $port);
    
    // Thiết lập UTF-8 để tránh lỗi tiếng Việt
    if (!$conn->set_charset("utf8mb4")) {
        throw new Exception("Lỗi thiết lập charset UTF-8: " . $conn->error);
    }
    
} catch (Exception $e) {
    // Log error for debugging (in production, log to file instead)
    error_log("Database connection error: " . $e->getMessage());
    
    // Display user-friendly error message
    die("Không thể kết nối đến cơ sở dữ liệu. Vui lòng thử lại sau.");
}
?>

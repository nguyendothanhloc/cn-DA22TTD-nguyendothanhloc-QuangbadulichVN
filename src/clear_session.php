<?php
// Clear all session data
session_start();
session_unset();
session_destroy();

// Start a new clean session
session_start();

echo "Session đã được xóa sạch!<br>";
echo "<a href='index.php'>Quay về trang chủ</a><br>";
echo "<a href='login.php'>Đăng nhập</a>";
?>

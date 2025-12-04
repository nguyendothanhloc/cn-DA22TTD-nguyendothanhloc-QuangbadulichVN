<?php
echo "<h2>Kiểm tra kết nối Database</h2>";

// Test 1: Kiểm tra extension mysqli
echo "<h3>1. Kiểm tra PHP mysqli extension:</h3>";
if (extension_loaded('mysqli')) {
    echo "✅ mysqli extension đã được cài đặt<br>";
} else {
    echo "❌ mysqli extension CHƯA được cài đặt<br>";
    echo "→ Cần enable mysqli trong php.ini<br>";
}

// Test 2: Thử kết nối với các port khác nhau
echo "<h3>2. Thử kết nối MySQL:</h3>";

$servername = "localhost";
$username = "root";
$password = "";
$ports = [3306, 3307, 3308]; // Thử các port phổ biến

foreach ($ports as $port) {
    echo "<br><strong>Thử port $port:</strong><br>";
    
    // Tắt error reporting tạm thời
    mysqli_report(MYSQLI_REPORT_OFF);
    
    $conn = @new mysqli($servername, $username, $password, "", $port);
    
    if ($conn->connect_error) {
        echo "❌ Không kết nối được - Lỗi: " . $conn->connect_error . "<br>";
    } else {
        echo "✅ Kết nối thành công!<br>";
        
        // Kiểm tra database travel_booking có tồn tại không
        $result = $conn->query("SHOW DATABASES LIKE 'travel_booking'");
        if ($result && $result->num_rows > 0) {
            echo "✅ Database 'travel_booking' đã tồn tại<br>";
            
            // Thử kết nối vào database
            $conn->select_db('travel_booking');
            
            // Kiểm tra các bảng
            $tables = $conn->query("SHOW TABLES");
            if ($tables) {
                echo "✅ Các bảng trong database:<br>";
                while ($row = $tables->fetch_array()) {
                    echo "&nbsp;&nbsp;&nbsp;- " . $row[0] . "<br>";
                }
            }
        } else {
            echo "❌ Database 'travel_booking' CHƯA tồn tại<br>";
            echo "→ Cần import file database.sql<br>";
        }
        
        $conn->close();
        
        echo "<br><strong style='color: green;'>→ SỬ DỤNG PORT $port</strong><br>";
        break; // Dừng lại khi tìm được port đúng
    }
}

echo "<hr>";
echo "<h3>3. Hướng dẫn tiếp theo:</h3>";
echo "<ol>";
echo "<li>Nếu không có port nào kết nối được → Kiểm tra MySQL đã chạy trong XAMPP chưa</li>";
echo "<li>Nếu kết nối được nhưng database chưa tồn tại → Import file database.sql</li>";
echo "<li>Sau khi sửa xong, cập nhật port đúng trong file db.php</li>";
echo "</ol>";
?>

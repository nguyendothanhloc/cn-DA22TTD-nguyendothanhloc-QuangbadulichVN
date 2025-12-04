<?php
include 'db.php';

echo "<h2>Thêm cột 'image' vào bảng tours</h2>";
echo "<hr>";

// Kiểm tra xem cột image đã tồn tại chưa
$check = $conn->query("SHOW COLUMNS FROM tours LIKE 'image'");

if ($check->num_rows > 0) {
    echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px;'>";
    echo "⚠️ Cột 'image' đã tồn tại trong bảng tours!";
    echo "</div>";
} else {
    // Thêm cột image
    $sql = "ALTER TABLE tours ADD COLUMN image VARCHAR(255) DEFAULT NULL AFTER description";
    
    if ($conn->query($sql)) {
        echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px;'>";
        echo "✅ <strong>THÀNH CÔNG!</strong><br>";
        echo "Đã thêm cột 'image' vào bảng tours.<br><br>";
        echo "Bây giờ bạn có thể tải hình ảnh lên cho tours!";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 20px; border-radius: 8px;'>";
        echo "❌ <strong>LỖI!</strong><br>";
        echo "Không thể thêm cột: " . $conn->error;
        echo "</div>";
    }
}

echo "<hr>";
echo "<h3>Cấu trúc bảng tours hiện tại:</h3>";
$columns = $conn->query("SHOW COLUMNS FROM tours");
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr style='background: #f0f0f0;'><th>Tên cột</th><th>Kiểu dữ liệu</th><th>Null</th><th>Mặc định</th></tr>";
while ($col = $columns->fetch_assoc()) {
    echo "<tr>";
    echo "<td><strong>{$col['Field']}</strong></td>";
    echo "<td>{$col['Type']}</td>";
    echo "<td>{$col['Null']}</td>";
    echo "<td>{$col['Default']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";
echo "<p><a href='admin/tours.php' style='padding: 10px 20px; background: #4caf50; color: white; text-decoration: none; border-radius: 5px;'>Quay lại trang quản lý Tours</a></p>";

$conn->close();
?>

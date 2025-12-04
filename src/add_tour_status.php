<?php
include 'db.php';

echo "<h2>Thêm cột 'status' vào bảng tours</h2>";
echo "<hr>";

// Kiểm tra xem cột status đã tồn tại chưa
$check = $conn->query("SHOW COLUMNS FROM tours LIKE 'status'");

if ($check->num_rows > 0) {
    echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px;'>";
    echo "⚠️ Cột 'status' đã tồn tại trong bảng tours!";
    echo "</div>";
} else {
    // Thêm cột status
    $sql = "ALTER TABLE tours ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active' AFTER available_seats";
    
    if ($conn->query($sql)) {
        echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px;'>";
        echo "✅ <strong>THÀNH CÔNG!</strong><br>";
        echo "Đã thêm cột 'status' vào bảng tours.<br><br>";
        echo "Các trạng thái:<br>";
        echo "- <strong>active</strong>: Hoạt động (cho phép đặt tour)<br>";
        echo "- <strong>inactive</strong>: Tạm ngưng (không cho đặt tour)<br>";
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
    $highlight = $col['Field'] === 'status' ? 'background: #e8f5e9;' : '';
    echo "<tr style='$highlight'>";
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

<?php
include 'db.php';

echo "<h2>Kiểm tra hình ảnh Tours</h2>";
echo "<hr>";

$result = $conn->query("SELECT id, name, image FROM tours ORDER BY id");

echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #f0f0f0;'>";
echo "<th>ID</th><th>Tên Tour</th><th>Tên file trong DB</th><th>File tồn tại?</th><th>Đường dẫn</th></tr>";

while ($tour = $result->fetch_assoc()) {
    $image = $tour['image'];
    $file_path = 'uploads/tours/' . $image;
    $exists = file_exists($file_path);
    
    $bg = $exists ? '#d4edda' : '#f8d7da';
    $status = $exists ? '✅ Có' : '❌ Không';
    
    echo "<tr style='background: $bg;'>";
    echo "<td>{$tour['id']}</td>";
    echo "<td>{$tour['name']}</td>";
    echo "<td><code>$image</code></td>";
    echo "<td><strong>$status</strong></td>";
    echo "<td><code>$file_path</code></td>";
    echo "</tr>";
}

echo "</table>";

echo "<hr>";
echo "<h3>Kiểm tra thư mục uploads/tours/</h3>";
$files = scandir('uploads/tours/');
echo "<ul>";
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        echo "<li>$file</li>";
    }
}
echo "</ul>";

$conn->close();
?>

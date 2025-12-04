<?php
/**
 * Script tạo thư mục uploads và cấu hình bảo mật
 */

echo "<h2>Tạo thư mục uploads cho hình ảnh</h2>";

// Danh sách thư mục cần tạo
$folders = [
    'uploads',
    'uploads/tours',
    'uploads/places'
];

$created = [];
$existed = [];
$errors = [];

foreach ($folders as $folder) {
    if (file_exists($folder)) {
        $existed[] = $folder;
        echo "<p style='color: blue;'>✓ Thư mục đã tồn tại: <strong>$folder</strong></p>";
    } else {
        if (mkdir($folder, 0755, true)) {
            $created[] = $folder;
            echo "<p style='color: green;'>✓ Đã tạo thư mục: <strong>$folder</strong></p>";
        } else {
            $errors[] = $folder;
            echo "<p style='color: red;'>✗ Lỗi tạo thư mục: <strong>$folder</strong></p>";
        }
    }
}

// Tạo file .htaccess để bảo mật
$htaccess_content = '# Allow image files only
<FilesMatch "\.(jpg|jpeg|png|gif|webp)$">
    Order Allow,Deny
    Allow from all
</FilesMatch>

# Deny access to PHP files
<FilesMatch "\.php$">
    Order Deny,Allow
    Deny from all
</FilesMatch>
';

$htaccess_files = [
    'uploads/tours/.htaccess',
    'uploads/places/.htaccess'
];

foreach ($htaccess_files as $file) {
    if (!file_exists($file)) {
        if (file_put_contents($file, $htaccess_content)) {
            echo "<p style='color: green;'>✓ Đã tạo file bảo mật: <strong>$file</strong></p>";
        } else {
            echo "<p style='color: orange;'>⚠ Không thể tạo file: <strong>$file</strong></p>";
        }
    } else {
        echo "<p style='color: blue;'>✓ File bảo mật đã tồn tại: <strong>$file</strong></p>";
    }
}

// Tạo file index.php để chặn directory listing
$index_content = '<?php
// Prevent directory listing
header("HTTP/1.0 403 Forbidden");
exit("Directory access is forbidden.");
?>';

$index_files = [
    'uploads/index.php',
    'uploads/tours/index.php',
    'uploads/places/index.php'
];

foreach ($index_files as $file) {
    if (!file_exists($file)) {
        if (file_put_contents($file, $index_content)) {
            echo "<p style='color: green;'>✓ Đã tạo file chặn listing: <strong>$file</strong></p>";
        }
    } else {
        echo "<p style='color: blue;'>✓ File chặn listing đã tồn tại: <strong>$file</strong></p>";
    }
}

// Kiểm tra quyền ghi
echo "<hr>";
echo "<h3>Kiểm tra quyền ghi:</h3>";

foreach (['uploads/tours', 'uploads/places'] as $folder) {
    if (is_writable($folder)) {
        echo "<p style='color: green;'>✓ Thư mục <strong>$folder</strong> có quyền ghi</p>";
    } else {
        echo "<p style='color: red;'>✗ Thư mục <strong>$folder</strong> KHÔNG có quyền ghi. Cần chmod 755 hoặc 777</p>";
    }
}

// Tóm tắt
echo "<hr>";
echo "<h3>Tóm tắt:</h3>";
echo "<ul>";
echo "<li>Thư mục đã tạo: <strong>" . count($created) . "</strong></li>";
echo "<li>Thư mục đã tồn tại: <strong>" . count($existed) . "</strong></li>";
echo "<li>Lỗi: <strong>" . count($errors) . "</strong></li>";
echo "</ul>";

if (empty($errors)) {
    echo "<div style='background: #e8f5e9; padding: 15px; border-left: 4px solid #4caf50; margin-top: 20px;'>";
    echo "<h3 style='color: #1b5e20; margin-top: 0;'>✓ Hoàn thành!</h3>";
    echo "<p>Tất cả thư mục đã được tạo và cấu hình bảo mật.</p>";
    echo "<p><strong>Bây giờ bạn có thể:</strong></p>";
    echo "<ul>";
    echo "<li>Upload ảnh tour qua trang admin</li>";
    echo "<li>Copy ảnh trực tiếp vào thư mục <code>uploads/tours/</code></li>";
    echo "<li>Ảnh sẽ tự động hiển thị trên website</li>";
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin-top: 20px;'>";
    echo "<h3 style='color: #856404; margin-top: 0;'>⚠ Có lỗi xảy ra</h3>";
    echo "<p>Một số thư mục không thể tạo. Vui lòng:</p>";
    echo "<ul>";
    echo "<li>Kiểm tra quyền ghi của thư mục gốc</li>";
    echo "<li>Tạo thư mục thủ công qua FTP/File Manager</li>";
    echo "<li>Chmod 755 hoặc 777 cho thư mục uploads</li>";
    echo "</ul>";
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='admin/add_tour.php' style='background: #4caf50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>→ Đi đến trang thêm tour</a></p>";
?>

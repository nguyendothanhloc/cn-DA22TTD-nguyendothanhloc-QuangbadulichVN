<?php
require_once 'db.php';
require_once 'helpers.php';

// Get featured tours (upcoming tours with available seats, limit 6)
$sql = "SELECT t.*, p.name as place_name 
        FROM tours t 
        LEFT JOIN places p ON t.place_id = p.id 
        WHERE t.available_seats > 0 
        AND t.departure_date >= CURDATE()
        ORDER BY t.departure_date ASC 
        LIMIT 6";
$featured_tours = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống Du lịch và Đặt vé</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/animations.css">
    <style>
        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #4caf50 0%, #1a4d2e 100%);
            color: white;
            padding: 80px 20px;
            text-align: center;
            border-radius: 10px;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(76, 175, 80, 0.3);
        }
        
        .hero h1 {
            font-size: 3em;
            margin-bottom: 20px;
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .hero p {
            font-size: 1.3em;
            margin-bottom: 30px;
            opacity: 0.95;
        }
        
        .hero .btn {
            font-size: 1.1em;
            padding: 15px 40px;
            margin: 0 10px;
        }
        
        .hero .btn-success {
            background: white;
            color: #1a4d2e;
            font-weight: 600;
        }
        
        .hero .btn-success:hover {
            background: #f0f9f4;
            color: #0d3320;
        }
        
        .hero .btn-secondary {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid white;
        }
        
        .hero .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        /* Features Section */
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin: 50px 0;
        }
        
        .feature-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
        }
        
        .feature-card h3 {
            color: #4caf50;
            margin-bottom: 10px;
        }
        
        /* Tours Section */
        .section-title {
            text-align: center;
            font-size: 2.5em;
            color: #1a4d2e;
            margin: 50px 0 30px 0;
            border-bottom: 3px solid #4caf50;
            padding-bottom: 15px;
        }
        
        .tours-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-bottom: 40px;
        }
        
        @media (max-width: 1200px) {
            .tours-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .tours-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .tour-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .tour-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 12px rgba(76, 175, 80, 0.2);
        }
        
        .tour-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            background: #e8f5e9;
        }
        
        .tour-card-body {
            padding: 20px;
        }
        
        .tour-card h3 {
            color: #1a4d2e;
            margin-bottom: 10px;
            font-size: 1.3em;
        }
        
        .tour-info {
            color: #666;
            margin: 10px 0;
            font-size: 0.95em;
        }
        
        .tour-info p {
            margin: 5px 0;
        }
        
        .tour-price {
            font-size: 1.4em;
            font-weight: bold;
            color: #2e7d32;
            margin: 15px 0;
        }
        
        .tour-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
        }
        
        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, #66bb6a 0%, #4caf50 100%);
            color: white;
            padding: 60px 20px;
            text-align: center;
            border-radius: 10px;
            margin: 50px 0;
            box-shadow: 0 8px 16px rgba(76, 175, 80, 0.3);
        }
        
        .cta-section h2 {
            color: white;
            font-size: 2.5em;
            margin-bottom: 20px;
            border: none;
        }
        
        .cta-section p {
            font-size: 1.2em;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Hero Section -->
        <div class="hero">
            <h1>Quảng bá du lịch Việt Nam</h1>
            <p>Đặt tour du lịch dễ dàng, trải nghiệm tuyệt vời</p>
            <a href="login.php" class="btn btn-success">Đăng nhập</a>
            <a href="register.php" class="btn btn-secondary">Đăng ký</a>
        </div>
        
        <!-- Features Section -->
        <div class="features">
            <div class="feature-card">
                <h3>Dễ dàng đặt vé</h3>
                <p>Đặt tour chỉ với vài cú click chuột, nhanh chóng và tiện lợi</p>
            </div>
            <div class="feature-card">
                <h3>Giá cả hợp lý</h3>
                <p>Nhiều tour với mức giá phù hợp, phục vụ mọi nhu cầu</p>
            </div>
            <div class="feature-card">
                <h3>Chất lượng đảm bảo</h3>
                <p>Đội ngũ hướng dẫn viên chuyên nghiệp, dịch vụ tận tâm</p>
            </div>
            <div class="feature-card">
                <h3>Quản lý đơn hàng</h3>
                <p>Theo dõi lịch sử đặt vé, quản lý đơn hàng dễ dàng</p>
            </div>
        </div>
        
        <!-- Featured Tours Section -->
        <h2 class="section-title">Tour nổi bật</h2>
        
        <?php if ($featured_tours && $featured_tours->num_rows > 0): ?>
            <div class="tours-grid">
                <?php while($tour = $featured_tours->fetch_assoc()): ?>
                    <div class="tour-card">
                        <?php 
                        $image_path = !empty($tour['image']) && file_exists('uploads/tours/' . $tour['image']) 
                            ? 'uploads/tours/' . $tour['image'] 
                            : 'uploads/tours/placeholder.svg';
                        ?>
                        <img src="<?= htmlspecialchars($image_path, ENT_QUOTES, 'UTF-8') ?>" 
                             alt="<?= htmlspecialchars($tour['name'], ENT_QUOTES, 'UTF-8') ?>" 
                             class="tour-image">
                        
                        <div class="tour-card-body">
                            <h3><?= htmlspecialchars($tour['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                            
                            <div class="tour-info">
                                <p><strong>Địa điểm:</strong> <?= htmlspecialchars($tour['place_name'] ?? 'Chưa xác định', ENT_QUOTES, 'UTF-8') ?></p>
                                <p><strong>Khởi hành:</strong> <?= formatDate($tour['departure_date']) ?></p>
                                <p><strong>Còn lại:</strong> <?= $tour['available_seats'] ?> chỗ</p>
                            </div>
                            
                            <div class="tour-price">
                                <?= formatPrice($tour['price']) ?>
                            </div>
                            
                            <div class="tour-meta">
                                <span class="badge badge-available">Còn chỗ</span>
                                <a href="login.php" class="btn btn-primary btn-small">Đặt ngay</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="card">
                <p class="text-center">Hiện tại chưa có tour nào. Vui lòng quay lại sau!</p>
            </div>
        <?php endif; ?>
        
        <!-- Call to Action -->
        <div class="cta-section">
            <h2>Sẵn sàng cho chuyến đi của bạn?</h2>
            <p>Đăng nhập ngay để khám phá và đặt tour yêu thích</p>
            <a href="login.php" class="btn btn-primary" style="background: white; color: #1a4d2e; font-size: 1.1em; font-weight: 600;">
                Bắt đầu ngay
            </a>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <p>&copy; 2024 Hệ thống Du lịch và Đặt vé. All rights reserved.</p>
        <p>Liên hệ: info@dulich.vn | Hotline: 1900-xxxx</p>
    </div>
    
    <!-- Animation JavaScript -->
    <script src="assets/animations.js"></script>
</body>
</html>

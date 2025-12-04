<?php
session_start();
require_once '../auth.php';
require_once '../db.php';
require_once '../helpers.php';

// Require user role
requireRole('user');

// Get tour ID
$tour_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($tour_id <= 0) {
    die("Tour không hợp lệ");
}

// Get tour details with place information
$stmt = $conn->prepare("SELECT t.*, p.name as place_name, p.note as place_note, p.image as place_image 
                        FROM tours t 
                        LEFT JOIN places p ON t.place_id = p.id 
                        WHERE t.id = ?");
$stmt->bind_param("i", $tour_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Tour không tồn tại");
}

$tour = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tour['name'], ENT_QUOTES, 'UTF-8') ?> - Chi tiết Tour</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/animations.css">
    <style>
        .container {
            max-width: 1000px;
            width: 100%;
            margin: 0 auto;
            padding: 20px;
        }
        h2, h3 {
            color: #1a4d2e;
        }
        .nav-menu {
            margin-bottom: 20px;
            padding: 15px 20px;
            background: linear-gradient(135deg, #1a4d2e 0%, #0d3320 100%);
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(26, 77, 46, 0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .nav-menu span {
            color: white;
            font-weight: 600;
            font-size: 1.1em;
        }
        .nav-menu div {
            display: flex;
            gap: 15px;
        }
        .nav-menu a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .nav-menu a:hover {
            background-color: #4caf50;
            transform: translateY(-2px);
        }
        .tour-detail {
            background: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .tour-detail h2 {
            color: #1a4d2e;
            margin-top: 0;
            border-bottom: 3px solid #4caf50;
            padding-bottom: 10px;
        }
        .tour-image {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .tour-info-section {
            margin: 20px 0;
        }
        .tour-info-section h3 {
            color: #555;
            margin-bottom: 10px;
        }
        .info-row {
            display: flex;
            margin: 10px 0;
            padding: 10px;
            background: #f0f9f4;
            border-radius: 6px;
            border-left: 3px solid #c8e6c9;
        }
        .info-label {
            font-weight: bold;
            width: 200px;
            color: #555;
        }
        .info-value {
            flex: 1;
            color: #333;
        }
        .tour-price {
            font-size: 1.5em;
            font-weight: bold;
            color: #2e7d32;
            margin: 20px 0;
        }
        .tour-status {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 3px;
            font-weight: bold;
            margin: 10px 0;
        }
        .status-available {
            background: #c8e6c9;
            color: #1b5e20;
        }
        .status-soldout {
            background: #f8d7da;
            color: #721c24;
        }
        .place-info {
            background: #e8f5e9;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border: 1px solid #c8e6c9;
        }
        .booking-form {
            background: #f0f9f4;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
            border: 2px solid #4caf50;
        }
        .booking-form h3 {
            margin-top: 0;
            color: #1a4d2e;
        }
        .form-group {
            margin: 15px 0;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 1em;
            transition: border-color 0.3s ease;
        }
        .form-group input:focus {
            outline: none;
            border-color: #4caf50;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
        }
        .form-group .help-text {
            font-size: 0.9em;
            color: #666;
            margin-top: 5px;
        }
        .total-price {
            background: #fff3cd;
            padding: 15px;
            border-radius: 3px;
            margin: 15px 0;
            font-size: 1.1em;
        }
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 1em;
            font-weight: 600;
            min-width: 180px;
            height: 48px;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        .btn-primary {
            background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%);
            color: white;
            box-shadow: 0 2px 4px rgba(76, 175, 80, 0.3);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #66bb6a 0%, #388e3c 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(76, 175, 80, 0.4);
        }
        .btn-success {
            background: linear-gradient(135deg, #66bb6a 0%, #4caf50 100%);
            color: white;
            box-shadow: 0 2px 4px rgba(76, 175, 80, 0.3);
        }
        .btn-success:hover {
            background: linear-gradient(135deg, #81c784 0%, #66bb6a 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(76, 175, 80, 0.4);
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
            box-shadow: 0 2px 4px rgba(108, 117, 125, 0.3);
        }
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(108, 117, 125, 0.4);
        }
        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .alert {
            padding: 15px;
            border-radius: 3px;
            margin: 15px 0;
        }
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav-menu">
            <span>Xin chào, <?= htmlspecialchars($_SESSION['fullname'] ?? $_SESSION['username'], ENT_QUOTES, 'UTF-8') ?></span>
            <div>
                <a href="home.php">Trang chủ</a>
                <a href="my_bookings.php">Lịch sử đặt vé</a>
                <a href="../logout.php">Đăng xuất</a>
            </div>
        </div>
        
        <div class="tour-detail">
            <h2><?= htmlspecialchars($tour['name'], ENT_QUOTES, 'UTF-8') ?></h2>
            
            <!-- Tour Image -->
            <?php 
            $image_path = !empty($tour['image']) && file_exists('../uploads/tours/' . $tour['image']) 
                ? '../uploads/tours/' . $tour['image'] 
                : '../uploads/tours/placeholder.svg';
            ?>
            <img src="<?= htmlspecialchars($image_path, ENT_QUOTES, 'UTF-8') ?>" 
                 alt="<?= htmlspecialchars($tour['name'], ENT_QUOTES, 'UTF-8') ?>" 
                 class="tour-image">
            
            <!-- Tour Information -->
            <div class="tour-info-section">
                <h3>Thông tin Tour</h3>
                
                <div class="info-row">
                    <div class="info-label">Mô tả:</div>
                    <div class="info-value"><?= nl2br(htmlspecialchars($tour['description'], ENT_QUOTES, 'UTF-8')) ?></div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Ngày khởi hành:</div>
                    <div class="info-value"><?= formatDate($tour['departure_date']) ?></div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Số chỗ còn lại:</div>
                    <div class="info-value"><?= $tour['available_seats'] ?> chỗ</div>
                </div>
                
                <div class="tour-price">
                    Giá: <?= formatPrice($tour['price']) ?> / người
                </div>
                
                <?php 
                $tour_status = $tour['status'] ?? 'active';
                if ($tour_status === 'inactive'): 
                ?>
                    <div class="tour-status status-soldout">Tạm ngưng</div>
                <?php elseif ($tour['available_seats'] == 0): ?>
                    <div class="tour-status status-soldout">Hết chỗ</div>
                <?php else: ?>
                    <div class="tour-status status-available">Còn chỗ</div>
                <?php endif; ?>
            </div>
            
            <!-- Place Information -->
            <?php if ($tour['place_id']): ?>
                <div class="tour-info-section">
                    <h3>Thông tin Địa điểm</h3>
                    <div class="place-info">
                        <h4><?= htmlspecialchars($tour['place_name'], ENT_QUOTES, 'UTF-8') ?></h4>
                        <?php if ($tour['place_note']): ?>
                            <p><?= nl2br(htmlspecialchars($tour['place_note'], ENT_QUOTES, 'UTF-8')) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Booking Form -->
            <div id="book" class="booking-form">
                <h3>Đặt Tour</h3>
                
                <?php if (isset($_SESSION['booking_error'])): ?>
                    <div class="alert alert-warning">
                        <strong>Lỗi:</strong> <?= htmlspecialchars($_SESSION['booking_error'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    <?php unset($_SESSION['booking_error']); ?>
                <?php endif; ?>
                
                <?php if ($tour_status === 'inactive'): ?>
                    <div class="alert alert-warning">
                        <strong>Xin lỗi!</strong> Tour này hiện đang tạm ngưng. Vui lòng chọn tour khác hoặc quay lại sau.
                    </div>
                <?php elseif ($tour['available_seats'] == 0): ?>
                    <div class="alert alert-warning">
                        <strong>Xin lỗi!</strong> Tour này hiện đã hết chỗ. Vui lòng chọn tour khác hoặc quay lại sau.
                    </div>
                <?php else: ?>
                    <form method="POST" action="book_tour.php" id="bookingForm">
                        <input type="hidden" name="tour_id" value="<?= $tour['id'] ?>">
                        
                        <div class="form-group">
                            <label for="num_people">Số lượng người:</label>
                            <input type="number" 
                                   id="num_people" 
                                   name="num_people" 
                                   min="1" 
                                   max="<?= $tour['available_seats'] ?>" 
                                   value="1" 
                                   required
                                   onchange="calculateTotal()">
                            <div class="help-text">Tối đa: <?= $tour['available_seats'] ?> người</div>
                        </div>
                        
                        <div class="total-price" id="totalPrice">
                            <strong>Tổng tiền:</strong> <span id="totalAmount"><?= formatPrice($tour['price']) ?></span>
                        </div>
                        
                        <div style="display: flex; gap: 10px; align-items: center; margin-top: 20px;">
                            <button type="submit" class="btn btn-success">Xác nhận đặt tour</button>
                            <a href="home.php" class="btn btn-secondary">Hủy</a>
                        </div>
                    </form>
                    
                    <script>
                        const pricePerPerson = <?= $tour['price'] ?>;
                        
                        function calculateTotal() {
                            const numPeople = document.getElementById('num_people').value;
                            const total = pricePerPerson * numPeople;
                            document.getElementById('totalAmount').textContent = formatPrice(total);
                        }
                        
                        function formatPrice(price) {
                            return new Intl.NumberFormat('vi-VN').format(price) + ' VNĐ';
                        }
                        
                        // Validate form before submit
                        document.getElementById('bookingForm').addEventListener('submit', function(e) {
                            const numPeople = parseInt(document.getElementById('num_people').value);
                            const maxSeats = <?= $tour['available_seats'] ?>;
                            
                            if (numPeople <= 0) {
                                e.preventDefault();
                                alert('Số lượng người phải lớn hơn 0');
                                return false;
                            }
                            
                            if (numPeople > maxSeats) {
                                e.preventDefault();
                                alert('Số lượng người vượt quá số chỗ còn lại (' + maxSeats + ' chỗ)');
                                return false;
                            }
                        });
                    </script>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

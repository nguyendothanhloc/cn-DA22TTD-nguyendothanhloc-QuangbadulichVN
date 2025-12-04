-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 02, 2025 at 04:24 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `travel_booking`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tour_id` int(11) NOT NULL,
  `num_people` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `tour_id`, `num_people`, `total_price`, `booking_date`, `status`) VALUES
(1, 2, 1, 2, 5000000.00, '2025-11-20 03:30:00', 'confirmed'),
(2, 2, 7, 1, 2800000.00, '2025-11-22 07:15:00', 'pending'),
(3, 3, 3, 3, 9600000.00, '2025-11-21 02:45:00', 'confirmed'),
(4, 3, 9, 2, 6400000.00, '2025-11-23 09:20:00', 'pending'),
(5, 4, 5, 4, 22000000.00, '2025-11-19 04:00:00', 'confirmed'),
(6, 4, 11, 2, 7200000.00, '2025-11-18 06:30:00', 'cancelled'),
(7, 5, 12, 1, 2200000.00, '2025-11-26 07:52:28', 'confirmed'),
(8, 5, 12, 1, 2200000.00, '2025-11-26 07:54:10', 'confirmed'),
(9, 5, 20, 1, 1100000.00, '2025-11-27 08:47:24', 'pending'),
(10, 5, 19, 1, 1000000.00, '2025-11-27 08:47:30', 'cancelled'),
(11, 5, 16, 4, 12800000.00, '2025-11-27 08:52:59', 'pending'),
(12, 5, 17, 1, 1000000.00, '2025-11-27 08:58:37', 'cancelled'),
(13, 5, 1, 1, 2500000.00, '2025-11-27 09:01:46', 'cancelled');

-- --------------------------------------------------------

--
-- Table structure for table `places`
--

CREATE TABLE `places` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `note` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `places`
--

INSERT INTO `places` (`id`, `name`, `note`, `image`, `created_at`) VALUES
(1, 'Hạ Long', 'Vịnh Hạ Long - Di sản thiên nhiên thế giới với hàng nghìn đảo đá vôi', 'place_6926a60c830bc.jpg', '2025-11-26 06:54:40'),
(2, 'Sapa', 'Thị trấn miền núi với ruộng bậc thang tuyệt đẹp và văn hóa dân tộc đa dạng', 'place_6926a5fd33674.jpg', '2025-11-26 06:54:40'),
(3, 'Phú Quốc', 'Đảo ngọc với bãi biển đẹp, rừng nhiệt đới và hải sản tươi ngon', 'place_6926a5eea2cd0.jpg', '2025-11-26 06:54:40'),
(4, 'Đà Lạt', 'Thành phố ngàn hoa với khí hậu mát mẻ quanh năm', 'place_6926a5dfac967.jpg', '2025-11-26 06:54:40'),
(5, 'Nha Trang', 'Thành phố biển với bãi tắm đẹp và các hoạt động thể thao dưới nước', 'place_6926a5d4044a8.jpg', '2025-11-26 06:54:40'),
(6, 'Hội An', 'Phố cổ với kiến trúc độc đáo, đèn lồng rực rỡ và ẩm thực phong phú', 'place_6926a5c903aff.jpg', '2025-11-26 06:54:40'),
(7, 'Huế', 'Cố đô với di tích lịch sử văn hóa và ẩm thực cung đình', 'place_6926a5bf99aa4.png', '2025-11-26 06:54:40'),
(8, 'Mũi Né', 'Bãi biển đẹp với đồi cát bay và các hoạt động thể thao mạo hiểm', 'place_6926a5b37ba5b.jpg', '2025-11-26 06:54:40'),
(9, 'Đồng Tháp', 'Đồng Tháp là tỉnh miền Tây Nam Bộ, nổi bật với cảnh quan sông nước, cánh đồng sen rộng lớn và các đặc sản như cá lóc, bồn bồn.', 'place_6926a7ea21d6a.jpg', '2025-11-26 07:10:34'),
(10, 'Vĩnh Long', 'Vĩnh Long là tỉnh lớn ở Đồng bằng sông Cửu Long, sở hữu thế mạnh về nông nghiệp, kinh tế biển, công nghiệp và văn hóa đa dạng với sự giao thoa của cộng đồng người Kinh – Khmer – Hoa.', 'place_6927f2d40bb1b.jpg', '2025-11-27 06:42:28');

-- --------------------------------------------------------

--
-- Table structure for table `tours`
--

CREATE TABLE `tours` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `available_seats` int(11) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `departure_date` date NOT NULL,
  `place_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tours`
--

INSERT INTO `tours` (`id`, `name`, `description`, `image`, `price`, `available_seats`, `status`, `departure_date`, `place_id`, `created_at`) VALUES
(1, 'Tour Hạ Long 2N1Đ', 'Khám phá vịnh Hạ Long với du thuyền sang trọng, tham quan hang động Thiên Cung, làng chài Cửa Vạn', 'tour_6926a744c7a2b.jpg', 2500000.00, 18, 'active', '2025-12-15', 1, '2025-11-26 06:54:40'),
(2, 'Tour Hạ Long 3N2Đ Premium', 'Trải nghiệm cao cấp trên du thuyền 5 sao, kayaking, tắm biển và BBQ tối', 'tour_6926a710e194d.jpg', 4500000.00, 15, 'active', '2025-12-20', 1, '2025-11-26 06:54:40'),
(3, 'Tour Sapa 3N2Đ', 'Chinh phục Fansipan, trekking qua bản làng dân tộc, ngắm ruộng bậc thang', 'tour_6926a765917f5.jpg', 3200000.00, 22, 'inactive', '2025-12-10', 2, '2025-11-26 06:54:40'),
(4, 'Tour Sapa - Hạ Long 5N4Đ', 'Kết hợp Sapa và Hạ Long trong một hành trình trọn vẹn', 'tour_6926a69e1e307.jpg', 6800000.00, 18, 'active', '2026-01-05', 2, '2025-11-26 06:54:40'),
(5, 'Tour Phú Quốc 4N3Đ', 'Nghỉ dưỡng tại resort 4 sao, tham quan VinWonders, Safari, câu cá', 'tour_6926a6ea9916d.jpeg', 5500000.00, 26, 'inactive', '2025-12-25', 3, '2025-11-26 06:54:40'),
(6, 'Tour Phú Quốc 3N2Đ', 'Khám phá đảo ngọc với lặn biển, tham quan dinh Cậu, chợ đêm', 'tour_6926a678ec82e.jpg', 3800000.00, 25, 'active', '2026-01-10', 3, '2025-11-26 06:54:40'),
(7, 'Tour Đà Lạt 3N2Đ', 'Tham quan thác Datanla, hồ Tuyền Lâm, làng hoa Vạn Thành, cafe view đẹp', 'tour_6926a72e1c139.jpg', 2800000.00, 21, 'inactive', '2025-12-18', 4, '2025-11-26 06:54:40'),
(8, 'Tour Đà Lạt Romantic', 'Chuyến đi lãng mạn dành cho cặp đôi với các địa điểm check-in đẹp', 'tour_6926a65473bee.jpg', 3500000.00, 16, 'active', '2026-01-15', 4, '2025-11-26 06:54:40'),
(9, 'Tour Nha Trang 3N2Đ', 'Tham quan Vinpearl Land, lặn biển ngắm san hô, tắm bùn khoáng', 'tour_6926a6fe7451c.jpg', 3200000.00, 26, 'active', '2025-12-22', 5, '2025-11-26 06:54:40'),
(10, 'Tour Nha Trang 4N3Đ', 'Tour nghỉ dưỡng cao cấp với resort 5 sao và các hoạt động thể thao biển', 'tour_6926a689a3294.jpg', 4800000.00, 20, 'inactive', '2026-01-08', 5, '2025-11-26 06:54:40'),
(11, 'Tour Hội An - Đà Nẵng 3N2Đ', 'Khám phá phố cổ Hội An, Bà Nà Hills, cầu Vàng, bãi biển Mỹ Khê', 'tour_6926a6d074b85.jpg', 3600000.00, 24, 'active', '2025-12-28', 6, '2025-11-26 06:54:40'),
(12, 'Tour Hội An 2N1Đ', 'Tham quan phố cổ, làng gốm Thanh Hà, rừng dừa Bảy Mẫu', 'tour_6926a66738ca0.jpg', 2200000.00, 18, 'active', '2026-01-12', 6, '2025-11-26 06:54:40'),
(13, 'Tour Huế 3N2Đ', 'Tham quan Đại Nội, lăng Khải Định, chùa Thiên Mụ, thưởng thức ẩm thực cung đình', 'tour_6926a6ae811d5.png', 2900000.00, 22, 'active', '2025-12-30', 7, '2025-11-26 06:54:40'),
(14, 'Tour Huế - Hội An 4N3Đ', 'Kết hợp cố đô Huế và phố cổ Hội An trong một chuyến đi', 'tour_6926a640356c4.jpg', 4200000.00, 18, 'active', '2026-01-18', 7, '2025-11-26 06:54:40'),
(15, 'Tour Mũi Né 2N1Đ', 'Khám phá đồi cát bay, suối tiên, làng chài, thưởng thức hải sản tươi sống', 'tour_6926a7572baaf.jpg', 1800000.00, 26, 'inactive', '2025-12-12', 8, '2025-11-26 06:54:40'),
(16, 'Tour Mũi Né 3N2Đ Resort', 'Nghỉ dưỡng tại resort view biển, lướt ván diều, tham quan đồi cát', 'tour_6926a62c0ea50.jpg', 3200000.00, 16, 'active', '2026-01-20', 8, '2025-11-26 06:54:40'),
(17, 'Xẻo Quýt', 'Xẻo Quýt là khu du lịch sinh thái nằm ở huyện Châu Thành, Đồng Tháp, nổi tiếng với hệ sinh thái rừng tràm ngập nước, cảnh quan thiên nhiên hoang sơ và không khí trong lành.', 'tour_6926a82964114.jpg', 1000000.00, 4, 'active', '2025-11-18', 9, '2025-11-26 07:11:37'),
(18, 'Đồng sen Tháp Mười', 'Đồng sen Tháp Mười là cánh đồng sen rộng đẹp ở Đồng Tháp, nổi tiếng với khung cảnh sen nở bát ngát và không khí miền Tây yên bình.', 'tour_6926b4ec26ab0.jpg', 1000000.00, 2, 'active', '2025-11-29', 9, '2025-11-26 08:06:04'),
(19, 'Ao Bà Om', 'Ao Bà Om là danh thắng nổi tiếng ở Trà Vinh cũ (hiện là Vĩnh Long), nổi bật với mặt nước phẳng lặng và rừng sao–dầu cổ thụ bao quanh. Đây là điểm du lịch đẹp và là nơi diễn ra lễ hội Ok Om Bok của người Khmer.', 'tour_6927f732e8516.jpg', 1000000.00, 31, 'active', '2025-11-29', 10, '2025-11-27 07:01:06'),
(20, 'Biển Ba Động', 'Biển Ba Động nằm ở huyện Duyên Hải, Trà Vinh cũ (hiện Vĩnh Long), nổi tiếng với bờ cát dài, sóng mạnh và không khí trong lành. Đây là điểm du lịch lý tưởng để tắm biển và thưởng thức hải sản.', 'tour_6927f8571cc8f.jpg', 1100000.00, 46, 'active', '2025-11-29', 10, '2025-11-27 07:05:59'),
(21, 'Bảo Tàng Văn Hóa Khmer', 'Bảo tàng Văn hóa Khmer nằm ở Trà Vinh cũ (hiện Vĩnh Long) trưng bày các hiện vật, tài liệu về văn hóa và lịch sử của người Khmer, giúp du khách hiểu thêm về phong tục, tập quán và đời sống của cộng đồng này.', 'tour_6927fb635154f.jpg', 1200000.00, 25, 'active', '2025-12-10', 10, '2025-11-27 07:18:59');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `fullname` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `fullname`, `email`, `phone`, `created_at`) VALUES
(1, 'admin', '$2y$10$snjL3BJV0YorVnmtI8lmtOjsLJoN0cAetByGbnzYENxn3xEU1wePm', 'admin', 'Admin Test', 'admin@test.com', '0900000001', '2025-11-26 06:54:40'),
(2, 'testuser', '$2y$10$aXMXllpfc4cEbFlkjuuwkeESdmxgRMFMo2MItQkkgc/i5DupDvYLS', 'user', 'Test User', 'testuser@test.com', '0900000002', '2025-11-26 06:54:40'),
(3, 'user1', '$2y$10$Sm3Qf8ibFo8TOXpOuX/gXeloUDHhwL0Rq6GQqBU8U4VgLuKHtpeJu', 'user', 'Nguyễn Văn A', 'nguyenvana@email.com', '0912345678', '2025-11-26 06:54:40'),
(4, 'user2', '$2y$10$H6PU8HnprmSh8tClUinIXOIx2ZolR2F0JnuoJFOw8ddq7HqRo8aUi', 'user', 'Trần Thị B', 'tranthib@email.com', '0923456789', '2025-11-26 06:54:40'),
(5, 'ThanhLoc', '$2y$10$bwVk7Ks4xSqBnkLPHcCOjuqWCoEgXySXg0QRtniyVD3DeHoxX0b7S', 'user', 'Nguyễn Đỗ Thành Lộc', 'loc@gmail.com', '0999999999', '2025-11-26 07:12:57'),
(6, 'HoaiBao', '$2y$10$jkdK97A/4xyxw8CdSZwXDOrxfWl46qF36fMLEbVDggcwQnsyGQUc.', 'user', 'Nguyễn Hoài Bảo', 'nhb@gmail.com', '0888888888', '2025-12-01 08:47:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_bookings_user` (`user_id`),
  ADD KEY `idx_bookings_tour` (`tour_id`),
  ADD KEY `idx_bookings_status` (`status`);

--
-- Indexes for table `places`
--
ALTER TABLE `places`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tours`
--
ALTER TABLE `tours`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tours_departure` (`departure_date`),
  ADD KEY `idx_tours_place` (`place_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `places`
--
ALTER TABLE `places`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tours`
--
ALTER TABLE `tours`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`tour_id`) REFERENCES `tours` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tours`
--
ALTER TABLE `tours`
  ADD CONSTRAINT `tours_ibfk_1` FOREIGN KEY (`place_id`) REFERENCES `places` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

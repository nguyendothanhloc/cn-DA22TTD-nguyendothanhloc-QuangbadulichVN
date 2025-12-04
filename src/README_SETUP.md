# 🚀 HƯỚNG DẪN THIẾT LẬP NHANH

## ⚡ Thiết lập trong 3 bước

### 1️⃣ Import Database
```bash
# Truy cập phpMyAdmin: http://localhost:81/phpmyadmin
# Chạy file: database.sql
```

### 2️⃣ Thiết lập mật khẩu (QUAN TRỌNG!)
```
Truy cập: http://localhost:81/setup_test_accounts.php
```
**Script này sẽ tự động tạo mật khẩu đúng cho máy của bạn!**

### 3️⃣ Kiểm tra và đăng nhập
```
Kiểm tra: http://localhost:81/verify_login.php
Đăng nhập: http://localhost:81/login.php
```

## 🔑 Tài khoản mặc định

| Username | Password | Role |
|----------|----------|------|
| admin | admin123 | Admin |
| testuser | user123 | User |
| user1 | user123 | User |
| user2 | user123 | User |

## ❓ Không đăng nhập được?

### Giải pháp 1: Chạy lại script thiết lập
```
http://localhost:81/setup_test_accounts.php
```

### Giải pháp 2: Kiểm tra trạng thái
```
http://localhost:81/verify_login.php
```

### Giải pháp 3: Xóa cache trình duyệt
- Ctrl + Shift + Delete
- Hoặc dùng chế độ ẩn danh

## 📁 Các file quan trọng

| File | Mục đích |
|------|----------|
| `database.sql` | Tạo database và bảng |
| `setup_test_accounts.php` | Thiết lập mật khẩu đúng |
| `verify_login.php` | Kiểm tra tài khoản |
| `db.php` | Cấu hình kết nối database |

## 🔧 Cấu hình Database

File: `db.php`
```php
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "travel_booking";
$port       = 3306;
```

## 💡 Tại sao cần chạy setup_test_accounts.php?

Mật khẩu hash bằng `password_hash()` có thể khác nhau giữa các:
- Phiên bản PHP khác nhau
- Hệ điều hành khác nhau
- Cấu hình server khác nhau

Script `setup_test_accounts.php` sẽ:
- ✅ Tạo hash mật khẩu phù hợp với môi trường hiện tại
- ✅ Cập nhật tất cả tài khoản test
- ✅ Đảm bảo đăng nhập hoạt động 100%

## 📝 Checklist

- [ ] XAMPP đã chạy (Apache + MySQL)
- [ ] Đã import `database.sql`
- [ ] Đã chạy `setup_test_accounts.php`
- [ ] Đã kiểm tra với `verify_login.php`
- [ ] Đã test đăng nhập thành công

## 🎯 Workflow thiết lập máy mới

```
1. Khởi động XAMPP
   ↓
2. Import database.sql
   ↓
3. Chạy setup_test_accounts.php  ← QUAN TRỌNG!
   ↓
4. Kiểm tra với verify_login.php
   ↓
5. Đăng nhập và sử dụng
```

---

**Lưu ý:** Mỗi khi chuyển sang máy mới, LUÔN chạy `setup_test_accounts.php` để đảm bảo mật khẩu hoạt động!

-- ============================================================
-- Script khởi tạo CSDL cho hệ thống QUẢN LÝ BÁN HÀNG
-- Chạy bằng:  mysql -u root -p < database.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS quanlybanhang CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE quanlybanhang;

-- ---------- Bảng tài khoản đăng nhập ----------
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- ---------- Bảng danh mục sản phẩm ----------
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ten_loai VARCHAR(100) NOT NULL
);

-- ---------- Bảng sản phẩm ----------
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ma_sp VARCHAR(20) NOT NULL UNIQUE,
    ten_sp VARCHAR(150) NOT NULL,
    category_id INT,
    gia DECIMAL(12,0) NOT NULL DEFAULT 0,
    so_luong INT NOT NULL DEFAULT 0,
    mo_ta VARCHAR(255),
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- ---------- Bảng khách hàng ----------
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ten VARCHAR(100) NOT NULL,
    sdt VARCHAR(20),
    dia_chi VARCHAR(255)
);

-- ---------- Bảng đơn hàng ----------
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    ngay_tao DATETIME DEFAULT CURRENT_TIMESTAMP,
    tong_tien DECIMAL(14,0) NOT NULL DEFAULT 0,
    trang_thai VARCHAR(30) NOT NULL DEFAULT 'Hoàn thành',
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
);

-- ---------- Bảng chi tiết đơn hàng ----------
CREATE TABLE IF NOT EXISTS order_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    so_luong INT NOT NULL,
    don_gia DECIMAL(12,0) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- ============================================================
-- DỮ LIỆU MẪU
-- ============================================================

-- Tài khoản đăng nhập mặc định: admin / admin123 (mật khẩu đã mã hoá bcrypt)
INSERT INTO users (username, password) VALUES
('admin', '$2y$10$BKuVACtnpSQLCe952DswreAU1XAlCDNuAHqqC4kwDacsVPXClPetq');

-- Danh mục
INSERT INTO categories (ten_loai) VALUES
('Đồ uống'),
('Thực phẩm'),
('Văn phòng phẩm');

-- Sản phẩm
INSERT INTO products (ma_sp, ten_sp, category_id, gia, so_luong, mo_ta) VALUES
('SP001', 'Coca Cola lon 330ml', 1, 10000, 100, 'Nước ngọt có gas'),
('SP002', 'Trà xanh không độ', 1, 9000, 80, 'Trà xanh đóng chai'),
('SP003', 'Mì tôm Hảo Hảo', 2, 4000, 200, 'Mì ăn liền vị tôm chua cay'),
('SP004', 'Bánh quy Cosy', 2, 25000, 50, 'Bánh quy hộp 200g'),
('SP005', 'Bút bi Thiên Long', 3, 5000, 150, 'Bút bi mực xanh'),
('SP006', 'Vở học sinh 96 trang', 3, 8000, 120, 'Vở kẻ ngang');

-- Khách hàng
INSERT INTO customers (ten, sdt, dia_chi) VALUES
('Nguyễn Văn An', '0901234567', 'Hà Nội'),
('Trần Thị Bình', '0912345678', 'Hải Phòng'),
('Lê Văn Cường', '0987654321', 'Nam Định');

-- Một đơn hàng mẫu để demo dashboard
INSERT INTO orders (customer_id, tong_tien, trang_thai) VALUES
(1, 38000, 'Hoàn thành');

INSERT INTO order_details (order_id, product_id, so_luong, don_gia) VALUES
(1, 1, 2, 10000),
(1, 3, 2, 4000),
(1, 5, 2, 5000);

-- (Tạo user MySQL riêng cho ứng dụng — nên dùng user này thay cho root khi deploy)
-- CREATE USER 'appuser'@'localhost' IDENTIFIED BY 'apppassword';
-- GRANT ALL PRIVILEGES ON quanlybanhang.* TO 'appuser'@'localhost';
-- FLUSH PRIVILEGES;

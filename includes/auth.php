<?php
// Kiểm tra phiên đăng nhập — gọi require_login() ở đầu các trang cần bảo vệ
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

// Định dạng tiền VNĐ dùng chung cho toàn hệ thống
function format_money($value) {
    return number_format($value, 0, ',', '.') . ' đ';
}

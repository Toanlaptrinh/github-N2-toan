<?php
// File cấu hình kết nối CSDL
// Khi đưa lên server thật, chỉnh lại 4 thông tin dưới đây cho khớp với MySQL trên server

$db_host = "localhost";
$db_user = "appuser";
$db_pass = "apppassword";
$db_name = "quanlybanhang";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Ket noi CSDL thất bại: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

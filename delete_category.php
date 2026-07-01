<?php
require_once 'includes/auth.php';
require_login();
require_once 'config/db.php';

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    // Sản phẩm thuộc danh mục này sẽ tự chuyển category_id = NULL (đã cấu hình ON DELETE SET NULL)
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

header("Location: categories.php");
exit;

<?php
require_once 'includes/auth.php';
require_login();
require_once 'config/db.php';

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    $conn->begin_transaction();
    try {
        // Hoàn lại số lượng sản phẩm vào kho trước khi xoá đơn
        $stmt = $conn->prepare("SELECT product_id, so_luong FROM order_details WHERE order_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $details = $stmt->get_result();
        $items = $details->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        foreach ($items as $it) {
            $stmt = $conn->prepare("UPDATE products SET so_luong = so_luong + ? WHERE id = ?");
            $stmt->bind_param("ii", $it['so_luong'], $it['product_id']);
            $stmt->execute();
            $stmt->close();
        }

        // Xoá đơn hàng (order_details tự xoá theo do ON DELETE CASCADE)
        $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
    }
}

header("Location: orders.php");
exit;

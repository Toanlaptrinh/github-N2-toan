<?php
require_once 'includes/auth.php';
require_login();
require_once 'config/db.php';

$page_title = "Chi tiết đơn hàng";

$id = intval($_GET['id'] ?? 0);

$stmt = $conn->prepare("
    SELECT o.*, c.ten AS ten_khach, c.sdt, c.dia_chi
    FROM orders o
    LEFT JOIN customers c ON o.customer_id = c.id
    WHERE o.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    header("Location: orders.php");
    exit;
}

$stmt = $conn->prepare("
    SELECT od.*, p.ten_sp, p.ma_sp
    FROM order_details od
    JOIN products p ON od.product_id = p.id
    WHERE od.order_id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$details = $stmt->get_result();
$stmt->close();

include 'includes/header.php';
?>

<div class="page-header">
    <h1>Đơn hàng #<?php echo $order['id']; ?></h1>
    <a href="orders.php" class="btn">← Quay lại danh sách</a>
</div>

<div class="panel">
    <p><b>Khách hàng:</b> <?php echo htmlspecialchars($order['ten_khach'] ?? 'Khách lẻ'); ?>
        <?php if ($order['sdt']): ?> — <?php echo htmlspecialchars($order['sdt']); ?><?php endif; ?></p>
    <p><b>Ngày tạo:</b> <?php echo $order['ngay_tao']; ?></p>
    <p><b>Trạng thái:</b> <span class="badge"><?php echo htmlspecialchars($order['trang_thai']); ?></span></p>

    <table class="data-table">
        <thead><tr><th>Mã SP</th><th>Sản phẩm</th><th>Đơn giá</th><th>Số lượng</th><th>Thành tiền</th></tr></thead>
        <tbody>
            <?php while ($d = $details->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($d['ma_sp']); ?></td>
                    <td><?php echo htmlspecialchars($d['ten_sp']); ?></td>
                    <td><?php echo format_money($d['don_gia']); ?></td>
                    <td><?php echo $d['so_luong']; ?></td>
                    <td><?php echo format_money($d['don_gia'] * $d['so_luong']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="cart-total">Tổng cộng: <b><?php echo format_money($order['tong_tien']); ?></b></div>
</div>

<?php include 'includes/footer.php'; ?>

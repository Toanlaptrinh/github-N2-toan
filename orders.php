<?php
require_once 'includes/auth.php';
require_login();
require_once 'config/db.php';

$page_title = "Đơn hàng";

$result = $conn->query("
    SELECT o.id, o.ngay_tao, o.tong_tien, o.trang_thai, c.ten AS ten_khach
    FROM orders o
    LEFT JOIN customers c ON o.customer_id = c.id
    ORDER BY o.id DESC
");

include 'includes/header.php';
?>

<div class="page-header">
    <h1>Danh sách đơn hàng</h1>
    <a href="add_order.php" class="btn btn-primary">+ Tạo đơn hàng mới</a>
</div>

<table class="data-table">
    <thead>
        <tr><th>Mã ĐH</th><th>Khách hàng</th><th>Ngày tạo</th><th>Tổng tiền</th><th>Trạng thái</th><th>Hành động</th></tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td>#<?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['ten_khach'] ?? 'Khách lẻ'); ?></td>
                    <td><?php echo $row['ngay_tao']; ?></td>
                    <td><?php echo format_money($row['tong_tien']); ?></td>
                    <td><span class="badge"><?php echo htmlspecialchars($row['trang_thai']); ?></span></td>
                    <td class="actions">
                        <a href="view_order.php?id=<?php echo $row['id']; ?>" class="btn btn-small">Xem</a>
                        <a href="delete_order.php?id=<?php echo $row['id']; ?>"
                           class="btn btn-small btn-danger"
                           onclick="return confirm('Xác nhận xoá đơn hàng này? Số lượng sản phẩm sẽ được hoàn lại vào kho.');">Xoá</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6" class="empty">Chưa có đơn hàng nào.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include 'includes/footer.php'; ?>

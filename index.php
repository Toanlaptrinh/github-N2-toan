<?php
require_once 'includes/auth.php';
require_login();
require_once 'config/db.php';

$page_title = "Dashboard";

// Thống kê tổng quan
$tong_doanh_thu = $conn->query("SELECT COALESCE(SUM(tong_tien),0) AS tong FROM orders")->fetch_assoc()['tong'];
$tong_don_hang  = $conn->query("SELECT COUNT(*) AS sl FROM orders")->fetch_assoc()['sl'];
$tong_sp        = $conn->query("SELECT COUNT(*) AS sl FROM products")->fetch_assoc()['sl'];
$tong_kh        = $conn->query("SELECT COUNT(*) AS sl FROM customers")->fetch_assoc()['sl'];

// Sản phẩm sắp hết hàng (dưới 10)
$sap_het = $conn->query("SELECT ten_sp, so_luong FROM products WHERE so_luong < 10 ORDER BY so_luong ASC");

// 5 đơn hàng gần nhất
$don_gan_nhat = $conn->query("
    SELECT o.id, o.ngay_tao, o.tong_tien, o.trang_thai, c.ten AS ten_khach
    FROM orders o
    LEFT JOIN customers c ON o.customer_id = c.id
    ORDER BY o.id DESC LIMIT 5
");

include 'includes/header.php';
?>

<div class="page-header">
    <h1>Tổng quan hệ thống</h1>
</div>

<div class="stat-grid">
    <div class="stat-box stat-blue">
        <div class="stat-label">Tổng doanh thu</div>
        <div class="stat-value"><?php echo format_money($tong_doanh_thu); ?></div>
    </div>
    <div class="stat-box stat-green">
        <div class="stat-label">Tổng đơn hàng</div>
        <div class="stat-value"><?php echo $tong_don_hang; ?></div>
    </div>
    <div class="stat-box stat-orange">
        <div class="stat-label">Tổng sản phẩm</div>
        <div class="stat-value"><?php echo $tong_sp; ?></div>
    </div>
    <div class="stat-box stat-purple">
        <div class="stat-label">Tổng khách hàng</div>
        <div class="stat-value"><?php echo $tong_kh; ?></div>
    </div>
</div>

<div class="grid-2">
    <div class="panel">
        <h2>Đơn hàng gần nhất</h2>
        <table class="data-table">
            <thead>
                <tr><th>Mã ĐH</th><th>Khách hàng</th><th>Ngày tạo</th><th>Tổng tiền</th><th>Trạng thái</th></tr>
            </thead>
            <tbody>
                <?php if ($don_gan_nhat->num_rows > 0): ?>
                    <?php while ($row = $don_gan_nhat->fetch_assoc()): ?>
                        <tr>
                            <td><a href="view_order.php?id=<?php echo $row['id']; ?>">#<?php echo $row['id']; ?></a></td>
                            <td><?php echo htmlspecialchars($row['ten_khach'] ?? 'Khách lẻ'); ?></td>
                            <td><?php echo $row['ngay_tao']; ?></td>
                            <td><?php echo format_money($row['tong_tien']); ?></td>
                            <td><span class="badge"><?php echo htmlspecialchars($row['trang_thai']); ?></span></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="empty">Chưa có đơn hàng nào.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="panel">
        <h2>⚠️ Sản phẩm sắp hết hàng (&lt; 10)</h2>
        <table class="data-table">
            <thead><tr><th>Sản phẩm</th><th>Còn lại</th></tr></thead>
            <tbody>
                <?php if ($sap_het->num_rows > 0): ?>
                    <?php while ($row = $sap_het->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['ten_sp']); ?></td>
                            <td><span class="badge badge-warning"><?php echo $row['so_luong']; ?></span></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="2" class="empty">Không có sản phẩm sắp hết hàng.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

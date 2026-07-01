<?php
require_once 'includes/auth.php';
require_login();
require_once 'config/db.php';

$page_title = "Khách hàng";

$keyword = trim($_GET['q'] ?? '');

if ($keyword !== '') {
    $stmt = $conn->prepare("SELECT * FROM customers WHERE ten LIKE ? OR sdt LIKE ? ORDER BY id DESC");
    $likeKeyword = "%{$keyword}%";
    $stmt->bind_param("ss", $likeKeyword, $likeKeyword);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM customers ORDER BY id DESC");
}

include 'includes/header.php';
?>

<div class="page-header">
    <h1>Danh sách khách hàng</h1>
    <a href="add_customer.php" class="btn btn-primary">+ Thêm khách hàng</a>
</div>

<form method="GET" action="customers.php" class="search-form">
    <input type="text" name="q" placeholder="Tìm theo tên hoặc số điện thoại..." value="<?php echo htmlspecialchars($keyword); ?>">
    <button type="submit" class="btn">Tìm kiếm</button>
</form>

<table class="data-table">
    <thead>
        <tr><th>ID</th><th>Họ tên</th><th>Số điện thoại</th><th>Địa chỉ</th><th>Hành động</th></tr>
    </thead>
    <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['ten']); ?></td>
                    <td><?php echo htmlspecialchars($row['sdt']); ?></td>
                    <td><?php echo htmlspecialchars($row['dia_chi']); ?></td>
                    <td class="actions">
                        <a href="edit_customer.php?id=<?php echo $row['id']; ?>" class="btn btn-small">Sửa</a>
                        <a href="delete_customer.php?id=<?php echo $row['id']; ?>"
                           class="btn btn-small btn-danger"
                           onclick="return confirm('Xác nhận xoá khách hàng này?');">Xoá</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5" class="empty">Chưa có khách hàng nào.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include 'includes/footer.php'; ?>

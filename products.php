<?php
require_once 'includes/auth.php';
require_login();
require_once 'config/db.php';

$page_title = "Sản phẩm";

$keyword = trim($_GET['q'] ?? '');

$sql = "
    SELECT p.*, c.ten_loai
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
";

if ($keyword !== '') {
    $sql .= " WHERE p.ma_sp LIKE ? OR p.ten_sp LIKE ? ";
    $sql .= " ORDER BY p.id DESC";
    $stmt = $conn->prepare($sql);
    $likeKeyword = "%{$keyword}%";
    $stmt->bind_param("ss", $likeKeyword, $likeKeyword);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql .= " ORDER BY p.id DESC";
    $result = $conn->query($sql);
}

include 'includes/header.php';
?>

<div class="page-header">
    <h1>Danh sách sản phẩm</h1>
    <a href="add_product.php" class="btn btn-primary">+ Thêm sản phẩm</a>
</div>

<form method="GET" action="products.php" class="search-form">
    <input type="text" name="q" placeholder="Tìm theo mã SP hoặc tên sản phẩm..." value="<?php echo htmlspecialchars($keyword); ?>">
    <button type="submit" class="btn">Tìm kiếm</button>
</form>

<table class="data-table">
    <thead>
        <tr>
            <th>Mã SP</th>
            <th>Tên sản phẩm</th>
            <th>Danh mục</th>
            <th>Giá bán</th>
            <th>Tồn kho</th>
            <th>Hành động</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['ma_sp']); ?></td>
                    <td><?php echo htmlspecialchars($row['ten_sp']); ?></td>
                    <td><?php echo htmlspecialchars($row['ten_loai'] ?? 'Chưa phân loại'); ?></td>
                    <td><?php echo format_money($row['gia']); ?></td>
                    <td>
                        <?php if ($row['so_luong'] < 10): ?>
                            <span class="badge badge-warning"><?php echo $row['so_luong']; ?></span>
                        <?php else: ?>
                            <?php echo $row['so_luong']; ?>
                        <?php endif; ?>
                    </td>
                    <td class="actions">
                        <a href="edit_product.php?id=<?php echo $row['id']; ?>" class="btn btn-small">Sửa</a>
                        <a href="delete_product.php?id=<?php echo $row['id']; ?>"
                           class="btn btn-small btn-danger"
                           onclick="return confirm('Xác nhận xoá sản phẩm này?');">Xoá</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6" class="empty">Chưa có sản phẩm nào.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include 'includes/footer.php'; ?>

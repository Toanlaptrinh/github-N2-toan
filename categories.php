<?php
require_once 'includes/auth.php';
require_login();
require_once 'config/db.php';

$page_title = "Danh mục sản phẩm";

$result = $conn->query("
    SELECT c.id, c.ten_loai, COUNT(p.id) AS so_sp
    FROM categories c
    LEFT JOIN products p ON p.category_id = c.id
    GROUP BY c.id, c.ten_loai
    ORDER BY c.id DESC
");

include 'includes/header.php';
?>

<div class="page-header">
    <h1>Danh mục sản phẩm</h1>
    <a href="add_category.php" class="btn btn-primary">+ Thêm danh mục</a>
</div>

<table class="data-table">
    <thead>
        <tr><th>ID</th><th>Tên danh mục</th><th>Số sản phẩm</th><th>Hành động</th></tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['ten_loai']); ?></td>
                    <td><?php echo $row['so_sp']; ?></td>
                    <td class="actions">
                        <a href="edit_category.php?id=<?php echo $row['id']; ?>" class="btn btn-small">Sửa</a>
                        <a href="delete_category.php?id=<?php echo $row['id']; ?>"
                           class="btn btn-small btn-danger"
                           onclick="return confirm('Xác nhận xoá danh mục này? Sản phẩm thuộc danh mục sẽ chuyển về không phân loại.');">Xoá</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4" class="empty">Chưa có danh mục nào.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include 'includes/footer.php'; ?>

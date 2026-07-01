<?php
require_once 'includes/auth.php';
require_login();
require_once 'config/db.php';

$page_title = "Thêm sản phẩm";
$error = "";

$categories = $conn->query("SELECT id, ten_loai FROM categories ORDER BY ten_loai ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ma_sp       = trim($_POST['ma_sp'] ?? '');
    $ten_sp      = trim($_POST['ten_sp'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $gia         = $_POST['gia'] ?? 0;
    $so_luong    = intval($_POST['so_luong'] ?? 0);
    $mo_ta       = trim($_POST['mo_ta'] ?? '');

    if ($ma_sp === '' || $ten_sp === '') {
        $error = "Vui lòng nhập đầy đủ Mã SP và Tên sản phẩm.";
    } else {
        $stmt = $conn->prepare("INSERT INTO products (ma_sp, ten_sp, category_id, gia, so_luong, mo_ta) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiids", $ma_sp, $ten_sp, $category_id, $gia, $so_luong, $mo_ta);
        if ($stmt->execute()) {
            header("Location: products.php");
            exit;
        } else {
            $error = (str_contains($stmt->error, 'Duplicate')) ? "Mã sản phẩm đã tồn tại." : "Lỗi khi lưu dữ liệu: " . $stmt->error;
        }
        $stmt->close();
    }
}

include 'includes/header.php';
?>

<div class="page-header">
    <h1>Thêm sản phẩm mới</h1>
</div>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="POST" action="add_product.php" class="data-form">
    <label>Mã sản phẩm</label>
    <input type="text" name="ma_sp" required value="<?php echo htmlspecialchars($_POST['ma_sp'] ?? ''); ?>">

    <label>Tên sản phẩm</label>
    <input type="text" name="ten_sp" required value="<?php echo htmlspecialchars($_POST['ten_sp'] ?? ''); ?>">

    <label>Danh mục</label>
    <select name="category_id">
        <option value="0">-- Chưa phân loại --</option>
        <?php while ($c = $categories->fetch_assoc()): ?>
            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['ten_loai']); ?></option>
        <?php endwhile; ?>
    </select>

    <label>Giá bán (đ)</label>
    <input type="number" step="1" min="0" name="gia" required value="<?php echo htmlspecialchars($_POST['gia'] ?? '0'); ?>">

    <label>Số lượng trong kho</label>
    <input type="number" step="1" min="0" name="so_luong" required value="<?php echo htmlspecialchars($_POST['so_luong'] ?? '0'); ?>">

    <label>Mô tả</label>
    <input type="text" name="mo_ta" value="<?php echo htmlspecialchars($_POST['mo_ta'] ?? ''); ?>">

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Lưu</button>
        <a href="products.php" class="btn">Hủy</a>
    </div>
</form>

<?php include 'includes/footer.php'; ?>

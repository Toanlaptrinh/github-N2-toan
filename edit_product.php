<?php
require_once 'includes/auth.php';
require_login();
require_once 'config/db.php';

$page_title = "Sửa sản phẩm";
$error = "";

$id = intval($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    header("Location: products.php");
    exit;
}

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
        $stmt = $conn->prepare("UPDATE products SET ma_sp=?, ten_sp=?, category_id=?, gia=?, so_luong=?, mo_ta=? WHERE id=?");
        $stmt->bind_param("ssiidsi", $ma_sp, $ten_sp, $category_id, $gia, $so_luong, $mo_ta, $id);
        if ($stmt->execute()) {
            header("Location: products.php");
            exit;
        } else {
            $error = (str_contains($stmt->error, 'Duplicate')) ? "Mã sản phẩm đã tồn tại." : "Lỗi khi lưu dữ liệu: " . $stmt->error;
        }
        $stmt->close();
    }
    $product = ['id'=>$id,'ma_sp'=>$ma_sp,'ten_sp'=>$ten_sp,'category_id'=>$category_id,'gia'=>$gia,'so_luong'=>$so_luong,'mo_ta'=>$mo_ta];
}

include 'includes/header.php';
?>

<div class="page-header">
    <h1>Sửa sản phẩm</h1>
</div>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="POST" action="edit_product.php?id=<?php echo $product['id']; ?>" class="data-form">
    <label>Mã sản phẩm</label>
    <input type="text" name="ma_sp" required value="<?php echo htmlspecialchars($product['ma_sp']); ?>">

    <label>Tên sản phẩm</label>
    <input type="text" name="ten_sp" required value="<?php echo htmlspecialchars($product['ten_sp']); ?>">

    <label>Danh mục</label>
    <select name="category_id">
        <option value="0">-- Chưa phân loại --</option>
        <?php while ($c = $categories->fetch_assoc()): ?>
            <option value="<?php echo $c['id']; ?>" <?php echo ($c['id'] == $product['category_id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($c['ten_loai']); ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label>Giá bán (đ)</label>
    <input type="number" step="1" min="0" name="gia" required value="<?php echo htmlspecialchars($product['gia']); ?>">

    <label>Số lượng trong kho</label>
    <input type="number" step="1" min="0" name="so_luong" required value="<?php echo htmlspecialchars($product['so_luong']); ?>">

    <label>Mô tả</label>
    <input type="text" name="mo_ta" value="<?php echo htmlspecialchars($product['mo_ta'] ?? ''); ?>">

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
        <a href="products.php" class="btn">Hủy</a>
    </div>
</form>

<?php include 'includes/footer.php'; ?>

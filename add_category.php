<?php
require_once 'includes/auth.php';
require_login();
require_once 'config/db.php';

$page_title = "Thêm danh mục";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten_loai = trim($_POST['ten_loai'] ?? '');

    if ($ten_loai === '') {
        $error = "Vui lòng nhập tên danh mục.";
    } else {
        $stmt = $conn->prepare("INSERT INTO categories (ten_loai) VALUES (?)");
        $stmt->bind_param("s", $ten_loai);
        if ($stmt->execute()) {
            header("Location: categories.php");
            exit;
        } else {
            $error = "Lỗi khi lưu dữ liệu: " . $stmt->error;
        }
        $stmt->close();
    }
}

include 'includes/header.php';
?>

<div class="page-header">
    <h1>Thêm danh mục mới</h1>
</div>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="POST" action="add_category.php" class="data-form">
    <label>Tên danh mục</label>
    <input type="text" name="ten_loai" required value="<?php echo htmlspecialchars($_POST['ten_loai'] ?? ''); ?>">

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Lưu</button>
        <a href="categories.php" class="btn">Hủy</a>
    </div>
</form>

<?php include 'includes/footer.php'; ?>

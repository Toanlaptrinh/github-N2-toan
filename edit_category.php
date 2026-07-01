<?php
require_once 'includes/auth.php';
require_login();
require_once 'config/db.php';

$page_title = "Sửa danh mục";
$error = "";

$id = intval($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$category = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$category) {
    header("Location: categories.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten_loai = trim($_POST['ten_loai'] ?? '');

    if ($ten_loai === '') {
        $error = "Vui lòng nhập tên danh mục.";
    } else {
        $stmt = $conn->prepare("UPDATE categories SET ten_loai = ? WHERE id = ?");
        $stmt->bind_param("si", $ten_loai, $id);
        if ($stmt->execute()) {
            header("Location: categories.php");
            exit;
        } else {
            $error = "Lỗi khi lưu dữ liệu: " . $stmt->error;
        }
        $stmt->close();
    }
    $category['ten_loai'] = $ten_loai;
}

include 'includes/header.php';
?>

<div class="page-header">
    <h1>Sửa danh mục</h1>
</div>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="POST" action="edit_category.php?id=<?php echo $category['id']; ?>" class="data-form">
    <label>Tên danh mục</label>
    <input type="text" name="ten_loai" required value="<?php echo htmlspecialchars($category['ten_loai']); ?>">

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
        <a href="categories.php" class="btn">Hủy</a>
    </div>
</form>

<?php include 'includes/footer.php'; ?>

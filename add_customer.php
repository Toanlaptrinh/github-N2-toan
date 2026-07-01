<?php
require_once 'includes/auth.php';
require_login();
require_once 'config/db.php';

$page_title = "Thêm khách hàng";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten     = trim($_POST['ten'] ?? '');
    $sdt     = trim($_POST['sdt'] ?? '');
    $dia_chi = trim($_POST['dia_chi'] ?? '');

    if ($ten === '') {
        $error = "Vui lòng nhập họ tên khách hàng.";
    } else {
        $stmt = $conn->prepare("INSERT INTO customers (ten, sdt, dia_chi) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $ten, $sdt, $dia_chi);
        if ($stmt->execute()) {
            header("Location: customers.php");
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
    <h1>Thêm khách hàng mới</h1>
</div>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="POST" action="add_customer.php" class="data-form">
    <label>Họ và tên</label>
    <input type="text" name="ten" required value="<?php echo htmlspecialchars($_POST['ten'] ?? ''); ?>">

    <label>Số điện thoại</label>
    <input type="text" name="sdt" value="<?php echo htmlspecialchars($_POST['sdt'] ?? ''); ?>">

    <label>Địa chỉ</label>
    <input type="text" name="dia_chi" value="<?php echo htmlspecialchars($_POST['dia_chi'] ?? ''); ?>">

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Lưu</button>
        <a href="customers.php" class="btn">Hủy</a>
    </div>
</form>

<?php include 'includes/footer.php'; ?>

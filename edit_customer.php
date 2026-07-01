<?php
require_once 'includes/auth.php';
require_login();
require_once 'config/db.php';

$page_title = "Sửa khách hàng";
$error = "";

$id = intval($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$customer) {
    header("Location: customers.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten     = trim($_POST['ten'] ?? '');
    $sdt     = trim($_POST['sdt'] ?? '');
    $dia_chi = trim($_POST['dia_chi'] ?? '');

    if ($ten === '') {
        $error = "Vui lòng nhập họ tên khách hàng.";
    } else {
        $stmt = $conn->prepare("UPDATE customers SET ten=?, sdt=?, dia_chi=? WHERE id=?");
        $stmt->bind_param("sssi", $ten, $sdt, $dia_chi, $id);
        if ($stmt->execute()) {
            header("Location: customers.php");
            exit;
        } else {
            $error = "Lỗi khi lưu dữ liệu: " . $stmt->error;
        }
        $stmt->close();
    }
    $customer = ['id'=>$id,'ten'=>$ten,'sdt'=>$sdt,'dia_chi'=>$dia_chi];
}

include 'includes/header.php';
?>

<div class="page-header">
    <h1>Sửa thông tin khách hàng</h1>
</div>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="POST" action="edit_customer.php?id=<?php echo $customer['id']; ?>" class="data-form">
    <label>Họ và tên</label>
    <input type="text" name="ten" required value="<?php echo htmlspecialchars($customer['ten']); ?>">

    <label>Số điện thoại</label>
    <input type="text" name="sdt" value="<?php echo htmlspecialchars($customer['sdt']); ?>">

    <label>Địa chỉ</label>
    <input type="text" name="dia_chi" value="<?php echo htmlspecialchars($customer['dia_chi']); ?>">

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
        <a href="customers.php" class="btn">Hủy</a>
    </div>
</form>

<?php include 'includes/footer.php'; ?>

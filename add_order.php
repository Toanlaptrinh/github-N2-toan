<?php
require_once 'includes/auth.php';
require_login();
require_once 'config/db.php';

$page_title = "Tạo đơn hàng";
$error = "";
$success = "";

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = []; // [product_id => qty]
}
if (!isset($_SESSION['order_customer_id'])) {
    $_SESSION['order_customer_id'] = 0; // 0 = Khách lẻ
}

// ---------- Xử lý các hành động (chọn khách hàng / thêm vào giỏ / xoá khỏi giỏ / xoá toàn bộ giỏ / thanh toán) ----------

// Chọn khách hàng cho đơn hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'set_customer') {
    $_SESSION['order_customer_id'] = intval($_POST['customer_id'] ?? 0);
    header("Location: add_order.php");
    exit;
}

// Thêm sản phẩm vào giỏ hàng tạm
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_item') {
    $product_id = intval($_POST['product_id'] ?? 0);
    $qty        = intval($_POST['qty'] ?? 0);

    if ($product_id > 0 && $qty > 0) {
        $stmt = $conn->prepare("SELECT so_luong FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $p = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($p) {
            $current_in_cart = $_SESSION['cart'][$product_id] ?? 0;
            $new_qty = $current_in_cart + $qty;
            if ($new_qty > $p['so_luong']) {
                $error = "Số lượng vượt quá hàng trong kho (còn {$p['so_luong']}).";
            } else {
                $_SESSION['cart'][$product_id] = $new_qty;
            }
        }
    }
}

// Xoá 1 sản phẩm khỏi giỏ hàng
if (isset($_GET['remove'])) {
    $rid = intval($_GET['remove']);
    unset($_SESSION['cart'][$rid]);
    header("Location: add_order.php");
    exit;
}

// Xoá toàn bộ giỏ hàng
if (isset($_GET['clear'])) {
    $_SESSION['cart'] = [];
    $_SESSION['order_customer_id'] = 0;
    header("Location: add_order.php");
    exit;
}

// Thanh toán / Tạo đơn hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'checkout') {
    if (empty($_SESSION['cart'])) {
        $error = "Giỏ hàng đang trống, vui lòng thêm sản phẩm trước khi tạo đơn.";
    } else {
        $conn->begin_transaction();
        try {
            $customer_id = $_SESSION['order_customer_id'] > 0 ? $_SESSION['order_customer_id'] : null;
            $tong_tien = 0;
            $items = [];

            foreach ($_SESSION['cart'] as $pid => $qty) {
                $stmt = $conn->prepare("SELECT id, ten_sp, gia, so_luong FROM products WHERE id = ? FOR UPDATE");
                $stmt->bind_param("i", $pid);
                $stmt->execute();
                $p = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if (!$p) continue;
                if ($qty > $p['so_luong']) {
                    throw new Exception("Sản phẩm \"{$p['ten_sp']}\" không đủ hàng trong kho.");
                }
                $tong_tien += $qty * $p['gia'];
                $items[] = ['id' => $p['id'], 'gia' => $p['gia'], 'qty' => $qty];
            }

            $stmt = $conn->prepare("INSERT INTO orders (customer_id, tong_tien, trang_thai) VALUES (?, ?, 'Hoàn thành')");
            $stmt->bind_param("id", $customer_id, $tong_tien);
            $stmt->execute();
            $order_id = $conn->insert_id;
            $stmt->close();

            foreach ($items as $it) {
                $stmt = $conn->prepare("INSERT INTO order_details (order_id, product_id, so_luong, don_gia) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiid", $order_id, $it['id'], $it['qty'], $it['gia']);
                $stmt->execute();
                $stmt->close();

                $stmt = $conn->prepare("UPDATE products SET so_luong = so_luong - ? WHERE id = ?");
                $stmt->bind_param("ii", $it['qty'], $it['id']);
                $stmt->execute();
                $stmt->close();
            }

            $conn->commit();
            $_SESSION['cart'] = [];
            $_SESSION['order_customer_id'] = 0;

            header("Location: view_order.php?id=" . $order_id);
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Không thể tạo đơn hàng: " . $e->getMessage();
        }
    }
}

// ---------- Lấy dữ liệu hiển thị ----------
$customers = $conn->query("SELECT id, ten FROM customers ORDER BY ten ASC");
$products  = $conn->query("SELECT id, ma_sp, ten_sp, gia, so_luong FROM products ORDER BY ten_sp ASC");

$cart_items = [];
$cart_total = 0;
if (!empty($_SESSION['cart'])) {
    $ids = array_map('intval', array_keys($_SESSION['cart']));
    $in = implode(',', $ids);
    $res = $conn->query("SELECT id, ten_sp, gia FROM products WHERE id IN ($in)");
    while ($row = $res->fetch_assoc()) {
        $qty = $_SESSION['cart'][$row['id']];
        $thanh_tien = $qty * $row['gia'];
        $cart_total += $thanh_tien;
        $cart_items[] = [
            'id' => $row['id'], 'ten_sp' => $row['ten_sp'], 'gia' => $row['gia'],
            'qty' => $qty, 'thanh_tien' => $thanh_tien
        ];
    }
}

$selected_customer_name = "Khách lẻ";
if ($_SESSION['order_customer_id'] > 0) {
    $stmt = $conn->prepare("SELECT ten FROM customers WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['order_customer_id']);
    $stmt->execute();
    $c = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($c) $selected_customer_name = $c['ten'];
}

include 'includes/header.php';
?>

<div class="page-header">
    <h1>Tạo đơn hàng mới</h1>
</div>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="grid-2">
    <div class="panel">
        <h2>1. Chọn khách hàng</h2>
        <form method="POST" action="add_order.php" class="inline-form">
            <input type="hidden" name="action" value="set_customer">
            <select name="customer_id" onchange="this.form.submit()">
                <option value="0" <?php echo $_SESSION['order_customer_id'] == 0 ? 'selected' : ''; ?>>-- Khách lẻ --</option>
                <?php while ($c = $customers->fetch_assoc()): ?>
                    <option value="<?php echo $c['id']; ?>" <?php echo ($c['id'] == $_SESSION['order_customer_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($c['ten']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </form>
        <p class="hint">Khách hàng đã chọn: <b><?php echo htmlspecialchars($selected_customer_name); ?></b>
            &nbsp;(<a href="add_customer.php">+ thêm khách hàng mới</a>)</p>

        <h2>2. Thêm sản phẩm vào đơn</h2>
        <table class="data-table">
            <thead><tr><th>Sản phẩm</th><th>Giá</th><th>Còn lại</th><th>SL</th><th></th></tr></thead>
            <tbody>
                <?php while ($p = $products->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($p['ten_sp']); ?></td>
                        <td><?php echo format_money($p['gia']); ?></td>
                        <td><?php echo $p['so_luong']; ?></td>
                        <td>
                            <form method="POST" action="add_order.php" class="inline-form">
                                <input type="hidden" name="action" value="add_item">
                                <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                                <input type="number" name="qty" value="1" min="1" max="<?php echo $p['so_luong']; ?>" class="qty-input">
                        </td>
                        <td>
                                <button type="submit" class="btn btn-small btn-primary" <?php echo $p['so_luong'] <= 0 ? 'disabled' : ''; ?>>Thêm</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="panel">
        <h2>3. Giỏ hàng tạm</h2>
        <table class="data-table">
            <thead><tr><th>Sản phẩm</th><th>SL</th><th>Thành tiền</th><th></th></tr></thead>
            <tbody>
                <?php if (count($cart_items) > 0): ?>
                    <?php foreach ($cart_items as $it): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($it['ten_sp']); ?></td>
                            <td><?php echo $it['qty']; ?></td>
                            <td><?php echo format_money($it['thanh_tien']); ?></td>
                            <td><a href="add_order.php?remove=<?php echo $it['id']; ?>" class="btn btn-small btn-danger">Xoá</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="empty">Giỏ hàng đang trống.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="cart-total">Tổng cộng: <b><?php echo format_money($cart_total); ?></b></div>

        <form method="POST" action="add_order.php" class="form-actions">
            <input type="hidden" name="action" value="checkout">
            <button type="submit" class="btn btn-primary" <?php echo count($cart_items) === 0 ? 'disabled' : ''; ?>>✅ Tạo đơn hàng</button>
            <a href="add_order.php?clear=1" class="btn btn-danger">Xoá giỏ hàng</a>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

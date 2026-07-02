<?php
$current = basename($_SERVER['PHP_SELF']);
function nav_active($files, $current) {
    return in_array($current, $files) ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . " - Quản lý bán hàng" : "Quản lý bán hàng"; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header class="topbar">
    <div class="topbar-title">🛒 Hệ thống Quản lý Bán hàng</div>
    <div class="topbar-user">
        Xin chào, <b><?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></b>
        &nbsp;|&nbsp; <a href="logout.php">Đăng xuất</a>
    </div>
</header>

<nav class="navmenu">
    <a href="index.php" class="<?php echo nav_active(['index.php'], $current); ?>">📊 Dashboard</a>
    <a href="products.php" class="<?php echo nav_active(['products.php', 'add_product.php', 'edit_product.php'], $current); ?>">📦 Sản phẩm</a>
    <a href="categories.php" class="<?php echo nav_active(['categories.php', 'add_category.php', 'edit_category.php'], $current); ?>">🏷️ Danh mục</a>
    <a href="customers.php" class="<?php echo nav_active(['customers.php', 'add_customer.php', 'edit_customer.php'], $current); ?>">👤 Khách hàng</a>
    <a href="orders.php" class="<?php echo nav_active(['orders.php', 'add_order.php', 'view_order.php'], $current); ?>">🧾 Đơn hàng</a>
</nav>

<main class="container">

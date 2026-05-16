<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    set_flash('error', 'Order not found.');
    redirect('menu.php');
}

$stmt = $conn->prepare(
    "SELECT * FROM orders WHERE id = ? AND user_id = ?"
);
$stmt->bind_param('ii', $id, $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    set_flash('error', 'Order not found.');
    redirect('menu.php');
}

$stmt = $conn->prepare(
    "SELECT oi.quantity, oi.price, m.name, c.name AS category_name
     FROM order_items oi
     JOIN menu_items m ON m.id = oi.menu_item_id
     JOIN categories c ON c.id = m.category_id
     WHERE oi.order_id = ?"
);
$stmt->bind_param('i', $id);
$stmt->execute();
$lines = $stmt->get_result();

$page_title = 'Order Summary - Bella Cucina';
include __DIR__ . '/includes/header.php';
?>

<div class="toolbar">
    <h1 style="margin:0;">Order #<?= (int)$order['id'] ?></h1>
    <span class="status status-<?= e($order['status']) ?>">
        <?= e($order['status']) ?>
    </span>
</div>

<p class="text-muted">Placed on <?= e($order['created_at']) ?></p>

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Category</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($l = $lines->fetch_assoc()): ?>
                <tr>
                    <td><strong><?= e($l['name']) ?></strong></td>
                    <td><?= e($l['category_name']) ?></td>
                    <td><?= (int)$l['quantity'] ?></td>
                    <td><?= e(price($l['price'])) ?></td>
                    <td><strong>
                        <?= e(price((float)$l['price'] * (int)$l['quantity'])) ?>
                    </strong></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div class="cart-summary">
    <a class="btn btn-outline" href="<?= e(base_url('menu.php')) ?>">Back to menu</a>
    <div class="total">Total: <?= e(price($order['total_price'])) ?></div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

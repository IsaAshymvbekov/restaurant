<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();

if (isset($_POST['action'], $_POST['id'])) {
    $oid    = (int)$_POST['id'];
    $action = $_POST['action'];

    $allowed = [
        'pending'   => 'pending',
        'preparing' => 'preparing',
        'completed' => 'completed',
        'cancelled' => 'cancelled',
    ];
    if (isset($allowed[$action])) {
        $new = $allowed[$action];
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param('si', $new, $oid);
        $stmt->execute();
        $stmt->close();
        set_flash('success', 'Order status updated.');
        redirect('orders.php');
    }

    if ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->bind_param('i', $oid);
        $stmt->execute();
        $stmt->close();
        set_flash('success', 'Order deleted.');
        redirect('orders.php');
    }
}

$rows = $conn->query(
    "SELECT o.*, u.full_name, u.email,
            (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) AS line_count
     FROM orders o
     JOIN users u ON u.id = o.user_id
     ORDER BY o.created_at DESC"
);

$page_title = 'Orders - Admin';
$current = 'orders';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/admin_nav.php';
?>

<h1>Orders</h1>

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Customer</th>
                <th>Email</th>
                <th>Items</th>
                <th>Total</th>
                <th>Created</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($rows->num_rows === 0): ?>
            <tr><td colspan="8" class="text-muted text-center">No orders yet.</td></tr>
        <?php else: while ($o = $rows->fetch_assoc()): ?>
            <tr>
                <td>#<?= (int)$o['id'] ?></td>
                <td><?= e($o['full_name']) ?></td>
                <td><?= e($o['email']) ?></td>
                <td><?= (int)$o['line_count'] ?></td>
                <td><strong><?= e(price($o['total_price'])) ?></strong></td>
                <td><?= e($o['created_at']) ?></td>
                <td><span class="status status-<?= e($o['status']) ?>"><?= e($o['status']) ?></span></td>
                <td class="actions">
                    <form method="post" class="inline-form">
                        <input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
                        <select name="action" onchange="this.form.submit()">
                            <option value="">Set status...</option>
                            <option value="pending">pending</option>
                            <option value="preparing">preparing</option>
                            <option value="completed">completed</option>
                            <option value="cancelled">cancelled</option>
                        </select>
                    </form>
                    <form method="post" class="inline-form">
                        <input type="hidden" name="id"     value="<?= (int)$o['id'] ?>">
                        <input type="hidden" name="action" value="delete">
                        <button class="btn btn-danger btn-sm" type="submit"
                                data-confirm="Delete this order permanently?">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

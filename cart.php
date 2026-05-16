<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

if (is_admin()) {
    set_flash('info', 'Admins do not place customer orders.');
    redirect('admin/dashboard.php');
}

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action  = $_POST['action']  ?? '';
    $item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;

    if ($action === 'add' && $item_id > 0) {
        $stmt = $conn->prepare(
            "SELECT id FROM menu_items WHERE id = ? AND available = 1"
        );
        $stmt->bind_param('i', $item_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $_SESSION['cart'][$item_id] = ($_SESSION['cart'][$item_id] ?? 0) + 1;
            set_flash('success', 'Item added to your order.');
        } else {
            set_flash('error', 'That item is not available.');
        }
        $stmt->close();
        redirect('cart.php');
    }

    if ($action === 'update' && $item_id > 0) {
        $qty = isset($_POST['qty']) ? max(0, (int)$_POST['qty']) : 0;
        if ($qty <= 0) {
            unset($_SESSION['cart'][$item_id]);
        } else {
            $_SESSION['cart'][$item_id] = $qty;
        }
        set_flash('success', 'Order updated.');
        redirect('cart.php');
    }

    if ($action === 'remove' && $item_id > 0) {
        unset($_SESSION['cart'][$item_id]);
        set_flash('success', 'Item removed from your order.');
        redirect('cart.php');
    }

    if ($action === 'clear') {
        $_SESSION['cart'] = [];
        set_flash('success', 'Order cleared.');
        redirect('cart.php');
    }

    if ($action === 'checkout') {
        if (empty($_SESSION['cart'])) {
            set_flash('error', 'Your order is empty.');
            redirect('cart.php');
        }

        // Build items list with current prices and verify availability.
        $ids = implode(',', array_map('intval', array_keys($_SESSION['cart'])));
        $items = [];
        $total = 0.0;
        $res = $conn->query("SELECT id, name, price, available FROM menu_items WHERE id IN ($ids)");
        while ($row = $res->fetch_assoc()) {
            if ((int)$row['available'] !== 1) continue;
            $qty = (int)($_SESSION['cart'][$row['id']] ?? 0);
            if ($qty <= 0) continue;
            $items[] = [
                'id'    => (int)$row['id'],
                'price' => (float)$row['price'],
                'qty'   => $qty,
            ];
            $total += (float)$row['price'] * $qty;
        }

        if (empty($items)) {
            set_flash('error', 'No valid items in your order.');
            redirect('cart.php');
        }

        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare(
                "INSERT INTO orders (user_id, total_price) VALUES (?, ?)"
            );
            $stmt->bind_param('id', $_SESSION['user_id'], $total);
            $stmt->execute();
            $order_id = $stmt->insert_id;
            $stmt->close();

            $stmt = $conn->prepare(
                "INSERT INTO order_items (order_id, menu_item_id, quantity, price)
                 VALUES (?, ?, ?, ?)"
            );
            foreach ($items as $line) {
                $stmt->bind_param('iiid',
                    $order_id, $line['id'], $line['qty'], $line['price']);
                $stmt->execute();
            }
            $stmt->close();

            $conn->commit();
            $_SESSION['cart'] = [];
            set_flash('success', 'Order placed! Thank you.');
            redirect('order_summary.php?id=' . $order_id);
        } catch (Throwable $t) {
            $conn->rollback();
            set_flash('error', 'Could not place the order. Please try again.');
            redirect('cart.php');
        }
    }
}

// GET - render the cart.
$lines = [];
$total = 0.0;
if (!empty($_SESSION['cart'])) {
    $ids = implode(',', array_map('intval', array_keys($_SESSION['cart'])));
    $res = $conn->query(
        "SELECT m.id, m.name, m.price, m.available, c.name AS category_name
         FROM menu_items m
         JOIN categories c ON c.id = m.category_id
         WHERE m.id IN ($ids)"
    );
    while ($row = $res->fetch_assoc()) {
        $qty = (int)($_SESSION['cart'][$row['id']] ?? 0);
        $sub = $qty * (float)$row['price'];
        $total += $sub;
        $lines[] = [
            'id'            => (int)$row['id'],
            'name'          => $row['name'],
            'category_name' => $row['category_name'],
            'price'         => (float)$row['price'],
            'available'     => (int)$row['available'],
            'qty'           => $qty,
            'subtotal'      => $sub,
        ];
    }
}

$page_title = 'Your Order - Bella Cucina';
include __DIR__ . '/includes/header.php';
?>

<div class="toolbar">
    <h1 style="margin:0;">Your Order</h1>
    <a class="btn btn-outline" href="<?= e(base_url('menu.php')) ?>">+ Add more items</a>
</div>

<?php if (empty($lines)): ?>
    <div class="empty-state">
        <h3>Your order is empty</h3>
        <p>Browse the menu and add some delicious items.</p>
        <a class="btn btn-primary mt-2" href="<?= e(base_url('menu.php')) ?>">Open Menu</a>
    </div>
<?php else: ?>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lines as $line): ?>
                    <tr>
                        <td><strong><?= e($line['name']) ?></strong></td>
                        <td><?= e($line['category_name']) ?></td>
                        <td><?= e(price($line['price'])) ?></td>
                        <td>
                            <form method="post" class="qty-form">
                                <input type="hidden" name="action"  value="update">
                                <input type="hidden" name="item_id" value="<?= (int)$line['id'] ?>">
                                <input type="number" name="qty" min="0" max="50"
                                       value="<?= (int)$line['qty'] ?>">
                                <button class="btn btn-outline btn-sm" type="submit">Update</button>
                            </form>
                        </td>
                        <td><strong><?= e(price($line['subtotal'])) ?></strong></td>
                        <td>
                            <form method="post" class="inline-form">
                                <input type="hidden" name="action"  value="remove">
                                <input type="hidden" name="item_id" value="<?= (int)$line['id'] ?>">
                                <button class="btn btn-danger btn-sm" type="submit"
                                        data-confirm="Remove this item?">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="cart-summary">
        <div>
            <form method="post" class="inline-form">
                <input type="hidden" name="action" value="clear">
                <button class="btn btn-outline btn-sm" type="submit"
                        data-confirm="Clear all items from your order?">Clear order</button>
            </form>
        </div>
        <div class="total">Total: <?= e(price($total)) ?></div>
        <form method="post" class="inline-form">
            <input type="hidden" name="action" value="checkout">
            <button class="btn btn-primary" type="submit">Place Order</button>
        </form>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>

<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();

// Handle delete via GET ?delete=id
if (isset($_GET['delete'])) {
    $del = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM menu_items WHERE id = ?");
    $stmt->bind_param('i', $del);
    $stmt->execute();
    $stmt->close();
    set_flash('success', 'Menu item deleted.');
    redirect('menu_items.php');
}

// Handle availability toggle
if (isset($_GET['toggle'])) {
    $tid = (int)$_GET['toggle'];
    $conn->query("UPDATE menu_items SET available = 1 - available WHERE id = $tid");
    set_flash('success', 'Availability updated.');
    redirect('menu_items.php');
}

$items = $conn->query(
    "SELECT m.*, c.name AS category_name
     FROM menu_items m
     JOIN categories c ON c.id = m.category_id
     ORDER BY c.name, m.name"
);

$page_title = 'Menu Items - Admin';
$current = 'menu_items';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/admin_nav.php';
?>

<div class="toolbar">
    <h1 style="margin:0;">Menu Items</h1>
    <a class="btn btn-primary" href="add_item.php">+ Add Item</a>
</div>

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Available</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($items->num_rows === 0): ?>
            <tr><td colspan="6" class="text-muted text-center">No menu items yet.</td></tr>
        <?php else: while ($it = $items->fetch_assoc()): ?>
            <tr>
                <td><?= (int)$it['id'] ?></td>
                <td><strong><?= e($it['name']) ?></strong></td>
                <td><?= e($it['category_name']) ?></td>
                <td><?= e(price($it['price'])) ?></td>
                <td>
                    <?php if ((int)$it['available'] === 1): ?>
                        <span class="status status-confirmed">Yes</span>
                    <?php else: ?>
                        <span class="status status-cancelled">No</span>
                    <?php endif; ?>
                </td>
                <td class="actions">
                    <a class="btn btn-outline btn-sm"
                       href="edit_item.php?id=<?= (int)$it['id'] ?>">Edit</a>
                    <a class="btn btn-outline btn-sm"
                       href="?toggle=<?= (int)$it['id'] ?>">Toggle</a>
                    <a class="btn btn-danger btn-sm"
                       href="?delete=<?= (int)$it['id'] ?>"
                       data-confirm="Delete this menu item?">Delete</a>
                </td>
            </tr>
        <?php endwhile; endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();

$counts = [
    'users'        => (int)$conn->query("SELECT COUNT(*) c FROM users WHERE role='customer'")->fetch_assoc()['c'],
    'menu_items'   => (int)$conn->query("SELECT COUNT(*) c FROM menu_items")->fetch_assoc()['c'],
    'categories'   => (int)$conn->query("SELECT COUNT(*) c FROM categories")->fetch_assoc()['c'],
    'reservations' => (int)$conn->query("SELECT COUNT(*) c FROM reservations")->fetch_assoc()['c'],
    'pending_res'  => (int)$conn->query("SELECT COUNT(*) c FROM reservations WHERE status='pending'")->fetch_assoc()['c'],
    'orders'       => (int)$conn->query("SELECT COUNT(*) c FROM orders")->fetch_assoc()['c'],
    'revenue'      => (float)$conn->query("SELECT COALESCE(SUM(total_price),0) s FROM orders WHERE status<>'cancelled'")->fetch_assoc()['s'],
];

$page_title = 'Admin Dashboard';
$current = 'dashboard';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/admin_nav.php';
?>

<h1>Admin Dashboard</h1>
<p class="text-muted">Overview of your restaurant.</p>

<div class="admin-grid">
    <div class="admin-card">
        <div class="stat"><?= (int)$counts['users'] ?></div>
        <div class="label">Customers</div>
    </div>
    <div class="admin-card">
        <div class="stat"><?= (int)$counts['menu_items'] ?></div>
        <div class="label">Menu Items</div>
    </div>
    <div class="admin-card">
        <div class="stat"><?= (int)$counts['categories'] ?></div>
        <div class="label">Categories</div>
    </div>
    <div class="admin-card">
        <div class="stat"><?= (int)$counts['reservations'] ?></div>
        <div class="label">Reservations</div>
    </div>
    <div class="admin-card">
        <div class="stat"><?= (int)$counts['pending_res'] ?></div>
        <div class="label">Pending Reservations</div>
    </div>
    <div class="admin-card">
        <div class="stat"><?= (int)$counts['orders'] ?></div>
        <div class="label">Orders</div>
    </div>
    <div class="admin-card">
        <div class="stat"><?= e(price($counts['revenue'])) ?></div>
        <div class="label">Total Revenue</div>
    </div>
</div>

<h2 class="mt-3">Recent Reservations</h2>
<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Time</th>
                <th>Guests</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $rec = $conn->query(
            "SELECT r.*, u.full_name
             FROM reservations r
             JOIN users u ON u.id = r.user_id
             ORDER BY r.created_at DESC
             LIMIT 5"
        );
        if ($rec->num_rows === 0): ?>
            <tr><td colspan="6" class="text-muted text-center">No reservations yet.</td></tr>
        <?php else: while ($r = $rec->fetch_assoc()): ?>
            <tr>
                <td><?= (int)$r['id'] ?></td>
                <td><?= e($r['full_name']) ?></td>
                <td><?= e($r['reservation_date']) ?></td>
                <td><?= e(substr($r['reservation_time'], 0, 5)) ?></td>
                <td><?= (int)$r['guests'] ?></td>
                <td><span class="status status-<?= e($r['status']) ?>"><?= e($r['status']) ?></span></td>
            </tr>
        <?php endwhile; endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

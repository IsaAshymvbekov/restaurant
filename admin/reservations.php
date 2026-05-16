<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();

if (isset($_POST['action'], $_POST['id'])) {
    $rid    = (int)$_POST['id'];
    $action = $_POST['action'];

    $allowed = ['confirm' => 'confirmed', 'cancel' => 'cancelled', 'pending' => 'pending'];
    if (isset($allowed[$action])) {
        $new = $allowed[$action];
        $stmt = $conn->prepare("UPDATE reservations SET status = ? WHERE id = ?");
        $stmt->bind_param('si', $new, $rid);
        $stmt->execute();
        $stmt->close();
        set_flash('success', 'Reservation status updated.');
        redirect('reservations.php');
    }

    if ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM reservations WHERE id = ?");
        $stmt->bind_param('i', $rid);
        $stmt->execute();
        $stmt->close();
        set_flash('success', 'Reservation deleted.');
        redirect('reservations.php');
    }
}

$status_filter = $_GET['status'] ?? 'all';
$where = '';
if (in_array($status_filter, ['pending','confirmed','cancelled'], true)) {
    $where = "WHERE r.status = '" . $status_filter . "'";
}

$rows = $conn->query(
    "SELECT r.*, u.full_name, u.email
     FROM reservations r
     JOIN users u ON u.id = r.user_id
     $where
     ORDER BY r.reservation_date DESC, r.reservation_time DESC"
);

$page_title = 'Reservations - Admin';
$current = 'reservations';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/admin_nav.php';
?>

<div class="toolbar">
    <h1 style="margin:0;">Reservations</h1>
    <div class="menu-categories" style="margin:0;">
        <a class="<?= $status_filter === 'all' ? 'active' : '' ?>" href="reservations.php">All</a>
        <a class="<?= $status_filter === 'pending' ? 'active' : '' ?>" href="?status=pending">Pending</a>
        <a class="<?= $status_filter === 'confirmed' ? 'active' : '' ?>" href="?status=confirmed">Confirmed</a>
        <a class="<?= $status_filter === 'cancelled' ? 'active' : '' ?>" href="?status=cancelled">Cancelled</a>
    </div>
</div>

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Customer</th>
                <th>Email</th>
                <th>Date</th>
                <th>Time</th>
                <th>Guests</th>
                <th>Notes</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($rows->num_rows === 0): ?>
            <tr><td colspan="9" class="text-muted text-center">No reservations.</td></tr>
        <?php else: while ($r = $rows->fetch_assoc()): ?>
            <tr>
                <td><?= (int)$r['id'] ?></td>
                <td><?= e($r['full_name']) ?></td>
                <td><?= e($r['email']) ?></td>
                <td><?= e($r['reservation_date']) ?></td>
                <td><?= e(substr($r['reservation_time'], 0, 5)) ?></td>
                <td><?= (int)$r['guests'] ?></td>
                <td><?= e($r['notes']) ?: '<span class="text-muted">-</span>' ?></td>
                <td><span class="status status-<?= e($r['status']) ?>"><?= e($r['status']) ?></span></td>
                <td class="actions">
                    <?php if ($r['status'] !== 'confirmed'): ?>
                        <form method="post" class="inline-form">
                            <input type="hidden" name="id"     value="<?= (int)$r['id'] ?>">
                            <input type="hidden" name="action" value="confirm">
                            <button class="btn btn-primary btn-sm" type="submit">Confirm</button>
                        </form>
                    <?php endif; ?>
                    <?php if ($r['status'] !== 'cancelled'): ?>
                        <form method="post" class="inline-form">
                            <input type="hidden" name="id"     value="<?= (int)$r['id'] ?>">
                            <input type="hidden" name="action" value="cancel">
                            <button class="btn btn-outline btn-sm" type="submit"
                                    data-confirm="Cancel this reservation?">Cancel</button>
                        </form>
                    <?php endif; ?>
                    <form method="post" class="inline-form">
                        <input type="hidden" name="id"     value="<?= (int)$r['id'] ?>">
                        <input type="hidden" name="action" value="delete">
                        <button class="btn btn-danger btn-sm" type="submit"
                                data-confirm="Delete this reservation permanently?">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

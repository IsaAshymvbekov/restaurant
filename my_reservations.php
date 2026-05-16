<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

$stmt = $conn->prepare(
    "SELECT * FROM reservations WHERE user_id = ? ORDER BY reservation_date DESC, reservation_time DESC"
);
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$rows = $stmt->get_result();

$page_title = 'My Reservations - Bella Cucina';
include __DIR__ . '/includes/header.php';
?>

<div class="toolbar">
    <h1 style="margin:0;">My Reservations</h1>
    <a class="btn btn-primary" href="<?= e(base_url('reservation.php')) ?>">+ New Reservation</a>
</div>

<?php if ($rows->num_rows === 0): ?>
    <div class="empty-state">
        <h3>No reservations yet</h3>
        <p>Book a table to get started.</p>
        <a class="btn btn-primary mt-2" href="<?= e(base_url('reservation.php')) ?>">Reserve a Table</a>
    </div>
<?php else: ?>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Guests</th>
                    <th>Notes</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($r = $rows->fetch_assoc()): ?>
                    <tr>
                        <td><?= (int)$r['id'] ?></td>
                        <td><?= e($r['reservation_date']) ?></td>
                        <td><?= e(substr($r['reservation_time'], 0, 5)) ?></td>
                        <td><?= (int)$r['guests'] ?></td>
                        <td><?= e($r['notes']) ?: '<span class="text-muted">-</span>' ?></td>
                        <td><span class="status status-<?= e($r['status']) ?>">
                            <?= e($r['status']) ?>
                        </span></td>
                        <td>
                            <?php if ($r['status'] !== 'cancelled'): ?>
                                <div class="actions">
                                    <a class="btn btn-outline btn-sm"
                                       href="<?= e(base_url('update_reservation.php?id=' . (int)$r['id'])) ?>">Edit</a>
                                    <a class="btn btn-danger btn-sm"
                                       href="<?= e(base_url('cancel_reservation.php?id=' . (int)$r['id'])) ?>"
                                       data-confirm="Cancel this reservation?">Cancel</a>
                                </div>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>

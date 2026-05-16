<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    set_flash('error', 'Reservation not found.');
    redirect('my_reservations.php');
}

$stmt = $conn->prepare(
    "UPDATE reservations SET status = 'cancelled' WHERE id = ? AND user_id = ?"
);
$stmt->bind_param('ii', $id, $_SESSION['user_id']);
$stmt->execute();
$affected = $stmt->affected_rows;
$stmt->close();

if ($affected > 0) {
    set_flash('success', 'Reservation cancelled.');
} else {
    set_flash('error', 'Reservation could not be cancelled.');
}
redirect('my_reservations.php');

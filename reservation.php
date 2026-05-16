<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

if (is_admin()) {
    set_flash('info', 'Admins manage reservations from the admin panel.');
    redirect('admin/reservations.php');
}

$errors = [];
$old = [
    'reservation_date' => '',
    'reservation_time' => '',
    'guests'           => '2',
    'notes'            => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['reservation_date'] = trim($_POST['reservation_date'] ?? '');
    $old['reservation_time'] = trim($_POST['reservation_time'] ?? '');
    $old['guests']           = trim($_POST['guests'] ?? '');
    $old['notes']            = trim($_POST['notes'] ?? '');

    if (!$old['reservation_date']) {
        $errors['reservation_date'] = 'Please choose a date.';
    } elseif ($old['reservation_date'] < date('Y-m-d')) {
        $errors['reservation_date'] = 'Date cannot be in the past.';
    }

    if (!$old['reservation_time']) {
        $errors['reservation_time'] = 'Please choose a time.';
    }

    $g = (int)$old['guests'];
    if ($g < 1 || $g > 20) {
        $errors['guests'] = 'Guests must be between 1 and 20.';
    }

    if (empty($errors)) {
        $stmt = $conn->prepare(
            "INSERT INTO reservations (user_id, reservation_date, reservation_time, guests, notes)
             VALUES (?,?,?,?,?)"
        );
        $stmt->bind_param('issis',
            $_SESSION['user_id'],
            $old['reservation_date'],
            $old['reservation_time'],
            $g,
            $old['notes']);
        $stmt->execute();
        $stmt->close();

        set_flash('success', 'Reservation submitted! We will confirm shortly.');
        redirect('my_reservations.php');
    }
}

$page_title = 'Reserve a Table - Bella Cucina';
include __DIR__ . '/includes/header.php';
?>

<div class="form-card" style="max-width:560px;">
    <h2>Reserve a Table</h2>
    <p class="subtitle">Book your table at Bella Cucina.</p>

    <form id="reservation-form" method="post" novalidate>
        <div class="form-row">
            <div class="form-group">
                <label for="reservation_date">Date</label>
                <input type="date" id="reservation_date" name="reservation_date"
                       value="<?= e($old['reservation_date']) ?>"
                       min="<?= date('Y-m-d') ?>" required>
                <span class="form-error" data-for="reservation_date">
                    <?= e($errors['reservation_date'] ?? '') ?>
                </span>
            </div>
            <div class="form-group">
                <label for="reservation_time">Time</label>
                <input type="time" id="reservation_time" name="reservation_time"
                       value="<?= e($old['reservation_time']) ?>" required>
                <span class="form-error" data-for="reservation_time">
                    <?= e($errors['reservation_time'] ?? '') ?>
                </span>
            </div>
        </div>

        <div class="form-group">
            <label for="guests">Number of guests</label>
            <input type="number" id="guests" name="guests" min="1" max="20"
                   value="<?= e($old['guests']) ?>" required>
            <span class="form-error" data-for="guests">
                <?= e($errors['guests'] ?? '') ?>
            </span>
        </div>

        <div class="form-group">
            <label for="notes">Special requests (optional)</label>
            <textarea id="notes" name="notes" maxlength="255"
                      placeholder="Window table, high chair, allergies..."><?= e($old['notes']) ?></textarea>
        </div>

        <button class="btn btn-primary btn-block" type="submit">Reserve Table</button>
    </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

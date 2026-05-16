<?php
/**
 * One-time installer:
 *   - inserts the default admin and demo customer accounts
 *   - generates valid bcrypt hashes via password_hash()
 *
 * Visit http://localhost/restaurant/install.php once after importing
 * database/restaurant.sql via phpMyAdmin.  Safe to run more than once
 * (it only inserts if the accounts don't already exist).
 */

require_once __DIR__ . '/config/db.php';

$accounts = [
    [
        'full_name' => 'System Admin',
        'email'     => 'admin@restaurant.test',
        'phone'     => '0000000000',
        'password'  => 'admin123',
        'role'      => 'admin',
    ],
    [
        'full_name' => 'Demo Customer',
        'email'     => 'user@restaurant.test',
        'phone'     => '0000000001',
        'password'  => 'user1234',
        'role'      => 'customer',
    ],
];

$created = [];
$existed = [];

foreach ($accounts as $a) {
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param('s', $a['email']);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        $existed[] = $a['email'];
        $check->close();
        continue;
    }
    $check->close();

    $hash = password_hash($a['password'], PASSWORD_BCRYPT);
    $stmt = $conn->prepare(
        "INSERT INTO users (full_name, email, phone, password, role) VALUES (?,?,?,?,?)"
    );
    $stmt->bind_param('sssss',
        $a['full_name'], $a['email'], $a['phone'], $hash, $a['role']);
    $stmt->execute();
    $stmt->close();
    $created[] = $a['email'] . ' (password: ' . $a['password'] . ')';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Install - Bella Cucina</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<main class="container main">
    <div class="form-card" style="max-width:600px;">
        <h2>Install complete</h2>
        <?php if ($created): ?>
            <div class="alert alert-success">
                Created the following accounts:
                <ul>
                    <?php foreach ($created as $c): ?>
                        <li><?= htmlspecialchars($c) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <?php if ($existed): ?>
            <div class="alert alert-info">
                These accounts already existed (skipped):
                <ul>
                    <?php foreach ($existed as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <p>Default sign-in credentials:</p>
        <ul>
            <li><strong>Admin:</strong> admin@restaurant.test / admin123</li>
            <li><strong>Customer:</strong> user@restaurant.test / user1234</li>
        </ul>

        <p class="text-center mt-2">
            <a class="btn btn-primary" href="index.php">Go to home page</a>
            <a class="btn btn-outline" href="login.php">Sign in</a>
        </p>

        <p class="text-muted text-center mt-3" style="font-size:0.9rem;">
            For security, you may delete <code>install.php</code> after running it.
        </p>
    </div>
</main>
</body>
</html>

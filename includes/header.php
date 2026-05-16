<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';

$page_title = $page_title ?? 'Bella Cucina Restaurant';
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= e($page_title) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= e(base_url('assets/css/style.css')) ?>">
</head>
<body>
<header class="site-header">
    <div class="container nav">
        <a class="brand" href="<?= e(base_url('index.php')) ?>">
            <span class="brand-mark">B</span>
            <span class="brand-text">Bella Cucina</span>
        </a>
        <nav class="nav-links">
            <a href="<?= e(base_url('index.php')) ?>">Home</a>
            <a href="<?= e(base_url('menu.php')) ?>">Menu</a>
            <a href="<?= e(base_url('reservation.php')) ?>">Reserve</a>
            <?php if (is_logged_in()): ?>
                <a href="<?= e(base_url('my_reservations.php')) ?>">My Reservations</a>
                <a href="<?= e(base_url('cart.php')) ?>">
                    Cart
                    <?php $cc = cart_count(); if ($cc > 0): ?>
                        <span class="badge"><?= (int)$cc ?></span>
                    <?php endif; ?>
                </a>
                <?php if (is_admin()): ?>
                    <a class="admin-link" href="<?= e(base_url('admin/dashboard.php')) ?>">Admin</a>
                <?php endif; ?>
                <span class="nav-user">Hi, <?= e($_SESSION['full_name']) ?></span>
                <a class="btn btn-outline" href="<?= e(base_url('logout.php')) ?>">Logout</a>
            <?php else: ?>
                <a href="<?= e(base_url('login.php')) ?>">Sign In</a>
                <a class="btn btn-primary" href="<?= e(base_url('signup.php')) ?>">Sign Up</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="container main">
<?php if ($flash): ?>
    <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>

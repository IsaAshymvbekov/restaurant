<?php
/**
 * Common helper functions used across the project.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Escape a string for safe HTML output. */
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/** Redirect helper. */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/** Set a one-shot flash message. */
function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/** Read and clear the current flash message (returns null if none). */
function get_flash() {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/** Returns true if a user is currently logged in. */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/** Returns true if the current user has the admin role. */
function is_admin() {
    return is_logged_in() && ($_SESSION['role'] ?? '') === 'admin';
}

/** Force the user to be logged in; redirect to login if not. */
function require_login() {
    if (!is_logged_in()) {
        set_flash('error', 'Please sign in to continue.');
        redirect(base_url('login.php'));
    }
}

/** Force the user to be an admin. */
function require_admin() {
    require_login();
    if (!is_admin()) {
        set_flash('error', 'Admin access required.');
        redirect(base_url('index.php'));
    }
}

/**
 * Build a URL relative to the project root, regardless of the depth
 * of the current page (works for /restaurant/foo.php and
 * /restaurant/admin/foo.php alike).
 */
function base_url($path = '') {
    $script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    // Strip trailing /admin so admin pages link back to the project root.
    if (substr($script, -6) === '/admin') {
        $script = substr($script, 0, -6);
    }
    if ($script === '' || $script === '/' || $script === '.') {
        return '/' . ltrim($path, '/');
    }
    return rtrim($script, '/') . '/' . ltrim($path, '/');
}

/** Format a price value for display. */
function price($value) {
    return '$' . number_format((float)$value, 2);
}

/** Sum the cart in $_SESSION['cart'] (id => quantity) into a total. */
function cart_total(mysqli $conn) {
    $cart = $_SESSION['cart'] ?? [];
    if (empty($cart)) return 0.0;
    $ids = implode(',', array_map('intval', array_keys($cart)));
    $total = 0.0;
    $res = $conn->query("SELECT id, price FROM menu_items WHERE id IN ($ids)");
    while ($row = $res->fetch_assoc()) {
        $qty = (int)($cart[$row['id']] ?? 0);
        $total += (float)$row['price'] * $qty;
    }
    return $total;
}

/** Total quantity of items in the cart (used for the navbar badge). */
function cart_count() {
    $cart = $_SESSION['cart'] ?? [];
    return array_sum(array_map('intval', $cart));
}

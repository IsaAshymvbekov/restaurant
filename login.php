<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect(is_admin() ? 'admin/dashboard.php' : 'index.php');
}

$errors = [];
$email_old = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_old = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';

    if (!filter_var($email_old, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }
    if ($password === '') {
        $errors['password'] = 'Password is required.';
    }

    if (empty($errors)) {
        $stmt = $conn->prepare(
            "SELECT id, full_name, email, password, role FROM users WHERE email = ?"
        );
        $stmt->bind_param('s', $email_old);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email']     = $user['email'];
            $_SESSION['role']      = $user['role'];
            set_flash('success', 'Welcome back, ' . $user['full_name'] . '!');
            redirect($user['role'] === 'admin' ? 'admin/dashboard.php' : 'index.php');
        } else {
            $errors['password'] = 'Invalid email or password.';
        }
    }
}

$page_title = 'Sign In - Bella Cucina';
include __DIR__ . '/includes/header.php';
?>

<div class="form-card">
    <h2>Welcome back</h2>
    <p class="subtitle">Sign in to your account.</p>

    <form id="login-form" method="post" novalidate>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email"
                   value="<?= e($email_old) ?>" required>
            <span class="form-error" data-for="email">
                <?= e($errors['email'] ?? '') ?>
            </span>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            <span class="form-error" data-for="password">
                <?= e($errors['password'] ?? '') ?>
            </span>
        </div>

        <button class="btn btn-primary btn-block" type="submit">Sign in</button>
        <a class="form-link" href="signup.php">Don't have an account? Sign up</a>
    </form>

    <div class="alert alert-info mt-3" style="font-size:0.85rem;">
        <strong>Demo accounts</strong><br>
        Admin: <code>admin@restaurant.test</code> / <code>admin123</code><br>
        Customer: <code>user@restaurant.test</code> / <code>user1234</code>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

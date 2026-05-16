<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$errors = [];
$old = [
    'full_name' => '',
    'email'     => '',
    'phone'     => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['full_name'] = trim($_POST['full_name'] ?? '');
    $old['email']     = trim($_POST['email'] ?? '');
    $old['phone']     = trim($_POST['phone'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm          = $_POST['confirm_password'] ?? '';

    if (strlen($old['full_name']) < 2) {
        $errors['full_name'] = 'Please enter your full name.';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }
    if ($old['phone'] !== '' && !preg_match('/^[0-9+\-\s()]{7,20}$/', $old['phone'])) {
        $errors['phone'] = 'Phone number looks invalid.';
    }
    if (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters.';
    }
    if ($password !== $confirm) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        // Make sure email is unique.
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $old['email']);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors['email'] = 'This email is already registered.';
        }
        $stmt->close();
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $role = 'customer';
        $stmt = $conn->prepare(
            "INSERT INTO users (full_name, email, phone, password, role) VALUES (?,?,?,?,?)"
        );
        $stmt->bind_param('sssss',
            $old['full_name'], $old['email'], $old['phone'], $hash, $role);
        $stmt->execute();
        $stmt->close();

        set_flash('success', 'Account created. Please sign in.');
        redirect('login.php');
    }
}

$page_title = 'Sign Up - Bella Cucina';
include __DIR__ . '/includes/header.php';
?>

<div class="form-card">
    <h2>Create your account</h2>
    <p class="subtitle">Sign up to reserve tables and place orders.</p>

    <form id="signup-form" method="post" novalidate>
        <div class="form-group">
            <label for="full_name">Full name</label>
            <input type="text" id="full_name" name="full_name"
                   value="<?= e($old['full_name']) ?>" required>
            <span class="form-error" data-for="full_name">
                <?= e($errors['full_name'] ?? '') ?>
            </span>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email"
                   value="<?= e($old['email']) ?>" required>
            <span class="form-error" data-for="email">
                <?= e($errors['email'] ?? '') ?>
            </span>
        </div>

        <div class="form-group">
            <label for="phone">Phone (optional)</label>
            <input type="text" id="phone" name="phone"
                   value="<?= e($old['phone']) ?>">
            <span class="form-error" data-for="phone">
                <?= e($errors['phone'] ?? '') ?>
            </span>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <span class="form-error" data-for="password">
                    <?= e($errors['password'] ?? '') ?>
                </span>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
                <span class="form-error" data-for="confirm_password">
                    <?= e($errors['confirm_password'] ?? '') ?>
                </span>
            </div>
        </div>

        <button class="btn btn-primary btn-block" type="submit">Sign up</button>
        <a class="form-link" href="login.php">Already have an account? Sign in</a>
    </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

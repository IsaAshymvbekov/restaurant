<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();

if (isset($_POST['action'], $_POST['id'])) {
    $uid    = (int)$_POST['id'];
    $action = $_POST['action'];

    if ($uid === (int)$_SESSION['user_id']) {
        set_flash('error', 'You cannot modify your own admin account here.');
        redirect('users.php');
    }

    if ($action === 'promote') {
        $conn->query("UPDATE users SET role='admin' WHERE id=$uid");
        set_flash('success', 'User promoted to admin.');
        redirect('users.php');
    }
    if ($action === 'demote') {
        $conn->query("UPDATE users SET role='customer' WHERE id=$uid");
        set_flash('success', 'User demoted to customer.');
        redirect('users.php');
    }
    if ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $stmt->close();
        set_flash('success', 'User deleted.');
        redirect('users.php');
    }
}

$users = $conn->query("SELECT * FROM users ORDER BY role, full_name");

$page_title = 'Users - Admin';
$current = 'users';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/admin_nav.php';
?>

<h1>Users</h1>

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Role</th>
                <th>Joined</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($u = $users->fetch_assoc()): ?>
            <tr>
                <td><?= (int)$u['id'] ?></td>
                <td><strong><?= e($u['full_name']) ?></strong></td>
                <td><?= e($u['email']) ?></td>
                <td><?= e($u['phone']) ?: '<span class="text-muted">-</span>' ?></td>
                <td>
                    <?php if ($u['role'] === 'admin'): ?>
                        <span class="status status-confirmed">admin</span>
                    <?php else: ?>
                        <span class="status status-pending">customer</span>
                    <?php endif; ?>
                </td>
                <td><?= e($u['created_at']) ?></td>
                <td class="actions">
                    <?php if ((int)$u['id'] === (int)$_SESSION['user_id']): ?>
                        <span class="text-muted">you</span>
                    <?php else: ?>
                        <?php if ($u['role'] === 'customer'): ?>
                            <form method="post" class="inline-form">
                                <input type="hidden" name="id"     value="<?= (int)$u['id'] ?>">
                                <input type="hidden" name="action" value="promote">
                                <button class="btn btn-outline btn-sm" type="submit">Make admin</button>
                            </form>
                        <?php else: ?>
                            <form method="post" class="inline-form">
                                <input type="hidden" name="id"     value="<?= (int)$u['id'] ?>">
                                <input type="hidden" name="action" value="demote">
                                <button class="btn btn-outline btn-sm" type="submit">Make customer</button>
                            </form>
                        <?php endif; ?>
                        <form method="post" class="inline-form">
                            <input type="hidden" name="id"     value="<?= (int)$u['id'] ?>">
                            <input type="hidden" name="action" value="delete">
                            <button class="btn btn-danger btn-sm" type="submit"
                                    data-confirm="Delete this user and all their data?">Delete</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

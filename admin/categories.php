<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();

$errors = [];
$edit_id   = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$edit_data = ['name' => '', 'description' => ''];

// Delete category
if (isset($_GET['delete'])) {
    $del = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param('i', $del);
    $stmt->execute();
    $stmt->close();
    set_flash('success', 'Category deleted (and its items).');
    redirect('categories.php');
}

if ($edit_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->bind_param('i', $edit_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row) {
        $edit_data = ['name' => $row['name'], 'description' => $row['description']];
    } else {
        $edit_id = 0;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $cid  = (int)($_POST['id'] ?? 0);

    if ($name === '') $errors['name'] = 'Category name is required.';

    if (empty($errors)) {
        if ($cid > 0) {
            $stmt = $conn->prepare(
                "UPDATE categories SET name = ?, description = ? WHERE id = ?"
            );
            $stmt->bind_param('ssi', $name, $desc, $cid);
            $stmt->execute();
            $stmt->close();
            set_flash('success', 'Category updated.');
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO categories (name, description) VALUES (?,?)"
            );
            $stmt->bind_param('ss', $name, $desc);
            $stmt->execute();
            $stmt->close();
            set_flash('success', 'Category added.');
        }
        redirect('categories.php');
    } else {
        $edit_id = $cid;
        $edit_data = ['name' => $name, 'description' => $desc];
    }
}

$cats = $conn->query("SELECT * FROM categories ORDER BY name");

$page_title = 'Categories - Admin';
$current = 'categories';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/admin_nav.php';
?>

<h1>Categories</h1>

<div style="display:grid; grid-template-columns: 1fr 2fr; gap:24px;" class="responsive-cats">
    <div class="form-card" style="max-width:none;">
        <h2><?= $edit_id > 0 ? 'Edit Category' : 'Add Category' ?></h2>

        <form id="category-form" method="post" novalidate>
            <input type="hidden" name="id" value="<?= (int)$edit_id ?>">

            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name"
                       value="<?= e($edit_data['name']) ?>" required>
                <span class="form-error" data-for="name"><?= e($errors['name'] ?? '') ?></span>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"><?= e($edit_data['description']) ?></textarea>
            </div>

            <button class="btn btn-primary btn-block" type="submit">
                <?= $edit_id > 0 ? 'Save Changes' : 'Add Category' ?>
            </button>
            <?php if ($edit_id > 0): ?>
                <a class="form-link" href="categories.php">Cancel edit</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($cats->num_rows === 0): ?>
                <tr><td colspan="4" class="text-muted text-center">No categories yet.</td></tr>
            <?php else: while ($c = $cats->fetch_assoc()): ?>
                <tr>
                    <td><?= (int)$c['id'] ?></td>
                    <td><strong><?= e($c['name']) ?></strong></td>
                    <td><?= e($c['description']) ?></td>
                    <td class="actions">
                        <a class="btn btn-outline btn-sm"
                           href="?edit=<?= (int)$c['id'] ?>">Edit</a>
                        <a class="btn btn-danger btn-sm"
                           href="?delete=<?= (int)$c['id'] ?>"
                           data-confirm="Delete category and all of its items?">Delete</a>
                    </td>
                </tr>
            <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
@media (max-width: 800px) {
    .responsive-cats { grid-template-columns: 1fr !important; }
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>

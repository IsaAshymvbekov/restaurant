<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    set_flash('error', 'Item not found.');
    redirect('menu_items.php');
}

$stmt = $conn->prepare("SELECT * FROM menu_items WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$item) {
    set_flash('error', 'Item not found.');
    redirect('menu_items.php');
}

$errors = [];
$old = [
    'name'        => $item['name'],
    'description' => $item['description'],
    'price'       => $item['price'],
    'category_id' => (int)$item['category_id'],
    'available'   => (int)$item['available'] === 1 ? '1' : '0',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['name']        = trim($_POST['name'] ?? '');
    $old['description'] = trim($_POST['description'] ?? '');
    $old['price']       = trim($_POST['price'] ?? '');
    $old['category_id'] = (int)($_POST['category_id'] ?? 0);
    $old['available']   = isset($_POST['available']) ? '1' : '0';

    if ($old['name'] === '') $errors['name'] = 'Item name is required.';
    if ($old['category_id'] <= 0) $errors['category_id'] = 'Please choose a category.';
    if (!is_numeric($old['price']) || (float)$old['price'] <= 0) {
        $errors['price'] = 'Price must be a positive number.';
    }

    if (empty($errors)) {
        $price = (float)$old['price'];
        $avail = (int)$old['available'];
        $stmt  = $conn->prepare(
            "UPDATE menu_items
             SET category_id = ?, name = ?, description = ?, price = ?, available = ?
             WHERE id = ?"
        );
        $stmt->bind_param('issdii',
            $old['category_id'], $old['name'], $old['description'],
            $price, $avail, $id);
        $stmt->execute();
        $stmt->close();
        set_flash('success', 'Menu item updated.');
        redirect('menu_items.php');
    }
}

$categories = $conn->query("SELECT id, name FROM categories ORDER BY name");

$page_title = 'Edit Menu Item - Admin';
$current = 'menu_items';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/admin_nav.php';
?>

<div class="form-card" style="max-width:600px;">
    <h2>Edit Menu Item</h2>

    <form id="menu-item-form" method="post" novalidate>
        <div class="form-group">
            <label for="name">Item name</label>
            <input type="text" id="name" name="name" value="<?= e($old['name']) ?>" required>
            <span class="form-error" data-for="name"><?= e($errors['name'] ?? '') ?></span>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id" required>
                    <option value="">-- choose --</option>
                    <?php while ($c = $categories->fetch_assoc()): ?>
                        <option value="<?= (int)$c['id'] ?>"
                            <?= (int)$old['category_id'] === (int)$c['id'] ? 'selected' : '' ?>>
                            <?= e($c['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <span class="form-error" data-for="category_id"><?= e($errors['category_id'] ?? '') ?></span>
            </div>
            <div class="form-group">
                <label for="price">Price</label>
                <input type="number" step="0.01" min="0.01" id="price" name="price"
                       value="<?= e($old['price']) ?>" required>
                <span class="form-error" data-for="price"><?= e($errors['price'] ?? '') ?></span>
            </div>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description"><?= e($old['description']) ?></textarea>
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="available" value="1"
                    <?= $old['available'] === '1' ? 'checked' : '' ?>>
                Available
            </label>
        </div>

        <button class="btn btn-primary btn-block" type="submit">Save Changes</button>
        <a class="form-link" href="menu_items.php">Cancel</a>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

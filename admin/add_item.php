<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();

$errors = [];
$old = [
    'name' => '', 'description' => '', 'price' => '',
    'category_id' => '', 'available' => '1',
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
            "INSERT INTO menu_items (category_id, name, description, price, available)
             VALUES (?,?,?,?,?)"
        );
        $stmt->bind_param('issdi',
            $old['category_id'], $old['name'], $old['description'], $price, $avail);
        $stmt->execute();
        $stmt->close();
        set_flash('success', 'Menu item added.');
        redirect('menu_items.php');
    }
}

$categories = $conn->query("SELECT id, name FROM categories ORDER BY name");

$page_title = 'Add Menu Item - Admin';
$current = 'menu_items';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/admin_nav.php';
?>

<div class="form-card" style="max-width:600px;">
    <h2>Add Menu Item</h2>

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

        <button class="btn btn-primary btn-block" type="submit">Add Item</button>
        <a class="form-link" href="menu_items.php">Cancel</a>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

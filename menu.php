<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$selected_cat = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;

$categories = $conn->query("SELECT * FROM categories ORDER BY id");

if ($selected_cat > 0) {
    $stmt = $conn->prepare(
        "SELECT m.*, c.name AS category_name
         FROM menu_items m
         JOIN categories c ON c.id = m.category_id
         WHERE m.category_id = ?
         ORDER BY m.name"
    );
    $stmt->bind_param('i', $selected_cat);
    $stmt->execute();
    $items = $stmt->get_result();
} else {
    $items = $conn->query(
        "SELECT m.*, c.name AS category_name
         FROM menu_items m
         JOIN categories c ON c.id = m.category_id
         ORDER BY c.id, m.name"
    );
}

$page_title = 'Menu - Bella Cucina';
include __DIR__ . '/includes/header.php';
?>

<h1>Our Menu</h1>
<p class="text-muted">Browse our dishes and add them to your order.</p>

<div class="menu-categories">
    <a href="menu.php" class="<?= $selected_cat === 0 ? 'active' : '' ?>">All</a>
    <?php while ($c = $categories->fetch_assoc()): ?>
        <a href="menu.php?cat=<?= (int)$c['id'] ?>"
           class="<?= $selected_cat === (int)$c['id'] ? 'active' : '' ?>">
            <?= e($c['name']) ?>
        </a>
    <?php endwhile; ?>
</div>

<?php if ($items->num_rows === 0): ?>
    <div class="empty-state">
        <h3>No items found</h3>
        <p>There are no items in this category yet.</p>
    </div>
<?php else: ?>
    <div class="menu-grid">
        <?php while ($item = $items->fetch_assoc()): ?>
            <div class="menu-item">
                <h3><?= e($item['name']) ?></h3>
                <p class="text-muted" style="font-size:0.8rem; margin:2px 0 6px;">
                    <?= e($item['category_name']) ?>
                </p>
                <p class="desc"><?= e($item['description']) ?></p>
                <div class="price-row">
                    <span class="price"><?= e(price($item['price'])) ?></span>
                    <?php if ((int)$item['available'] === 1): ?>
                        <?php if (is_logged_in() && !is_admin()): ?>
                            <form method="post" action="<?= e(base_url('cart.php')) ?>" class="inline-form">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="item_id" value="<?= (int)$item['id'] ?>">
                                <button class="btn btn-primary btn-sm" type="submit">Add to order</button>
                            </form>
                        <?php elseif (!is_logged_in()): ?>
                            <a class="btn btn-outline btn-sm"
                               href="<?= e(base_url('login.php')) ?>">Sign in to order</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="unavailable">Unavailable</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>

<?php
$page_title = 'Bella Cucina - Reserve & Order Online';
include __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <h1>Welcome to Bella Cucina</h1>
    <p>
        Authentic Italian cuisine, freshly made every day.
        Reserve your table in seconds or order from our menu online.
    </p>
    <div class="hero-actions">
        <a class="btn btn-primary" href="<?= e(base_url('reservation.php')) ?>">
            Reserve a Table
        </a>
        <a class="btn btn-accent" href="<?= e(base_url('menu.php')) ?>">
            View Menu
        </a>
    </div>
</section>

<section class="section">
    <h2>Why choose us</h2>
    <div class="features">
        <div class="feature-card">
            <div class="feature-icon">R</div>
            <h3>Easy Reservations</h3>
            <p>Pick a date, time, and party size in just a few clicks.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">M</div>
            <h3>Full Menu Online</h3>
            <p>Browse starters, mains, desserts, and drinks with prices.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">O</div>
            <h3>Quick Ordering</h3>
            <p>Add items to your order and view a clear summary with the total.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">A</div>
            <h3>Account Management</h3>
            <p>Sign in to view, update, or cancel your reservations any time.</p>
        </div>
    </div>
</section>

<section class="section">
    <h2>Today's Highlights</h2>
    <div class="menu-grid">
        <?php
        $highlight = $conn->query(
            "SELECT m.*, c.name AS category_name
             FROM menu_items m
             JOIN categories c ON c.id = m.category_id
             WHERE m.available = 1
             ORDER BY RAND()
             LIMIT 4"
        );
        while ($item = $highlight->fetch_assoc()):
        ?>
            <div class="menu-item">
                <h3><?= e($item['name']) ?></h3>
                <p class="text-muted" style="font-size:0.8rem; margin:2px 0 6px;">
                    <?= e($item['category_name']) ?>
                </p>
                <p class="desc"><?= e($item['description']) ?></p>
                <div class="price-row">
                    <span class="price"><?= e(price($item['price'])) ?></span>
                    <a class="btn btn-outline btn-sm"
                       href="<?= e(base_url('menu.php')) ?>">View menu</a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>

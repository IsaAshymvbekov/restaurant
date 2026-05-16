<?php
/** Renders the admin sub-navigation tabs.
 *  $current is set by the including page to the page key. */
$tabs = [
    'dashboard'    => ['label' => 'Dashboard',    'url' => 'dashboard.php'],
    'menu_items'   => ['label' => 'Menu Items',   'url' => 'menu_items.php'],
    'categories'   => ['label' => 'Categories',   'url' => 'categories.php'],
    'reservations' => ['label' => 'Reservations', 'url' => 'reservations.php'],
    'orders'       => ['label' => 'Orders',       'url' => 'orders.php'],
    'users'        => ['label' => 'Users',        'url' => 'users.php'],
];
?>
<nav class="admin-tabs">
<?php foreach ($tabs as $key => $tab): ?>
    <a href="<?= e($tab['url']) ?>" class="<?= ($current ?? '') === $key ? 'active' : '' ?>">
        <?= e($tab['label']) ?>
    </a>
<?php endforeach; ?>
</nav>

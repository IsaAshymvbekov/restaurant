<?php
/**
 * Database connection
 *
 * Default XAMPP MySQL settings:
 *   host     = localhost
 *   user     = root
 *   password = (empty)
 *
 * If your XAMPP uses different credentials, change them below.
 */

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'restaurant_db';

// Use mysqli with exception reporting so problems are easier to spot.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    die(
        '<h2 style="font-family:sans-serif;color:#b00020;">Database connection failed</h2>' .
        '<p>Make sure XAMPP MySQL is running and that you imported '
        . '<code>database/restaurant.sql</code> through phpMyAdmin.</p>' .
        '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>'
    );
}

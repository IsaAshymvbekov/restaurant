-- =====================================================================
-- Restaurant Online Reservation and Ordering System
-- Database SQL File
-- Course: CMPE483 - Internet Programming II
--
-- HOW TO INSTALL:
--   1. Start XAMPP (Apache + MySQL).
--   2. Open http://localhost/phpmyadmin
--   3. Click "Import" and select this file (restaurant.sql).
--   4. Open http://localhost/restaurant/install.php once to create
--      the default admin and demo customer accounts (uses
--      password_hash() so the hashes are valid).
--
-- DEFAULT ACCOUNTS (created by install.php):
--   Admin    -> admin@restaurant.test  / admin123
--   Customer -> user@restaurant.test   / user1234
-- =====================================================================

CREATE DATABASE IF NOT EXISTS restaurant_db
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE restaurant_db;

-- ---------------------------------------------------------------------
-- Drop tables (in correct order due to foreign keys) so the script
-- can be re-imported safely from phpMyAdmin.
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS reservations;
DROP TABLE IF EXISTS menu_items;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

-- ---------------------------------------------------------------------
-- Table: users
-- Stores customers and administrators.
-- ---------------------------------------------------------------------
CREATE TABLE users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    full_name     VARCHAR(100) NOT NULL,
    email         VARCHAR(120) NOT NULL UNIQUE,
    phone         VARCHAR(20)  DEFAULT NULL,
    password      VARCHAR(255) NOT NULL,
    role          ENUM('customer','admin') NOT NULL DEFAULT 'customer',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table: categories
-- Food categories shown on the menu (e.g. Starters, Main, Drinks).
-- ---------------------------------------------------------------------
CREATE TABLE categories (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(80) NOT NULL UNIQUE,
    description VARCHAR(255) DEFAULT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table: menu_items
-- Individual food / drink items belonging to a category.
-- ---------------------------------------------------------------------
CREATE TABLE menu_items (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    category_id  INT NOT NULL,
    name         VARCHAR(120) NOT NULL,
    description  TEXT,
    price        DECIMAL(8,2) NOT NULL,
    image        VARCHAR(255) DEFAULT NULL,
    available    TINYINT(1) NOT NULL DEFAULT 1,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_menu_category
        FOREIGN KEY (category_id) REFERENCES categories(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table: reservations
-- Table reservations made by customers.
-- ---------------------------------------------------------------------
CREATE TABLE reservations (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    user_id          INT NOT NULL,
    reservation_date DATE NOT NULL,
    reservation_time TIME NOT NULL,
    guests           INT NOT NULL,
    notes            VARCHAR(255) DEFAULT NULL,
    status           ENUM('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reservation_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table: orders
-- Header for a customer order.  Lines are stored in order_items.
-- ---------------------------------------------------------------------
CREATE TABLE orders (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL DEFAULT 0,
    status      ENUM('pending','preparing','completed','cancelled') NOT NULL DEFAULT 'pending',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_order_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table: order_items
-- One row per menu item inside an order.
-- ---------------------------------------------------------------------
CREATE TABLE order_items (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    order_id      INT NOT NULL,
    menu_item_id  INT NOT NULL,
    quantity      INT NOT NULL DEFAULT 1,
    price         DECIMAL(8,2) NOT NULL,
    CONSTRAINT fk_oi_order
        FOREIGN KEY (order_id) REFERENCES orders(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_oi_item
        FOREIGN KEY (menu_item_id) REFERENCES menu_items(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================================
-- Seed catalog data (categories + menu items).
-- The default admin / demo user accounts are created by install.php
-- so that PHP can build valid password_hash() values.
-- =====================================================================

INSERT INTO categories (name, description) VALUES
('Starters',    'Light dishes to start your meal'),
('Main Course', 'Hearty main meals'),
('Desserts',    'Sweet treats to finish your meal'),
('Drinks',      'Cold and hot beverages');

INSERT INTO menu_items (category_id, name, description, price, available) VALUES
(1, 'Caesar Salad',       'Fresh romaine, parmesan, croutons, Caesar dressing', 8.50,  1),
(1, 'Tomato Soup',        'Creamy tomato soup served with bread',               6.00,  1),
(1, 'Bruschetta',         'Toasted bread topped with tomato and basil',         7.25,  1),
(2, 'Grilled Chicken',    'Grilled chicken breast with seasonal vegetables',   14.50,  1),
(2, 'Beef Steak',         '250g sirloin steak served with fries',              19.90,  1),
(2, 'Margherita Pizza',   'Classic pizza with mozzarella and basil',           12.00,  1),
(2, 'Spaghetti Bolognese','Spaghetti with rich beef tomato sauce',             11.75,  1),
(3, 'Chocolate Cake',     'Warm chocolate cake with vanilla ice cream',         5.50,  1),
(3, 'Cheesecake',         'New York style cheesecake',                          5.75,  1),
(4, 'Coca Cola',          '330 ml can',                                         2.00,  1),
(4, 'Fresh Orange Juice', 'Freshly squeezed orange juice',                      3.50,  1),
(4, 'Turkish Tea',        'Traditional black tea',                              1.50,  1),
(4, 'Espresso',           'Single shot espresso',                               2.50,  1);

# Term Project Report

**Course:** CMPE483 - Internet Programming II
**Project Title:** Bella Cucina - Restaurant Online Reservation and Ordering System
**Group Number:** _Group X_
**Group Members:** Isa Ashymbekov 22208915
**Submission Date:** May 19, 2026

---

## 1. Introduction

This project is a complete full-stack web application that simulates the
online presence of a small Italian restaurant called **Bella Cucina**. It was
chosen from the list of project options provided by the instructor (Restaurant
Online Reservation and Ordering System).

The purpose of the system is to give customers an easy way to:

- create an account and sign in;
- browse a menu organized by categories;
- reserve a table by choosing a date, time, and party size;
- view, update, and cancel their own reservations;
- add menu items to an order, see a price summary, and place the order.

At the same time, the system gives administrators a private back-office where
they can:

- see overall statistics on the dashboard;
- add, update, delete, and toggle the availability of menu items;
- manage food categories;
- approve, cancel, or delete reservations;
- update order status (pending → preparing → completed) or delete orders;
- promote / demote / delete users.

The application is built with **HTML, CSS, JavaScript, PHP, and MySQL** and
runs locally on **XAMPP**.

---

## 2. System Features

### 2.1 Customer side

| Page                     | Description                                                                |
| ------------------------ | -------------------------------------------------------------------------- |
| `index.php`              | Landing page with a hero section, four feature cards, and four randomly highlighted dishes. |
| `signup.php`             | Sign-up form with name, email, optional phone, and password fields.        |
| `login.php`              | Sign-in form with demo credentials shown for graders.                      |
| `logout.php`             | Destroys the session and signs the user out.                               |
| `menu.php`               | Lists all menu items, with category filter buttons (All / Starters / Main Course / Desserts / Drinks). Logged-in customers see an **Add to order** button next to each item. |
| `reservation.php`        | Form for booking a table (date, time, guests 1–20, optional notes).        |
| `my_reservations.php`    | Lists the user's own reservations with status badges. Users can edit (`update_reservation.php`) or cancel (`cancel_reservation.php`) any reservation that is not already cancelled. |
| `cart.php`               | Shows the order summary (item, category, price, quantity, subtotal). Users can change quantities, remove items, clear the cart, or place the order. |
| `order_summary.php`      | Receipt page after a successful checkout, with status badge and total.     |

### 2.2 Admin side (`/admin/`)

All admin pages are protected by a `require_admin()` guard.

| Page                       | Description                                                                |
| -------------------------- | -------------------------------------------------------------------------- |
| `admin/dashboard.php`      | Stat cards for customers, menu items, categories, reservations, pending reservations, orders, and total revenue, plus a list of the five most recent reservations. |
| `admin/menu_items.php`     | List of every menu item with edit, toggle availability, and delete actions. |
| `admin/add_item.php`       | Form to add a new menu item (name, category, price, description, available checkbox). |
| `admin/edit_item.php`      | Same form pre-filled, used to update an existing item.                     |
| `admin/categories.php`     | Combined add / edit / list / delete page for categories.                   |
| `admin/reservations.php`   | List of all reservations with status filter buttons; admins can confirm, cancel, or delete each reservation. |
| `admin/orders.php`         | List of all orders with a drop-down to change the status, plus delete.     |
| `admin/users.php`          | List of users with promote / demote / delete buttons (admins cannot delete themselves). |

### 2.3 Cross-cutting features

- Session-based authentication using `password_hash()` (bcrypt).
- Two roles in the `users` table: `customer` and `admin`.
- One-time `install.php` script that creates the default admin and demo
  customer with valid bcrypt hashes (the schema SQL alone cannot ship
  bcrypt hashes safely, so a small PHP installer is used).
- Flash message system (`set_flash`, `get_flash`) for success / error / info
  notifications after redirects.
- Cart kept in `$_SESSION['cart']`, then atomically converted to one
  `orders` row + many `order_items` rows inside a single transaction.
- **Client-side** validation in JavaScript (`assets/js/validation.js`) with
  inline error messages, **and** independent **server-side** validation in
  every PHP page. The two layers do not trust each other.
- **Security:** every SQL statement uses `mysqli` prepared statements with
  parameter binding; every output uses `htmlspecialchars()` (`e()` helper)
  to prevent XSS; the session id is regenerated after sign-in.

---

## 3. Languages Used

### 3.1 HTML
HTML provides the structure of every page (headings, forms, tables, navigation,
hero section, etc.). The shared layout is built once in
`includes/header.php` and `includes/footer.php`, then included by every page
so the site has a consistent look and a single navigation bar. Semantic tags
such as `<header>`, `<nav>`, `<main>`, `<section>`, and `<footer>` are used.

### 3.2 CSS
A single stylesheet (`assets/css/style.css`) defines the visual design. It
uses **CSS variables** for the color palette, **CSS Grid** and **Flexbox**
for the layouts, and **media queries** for responsive behavior down to
phone widths. Reusable utility classes (`.btn`, `.btn-primary`, `.alert`,
`.form-card`, `.data-table`, `.status`, …) keep the markup clean.

### 3.3 JavaScript
`assets/js/validation.js` runs entirely on the client. It binds the
`submit` event of every form (sign up, login, reservation, menu item,
category) and shows inline error messages without contacting the server,
giving immediate feedback. It also implements a generic `data-confirm`
attribute used on delete and cancel buttons to ask for confirmation before
destructive actions.

### 3.4 PHP
PHP is the server-side language. It:

- connects to MySQL through `mysqli` (`config/db.php`);
- handles form submissions, validates input, inserts / updates / deletes rows;
- manages sessions for authentication and the cart;
- enforces authorization with `require_login()` and `require_admin()`;
- renders dynamic HTML by mixing `<?= ... ?>` with the markup;
- uses **prepared statements with parameter binding** for every SQL query,
  which defeats SQL-injection attacks;
- wraps the order-creation flow in a database transaction so an order is
  never half-saved.

### 3.5 MySQL
The database (`restaurant_db`) stores all persistent data: users,
categories, menu items, reservations, orders, and order items. **Foreign
keys** with `ON DELETE CASCADE` keep the data consistent (e.g. deleting a
user automatically removes their reservations and orders). The complete
schema and seed data live in `database/restaurant.sql`, imported through
phpMyAdmin.

---

## 4. Database Design

The database is named **`restaurant_db`** and contains six tables.

### 4.1 `users`
Stores both customers and administrators.

| Column      | Type                            | Notes                          |
| ----------- | ------------------------------- | ------------------------------ |
| id          | INT, PK, AUTO_INCREMENT         |                                |
| full_name   | VARCHAR(100)                    | required                       |
| email       | VARCHAR(120)                    | UNIQUE                         |
| phone       | VARCHAR(20)                     | optional                       |
| password    | VARCHAR(255)                    | bcrypt hash                    |
| role        | ENUM('customer','admin')        | default `customer`             |
| created_at  | TIMESTAMP                       | default `CURRENT_TIMESTAMP`    |

### 4.2 `categories`

| Column      | Type                            | Notes               |
| ----------- | ------------------------------- | ------------------- |
| id          | INT, PK, AUTO_INCREMENT         |                     |
| name        | VARCHAR(80) UNIQUE              | required            |
| description | VARCHAR(255)                    | optional            |
| created_at  | TIMESTAMP                       |                     |

### 4.3 `menu_items`

| Column      | Type                            | Notes                        |
| ----------- | ------------------------------- | ---------------------------- |
| id          | INT, PK, AUTO_INCREMENT         |                              |
| category_id | INT, FK → `categories.id`       | `ON DELETE CASCADE`          |
| name        | VARCHAR(120)                    | required                     |
| description | TEXT                            |                              |
| price       | DECIMAL(8,2)                    | required                     |
| image       | VARCHAR(255)                    | optional                     |
| available   | TINYINT(1)                      | 1 = shown, 0 = hidden        |
| created_at  | TIMESTAMP                       |                              |

### 4.4 `reservations`

| Column           | Type                                       | Notes                |
| ---------------- | ------------------------------------------ | -------------------- |
| id               | INT, PK                                    |                      |
| user_id          | INT, FK → `users.id`                       | `ON DELETE CASCADE`  |
| reservation_date | DATE                                       |                      |
| reservation_time | TIME                                       |                      |
| guests           | INT                                        | 1–20 (validated)     |
| notes            | VARCHAR(255)                               | optional             |
| status           | ENUM('pending','confirmed','cancelled')    |                      |
| created_at       | TIMESTAMP                                  |                      |

### 4.5 `orders`

| Column      | Type                                                        |
| ----------- | ----------------------------------------------------------- |
| id          | INT, PK                                                     |
| user_id     | INT, FK → `users.id` (CASCADE)                              |
| total_price | DECIMAL(10,2)                                               |
| status      | ENUM('pending','preparing','completed','cancelled')         |
| created_at  | TIMESTAMP                                                   |

### 4.6 `order_items`

| Column        | Type                                  |
| ------------- | ------------------------------------- |
| id            | INT, PK                               |
| order_id      | INT, FK → `orders.id` (CASCADE)       |
| menu_item_id  | INT, FK → `menu_items.id` (CASCADE)   |
| quantity      | INT                                   |
| price         | DECIMAL(8,2)                          |

### 4.7 Relationships

```
users 1───* reservations
users 1───* orders   1───* order_items *───1 menu_items *───1 categories
```

A user can have many reservations and many orders. Each order contains many
order items. Each order item refers to one menu item, and each menu item
belongs to one category.

---

## 5. Screenshots

All screenshots were captured from the running application on
`http://localhost/restaurant/` after importing
`database/restaurant.sql` and running `install.php`. They are stored in
the `screenshots/` folder of this project.

### 5.1 Public pages

| # | Page                                        | File                          |
| - | ------------------------------------------- | ----------------------------- |
| 1 | Home page (hero, features, highlights)      | `01_home.png`                 |
| 2 | Sign-up form                                | `02_signup.png`               |
| 3 | Sign-in form (with demo credentials)        | `03_login.png`                |
| 4 | Menu (all categories)                       | `04_menu_all.png`             |
| 5 | Menu filtered to "Starters"                 | `05_menu_starters.png`        |

### 5.2 Customer flow

| #  | Page                                           | File                          |
| -- | ---------------------------------------------- | ----------------------------- |
| 6  | Reservation form                               | `06_reservation_form.png`     |
| 7  | "My Reservations" (with edit / cancel actions) | `07_my_reservations.png`     |
| 8  | Order cart with 5 items, total $56.00          | `08_cart.png`                 |
| 9  | Menu when logged in (with Add to order)        | `09_menu_logged_in.png`       |
| 10 | Order summary / receipt after checkout         | `10_order_summary.png`        |

### 5.3 Admin flow

| #  | Page                                           | File                          |
| -- | ---------------------------------------------- | ----------------------------- |
| 11 | Admin dashboard with stats                     | `11_admin_dashboard.png`      |
| 12 | Admin: list of menu items                      | `12_admin_menu_items.png`     |
| 13 | Admin: add new menu item form                  | `13_admin_add_item.png`       |
| 14 | Admin: categories (add / edit / list)          | `14_admin_categories.png`     |
| 15 | Admin: reservations (with confirm / cancel)    | `15_admin_reservations.png`   |
| 16 | Admin: orders (with status drop-down)          | `16_admin_orders.png`         |
| 17 | Admin: users (with promote / demote / delete)  | `17_admin_users.png`          |

---

## 6. Languages used in numbers

The final project contains:

- **34 PHP files** (including admin pages and includes)
- **1 CSS file** (`assets/css/style.css`, ~540 lines, custom design)
- **1 JavaScript file** (`assets/js/validation.js`, validates 5 forms)
- **1 SQL file** (`database/restaurant.sql`, 6 tables + seed data)
- **17 screenshots** of the running system

---

## 7. Tested functionality

The system was started locally on XAMPP (Apache 2.4.58 + PHP 8.2.12 +
MariaDB 10.4.32). The following pages were verified to return HTTP 200:

| Group              | Pages                                                                |
| ------------------ | -------------------------------------------------------------------- |
| Public             | `index.php`, `signup.php`, `login.php`, `menu.php`                   |
| Static assets      | `assets/css/style.css`, `assets/js/validation.js`                    |
| Customer pages     | `reservation.php`, `my_reservations.php`, `cart.php`, `order_summary.php` |
| Admin pages        | `admin/dashboard.php`, `admin/menu_items.php`, `admin/categories.php`, `admin/reservations.php`, `admin/orders.php`, `admin/users.php` |

The following user flows were exercised end-to-end and confirmed working:

1. Sign up → sign in → see "Welcome back" message.
2. Make a reservation → see it in **My Reservations** with `pending` status.
3. Edit a reservation → status returns to `pending`.
4. Cancel a reservation → status changes to `cancelled` and edit / cancel
   actions disappear.
5. Add menu items to the cart, change quantities, remove an item, clear,
   and finally **Place Order** → an `orders` row is created with the
   correct `total_price` and an `order_items` row per line, then the user
   is redirected to the order summary.
6. Sign in as admin → admin dashboard shows correct counts and revenue.
7. Add / edit / toggle / delete menu items.
8. Create a category, edit it, then delete it.
9. From the admin reservations page, confirm / cancel / delete a reservation.
10. From the admin orders page, change order status with the drop-down.
11. From the admin users page, promote and demote users.

---

## 8. Conclusion

This project gave us hands-on experience building a real, full-stack web
application from scratch. We practiced:

- **HTML** for structure, with semantic tags and shared header / footer
  includes.
- **CSS** for a modern, responsive design using CSS variables, Grid, and
  Flexbox.
- **JavaScript** for client-side validation with inline error messages and
  confirmation dialogs.
- **PHP** for server-side logic, sessions, role-based access control, form
  handling, and database access.
- **MySQL** for persistent storage with proper foreign keys and a clean
  schema.

We also applied software-engineering practices that we had only read about
until now: **prepared statements** to prevent SQL injection,
`htmlspecialchars()` everywhere to prevent XSS, **password hashing** with
bcrypt, **session id regeneration** after sign-in, **transactions** when
creating orders, and a clean separation between configuration, includes,
customer pages, and admin pages.

The end result is a working system that any small restaurant could
realistically deploy after some styling tweaks. More importantly for us, we
now understand how the pieces fit together: how a browser request flows
through Apache to PHP, how PHP talks to MySQL, how data comes back as HTML,
and how a `<form>` can be validated both in the browser and on the server.

# Bella Cucina - Restaurant Online Reservation and Ordering System

CMPE483 - Internet Programming II - Term Project

A complete web-based restaurant application built with HTML, CSS, JavaScript,
PHP, and MySQL.  Customers can sign up, sign in, browse the menu, reserve a
table, and place an order. Admins can manage menu items, categories,
reservations, orders, and users.

---

## Stack

| Layer        | Technology                            |
| ------------ | ------------------------------------- |
| Structure    | HTML5                                 |
| Styling      | CSS3 (custom, no framework)           |
| Interactivity| JavaScript (vanilla, form validation) |
| Server       | PHP 7.4+ / 8.x (mysqli)               |
| Database     | MySQL via XAMPP / phpMyAdmin          |

---

## Installation (XAMPP)

1. **Install XAMPP** from <https://www.apachefriends.org/>.
2. Copy this `restaurant` folder into XAMPP's `htdocs`:
   ```
   C:\xampp\htdocs\restaurant
   ```
3. Start **Apache** and **MySQL** from the XAMPP Control Panel.
4. Open <http://localhost/phpmyadmin>, click **Import**, and import
   `database/restaurant.sql`. This creates the `restaurant_db` database,
   all tables, and seed data (categories + menu items).
5. Open <http://localhost/restaurant/install.php> **once**. This script
   creates the default admin and demo customer accounts using
   `password_hash()` so the bcrypt hashes are valid.
6. Visit <http://localhost/restaurant/> to use the site.

> If your MySQL has a different username or password, change the
> credentials in `config/db.php`.

---

## Default accounts

| Role      | Email                     | Password   |
| --------- | ------------------------- | ---------- |
| Admin     | admin@restaurant.test     | admin123   |
| Customer  | user@restaurant.test      | user1234   |

You can also register a new account from `signup.php`.

---

## Project Structure

```
restaurant/
├── index.php                # Home page
├── signup.php               # Sign up form
├── login.php                # Sign in form
├── logout.php               # End session
├── menu.php                 # View menu, filter by category
├── reservation.php          # Make a reservation (customer)
├── my_reservations.php      # View / edit / cancel own reservations
├── update_reservation.php
├── cancel_reservation.php
├── cart.php                 # Add / update / remove items, place order
├── order_summary.php        # Receipt for a placed order
├── install.php              # One-time seed for admin / demo accounts
│
├── config/
│   └── db.php               # Database connection
├── includes/
│   ├── header.php           # Shared HTML header + nav
│   ├── footer.php           # Shared HTML footer + JS
│   └── functions.php        # Helpers (auth, flash, escape, etc.)
├── assets/
│   ├── css/style.css        # All styling
│   └── js/validation.js     # Client-side form validation
├── admin/
│   ├── dashboard.php        # Stats overview
│   ├── menu_items.php       # Manage menu items
│   ├── add_item.php
│   ├── edit_item.php
│   ├── categories.php       # Manage categories
│   ├── reservations.php     # Confirm / cancel / delete reservations
│   ├── orders.php           # Update order status, delete
│   └── users.php            # Promote / demote / delete users
└── database/
    └── restaurant.sql       # Full SQL dump
```

---

## Feature checklist (matches the assignment)

- [x] Sign up and sign in system (`signup.php`, `login.php`, `logout.php`)
- [x] User and admin roles (`users.role` ENUM; `require_admin()` guard)
- [x] Table reservation form (`reservation.php`)
- [x] View, update, and cancel reservations (`my_reservations.php`,
      `update_reservation.php`, `cancel_reservation.php`)
- [x] Menu page with food categories (`menu.php`)
- [x] Add, update, delete, and view menu items (admin CRUD)
- [x] Add items to an order (`cart.php` action=add)
- [x] View order summary and total price (`cart.php`, `order_summary.php`)
- [x] Database tables for users, reservations, menu items, categories,
      and orders (see `database/restaurant.sql`)
- [x] JavaScript validation (`assets/js/validation.js`)
- [x] CSS design (`assets/css/style.css`)

---

## Notes for graders

- Passwords are stored as bcrypt hashes (`password_hash` / `password_verify`).
- All SQL is executed through `mysqli` prepared statements with parameter
  binding to prevent SQL injection.
- All output uses `htmlspecialchars()` (`e()` helper) to prevent XSS.
- Sessions are protected with `session_regenerate_id` after sign-in.
- Forms validate on the **client** (JavaScript) and on the **server** (PHP).

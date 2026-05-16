# Bella Cucina - Restaurant Online Reservation and Ordering System

CMPE483 - Internet Programming II - Term Project

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

## Notes for grader :3

- Passwords are stored as bcrypt hashes (`password_hash` / `password_verify`).
- All SQL is executed through `mysqli` prepared statements with parameter
  binding to prevent SQL injection.
- All output uses `htmlspecialchars()` (`e()` helper) to prevent XSS.
- Sessions are protected with `session_regenerate_id` after sign-in.
- Forms validate on the **client** (JavaScript) and on the **server** (PHP).

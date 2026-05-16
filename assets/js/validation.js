/* =========================================================
   Bella Cucina - Client side form validation
   CMPE483 Term Project
   ========================================================= */
(function () {
    'use strict';

    /**
     * Show an inline error under a form field.
     * Each input that should be validated has a sibling
     * <span class="form-error" data-for="fieldName"></span>.
     */
    function setError(form, name, message) {
        var span = form.querySelector('.form-error[data-for="' + name + '"]');
        if (span) span.textContent = message || '';
    }

    function clearErrors(form) {
        var spans = form.querySelectorAll('.form-error');
        for (var i = 0; i < spans.length; i++) spans[i].textContent = '';
    }

    function value(form, name) {
        var el = form.elements[name];
        return el ? (el.value || '').trim() : '';
    }

    var emailRegex = /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/;
    var phoneRegex = /^[0-9+\-\s()]{7,20}$/;

    // ---------- Sign up ----------
    function validateSignup(form) {
        clearErrors(form);
        var ok = true;

        var name = value(form, 'full_name');
        if (name.length < 2) {
            setError(form, 'full_name', 'Please enter your full name.');
            ok = false;
        }

        var email = value(form, 'email');
        if (!emailRegex.test(email)) {
            setError(form, 'email', 'Please enter a valid email address.');
            ok = false;
        }

        var phone = value(form, 'phone');
        if (phone && !phoneRegex.test(phone)) {
            setError(form, 'phone', 'Phone number looks invalid.');
            ok = false;
        }

        var pass = value(form, 'password');
        if (pass.length < 6) {
            setError(form, 'password', 'Password must be at least 6 characters.');
            ok = false;
        }

        var confirm = value(form, 'confirm_password');
        if (confirm !== pass) {
            setError(form, 'confirm_password', 'Passwords do not match.');
            ok = false;
        }

        return ok;
    }

    // ---------- Login ----------
    function validateLogin(form) {
        clearErrors(form);
        var ok = true;

        var email = value(form, 'email');
        if (!emailRegex.test(email)) {
            setError(form, 'email', 'Please enter a valid email address.');
            ok = false;
        }

        if (value(form, 'password').length === 0) {
            setError(form, 'password', 'Password is required.');
            ok = false;
        }
        return ok;
    }

    // ---------- Reservation ----------
    function validateReservation(form) {
        clearErrors(form);
        var ok = true;

        var date = value(form, 'reservation_date');
        if (!date) {
            setError(form, 'reservation_date', 'Please choose a date.');
            ok = false;
        } else {
            var today = new Date();
            today.setHours(0, 0, 0, 0);
            var picked = new Date(date);
            if (picked < today) {
                setError(form, 'reservation_date', 'Date cannot be in the past.');
                ok = false;
            }
        }

        if (!value(form, 'reservation_time')) {
            setError(form, 'reservation_time', 'Please choose a time.');
            ok = false;
        }

        var guests = parseInt(value(form, 'guests'), 10);
        if (isNaN(guests) || guests < 1 || guests > 20) {
            setError(form, 'guests', 'Guests must be between 1 and 20.');
            ok = false;
        }
        return ok;
    }

    // ---------- Menu item (admin) ----------
    function validateMenuItem(form) {
        clearErrors(form);
        var ok = true;

        if (value(form, 'name').length < 2) {
            setError(form, 'name', 'Item name is required.');
            ok = false;
        }
        if (!value(form, 'category_id')) {
            setError(form, 'category_id', 'Please choose a category.');
            ok = false;
        }
        var price = parseFloat(value(form, 'price'));
        if (isNaN(price) || price <= 0) {
            setError(form, 'price', 'Price must be a positive number.');
            ok = false;
        }
        return ok;
    }

    // ---------- Category (admin) ----------
    function validateCategory(form) {
        clearErrors(form);
        if (value(form, 'name').length < 2) {
            setError(form, 'name', 'Category name is required.');
            return false;
        }
        return true;
    }

    var validators = {
        'signup-form':      validateSignup,
        'login-form':       validateLogin,
        'reservation-form': validateReservation,
        'menu-item-form':   validateMenuItem,
        'category-form':    validateCategory
    };

    document.addEventListener('DOMContentLoaded', function () {
        Object.keys(validators).forEach(function (id) {
            var form = document.getElementById(id);
            if (!form) return;
            form.addEventListener('submit', function (ev) {
                if (!validators[id](form)) ev.preventDefault();
            });
        });

        // Generic confirm-before-action for buttons / links with data-confirm
        var confirmEls = document.querySelectorAll('[data-confirm]');
        for (var i = 0; i < confirmEls.length; i++) {
            confirmEls[i].addEventListener('click', function (ev) {
                if (!confirm(this.getAttribute('data-confirm'))) ev.preventDefault();
            });
        }
    });
})();

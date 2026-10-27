# Ticketly - PHP Ticket Booking System

A responsive event ticket booking demo built with PHP 8+, MySQL, HTML and CSS.

## Features
- Browse upcoming events
- Search events by name or venue
- Book tickets with name, email and quantity
- Server-side validation and prepared SQL statements
- Transaction-safe ticket availability updates
- Booking confirmation with reference number
- Simple admin dashboard to add events and view bookings
- Responsive dark/neon interface

## Requirements
- PHP 8.0+
- MySQL 5.7+ / MariaDB
- Apache (XAMPP, WAMP, MAMP or similar)

## Setup with XAMPP
1. Extract this folder into `C:\xampp\htdocs\php-ticket-booking-system`.
2. Start Apache and MySQL in XAMPP.
3. Open phpMyAdmin at `http://localhost/phpmyadmin`.
4. Import `database.sql`.
5. Check `config.php` and set your database username/password if needed. Default XAMPP settings are usually user `root`, blank password.
6. Open `http://localhost/php-ticket-booking-system/`.

## Admin page
Open `/admin.php`. Demo credentials:
- Username: `admin`
- Password: `ChangeMe123!`

Change the password hash in `config.php` before deploying this project publicly. This demo admin login is intentionally simple for learning; add CSRF protection, rate limiting, and stronger deployment configuration before production use.

## Important notes
- This is a learning/demo project, not a production ticketing platform.
- It does not process online payments.
- Do not use real customer data on a public deployment without further security review.

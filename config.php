<?php
declare(strict_types=1);

session_start();

const DB_HOST = 'localhost';
const DB_NAME = 'ticketly';
const DB_USER = 'root';
const DB_PASS = '';

/*
 * Demo admin credentials:
 * username: admin
 * password: ChangeMe123!
 * Replace this hash with password_hash('your-new-password', PASSWORD_DEFAULT)
 * before publishing the application.
 */
const ADMIN_USERNAME = 'admin';
const ADMIN_PASSWORD_HASH = '$2y$12$N8hZ1Q0gweWA0.4fppRcoOKBBEB1iNqqfBAGpgUcrxisbfI3b7VVe';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function db(): mysqli {
    static $connection = null;
    if ($connection === null) {
        $connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $connection->set_charset('utf8mb4');
    }
    return $connection;
}

function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function money(float|int|string $amount): string {
    return 'R' . number_format((float)$amount, 2);
}

function redirect(string $url): never {
    header('Location: ' . $url);
    exit;
}

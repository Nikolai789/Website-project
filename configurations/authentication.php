<?php

/**
 * Ensure the user is logged in.
 * If not, redirect to login page.
 */
function requireLogin(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['user_id'])) {
        header("Location: /login.php");
        exit;
    }
}

/**
 * Ensure the user is logged in AND is an admin.
 * If not logged in, redirect to login.
 * If logged in but not admin, redirect to index.
 */
function requireAdmin(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['user_id'])) {
        header("Location: /login.php");
        exit;
    }

    if ($_SESSION['user_role'] !== 'admin') {
        header("Location: /index.php");
        exit;
    }
}

/**
 * Ensure the user is logged in AND is a customer.
 * If not logged in, redirect to login.
 * If logged in but not a customer (e.g. admin), redirect to admin page.
 */
function requireCustomer(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['user_id'])) {
        header("Location: /login.php");
        exit;
    }

    if ($_SESSION['user_role'] !== 'customer') {
        header("Location: /admin.php");
        exit;
    }
}
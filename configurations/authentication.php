<?php

/**
 * Build an absolute URL path inside this project, regardless of nested script location.
 */
function appPath(string $relativePath): string {
    $projectRoot = str_replace('\\', '/', realpath(__DIR__ . '/..') ?: '');
    $documentRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '');

    $basePath = '';
    if ($projectRoot !== '' && $documentRoot !== '' && strpos($projectRoot, $documentRoot) === 0) {
        $basePath = substr($projectRoot, strlen($documentRoot));
    }

    $basePath = '/' . trim($basePath, '/');
    if ($basePath === '/') {
        return '/' . ltrim($relativePath, '/');
    }

    return $basePath . '/' . ltrim($relativePath, '/');
}

/**
 * Ensure the user is logged in.
 * If not, redirect to login page.
 */
function requireLogin(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['user_id'])) {
        header('Location: ' . appPath('login.php'));
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
        header('Location: ' . appPath('index.php'));
        exit;
    }

    if (($_SESSION['user_role'] ?? '') !== 'admin') {
        header('Location: ' . appPath('index.php'));
        exit;
    }
}

/**
 * Ensure the user is logged in AND is a regular user.
 * If not logged in, redirect to login.
 * If logged in but not a regular user (e.g. admin), redirect to admin page.
 */
function requireCustomer(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['user_id'])) {
        header('Location: ' . appPath('login.php'));
        exit;
    }

    if (($_SESSION['user_role'] ?? '') !== 'user') {
        header('Location: ' . appPath('admin.php'));
        exit;
    }
}

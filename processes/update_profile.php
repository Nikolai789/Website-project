<?php

session_start();
require_once __DIR__ . "/../configurations/config.php";

if (empty($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_POST['update_profile'])) {
    header("Location: ../edit_profile.php");
    exit();
}

// ── Sanitize inputs ──
$user_id      = $_SESSION['user_id'];
$username     = trim($_POST['username']      ?? '');
$email        = trim($_POST['email']         ?? '');
$cur_password = $_POST['current_password']   ?? '';
$new_password = $_POST['new_password']       ?? '';
$address      = trim($_POST['address']       ?? '');

// ── Basic validation ──
if (empty($username) || empty($email)) {
    $_SESSION['edit_error'] = 'Username and email are required.';
    header("Location: ../edit_profile.php");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['edit_error'] = 'Please enter a valid email address.';
    header("Location: ../edit_profile.php");
    exit();
}

// ── Password change ──
if (!empty($new_password)) {
    if (empty($cur_password)) {
        $_SESSION['edit_error'] = 'Please enter your current password to set a new one.';
        header("Location: ../edit_profile.php");
        exit();
    }

    if (strlen($new_password) < 6) {
        $_SESSION['edit_error'] = 'New password must be at least 6 characters.';
        header("Location: ../edit_profile.php");
        exit();
    }

    // Verify current password against DB
    $res = $conn->query("SELECT password FROM users WHERE user_id = '$user_id'");
    $row = $res->fetch_assoc();
    if (!password_verify($cur_password, $row['password'])) {
        $_SESSION['edit_error'] = 'Current password is incorrect.';
        header("Location: ../edit_profile.php");
        exit();
    }

    // Hash and update password
    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
    $conn->query("UPDATE users SET password = '$hashed' WHERE user_id = '$user_id'");
}

// ── Update username, email, address in DB ──
$username = $conn->real_escape_string($username);
$email    = $conn->real_escape_string($email);
$address  = $conn->real_escape_string($address);

$conn->query("UPDATE users SET
    username = '$username',
    email    = '$email',
    address  = '$address'
    WHERE user_id = '$user_id'");

if ($conn->affected_rows === -1) {
    $_SESSION['edit_error'] = 'Something went wrong. Please try again.';
    header("Location: ../edit_profile.php");
    exit();
}

// ── Update session to reflect changes ──
$_SESSION['username'] = $username;
$_SESSION['email']    = $email;
$_SESSION['address']  = $address;

$_SESSION['edit_success'] = 'Profile updated successfully!';
header("Location: ../edit_profile.php");
exit();
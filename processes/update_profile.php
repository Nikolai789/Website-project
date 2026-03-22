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
$user_id      = (int) $_SESSION['user_id'];
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

    // Verify current password against DB
    $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row || !password_verify($cur_password, $row['password'])) {
        $_SESSION['edit_error'] = 'Current password is incorrect.';
        header("Location: ../edit_profile.php");
        exit();
    }

    // Hash and update password
    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    $stmt->bind_param("si", $hashed, $user_id);
    $stmt->execute();
    $stmt->close();
}

// ── Update username, email, address in DB ──
$stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, address = ? WHERE user_id = ?");
$stmt->bind_param("sssi", $username, $email, $address, $user_id);
$stmt->execute();

if ($stmt->errno) {
    $stmt->close();
    $_SESSION['edit_error'] = 'Something went wrong. Please try again.';
    header("Location: ../edit_profile.php");
    exit();
}

$stmt->close();

// ── Update session to reflect changes ──
$_SESSION['username'] = $username;
$_SESSION['email']    = $email;
$_SESSION['address']  = $address;

$_SESSION['edit_success'] = 'Profile updated successfully!';
header("Location: ../edit_profile.php");
exit();
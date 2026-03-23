<?php
session_start();
require_once __DIR__ . "/../configurations/config.php";

if (isset($_POST['register'])) {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';

        // Validate password length
    if (strlen($password) < 6) {
        $_SESSION['register_error'] = 'Password must be at least 6 characters.';
        $_SESSION['active_form']    = 'register';
        header("Location: ../login.php");
        exit();
    }
    

    // Check if email is already registered
    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        $_SESSION['register_error'] = 'Email is already registered!';
        $_SESSION['active_form']    = 'register';
        header("Location: ../login.php");
        exit();
    }
    $stmt->close();

    // Insert new user
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $hashed);
    $stmt->execute();
    $stmt->close();

    header("Location: ../login.php");
    exit();
}

if (isset($_POST['login'])) {
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';

    $stmt = $conn->prepare("SELECT user_id, username, email, password, user_role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']  = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email']    = $user['email'];
        $_SESSION['user_role'] = $user['user_role'];

        if ($user['user_role'] === 'admin') {
            header("Location: ../admin.php");
        } else {
            header("Location: ../index.php");
        }
        exit();
    }

    $_SESSION['login_error'] = 'Incorrect email or password!';
    $_SESSION['active_form'] = 'login';
    header("Location: ../login.php");
    exit();
}

// Fallback — neither button was submitted
header("Location: ../login.php");
exit();
<?php

session_start();

$errors = [
    'login'    => $_SESSION['login_error']    ?? '',
    'register' => $_SESSION['register_error'] ?? ''
];
$activeForm = $_SESSION['active_form'] ?? 'login';

session_unset();

function showError($error) {
    return !empty($error)
        ? "<p class='error-message'>" . htmlspecialchars($error) . "</p>"
        : '';
}

function isActiveForm($formName, $activeForm) {
    return $formName === $activeForm ? 'active' : '';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Register</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="css/navigation.css">
</head>

<body>
    <div class="login_title">
        <h1>Gear<span class="Hub_login_text">Hub</span></h1>
        <h3>Online Computer Peripheral Store</h3>
    </div>


    <div class="container">
        <div class="form-box <?= isActiveForm('login', $activeForm); ?>" id="login-form">
            <form action="processes/login_register.php" method="post">
                <a href="index.php" class="back-home-link">Back</a>
                <h2>Login</h2>
                <?= showError($errors['login']) ?>
                <input type="email" name="email" placeholder="email" required>
                <input type="password" name="password" placeholder="password" required>
                <input type="hidden" name="login_portal" value="user">
                <button type="submit" name="login">Login</button>
                <p><a class="admin-login-link" href="admin_login.php">Login as Admin</a></p>
                <p>Don't have an account? <a href="#" onclick="showForm('register-form')">Register</a></p>
            </form>
        </div>

        <div class="form-box <?= isActiveForm('register', $activeForm); ?>" id="register-form">
            <form action="processes/login_register.php" method="post">
                <a href="index.php" class="back-home-link">Back</a>
                <h2>Register</h2>
                <?= showError($errors['register']) ?>
                <input type="text" name="username" placeholder="username" required>
                <input type="email" name="email" placeholder="email" required>
                <input type="password" name="password" placeholder="password" required>
                <button type="submit" name="register">Register</button>
                <p>Already have an account? <a href="#" onclick="showForm('login-form')">Login</a></p>
            </form>
        </div>
    </div>

    <div class="login_bottom_text">
        <p>GearHub © 2026. All rights reserved.</p>
    </div>

    <script src="javascript/login.js"></script>

</body>
</html>

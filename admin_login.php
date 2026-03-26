<?php

session_start();

$adminLoginError = $_SESSION['admin_login_error'] ?? '';
unset($_SESSION['admin_login_error']);

function showAdminError(string $error): string {
    return $error !== ''
        ? "<p class='error-message'>" . htmlspecialchars($error) . "</p>"
        : '';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="css/navigation.css">
</head>

<body>
    <div class="login_title">
        <h1>Gear<span class="Hub_login_text">Hub</span></h1>
        <h3>Admin Access Portal</h3>
    </div>

    <div class="container">
        <div class="form-box active" id="admin-login-form">
            <form action="processes/login_register.php" method="post">
                <a href="login.php" style="text-decoration: none; color: rgb(255, 255, 255);">Back to user login</a>
                <h2>Admin Login</h2>
                <?= showAdminError($adminLoginError) ?>
                <input type="email" name="email" placeholder="admin email" required>
                <input type="password" name="password" placeholder="password" required>
                <input type="hidden" name="login_portal" value="admin">
                <button type="submit" name="login">Login</button>
            </form>
        </div>
    </div>

    <div class="login_bottom_text">
        <p>GearHub © 2026. All rights reserved.</p>
    </div>
</body>
</html>

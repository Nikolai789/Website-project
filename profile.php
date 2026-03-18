<?php

session_start();

if (empty($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/navigation.css">
</head>
<body>
    <?php include "includes/nav.php"; ?>

    <main style="max-width: 900px; margin: 32px auto; padding: 0 16px;">
        <h2 style="margin-bottom: 8px;">User profile</h2>
        <p style="margin-top: 0;">
            Logged in as <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
            (<?= htmlspecialchars($_SESSION['email'] ?? '') ?>)
        </p>
    </main>
</body>
</html>


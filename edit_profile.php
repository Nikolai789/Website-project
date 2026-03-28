<?php

session_start();

if (empty($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$success = $_SESSION['edit_success'] ?? '';
$error   = $_SESSION['edit_error']   ?? '';
unset($_SESSION['edit_success'], $_SESSION['edit_error']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/navigation.css">
    <link rel="stylesheet" href="css/edit_profile.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Exo+2:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>
    <?php include "includes/nav.php"; ?>

    <main class="edit-wrapper">

        <a href="profile.php" class="back-link">
            <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
            Back to profile
        </a>

        <div class="edit-header">
            <h2><span class="edit_text">Edit</span> <span class="profile_text2">Profile</span></h2>
            <div class="header-line"></div>
        </div>

        <?php if ($success): ?>
            <div class="form-message success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="form-message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="processes/update_profile.php" method="post">
            <div class="edit-card">

                <!-- ── Account Info ── -->
                <div class="edit-section">
                    <div class="edit-section-title">Account Info</div>

                    <div class="field-row">
                        <div class="field-group">
                            <label for="username">Username</label>
                            <input
                                type="text"
                                id="username"
                                name="username"
                                value="<?= htmlspecialchars($_SESSION['username']) ?>"
                                placeholder="Your username"
                                required
                            >
                        </div>
                        <div class="field-group">
                            <label for="email">Email</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>"
                                placeholder="your@email.com"
                                required
                            >
                        </div>
                    </div>
                </div>

                <!-- ── Change Password ── -->
                <div class="edit-section">
                    <div class="edit-section-title">Change Password</div>

                    <div class="field-row">
                        <div class="field-group">
                            <label for="current_password">Current Password</label>
                            <input
                                type="password"
                                id="current_password"
                                name="current_password"
                                placeholder="Enter current password"
                            >
                        </div>
                        <div class="field-group">
                            <label for="new_password">New Password</label>
                            <input
                                type="password"
                                id="new_password"
                                name="new_password"
                                placeholder="Enter new password"
                            >
                            <span class="field-hint">Leave blank to keep current password</span>
                        </div>
                    </div>
                </div>

                <!-- ── Delivery Address ── -->
                <div class="edit-section">
                    <div class="edit-section-title">Delivery Address</div>

                    <div class="field-row full">
                        <div class="field-group">
                            <label for="address">Address</label>
                            <textarea
                                id="address"
                                name="address"
                                placeholder="Enter your full delivery address"
                            ><?= htmlspecialchars($_SESSION['address'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

            </div><!-- /.edit-card -->

            <!-- ── Actions ── -->
            <div class="edit-actions edit-actions-plain">
                <a href="profile.php" class="btn btn-ghost">Cancel</a>
                <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
            </div>

        </form>

    </main>
</body>
</html>

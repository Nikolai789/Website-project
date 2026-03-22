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
    <link rel="stylesheet" href="css/profile.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
</head>
<body>
    <?php include "includes/nav.php"; ?>
 
    <main class="profile-wrapper">
 
        <div class="profile-header">
            <h2> <span class="user_text">User</span> <span class="profile_text">Profile</span></h2>
            <div class="header-line"></div>
        </div>
 
        <div class="profile-card">
 
            <!-- Banner -->
            <div class="profile-banner">
                <div class="banner-grid"></div>
            </div>
 
            <!-- Identity -->
            <div class="profile-identity">
                <div class="avatar-wrap">
                    <div class="avatar">
                        <?= strtoupper(substr(htmlspecialchars($_SESSION['username']), 0, 2)) ?>
                    </div>
                    <div class="avatar-ring"></div>
                </div>
                <div class="profile-name-block">
                    <div class="profile-name"><?= htmlspecialchars($_SESSION['username']) ?></div>
                    <div class="profile-email"><?= htmlspecialchars($_SESSION['email'] ?? '') ?></div>
                </div>
            </div>
 
            <div class="card-divider"></div>
 
            <!-- Info Grid -->
            <div class="profile-info">
                <div class="info-cell">
                    <div class="info-label">Username</div>
                    <div class="info-value accent"><?= htmlspecialchars($_SESSION['username']) ?></div>
                </div>
                <div class="info-cell">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?= htmlspecialchars($_SESSION['email'] ?? '—') ?></div>
                </div>
                <div class="info-cell">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        <span class="status-badge">
                            <span class="status-dot"></span> Active
                        </span>
                    </div>
                </div>
            </div>
 
            <!-- Actions -->
            <div class="profile-actions">
                <a href="edit_profile.php" class="btn btn-primary">Edit Profile</a>
                <a href="logout.php" class="btn btn-ghost">Logout</a>
            </div>
 
        </div> 
    </main>
</body>
</html>
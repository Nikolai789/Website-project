<?php
// Ensure session is available for conditional nav rendering.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

    <nav>
        <header class = "navbar">
            <div class = "brand">Gear<span class="green-text">Hub</span></div>
            <div class = "links">
                <a data-active="index" href="index.php">Home</a>
                <a data-active="about" href="about.php">About</a>
                <a data-active="contact" href="contact.php">Contact</a>
            </div>

            <div class = "registerlogin">
                <?php if (!empty($_SESSION['username'])): ?>
                    <a href="profile.php"><?= htmlspecialchars($_SESSION['username']) ?></a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                <?php endif; ?>
            </div>
        </header>
    </nav>
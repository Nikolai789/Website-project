<?php
require_once __DIR__ . '/../configurations/url_helpers.php';

// Ensure session is available for conditional nav rendering.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<head> 
    <link href="https://fonts.googleapis.com/css2?family=Exo+2&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(app_url('css/style.css')) ?>">
</head>

    <nav>
        <header class = "navbar">
            <div class = "brand">Gear<span class="green-text">Hub</span></div>
            <div class = "links">
                <a data-active="index" href="<?= htmlspecialchars(app_url('index.php')) ?>">Home</a>
                <a data-active="about" href="<?= htmlspecialchars(app_url('about.php')) ?>">About</a>
                <a data-active="contact" href="<?= htmlspecialchars(app_url('contact.php')) ?>">Contact</a>
            </div>

            <div class = "registerlogin">
                <?php if (!empty($_SESSION['username'])): ?>
                    <a href="<?= htmlspecialchars(app_url('profile.php')) ?>"><?= htmlspecialchars($_SESSION['username']) ?></a>
                    <a href="<?= htmlspecialchars(app_url('logout.php')) ?>">Logout</a>
                <?php else: ?>
                    <a href="<?= htmlspecialchars(app_url('login.php')) ?>">Login</a>
                <?php endif; ?>
            </div>
        </header>

        <script>
            document.querySelectorAll('nav a').forEach(link => {
                link.addEventListener('click', e => {
                    const href = link.getAttribute('href');
                    // skip if same page, anchor link, or external
                    if (!href || href.startsWith('#') || href.startsWith('http')) return;

                    e.preventDefault();
                    document.body.classList.add('leaving');

                    setTimeout(() => {
                        window.location.href = href;
                    }, 250); // match pageOut duration
                });
            });
        </script>
    </nav>

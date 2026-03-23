<?php
// Ensure session is available for conditional nav rendering.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<head> 
    <link href="https://fonts.googleapis.com/css2?family=Exo+2&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/logout_modal.css">
</head>

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
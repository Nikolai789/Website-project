<?php require_once __DIR__ . '/../configurations/url_helpers.php'; ?>

<nav>
        <div class="nav2">

            <script>
                function toggleMode() {
                    document.body.classList.toggle("dark-mode");
                }
            </script>

            <div class="search-bar">
                <form method="GET" action="<?= htmlspecialchars(app_url('index.php')) ?>" class="search-form">
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Search for products"
                        value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                    >
                    <button type="submit" class="search-submit" aria-label="Search products"></button>
                </form>
            </div>

            <a class="cart" href="<?= htmlspecialchars(app_url('check-out/checkout.php')) ?>">🛒 Cart</a>
            
        </div>
    
</nav>

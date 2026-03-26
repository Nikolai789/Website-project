<nav>
        <div class="nav2">

            <script>
                function toggleMode() {
                    document.body.classList.toggle("dark-mode");
                }
            </script>

            <div class="search-bar">
                <form method="GET" action="index.php" class="search-form">
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Search for products"
                        value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                    >
                    <button type="submit" class="search-submit" aria-label="Search products"></button>
                </form>
            </div>

            <a class="cart" href="check-out/checkout.php">🛒 Cart</a>
            
        </div>
    
</nav>

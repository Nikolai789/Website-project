<nav>
        <div class="nav2">

            <script>
                function toggleMode() {
                    document.body.classList.toggle("dark-mode");
                }
            </script>

            <div class="search-bar">
                <form method="GET" action="index.php" style="display:flex; align-items:center;">
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Search for products"
                        value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                    >
                    <button type="submit" style="background:none; border:none; cursor:pointer;"></button>
                </form>
            </div>

            <a class="cart" href="check-out/checkout.php">🛒 Cart</a>
            
        </div>
    
</nav>
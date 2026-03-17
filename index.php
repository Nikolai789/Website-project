<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- these two tags are important for SEO to understand our website -->
    <meta name="description" content="we have a wide selection of gadget peripherals">
    <meta name="keywords" content= "mouse, keyboards, headphones"> 

    <link rel="stylesheet" href="css/style.css">
    
    <link rel="stylesheet" href="css/header.css">

    <link rel="stylesheet" href="css/main.css">
    <title>Periph</title>

</head>
<body>
    <?php include "includes/nav.php" ?>
    <?php include "includes/second_nav.php" ?>
    <?php include "includes/header.php" ?>
 

    <main>
        <div class="left">
            <div class="section-title">Product Categories</div>
            <a href="">Mouse</a>
            <a href="">Keyboard</a>
        </div>

        <div class="right">
            <div class="section-title">Products</div>
            <div class="product">
                <div class="product-left">
                    <img src="product_images/Mouse.webp" alt="Office Mouse" class="product-img">
                </div>
                <div class="product-right">
                    <p class="name">
                        <a href="">Logitech M331</a>
                    </p>

                    <p class="description">The Logitech M331 Silent Plus is a wireless mouse engineered for those who prioritize a quiet, focused workspace without sacrificing performance. Designed with comfort and longevity in mind, it is an ideal companion for busy office environments, shared workspaces, or late-night creative sessions at home.</p>
                    <div class="price-row">
                        <p class="price">
                            <span class="currency">₱</span>930.00 
                        </p>

                        <p class="stock">
                            Stock: <span class="yellow">1</span>
                        </p>
                    </div>
                    
                </div>
            </div>
        </div>
    </main>
    
    <?php include "includes/footer.php" ?>

    <script src="javascript/script.js"></script>
</body>
</html>
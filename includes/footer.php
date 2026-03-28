<?php
require_once __DIR__ . '/../configurations/url_helpers.php';
?>
<footer class="footer">
    <div class="footer-top">
        
        <!-- LEFT SIDE -->
        <div class="footer-left">
            <div class="footer-item">
                <p>Learn more about us</p>
                <a href="<?= htmlspecialchars(app_url('about.php')) ?>" class="footer-btn">About Us</a>
            </div>

            <div class="footer-item">
                <p>Need help? Email us!</p>
                <a href="<?= htmlspecialchars(app_url('contact.php')) ?>" class="footer-btn">Contact</a>
            </div>
        </div>

        <!-- DIVIDER -->
        <div class="footer-divider"></div>

        <!-- RIGHT SIDE -->
        <div class="footer-right">
            <p class="follow-text">Follow Us</p>

            <div class="social-icons">
                <a href="https://www.facebook.com/whysharethislinkyoumf" target="_blank"> 
                    <img src="<?= htmlspecialchars(app_url('Assets/Icons/facebook-logo-facebook-social-media-icon-free-png.png')) ?>" alt="Facebook">
                </a>
                <a href="https://www.instagram.com/pinyatooo_arts1?fbclid=IwY2xjawQrPGxleHRuA2FlbQIxMABicmlkETExd3FVemNUVFEzdWxFWDdmc3J0YwZhcHBfaWQQMjIyMDM5MTc4ODIwMDg5MgABHpJB2tM_zXoInX87Y-cJ1FF-dEz761AmoIBe1KkVvfITCA9iUcWGG-RjSStU_aem_dKmc10fdxe7PEC5OyY4hKQ" target="_blank">
                    <img src="<?= htmlspecialchars(app_url('Assets/Icons/instagram-button-icon-set-instagram-screen-social-media-and-social-network-interface-template-stories-user-button-symbol-sign-logo-stories-liked-editorial-free-png.png')) ?>" alt="Instagram">
                </a>
                <a href="https://x.com/masoq095" target="_blank">
                    <img src="<?= htmlspecialchars(app_url('Assets/Icons/new-twitter-x-logo-twitter-icon-x-social-media-icon-free-png.png')) ?>" alt="Twitter">
                </a>
            </div>
        </div>

    </div>

    <!-- BOTTOM -->
    <div class="footer-bottom">
        <p>GearHub © <?php echo date("Y"); ?>. All Rights Reserved </p>
    </div>
</footer>

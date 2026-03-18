<?php
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'success') {
        echo "<p style='color:green; text-align:center; font-weight:bold;'>✅ Your message was sent successfully!</p>";
    } elseif ($_GET['status'] == 'error') {
        echo "<p style='color:red; text-align:center; font-weight:bold;'>❌ Oops! Something went wrong. Please try again.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- these two tags are important for SEO to understand our website -->
    <meta name="description" content="we have a wide selection of gadget peripherals">
    <meta name="keywords" content= "mouse, keyboards, headphones"> 

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/contact.css">
    <link rel="stylesheet" href="css/navigation.css">
    <title>Periph</title>

</head>
<body>
    <?php include "includes/nav.php" ?>

    <main class="Contact">
        <div class="contact_intro">
            <h1>Contact Us</h1>
            <p>If you have any questions or feedback, feel free to reach out to us!</p>
        </div>

        <hr>

        <h2>Contact Form</h2>
        <form action="send_email.php" method="post">
            <label for="name">Name:</label><br>
            <input type="text" id="name" name="name" required><br><br>

            <label for="email">Email:</label><br>
            <input type="email" id="email" name="email" required><br><br>

            <label for="message">Message:</label><br>
            <textarea id="message" name="message" rows="5" required></textarea><br><br>

            <input type="submit" value="Submit">
        </form>

        <div class="contact_details">
            <h2>Our Contact Details</h2>
            <p><strong>Email:</strong> dummy@gmail.comp</p>
            <p><strong>Phone:</strong> +1 234 567 890</p>
            <p><strong>Address:</strong> 123 Main Street, Anytown, USA</p>
        </div>
    </main>
    <?php include "includes/footer.php" ?>

    <script>src="javascript/script.js"</script>
</body>
</html>
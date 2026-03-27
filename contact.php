<?php
$status = $_GET['status'] ?? '';
$statusMessages = [
    'success' => 'Your message was sent successfully!',
    'error' => 'Oops! Something went wrong. Please try again.',
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- these two tags are important for SEO to understand our website -->
    <meta name="description" content="we have a wide selection of gadget peripherals">
    <meta name="keywords" content="mouse, keyboards, headphones">

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/contact.css">
    <link rel="stylesheet" href="css/navigation.css">
    <title>Contact GearHub</title>

</head>
<body>
    <?php include "includes/nav.php" ?>

    <main class="Contact">
        <?php if (isset($statusMessages[$status])): ?>
            <p class="msg <?= $status === 'success' ? 'msg-success' : 'msg-error' ?>">
                <?= htmlspecialchars($statusMessages[$status]) ?>
            </p>
        <?php endif; ?>

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
            <p><strong>Email:</strong> gearhub21@gmail.com</p>
            <p><strong>Phone:</strong> +1 234 567 890</p>
            <p><strong>Address:</strong> Apokon, Davao del Norte, Philippines</p>
        </div>
    </main>
    <?php include "includes/footer.php" ?>

    <script src="javascript/script.js"></script>
</body>
</html>

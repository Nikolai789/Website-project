<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Only process POST requests
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $message = htmlspecialchars($_POST['message']);

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['GMAIL_USER']; // from .env
        $mail->Password = $_ENV['GMAIL_PASS']; // from .env
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Recipients
        $mail->setFrom($_ENV['GMAIL_USER'], 'GearHub Team'); // sender shows website name
        $mail->addAddress($_ENV['GMAIL_USER']);              // recipient = your Gmail
        $mail->addReplyTo($email, $name);                    // user email

        // Content
        $mail->isHTML(false);
        $mail->Subject = "Contact Form Message from $name";
        $mail->Body    = "Name: $name\nEmail: $email\n\nMessage:\n$message";

        $mail->send();

        // Redirect back to contact page with success status
        header("Location: contact.php?status=success");
        exit;

    } catch (Exception $e) {
        header("Location: contact.php?status=error");
        exit;
    }
} else {
    header("Location: contact.php");
    exit;
}

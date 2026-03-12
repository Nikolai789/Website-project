<?php

session_start(); // this fucntion starts the session which allows us to store certain data that can access across pages during user session
require_once "config.php"; // this is contains the configuration to connect to the database

if (isset($_POST['register'])) { // this checks if the register button is clicked
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // this hash the password and encrypts it

    $checkEmail = $conn->query("SELECT email FROM users WHERE email = '$email'"); // query checks if the email is already registered
    if ($checkEmail->num_rows > 0) {
        $_SESSION['register_error'] = 'Email is already registered!';
        $_SESSION['active_form'] = 'register'; 
    } else {
        $conn->query("INSERT INTO customers (username, email, password) VALUES ('$username', '$email', '$password')");
    }
    header("Location: index.php");// this redirects the user to the main page after registering
    exit();
}

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE email = '$email'");
    if ($resukt->num_rows > 0) {
        $user = $resukt->fetch_assoc();
        if (password_verify($password, $user['password'])) { // this checks if the password is correct
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            header("Location: index.php"); // this redirects the user to the main page after logging in
        }
        exit();
    } 
}
$_SESSION['login_error'] = 'Incorrect email or password!';
$_SESSION['active_form'] = 'login';
header("Location: login.php");
exit();
?>
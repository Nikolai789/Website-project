<?php
require_once __DIR__ . "/database_settings.php";

// this is where we connect to the database
$host = DB_HOST;        // this stores the server address
$user = DB_USER;        // users who have access to mysql
$password = DB_PASSWORD;// password of the db
$database = DB_NAME;    // database name, databse that wel access

$conn = new mysqli($host, $user, $password, $database); // this stores databse connection, it automatically connect to the database

// check connection if there are errors, this code runs if there is an error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

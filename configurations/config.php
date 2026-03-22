<?php
// this is where we connect to the database
$host = "localhost";    // this stores the server address
$user = "root";         // users who have access to mysql
$password = "";         // password of the db
$database = "gearhubDB";// database name, databse that wel access

$conn = new mysqli($host, $user, $password, $database); // this stores databse connection, it automatically connect to the database

// check connection if there are errors, this code runs if there is an error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
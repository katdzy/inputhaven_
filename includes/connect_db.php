<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$database = "shop";
$conn = new mysqli($servername, $username, $password, $database);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>


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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../images/key_fav.png">
</head>
<body>
</body>
</html>

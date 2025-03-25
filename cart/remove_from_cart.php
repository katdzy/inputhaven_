<?php
session_start();
$conn = new mysqli("localhost", "root", "", "shop");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
if (!isset($_GET["product_id"])) {
    die("Product ID not provided.");
}
$product_id = $_GET["product_id"];

$delete_query = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
$delete_query->bind_param("si", $user_id, $product_id);
$delete_query->execute();
$delete_query->close();

header("Location: cart_view.php");
exit();
?>

<?php
require_once '../includes/connect_db.php';

// Check if user is logged in
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "client") {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION["user_id"];

// Include database connection


// Fetch cart count
$cart_count = 0;
$cart_count_query = $conn->prepare("SELECT COUNT(DISTINCT product_id) AS total FROM cart WHERE user_id = ?");
if ($cart_count_query) {
    $cart_count_query->bind_param("s", $user_id);
    $cart_count_query->execute();
    $cart_count_result = $cart_count_query->get_result();
    if ($cart_count_result) {
        $cart_count_row = $cart_count_result->fetch_assoc();
        $cart_count = $cart_count_row['total'] ?? 0;
    }
    $cart_count_query->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;600&display=swap" rel="stylesheet">
    <style>
        /* Header and Navigation */
header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 10%;
    border-bottom: 0.8px solid #dddddd;
}

.logo {
    cursor: pointer;
    height: 40px;
}

.navlinks {
    list-style: none;
    display: flex;
}

.navlinks li {
    display: inline-block;
    padding: 0 30px;
}

.navlinks li a {
    font-family: "Lexend", sans-serif;
    font-weight: 500;
    font-size: 18px;
    color: #000000;
    text-decoration: none;
    transition: all 0.3s ease 0s;
}

.navlinks li a:hover {
    color: #0088a9;
}

/* Cart and Profile Section */
.cart-profile {
    display: flex;
    align-items: center;
    gap: 15px;
}

.cart-button {
    position: relative;
    display: inline-block;
}

.cart-image {
    width: 30px;
    height: auto;
}

.counter {
    position: absolute;
    top: -10px;
    right: -10px;
    background-color: #ff3333;
    color: white;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: bold;
}

/* Profile Dropdown */
.profile-dropdown {
    position: relative;
    display: inline-block;
}

.profile-btn {
    padding: 8px 12px;
    border: none;
    cursor: pointer;
    background-color: transparent;
}

.profile-btn img {
    width: 30px;
}

.dropdown-content {
    display: none;
    position: absolute;
    right: 0;
    background-color: white;
    min-width: 150px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border-radius: 5px;
    z-index: 10;
}

.dropdown-content a {
    display: block;
    text-decoration: none;
    color: black;
    padding: 10px;
    border-radius: 5px;
    background-color: white;
}

.dropdown-content a:hover {
    background-color: #ddd;
}

.profile-dropdown:hover .dropdown-content {
    display: block;
}
    </style>

</head>
<body>
    <header>
        <a href="../main/index.php">
            <img src="../images/logo.svg" class="logo" alt="logo">
        </a>    
        <nav>
            <ul class="navlinks">
                <li><a href="../products/keyboards.php">Keyboards</a></li>
                <li><a href="../products/mice.php">Mice</a></li>
                <li><a href="../products/accessories.php">Accessories</a></li>
            </ul>
        </nav>
        <div class="cart-profile">
            <a href="../cart/cart_view.php" class="cart-button">
                <img src="../images/shopping-cart.png" class="cart-image" alt="Shopping Cart">
                <span class="counter"><?= $cart_count ?></span>
            </a>
            <div class="profile-dropdown">
                <button class="profile-btn"><img src="../images/user.png" alt="User Profile"></button>
                <div class="dropdown-content">
                    <a href="../user/my_profile.php">My Profile</a>
                    <a href="../user/my_purchases.php">My Purchases</a>
                    <a href="../auth/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </header>
</body>

</html>
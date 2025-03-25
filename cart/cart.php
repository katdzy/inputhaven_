<?php
session_start();
$conn = new mysqli("localhost", "root", "", "shop");

// Check database connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Ensure the user is logged in
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "client") {
    header("Location: ../auth/login.php");
    exit();
}

if (isset($_POST['add_to_cart'])) {
    $user_id = $_SESSION["user_id"];
    $product_id = $_POST["product_id"];
    $quantity = (int)$_POST["quantity"];

    // Check stock availability
    $stock_query = $conn->prepare("SELECT stock FROM products WHERE product_id = ?");
    $stock_query->bind_param("i", $product_id);
    $stock_query->execute();
    $stock_result = $stock_query->get_result();
    $product = $stock_result->fetch_assoc();
    $available_stock = $product['stock'] ?? 0;

    // Check current cart quantity for this product
    $cart_query = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $cart_query->bind_param("si", $user_id, $product_id);
    $cart_query->execute();
    $cart_result = $cart_query->get_result();
    $cart_item = $cart_result->fetch_assoc();
    $existing_quantity = $cart_item['quantity'] ?? 0;

    // Total quantity after adding the new amount
    $new_quantity = $existing_quantity + $quantity;

    if ($new_quantity > $available_stock) {
        // Redirect back with stock error and product_id
        header("Location: ../main/index.php?" . http_build_query(["stock_error" => $product_id]));
        exit();
    } else {
        if ($existing_quantity > 0) {
            // Update existing cart item quantity
            $update_cart = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?");
            $update_cart->bind_param("isi", $quantity, $user_id, $product_id);
            $update_cart->execute();
        } else {
            // Insert new item into cart
            $insert_cart = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $insert_cart->bind_param("sii", $user_id, $product_id, $quantity);
            $insert_cart->execute();
        }
    }
}

// Redirect back to the previous page
header("Location: ../main/index.php");
exit();

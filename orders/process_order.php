<?php
include '../includes/connect_db.php';
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Ensure user is logged in
if (!isset($_SESSION["user_id"])) {
    die("Error: User not logged in.");
}

$user_id = $_SESSION["user_id"];

// Ensure necessary POST data exists
if (!isset($_POST["selected_products"], $_POST["name"], $_POST["shipping_courier"], $_POST["payment_method"])) {
    die("Error: Missing required form data.");
}

// Check if using saved address or new address
$using_saved_address = isset($_POST["address_option"]) && $_POST["address_option"] === "saved" && isset($_POST["selected_address_id"]);

if ($using_saved_address) {
    // Validate the selected address belongs to this user
    $address_check = $conn->prepare("SELECT address_id, address, postal_code FROM user_addresses WHERE address_id = ? AND user_id = ?");
    if (!$address_check) {
        die("Query preparation failed: " . $conn->error);
    }
    $address_id = $_POST["selected_address_id"];
    $address_check->bind_param("is", $address_id, $user_id);
    if (!$address_check->execute()) {
        die("Query execution failed: " . $address_check->error);
    }
    $address_result = $address_check->get_result();
    
    if ($address_result->num_rows === 0) {
        die("Error: Invalid address selected.");
    }
    
    $address_data = $address_result->fetch_assoc();
    $address = $address_data["address"];
    $postal_code = $address_data["postal_code"];
    $address_check->close();
} else {
    // Using new address
    if (!isset($_POST["address"], $_POST["postal_code"])) {
        die("Error: Missing address information.");
    }
    
    $address = $_POST["address"];
    $postal_code = $_POST["postal_code"];
    
    // Check if we should save this address
    if (isset($_POST["save_address"]) && $_POST["save_address"] == 1) {
        // Insert address into user_addresses
        $address_query = $conn->prepare("INSERT INTO user_addresses (user_id, address, postal_code) VALUES (?, ?, ?)");
        if (!$address_query) {
            die("Query preparation failed: " . $conn->error);
        }
        $address_query->bind_param("sss", $user_id, $address, $postal_code);
        if (!$address_query->execute()) {
            die("Query execution failed: " . $address_query->error);
        }
        $address_id = $address_query->insert_id;
        $address_query->close();
    } else {
        // Create temporary address record for this order
        $address_query = $conn->prepare("INSERT INTO user_addresses (user_id, address, postal_code) VALUES (?, ?, ?)");
        if (!$address_query) {
            die("Query preparation failed: " . $conn->error);
        }
        $address_query->bind_param("sss", $user_id, $address, $postal_code);
        if (!$address_query->execute()) {
            die("Query execution failed: " . $address_query->error);
        }
        $address_id = $address_query->insert_id;
        $address_query->close();
    }
}

$selected_products = $_POST["selected_products"];
$name = $_POST["name"];
$shipping_courier_id = $_POST["shipping_courier"];
$payment_method_id = $_POST["payment_method"];

// Validate if the selected shipping courier exists BEFORE inserting the order
$courier_check = $conn->prepare("SELECT courier_id FROM shipping_couriers WHERE courier_id = ?");
$courier_check->bind_param("i", $shipping_courier_id);
$courier_check->execute();
$courier_result = $courier_check->get_result();
if ($courier_result->num_rows === 0) {
    die("Error: Invalid shipping courier selected.");
}
$courier_check->close();

// Validate if the selected payment method exists
$payment_check = $conn->prepare("SELECT payment_id FROM payment_methods WHERE payment_id = ?");
$payment_check->bind_param("i", $payment_method_id);
$payment_check->execute();
$payment_result = $payment_check->get_result();
if ($payment_result->num_rows === 0) {
    die("Error: Invalid payment method selected.");
}
$payment_check->close();

// Generate unique order ID
$order_id = "ORD" . time() . rand(100, 999);

// Calculate total price (yung may shipping fee of ₱65)
$total_price = 65; // Base shipping fee
foreach ($selected_products as $product_id) {
    $product_query = $conn->prepare("SELECT price, stock FROM products WHERE product_id = ?");
    if (!$product_query) {
        die("Query preparation failed: " . $conn->error);
    }
    $product_query->bind_param("i", $product_id);
    if (!$product_query->execute()) {
        die("Query execution failed: " . $product_query->error);
    }
    $product_result = $product_query->get_result();
    $product_data = $product_result->fetch_assoc();
    
    if (!$product_data) {
        die("Error: Product not found.");
    }
    
    $price = $product_data["price"];
    $stock = $product_data["stock"];

    // Check stock availability
    $cart_query = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $cart_query->bind_param("si", $user_id, $product_id);
    $cart_query->execute();
    $cart_result = $cart_query->get_result();
    $cart_data = $cart_result->fetch_assoc();
    $cart_quantity = $cart_data["quantity"];
    $cart_query->close();

    if ($cart_quantity > $stock) {
        die("Error: Not enough stock for product ID $product_id.");
    }

    // Add product price * quantity to total
    $total_price += $price * $cart_quantity;
}

// Insert order into orders table
$order_query = $conn->prepare("INSERT INTO orders (order_id, user_id, address_id, total_price, shipping_courier_id, payment_method_id, order_status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
if (!$order_query) {
    die("Query preparation failed: " . $conn->error);
}
$order_query->bind_param("ssiidi", $order_id, $user_id, $address_id, $total_price, $shipping_courier_id, $payment_method_id);
if (!$order_query->execute()) {
    die("Query execution failed: " . $order_query->error);
}
$order_query->close();

// Insert order items into order_items table
foreach ($selected_products as $product_id) {
    // Get product price at time of order
    $product_query = $conn->prepare("SELECT price FROM products WHERE product_id = ?");
    $product_query->bind_param("i", $product_id);
    $product_query->execute();
    $product_result = $product_query->get_result();
    $product_data = $product_result->fetch_assoc();
    $price_at_purchase = $product_data["price"];
    $product_query->close();

    // Get quantity from cart
    $cart_query = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $cart_query->bind_param("si", $user_id, $product_id);
    $cart_query->execute();
    $cart_result = $cart_query->get_result();
    $cart_data = $cart_result->fetch_assoc();
    $quantity = $cart_data["quantity"];
    $cart_query->close();

    // Insert into order_items
    $order_item_query = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)");
    $order_item_query->bind_param("siid", $order_id, $product_id, $quantity, $price_at_purchase);
    if (!$order_item_query->execute()) {
        die("Query execution failed: " . $order_item_query->error);
    }
    $order_item_query->close();

    // Deduct stock from products
    $update_stock_query = $conn->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ?");
    $update_stock_query->bind_param("ii", $quantity, $product_id);
    if (!$update_stock_query->execute()) {
        die("Stock update failed: " . $update_stock_query->error);
    }
    $update_stock_query->close();

    // Remove purchased items from cart
    $delete_cart_query = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    $delete_cart_query->bind_param("si", $user_id, $product_id);
    if (!$delete_cart_query->execute()) {
        die("Cart item removal failed: " . $delete_cart_query->error);
    }
    $delete_cart_query->close();
}

// Redirect to confirmation page
header("Location: order_confirmation.php?order_id=$order_id");
exit();
?>
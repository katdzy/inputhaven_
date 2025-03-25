<?php
include '../includes/connect_db.php';
include '../templates/header.php';

// Check database connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Check if user is logged in as client
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "client") {
    header("Location: ../auth/login.php");
    exit();
}

// Check if order_id is provided
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    die("Invalid Order ID.");
}

$order_id = $_GET['order_id']; 
$user_id = $_SESSION["user_id"];

// Fetch order details ensuring it belongs to the user
$query = $conn->prepare("
    SELECT orders.*, user_addresses.address, 
           shipping_couriers.courier_name, payment_methods.method_name
    FROM orders
    JOIN user_addresses ON orders.address_id = user_addresses.address_id
    JOIN shipping_couriers ON orders.shipping_courier_id = shipping_couriers.courier_id
    JOIN payment_methods ON orders.payment_method_id = payment_methods.payment_id
    WHERE orders.order_id = ? AND orders.user_id = ?
");
$query->bind_param("ss", $order_id, $user_id); 

$query->execute();
$result = $query->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    die("Order not found or does not belong to you.");
}

// Fetch order items
$item_query = $conn->prepare("
    SELECT order_items.*, products.name, products.image 
    FROM order_items 
    JOIN products ON order_items.product_id = products.product_id 
    WHERE order_items.order_id = ?
");
$item_query->bind_param("s", $order_id); 
$item_query->execute();
$item_result = $item_query->get_result();
$order_items = $item_result->fetch_all(MYSQLI_ASSOC);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Purchase Details</title>
    <link rel="stylesheet" href="../css/mypurchases_details.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;600&display=swap" rel="stylesheet">
    <style>
/* Reset and General Styles */
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: "Lexend", sans-serif;
}

body {
    background-color: #ffffff;
    color: #333;
}

/* Container */
.container {
    max-width: 1000px;
    margin: 30px auto 50px;
    padding: 30px;
    background-color: #f5f5f5;
    border-radius: 15px;
    box-shadow: 0 6px 16px rgba(1, 42, 87, 0.08);
}
    </style>
</head>
<body>
    <div class="container">
        <h2>My Purchase Details</h2>
        <a href="my_purchases.php" class="back-btn"> Back to My Purchases</a>

        <div class="order-info">
            <p><strong>Address:</strong> <?= htmlspecialchars($order['address']) ?></p>
            <p><strong>Shipping Courier:</strong> <?= htmlspecialchars($order['courier_name']) ?></p>
            <p><strong>Payment Method:</strong> <?= htmlspecialchars($order['method_name']) ?></p>
            <p><strong>Order Status:</strong> <?= htmlspecialchars($order['order_status']) ?></p>
            <p><strong>Order Date:</strong> <?= htmlspecialchars($order['order_date']) ?></p>
        </div>

        <h3>Ordered Products</h3>
        <div class="products-container">
            <?php foreach ($order_items as $item): ?>
                <div class="product-card">
                    <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                    <div class="product-details">
                        <p><strong><?= htmlspecialchars($item['name']) ?></strong></p>
                        <p>₱<?= number_format($item['price_at_purchase'], 2) ?> x <?= (int)$item['quantity'] ?></p>
                        <p><strong>Subtotal:</strong> ₱<?= number_format($item['price_at_purchase'] * $item['quantity'], 2) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <p class="total-price">Total Price: ₱<?= number_format($order['total_price'], 2) ?></p>
    </div>
</body>
</html>

<?php 
$query->close();
$item_query->close();
$conn->close();
?>
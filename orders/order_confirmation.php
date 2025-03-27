<?php
include '../includes/connect_db.php';
include '../templates/header.php';

// Check database connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Verify user session and order ID
if (!isset($_SESSION["user_id"]) || !isset($_GET["order_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

$order_id = $_GET["order_id"]; 
$user_id = $_SESSION["user_id"];

// Fetch order details
$order_sql = "SELECT o.order_id, o.total_price, o.order_status, o.order_date,
                     sc.courier_name, pm.method_name
              FROM orders o
              LEFT JOIN shipping_couriers sc ON o.shipping_courier_id = sc.courier_id
              LEFT JOIN payment_methods pm ON o.payment_method_id = pm.payment_id
              WHERE o.order_id = ? AND o.user_id = ?";
$order_query = $conn->prepare($order_sql);
if (!$order_query) {
    die("Query preparation failed: " . $conn->error);
}
$order_query->bind_param("ss", $order_id, $user_id);
$order_query->execute();
$order_result = $order_query->get_result();

if ($order_result->num_rows === 0) {
    die("Order not found.");
}

$order = $order_result->fetch_assoc();
$order_query->close();

// Fetch ordered products
$items_sql = "SELECT p.name, p.image, oi.quantity, oi.price_at_purchase
              FROM order_items oi
              INNER JOIN products p ON oi.product_id = p.product_id
              WHERE oi.order_id = ?";
$items_query = $conn->prepare($items_sql);
if (!$items_query) {
    die("Query preparation failed: " . $conn->error);
}
$items_query->bind_param("s", $order_id);
$items_query->execute();
$items_result = $items_query->get_result();
$order_items = $items_result->fetch_all(MYSQLI_ASSOC);
$items_query->close();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Order Confirmation</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/order_conf.css">
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

        /* Confirmation Container */
        .container {
            max-width: 1000px;
            margin: 30px auto 50px;
            padding: 30px;
            background-color: #f7f9fc;
            border-radius: 15px;
            box-shadow: 0 6px 16px rgba(1, 42, 87, 0.08);
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Order Confirmation</h1>
    <div class="thank-you">
        <p>Thank you for your order! Your order has been placed successfully.</p>
    </div>
    
    <div class="section-header">
        <h3>Order Details</h3>
    </div>
    <div class="order-info">
        <p><strong>Order ID:</strong> <span><?= htmlspecialchars($order['order_id']) ?></span></p>
        <p><strong>Status:</strong> <span class="status-badge"><?= htmlspecialchars($order['order_status']) ?></span></p>
        <p><strong>Order Date:</strong> <span><?= htmlspecialchars($order['order_date']) ?></span></p>
        <p><strong>Total Price:</strong> <span>₱<?= number_format($order['total_price'], 2) ?></span></p>
        <p><strong>Shipping Courier:</strong> <span><?= htmlspecialchars($order['courier_name'] ?? "Not Available") ?></span></p>
        <p><strong>Payment Method:</strong> <span><?= htmlspecialchars($order['method_name'] ?? "Not Available") ?></span></p>
    </div>
    
    <div class="section-header">
        <h3>Ordered Products</h3>
    </div>
    <div class="product-container">
        <?php foreach ($order_items as $item): ?>
        <div class="product-pill">
            <img class="product-image" src="<?= htmlspecialchars($item['image']) ?>" alt="Product Image">
            <div class="product-details">
                <div class="product-name"><?= htmlspecialchars($item['name']) ?></div>
                <div class="product-price">₱<?= number_format($item['price_at_purchase'], 2) ?></div>
                <div class="product-quantity"><?= (int)$item['quantity'] ?></div>
                <div class="product-subtotal">₱<?= number_format($item['price_at_purchase'] * $item['quantity'], 2) ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="total-container">
        <div class="total-price">Total Price: ₱<?= number_format($order['total_price'], 2) ?></div>
    </div>
    
    <div class="button-container">
        <a class="back-link" href="../index.php"> Return to Home</a>
    </div>
</div>
</body>
</html>
<?php $conn->close(); ?>
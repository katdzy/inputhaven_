<?php
include '../includes/connect_db.php';
include '../templates/admin_header.php';

// Check database connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Check if user is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

// Check if order_id is provided
if (!isset($_GET["order_id"])) {
    die("Order ID is missing.");
}

$order_id = $_GET["order_id"];

// Handle order status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_status"])) {
    $new_status = $_POST["order_status"];
    $update_query = $conn->prepare(
        "UPDATE orders SET order_status = ? WHERE order_id = ?"
    );
    $update_query->bind_param("ss", $new_status, $order_id);
    if ($update_query->execute()) {
        $message = "Order status updated successfully!";
    } else {
        $message = "Failed to update order status.";
    }
    $update_query->close();
}

// Fetch order details
$query = $conn->prepare("
    SELECT orders.*, users.username, user_addresses.address, 
           shipping_couriers.courier_name, payment_methods.method_name
    FROM orders
    JOIN users ON orders.user_id = users.user_id
    JOIN user_addresses ON orders.address_id = user_addresses.address_id
    JOIN shipping_couriers ON orders.shipping_courier_id = shipping_couriers.courier_id
    JOIN payment_methods ON orders.payment_method_id = payment_methods.payment_id
    WHERE orders.order_id = ?
");
$query->bind_param("s", $order_id);
$query->execute();
$result = $query->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    die("Order not found.");
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

// Get all possible statuses
$statuses = [
    "Pending",
    "Shipped",
    "Out for Delivery",
    "Delivered",
    "Cancelled",
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin - Order Details</title>
    <link rel="stylesheet" href="../css/a-order-details.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Lexend', sans-serif;
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            display: flex;
            flex-direction: column;
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        
    </style>
</head>

<body>
    
    <div class="main-content">
        <div class="container">
            <h2>Order Details</h2>
            
            <?php if (isset($message)): ?>
                <div class="message"><?= $message ?></div>
            <?php endif; ?>
            
            <a href="admin_orders.php" class="back-link">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M10 12L6 8L10 4" stroke="#012a57" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Back to Orders
            </a>
            
            <div class="order-details">
                <div class="order-details-grid">
                    <div class="detail-item">
                        <div class="detail-label">Order ID</div>
                        <div class="detail-value"><?= htmlspecialchars(
                            $order["order_id"]
                        ) ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Customer</div>
                        <div class="detail-value"><?= htmlspecialchars(
                            $order["username"]
                        ) ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Order Date</div>
                        <div class="detail-value"><?= date(
                            "F j, Y g:i A",
                            strtotime($order["order_date"])
                        ) ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Status</div>
                        <div class="detail-value">
                            <span class="status-badge status-<?= strtolower(
                                str_replace(" ", "-", $order["order_status"])
                            ) ?>">
                                <?= htmlspecialchars($order["order_status"]) ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Shipping Courier</div>
                        <div class="detail-value"><?= htmlspecialchars(
                            $order["courier_name"]
                        ) ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Payment Method</div>
                        <div class="detail-value"><?= htmlspecialchars(
                            $order["method_name"]
                        ) ?></div>
                    </div>
                </div>
                
                <div class="detail-item" style="margin-top: 15px;">
                    <div class="detail-label">Shipping Address</div>
                    <div class="detail-value"><?= htmlspecialchars(
                        $order["address"]
                    ) ?></div>
                </div>
            </div>

            <h3>Ordered Products</h3>
            <div class="products">
                <?php foreach ($order_items as $item): ?>
                    <div class="product-card">
                        <img src="<?= htmlspecialchars(
                            $item["image"]
                        ) ?>" alt="Product">
                        <div class="product-info">
                            <p class="product-name"><?= htmlspecialchars(
                                $item["name"]
                            ) ?></p>
                            <p class="product-price">₱<?= number_format(
                                $item["price_at_purchase"],
                                2
                            ) ?> × <?= (int) $item["quantity"] ?></p>
                            <p class="product-subtotal">Subtotal: ₱<?= number_format(
                                $item["price_at_purchase"] * $item["quantity"],
                                2
                            ) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <p class="total-price">Total Price: ₱<?= number_format(
                $order["total_price"],
                2
            ) ?></p>

            <!-- Order Status Update Form -->
            <div class="status-form">
                <h3>Update Order Status</h3>
                <form method="POST">
                    <div class="form-row">
                        <label for="order_status">Change Status:</label>
                        <select name="order_status" id="order_status" required>
                            <?php foreach ($statuses as $status): ?>
                                <option value="<?= $status ?>" <?= $order[
    "order_status"
] == $status
    ? "selected"
    : "" ?>>
                                    <?= $status ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="update_status" class="update-btn">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-hide message after 5 seconds
        const message = document.querySelector('.message');
        if (message) {
            setTimeout(() => {
                message.style.display = 'none';
            }, 5000);
        }
    </script>
</body>
</html>

<?php
include '../includes/connect_db.php';
include '../templates/header.php';

// Check database connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "client") {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Fetch orders categorized by status
$statuses = ["Pending", "Shipped", "Out for Delivery", "Delivered", "Cancelled"];
$order_data = [];

foreach ($statuses as $status) {
    $query = $conn->prepare("
        SELECT orders.order_id, 
               orders.total_price, 
               shipping_couriers.courier_name, 
               payment_methods.method_name, 
               orders.order_status,
               GROUP_CONCAT(products.name SEPARATOR ', ') AS item_names,
               GROUP_CONCAT(products.image SEPARATOR ', ') AS item_images
        FROM orders
        JOIN order_items ON orders.order_id = order_items.order_id
        JOIN products ON order_items.product_id = products.product_id
        JOIN shipping_couriers ON orders.shipping_courier_id = shipping_couriers.courier_id
        JOIN payment_methods ON orders.payment_method_id = payment_methods.payment_id
        WHERE orders.user_id = ? AND orders.order_status = ?
        GROUP BY orders.order_id
        ORDER BY orders.order_date DESC
    ");
    $query->bind_param("is", $user_id, $status);
    $query->execute();
    $result = $query->get_result();
    $order_data[$status] = $result->fetch_all(MYSQLI_ASSOC);
    $query->close();

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

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Orders</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/mypurchases.css">
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
        
    </style>
    <script>
        function showTab(status) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-button').forEach(button => button.classList.remove('active'));
            
            document.getElementById(status).classList.add('active');
            document.getElementById(status + "-btn").classList.add('active');
        }
    </script>
</head>
<body>

    <h3>My Orders</h3>
    <div class="main-container">
        

        <div class="tabs">
            <?php foreach ($statuses as $status): ?>
                <button class="tab-button" id="<?= $status ?>-btn" onclick="showTab('<?= $status ?>')">
                    <?= $status ?> (<?= count($order_data[$status]) ?>)
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Order Details -->
        <?php foreach ($statuses as $status): ?>
            <div class="tab-content" id="<?= $status ?>">
                <?php if (!empty($order_data[$status])): ?>
                    <?php foreach ($order_data[$status] as $order): ?>
                        <div class="order-container">
                            <div class="order-header">
                                <div class="order-id">Order #<?= $order['order_id'] ?></div>
                                <div class="order-date"><?= date('F j, Y', strtotime($order['order_date'] ?? 'now')) ?></div>
                            </div>
                            
                            <div class="order-details">
                                <p><strong>Items:</strong> <?= htmlspecialchars($order['item_names']) ?></p>
                                <p><strong>Total Price:</strong> ₱<?= number_format($order['total_price'], 2) ?></p>
                                <p><strong>Shipping Courier:</strong> <?= htmlspecialchars($order['courier_name']) ?></p>
                                <p><strong>Payment Method:</strong> <?= htmlspecialchars($order['method_name']) ?></p>
                            </div>
                            
                            <div class="order-images">
                                <?php
                                $images = explode(", ", $order['item_images']);
                                foreach ($images as $image): ?>
                                    <img src="<?= htmlspecialchars($image) ?>" alt="Product Image">
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="order-actions">
                                <a href="my_purchase_details.php?order_id=<?= $order['order_id'] ?>" class="view-btn">View Details</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-message">
                        <p>No orders in this category.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <div class="button-container">
            <a href="../index.php" class="back-btn">Back to Shopping</a>
        </div>
    </div>

    <script>
        // Show the "Pending" tab by default
        showTab('Pending');
    </script>
</body>
</html>

<?php $conn->close(); ?>
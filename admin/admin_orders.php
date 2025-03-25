<?php
include '../includes/connect_db.php';
include '../templates/admin_header.php';

// Check database connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Check if admin is logged in
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

// Handle order status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["order_id"], $_POST["order_status"])) {
    $order_id = $_POST["order_id"];
    $order_status = $_POST["order_status"];

    $update_query = $conn->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
    $update_query->bind_param("ss", $order_status, $order_id);

    if ($update_query->execute()) {
        $message = "Order status updated successfully!";
        // Redirect to refresh the page data after update
        header("Location: admin_orders.php?status=" . urlencode($order_status) . "&message=" . urlencode($message));
        exit();
    } else {
        $message = "Error updating order status: " . $conn->error;
    }

    $update_query->close();
}

// Get message from URL if present
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}

// Fetch all orders
$all_orders_query = $conn->query("SELECT order_status, COUNT(*) as count FROM orders GROUP BY order_status");
$order_counts = [];

while ($row = $all_orders_query->fetch_assoc()) {
    $order_counts[$row['order_status']] = $row['count'];
}

// Ensure all statuses have a count (even if zero)
$statuses = ["Pending", "Shipped", "Out for Delivery", "Delivered", "Cancelled"];
foreach ($statuses as $status) {
    if (!isset($order_counts[$status])) {
        $order_counts[$status] = 0;
    }
}

// Determine which tab to show (from URL or default to Pending)
$active_status = isset($_GET['status']) && in_array($_GET['status'], $statuses) ? $_GET['status'] : 'Pending';

// Fetch orders for active tab only
$query = $conn->prepare("
    SELECT orders.*, 
           users.username, 
           user_addresses.address, 
           shipping_couriers.courier_name, 
           payment_methods.method_name
    FROM orders 
    JOIN users ON orders.user_id = users.user_id
    JOIN user_addresses ON orders.address_id = user_addresses.address_id
    JOIN shipping_couriers ON orders.shipping_courier_id = shipping_couriers.courier_id
    JOIN payment_methods ON orders.payment_method_id = payment_methods.payment_id
    WHERE orders.order_status = ?
    ORDER BY order_date DESC
");
$query->bind_param("s", $active_status);
$query->execute();
$result = $query->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
$query->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Admin - Manage Orders</title>
    <link rel="stylesheet" href="../css/a-orders.css">
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
            <h3>Order Management</h3>
            
            <?php if (isset($message)): ?>
                <div class="message"><?= $message ?></div>
            <?php endif; ?>
            
            <a href="admin.php" class="back-link">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M10 12L6 8L10 4" stroke="#012a57" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Back to Dashboard
            </a>

            <!-- Tabs -->
            <div class="tabs">
                <?php foreach ($statuses as $status): ?>
                    <a href="?status=<?= urlencode($status) ?>" 
                       class="tab-button <?= $active_status === $status ? 'active' : '' ?>">
                        <?= $status ?> 
                        <span class="count-badge">(<?= $order_counts[$status] ?>)</span>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Orders Table -->
            <?php if (empty($orders)): ?>
                <div class="empty-state">
                    <p>No orders with "<?= $active_status ?>" status.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Update Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?= htmlspecialchars($order['order_id']) ?></td>
                                <td><?= htmlspecialchars($order['username']) ?></td>
                                <td>₱<?= number_format($order['total_price'], 2) ?></td>
                                <td>
                                    <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $order['order_status'])) ?>">
                                        <?= htmlspecialchars($order['order_status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="post" class="status-form">
                                        <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                        <select name="order_status">
                                            <?php foreach ($statuses as $option): ?>
                                                <option value="<?= $option ?>" <?= $order['order_status'] === $option ? 'selected' : '' ?>>
                                                    <?= $option ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="update-btn">Update</button>
                                    </form>
                                </td>
                                <td>
                                    <a href="admin_order_details.php?order_id=<?= $order['order_id'] ?>" class="view-link">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
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

<?php $conn->close(); ?>
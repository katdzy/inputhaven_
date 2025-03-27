<?php
include '../includes/connect_db.php';
include '../templates/header.php';

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Fetch cart items
$sql = "SELECT cart.product_id, cart.quantity, products.name, products.price, products.image 
        FROM cart 
        INNER JOIN products ON cart.product_id = products.product_id 
        WHERE cart.user_id = ?";

$query = $conn->prepare($sql);
$query->bind_param("s", $user_id);
$query->execute();
$result = $query->get_result();
$query->close();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Your Cart</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/cart.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;600&display=swap" rel="stylesheet">
    <script>
    function updateTotal() {
        let checkboxes = document.querySelectorAll('input[name="selected_products[]"]:checked');
        let total = 0;

        checkboxes.forEach(checkbox => {
            let price = parseFloat(checkbox.getAttribute("data-price"));
            let quantity = parseInt(checkbox.getAttribute("data-quantity"));
            total += price * quantity;
        });

        let totalAmount = document.getElementById("totalAmount");
        totalAmount.textContent = "Total Amount: ₱" + total.toFixed(2);

        // Hide error message if at least one item is selected
        let errorDiv = document.getElementById("checkoutError");
        if (checkboxes.length > 0) {
            errorDiv.style.display = "none";
        }
    }

    function validateCheckout() {
        let checkboxes = document.querySelectorAll('input[name="selected_products[]"]:checked');
        let errorDiv = document.getElementById("checkoutError");

        if (checkboxes.length === 0) {
            errorDiv.style.display = "block";
            return false; // Prevent form submission
        }

        return true; // Allow form submission
    }

    // Initialize total on page load
    document.addEventListener("DOMContentLoaded", updateTotal);
</script>


</head>
<body>

    <h1 class="title">Your Basket</h1>

    <div class="cart-container">
        <?php if ($result->num_rows > 0): ?>
            <form action="../orders/checkout.php" method="post" onsubmit="return validateForm()">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="cart-item">
                        <input type="checkbox" name="selected_products[]" value="<?= htmlspecialchars($row['product_id']) ?>" data-price="<?= $row['price'] ?>" data-quantity="<?= $row['quantity'] ?>" onclick="updateTotal()">
                        <img src="<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                        <div class="cart-details">
                            <h2><?= htmlspecialchars($row['name']) ?></h2>
                            <p>Price: ₱<?= number_format($row['price'], 2) ?></p>
                            <p>Quantity: <?= (int)$row['quantity'] ?></p>
                        </div>
                        <a href="remove_from_cart.php?product_id=<?= htmlspecialchars($row['product_id']) ?>" class="remove-btn"><img src="../images/delete.png"></a>
                    </div>
                <?php endwhile; ?>

                <div class="cart-summary">
    <div id="checkoutError" style="display: none;">Please select at least one item to proceed.</div>
    <div class="cart-summary-inner">
        <div class="cart-footer" id="totalAmount">Total Amount: ₱0.00</div>
        <div class="cart-buttons">
            <button type="submit" class="checkout-btn" onclick="return validateCheckout()">Proceed to Checkout</button>
            <a href="../index.php" class="continue-btn">Continue Shopping</a>
        </div>
    </div>
</div>
            </form>
        <?php else: ?>
            <p>Your cart is empty.</p>
        <?php endif; ?>
    </div>

    
</body>
</html>
